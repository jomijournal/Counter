try:
    import xml.etree.cElementTree as ET
except ImportError:
    import xml.etree.ElementTree as ET

def generateCounterXML(clickyXML):
	return clickyXML
	#if clickyXML.find('response[@status="ok"]'):
		#for elem in clickyXML.iterfind('response/type[status="visitors"]'):
			#i = 1
			## how do we want to store visitor data?
			## could use array where each element represents visitors during month
			## Only include whole months?
			## what do with the "actions" stats
	#return ET.tostring(clickyXML, encoding='utf8', method='xml')