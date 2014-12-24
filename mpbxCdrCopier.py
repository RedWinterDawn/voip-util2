#!/usr/bin/env python26
import hashlib
import os, sys
import shutil
import logging, logging.handlers

#IGNORE the following directories -- NOTE: Chicago Legacy should always be ignored because it's the destination
ignoreList = ['pvu', 'geg', 'chicago-legacy'] 
basedir = '/cluster/sites/{0}/cdrs'
sourcedir = '/cluster/sites/{0}/cdrs/{1}/'
destdir = '/cluster/nfs/pbx/cdrs/{0}/'
basename = 'Master.csv'
rotateCount = 65
logLevel = logging.INFO

def getLogging(level):
	logger = logging.getLogger('cdrCopier')
	logger.setLevel(level)
	fmt = logging.Formatter('%(asctime)s %(name)s %(levelname)s %(message)s')
	sl_handler = logging.handlers.RotatingFileHandler('/var/log/cdrCopier.log', maxBytes=40960000)
	sl_handler.setLevel(level)
	sl_handler.setFormatter(fmt)
	logger.addHandler(sl_handler)

	return logger

def getChecksum(path, file):
	filepath = path + file
	blocksize = 65536
	hasher = hashlib.md5()
	try:	
		with open (filepath, 'rb') as afile:
			buf = afile.read(blocksize)
			while len(buf) > 0:
				hasher.update(buf)
				buf = afile.read(blocksize)
				
		return hasher.hexdigest()
	except IOError:
		logger.error(" IOError on path {0}{1}, returning unmatchable checksum.".format(path, file))
		return -1

def getFileLists():
	fileLists = {}
	for dir in os.listdir('/cluster/sites'):
		if dir in ignoreList:
			logger.info("Skipping {0}".format(dir))
			continue
		logger.info("Adding {0}".format(dir))
		fileLists[dir] = os.listdir(basedir.format(dir)) 

	return fileLists

def getOffset(sourcePath, destPath):
	destSum = getChecksum(destPath, basename + ".1")
	if getChecksum(sourcePath, basename + ".1") == destSum:
		logger.info(" File at {0} is already up to date".format(destPath))
		return 0
	for offset in range(2, 6):
		if getChecksum(sourcePath, basename + "." + str(offset)) == destSum:
			return offset - 1
	logger.warn(" Offset is more than 5 days, please manually copy files for {0}".format(sourcePath))
	return -1

def rotateFiles(path, number, offset):
	for i in xrange(number, 0, -1):
		logger.debug(" Renaming {0}{1}.{2} to .{3}".format(path, basename, i, i+offset))
		try:
			os.rename("{0}{1}.{2}".format(path, basename, i), "{0}{1}.{2}".format(path, basename, i+offset))
			pass
		except:
			logger.error(" Encountered an error while rotating files in {0}".format(path))
			return False
	return True

def copyFiles(sourcePath, destPath, count):
	for i in range(1, count + 1):
		logger.debug(" Copy {0}{1}.{2}, {3}".format(sourcePath, basename, i, destPath))
		try:
			shutil.copy2("{0}{1}.{2}".format(sourcePath, basename, i), destPath)
			pass
		except:
			logger.error(" Encountered an error while copying {0}{1}.{2}".format(sourcePath, basename, i))

logger = getLogging(logLevel)
sitelist = getFileLists()

for site in sitelist:
	for mpbx in sitelist[site]:
		offset = getOffset(sourcedir.format(site, mpbx), destdir.format(mpbx))
		if offset:
			rotated = rotateFiles(destdir.format(mpbx), rotateCount, offset)
			if rotated:
				copyFiles(sourcedir.format(site, mpbx), destdir.format(mpbx), offset)
