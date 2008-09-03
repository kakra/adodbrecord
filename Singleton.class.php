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
	# This class is used to provide singleton access to classes and objects.

	require_once("AdoDBRecord/Tools.module.php");

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
