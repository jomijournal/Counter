try:
    import xml.etree.cElementTree as ET
except ImportError:
    import xml.etree.ElementTree as ET

def generateCounterXML(clickyXML):
	return ET.tostring(clickyXML, encoding='utf8', method='xml')