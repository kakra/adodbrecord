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

	require_once("AdoDBRecord/Tools.module.php");

	class Singleton {
		function &instance($class = false) {
			if ($class === false) {
				if (!isset($this)) die("Singleton::instance() called from non-instanciated context.");
				$class = get_class($this);
			}
			$class = ucfirst(strtolower($class));
			$instance = "__{$class}";
			global $$instance;
			if (isset($$instance)) return $$instance;
			return $$instance = new $class();
		}
	}
?>
