#!/usr/bin/python
# -*- coding: utf-8 -*-

import MySQLdb
import sys

try:
    con =  MySQLdb.connect(host="45.55.246.16",port=3306,user="root2",passwd="jomicat1234",db="jomi")
    cur = con.cursor()
    cur.execute("SELECT VERSION()")

    ver = cur.fetchone()
    
    print "Database version : %s " % ver
    
except  MySQLdb.Error, e:
  
    print "Error %d: %s" % (e.args[0],e.args[1])
    sys.exit(1)
    
finally:    
        
    if con:    
        con.close()