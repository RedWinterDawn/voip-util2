#!/usr/bin/python2.6

import csv
import psycopg2
import sys
import rateupdate


CONFIGURATOR = '10.125.252.170'
CDR = 'cdr'

#variables
count = 0
pushes = 1
connection = "dbname='ratedeck' user='postgres' host='%s' " %(CDR)
fileName = "/var/www/uploads/level3-ratedeck.csv"

## connect to db
try:
    db = psycopg2.connect(connection)
    cur = db.cursor()
    print 'connected to db'
except:
    print 'failed to connect to db'
    sys.exit(1)

##update validto    
query = "UPDATE level3_domestic SET validto = now() where validto is NULL"
query1 = "UPDATE level3_extdomestic SET validto = now() where validto is NULL"
try:
    cur.execute(query)
    cur.execute(query1)
    db.commit()
except:
    print 'failed to update'
    sys.exit(1)

#load csv file
with open(fileName, 'rb') as csvfile:
    csvreader = csv.reader(csvfile, delimiter=',')
    for row in csvreader:
        npa = row[1]
        if npa.isdigit():
            if count == 0:
                query = "INSERT INTO level3_domestic (npa, nxx, inter, intra, validfrom) VALUES (%s, %s, %s, %s, now())" %(row[0], row[1], row[2], row[3])
                count = count + 1
            elif count < 10000:
                query = "%s, (%s, %s, %s, %s, now())" %(query, row[0], row[1], row[2], row[3])
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

count = 0
fileName = "/var/www/uploads/level3-ext-ratedeck.csv"
with open(fileName, 'rb') as csvfile:
    csvreader = csv.reader(csvfile, delimiter=',')
    for row in csvreader:
        npa = row[1]
        if npa.isdigit():
            if count == 0:
              query = "INSERT INTO level3_extdomestic (location, zone, country_code, dedicated, validfrom) VALUES ('%s', '%s', '%s', '%s', now())" %(row[0], row[1], row[2], row[3][2:])
              count = count + 1
            elif count < 10000:
              query = "%s, ('%s', '%s', '%s', '%s', now())" %(query, row[0], row[1], row[2], row[3][2:])
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
rateupdate.update('Level3', 'level3_domestic', 'npa || nxx as prefix')
print ''
rateupdate.update('Level3', 'level3_extdomestic', 'country_code', 'dedicated', 'dedicated', False)
print ''
print 'Completed';
