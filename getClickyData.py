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

def getMonths(beginDate, endDate):
	startMonth = beginDate.month
	endMonths  = (endDate.year-beginDate.year)*12 + endDate.month+1
	return [datetime(year=yr, month=mn, day=1).strftime('%Y-%m') for (yr, mn) in (
			((m - 1) / 12 + beginDate.year, (m - 1) % 12 + 1) for m in range(startMonth, endMonths)
			)]

def getClickyData(month):
	# For more info: http://clicky.com/help/api
	requested = urllib2.Request('http://api.clicky.com/api/stats/4?' + 
		'site_id=' + config.site_id + '&sitekey=' + config.sitekey + 
		'&type=visitors,actions&date=' + month +'&output=xml')
	try:
		response = urllib2.urlopen( requested )
		return parseClickyData(response)
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
	print beginDate, endDate
	
	months = getMonths(beginDate, endDate)
	print months
	xmlContent = ''
	
	now = datetime.now()
	
	for month in months:
		monthFile = 'cache/' + month + '.xml'
		if os.path.isfile():
			try:
				mtime = os.path.getmtime(monthFile)
			except OSError:
				mtime = 0
			modified = datetime.fromtimestamp(mtime)
			
			if modified.year < now.year or 
			   (modified.year == now.year and modified.month < now.month):
				
				with open(monthFile) as f:
					xmlContent += f.read()
			else:
				xmlContent += getClickyData(month)
		else:
			xmlContent += getClickyData(month)
	
	return xmlContent



# createTree: creates an elementTree object of an xml file and returns it
# needs: error handling (possibly incorporate explicit encoding)
def createTree(text):
	return ET.fromstring(text)
