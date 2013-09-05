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
                                and d.timestamp >= date_sub(NOW(), interval ${period} hour)
                                order by d.timestamp" );

        header("Content-type: text/csv");
        echo 'timestamp,value'.NEWLINE;

        while ( $row = mysqli_fetch_assoc( $results ) ) {
            printf('%s,%s'.NEWLINE, $row['timestamp'], $row['value']);
       }
    }


/* ========================================================================== */
/* List data for 'today'                                                      */
/* ========================================================================== */
    function process_today_data( $sensor ) {
        global $db;

        $results = $db->query( "select s.sensor_id
                                ,      s.sensor_name
                                ,      round( min(d.value), 2 ) as min
                                ,      round( max(d.value), 2 ) as max
                                ,      round( avg(d.value), 2 ) as avg
                                from   sensor_data d
                                ,      sensors     s
                                where  s.sensor_id = ${sensor}
                                and    s.sensor_id = d.sensor_id
                                and    date(d.timestamp) = curdate()
                                group by s.sensor_id" );

        return mysqli_fetch_assoc( $results );
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

        case 'tweet_data';
            $row = process_today_data( 1 );
            $msg1 = sprintf('DS18B20'.NEWLINE.'Min=%s°C, Max=%s°C, Avg=%s°C'.NEWLINE, $row['min'], $row['max'], $row['avg']);

            $row = process_today_data( 2 );
            $msg2 = sprintf('DHT22'.NEWLINE.'Min=%s°C, Max=%s°C, Avg=%s°C'.NEWLINE, $row['min'], $row['max'], $row['avg']);

            $row = process_today_data( 3 );
            $msg3 = sprintf('DHT22'.NEWLINE.'Min=%s%%, Max=%s%%, Avg=%s%%'.NEWLINE, $row['min'], $row['max'], $row['avg']);

            exec( 'python /home/pi/raspberrypi/tweet.py "d drexore '.$msg1.NEWLINE.$msg2.NEWLINE.$msg3.'"' );
        break;
    }

/* ========================================================================== */
/* Close MySQL connection                                                     */
/* ========================================================================== */
    $db->close();
?>