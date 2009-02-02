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

	require_once("Singleton.class.php");
	require_once("Inflector.class.php");

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

		# converts an array to a sql parameter list suitable for "IN" conditionals, so it can be
		# passed to AdoDB correctly
		function convert_sql_params(&$sql, &$array) {
			$conn = _adodb_conn();

			$sql_array = explode("?", $sql);
			$sql = ""; $i = 0;
			foreach ($array as $k => $v) {
				$sql .= $sql_array[$i];
				if (is_array($v)) {
					unset($array[$k]);
					foreach ($v as $kp => $p) if (!is_numeric($p)) $v[$kp] = $conn->qstr($p);
					$sql .= join($v, ",");
				}
				else
					$sql .= "?";
				$i++;
			}
			$sql .= $sql_array[$i];
		}

		# parse conditions recursively
		function parse_conditions($conditions, &$parsed_conditions, &$parsed_params) {
			if (empty($conditions)) return;
			if (!is_array(reset($conditions))) {
				$parsed_conditions[] = array_shift($conditions);
				if (count($conditions) > 0) $parsed_params = array_merge($parsed_params, $conditions);
				return;
			}
			foreach ($conditions as $condition)
				AdoDBRecord_Tools::parse_conditions($condition, $parsed_conditions, $parsed_params);
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
			global $PREFIX_ADODB, $ADODB_FETCH_MODE;

			require_once("${PREFIX_ADODB}adodb.inc.php");
			$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

			require_once("Base.class.php");
			$registration  = AdoDBRecord_Tools::registration();
			$registration->_column_cache = array();
			AdoDBRecord_Base::register_hooks();
		}

		# check if the named property is a column property
		function is_column_property($name) {
			return in_array($name, $this->_columns);
		}

		# check if the named property is part of a has_many association
		function is_has_many_property($name) {
			return array_key_exists($name, $this->_has_many);
		}

		# check if the named property is part of a has_one association
		function is_has_one_property($name) {
			return array_key_exists($name, $this->_has_one);
		}

		# check if the named property is part of a has_many association
		function is_belongs_to_property($name) {
			return array_key_exists($name, $this->_belongs_to);
		}

		# check if the named property is part of any association
		function is_association_property($name) {
			return AdoDBRecord_Tools::is_belongs_to_property($name) ||
				AdoDBRecord_Tools::is_has_many_property($name) ||
				AdoDBRecord_Tools::is_has_one_property($name);
		}
	}
?>