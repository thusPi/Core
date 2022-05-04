<?php 
    namespace thusPi\Debug;

    class WaypointList {
        private $mode, $start, $counter, $previous_time, $enabled;

        public function __construct($mode = 'shell', $start = null) {
            $this->mode          = $mode;
            $this->start         = round(microtime(true) * 1000);
            $this->counter       = 1;
            $this->previous_time = $this->start;
        }

        public function disable($disable = true) {
            $this->enabled = !$disable;
        }

        public function enable($enable = true) {
            $this->enabled = $enable;
        }

        public function printWaypoint($message = null, $time = null) {
            if($message === null) {
                $message = "Waypoint {$this->counter} reached.";
            }

            if($this->enabled !== true) {
                return false;
            }

            if($time === null) {
                $time = round(microtime(true) * 1000);
            }

            $at       = $time - $this->start;
            $at_str   = str_pad("{$at}", 5, '0', STR_PAD_LEFT);
            $diff     = $time - $this->previous_time;
            $diff_str = ($diff >= 0 ? '+' : '-') . $diff;

            switch($this->mode) {
                case 'shell':
                    echo("[\033[1m{$at_str}ms\033[0m] (\033[1m{$diff_str}ms\033[0m) {$message}\n");
                    break;

                case 'html':
                    echo("[<b>{$at_str}ms</b>] (<b>{$diff_str}ms</b>) {$message}<br>");
                    break;

                case 'plain':
                    echo("[{$at_str}ms] ({$diff_str}ms) {$message}\n");
                    break;
            }

            $this->previous_time = $time;
        }
    }
?>