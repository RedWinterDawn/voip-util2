#!/usr/bin/python2.6

import yaml
import os
import psycopg2
import string
import sys
import re

CONFIGURATOR = '10.125.252.170'
KEY = 'md5b5f5ba1a423792b526f799ae4eb3d59e'

#connect to db
try:
    connection = "dbname='ebc' user='ebc' host='%s' password='%s'" %(CONFIGURATOR, KEY)
    db = psycopg2.connect(connection)
    cur = db.cursor()
except psycopg2.Error as e:
    print 'failed to connect to db'
    print e.pgerror

#copy current gloabl file
os.system('sudo scp root@172.31.1.10:/srv/www/data/dns/global.yml /tmp/tmpglobal.yml')

#read in the yaml file
with open('/tmp/tmpglobal.yml', 'r') as globalIn:
    doc = yaml.load(globalIn)

#datacenters to check for 
sites = ['chi-1a', 'atl-1a', 'dfw-1a', 'lax-1a', 'lon-1a', 'nyc-1a', 'ord-1a', 'geg-1a'];

#set all srv to false
query = "UPDATE srv SET status = 'False'"
cur.execute(query)
db.commit()

#update database from current .yml
for site in sites:
    status = doc["sites"][site]["enabled"]
    try:
        query = "INSERT INTO site_status (site, active) VALUES ('%s', '%s')" %(site, status)
        cur.execute(query)
        db.commit()
#        print 'site inserted'
    except psycopg2.Error as e:
        db.rollback()
        try:
            query = "UPDATE site_status SET active = '%s' WHERE site = '%s'" %(status, site)
            cur.execute(query)
            db.commit()
 #           print 'site updated'
        except psycopg2.Error as e:
            print 'failed to update site_status'
            print e.pgerror
            db.rollback()

  #  print "%s enabled: %s" %(site, status)
    count = 0
    for srv in doc["sites"][site]['srv']:
   #     print srv
        try:
            #print doc["sites"][site]["arecord"][count]
            try:
                query = "INSERT INTO srv (site, host, weight, priority, arecord, status) VALUES ('%s', '%s', %s, %s, '%s', 'True')" %(site, srv['host'], srv['weight'], srv['priority'], doc['sites'][site]['arecord'][count])
                cur.execute(query)
                db.commit()
              #  print 'srv insert'
            except psycopg2.Error as e:
               # print 'failed to insert'
               # print e.pgerror
                db.rollback()
                try:
                    query = "UPDATE srv SET status = 'True' WHERE host = '%s'" %(srv['host'])
                    cur.execute(query)
                    db.commit()
                #    print 'srv updated'
                except psycopg2.Error as e:
                    print 'failed to update srv'
                    print e.pgerror
                    db.rollback()
        except:
            break
        count = count + 1


#close db connection 
db.close()

#check for and get args
length = len(sys.argv)
if length > 5 or length < 3:
    print 'invalid or no arguments'
    sys.exit(0)
if length == 4:
    state = sys.argv[1]
    host = sys.argv[2]
    arecord = sys.argv[3]
if length == 3:
    state = sys.argv[1]
    site = sys.argv[2]
    search = '%s:' %(site)

#write the new yaml file
#edit global.yml
tempFile = open('/tmp/tmpglobal.yml', 'r')
lines = tempFile.readlines()
tempFile.close
newFile = open('/tmp/global.yml', 'w')
printLine = True

for line in lines:
    if printLine == False:
        printLine = True
        line = ''
    if length == 4 and state == 'a' and re.search("#\s*-\s*%s" %(arecord), line):
        m = re.search("#\s*-\s*%s" %(arecord), line)
        arecordReg = m.group(0) 
        line = string.replace(line, arecordReg, "- %s" %(arecord))
    if length == 4 and state == 'a' and re.search("#\s*- { host: '%s'" %(host), line):
        m = re.search("#\s*- { host: '%s'" %(host), line)
        hostReg = m.group(0)
        line = string.replace(line, hostReg, "- { host: '%s'" %(host))
    if state == 'd' and length == 4:
        line = string.replace(line, "- %s" %(arecord), "# - %s" %(arecord))
        line = string.replace(line, "- { host: '%s'" %(host), "# - { host: '%s'" %(host))
    if state == 'a' and length == 3:
        line = string.replace(line, "  %s:" %(site), "  %s:\n    enabled: true" %(site))
    if state == 'd' and length == 3:
        line = string.replace(line, "  %s:" %(site), "  %s:\n    enabled: false" %(site))
    if length == 3 and re.search(search, line):
        printLine = False
    newFile.write('%s' %(line))
newFile.close()
print 'OK'

## copy file to bastion
try:
    os.system('sudo scp /tmp/global.yml root@172.31.1.10:/srv/www/data/dns/global.yml')
except:
    print 'failed to scp file to bootstrap'
    system.exit(1)
## execute update

try:
    os.system('sudo ssh root@172.31.1.10 "pushd /srv/www/data/dns; ./update; ./validate;"')
    #os.system('sudo ssh root@172.31.1.10 "echo hello"')
except:
    print 'faild to run update and validate'

