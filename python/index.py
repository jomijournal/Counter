import tornado.ioloop
import tornado.web
import codecs
try:
    import xml.etree.cElementTree as ET
except ImportError:
    import xml.etree.ElementTree as ET
    
from getClickyData import parseRequest
from generateCounterXML import generateCounterXML


class MainHandler(tornado.web.RequestHandler):
	def post(self):
		raw_data = self.request.body
		
		#print raw_data
		
		clickyXMLString = parseRequest(raw_data)
		
		self.write(generateCounterXML(clickyXMLString))


application = tornado.web.Application([
	(r"/", MainHandler),
])

if __name__ == "__main__":
	application.listen(8888)
	tornado.ioloop.IOLoop.current().start()
