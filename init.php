<?php
	# This file is part of:
	# AdoDBRecord -- an ActiveRecord look-alike in PHP using AdoDB
	#
	# Author: Kai Krakow <kai@kaishome.de>
	# http://github.com/kakra/adodbrecord/
	#
	# Disclaimer: By using this software you agree to the terms of GPLv2:
	# http://www.gnu.org/licenses/gpl-2.0.html
	#
	# This file is an convenient intializer. It sets up the include path
	# and loads the main classes so it can be used right away. As an example
	# you can setup your database connection here. Note that the variable
	# holding the connection needs to be set in global scope.

	if ($ADODB_RECORD_OLD_INCLUDE = set_include_path(dirname(__FILE__) . '/lib/' . PATH_SEPARATOR . get_include_path())) {
		global $PREFIX_ADODB;
		if (!isset($PREFIX_ADODB)) $PREFIX_ADODB = "adodb/";
		require_once("lib/AdoDBRecord.class.php");
		set_include_path($ADODB_RECORD_OLD_INCLUDE);

		# FIXME initiate your connection here
#		global $_adodb_conn;
#		$_adodb_conn = ADONewConnection($database[type]);
#		$_adodb_conn->Connect($database[host],$database[user],$database[password],$database[db_name]);
#		$_adodb_conn->debug = true;
	}
	else
		die("Cannot set include path in " . __FILE__);
?>
