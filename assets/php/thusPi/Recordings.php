<?php 
    namespace thusPi\Recordings;

use DateTime;
use \thusPi\Interfaces\defaultInterface;

    class Analytic extends defaultInterface {
        private $userHistorySelection;
        
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

        public function getHistory($options) {
            $options = array_replace([
                'max_rows' => 750,
                'interval' => null,
                'x_start'  => null,
                'x_end'    => null
            ], $options);

            // Check if the interval is valid, otherwise unset it
            if(!in_array($options['interval'], ['hour', 'day', 'week', 'month', 'year'])) {
                $options['interval'] = null;
            }

            $rows = [];

            $files = glob(DIR_DATA."/recordings/history/{$this->id}/*.csv");

            $properties = $this->getProperties();

            foreach($files as $file) {
                if(($handle = fopen($file, 'r')) !== false) {
                    while(($values = fgetcsv($handle, 512, ',')) !== false) {
                        // Continue if value row doesn't have at least two values (x and y)
                        if(!isset($values[0]) || !isset($values[1])) {
                            continue;
                        }

                        // Force values to be float
                        $values = array_map('floatval', $values);

                        // Save x value in row
                        $row['x'] = $values[0];

                        // // Get array of all y values
                        // $y_values = array_values(array_slice($values, 1, count($properties['columns'])));
                        
                        // // Mirror them if specified in the manifest
                        // array_walk($y_values, function(&$value, $column_index) use ($properties) {
                        //     if(isset($properties['columns']['y'.$column_index]['mirrored']) && $properties['columns']['y'.$column_index]['mirrored'] == true) {
                        //         $value = $value * -1;
                        //     }
                        // });
                        
                        // // Save y values in row
                        // $row['y'] = $y_values;

                        // Save y values in row
                        $row['y'] = array_values(array_slice($values, 1, count($properties['columns'])));

                        // Save row in output
                        $rows[] = $row;
                    }
                    fclose($handle);
                }
            }

            return $this->compressRows($rows, $options);
        }

        private function compressRows($rows, $options) {
            if(!isset($rows[0])) {
                return false;
            }

            // Calculate the total amount of rows that are given
            $total_rows = count($rows);

            // Variable for storing the result
            $res = [];

            // Calculate the Nth row that should pass
            $keep_nth_row = ceil($total_rows/$options['max_rows']);

            // New row index counter
            $a = 0;

            $last_row = null;
            foreach ($rows as $i => $row) {
                if(!isset($options['interval'])) {
                    // User has not selected an interval, save the row
                    // unless it leads to exceeding the max number of rows
                    if($i % $keep_nth_row == 0) {
                        $res[$a] = $row;
                        $a++;
                    }

                    continue;
                }

                // User has selected an interval, check if both rows
                // have been recorded during the same interval
                if(!isset($last_row['x']) || $this->recordedAtSameInterval($row['x'], $last_row['x'], $options['interval'])) {
                    // This row was recorded during the same interval as the last row,
                    // store the row if it was not yet stored and continue
                    if(!isset($res[$a])) {
                        // Convert type to array so the values from other rows that were
                        // recorded during the same period can be pushed
                        $res[$a]['x'] = [$row['x']];

                        foreach ($row['y'] as $column_index => $value_numeric) {
                            // Convert value type to array so the values from other rows that were
                            // recorded during the same period can be pushed
                            $res[$a]['y'][$column_index] = [$value_numeric];
                        }
            
                        continue;
                    }

                    $res[$a]['x'][] = $row['x'];

                    foreach ($res[$a]['y'] as $column_index => &$value) {
                        // Continue if the column index doesn't exist in the current row or if the value is zero
                        if(!isset($row['y'][$column_index]) || $row['y'][$column_index] == 0) {
                            continue;
                        }

                        // Push the value of this row so the average can be calculated later
                        array_push($value, $row['y'][$column_index]);
                    }
                } else {
                    // The current row was not recorded during the same interval as the last row,
                    // increment the counter to create a new row
                    $a++;
                }

                $last_row = $row;
            }

            // If the user has selected an interval, loop all rows one last time to calculate the y averages
            // and the interval of the x
            if(isset($options['interval'])) {
                foreach ($res as &$row) {
                    // Calculate the start and end x
                    $row['x_end'] = end($row['x']);
                    $row['x']     = reset($row['x']);

                    // Calculate the average y value per column
                    foreach ($row['y'] as $column_index => &$value) {
                        $value = array_sum($value) / count($value);
                    }
                }
            }

            return array_values($res);
        }

        private function recordedAtSameInterval($unix1, $unix2, $interval) {
            $format = [
                'hour'  => 'd-m-Y H',
                'day'   => 'd-m-Y',
                'week'  => 'W m-Y',
                'month' => 'm-Y',
                'year'  => 'Y'
            ][$interval] ?? null;

            if(!isset($format)) {
                return false;
            }

            $date1 = date($format, $unix1);
            $date2 = date($format, $unix2);

            if($date1 == false || $date2 == false) {
                return false;
            }

            return ($date1 == $date2);
        }

        public function saveRecording($recording) {
            $filename = date('Y-m-d');
            $filepath = DIR_DATA."/recordings/history/{$this->id}/{$filename}.csv";

            $csv_string = str_putcsv($recording);

            $this->setProperties(['latest_recording' => time()]);

            return file_put_contents($filepath, $csv_string.PHP_EOL, FILE_APPEND);
        }

        public function getProperties() {
			return \thusPi\Recordings\get($this->id);
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