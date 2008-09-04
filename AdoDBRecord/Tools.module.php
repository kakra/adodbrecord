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
	# This file holds some tools for initialization and configuration

	require_once("../Singleton.class.php");

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

	# Helper function to return a global database connection to AdoDB
	function &_adodb_conn() {
		global $_adodb_conn;
		return $_adodb_conn;
	}

	class AdoDBRecord_Tools extends Singleton {
		var $_column_cache = array();

		function &registration() {
			return Singleton::instance(__CLASS__);
		}

		# AdoDB version min. v4.56 is needed
		function version_check() {
			global $PREFIX_ADODB, $ADODB_vers;
			require_once("${PREFIX_ADODB}adodb.inc.php");
			sscanf($ADODB_vers, "V%d.%d %s", $v_major, $v_minor, $dummy);
			if ($v_major > 4) return;
			if (($v_major == 4) && ($v_minor >= "56")) return;
			die("AdoDBRecord: Your AdoDB version is too old. Requiring at least v4.56.");
		}

		function get_columns() {
			$registration =& AdoDBRecord_Tools::registration();
			$table = $this->_table_name;
			if (array_key_exists($table, $registration->_column_cache))
				return $registration->_column_cache[$table];
			$conn =& _adodb_conn();
			return $registration->_column_cache[$table] =& $conn->MetaColumnNames($table);
		}

		function init() {
			global $PREFIX_ADODB;
			require_once("${PREFIX_ADODB}adodb.inc.php");
			require_once("Base.class.php");
			$registration  = AdoDBRecord_Tools::registration();
			$registration->_column_cache = array();
			AdoDBRecord_Base::register_hooks();
		}
	}
?>
