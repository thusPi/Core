<div class="dashboard-widgets">
    <?php 
        $widgets = \thusPi\Extensions\list_all_features('dashboard/widgets');

        foreach ($widgets as $widget) {
            $widget       = new \thusPi\Widgets\Widget($widget['extension_id'], $widget['feature']['id']);
            $widget_html  = $widget->getHTML();
            $widget_css   = $widget->getCSS();
            $widget_js    = $widget->getJS();
            $widget_title = $widget->translate('title');

            echo("
                <div class=\"dashboard-widget p-1 transition-fade-order\" data-widget-id=\"{$widget->getProperty('extension_id')}_{$widget->getProperty('id')}\">
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