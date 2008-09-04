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
	# This class is used to commonly implement parsers for magical methods
	# (independent of php version).

	class AdoDBRecord_Overloadable_Parsers {

		function _parse_find_by($fields, $arguments) {
			# TODO do real stuff here
			echo("called _parse_find_by('$fields',".var_export($arguments, true).")\n");
		}

		function _parse_find_all_by($fields, $arguments) {
			# TODO do real stuff here
			echo("called _parse_find_all_by('$fields',".var_export($arguments, true).")\n");
		}
	}
?>