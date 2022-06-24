<?php 
    namespace thusPi\Categories;
    
	use \thusPi\Interfaces\defaultInterface;

    class Category extends defaultInterface {
        public function __construct($id) {
            $this->id = $id;
        }

        public function getProperties() {
            if(!$categories = \thusPi\Categories\get_all()) {
                return null;
            }

            if(!isset($categories[$this->id])) {
                return null;
            }

            return $categories[$this->id];
        }
    }
 
    function get_all() {
        return \thusPi\Config\get(null, 'generic/categories');
    }
?>