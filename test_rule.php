<?php
require_once( 'Rules.php' );
require_once( 'pushover.php' );


$rf = new Rules( 'localhost', 'rpi', 'rpi', 'sensordata' );
$rf->run_rule( 1 );

// echo $rf->getOutput();
send_notification( $rf->getDescription(), $rf->getOutput(), null, null );
?>
