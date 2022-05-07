<?php 
    namespace thusPi\Debug;

    class WaypointList {
        private $mode, $start, $counter, $previousTime, $enabled, $indentationLevel;

        public function __construct($mode = 'shell', $indentation_level = 0) {
            $this->mode             = $mode;
            $this->start            = round(microtime(true) * 1000);
            $this->indentationLevel = intval($indentation_level) ?? 0;
            $this->counter          = 1;
            $this->previousTime     = $this->start;
            $this->enabled          = true;

            return $this;
        }

        public function disable($disable = true) {
            $this->enabled = !$disable;

            return $this;
        }

        public function enable($enable = true) {
            $this->enabled = $enable;

            return $this;
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

            $at          = $time - $this->start;
            $at_str      = str_pad("{$at}", 5, '0', STR_PAD_LEFT);
            $diff        = $time - $this->previousTime;
            $diff_str    = ($diff >= 0 ? '+' : '-') . $diff;
            $indentation = str_repeat('    ', $this->indentationLevel);

            switch($this->mode) {
                case 'shell':
                    echo("{$indentation}[\033[1m{$at_str}ms\033[0m] (\033[1m{$diff_str}ms\033[0m) {$message}\n");
                    break;

                case 'html':
                    echo("{$indentation}[<b>{$at_str}ms</b>] (<b>{$diff_str}ms</b>) {$message}<br>");
                    break;

                case 'plain':
                    echo("{$indentation}[{$at_str}ms] ({$diff_str}ms) {$message}\n");
                    break;
            }

            $this->previousTime = $time;

            return $this;
        }
    }
?>