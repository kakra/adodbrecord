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
	# Based on Rails' inflectors.
	#
	# This class is used to inflect strings.

	require_once("Inflections.module.php");

	class Inflector {

		function &inflections() {
			return Singleton::instance("Inflections");
		}

		# pluralizes a string
		function pluralize($string) {
			$inflections =& Inflector::inflections();
			if (in_array(strtolower($string), $inflections->uncountables)) return $string;
			foreach ($inflections->plurals as $plural) {
				list($rule, $replacement) = $plural;
				# This looks redundant but is needed for PHP4 compatibility
				if (preg_match($rule, $string)) {
					$result = preg_replace($rule, $replacement, $string, 1);
					break;
				}
			}
			if (!isset($result)) $result = $string;
			return $result;
		}

		# singularizes a string
		function singularize($string) {
			$inflections =& Inflector::inflections();
			if (in_array(strtolower($string), $inflections->uncountables)) return $string;
			foreach ($inflections->singulars as $singular) {
				list($rule, $replacement) = $singular;
				# This looks redundant but is needed for PHP4 compatibility
				if (preg_match($rule, $string)) {
					$result = preg_replace($rule, $replacement, $string, 1);
					break;
				}
			}
			if (!isset($result)) $result = $string;
			return $result;
		}

		# converts camel case to underscores
		function underscore($string) {
			$string = str_replace("::", "/", $string);
			$string = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\1_\2', $string);
			$string = preg_replace('/([a-z\d])([A-Z])/', '\1_\2', $string);
			return strtolower($string);
		}

		# converts class name to table name
		function tableize($string) {
			return Inflector::pluralize(Inflector::underscore($string));
		}
	}

?>