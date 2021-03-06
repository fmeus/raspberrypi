-- Create database: sensordata
create database sensordata;

-- Create table: sensor_types
create table sensor_types ( 
  type_id       int not null auto_increment
, type_name     char(200) not null
, primary key (type_id)
);
create index type_idx on sensor_types(type_id);

-- Create table: location
create table locations(
  location_id    int not null auto_increment
, location_name  char(200) not null
, primary key (location_id)
);
create index location_idx on locations(location_id);

-- Create table: sensors
create table sensors( 
  sensor_id     int not null auto_increment
, type_id       integer not null
, location_id   integer not null
, sensor_name   char(200) not null
, primary key (sensor_id)
, constraint foreign key (type_id) references sensor_types(type_id)
, constraint foreign key (location_id) references locations(location_id)
);
create index sensor_idx on sensors(sensor_id);
create index sensor_type_idx on sensors(type_id);
create index sensor_location_idx on sensors(location_id);

-- Create table: sensor_data
create table sensor_data( 
  id            int not null auto_increment
, timestamp     timestamp not null default current_timestamp
, sensor_id     integer not null
, value         real
, primary key (id)
, constraint foreign key (sensor_id) references sensors(sensor_id)
);
create index sd_timestamp_idx on sensor_data(timestamp);
create index sd_sensorid_idx on sensor_data(sensor_id);

alter table sensor_data add column valid char(1) not null default 'Y' after value;
create index sd_valid_idx on sensor_data(valid);

create table bad_sensor_data select * from sensor_data where 1<1;

-- Create table: sensor_high_low
create table sensor_high_low(
  sensor_id integer not null
, min_value real
, max_value real
, primary key (sensor_id)
, constraint foreign key (sensor_id) references sensors(sensor_id)
);

-- Add locations
insert into locations(location_name) values('Bedroom');
insert into locations(location_name) values('Office');
insert into locations(location_name) values('Guestroom');
insert into locations(location_name) values('Hall');
insert into locations(location_name) values('Kitchen/Livingroom');
insert into locations(location_name) values('Storageroom');

-- Add sensor types
insert into sensor_types(type_name) values('Temperature');
insert into sensor_types(type_name) values('Humidity');

-- Add sensors
insert into sensors(type_id,sensor_name,location_id) values(1,'DS18B20',2);
insert into sensors(type_id,sensor_name,location_id) values(1,'DHT22 - Temperature',2);
insert into sensors(type_id,sensor_name,location_id) values(2,'DHT22 - Humidity',2);
insert into sensors(type_id,sensor_name,location_id) values(1,'RPi CPU',2);
insert into sensors(type_id,sensor_name,location_id) values(1,'RPi GPU',2);

-- Create user
create user 'rpi'@'localhost' identified by 'rpi';
create user 'rpi'@'amun.local' identified by 'rpi';

-- Grant privileges
grant all on sensordata.* to 'rpi'@'localhost';
grant all on sensordata.* to 'rpi'@'amun.local';

-- Create table: rules
create table rules(
  rule_id         int not null auto_increment comment 'Internal ID'
, rule_desc       char(200) not null comment 'Description of the rule'
, rule_query      text not null comment 'Query to be used for the rule'
, rule_message    char(200) not null comment 'Format string to be used when rule is triggered (using pfrint format)'
, rule_active     char(1) not null default 'N' comment 'Should the rule be checked (Y/N)'
, rule_preproc    text comment 'Process to perform before checking rule (optional, results not checked)'
, rule_postproc   text comment 'Process to perform after checking rule (optional, results not checked)'
, rule_last_used  timestamp comment 'When as the rule last triggered'
, primary key (rule_id)
);
create index rule_idx on rules(rule_id);
alter table rules add column rule_shellcmd text comment 'Shell command to be run (optional)' after rule_postproc;
alter table rules add column rule_run_shell enum('never','always','results') default 'never' comment 'When should shell command be run (always or on resuts)' after rule_shellcmd;
