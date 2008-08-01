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
	# This class is used to "magically" extend derived classes by additional
	# methods for attribute setters and getters and other functions like method
	# name parsers. This works by registering hooks which define derived classes
	# in global space and by defining base methods.

	require_once("AdoDBRecord_BaseImplementer.class.php");

	class AdoDBRecord_Base {

		function _setup() {}

		# register the hook which defines a derived class
		function register_hooks() {
			stream_wrapper_register("AdoDBRecord", "AdoDBRecord_BaseImplementer")
			   	or die("Cannot register extension hook.");
		}
	}
?>
