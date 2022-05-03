<?php 
    namespace thusPi\Recordings;

	use \thusPi\Interfaces\defaultInterface;

    class Analytic extends defaultInterface {
        private $history_size;
        private $history_wanted_selection;
        
        public function __construct($id, $respect_permissions = false) {
            if($respect_permissions && !\thusPi\Users\CurrentUser::checkFlagItem('devices', $id)) {
                \thusPi\Response\error('no_permission', "You are not permitted to view device {$id}.");
            }

            $this->id = $id;
        }

        public function record($async = true) {
            $recorder = DIR_SYSTEM.'/recordings/record.php';

            if($async === true) {
                execute(script_name_to_shell_cmd($recorder, [$this->id]), $output, 0);
                return true;
            } else {
                execute(script_name_to_shell_cmd($recorder, [$this->id]), $output, 30);
                return $output;
            }
        }

        public function getHistory($max_rows = -1) {
            $rows = [];

            $files = glob(DIR_DATA."/recordings/history/{$this->id}/*.csv");

            foreach($files as $file) {
                if(($handle = fopen($file, 'r')) !== false) {
                    while(($values = fgetcsv($handle, 512, ',')) !== false) {
                        if(!isset($values[0]) || !isset($values[1])) {
                            continue;
                        }

                        // Force float values
                        $values = array_map('floatval', $values);

                        // Skip if x does not fit into selection
                        if(isset($this->history_wanted_selection['x_min']) && $values[0] < $this->history_wanted_selection['x_min'] || 
                           isset($this->history_wanted_selection['x_max']) && $values[0] > $this->history_wanted_selection['x_max']) {
                                continue;
                        }

                        // Save x value in row
                        $row['x'] = $values[0];
                        unset($values[0]);

                        // Skip if y does not fit into selection
                        // if(isset($this->history_wanted_selection['y_min']) && max($values) < $this->history_wanted_selection['y_min'] || 
                        //    isset($this->history_wanted_selection['y_max']) && min($values) > $this->history_wanted_selection['y_max']) {
                        //         continue;
                        // }

                        // Save y values in row
                        $row['y'] = array_values($values); // Re-index the numeric keys

                        // Save row in output
                        $rows[] = $row;
                    }
                    fclose($handle);
                }
            }

            return $this->compressRows($rows, $max_rows);
        }

        public function getHistorySize() {
            if(!isset($this->history_size)) {
                $this->getHistory(750);
            }

            return $this->history_size;
        }

        public function setHistorySelection($x0 = null, $y0 = null, $x1 = null, $y1 = null) {
            $x0 = is_numeric($x0) ? floatval($x0) : null;
            $y0 = is_numeric($y0) ? floatval($y0) : null;
            $x1 = is_numeric($x1) ? floatval($x1) : null;
            $y1 = is_numeric($y1) ? floatval($y1) : null;

            $this->history_wanted_selection = [
                'x_min' => min($x0, $x1) ?? null,
                'y_min' => min($y0, $y1) ?? null,
                'x_max' => max($x0, $x1) ?? null,
                'y_max' => max($y0, $y1) ?? null
            ];

            if(@$_POST['debug'] == 'true') {
                var_dump($this->history_wanted_selection);
            }

            return $this;
        }

        public function compressRows($rows, $max_rows = -1) {
            if($max_rows < 0) {
                return $rows;
            } 

            $history_size = [
                'min_x' => null,
                'max_x' => null,
                'dif_x' => null,
                'min_y' => null,
                'max_y' => null,
                'dif_y' => null
            ];

            $total_rows = count($rows);
            if($max_rows > $total_rows) {
                return $rows;
            }

            $keep_nth_row = ceil($total_rows/$max_rows);

            foreach ($rows as $i => $row) {
                if($i % $keep_nth_row != 0) {
                    unset($rows[$i]);
                } else {
                    // Check for min and max x or y to determine graph size 
                    if(!isset($history_size['min_x']) || $row['x'] < $history_size['min_x']) { $history_size['min_x'] = $row['x']; }
                    if(!isset($history_size['max_x']) || $row['x'] > $history_size['max_x']) { $history_size['max_x'] = $row['x']; }

                    if(count($row['y']) > 1) {
                        $row_min_y = min(...$row['y']);
                        $row_max_y = max(...$row['y']);
                    } else {
                        $row_min_y = reset($row['y']);
                        $row_max_y = reset($row['y']);
                    }

                    if(!isset($history_size['min_y']) || $row_min_y < $history_size['min_y']) { $history_size['min_y'] = $row_min_y; }
                    if(!isset($history_size['max_y']) || $row_max_y > $history_size['max_y']) { $history_size['max_y'] = $row_max_y; }
                }
            }

            // Re-index keys
            $rows = array_values($rows);

            // Save new history area
            $history_size['dif_x'] = abs($history_size['max_x'] - $history_size['min_x']);
            $history_size['dif_y'] = abs($history_size['max_y'] - $history_size['min_y']);
            $this->history_size = $history_size;
            
            return $rows;
        }

        public function saveRecording($recording) {
            $filename = date('Y-m-d');
            $filepath = DIR_DATA."/recordings/history/{$this->id}/{$filename}.csv";

            $csv_string = str_putcsv($recording);

            $this->setProperties(['latest_recording' => time()]);

            return file_put_contents($filepath, $csv_string.PHP_EOL, FILE_APPEND);
        }

        public function getProperties() {
			$properties = \thusPi\Recordings\get($this->id);
			return $properties;
		}

        public function setProperties($new_properties) {
            $db = \thusPi\Database\connect();

            $db->where('id', $this->id);
            if(!$db->update('analytics', $new_properties)) {
                return false;
            }

            return true;
        }
    }

    function get($id, $respect_permissions = false) {
        if($respect_permissions && !\thusPi\Users\CurrentUser::checkFlagItem('devices', $id)) {
            \thusPi\Response\error('no_permission');
            return null;
        }

        $db = \thusPi\Database\connect();

        $db->where('id', $id);
        $analytic = $db->getOne('analytics');

        if(!isset($analytic)) {
            return null;
        }

        if(is_string($analytic['axes'])) {
            $analytic['axes'] = @json_decode($analytic['axes'], true);
        }

        if(is_string($analytic['columns'])) {
            $analytic['columns'] = @json_decode($analytic['columns'], true);
        }

        $analytic = array_replace_recursive([
            'id'   => $id,
            'axes' => [
                'x' => [
                    'title' => '',
                    'unit'  => '',
                    'decimals' => 0
                ],
                'y' => [
                    'title' => '',
                    'unit'  => '',
                    'decimals' => 0
                ]
            ],
            'columns' => []
        ], $analytic);

        return $analytic;
    }

    function get_all($respect_permissions = false) {
        $db = \thusPi\Database\connect();

        $recordings = [];

        $ids = array_column($db->get('analytics', null, 'id'), 'id');

        foreach ($ids as $id) {
            if($respect_permissions && !\thusPi\Users\CurrentUser::checkFlagItem('devices', $id)) {
				continue;
			}

            $analytic = \thusPi\Recordings\get($id);

            if(!is_array($analytic)) {
                continue;
            }

            $recordings[] = $analytic;
        }

        return $recordings;
    }
?>