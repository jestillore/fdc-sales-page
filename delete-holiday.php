<?php

require_once('./loader.php');

global $client;

$lockout_days_arr = array();
$lockout_days = get_option( 'orddd_lite_lockout_days' );
if ( $lockout_days != '' && $lockout_days != '{}' && $lockout_days != '[]' ) {
    $lockout_days_arr = json_decode( get_option( 'orddd_lite_lockout_days' ) );
}

$lockout_dates = [];

$ddate = $_GET['date'];

foreach ( $lockout_days_arr as $k => $v ) {
    if ( $v->o >= get_option( 'orddd_lite_lockout_date_after_orders' ) ) {
    	if ($v->d !== $ddate)
	    	$lockout_dates[] = [
	    		'o' => $v->o,
	    		'd' => $v->d
	    		];
    }
}

update_option('orddd_lite_lockout_days', json_encode($lockout_dates));
header('Location: holidays.php');
