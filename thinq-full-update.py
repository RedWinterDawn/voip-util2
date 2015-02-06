#!/usr/bin/python2.6

import psycopg2
import sys

CONFIGURATOR = '10.125.252.170'
PVUV4LCR = '10.117.255.18'

#variables
count = 0
pushes = 1
ratedeckConn = "dbname='ratedeck' user='postgres' host='%s' " %(CONFIGURATOR)
lcrConn = "dbname='nanpa' user='postgres' host='%s' " %(CONFIGURATOR)

## connect to db
try:
    ratedeckDB = psycopg2.connect(ratedeckConn)
    ratedeckCur = ratedeckDB.cursor()
    lcrDB = psycopg2.connect(lcrConn)
    lcrCur = lcrDB.cursor()
    print 'connected to db'
except:
    print 'failed to connect to db'
    sys.exit(1)

##drop rates from rate    
query = "DELETE FROM domestic WHERE carrier_id = 5"
try:
    lcrCur.execute(query)
    lcrDB.commit()
    print query, "  yup that just happened"
except psycopg2.Error as e:
    print 'failed to delete rates'
    print e.pgerror
    sys.exit(1)

#insert new rates
query = "SELECT substring(prefix::varchar(20) from 2) as newprefix, inter, intra FROM thinq_prime WHERE validto is NULL"
ratedeckCur.execute(query)
rates = ratedeckCur.fetchall()
count = 0
for rate in rates:
    try:
        if (count == 0):
            query = "INSERT INTO domestic (carrier_id, prefix, inter, intra) VALUES (5, %s, %s, %s)" %(rate[0], rate[1], rate[2])
        else:
            query = "%s, (5, %s, %s, %s)" %(query, rate[0], rate[1], rate[2])

        count = count + 1
        if (count==1000):
            lcrCur.execute(query)
            lcrDB.commit()            
            print '.',
            sys.stdout.flush()
            count = 0
    except psycopg2.Error as e:
        print e.pgerror
        pass

lcrCur.execute(query)
lcrDB.commit()
    
ratedeckDB.close()
lcrDB.close()

