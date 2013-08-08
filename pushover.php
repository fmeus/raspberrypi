<?php
/* pushover.php */

define( 'APP_TOKEN', getenv( 'PUSHOVER_APP_TOKEN' ) );
define( 'USER_KEY', getenv( 'PUSHOVER_USER_KEY' ) );

function send_notification( $msg ) {
	curl_setopt_array( $ch = curl_init()
	                 , array( CURLOPT_URL => "https://api.pushover.net/1/messages.json"
	                        , CURLOPT_RETURNTRANSFER => true
	                        , CURLOPT_POSTFIELDS => array( "token" => APP_TOKEN
	                                                     , "user" => USER_KEY
	                                                     , "title" => "RPi Environmental Data"
	                                                     , "message" => $msg
	                                                     , "url" => "http://littlegemsoftware.com:314/chart-sensor.html"
	                                                     , "url_title" => "RPi Sensor Data"
	                                                     )
	                        )
	                 );
	curl_exec( $ch );
	curl_close( $ch );
}
?>