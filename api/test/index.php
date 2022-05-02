<?php
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<pre>
<?php 
    
    $cmd = "ping 127.0.0.1 -c 3";

    execute("ping 127.0.0.1 -c 3", $output);

    var_dump($output);

    // $descriptorspec = array(
    //     0 => array("pipe", "r"),   // stdin is a pipe that the child will read from
    //     1 => array("pipe", "w"),   // stdout is a pipe that the child will write to
    //     2 => array("pipe", "w")    // stderr is a pipe that the child will write to
    // );
    // $process = proc_open($cmd, $descriptorspec, $pipes);
    // echo "<pre>";
    // if (is_resource($process)) {
    //     while ($s = fgets($pipes[1])) {
    //         print date('H:i:s') . ' '. $s;
    //     }
    // }
    // echo "</pre>";
?>