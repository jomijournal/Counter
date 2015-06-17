import tornado.ioloop
import tornado.web
import codecs
try:
    import xml.etree.cElementTree as ET
except ImportError:
    import xml.etree.ElementTree as ET
    

import getClickyData


class MainHandler(tornado.web.RequestHandler):
	def post(self):
		raw_data = self.request.body
		
		
		clickyXML = getClickyData.getClickyData(rawData)
		
		self.write(generateCounterXML(clickyXML)

application = tornado.web.Application([
	(r"/", MainHandler),
])

if __name__ == "__main__":
	application.listen(8888)
	tornado.ioloop.IOLoop.current().start()
