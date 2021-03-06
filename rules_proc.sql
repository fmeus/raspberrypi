delimiter $$

drop procedure if exists new_high_low$$
create procedure new_high_low()
begin
    truncate table tmp_high_low;

    insert into tmp_high_low( sensor_id, min_value, max_value )
        select sensor_id
        ,      min(value)
        ,      max(value)
        from   sensor_data
        where  timestamp >= date_sub(NOW(), interval 8 hour)
        group by sensor_id;

    -- Save changes
    commit;
end$$

drop procedure if exists update_high_low$$
create procedure update_high_low()
begin
    insert into sensor_high_low( sensor_id, min_value, max_value )
        select x.sensor_id
        ,      x.min_value
        ,      x.max_value
        from   tmp_high_low x
    on duplicate key
        update min_value=if( sensor_high_low.min_value > x.min_value, x.min_value, sensor_high_low.min_value )
        ,      max_value=if( sensor_high_low.max_value < x.max_value, x.max_value, sensor_high_low.max_value );  

    -- Save changes
    commit;
end$$

drop procedure if exists mark_invalid$$
create procedure mark_invalid()
begin
    update sensor_data as a
    inner join (select id
                from (select id
                     ,       (@prev := case when @sens = sensor_id then @prev else null end) as prev
                     ,       (@sens := sensor_id) sensor
                     ,       ( value - @prev ) / @prev * 100 as perc
                     ,       (@prev := value) as curr
                     from    sensor_data d
                     ,       (select @prev := null, @sens := null) a
                     where   d.timestamp >= date_sub(NOW(), interval 1 hour)
                     order by sensor_id, id) x
                where abs(x.perc) > 25) as b
    on a.id = b.id
    set a.valid = 'N'; 

    -- Save changes
    commit;
end$$

drop procedure if exists delete_invalid$$
create procedure delete_invalid()
begin
    -- Store invalid data for later review
    insert into bad_sensor_data select * from sensor_data where valid = 'N';

    -- Remove invalid data
    delete from sensor_data where valid = 'N';

    -- Save changes
    commit;
end$$

delimiter ;