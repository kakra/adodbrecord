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
			return Module::instance("Inflections");
		}

		# pluralizes a string
		function pluralize($string) {
			$inflections = Inflector::inflections();
			if (in_array(strtolower($string), $inflections->uncountables)) return $string;
			foreach ($inflections->plurals as $plural) {
				list($rule, $replacement) = $plural;
				$result = preg_replace($rule, $replacement, $string, 1, $count);
				 if ($count) break;
			}
			if (!isset($result)) $result = $string;
			return $result;
		}

		# singularizes a string
		function singularize($string) {
			$inflections = Inflector::inflections();
			if (in_array(strtolower($string), $inflections->uncountables)) return $string;
			foreach ($inflections->singulars as $singular) {
				list($rule, $replacement) = $singular;
				$result = preg_replace($rule, $replacement, $string, 1, $count);
				 if ($count) break;
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

	$inflect = Inflector::inflections();

	$inflect->plural('/$/', 's');
	$inflect->plural('/s$/i', 's');
	$inflect->plural('/(ax|test)is$/i', '\1es');
	$inflect->plural('/(octop|vir)us$/i', '\1i');
	$inflect->plural('/(alias|status)$/i', '\1es');
	$inflect->plural('/(bu)s$/i', '\1ses');
	$inflect->plural('/(buffal|tomat)o$/i', '\1oes');
	$inflect->plural('/([ti])um$/i', '\1a');
	$inflect->plural('/sis$/i', 'ses');
	$inflect->plural('/(?:([^f])fe|([lr])f)$/i', '\1\2ves');
	$inflect->plural('/(hive)$/i', '\1s');
	$inflect->plural('/([^aeiouy]|qu)y$/i', '\1ies');
	$inflect->plural('/(x|ch|ss|sh)$/i', '\1es');
	$inflect->plural('/(matr|vert|ind)(?:ix|ex)$/i', '\1ices');
	$inflect->plural('/([m|l])ouse$/i', '\1ice');
	$inflect->plural('/^(ox)$/i', '\1en');
	$inflect->plural('/(quiz)$/i', '\1zes');

	$inflect->singular('/s$/i', '');
	$inflect->singular('/(n)ews$/i', '\1ews');
	$inflect->singular('/([ti])a$/i', '\1um');
	$inflect->singular('/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i', '\1\2sis');
	$inflect->singular('/(^analy)ses$/i', '\1sis');
	$inflect->singular('/([^f])ves$/i', '\1fe');
	$inflect->singular('/(hive)s$/i', '\1');
	$inflect->singular('/(tive)s$/i', '\1');
	$inflect->singular('/([lr])ves$/i', '\1f');
	$inflect->singular('/([^aeiouy]|qu)ies$/i', '\1y');
	$inflect->singular('/(s)eries$/i', '\1eries');
	$inflect->singular('/(m)ovies$/i', '\1ovie');
	$inflect->singular('/(x|ch|ss|sh)es$/i', '\1');
	$inflect->singular('/([m|l])ice$/i', '\1ouse');
	$inflect->singular('/(bus)es$/i', '\1');
	$inflect->singular('/(o)es$/i', '\1');
	$inflect->singular('/(shoe)s$/i', '\1');
	$inflect->singular('/(cris|ax|test)es$/i', '\1is');
	$inflect->singular('/(octop|vir)i$/i', '\1us');
	$inflect->singular('/(alias|status)es$/i', '\1');
	$inflect->singular('/^(ox)en/i', '\1');
	$inflect->singular('/(vert|ind)ices$/i', '\1ex');
	$inflect->singular('/(matr)ices$/i', '\1ix');
	$inflect->singular('/(quiz)zes$/i', '\1');

	$inflect->irregular("person", "people");
	$inflect->irregular("man", "men");
	$inflect->irregular("child", "children");
	$inflect->irregular("sex", "sexes");
	$inflect->irregular("move", "moves");
	$inflect->irregular("cow", "kine");

	$inflect->uncountable(array("equipment", "information", "rice", "money", "species", "series", "fish", "sheep"));

?>