# -*- coding: utf-8 -*- 

import MySQLdb as sql
import os

class Rules:
	__description = None
	__preprocess = None
	__postprocess = None
	__query = None
	__message = None
	__output = None
	__active = None
	__shellcmd = None
	__runshell = None


	def reset( self ):
		self.__description = None
		self.__preprocess = None
		self.__postprocess = None
		self.__query = None
		self.__message = None
		self.__output = None
		self.__active = None
		self.__shellcmd = None
		self.__runshell = None


	def connect( self, host, username, password, database ):
		self.connection = sql.connect( host, username, password, database )
		self.cursor = self.connection.cursor()


	def disconnect( self ):
		self.connection.close()


	def get_rule_data( self, ruleid ):
		self.cursor.execute( "select rule_desc \
                              ,      rule_query \
                              ,      rule_message \
                              ,      rule_active \
                              ,      rule_preproc \
                              ,      rule_postproc \
                              ,      rule_shellcmd \
                              ,      rule_run_shell \
                              from   rules \
                              where  rule_id = {0}".format( ruleid ) )

		row = self.cursor.fetchone()

		self.__description = row[0]
		self.__query = row[1]
		self.__message = row[2]
		self.__active = row[3]
		self.__preprocess = row[4]
		self.__postprocess = row[5]
		self.__shellcmd = row[6]
		self.__runshell = row[7]


	def run_rule( self, ruleid ):
		self.reset()
		self.get_rule_data( ruleid )

		if ( self.__active == 'Y' ):
			# Clear any previous output
			self.__output = None

			# Load last used data into variable
			self.cursor.execute( "select rule_last_used into @last_used from rules where rule_id = {0}".format( ruleid ) )

			# Execute pre process
			if ( self.__preprocess is not None and len( self.__preprocess ) > 0 ):
				self.cursor.execute( self.__preprocess )

			# Execute the actual rule
			rule_result = self.cursor.execute( self.__query )

			# Format the message
			if ( self.cursor.rowcount == 1 ):
				self.__output = self.__message % ( self.cursor.fetchone() )

			# Execute post process
			if ( self.__postprocess is not None and len( self.__postprocess ) > 0 ):
				self.cursor.execute( self.__postprocess )

			# Update last usage timestamp for rule
			self.cursor.execute( "update rules set rule_last_used=now() where rule_id = {0}".format( ruleid ) )
			self.connection.commit()

			# Remove variable
			self.cursor.execute( "set @last_used = null;" )

			# Result result message
			return ( self.__output is not None and len( self.__output ) > 0 )

		# Return failed indicator
		return false


	def getDescription( self ):
		return self.__description


	def getOutput( self ):
		return self.__output


	def runShellCmd( self ):
		if ( self.__shellcmd is not None and len( self.__shellcmd ) > 0 ):
			if ( self.__runshell == 'never' ):
				# Nothing to do
				pass
			elif ( self.__runshell == 'always' ):
				os.system( self.__shellcmd )
			elif ( self.__runshell == 'results' ):
				if ( self.__output is not None and len( self.__output ) > 0 ):
					os.system( self.__shellcmd )


	def getActive( self ):
		return self.__active


	def __init__( self, host, username, password, database ):
		self.connect( host, username, password, database )


	def __del__( self ):
		self.disconnect()
