#!/usr/bin/python2.6

import csv
import psycopg2
import sys
import rateupdate

CONFIGURATOR = '10.125.252.170'
CDR = 'cdr'

##check for and get args
#length = len(sys.argv)
#if length != 2:
#    print 'Invalid or no args'
#    sys.exit(1)

#variables
count = 0
pushes = 1
connection = "dbname='ratedeck' user='postgres' host='%s' " %(CDR)
fileName = "/var/www/uploads/voip-ratedeck.csv"

## connect to db
try:
    db = psycopg2.connect(connection)
    cur = db.cursor()
    print 'connected to db'
except:
    print 'failed to connect to db'
    sys.exit(1)

##update validto    
query = "UPDATE voip_domestic SET validto = now() where validto is NULL"
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
        lrn = row[0]
        if lrn.isdigit():
            if count == 0:
              query = "INSERT INTO voip_domestic (lrn, inter, intra, validfrom) VALUES (%s, %s, %s, now())" %(row[0], row[1][2:], row[2][2:])
              count = count + 1
            elif count < 10000:
              query = "%s, (%s, %s, %s, now())" %(query, row[0], row[1][2:], row[2][2:])
              count  = count + 1
            else:    
                try:
                    cur.execute(query)
                    db.commit()
                    print pushes, ' ',
                    pushes = pushes +1
                    count = 0
                    query = ''
                except psycopg2.Error as e:
                    print e.pgerror        
                    pass
try:
    cur.execute(query)
    db.commit()
except psycopg2.Error as e:
    print e.pgerror
    pass
db.close()

print ''
rateupdate.update('VoIP Innovations', 'voip_domestic')
print ''
print 'Completed'
