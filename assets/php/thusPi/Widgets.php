<?php 
    namespace thusPi\Widgets;

    use thusPi\Interfaces\defaultInterface;

    class Widget extends defaultInterface {
        protected $extension_id;
        protected $extension_dir;
        protected $id;

        public function __construct($extension_id, $id) {
            $this->extension_id  = basename($extension_id);
            $this->extension_dir = DIR_EXTENSIONS."/{$extension_id}";
            $this->id            = basename($id);
            $this->dir           = "{$this->extension_dir}/features/dashboard/widgets/{$this->id}";
        }

        public function getProperties() {
            $extension = new \thusPi\Extensions\Extension($this->extension_id);

            $properties = $extension->getProperties()['features']['dashboard/widgets'][$this->id] ?? [];

            return array_merge([
                'extension_id' => $this->extension_id,
                'id' => $this->id
            ], $properties);
        }

        public function getHTML() {
            $extension = new \thusPi\Extensions\Extension($this->extension_id);
            $html      = $extension->callFeatureComponent('dashboard/widgets', $this->id, 'main');

            return $html;
        }

        public function getCSS() {
            $extension = new \thusPi\Extensions\Extension($this->extension_id);
            $css       = trim($extension->callFeatureComponent('dashboard/widgets', $this->id, 'widget.css'));
            
            // $all_ats_excluded = false;
            // while(strpos($css, '@') === 0) {
            //     $css = preg_match()

            //     // Check for "; on the first line
            //     preg_match('/(?<![\s])";/', $css, $matches, PREG_OFFSET_CAPTURE);
            //     $pos = (isset($matches[0][1]) ? $matches[0][1] + 2 : null);

            //     // If it doesn't exist, check for the next anywhere in the code }
            //     $pos = $pos ?? strpos($css, '}')+1;

            //     var_dump($pos);

            //     if($pos <= 1) {
            //         return;
            //     }

            //     $css = trim(substr($css, $pos));
            // }

            // Something with @.*?(?<=:)\)

            // ================================= //
            //    Limit styles to this widget    //
            // ================================= //

            // Remove all whitespace characters except spaces that aren't wrapped in double quotes
            $css = preg_replace('/[^\S ]+(?=(?:[^"]*"[^"]*")*[^"]*$)/', '', $css);
            
            // Remove all whitespace characters except spaces that aren't wrapped in single quotes
            $css = preg_replace('/[^\S ]+(?=(?:[^\']*"[^\']*\')*[^\']*$)/', '', $css);

            // Split styles in to array of rules
            $rules = explode('}', $css);
            
            // Prepend widget selector to css rule
            foreach ($rules as &$rule) {
                // Re-add the delimiter
                $rule .= '}';

                if(!str_contains($rule, '{') || str_starts_with($rule, '@')) {
                    continue;
                }

                $rule = ".dashboard-widget[data-widget-id=\"{$this->extension_id}_{$this->id}\"] {$rule}";
            }

            $css = implode('', $rules);

            return $css;
        }

        public function getJS() {
            $extension = new \thusPi\Extensions\Extension($this->extension_id);
            $js        = $extension->callFeatureComponent('dashboard/widgets', $this->id, 'widget.js');

            // Prevent error in empty files
            if(empty($js)) {
                $js = 'class Widget {}';
            }
            
            // Inject javascript which will register the widget
            $js = "thusPiAssign('data.dashboard.widgets.{$this->extension_id}_{$this->id}',{$js});";

            return $js;
        }

        public function getConfig() {
            return @file_get_json("{$this->dir}/widget.json") ?? null;
        }

        public function translate($key, $replacements = null, $fallback = null) {
            $extension = new \thusPi\Extensions\Extension($this->extension_id);
            return $extension->translate("features.dashboard/widgets.{$this->id}.{$key}", $replacements, $fallback);
        }
    }
?>