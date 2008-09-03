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
	# This is the PHP5 version which does some convenient tricks:
	#   * Trigger an error if something really wasn't found
	#	* Define magic methods using the PHP5 interface and map them to more generic versions

	require_once("Base.class.php");

	class AdoDBRecord_Overloadable {
		
		private function __call($method, $args) {
			return AdoDBRecord_Base::parse_method($method, $args);
		}

		private function __get($property) {
			return AdoDBRecord_Base::parse_member($property);
		}

		private function __set($property, $value) {
			AdoDBRecord_Base::parse_member($property, $value);
		}
	}
?>
