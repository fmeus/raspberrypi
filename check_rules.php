<?php
	/* check_rules.php */
	require_once( 'Rules.php' );
	require_once( 'pushover.php' );

	define( 'URL', 'http://littlegemsoftware.com:314/chart-sensor.html' );
	define( 'URL_TITLE', 'RPi Sensor Data' );

	/* Setup Rules */
	$rf = new Rules( 'localhost', 'rpi', 'rpi', 'sensordata' );

	/* Run rule - New Record */
	$rf->run_rule( 2 );
	if ( strlen( $rf->getOutput() ) > 0 ) {
		send_notification( $rf->getDescription(), $rf->getOutput(), URL, URL_TITLE );
	}

	/* Run rule - No Data Logged */
	$rf->run_rule( 3 );
	if ( strlen( $rf->getOutput() ) > 0 ) {
		send_notification( $rf->getDescription(), $rf->getOutput(), URL, URL_TITLE );
	}

	/* Run rule - Unhealthy Humidity */
	$rf->run_rule( 4 );
	if ( strlen( $rf->getOutput() ) > 0 ) {
		send_notification( $rf->getDescription(), $rf->getOutput(), URL, URL_TITLE );
	}
?>