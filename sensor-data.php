<?php
/* ============================================================================================== */
/* SQLite tables                                                                                  */
/* ============================================================================================== */
    define( 'NEWLINE', "\n");


/* ============================================================================================== */
/* Open SQLite connection                                                                         */
/* ============================================================================================== */
    $db = new SQLite3( 'sensor-data.sqlite' );


/* ============================================================================================== */
/* Prepared SQLite statements                                                                     */
/* ============================================================================================== */
    $stmt_sensor_data = new StdClass();
    $stmt_sensors = new StdClass();

    function process_prepare_statements() {
        global $db, $stmt_sensor_data, $stmt_sensors;

        if ( $db->busyTimeout( 2000 ) ) {
            $stmt_sensors = $db->prepare( 'insert into sensors( sensor_type
                                                              , sensor_name
                                                              , sensor_location )
                                                        values( :sensor_type
                                                              , :sensor_name
                                                              , :sensor_location )' );

            $stmt_sensor_data = $db->prepare( 'insert into sensor_data( timestamp
                                                                      , sensor_id
                                                                      , value )
                                                                values( datetime( \'now\', \'localtime\' )
                                                                      , :sensor_id
                                                                      , :value )' );
        }
        $db->busyTimeout( 0 );
    }


/* ============================================================================================== */
/* Execute SQLite statement with wait for unlocked state (default 10.000 ms                       */
/* ============================================================================================== */
    function execute_timeout( $statement, $timeout = 10000 ) {
        global $db;

        if ( $db->busyTimeout( $timeout ) ) {
            $statement->execute();
        }

        $db->busyTimeout( 0 );
    }


/* ============================================================================================== */
/* Perform SQLite query with wait for unlocked state (default 2000ms)                             */
/* ============================================================================================== */
    function query_timeout( $query, $timeout = 6000 ) {
        global $db;

        if ( $db->busyTimeout( $timeout ) ) {
            $results = $db->query( $query );
        }

        $db->busyTimeout( 0 );

        return $results;
    }


/* ============================================================================================== */
/* Log sensor data                                                                                */
/* ============================================================================================== */
    function process_log_data( $id, $value ) {
        global $db, $stmt_sensor_data;

        $stmt_sensor_data->bindValue( ':sensor_id', $id, SQLITE3_INTEGER );
        $stmt_sensor_data->bindValue( ':value', $value, SQLITE3_FLOAT );
        execute_timeout( $stmt_sensor_data );
    }


/* ============================================================================================== */
/* List sensor data                                                                               */
/* ============================================================================================== */
    function process_csv_log_data( $sensor, $period ) {
        $results = query_timeout( "select s.sensor_id
                                   ,      s.sensor_type
                                   ,      s.sensor_name
                                   ,      s.sensor_location
                                   ,      d.timestamp
                                   ,      d.value
                                   from   sensors s
                                   ,      sensor_data d
                                   where  s.sensor_id = ${sensor}
                                   and    s.sensor_id = d.sensor_id
                                   and    d.timestamp > datetime('now', '-${period} hours', 'localtime')
                                   order by d.timestamp" );
        header("Content-type: text/csv");
        echo 'timestamp,temperature'.NEWLINE;

        while ( $row = $results->fetchArray( SQLITE3_ASSOC ) ) {
            echo $row['timestamp'];
            echo ',';
            echo $row['value'].NEWLINE;
       }
    }


/* ============================================================================================== */
/* Main process                                                                                   */
/* ============================================================================================== */
    $command = isset( $_GET['action'] )? $_GET['action'] : 'UNKNOWN_COMMAND';

    switch( $command )
    {
        case 'log_data';
            process_prepare_statements();
            process_log_data( (int)$_GET['id'], (float)$_GET['value'] );
        break;

        case 'csv_data';
            $id = (int)$_GET['id'];
            $period = (int)$_GET['period'];
            process_csv_log_data( $id, $period );
        break;
    }


/* ============================================================================================== */
/* Close SQLite connection                                                                        */
/* ============================================================================================== */
    $db->close();
?>
