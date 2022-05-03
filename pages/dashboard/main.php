<div class="dashboard-widgets">
    <?php 
        $widgets = \thusPi\Extensions\list_all_features('dashboard/widgets');

        foreach ($widgets as $widget) {
            $widget            = new \thusPi\Widgets\Widget($widget['extension_id'], $widget['feature']['id']);
            $widget_html       = $widget->getHTML();
            $widget_css        = $widget->getCSS();
            $widget_js         = $widget->getJS();
            $widget_config     = $widget->getConfig() ?? [];
            $widget_title      = $widget->translate('title');
            $widget_config_str = '';
            
            // Transform config into attribute string
            foreach ($widget_config as $key => $value) {
                if(!is_string($value) && !is_numeric($value)) {
                    continue;
                }

                // Encode key and value for attribute
                $key   = htmlspecialchars($key);
                $value = htmlspecialchars($value);

                $widget_config_str .= "data-config-{$key}=\"{$value}\" ";
            }
            $widget_config_str = rtrim($widget_config_str);

            echo("
                <div class=\"dashboard-widget p-1 transition-fade-order\" data-widget-id=\"{$widget->getProperty('extension_id')}_{$widget->getProperty('id')}\" {$widget_config_str}>
                    <div class=\"tile\">
                        <h3 class=\"tile-title\">{$widget_title}</h3>
                        <div class=\"dashboard-widget-content\">
                            {$widget_html}
                        </div>
                    </div>
                    <style>{$widget_css}</style>
                    <script>{$widget_js}</script>
                </div>
            ");
        }
    ?>
</div>