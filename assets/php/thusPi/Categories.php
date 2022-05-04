<?php 
    namespace thusPi\Categories;
    
	use \thusPi\Interfaces\defaultInterface;

    class Category extends defaultInterface {
        private $file_path;

        public function __construct($id) {
            $this->id = $id;
            $this->file_path = DIR_CONFIG.'/categories.json';
        }

        public function getProperties() {
            if(!$categories = \file_get_json(DIR_CONFIG.'/categories.json')) {
                return null;
            }

            if(!isset($categories[$this->id])) {
                return null;
            }

            return $categories[$this->id];
        }
    }
 
    function get_all() {
        return \file_get_json(DIR_CONFIG.'/categories.json');
    }
?>