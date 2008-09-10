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
	# This class is used to provide singleton access to classes and objects. It does
	# not enforce this behaviour however because classes need not to inherit from
	# this class to create singleton instances of them.
	#
	# References:
	# http://en.wikipedia.org/wiki/Singleton_pattern
	#
	# TODO: deprecate _class_name()

	require_once("AdoDBRecord/Tools.module.php");

	# Return class name derived from backtrace because php isn't able
	# to return the correct one (read: the one we need) in static call implementations
	function _class_name($skip = 0) {
		$backtrace = debug_backtrace();
		while ($a = next($backtrace)) { // first always ignored
			if ($skip > 0) {
				$skip--;
				continue;
			}
			if (!empty($a["class"])) return $a["class"];
		}
		return NULL;
	}

	class Singleton {
		function &instance($class = false) {
			if (!$class) {
				if (isset($this)) return $this;
				# FIXME deprecate _class_name()
				$class = _class_name(1);
			}
			$class = ucfirst(strtolower($class));
			$instance = "__{$class}";
			global $$instance;
			if (isset($$instance)) return $$instance;
			return $$instance = new $class();
		}
	}
?>
