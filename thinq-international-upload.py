#!/usr/bin/python2.6

import csv
import psycopg2
import sys
import os

CONFIGURATOR = '10.125.252.170'

##check for and get args
##length = len(sys.argv)
##if length != 2:
##    print 'Invalid or no args'
##    sys.exit(1)

#variables
count = 0
pushes = 1
connection = "dbname='ratedeck' user='postgres' host='%s' " %(CONFIGURATOR)
fileName = "/var/www/uploads/thinq-int/"

##unzip file
os.system('rm -rf /var/www/uploads/thinq-int; unzip /var/www/uploads/thinq-int-ratedeck.zip -d /var/www/uploads/thinq-int')
files = os.listdir(fileName)
fileName = "%s%s" %(fileName, files[0])
## connect to db
try:
    db = psycopg2.connect(connection)
    cur = db.cursor()
    print 'connected to db'
except:
    print 'failed to connect to db'
    sys.exit(1)

##update validto    
query = "UPDATE thinq_international SET validto = now() where validto is NULL"
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
        lrn = row[1]
        dest = row[0].replace("'", "")
        if lrn.isdigit():
            if count == 0:
                query = "INSERT INTO thinq_international (carrier_id, destination, prefix, rate, initial, effective, validFrom) VALUES (0, '%s', '%s', %s, %s, '%s', now())" %(dest, lrn, row[2], row[3], row[5])
                count = count + 1
            elif count < 1000:
                query = "%s, (0, '%s', '%s', %s, %s, '%s', now())" %(query, dest, lrn, row[2], row[3], row[5])
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
                    pass
try:
    cur.execute(query)
    db.commit()
    print "Completed"
except psycopg2.Error as e:
    print e.pgerror
    pass
db.close()

