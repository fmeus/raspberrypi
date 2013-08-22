<?php
	/* check_rules.php */
	require_once( 'Rules.php' );
	require_once( 'pushover.php' );

	define( 'URL', 'http://littlegemsoftware.com:314/chart-sensor.html' );
	define( 'URL_TITLE', 'RPi Sensor Data' );
	define( 'RULES_TO_RUN', serialize( array( 2, 3, 4 ) ) );

	/* Setup Rules */
	$rf = new Rules( 'localhost', 'rpi', 'rpi', 'sensordata' );

	/* Loop through rules */
	foreach( unserialize( RULES_TO_RUN ) as $ruleid ) {
		$rf->run_rule( $ruleid );
		if ( strlen( $rf->getOutput() ) > 0 ) {
			send_notification( $rf->getDescription(), $rf->getOutput(), URL, URL_TITLE );
		}
	}
?>