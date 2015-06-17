import tornado.ioloop
import tornado.web
import codecs
try:
    import xml.etree.cElementTree as ET
except ImportError:
    import xml.etree.ElementTree as ET
    
from getClickyData import getClickyData
from generateCounterXML import generateCounterXML


class MainHandler(tornado.web.RequestHandler):
	def post(self):
		raw_data = self.request.body
		
		#print raw_data
		
		clickyXML = getClickyData(raw_data)
		
		self.write(generateCounterXML(clickyXML))


application = tornado.web.Application([
	(r"/", MainHandler),
])

if __name__ == "__main__":
	application.listen(8888)
	tornado.ioloop.IOLoop.current().start()
