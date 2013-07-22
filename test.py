#!/usr/bin/env python

import sqlite3 as sql


try:
    con = sql.connect( 'sensor-data.sqlite' )
    cur = con.cursor()
    cur.execute( 'select SQLITE_VERSION()' )
    print cur.fetchone()

    print( "insert into sensor_data(timestamp,sensor_id,value) values(datetime('now','localtime'), {0}, {1} )".format( 1, 27.5 ) )
    cur.commit()

except sql.Error, e:
    if con:
        con.rollback()

    print "Error %s" % e.args[0]

finally:
    if con:
        con.close()

