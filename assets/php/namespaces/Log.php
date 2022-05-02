<?php 
    namespace thusPi\Log;

    function write($title = '', $content = '', $group = 'info') {
        if(!in_array($group, ['success', 'info', 'warning', 'error', 'debug'])) {
            return false;
        }

        $filename = \thusPi\Log\getNewFilename();

        // Force one period at line end.
        $content = trim($content, '.').'.';

        // Create new file if it doesn't exist
        if(!file_exists(DIR_DATA."/logs/{$filename}.csv")) {
            file_put_contents(DIR_DATA."/logs/{$filename}.csv", '');
        }
        
        // Save line
        if(($handle = @fopen(DIR_DATA."/logs}/{$file_index}.csv", 'a')) !== false) {
            $line = [time(), $category, $title, $content];
            fputcsv($handle, $line);
            fclose($handle);
        }
    }

    function getNewFilename() {
        $new_filename = date('Y-m-d');

        return $new_filename;
    }

    function getItemCount($filename) {
        $dir = DIR_DATA.'/logs';
        $path = "{$dir}{$filename}.csv";
        
        if(!file_exists($path)) {
            return false;
        }

        $file = new SplFileObject($path, 'r');
        $file->seek(100000000);
        $count = $file->key();
        return $count;
    }

    function readLatest(int $n_to_read = 1) {
        $dir = DIR_DATA.'/logs';

        $files_to_read = [];
        $i = 0;

        for ($read=0; $read < $n_to_read;) { 
            $filename   = date('Y-m-d', strtotime("-{$i} days"));
            $item_count = \thusPi\Log\getItemCount($filename);
            var_dump($item_count);
            var_dump($filename);

            $read += max($item_count, 3);
            $i++;
        }
    }

    function readIndexesBetween(int $min_i = 0, int $max_i = -1) {
        global $d;
        $dir  = "{$d['data']}/logs";

        $log_items_all = [];

        if(isset($min_i) && isset($max_i)) {
            $min_i = max(0, $min_i); // Can't go below 0

            if((!isset($max_i) || ($max_i-$min_i) > 1000)) {
                $max_i = $min_i + 1000; // Read 1000 items at max.
            }

            $requested_log_files = range(
                ceil($min_i / 250),
                floor($max_i / 250)
            );

            foreach ($requested_log_files as $requested_log_file) {
                if(($log_items = \thusPi\Log\readFile($requested_log_file)) !== false) {
                    $log_items_keys = array_keys($log_items);
                    foreach ($log_items_keys as $log_item_index) {
                        if(
                            ($log_item_index < $min_i && $min_i !== -1) ||
                            ($log_item_index > $max_i && $max_i !== -1)
                        ) {
                            unset($log_items[$log_item_index]);
                        }
                        
                        $log_items[$log_item_index]['index'] = $log_item_index;
                    }
                    $log_items_all = $log_items_all + $log_items;
                }
            }
            return $log_items_all;
        }
        return false;
    }

    function readFile(int $file_index) {
        global $d;
        $dir = "{$d['data']}/logs";

        $items = [];

        $file = "{$dir}/{$file_index}.csv";

        if(!file_exists($file)) {
            return false;
        }

        if(($handle = @fopen($file, 'r')) !== false) {
            $file_index = intval(pathinfo($file, PATHINFO_FILENAME));
            $items_index = 0;
            while (($log_item = fgetcsv($handle)) !== false) {
                if(count($log_item) < 4) {
                    continue;
                }

                $items[($file_index * 250) + $items_index] = [
                    'at' => $log_item[0],
                    'category' => $log_item[1],
                    'title' => $log_item[2],
                    'content' => $log_item[3]
                ];
                $items_index++;
            }
            fclose($handle);
            return $items;
        }
        return false;
    }
?>