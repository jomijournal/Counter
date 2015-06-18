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




# Given the user-provided xml doc, returns an elementTree object of JoMI's
# daily visitors and actions stats during the specified time period from Clicky
def getClickyData(info):
	requestXML = createTree(info)
	beginDate = datetime.strptime(requestXML.find('ReportDefinition/Filters/UsageDateRange/Begin').text.strip(),
								  '%Y-%m-%d')
	endDate   = datetime.strptime(requestXML.find('ReportDefinition/Filters/UsageDateRange/End').text.strip(),
								  '%Y-%m-%d')
	print beginDate, endDate
	# return requestXML
	# Date range request format: YYYY-MM-DD,YYYY-MM-DD, e.g. 2015-05-01,2015-05-31
	# According to API:
		# The maximum range is 750 days for Pro users, and 31 days for everyone else.
	# For more info: http://clicky.com/help/api
	requested = urllib2.Request('http://api.clicky.com/api/stats/4?' + 
		'site_id=' + config.site_id + '&sitekey=' + config.sitekey + 
		'93c104e29de28bd9&type=visitors,actions&' +
		'date=' + beginDate + ',' + endDate + '&daily=1&output=xml')
	try:
		response = urllib2.urlopen( requested )
	except urllib2.HTTPError as error:
		print 'The server couldn\'t fulfill the request.'
		print 'Error code: ', error.code
	except urllib2.URLError as error:
		print 'Failed to reach a server.'
		print 'Reason: ', error.reason
	else:
		return createTree( response.read() )

	return 'Failed to get Clicky data'



# createTree: creates an elementTree object of an xml file and returns it
# needs: error handling (possibly incorporate explicit encoding)
def createTree(text):
	return ET.fromstring(text)