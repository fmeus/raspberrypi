<?php
/* pushover.php */

define( 'APP_TOKEN', getenv( 'PUSHOVER_APP_TOKEN' ) );
define( 'USER_KEY', getenv( 'PUSHOVER_USER_KEY' ) );

function send_notification( $title, $msg, $url, $url_title ) {
	curl_setopt_array( $ch = curl_init()
	                 , array( CURLOPT_URL => "https://api.pushover.net/1/messages.json"
	                        , CURLOPT_RETURNTRANSFER => true
	                        , CURLOPT_POSTFIELDS => array( "token" => APP_TOKEN
	                                                     , "user" => USER_KEY
	                                                     , "title" => $title
	                                                     , "message" => $msg
	                                                     , "url" => $url
	                                                     , "url_title" => $url_title
	                                                     )
	                        )
	                 );
	curl_exec( $ch );
	curl_close( $ch );
}
?>
