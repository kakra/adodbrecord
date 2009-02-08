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
	# This class is used to "magically" access undefined methods and properties
	# which map to finders, attributes, associations, etc.
	#
	# This simple stub only decides if the PHP4 or PHP5 compatible version needs
	# to be loaded.

	require_once("AdoDBRecord.class.php"); # make sure the defines are there

	if (defined("AR_PHP4_COMPAT"))
		require_once("AdoDBRecord/Overloadable.php4.class.php");
	else
		require_once("AdoDBRecord/Overloadable.php5.class.php");

?>