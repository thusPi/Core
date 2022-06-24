<?php 
    $pass = false;

    include_class('Analytic');

    $analytic = new Analytic($parameters['analytic_id']);

    $latest_values = $analytic->get_latest_values();

    if(!isset($latest_values[$parameters['axis']])) {
        return false;
    }

    $axis_value    = $latest_values[$parameters['axis']];
    $compare_value = $parameters['value'];

    if(!is_numeric($axis_value) || !is_numeric($compare_value)) {
        return false;
    }

    switch ($parameters['compare']) {
        case 'higher':
            $pass = ($axis_value > $compare_value ? true : false);
            break;

        case 'higher_or_equal':
            $pass = ($axis_value >= $compare_value ? true : false);
            break;

        case 'equal':
            $pass = ($axis_value == $compare_value ? true : false);
            break;

        case 'lower_or_equal':
            $pass = ($axis_value <= $compare_value ? true : false);
            break;

        case 'lower':
            $pass = ($axis_value < $compare_value ? true : false);
            break;
    }

    echo($pass);
    return;
?>