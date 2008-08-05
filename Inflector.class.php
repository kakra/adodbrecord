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

	class Inflections {
		var $irregulars = array();
		var $plurals = array();
		var $singulars = array();
		var $uncountables = array();

		function instance() {
			global $Inflections;
			if (defined($this)) return $this;
			if (!$Inflections) $Inflections = new Inflections();
			return $Inflections;
		}

		function irregular($singular, $plural) {
			if (strtoupper(substr($singular, 0, 1)) == strtoupper(substr($plural, 0, 1))) {
				Inflections::instance #here#
			}
			array_unshift(Inflections::instance()->irregulars,
		}
	}

	class Inflector {

		# pluralizes a string
		function pluralize($string) {
			if (in_array(strtolower($string), Inflections::instance()->uncountables)) return $string;
			foreach (Inflections::instance()->plurals as $rule => $replacement) {
				if ($result = preg_replace($rule, $replacement, $string)) if ($result != $string) break;
			}
			if (!$result) $result = $string;
			return $result;
		}
	}

	Inflections::irregular("person", "people");
	Inflections::irregular("man", "men");
	Inflections::irregular("child", "children");
	Inflections::irregular("sex", "sexes");
	Inflections::irregular("move", "moves");
	Inflections::irregular("cow", "kine");


		var $plurals = array(
			'/$/' => 's',
			'/s$/i' => 's',
			'(ax|test)is$/i' => '\1es',
			'/(octop|vir)us$/i' => '\1i',
			'/(alias|status)$/i' => '\1es',
			'(bu)s$/i' => '\1ses',
			'(buffal|tomat)o$/i' => '\1oes',
			'([ti])um$/i' => '\1a',
			'/sis$/i' => 'ses',
			'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
			'/(hive)$/i' => '\1s',
			'/([^aeiouy]|qu)y$/i' => '\1ies',
			'/(x|ch|ss|sh)$/i' => '\1es',
			'/(matr|vert|ind)(?:ix|ex)$/i' => '\1ices',
			'/([m|l])ouse$/i' => '\1ice',
			'/^(ox)$/i' => '\1en',
			'/(quiz)$/i' => '\1zes'
		);

		var $singulars = array(
			'/s$/i' => '',
			'/(n)ews$/i' => '\1ews',
			'/([ti])a$/i' => '\1um',
			'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
			'/(^analy)ses$/i' => '\1sis',
			'/([^f])ves$/i' => '\1fe',
			'/(hive)s$/i' => '\1',
			'/(tive)s$/i' => '\1',
			'/([lr])ves$/i' => '\1f',
			'/([^aeiouy]|qu)ies$/i' => '\1y',
			'/(s)eries$/i' => '\1eries',
			'/(m)ovies$/i' => '\1ovie',
			'/(x|ch|ss|sh)es$/i' => '\1',
			'/([m|l])ice$/i' => '\1ouse',
			'/(bus)es$/i' => '\1',
			'/(o)es$/i' => '\1',
			'/(shoe)s$/i' => '\1',
			'/(cris|ax|test)es$/i' => '\1is',
			'/(octop|vir)i$/i' => '\1us',
			'/(alias|status)es$/i' => '\1',
			'/^(ox)en/i' => '\1',
			'/(vert|ind)ices$/i' => '\1ex',
			'/(matr)ices$/i' => '\1ix',
			'/(quiz)zes$/i' => '\1'
		);

		var $uncountables = array("equipment", "information", "rice", "money", "species", "series", "fish", "sheep");
		}

?>