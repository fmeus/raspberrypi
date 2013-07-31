<?php
/* ========================================================================== */
/* Globals                                                                    */
/* ========================================================================== */
    define( 'NEWLINE', "\n");

/* ========================================================================== */
/* Open MySQL connection                                                      */
/* ========================================================================== */
    $db = new mysqli( 'localhost', 'rpi', 'rpi', 'sensordata' );

/* ========================================================================== */
/* List sensor data                                                           */
/* ========================================================================== */
    function process_csv_log_data( $sensor, $period ) {
        global $db;

        $results = $db->query( "select d.timestamp
                                ,      d.value
                                from   sensor_data d
                                where  d.sensor_id = ${sensor}
                                and    timestampdiff(HOUR,now(),d.timestamp) <= ${period}
                                order by d.timestamp" );

        header("Content-type: text/csv");
        echo 'timestamp,temperature'.NEWLINE;

        while ( $row = mysqli_fetch_assoc( $results ) ) {
            printf('%s,%s'.NEWLINE, $row['timestamp'], $row['value']);
       }
    }

/* ========================================================================== */
/* Main process                                                               */
/* ========================================================================== */
    $command = isset( $_GET['action'] )? $_GET['action'] : 'UNKNOWN_COMMAND';

    switch( $command )
    {
        case 'csv_data';
            $id = (int)$_GET['id'];
            $period = (int)$_GET['period'];
            process_csv_log_data( $id, $period );
        break;
    }

/* ========================================================================== */
/* Close MySQL connection                                                     */
/* ========================================================================== */
    $db->close();
?>