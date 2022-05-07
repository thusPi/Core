<?php
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<?php 
    // Check if recording id is given
	if(!isset($_POST['id'])) {
		\thusPi\Response\error('request_field_missing', 'Field id is missing.');
	}

    $recording_id  = $_POST['id'];
    $interval      = $_POST['interval'] ?? null;
    $recording     = new \thusPi\Recordings\Analytic($recording_id, true);

    $rows = $recording->getHistory([
        'max_rows' => $_POST['max_rows'] ?? 750, 
        'interval' => $interval,
        'x_start'  => $_POST['x_start'] ?? null,
        'x_end'    => $_POST['x_end'] ?? null
    ]);

    $timezone = \thusPi\Users\CurrentUser::getSetting('timezone') ?? 'UTC';

    foreach ($rows as &$row) {
        if(!isset($row['x']) || !isset($row['y'])) {
            unset($row);
        }

        if(isset($row['x_end'])) {
            switch($interval) {
                case 'hour':
                    $period_start = $row['x'] - ($row['x'] % 3600); /* Start of the hour */
                    $period_end   = $period_start + 3540; /* 59 minutes later */
                    break;
                case 'day':
                    $period_start = $row['x'] - ($row['x'] % 86400); /* Start of the day */
                    $period_end   = $period_start + 86340; /* 23 hours and 59 minutes later */
                    break;
                case 'week':
                    $period_start = strtotime('this week', $row['x']); /* Start of the week */
                    $period_end   = $period_start + 518400; /* 6 days later */
                    break; 
                case 'month':
                    $period_start = strtotime('first day of this month', $row['x']); /* Start of the month */
                    $period_end   = $period_start + (date('t', $row['x']) - 1) * 86400; /* Days-in-month-minus-1 days later */
                    break; 
                default:
                    $period_start = 0;
                    $period_end = 0;
            }

            
            $row['x_formatted'] = \thusPi\Locale\date_format_period($period_start, $period_end, $timezone, 'short');
        } else {
            $row['x_formatted'] = \thusPi\Locale\date_format('full,full', $row['x']);
        }
    }

    \thusPi\Response\success(null, [ 
        'manifest'  => $recording->getProperties(),
        'rows'      => $rows,
        'row_count' => count($rows)
    ]);
?>