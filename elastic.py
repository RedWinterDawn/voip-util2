#!/usr/bin/python2.6

import elasticsearch
from datetime import datetime

try:
    es = elasticsearch.Elasticsearch(["10.130.8.8", "10.130.8.7"], sniff_on_start=True)
except:
   print 'faild to connect to es'

es.index(index="logstash-2015.08.04", doc_type="test-type", id=42, body={"any": "data", "timestamp": datetime.now()})


