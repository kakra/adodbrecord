<?php
	# AdoDBRecord -- an ActiveRecord look-alike in PHP using AdoDB
	#
	# Author: Kai Krakow <kai@kaishome.de>
	# Version 0.1
	#
	# Disclaimer: By using this software you agree to the terms of GPL2:
	# http://www.gnu.org/licenses/gpl-2.0.html
	#
	# You need the following software to run AdoDBRecord:
	# http://phplens.com/adodb/index.html

	require_once("adodb.inc.php"); # min. v4.56

	# FIXME initiate your connection here
#	$_adodb_conn = &ADONewConnection($database[type]);
#	$_adodb_conn->Connect($database[host],$database[user],$database[password],$database[db_name]);
#	$_adodb_conn->debug = true;

	# Return class name derived from backtrace because php isn't able
	# to return the correct one (read: the one we need) in static call implementations
	function _class_name() {
		$backtrace = debug_backtrace();
		while ($a = next($backtrace)) { // first always ignored
			if (!empty($a["class"])) return $a["class"];
		}
		return NULL;
	}

	# Helper function to return a global database connection to AdoDB
	function &_adodb_conn() {
		global $_adodb_conn;
		return $_adodb_conn;
	}

	class AdoDBRecord {
		var $_attributes = array (); # holds the attributes
		var $_new_record = true; # if this is a new record

		# initializer
		function AdoDBRecord($attributes = false) {
			if ($attributes) $this->_attributes = $attributes;
		}

		# return the id of this record as where-clause or false if new
		function _id() {
			if ($this->_new_record) return false;
			return sprintf("`id` = %d" ,$this->_attributes["id"]);
		}

		# returns an assoziative array
		# FIXME should probably better return instances of _class_name()
		function find_all($options = false) {
			$conn = _adodb_conn();
			$append_sql = "";
			if ($options) $append_sql = " ${options}";
			return $conn->GetAll("SELECT * FROM `" . _class_name() . "`${append_sql}");
		}

		# returns the one record found by $id
		# as an instance of _class_name()
		function find($id) {
			$conn = _adodb_conn();
			$class = _class_name();
			$obj = new $class($conn->GetRow("SELECT * FROM `" . _class_name() . "` WHERE `id` = ?", array($id)));
			$obj->_new_record = false;
			return $obj;
		}

		# returns the last error message of the db connection
		function errmsg() {
			$conn = _adodb_conn();
			return $conn->ErrorMsg();
		}

		# saves the record by update or insert depending on _new_record
		# this automagically adds updated_at and created_at which are send
		# to the db only if the columns exist (AdoDB automagic in AutoExecute())
		# _new_record gets cleared on successful save
		function save() {
			$conn = _adodb_conn();
			$this->_attributes["updated_at"] = mktime();
			if ($this->_new_record)
			{
				$this->_attributes["created_at"] = mktime();
				if ($res = $conn->AutoExecute(_class_name(), $this->_attributes, 'INSERT'))
				{
					$this->_attributes["id"] = $conn->Insert_ID();
					$this->_new_record = false;
				}
				return $res;
			}
			return $conn->AutoExecute(_class_name(), $this->_attributes, 'UPDATE', $this->_id());
		}
	}
?>