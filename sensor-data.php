<?php
/* ============================================================================================== */
/* SQLite tables                                                                                  */
/* ============================================================================================== */
    define( 'NEWLINE', "\n");

/* ============================================================================================== */
/* SQLite tables                                                                                  */
/* ============================================================================================== */
    $tables = array( 'sensor_data'
                   , 'sensors' 
                   );


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
/* Add sensor                                                                                     */
/* ============================================================================================== */
    function process_add_sensor( $type, $name, $location ) {
        global $db, $stmt_sensors;

        $stmt_sensors->bindValue( ':sensor_type', $type, SQLITE3_TEXT );
        $stmt_sensors->bindValue( ':sensor_name', $name, SQLITE3_TEXT );
        $stmt_sensors->bindValue( ':sensor_location', $location, SQLITE3_TEXT );
        execute_timeout( $stmt_sensors );
    }

/* ============================================================================================== */
/* List sensors                                                                                   */
/* ============================================================================================== */
    function process_list_sensors() {
        $results = query_timeout( "select sensor_id, sensor_type, sensor_name, sensor_location from sensors" );

        echo str_pad( 'ID', 5, ' ', STR_PAD_LEFT) ;
        echo ' ';
        echo str_pad( 'TYPE', 12, ' ', STR_PAD_RIGHT) ;
        echo ' ';
        echo str_pad( 'NAME', 25, ' ', STR_PAD_RIGHT) ;
        echo ' ';
        echo str_pad( 'LOCATION', 25, ' ', STR_PAD_RIGHT).NEWLINE;
        echo ' ';
        echo str_pad( '', 67, '-', STR_PAD_LEFT).NEWLINE;

        while ( $row = $results->fetchArray( SQLITE3_ASSOC ) ) {
            echo str_pad( $row['sensor_id'], 5, ' ', STR_PAD_LEFT);
            echo ' ';
            echo str_pad( $row['sensor_type'], 12, ' ', STR_PAD_RIGHT);
            echo ' ';
            echo str_pad( $row['sensor_name'], 25, ' ', STR_PAD_RIGHT);
            echo ' ';
            echo str_pad( $row['sensor_location'], 25, ' ', STR_PAD_RIGHT).NEWLINE;
        }
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
    function process_list_log_data() {
        $results = query_timeout( "select s.sensor_id, s.sensor_type, s.sensor_name, s.sensor_location, d.timestamp, d.value from sensors s, sensor_data d where s.sensor_id = d.sensor_id order by d.timestamp" );

        echo str_pad( 'ID', 5, ' ', STR_PAD_LEFT) ;
        echo ' ';
        echo str_pad( 'TYPE', 12, ' ', STR_PAD_RIGHT) ;
        echo ' ';
        echo str_pad( 'NAME', 25, ' ', STR_PAD_RIGHT) ;
        echo ' ';
        echo str_pad( 'LOCATION', 25, ' ', STR_PAD_RIGHT);
        echo ' ';
        echo str_pad( 'TIMESTMAP', 20, ' ', STR_PAD_RIGHT);
        echo ' ';
        echo str_pad( 'VALUE', 10, ' ', STR_PAD_RIGHT).NEWLINE;
        echo ' ';
        echo str_pad( '', 100, '-', STR_PAD_LEFT).NEWLINE;

        while ( $row = $results->fetchArray( SQLITE3_ASSOC ) ) {
            echo str_pad( $row['sensor_id'], 5, ' ', STR_PAD_LEFT);
            echo ' ';
            echo str_pad( $row['sensor_type'], 12, ' ', STR_PAD_RIGHT);
            echo ' ';
            echo str_pad( $row['sensor_name'], 25, ' ', STR_PAD_RIGHT);
            echo ' ';
            echo str_pad( $row['sensor_location'], 25, ' ', STR_PAD_RIGHT);
            echo ' ';
            echo str_pad( $row['timestamp'], 20, ' ', STR_PAD_RIGHT);
            echo ' ';
            echo str_pad( $row['value'], 10, ' ', STR_PAD_RIGHT).NEWLINE;
        }
    }

    function process_csv_log_data( $sensor, $period ) {
        $results = query_timeout( "select s.sensor_id, s.sensor_type, s.sensor_name, s.sensor_location, d.timestamp, d.value from sensors s, sensor_data d where s.sensor_id = ${sensor} and s.sensor_id = d.sensor_id and d.timestamp > datetime('now', '-${period} hours', 'localtime') order by d.timestamp" );
        header("Content-type: text/csv");
        echo 'timestamp,temperature'.NEWLINE;

        while ( $row = $results->fetchArray( SQLITE3_ASSOC ) ) {
            echo $row['timestamp'];
            echo ',';
            echo $row['value'].NEWLINE;
       }
    }


/* ============================================================================================== */
/* Create SQLite tables                                                                           */
/* ============================================================================================== */
    function process_create_tables() {
        global $db;

        $db->exec( 'create table sensors( sensor_id        integer primary key autoincrement
                                        , sensor_type      text check( sensor_type in (\'TEMPERATURE\',\'HUMIDITY\',\'MOTION\',\'LIGHT\') ) not null default \'\'
                                        , sensor_name      text not null
                                        , sensor_location  text not null )' );

        $db->exec( 'create table sensor_data( id           integer primary key autoincrement
                                            , timestamp    datetime default current_timestamp
                                            , sensor_id    integer not null
                                            , value        real )' );
    }


/* ============================================================================================== */
/* Drop SQLite tables                                                                             */
/* ============================================================================================== */
    function process_drop_tables() {
        global $db, $tables;

        foreach ( $tables as $table ) {
            $db->exec( "drop table $table" );
        }
    }


/* ============================================================================================== */
/* Empty SQLite tables                                                                            */
/* ============================================================================================== */
    function process_empty_tables() {
        global $db, $tables;

        foreach ( $tables as $table ) {
            $db->exec( "delete from $table" );
        }
    }


/* ============================================================================================== */
/* Main process                                                                                   */
/* ============================================================================================== */
    $command = isset( $argv[1] )? $argv[1] : $_GET['action'];

    switch( $command )
    {
        case 'add_sensor';
            process_prepare_statements();
            // process_add_sensor( 'TEMPERATURE', 'DS18B20', 'Office' );
            // process_add_sensor( 'TEMPERATURE', 'DHT22', 'Office' );
            // process_add_sensor( 'HUMIDITY', 'DHT22', 'Office' );
        break;

        case 'list_sensors';
            process_list_sensors();
        break;

        case 'log_data';
            process_prepare_statements();
            process_log_data( (int)$_GET['id'], (float)$_GET['value'] );
        break;

        case 'list_data';
            process_list_log_data();
        break;

        case 'csv_data';
            $id = (int)$_GET['id'];
            $period = (int)$_GET['period'];
            process_csv_log_data( $id, $period );
        break;

        case 'drop';
            process_drop_tables();
        break;

        case 'create';
            process_create_tables();
        break;

        case 'empty';
            process_empty_tables();
        break;

        case 'vacuum';
            $db->exec( 'vacuum' );
        break;

        case 'cleanup';
            process_cleanup();
        break;

        case 'test';
            $results = query_timeout( "select s.*, datetime('now'), datetime('now', '-2 hours', 'localtime') from sensors s" );
            while ( $row = $results->fetchArray( SQLITE3_ASSOC ) ) {
                echo print_r( $row, true );
            }
        break;

        default;
            echo "No command or invalid command was specified. Known commands are;\n";
            echo "   drop    - Drop SQLite tables\n";
            echo "   create  - Create SQLite tables\n";
            echo "   empty   - Empty SQLite tables\n";
            echo "   vacuum  - Rebuild entire database\n";
        break;
    }




/* ============================================================================================== */
/* Close SQLite connection                                                                        */
/* ============================================================================================== */
    $db->close();
?>
