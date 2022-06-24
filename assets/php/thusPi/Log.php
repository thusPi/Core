<?php 
    namespace thusPi\Log;

    function write($title = '', $content = '', $group = 'info') {
        if(!in_array($group, ['success', 'info', 'warning', 'error', 'debug'])) {
            $group = 'info';
        }

        // Remove period at end of line
        $content = trim($content, '.');

        $filename = \thusPi\Log\getCurrentFilename();

        // Create new file if it doesn't exist
        if(!file_exists(DIR_DATA."/logs/{$filename}.csv")) {
            file_put_contents(DIR_DATA."/logs/{$filename}.csv", '');
        }
        
        // Save line:
        // Current time | First letter of group | Title | Content
        if(($handle = @fopen(DIR_DATA."/logs/{$filename}.csv", 'a')) !== false) {
            $line = [time(), substr($group, 0, 1), $title, $content];
            fputcsv($handle, $line);
            fclose($handle);
        }
    }

    function getCurrentFilename() {
        return date('Y-m-d');
    }

    function read($top = 25, $min_time = 0) {
        $messages = [];
        $i = 0;

        $files = array_diff(scandir(DIR_DATA.'/logs/'), array('..', '.'));
        natsort($files);

        // Put newest files at start of array
        $files = array_values(array_reverse($files));

        $min_time_midnight = strtotime(date('Y-m-d', $min_time));

        foreach ($files as $filename) {
            $filepath = DIR_DATA."/logs/{$filename}";

            // Continue if file does not exist or it is not a csv file
            if(!file_exists($filepath) || !is_file($filepath) || pathinfo($filepath, PATHINFO_EXTENSION) != 'csv') {
                continue;
            }

            // Continue if we're not interested in messages for that date
            if(strtotime(strtok($filename, '.')) < $min_time_midnight) {
                continue;
            }

            // Read messages
            if(($handle = @fopen($filepath, 'r')) !== false) {
                while (($log_item = fgetcsv($handle)) !== false) {
                    if(count($log_item) < 4) {
                        continue;
                    }

                    // Turn group letter into group name
                    $group = [
                        'd' => 'debug',
                        'e' => 'error',
                        'i' => 'info',
                        's' => 'success',
                        'w' => 'warning'
                    ][$log_item[1]] ?? 'info';

                    $messages[] = [
                        'at'          => $log_item[0],            // Unix timestamp
                        'at_readable' => \thusPi\Locale\date_format('full,full', $log_item[0]),
                        'group'       => $group,                  // Group
                        'title'       => ucfirst($log_item[2]),   // Title
                        'content'     => $log_item[3]             // Content
                    ];

                    $i++;

                    if($i > $top) {
                        break 2;
                    }
                }

                fclose($handle);
                return $messages;
            }

            return false;
        }
    }
?>