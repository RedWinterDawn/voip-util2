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

def parseIntNum(number):
    length = len(number[0])
    x = 1
    query = "'1%s'" %(number[0])
    while x < length:
        query = "%s, '1%s'" %(query, number[0][:-x])
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

intCarrierQuery = "SELECT DISTINCT v4code as code FROM carrier WHERE international = TRUE"
domCarrierQuery = "SELECT DISTINCT v4code as code FROM carrier WHERE usdomestic = TRUE OR international = TRUE"
preFixQuery = "SELECT DISTINCT prefix from (SELECT DISTINCT substring(dst_code from 2) as prefix from international where dst_code like '1%' UNION SELECT DISTINCT prefix from domestic) as prefix ORDER BY prefix ASC"

ratedeckCur.execute(intCarrierQuery)
intCarrierArray = ratedeckCur.fetchall()
#print intCarrierArray

ratedeckCur.execute(domCarrierQuery)
domCarrierArray = ratedeckCur.fetchall()
#print domCarrierArray

ratedeckCur.execute(preFixQuery)
prefixArray = ratedeckCur.fetchall()

rates = ['inter', 'intra']
k = 0
for prefix in prefixArray:
    insert = True
    for rate in rates:
        interQuery = "WITH rates AS (SELECT v4code as code, LENGTH(prefix) as length, %s FROM domestic NATURAL JOIN carrier WHERE prefix in (%s) UNION SELECT v4code, LENGTH(dst_code), cost FROM international NATURAL JOIN carrier WHERE dst_code in (%s) UNION SELECT v4code, 1, %s FROM star NATURAL JOIN carrier) SELECT code FROM (" %(rate, parseNum(prefix), parseIntNum(prefix), rate) 

        first = True
        for carrier in domCarrierArray:
            if first:
                first = False
            else:
                interQuery = "%s UNION" %(interQuery)
            interQuery = "%s SELECT * FROM (SELECT code, %s, length FROM rates WHERE code = '%s' ORDER BY length DESC, %s DESC LIMIT 1) as \"%s\"" %(interQuery, rate, carrier[0], rate, carrier[0])

        interQuery = "%s) as a ORDER BY %s ASC" %(interQuery, rate)

        ratedeckCur.execute(interQuery)
        interArray = ratedeckCur.fetchall()

        jdata = buildjson(interArray)
        if insert:
            insertQuery = "INSERT INTO domestic_codes (prefix, %s) VALUES ('%s', '%s')" %(rate, prefix[0], jdata)
            insert = False
        else:
            insertQuery = "UPDATE domestic_codes SET %s = '%s' WHERE prefix = '%s'" %(rate, jdata, prefix[0])
        if (k % 1000) == 0:
            print '.',
            sys.stdout.flush()
        if (k %  10000) == 0:
            print k,
            sys.stdout.flush()
        k = k+1
        ratedeckCur.execute(insertQuery)
        ratedeckDB.commit()

## add star rates        
for rate in rates:
    starQuery = 'SELECT v4code FROM star NATURAL JOIN carrier ORDER BY %s ASC' %(rate)
    ratedeckCur.execute(starQuery)
    starArray = ratedeckCur.fetchall()
    jdata = buildjson(interArray)
    insertQuery = "INSERT INTO domestic_codes (prefix, %s) VALUES ('*', '%s')" %(rate, jdata)
    ratedeckCur.execute(insertQuery)
    ratedeckDB.commit()

ratedeckDB.close()
