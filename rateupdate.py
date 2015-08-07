#!/usr/bin/python2.6

import psycopg2
import sys

CONFIGURATOR = '10.125.252.170'
PVUV4LCR = '10.117.255.18'
CDR = 'cdr'

#variables
count = 0
pushes = 1
#ratedeckConn = "dbname='ratedeck' user='postgres' host='%s' " %(CONFIGURATOR)
ratedeckConn = "dbname='ratedeck' user='postgres' host='%s' " %(CDR)
#lcrConn = "dbname='nanpa' user='postgres' host='%s' " %(CONFIGURATOR)
lcrConn = "dbname='lcr' user='postgres' host='%s' " %(CDR)

def dbConnect(connection):
    try:
        db = psycopg2.connect(connection)
    except:
        print 'failed to connect to db'
        sys.exit(1)
    return db

def update(carrier, carrierTable, prefix = 'lrn', intra = 'intra', inter = 'inter', drop = True):
    print "pushing rates to domestic"
    #connect to db
    ratedeckDB = dbConnect(ratedeckConn)
    ratedeckCur = ratedeckDB.cursor()
    lcrDB = dbConnect(lcrConn)
    lcrCur = lcrDB.cursor()
    
    #get carrier_id
    select = "SELECT carrier_id FROM carriers WHERE name = '%s'" %(carrier)
    lcrCur.execute(select)
    carrier = lcrCur.fetchall()

    #drop old rates
    if (drop):
        query = "DELETE FROM domestic_rates WHERE carrier_id = %s" %(carrier[0][0])
        lcrCur.execute(query)
        lcrDB.commit()
        print query, "  yup that just happened"

    #insert New Rates
    query = "SELECT %s, %s, %s FROM %s WHERE validto is NULL" %(prefix, intra, inter, carrierTable)
    ratedeckCur.execute(query)
    rates = ratedeckCur.fetchall()
    count = 0
    for rate in rates:
        try:
            if (count == 0):
                query = "INSERT INTO domestic_rates (carrier_id, prefix, inter, intra) VALUES (%s, '%s', '%s', '%s')" %(carrier[0][0], rate[0], rate[1], rate[2])
            else:
                query = "%s, (%s, '%s', '%s', '%s')" %(query, carrier[0][0], rate[0], rate[1], rate[2])
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

def intUpdate (carrier, carrierTable, name = 'name', prefix = 'prefix', cost = 'cost'):
    print "pushing rates to international"
    #connect to db
    ratedeckDB = dbConnect(ratedeckConn)
    ratedeckCur = ratedeckDB.cursor()
    lcrDB = dbConnect(lcrConn)
    lcrCur = lcrDB.cursor()
    
    #get carrier_id
    select = "SELECT carrier_id FROM carriers WHERE name = '%s'" %(carrier)
    lcrCur.execute(select)
    carrier = lcrCur.fetchall()

    #drop old rates
    query = "DELETE FROM international_rates WHERE carrier_id = %s" %(carrier[0][0])
    lcrCur.execute(query)
    lcrDB.commit()
    print query, "  yup that just happened"

    #insert new rates
    query = "SELECT %s, %s, %s FROM %s WHERE validto is NULL" %(name, prefix, cost, carrierTable)
    ratedeckCur.execute(query)
    rates = ratedeckCur.fetchall()
    count = 0
    for rate in rates:
        try:
            if (count == 0):
                query = "INSERT INTO international_rates (carrier_id, location, dst_code, cost) VALUES (%s, '%s', %s, %s)" %(carrier[0][0], rate[0], rate[1], rate[2])
            else:
                query = "%s, (%s, '%s', %s, %s)" %(query, carrier[0][0], rate[0], rate[1], rate[2])

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
