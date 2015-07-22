#!/usr/bin/python2.6

import openpyxl
import psycopg2
import sys
import rateupdate

CONFIGURATOR = '10.125.252.170'

#variables
datas = False
count = 0
pushes = 1
fileName = "/var/www/uploads/onvoy-int-ratedeck.xlsx"
connection = "dbname='ratedeck' user='postgres' host='%s' " %(CONFIGURATOR)

#load file
wb = openpyxl.load_workbook(fileName, use_iterators = True)
wsname =  wb.get_sheet_names()
ws = wb.get_sheet_by_name(name = wsname[0]) 

##connect to db
try:
    db = psycopg2.connect(connection)
    cur = db.cursor()
    print 'connected to db'
except:
    print 'failed to connect to db'
    sys.exit(1)

##update validto
query = "UPDATE onvoy_international SET validto = now() where validto is NULL"
try:
    cur.execute(query)
    db.commit()
    print 'update success'
except:
    print 'failed to update'
    sys.exit(1)

for row in ws.iter_rows():
    if isinstance(row[1].value, int):
        if count == 0:
            query = "INSERT INTO onvoy_international (name, code, cost, validfrom) VALUES ('%s', %s, %s, now())" %(row[0].value, row[1].value, row[2].value)
            count = count + 1
        elif count < 1000:
            query = "%s, ('%s', %s, %s, now())" %(query, row[0].value, row[1].value, row[2].value)
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
                print 'database fail'
                print query
                pass
try:
    cur.execute(query)
    db.commit()
except psycopg2.Error as e:
    print e.pgerror
    pass
db.close()

rateupdate.intUpdate('Onvoy', 'onvoy_international', 'name', 'code')
print 'completed' 
