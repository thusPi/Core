<?php 
    namespace thusPi\Interfaces;
    
    class defaultInterface {
        protected $id;

        public function __construct($id) {
            $this->id = $id;
        }

        public function getProperties() {
            return null;
        }

        public function setProperties(array $new_properties) {
            return null;
        }

        public function getProperty(string $property = null) {
			if(!isset($property)) {
				return null;
			}

			if(($properties = $this->getProperties()) === false) {
				return null;
			}
			
			if(!isset($properties[$property])) {
				return null;
			}

			return $properties[$property];
		}

        public function setProperty(string $property = null, $value = null) {
            if(!isset($property) || !isset($value)) {
				return false;
			}

            return $this->setProperties([$property => $value]);
        }

    }
?>