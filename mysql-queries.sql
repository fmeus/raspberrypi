/* Min/Max/Avg alltime */
select s.sensor_id
,      s.sensor_name
,      min(d.value) as min
,      max(d.value) as max
,      avg(d.value) as avg
from   sensor_data d
,      sensors     s
where  s.sensor_id = d.sensor_id
group by s.sensor_id;

/* Min/Max/Avg today */
select s.sensor_id
,      s.sensor_name
,      min(d.value) as min
,      max(d.value) as max
,      avg(d.value) as avg
from   sensor_data d
,      sensors     s
where  s.sensor_id = d.sensor_id
and    date(d.timestamp) = curdate()
group by s.sensor_id;


/* 'Current' data */
select s.sensor_id
,      s.sensor_name
,      d.value
,      date(d.timestamp)
from   sensor_data d
,      sensors     s
,      (select max(id) as id from sensor_data group by sensor_id) m
where  d.id = m.id
and    d.sensor_id = s.sensor_id;
