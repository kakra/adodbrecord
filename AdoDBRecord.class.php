<?php
	# AdoDBRecord -- an ActiveRecord look-alike in PHP using AdoDB
	#
	# Author: Kai Krakow <kai@kaishome.de>
	# http://github.com/kakra/adodbrecord/
	# Version 0.4
	#
	# Disclaimer: By using this software you agree to the terms of GPLv2:
	# http://www.gnu.org/licenses/gpl-2.0.html
	#
	# You need the following software to run AdoDBRecord:
	# http://phplens.com/adodb/index.html

	global
		$PREFIX_ADODB, # set your adodb.inc.php include prefix if needed
		$ADODB_vers;

	require_once("${PREFIX_ADODB}adodb.inc.php");
	require_once("AdoDBRecord_Base.class.php");
	require_once("Inflector.class.php");

	# AdoDB version min. v4.56 is needed
	function _adodb_version_check()
	{
		global $ADODB_vers;
		sscanf($ADODB_vers, "V%d.%d %s", $v_major, $v_minor, $dummy);
		if ($v_major > 4) return;
		if (($v_major == 4) && ($v_minor >= "56")) return;
		die("AdoDBRecord: Your AdoDB version is too old. Requiring at least v4.56.");
	}

	# AdoDBRecord initialization functions
	function _adodb_record_init() {
		global $_adodb_column_cache;
		$_adodb_column_cache = array();
		AdoDBRecord_Base::register_hooks();
	}

	# FIXME initiate your connection here
#	$_adodb_conn = &ADONewConnection($database[type]);
#	$_adodb_conn->Connect($database[host],$database[user],$database[password],$database[db_name]);
#	$_adodb_conn->debug = true;

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

	_adodb_version_check();
	_adodb_record_init();

	class AdoDBRecord extends AdoDBRecord_Base {
		var $_attributes = array (); # holds the attributes
		var $_new_record = true; # if this is a new record
		var $_table_name = false;

		# initializer
		function AdoDBRecord($attributes = false) {
			global $_adodb_column_cache;
			$conn = _adodb_conn();

			parent::AdoDBRecord_Base();

			# TODO move code to seperate base class
			if (!$this->_table_name) $this->_table_name = Inflector::pluralize(_class_name());
			if ($_adodb_column_cache[$this->_table_name])
			$this->_columns = $conn->GetCol(sprintf("SHOW COLUMNS FROM `%s`", $this->_table_name()));

			if ($attributes) $this->_attributes = $attributes;
		}

		# logs an error
		# FIXME to be moved to seperate class
		function log_error($message, $priority = E_USER_NOTICE, $fatal = false) {
			trigger_error($message, $priority);
			if ($fatal) die($message);
		}

		# private attribute getter
		# returns the current value
		function _get_attribute($attribute, $db_only = true) {
			if (!$db_only || in_array($attribute, $this->_columns)) return $this->_attributes[$attribute];
			AdoDBRecord::log_error("column not found", E_USER_ERROR, true);
		}

		# private attribute setter
		# returns the new value
		function _set_attribute($attribute, $value, $db_only = true) {
			if (!$db_only || in_array($attribute, $this->_columns)) return $this->_attributes[$attributes] = $value;
			AdoDBRecord::log_error("column not found", E_USER_ERROR, true);
		}

		# instanciate and save a new object
		function create($attributes = false) {
			$class = _class_name();
			$obj = new $class(&$attributes);
			$obj->save();
			return $obj;
		}

		# get the class name of the instance or static invocation
		function _class() {
			return _class_name();
		}

		# return the id of this record as where-clause or false if new
		function _id() {
			if ($this->_new_record) return false;
			return sprintf("`id` = %d" ,$this->_attributes["id"]);
		}

		# returns an associative array
		# FIXME should probably better return instances of _class_name()
		# FIXME support to manually set table name
		function find_all($options = false) {
			$conn = _adodb_conn();
			$append_sql = "";
			if ($options) $append_sql = " ${options}";
			return $conn->GetAll("SELECT * FROM `" . _class_name() . "`${append_sql}");
		}

		# returns the one record found by $id
		# as an instance of _class_name()
		# FIXME support to manually set table name
		function &find($id) {
			$conn = _adodb_conn();
			$class = _class_name();
			if ($row = $conn->GetRow("SELECT * FROM `" . _class_name() . "` WHERE `id` = ?", array($id))) {
				$obj = new $class($row);
				$obj->_new_record = false;
				return $obj;
			}
			return NULL;
		}

		# returns the last error message of the db connection
		function errmsg() {
			$conn = _adodb_conn();
			return $conn->ErrorMsg();
		}

		# saves the record by update or insert depending on _new_record
		# this automagically adds updated_at and created_at which are sent
		# to the db only if the columns exist (AdoDB's automagic in AutoExecute())
		# _new_record gets cleared on successful save
		function save() {
			$conn = _adodb_conn();
			$this->_attributes["updated_at"] = mktime();
			if ($this->_new_record) {
				$this->_attributes["created_at"] = mktime();
				if ($res = $conn->AutoExecute($this->_table_name, $this->_attributes, 'INSERT')) {
					$this->_attributes["id"] = $conn->Insert_ID();
					$this->_new_record = false;
				}
				return $res;
			}
			return $conn->AutoExecute($this->_table_name, $this->_attributes, 'UPDATE', $this->_id());
		}

		# delete the instance from the database, sets _new_record to false to indicate it's no longer
		# stored in the database
		function delete() {
			$conn = _adodb_conn();
			if ($this->_new_record) return false;
			if ($res = $conn->Execute(sprintf("DELETE FROM `%s` WHERE %s", $this->_table_name, $this->_id())))
				$this->_new_record = true;
			return $res;
		}

		# destroy one or more id's by finding each id and running destroy() on it
		# if called on an instance it runs delete() on it
		function destroy($id) {
			$class = _class_name();
			if (is_array($id)) {
				foreach ($id as $one_id) eval(sprintf("$class::destroy(%d);", $one_id));
				return;
			}
			if (isset($this))
				return $this->delete();
			else {
				eval(sprintf("\$obj = $class::find(%d);", $id));
				return $obj->destroy();
			}
		}

		# updates the attributes by merging the new array with the existing
		# attributes and saves the object
		function update_attributes($attributes) {
			$this->_attributes = array_merge($this->_attributes, $attributes);
			return $this->save();
		}
	}
?>
