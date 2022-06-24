<?php
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<?php 
    // Check if recording id is given
	if(!isset($_POST['id'])) {
		\thusPi\Response\error('request_field_missing', 'Field id is missing.');
	}

    $graph = new Goat1000\SVGGraph\SVGGraph(640, 480);

    $graph->colours(['red','green','blue']);
    $graph->values(100, 200, 150);
    $graph->links('/Tom/', '/Dick/', '/Harry/');
    $graph->render('BarGraph');
    
    exit();

    $recording_id = $_POST['id'];
    $_POST['curves'] = false;

    $svg_data = [
        'size'  => [],
        'lines' => []
    ];

    $recording = new \thusPi\Recordings\Recording($recording_id, true);

    $recording->setHistorySelection(
        $_POST['selection']['x0'] ?? null,
        $_POST['selection']['x1'] ?? null,
    );

    if(isset($_POST['selection'])) {
        echo(@$_POST['selection']['x1'] - @$_POST['selection']['x0']);
    }

    $rows             = $recording->getHistory($_POST['max_rows'] ?? 750);
    $manifest         = $recording->getProperties();

    // $rows = [
    //     ['x' => 1, 'y' => [9, 10]],
    //     ['x' => 2, 'y' => [12, 11]],
    //     ['x' => 3, 'y' => [15, 9]],
    //     ['x' => 4, 'y' => [13, 6]],
    //     ['x' => 5, 'y' => [9, 5]],
    //     ['x' => 6, 'y' => [6, 7]],
    //     ['x' => 7, 'y' => [8, 9]]
    // ];

    // $rows = [
    //     ['x' => 1, 'y' => [9]],
    //     ['x' => 2, 'y' => [12]],
    //     ['x' => 3, 'y' => [15]],
    //     ['x' => 4, 'y' => [13]],
    //     ['x' => 5, 'y' => [9]],
    //     ['x' => 6, 'y' => [6]],
    //     ['x' => 7, 'y' => [8]]
    // ];

    // Create SVG graph
    foreach ($rows as $i => $row) {
        foreach ($row as $column => $value) {
            if(!is_array($value)) {
                continue;
            }

            foreach ($value as $key => $value) {
                if(!isset($svg_data['lines'][$column.$key])) {
                    $svg_data['lines'][$column.$key] = '';
                }

                if(!isset($rows[$i-1]['x']) || !isset($rows[$i+1]['x']) ||
                   !isset($rows[$i-1]['y'][$key]) || !isset($rows[$i+1]['y'][$key])) {
                    continue;
                }

                if(isset($_POST['debug']) && $_POST['debug'] == 'true') {
                    var_dump($svg_data);
                }
                
                $x0 = $rows[$i-1]['x'];
                $x1 = $rows[$i]['x'];
                $y0 = $rows[$i-1]['y'][$key];
                $y1 = $rows[$i]['y'][$key];

                if($svg_data['size']['dif_x'] > 0) {
                    $d0_x = round(($x0 - $svg_data['size']['min_x'])/$svg_data['size']['dif_x']*100, 3);
                    $d1_x = round(($x1 - $svg_data['size']['min_x'])/$svg_data['size']['dif_x']*100, 3);
                } else {
                    $d0_x = $d1_x = 0;
                }

                if($svg_data['size']['dif_y'] > 0) {
                    $d0_y = round(100 - ($y0 - $svg_data['size']['min_y'])/$svg_data['size']['dif_y']*100, 3);
                    $d1_y = round(100 - ($y1 - $svg_data['size']['min_y'])/$svg_data['size']['dif_y']*100, 3);
                } else {
                    $d0_y = $d1_y = 0;
                }

                if(isset($_POST['curves']) && $_POST['curves'] == 'true') {
                    $x2 = $rows[$i+1]['x'];
                    $y2 = $rows[$i+1]['y'];

                    // $x2 -= abs($x1 - $x2);
                    // $y2 -= abs($y1 - $y2);

                    $d2_x = round($svg_data['size']['dif_x'] > 0 ? ($x2 - $svg_data['size']['min_x'])/$svg_data['size']['dif_x']*100 : 0, 3);
                    $d2_y = round($svg_data['size']['dif_y'] > 0 ? ($y2 - $svg_data['size']['min_y'])/$svg_data['size']['dif_y']*100 : 0, 3);
                    $svg_data['lines'][$column.$key] .= "C {$d0_x},{$d0_y} {$d1_x},{$d1_y} {$d2_x},{$d2_y} ";
                } else {
                    $svg_data['lines'][$column.$key] .= "L {$d0_x} {$d0_y}";
                }
            }
        }
    }

    $html =  '';
    foreach($svg_data['lines'] as $column => $d) {
        $html .= '<path class="graph-line" data-column="'.$column.'" d="M 0 100 '.$d.'L 100 100 Z" stroke-width="0.1" stroke="orange"></path>';
    };

    \thusPi\Response\success(null, [
        'manifest' => $manifest,
        'info'     => [
            'size' => $svg_data['size']
        ],
        'svg'     => '<svg width="100%" height="100%" viewBox="0 0 100 100" preserveAspectRatio="none">'.$html.'</svg>'
    ]);
?>