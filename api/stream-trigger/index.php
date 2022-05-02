<?php
	chdir(__DIR__);
	include_once("../../autoload.php");
?>
<?php
    if(!isset($_GET['id'])) {
        \thusPi\Response\error('Missing query parameter id.');
    }
    $id = $_GET['id'];

    if(!isset($_GET['name'])) {
        \thusPi\Response\error('Missing query parameter name.');
    }
    $name = $_GET['name'];

    if(!isset($_GET['parameters'])) {
        \thusPi\Response\error('Missing query parameter parameters.');
    }
    $parameters = $_GET['parameters'];


    $stream = new \thusPi\Streams\Stream($id);
?>