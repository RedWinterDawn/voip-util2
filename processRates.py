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

def upsert (db, cur, update, insert):
    cur.execute(update)
    if (cur.rowcount) == 0:
        cur.execute(insert)
    db.commit()

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

##connect to ratedeck
dbConnection = "dbname='ratedeck' user='postgres' host='%s' " %(CONFIGURATOR)

try:
    db = psycopg2.connect(dbConnection)
    dbCur = db.cursor()
    print 'conectied to db'
except:
    print 'failed to connect to db2'
    sys.exit(1)

#drop current/old codes
v4DropQuery = "DELETE FROM v4domestic_codes"
dropQuery = "DELETE FROM domestic_codes"

ratedeckCur.execute(v4DropQuery)
ratedeckDB.commit()
ratedeckCur.execute(dropQuery)
ratedeckDB.commit()


v4intCarrierQuery = "SELECT DISTINCT v4code as code FROM carrier WHERE international = TRUE"
v4domCarrierQuery = "SELECT DISTINCT v4code as code FROM carrier WHERE usdomestic = TRUE OR international = TRUE"
intCarrierQuery = "SELECT DISTINCT code as code FROM carrier WHERE international = TRUE"
domCarrierQuery = "SELECT DISTINCT code as code FROM carrier WHERE usdomestic = TRUE OR international = TRUE"
preFixQuery = "SELECT DISTINCT prefix from (SELECT DISTINCT substring(dst_code from 2) as prefix from international where dst_code like '1%' UNION SELECT DISTINCT prefix from domestic) as prefix ORDER BY prefix ASC"

ratedeckCur.execute(v4intCarrierQuery)
v4intCarrierArray = ratedeckCur.fetchall()
ratedeckCur.execute(intCarrierQuery)
intCarrierArray = ratedeckCur.fetchall()
#print intCarrierArray

ratedeckCur.execute(v4domCarrierQuery)
v4domCarrierArray = ratedeckCur.fetchall()
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
        for carrier in v4domCarrierArray:
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
            insertQuery = "INSERT INTO v4domestic_codes (prefix, %s) VALUES ('%s', '%s')" %(rate, prefix[0], jdata)
            insert = False
        else:
            insertQuery = "UPDATE v4domestic_codes SET %s = '%s' WHERE prefix = '%s'" %(rate, jdata, prefix[0])
        if (k % 1000) == 0:
            print '.',
            sys.stdout.flush()
        if (k %  10000) == 0:
            print k,
            sys.stdout.flush()
        k = k+1
        ratedeckCur.execute(insertQuery)
        ratedeckDB.commit()

for prefix in prefixArray:
    insert = True
    for rate in rates:
        interQuery = "WITH rates AS (SELECT code, LENGTH(prefix) as length, %s FROM domestic NATURAL JOIN carrier WHERE prefix in (%s) UNION SELECT code, LENGTH(dst_code), cost FROM international NATURAL JOIN carrier WHERE dst_code in (%s) UNION SELECT code, 1, %s FROM star NATURAL JOIN carrier) SELECT code FROM (" %(rate, parseNum(prefix), parseIntNum(prefix), rate) 

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
first = True
for rate in rates:
    starQuery = 'SELECT v4code FROM star NATURAL JOIN carrier ORDER BY %s ASC' %(rate)
    ratedeckCur.execute(starQuery)
    starArray = ratedeckCur.fetchall()
    jdata = buildjson(starArray)
    if (first):
        insertQuery = "INSERT INTO v4domestic_codes (prefix, %s) VALUES ('*', '%s')" %(rate, jdata)
        first = False
    else:
        insertQuery = "UPDATE v4domestic_codes set %s = '%s' where prefix = '*'" %(rate, jdata)
    ratedeckCur.execute(insertQuery)
    ratedeckDB.commit()

first = True
for rate in rates:
    starQuery = 'SELECT code FROM star NATURAL JOIN carrier ORDER BY %s ASC' %(rate)
    ratedeckCur.execute(starQuery)
    starArray = ratedeckCur.fetchall()
    jdata = buildjson(starArray)
    if (first):
        insertQuery = "INSERT INTO domestic_codes (prefix, %s) VALUES ('*', '%s')" %(rate, jdata)
        first = False
    else:
        insertQuery = "UPDATE domestic_codes set %s = '%s' where prefix = '*'" %(rate, jdata)
    ratedeckCur.execute(insertQuery)
    ratedeckDB.commit()

## set 400 rates
incontact = [['INCONTACT']]
jinter = buildjson (incontact)
jintra= buildjson(incontact) 
update = "UPDATE v4domestic_codes SET intra = '%s', inter = '%s' WHERE prefix = '400'" %(jintra, jinter)
ratedeckCur.execute(update)
ratedeckDB.commit()
update = "UPDATE domestic_codes SET intra = '%s', inter = '%s' WHERE prefix = '400'" %(jintra, jinter)
ratedeckCur.execute(update)
ratedeckDB.commit()

##apply overrides
overrideQuery = "SELECT * FROM override_domestic"
dbCur.execute(overrideQuery)
overrideArray = dbCur.fetchall()
for override in overrideArray:
    #upsert v4domestic
    update = "UPDATE v4domestic_codes SET intra = '%s', inter = '%s' WHERE prefix = '%s'" %(override[2], override[3], override[1]) 
    insert = "INSERT INTO v4domestic_codes (prefix, intra, inter) VALUES ('%s', '%s', '%s')" %(override[1], override[2], override[3])
    upsert(ratedeckDB, ratedeckCur, update, insert)

    #upsert v5domestic
    update = "UPDATE domestic_codes SET intra = '%s', inter = '%s' WHERE prefix = '%s'" %(override[4], override[5], override[1]) 
    insert = "INSERT INTO domestic_codes (prefix, intra, inter) VALUES ('%s', '%s', '%s')" %(override[1], override[4], override[5])
    upsert(ratedeckDB, ratedeckCur, update, insert)

ratedeckDB.close()
db.close()
