#!/usr/bin/python2.6

import psycopg2
import sys
import json

CONFIGURATOR = '10.125.252.170'
CDR = 'cdr'

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
ratedeckConn = "dbname='lcr' user='postgres' host='%s' " %(CDR)

## connect to db
try:
    ratedeckDB = psycopg2.connect(ratedeckConn)
    ratedeckCur = ratedeckDB.cursor()
    print 'connected to db'
except:
    print 'failed to connect to db'
    sys.exit(1)

#get current version
versionSelect = "SELECT version FROM current_ratedeck WHERE released IS NULL ORDER BY version DESC LIMIT 1"
ratedeckCur.execute(versionSelect)
version = ratedeckCur.fetchall()
try:
    version = version[0][0]
    print "Updating to version %s" %(version)
except:
    print "Running current version"
    sys.exit(1)


v4intCarrierQuery = "SELECT DISTINCT v4code as code FROM carriers WHERE international = TRUE"
v4domCarrierQuery = "SELECT DISTINCT v4code as code FROM carriers WHERE usdomestic = TRUE OR international = TRUE"
intCarrierQuery = "SELECT DISTINCT code as code FROM carriers WHERE international = TRUE"
domCarrierQuery = "SELECT DISTINCT code as code FROM carriers WHERE usdomestic = TRUE OR international = TRUE"
prefixQuery = "SELECT DISTINCT prefix from (SELECT DISTINCT substring(dst_code from 2) as prefix from international_rates where dst_code like '1%' UNION SELECT DISTINCT prefix from domestic_rates) as prefix ORDER BY prefix ASC"
intPrefixQuery = "SELECT DISTINCT dst_code from international_rates ORDER BY dst_code ASC"

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

ratedeckCur.execute(prefixQuery)
prefixArray = ratedeckCur.fetchall()
ratedeckCur.execute(intPrefixQuery)
intPrefixArray = ratedeckCur.fetchall()

rates = ['inter', 'intra']
prefixArray = [['201494'], ['201495']]
k = 0
for prefix in prefixArray:
    insert = True
    for rate in rates:
        interQuery = "WITH rates AS (SELECT v4code as code, LENGTH(prefix) as length, %s FROM domestic_rates NATURAL JOIN carriers WHERE prefix in (%s) UNION SELECT v4code, LENGTH(dst_code), cost FROM international_rates NATURAL JOIN carriers WHERE dst_code in (%s) UNION SELECT v4code, 1, %s FROM star_rates NATURAL JOIN carriers) SELECT code FROM (" %(rate, parseNum(prefix), parseIntNum(prefix), rate) 

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
            insertQuery = "INSERT INTO v4domestic_codes (version, prefix, %s) VALUES (%s, '%s', '%s')" %(rate, version, prefix[0], jdata)
            insert = False
        else:
            insertQuery = "UPDATE v4domestic_codes SET %s = '%s' WHERE prefix = '%s'" %(rate, jdata, prefix[0])
        if (k % 10000) == 0:
            print k,
            sys.stdout.flush()
        if (k % 1000) == 0:
            print '.',
            sys.stdout.flush()
        k = k+1
        ratedeckCur.execute(insertQuery)
        ratedeckDB.commit()

for prefix in prefixArray:
    insert = True
    for rate in rates:
        interQuery = "WITH rates AS (SELECT code, LENGTH(prefix) as length, %s FROM domestic_rates NATURAL JOIN carriers WHERE prefix in (%s) UNION SELECT code, LENGTH(dst_code), cost FROM international_rates NATURAL JOIN carriers WHERE dst_code in (%s) UNION SELECT code, 1, %s FROM star_rates NATURAL JOIN carriers) SELECT code FROM (" %(rate, parseNum(prefix), parseIntNum(prefix), rate) 

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
            insertQuery = "INSERT INTO domestic_codes (version, prefix, %s) VALUES (%s, '%s', '%s')" %(rate, version, prefix[0], jdata)
            insert = False
        else:
            insertQuery = "UPDATE domestic_codes SET %s = '%s' WHERE prefix = '%s'" %(rate, jdata, prefix[0])
        if (k % 10000) == 0:
            print k,
            sys.stdout.flush()
        if (k %  1000) == 0:
            print '.',
            sys.stdout.flush()
        k = k+1
        ratedeckCur.execute(insertQuery)
        ratedeckDB.commit()

## add star rates        
first = True
for rate in rates:
    starQuery = 'SELECT v4code FROM star_rates NATURAL JOIN carriers ORDER BY %s ASC' %(rate)
    ratedeckCur.execute(starQuery)
    starArray = ratedeckCur.fetchall()
    jdata = buildjson(starArray)
    if (first):
        insertQuery = "INSERT INTO v4domestic_codes (version, prefix, %s) VALUES (%s, '*', '%s')" %(rate, version, jdata)
        first = False
    else:
        insertQuery = "UPDATE v4domestic_codes set %s = '%s' where prefix = '*'" %(rate, jdata)
    ratedeckCur.execute(insertQuery)
    ratedeckDB.commit()

first = True
for rate in rates:
    starQuery = 'SELECT code FROM star_rates NATURAL JOIN carriers ORDER BY %s ASC' %(rate)
    ratedeckCur.execute(starQuery)
    starArray = ratedeckCur.fetchall()
    jdata = buildjson(starArray)
    if (first):
        insertQuery = "INSERT INTO domestic_codes (version, prefix, %s) VALUES (%s, '*', '%s')" %(rate, version, jdata)
        first = False
    else:
        insertQuery = "UPDATE domestic_codes set %s = '%s' where prefix = '*'" %(rate, jdata)
    ratedeckCur.execute(insertQuery)
    ratedeckDB.commit()

## set 400 rates
incontact = [['INCONTACT']]
jinter = buildjson (incontact)
jintra= buildjson(incontact) 
insert = "INSERT INTO v4domestic_codes (version, prefix, intra, inter) VALUES (%s, '400', '%s', '%s')" %(version, jintra, jinter)
ratedeckCur.execute(insert)
ratedeckDB.commit()
insert = "INSERT INTO domestic_codes (version, prefix, intra, inter) VALUES (%s, '400', '%s', '%s')" %(version, jintra, jinter)
ratedeckCur.execute(insert)
ratedeckDB.commit()

##apply overrides
overrideQuery = "SELECT * FROM override_domestic"
ratedeckCur.execute(overrideQuery)
overrideArray = ratedeckCur.fetchall()
for override in overrideArray:
    #upsert v4domestic
    update = "UPDATE v4domestic_codes SET intra = '%s', inter = '%s' WHERE prefix = '%s'" %(override[2], override[3], override[1]) 
    insert = "INSERT INTO v4domestic_codes (version, prefix, intra, inter) VALUES (%s, '%s', '%s', '%s')" %(version, override[1], override[2], override[3])
    upsert(ratedeckDB, ratedeckCur, update, insert)

    #upsert v5domestic
    update = "UPDATE domestic_codes SET intra = '%s', inter = '%s' WHERE prefix = '%s'" %(override[4], override[5], override[1]) 
    insert = "INSERT INTO domestic_codes (version, prefix, intra, inter) VALUES (%s, '%s', '%s', '%s')" %(version, override[1], override[4], override[5])
    upsert(ratedeckDB, ratedeckCur, update, insert)

#update international codes
k=0
for prefix in intPrefixArray:
    interQuery = "WITH rates AS (SELECT v4code as code, LENGTH(dst_code) as length, cost FROM international_rates NATURAL JOIN carriers WHERE dst_code in (%s)) SELECT code FROM (" %(parseNum(prefix))

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

for prefix in intPrefixArray:
    interQuery = "WITH rates AS (SELECT code, LENGTH(dst_code) as length, cost FROM international_rates NATURAL JOIN carriers WHERE dst_code in (%s)) SELECT code FROM (" %(parseNum(prefix))

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
    ratedeckCur.execute(insertQuery)
    ratedeckDB.commit()
    if (k%10000) == 0:
        print k,
        sys.stdout.flush()
    if (k%1000) == 0:
        print '.',
        sys.stdout.flush()
    k=k+1

#delete old version
delete = "DELETE from v4domestic_codes where version != %s or version is NULL" %(version)
ratedeckCur.execute(delete)
ratedeckDB.commit()

#set release date
update = "UPDATE current_ratedeck SET released = now() WHERE released is NULL"
ratedeckCur.execute(update)
ratedeckDB.commit()


ratedeckDB.close()
