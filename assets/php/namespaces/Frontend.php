<?php 
    namespace thusPi\Frontend;

    function print_scripts() {
        $html = '';

        // Print variables
        $html .= '
            <script file="none">
                var thusPi = {};
                function thusPiAssign(e,i){(e=e.split(".")).reduce((n,o,t)=>void 0===n[o]&&t!==e.length-1?(n[o]={},n[o]):t===e.length-1?(n[o]=i,i):n[o],thusPi)}
                thusPiAssign(\'data.config\', '.json_encode(CONFIG, JSON_UNESCAPED_SLASHES).');
                thusPiAssign(\'data.locale.translations\', '.json_encode(TRANSLATIONS, JSON_UNESCAPED_SLASHES).');
                thusPiAssign(\'data.users.currentUser\', '.json_encode(\thusPi\Users\CurrentUser::getProperties(), JSON_UNESCAPED_SLASHES).');
                thusPiAssign(\'data.webroot\', \''.\thusPi\Config\get('env_webroot').'\');
            </script>
        ';

        // Find scripts
        $scripts = glob(DIR_ASSETS.'/js/*/*.js');
        foreach ($scripts as $script) {
            if(strpos($script, '.min.') !== false) {
                continue;
            }

            $html .= '<script defer file="'.basename($script).'" type="text/javascript">'.file_get_contents($script).'</script>'."\n";
        }

        echo($html);

        return true;
    }

    function print_element($element_name) {
		$element['html'] = $element['php'] = $element['js'] = $element['css'] = '';

		$element['php'] = DIR_ASSETS."/php/elements/{$element_name}/element.php";
		$element['js']  = DIR_ASSETS."/php/elements/{$element_name}/element.js";
		$element['css'] = DIR_ASSETS."/php/elements/{$element_name}/element.css";
		if(!file_exists($element['php'])) {
            return false;
        }

        $php = include_once($element['php']);
        $element['html'] .= $php;

        if(file_exists($element['js'])) {
            $js = file_get_contents($element['js']);
            $element['html'] .= "<script>{$js}</script>";
        }

        if(file_exists($element['css'])) {
            $css = file_get_contents($element['css']);
            $element['html'] .= "<style>{$css}</style>";
        }
		
        echo $element['html'];

        return true;
	}

    function print_stylesheets() {
        $html = '';
        
        // Find stylesheets
        $stylesheets = glob(DIR_ASSETS.'/css/*/*.css');
        foreach ($stylesheets as $stylesheet) {
            if(strpos($stylesheet, '.min.') !== false) {
                continue;
            }

            if(basename(dirname($stylesheet)) == '_themes') {
                continue;
            }

            $html .= '<style file="'.basename($stylesheet).'" type="text/css">'.file_get_contents($stylesheet).'</style>'."\n";
        }

        echo($html);

        return true;
    }

    function print_category_css() {
        $categories = \thusPi\Categories\get_all();

        foreach ($categories as $namespace => $category) {
            echo("<style>
                [data-category=\"{$namespace}\"] .header-bg-category::before {
                    background: linear-gradient(0deg, var(--secondary) 60%, var(--{$category['color']}) 40%) !important;
                }

                [data-category=\"{$namespace}\"] .text-category {
                    color: var(--{$category['color']}) !important;
                }

                [data-category=\"{$namespace}\"] .bg-category {
                    background: var(--{$category['color']}) !important;
                }
            </style>");
        }
    }
    
    function print_theme() {
        $html = '';

        // Get theme preferred by user or fall back on the config theme
        if(!($theme = \thusPi\Users\CurrentUser::getSetting('theme') ?? \thusPi\Config\get('theme'))) {
            $theme = \thusPi\Config\get('theme');
        }

        if(!file_exists(DIR_ASSETS."/css/_themes/{$theme}.css")) {
            $theme = \thusPi\Config\get('theme');
        }

        if(!file_exists(DIR_ASSETS."/css/_themes/{$theme}.css")) {
            return false;
        }

        // Load theme file contents
        if(!$css = file_get_contents(DIR_ASSETS."/css/_themes/{$theme}.css")) {
            return false;
        }

        $html .= "<style>{$css}</style>";

        echo($html);

        return true;
    }

    class DOMnode {
        protected $node = 'div';
        protected $classes = [];
        protected $styles = [];
        protected $attributes = [];
        protected $content = '';

        public function __construct($node = 'div') {
            $this->node = $node;
        }

        public function setAttribute($key, $value) {
            $this->attributes[$key] = $value;
            return $this;
        }

        public function addClass($class) {
            if(!in_array($class, $this->classes)) {
                $this->classes[] = $class;
            }
            return $this;
        }

        public function setStyle($key, $value) {
            $this->styles[$key] = $value;
            return $this;
        }

        public function setContent($content) {
            $this->content = $content;
            return $this;
        }

        public function getAttributesHTML() {
            $html = '';
            if(count($this->classes) > 0) {
                $html .= 'class="'.implode(' ', $this->classes).'" ';
            }

            foreach ($this->attributes as $key => $value) {
                $html .= "{$key}=\"{$value}\" ";
            }

            if(count($this->styles) > 0) {
                $html .= 'style="';
                foreach ($this->styles as $key => $value) {
                    $html .= "{$key}=\"{$value}\" ";
                }
                $html .= '" ';
            }

            return $html;
        }

        public function getOuterHTML() {
            $outerHTML = "<{$this->node} " . $this->getAttributesHTML() . ">{$this->content}</{$this->node}>";
            return $outerHTML;
        }

        public function print() {
            echo($this->getOuterHTML());
            return $this;
        }
    }

    class inputSearch extends DOMnode {
        protected $value = '';
        protected $results = [];

        public function setValue($value, $shown_value = null) {
            $shown_value = $shown_value ?? $value;
            $this->value = $value;
            $this->setAttribute('value', $shown_value);
            return $this;
        }

        public function setContent($content) {
            $this->setValue($content);
            return $this;
        }

        public function setPlaceholder($placeholder) {
            $this->attributes['placeholder'] = $placeholder;
            return $this;
        }

        public function addResults(array $results) {
            foreach ($results as $result) {
                if(!is_array($result)) {
                    continue;
                }

                if(!isset($result[0]) || count($result) > 3) {
                    $this->addResult($result);
                } else {
                    $this->addResult($result[0] ?? null, $result[1] ?? null, $result[2] ?? null);
                }
            }
            return $this;
        }

        public function addResult($value, $shown_value = null, $match = null, $description = '', $thumbnail = '') {
            if(is_array($value)) { // If arguments are passed as array
                $args = $value;
                if(isset($args[0])) {
                    // Make array keys string if they are numeric
                    $args = array_combine(['value', 'shown_value', 'match', 'description', 'thumbnail'], array_pad($args, 5, null));
                }

                $args['shown_value'] = $args['shown_value'] ?? $args['value'];
                $args['match']       = $args['match'] ?? $args['shown_value'];

                $this->results[] = array_replace([
                    'value'       => '',
                    'shown_value' => '',
                    'match'       => '',
                    'description' => null,
                    'thumbnail'   => null
                ], $args);
            } else {
                $shown_value = $shown_value ?? $value;
                $match       = $match ?? $shown_value;

                $this->results[] = [
                    'value'       => $value, 
                    'shown_value' => $shown_value, 
                    'match'       => $match
                ];
            }

            return $this;
        }

        public function getResultsHTML() {
            $html = '';

            foreach ($this->results as $result) {
                if(isset($result['thumbnail']) || isset($result['description'])) {
                    $html .= "<li class=\"input-search-result btn btn-sm btn-secondary input-search-result-rich".($result['value'] == $this->value ? ' active' : '')."\" value=\"{$result['value']}\" data-shown-value=\"{$result['shown_value']}\" data-search-match=\"{$result['match']}\" tabindex=\"0\"><h5 class=\"input-search-result-title\">{$result['shown_value']}</h5><span class=\"input-search-result-description\">{$result['description']}</span><div class=\"input-search-result-thumbnail\"><img src=\"{$result['thumbnail']}\"></div></li>";
                } else {
                    $html .= "<li class=\"input-search-result btn btn-sm btn-secondary".($result['value'] == $this->value ? ' active' : '')."\" value=\"{$result['value']}\" data-shown-value=\"{$result['shown_value']}\" data-search-match=\"{$result['match']}\" tabindex=\"0\">{$result['shown_value']}</li>";
                }
            }

            return $html;
        }

        public function print() {
            if(!isset($this->attributes['placeholder'])) {
                $this->setPlaceholder(\thusPi\Locale\translate('generic.action.select'));
            }

            $this->addClass('input-search');

            $outerHTML = "
                <div class=\"input-wrapper input-wrapper-search\" data-value=\"{$this->value}\">
                    <input type=\"text\" {$this->getAttributesHTML()}>
                    <ul class=\"input-search-results\">
                        {$this->getResultsHTML()}
                        <li class=\"input-search-result btn btn-sm input-search-result-no-results\">".\thusPi\Locale\Translate('generic.error.search_no_results')."</li>
                    </ul>
                </div>
            ";

            echo($outerHTML);
        }
    }
?>