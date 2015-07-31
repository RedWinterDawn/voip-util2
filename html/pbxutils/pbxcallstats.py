#!/usr/bin/env python26
import logging, logging.handlers
import time
import sys
from twisted.internet import task, reactor, defer
import psycopg2
import httplib2
import json
import socket
import re
import multiprocessing

from starpy import manager
def setup_logging(level):
    # setup logging
    logger = logging.getLogger('jive')
    logger.setLevel(level)
    formatter = logging.Formatter('%(name)s %(levelname)s %(message)s')

    sl_handler = logging.handlers.SysLogHandler()
    sl_handler.setLevel(level)
    sl_handler.setFormatter(formatter)
    logger.addHandler(sl_handler)

class Countdown(object):
 
    counter = 5
 
    def count(self):
        if self.counter == 0:
            reactor.stop()
        else:
            self.counter -= 1
            reactor.callLater(1, self.count)

class WebConnection:

    def __init__(self, connect_args):
        self.connect_args = connect_args
        #self.sock = socket.socket()
        #self.sock.connect(('graphite.devops.jive.com', 2003))
        self.conn = httplib2.Http(timeout=1, disable_ssl_certificate_validation=True)

    def send(self, data):
        try:
            jdata = json.dumps(data)
            #self.sock.sendall("icalls.pbxs.%s.channels %s %d\n" % (re.sub('\.','_',data[0][1]),data[1][1], int(time.time())))
            self.conn.request(body=str(jdata), **self.connect_args)
            print jdata
        except:
            pass


class jiveAMIProtocol(manager.AMIProtocol):

    def generateActionId(self):
        self.count += 1
        return '%s-%s-%s' % (self.transport.getPeer().host, id(self), self.count)

    def corestatus(self):
        message = {'action':'CoreStatus'}
        return self.sendDeferred(message).addCallback(self.errorUnlessResponse)

class jiveManager(manager.AMIFactory):
    protocol = jiveAMIProtocol 

def printData(df):
    print df

class jiveRealTime(manager.AMIFactory):
    protocol = jiveAMIProtocol 

    def __init__(self, jrtm, pbx):
        self.jrtm = jrtm
        self.pbx = pbx
        manager.AMIFactory.__init__(self, 'reporting', 'iUVrxAhp7ZnE')

    def connect(self):
        self.df = self.login(self.pbx, 5038)
        self.df.addCallbacks(self.onLogin, self.onFailure)
        return self

    def onLogin(self, ami):
        self.ami = ami
        self.queryStats()

    def queryStats(self):
        df = self.ami.corestatus()
        def onSuccess(message):
			calls = int(message['corecurrentcalls'])
			rest.send([('pbx', self.pbx), ('calls', calls), ('ts', int(time.time() * 1000)), ('site', 'CHI')])
        def onFail(message):
			print message
        df.addCallbacks(onSuccess, onFail)

    def onFailure(self, reason):
        """Unable to log in!"""
        self.jrtm.fail(self, reason)
        return reason

    def printTest(self):
        print 'TESTING!@#$'

class jiveRealTimeManager():

    def __init__(self):
        self.clients = {} 

    def start(self):
        self.servers = self.getServers()
        for x in self.servers:
            if x not in self.clients:
                f = jiveRealTime(self, x).connect()
                self.clients[x] = f
    def fail(self, jrt, reason):
        print reason, jrt

    def printClients(self):
        for x in self.clients:
            print self.clients[x]
            if self.clients[x]:
                print x
                print dir(self.clients[x]) 
            else:
                print 'DIED: {0}'.format(x)
    def getServers(self):
        servers={}
        servers['10.101.12.1'] = 'CR1'
        servers['10.101.12.2'] = 'CR2'
        servers['10.101.7.1'] = 'CHI'
        servers['10.101.7.2'] = 'JIVE-4-10'
        servers['10.101.7.3'] = 'JIVE-4-10'
        servers['10.101.7.4'] = 'JIVE-4-10'
        servers['10.101.7.5'] = 'JIVE-4-10'
        servers['10.101.7.6'] = 'JIVE-4-10'
        servers['10.101.7.7'] = 'JIVE-4-10'
        servers['10.101.7.8'] = 'JIVE-4-10'
        servers['10.101.7.9'] = 'JIVE-4-10'
        servers['10.101.7.10'] = 'JIVE-4-10'
        servers['10.101.7.11'] = 'JIVE-4-10'
        servers['10.101.7.12'] = 'JIVE-4-10'
        servers['10.101.7.13'] = 'JIVE-4-10'
        servers['10.101.7.14'] = 'JIVE-4-10'
        servers['10.101.7.15'] = 'JIVE-4-10'
        servers['10.101.7.16'] = 'JIVE-4-10'
        servers['10.101.7.17'] = 'JIVE-4-10'
        servers['10.101.7.18'] = 'JIVE-4-10'
        servers['10.101.7.19'] = 'JIVE-4-10'
        servers['10.101.7.20'] = 'JIVE-4-10'
        servers['10.101.7.21'] = 'JIVE-4-10'
        servers['10.101.7.22'] = 'JIVE-4-10'
        servers['10.101.7.23'] = 'JIVE-4-10'
        servers['10.101.7.24'] = 'JIVE-4-10'
        servers['10.101.7.25'] = 'JIVE-4-10'
        servers['10.101.7.26'] = 'JIVE-4-10'
        servers['10.101.7.27'] = 'JIVE-4-10'
        servers['10.101.7.28'] = 'JIVE-4-10'
        servers['10.101.7.29'] = 'JIVE-4-10'
        servers['10.101.7.30'] = 'JIVE-4-10'
        servers['10.101.7.31'] = 'JIVE-4-10'
        servers['10.101.7.32'] = 'JIVE-4-10'
        servers['10.101.7.33'] = 'JIVE-4-10'
        servers['10.101.7.34'] = 'JIVE-4-10'
        servers['10.101.7.35'] = 'JIVE-4-10'
        servers['10.101.7.36'] = 'JIVE-4-10'
        servers['10.101.7.37'] = 'JIVE-4-10'
        servers['10.101.7.38'] = 'JIVE-4-10'
        servers['10.101.7.39'] = 'JIVE-4-10'
        servers['10.101.7.40'] = 'JIVE-4-10'
        servers['10.101.7.41'] = 'JIVE-4-10'
        servers['10.101.7.42'] = 'JIVE-4-10'
        servers['10.101.7.43'] = 'JIVE-4-10'
        servers['10.101.7.44'] = 'JIVE-4-10'
        servers['10.101.7.45'] = 'JIVE-4-10'
        #servers['10.101.7.100'] = 'JIVE-4-10'
        servers['10.101.7.101'] = 'JIVE-4-10'
        servers['10.125.60.1'] = 'JIVE-4-10'
        servers['10.125.60.2'] = 'JIVE-4-10'
        servers['10.125.60.3'] = 'JIVE-4-10'
#        servers['10.125.60.4'] = 'JIVE-4-10'
        servers['10.125.60.5'] = 'JIVE-4-10'
        servers['10.125.60.6'] = 'JIVE-4-10'
        servers['10.125.60.7'] = 'JIVE-4-10'
        servers['10.125.60.8'] = 'JIVE-4-10'
        servers['10.125.60.9'] = 'JIVE-4-10'
        servers['10.125.60.10'] = 'JIVE-4-10'
        servers['10.125.60.11'] = 'JIVE-4-10'
        servers['10.125.60.12'] = 'JIVE-4-10'
        servers['10.125.60.13'] = 'JIVE-4-10'
        servers['10.125.60.14'] = 'JIVE-4-10'
        servers['10.125.60.15'] = 'JIVE-4-10'
        servers['10.125.60.16'] = 'JIVE-4-10'
        servers['10.125.60.17'] = 'JIVE-4-10'
        servers['10.125.60.18'] = 'JIVE-4-10'
        servers['10.125.60.19'] = 'JIVE-4-10'
        servers['10.125.60.20'] = 'JIVE-4-10'
        servers['10.125.60.21'] = 'JIVE-4-10'
        servers['10.125.60.22'] = 'JIVE-4-10'
        servers['10.125.60.23'] = 'JIVE-4-10'
        servers['10.125.60.24'] = 'JIVE-4-10'
        servers['10.125.60.25'] = 'JIVE-4-10'
        servers['10.125.60.26'] = 'JIVE-4-10'
        servers['10.125.60.27'] = 'JIVE-4-10'
        servers['10.125.60.28'] = 'JIVE-4-10'
        servers['10.125.60.29'] = 'JIVE-4-10'
        servers['10.125.60.30'] = 'JIVE-4-10'
        servers['10.125.60.31'] = 'JIVE-4-10'
        servers['10.125.60.32'] = 'JIVE-4-10'
        servers['10.125.60.33'] = 'JIVE-4-10'
        servers['10.125.60.34'] = 'JIVE-4-10'
        servers['10.125.60.35'] = 'JIVE-4-10'
        servers['10.125.60.36'] = 'JIVE-4-10'
        servers['10.125.60.37'] = 'JIVE-4-10'
        servers['10.125.60.38'] = 'JIVE-4-10'
        servers['10.125.60.39'] = 'JIVE-4-10'
        servers['10.125.60.40'] = 'JIVE-4-10'
        servers['10.125.60.41'] = 'JIVE-4-10'
        servers['10.125.60.42'] = 'JIVE-4-10'
        servers['10.125.60.43'] = 'JIVE-4-10'
        servers['10.125.60.44'] = 'JIVE-4-10'
        servers['10.125.60.45'] = 'JIVE-4-10'
        servers['10.125.60.46'] = 'JIVE-4-10'
        servers['10.119.60.1'] = 'JIVE-4-10'
        servers['10.119.60.2'] = 'JIVE-4-10'
        servers['10.119.60.3'] = 'JIVE-4-10'
        servers['10.119.60.4'] = 'JIVE-4-10'
        servers['10.119.60.5'] = 'JIVE-4-10'
        servers['10.119.60.12'] = 'JIVE-4-10'
        servers['10.119.60.13'] = 'JIVE-4-10'
        servers['10.119.60.14'] = 'JIVE-4-10'
        servers['10.119.60.15'] = 'JIVE-4-10'
        servers['10.119.60.16'] = 'JIVE-4-10'
        servers['10.119.60.17'] = 'JIVE-4-10'
        servers['10.119.60.18'] = 'JIVE-4-10'
        servers['10.119.60.19'] = 'JIVE-4-10'
        servers['10.119.60.20'] = 'JIVE-4-10'
        servers['10.119.60.21'] = 'JIVE-4-10'
        servers['10.119.60.22'] = 'JIVE-4-10'
        servers['10.119.60.23'] = 'JIVE-4-10'
        servers['10.119.60.24'] = 'JIVE-4-10'
        servers['10.119.60.25'] = 'JIVE-4-10'
        servers['10.119.60.26'] = 'JIVE-4-10'
        servers['10.119.60.27'] = 'JIVE-4-10'
        servers['10.119.60.28'] = 'JIVE-4-10'
        servers['10.119.12.1'] = 'JIVE-4-10'
        servers['10.120.60.1'] = 'JIVE-4-10'
        servers['10.120.60.2'] = 'JIVE-4-10'
        servers['10.120.60.3'] = 'JIVE-4-10'
        servers['10.120.60.4'] = 'JIVE-4-10'
        servers['10.120.60.5'] = 'JIVE-4-10'
        servers['10.120.60.12'] = 'JIVE-4-10'
        servers['10.120.60.13'] = 'JIVE-4-10'
        servers['10.120.60.14'] = 'JIVE-4-10'
        servers['10.120.60.15'] = 'JIVE-4-10'
        servers['10.120.60.16'] = 'JIVE-4-10'
        servers['10.120.60.17'] = 'JIVE-4-10'
        servers['10.120.60.18'] = 'JIVE-4-10'
        servers['10.120.60.19'] = 'JIVE-4-10'
        servers['10.120.60.20'] = 'JIVE-4-10'
        servers['10.120.60.21'] = 'JIVE-4-10'
        servers['10.120.60.22'] = 'JIVE-4-10'
        servers['10.120.60.23'] = 'JIVE-4-10'
        servers['10.120.60.24'] = 'JIVE-4-10'
        servers['10.120.60.25'] = 'JIVE-4-10'
        servers['10.120.60.26'] = 'JIVE-4-10'
        servers['10.120.60.27'] = 'JIVE-4-10'
        servers['10.120.60.28'] = 'JIVE-4-10'
        servers['10.122.12.1'] = 'JIVE-4-10'
        servers['10.122.60.1'] = 'JIVE-4-10'
        servers['10.122.60.2'] = 'JIVE-4-10'
        servers['10.122.60.3'] = 'JIVE-4-10'
        servers['10.122.60.4'] = 'JIVE-4-10'
        servers['10.122.60.5'] = 'JIVE-4-10'
        servers['10.122.60.12'] = 'JIVE-4-10'
        servers['10.122.60.13'] = 'JIVE-4-10'
        servers['10.122.60.14'] = 'JIVE-4-10'
        servers['10.122.60.15'] = 'JIVE-4-10'
        servers['10.122.60.16'] = 'JIVE-4-10'
        servers['10.122.60.17'] = 'JIVE-4-10'
        servers['10.122.60.18'] = 'JIVE-4-10'
        servers['10.122.60.19'] = 'JIVE-4-10'
        servers['10.122.60.20'] = 'JIVE-4-10'
        servers['10.122.60.21'] = 'JIVE-4-10'
        servers['10.122.60.22'] = 'JIVE-4-10'
        servers['10.122.60.23'] = 'JIVE-4-10'
        servers['10.122.60.24'] = 'JIVE-4-10'
        servers['10.122.60.25'] = 'JIVE-4-10'
        servers['10.122.60.26'] = 'JIVE-4-10'
        servers['10.122.60.27'] = 'JIVE-4-10'
        servers['10.122.60.28'] = 'JIVE-4-10'
#        servers['10.123.60.1'] = 'JIVE-4-10'
#        servers['10.123.60.2'] = 'JIVE-4-10'
#        servers['10.123.60.3'] = 'JIVE-4-10'
#        servers['10.123.60.4'] = 'JIVE-4-10'
#        servers['10.123.60.5'] = 'JIVE-4-10'
#        servers['10.123.60.6'] = 'JIVE-4-10'
#        servers['10.123.60.7'] = 'JIVE-4-10'
#        servers['10.123.60.8'] = 'JIVE-4-10'
#        servers['10.123.60.9'] = 'JIVE-4-10'
#        servers['10.123.60.10'] = 'JIVE-4-10'
#        servers['10.123.60.11'] = 'JIVE-4-10'
#        servers['10.123.60.12'] = 'JIVE-4-10'
#        servers['10.123.60.13'] = 'JIVE-4-10'
#        servers['10.123.60.14'] = 'JIVE-4-10'
#        servers['10.123.60.15'] = 'JIVE-4-10'
#        servers['10.123.60.16'] = 'JIVE-4-10'
#        servers['10.123.60.17'] = 'JIVE-4-10'
#        servers['10.123.60.18'] = 'JIVE-4-10'
#        servers['10.123.60.19'] = 'JIVE-4-10'
        #servers['10.123.60.20'] = 'JIVE-4-10'
        #servers['10.123.60.21'] = 'JIVE-4-10'
        #servers['10.123.60.22'] = 'JIVE-4-10'
        #servers['10.123.60.23'] = 'JIVE-4-10'
        #servers['10.123.60.24'] = 'JIVE-4-10'
        #servers['10.123.60.25'] = 'JIVE-4-10'
        #servers['10.123.60.26'] = 'JIVE-4-10'
        #servers['10.123.60.27'] = 'JIVE-4-10'
        #servers['10.123.60.28'] = 'JIVE-4-10'
        #servers['10.123.60.29'] = 'JIVE-4-10'
        #servers['10.123.60.30'] = 'JIVE-4-10'
        #servers['10.123.60.31'] = 'JIVE-4-10'
        #servers['10.123.60.32'] = 'JIVE-4-10'
        #servers['10.123.60.33'] = 'JIVE-4-10'
        return servers

def main():
    p = jiveRealTimeManager()
    p.start()
    reactor.callWhenRunning(Countdown().count)
    reactor.run()
if __name__ == "__main__":
    log_level = logging.INFO
    log = logging.getLogger('jive.stats')
    setup_logging(log_level)
    rest = WebConnection({
        'method' : 'POST',
        #'uri' : 'http://10.199.8.1:8880/form',
        #mostrecent#'uri' : 'http://icalls.getjive.com:8080/update',
        'uri' : 'http://icalls-internal:8080/update',
        #'uri' : 'http://54.236.216.56:8080/update',
        #'uri' : 'http://ws.bretep.me:8080/update',
        'headers' : {'Content-Type': 'application/json'}
        })
    servers = {}
    main()
