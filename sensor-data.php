<?php
/* ========================================================================== */
/* SQLite tables                                                              */
/* ========================================================================== */
    define( 'NEWLINE', "\n");

/* ========================================================================== */
/* Open SQLite connection                                                     */
/* ========================================================================== */
    $db = new SQLite3( '/var/sqlite/sensor-data.sqlite' );

/* ========================================================================== */
/* Perform SQLite query with wait for unlocked state (default 6000ms)         */
/* ========================================================================== */
    function query_timeout( $query, $timeout = 6000 ) {
        global $db;

        if ( $db->busyTimeout( $timeout ) ) {
            $results = $db->query( $query );
        }

        $db->busyTimeout( 0 );

        return $results;
    }

/* ========================================================================== */
/* List sensor data                                                           */
/* ========================================================================== */
    function process_csv_log_data( $sensor, $period ) {
        $results = query_timeout( "select d.sensor_id
                                   ,      d.timestamp
                                   ,      d.value
                                   from   sensor_data d
                                   where  d.sensor_id = ${sensor}
                                   and    d.timestamp > datetime('now', '-${period} hours', 'localtime')
                                   order by d.timestamp" );

        header("Content-type: text/csv");
        echo 'timestamp,temperature'.NEWLINE;

        while ( $row = $results->fetchArray( SQLITE3_ASSOC ) ) {
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
/* Close SQLite connection                                                    */
/* ========================================================================== */
    $db->close();
?>