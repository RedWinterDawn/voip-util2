#!/usr/bin/python2.6

import psycopg2
import sys
import json

CONFIGURATOR = '10.125.252.170'

def parseNum(number):
    length = len(number[0])
    x = 1
    query = "'%s'" %(number[0])
    while x < length:
        query = "%s, '%s'" %(query, number[0][:-x])
        x = x+1
    return query

def buildjson(carriers):
    data = []
    for carrier in carriers:
        data.append(carrier[0])
    jdata = json.dumps(data)
    jdata = '{"results":%s}' %(jdata)
    return jdata


#variables
ratedeckConn = "dbname='nanpa' user='postgres' host='%s' " %(CONFIGURATOR)

## connect to db
try:
    ratedeckDB = psycopg2.connect(ratedeckConn)
    ratedeckCur = ratedeckDB.cursor()
    print 'connected to db'
except:
    print 'failed to connect to db'
    sys.exit(1)

v4intCarrierQuery = "SELECT DISTINCT v4code as code FROM carrier WHERE international = TRUE"
intCarrierQuery = "SELECT DISTINCT code as code FROM carrier WHERE international = TRUE"
preFixQuery = "SELECT DISTINCT dst_code from international ORDER BY dst_code ASC"

ratedeckCur.execute(v4intCarrierQuery)
v4intCarrierArray = ratedeckCur.fetchall()
ratedeckCur.execute(intCarrierQuery)
intCarrierArray = ratedeckCur.fetchall()
#print intCarrierArray

ratedeckCur.execute(preFixQuery)
prefixArray = ratedeckCur.fetchall()

k=0
for prefix in prefixArray:
    interQuery = "WITH rates AS (SELECT v4code as code, LENGTH(dst_code) as length, cost FROM international NATURAL JOIN carrier WHERE dst_code in (%s)) SELECT code FROM (" %(parseNum(prefix)) 

    first = True
    for carrier in v4intCarrierArray:
        if first:
            first = False
        else:
            interQuery = "%s UNION" %(interQuery)
        interQuery = "%s SELECT * FROM (SELECT code, cost, length FROM rates WHERE code = '%s' ORDER BY length DESC, cost DESC LIMIT 1) as \"%s\"" %(interQuery, carrier[0], carrier[0])

    interQuery = "%s) as a ORDER BY cost ASC" %(interQuery)

    ratedeckCur.execute(interQuery)
    interArray = ratedeckCur.fetchall()

    jdata = buildjson(interArray)
    insertQuery = "INSERT INTO v4international_codes (prefix, codes) VALUES ('%s', '%s')" %(prefix[0], jdata)
    #print insertQuery
    ratedeckCur.execute(insertQuery)
    ratedeckDB.commit()
    if (k%10000) == 0:
        print k,
        sys.stdout.flush()
    if (k%1000) == 0:
        print '.',
        sys.stdout.flush()
    k=k+1

for prefix in prefixArray:
    interQuery = "WITH rates AS (SELECT code, LENGTH(dst_code) as length, cost FROM international NATURAL JOIN carrier WHERE dst_code in (%s)) SELECT code FROM (" %(parseNum(prefix)) 

    first = True
    for carrier in intCarrierArray:
        if first:
            first = False
        else:
            interQuery = "%s UNION" %(interQuery)
        interQuery = "%s SELECT * FROM (SELECT code, cost, length FROM rates WHERE code = '%s' ORDER BY length DESC, cost DESC LIMIT 1) as \"%s\"" %(interQuery, carrier[0], carrier[0])

    interQuery = "%s) as a ORDER BY cost ASC" %(interQuery)

    ratedeckCur.execute(interQuery)
    interArray = ratedeckCur.fetchall()

    jdata = buildjson(interArray)
    insertQuery = "INSERT INTO international_codes (prefix, codes) VALUES ('%s', '%s')" %(prefix[0], jdata)
    #print insertQuery
    ratedeckCur.execute(insertQuery)
    ratedeckDB.commit()
    if (k%10000) == 0:
        print k,
        sys.stdout.flush()
    if (k%1000) == 0:
        print '.',
        sys.stdout.flush()
    k=k+1

ratedeckDB.close()
