# -*- coding: utf-8 -*-
# file: getClickyData.py
# date: 6/17/15
# purpose: Retrieve an xml file from the Clicky API of JoMI's stats over a 
#			user-specified time period. Returns xml as an elementTree object

import urllib2
from datetime import datetime
try:
	import xml.etree.cElementTree as ET
except ImportError:
	import xml.etree.ElementTree as ET
	
import config
import os
import MySQLdb as msql

def getMonths(beginDate, endDate):
	startMonth = beginDate.month
	endMonths  = (endDate.year-beginDate.year)*12 + endDate.month+1
	return [datetime(year=yr, month=mn, day=1).strftime('%Y-%m') for (yr, mn) in (
			((m - 1) / 12 + beginDate.year, (m - 1) % 12 + 1) for m in range(startMonth, endMonths)
			)]

def getIPsFromID(rID):
	con = False
	ipList = ''
	try:
		con =  msql.connect(host=config.db['host'],
							   port=config.db['port'],
							   user=config.db['user'],
							   passwd=config.db['passwd'],
							   db=config.db['db'])
		cur = con.cursor()
		cur.execute('SELECT start, end FROM SELECT wp_institution_ips WHERE location_id=' + rID)
		
		rows = cur.fetchall()
		
		for row in rows:
			ipList += row[0] + ',' + row[1] +'|'
		
	except msql.Error, e:
		print "Error %d: %s" % (e.args[0],e.args[1])
		ipList = '2080327680,2080331775'
	
	if con:
		con.close()
	
	return ipList

def parseClickyDate(data):
	clickyDataTree = createTree(data)
	visitorCount = clickyDataTree.find('type[@type="segmentation"]/date/item/value')
	if(visitorCount):
		return int(visitorCount.text)
	return 0

def getClickyData(months, ips):
	
	print 'http://api.clicky.com/api/stats/4?' + 
		'site_id='+ config.site_id + '&sitekey=' + config.sitekey + 
		'&type=visitors-list&date=' + month + 
		'&output=xml&ip_address=' + ips
	
	# For more info: http://clicky.com/help/api
	requested = urllib2.Request('http://api.clicky.com/api/stats/4?' + 
		'site_id=' + config.site_id + '&sitekey=' + config.sitekey + 
		'&type=visitors-list&date=' + month + 
		'&output=xml&ip_address=' + ips)
	try:
		response = urllib2.urlopen( requested )
		return parseClickyData(response.read())
	except urllib2.HTTPError as error:
		print 'The server couldn\'t fulfill the request.'
		print 'Error code: ', error.code
		return ''
	except urllib2.URLError as error:
		print 'Failed to reach a server.'
		print 'Reason: ', error.reason
		return ''

# Given the user-provided xml doc, returns an elementTree object of JoMI's
# daily visitors and actions stats during the specified time period from Clicky
def parseRequest(info):
	requestXML = createTree(info)
	beginDate = datetime.strptime(requestXML.find('ReportDefinition/Filters/UsageDateRange/Begin').text.strip(),
								  '%Y-%m-%d')
	endDate   = datetime.strptime(requestXML.find('ReportDefinition/Filters/UsageDateRange/End').text.strip(),
								  '%Y-%m-%d')
	
	requestorID = requestXML.find('Requestor/ID').text 
	
	ips = getIPsFromID(requestorID)
	print beginDate, endDate
	
	months = getMonths(beginDate, endDate)
	
	data = []
	
	for month in months:
		data.append((month, getClickyData(month, ips)))
		
	return data



# createTree: creates an elementTree object of an xml file and returns it
# needs: error handling (possibly incorporate explicit encoding)
def createTree(text):
	return ET.fromstring(text)
