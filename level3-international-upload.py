#!/usr/bin/python2.6

import csv
import psycopg2
import sys
import os

CONFIGURATOR = '10.125.252.170'

#variables
count = 0
pushes = 1
connection = "dbname='ratedeck' user='postgres' host='%s' " %(CONFIGURATOR)
fileName = "/var/www//uploads/level3-int-ratedeck.csv"

## connect to db
try:
    db = psycopg2.connect(connection)
    cur = db.cursor()
    print 'connected to db'
except:
    print 'failed to connect to db'
    sys.exit(1)

##update validto    
query = "UPDATE level3_international SET validto = now() where validto is NULL"
try:
    cur.execute(query)
    db.commit()
except:
    print 'failed to update'
    sys.exit(1)

#load csv file
with open(fileName, 'rb') as csvfile:
    csvreader = csv.reader(csvfile, delimiter=',')
    for row in csvreader:
        lrn = "%s" %(row[2])
        if lrn.isdigit():
            if count == 0:
              query = "INSERT INTO level3_international (destination, worldzone, fullcode, countrycode, citycode, rate, effectivedate, changetype, validFrom) VALUES ('%s', %s, '%s', '%s', '%s', %s, '%s', '%s', now())" %(row[0], row[1], row[2], row[3], row[4], row[5][2:], row[6], row[7])
              count = count + 1
            elif count < 100:
              query = "%s, ('%s', %s, '%s', '%s', '%s', %s, '%s', '%s', now())" %(query, row[0], row[1], row[2], row[3], row[4], row[5], row[6], row[7])
              count  = count + 1
            else:    
                try:
                    cur.execute(query)
                    db.commit()
                    print pushes
                    pushes = pushes +1
                    count = 0
                    query = ''
                except psycopg2.Error as e:
                    print e.pgerror        
                    print query
                    sys.exit(1)
                    pass
try:
    cur.execute(query)
    db.commit()
    print "Completed"
except psycopg2.Error as e:
    print e.pgerror
    pass
db.close()

