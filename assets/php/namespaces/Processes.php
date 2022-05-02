<?php 
    namespace thusPi\Processes;

    class Process {
        protected $name, $status, $issued;

        public function __construct($name, $status = 'running', $issued = null) {
            $db = \thusPi\Database\connect();
            
            $id     = \unique_id(16);
            $issued = $issued ?? time();

            $process = [
                'id'     => $id,
                'name'   => $name,
                'status' => $status,
                'issued' => $issued
            ];

            if(!$db->insert('processes', $process)) {
                return false;
            }

            $this->id     = $id;
            $this->name   = $name;
            $this->status = $status;
            $this->issued = $issued;

            return true;
        }

        public function status($status = null) {
            if(is_null($status)) {
                return $this->status;
            } else {
                $db = \thusPi\Database\connect();

                $db->where('id', $this->id);

                if($status == 'finished') {     
                    $db->delete('processes');
                } else {
                    $db->update('processes', ['status' => $status]);
                }

                $this->status = $status;
            }
        }
    }

    function get_all($name) {
        $db = \thusPi\Database\connect();

        $db->where('name', $name);
        $processes = $db->get('processes');
        
        return $processes;
    }

    function wait_for_finish($name, $ms_timeout = 16000, $ms_interval = 200) {
        $timeout_max = $ms_timeout;

        $db = \thusPi\Database\connect();

        $db->where('name', $name);

        while($ms_timeout > 0) {
            $ms_start = round(microtime(true) * 1000);
            $processes = $db->getOne('processes');
            $ms_end = round(microtime(true) * 1000);

            if(is_null($processes)) { // All processes finished
                return true;
            }

            ms_sleep($ms_interval - ($ms_end - $ms_start));

            $ms_timeout -= $ms_interval;
        }

        return false;
    }
?>