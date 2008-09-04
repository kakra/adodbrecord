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
	# This is the PHP4 version which needs some dirty tricks to get it working:
	#   * Mark the class overloaded (not so dirty but pretty unknown)
	#   * Suppress the "unknown <foobar>" warning
	#   * Trigger an error if something really wasn't found
	#	* Define magic methods using the PHP4 interface and map them to more generic versions

	if (!function_exists("overload"))
		die("Missing 'overload' PHP extension. Please enable it.");

	require_once("Base.class.php");

	class AdoDBRecord_Overloadable {

		function __call($method, $args, &$return) {
			$return = AdoDBRecord_Base::parse_method($method, $args);
			return true;
		}

		function __get($property, &$return) {
			$return = AdoDBRecord_Base::parse_member($property);
			return true;
		}

		function __set($property, $value) {
			AdoDBRecord_Base::parse_member($property, $value);
			return true;
		}
	}

	/* enable __call(), __get() and __set() methods */
	overload('AdoDBRecord_Overloadable');
?>
