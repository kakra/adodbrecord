<?php
	# AdoDBRecord -- an ActiveRecord look-alike in PHP using AdoDB
	#
	# Author: Kai Krakow <kai@kaishome.de>
	# http://github.com/kakra/adodbrecord/
	# Version 0.5
	#
	# Disclaimer: By using this software you agree to the terms of GPLv2:
	# http://www.gnu.org/licenses/gpl-2.0.html
	#
	# You need the following software to run AdoDBRecord:
	# http://phplens.com/adodb/index.html
	#
	# Set global $PREFIX_ADODB to make AdoDBRecord find you adodb
	# installation, e.g. $PREFIX_ADODB = "adodb/" -- beware the final
	# slash "/" in the path name. This will look for "adodb/adodb.inc.php"
	# in your include path.

	require_once("AdoDBRecord/Tools.module.php");
	require_once("AdoDBRecord/Base.class.php");
	require_once("Inflector.class.php");

	if (version_compare(PHP_VERSION, '5.0.0') < 0)
		require_once("AdoDBRecord/Overloadable.php4.class.php");
	else
		require_once("AdoDBRecord/Overloadable.class.php");

	# FIXME initiate your connection here
#	$_adodb_conn = ADONewConnection($database[type]);
#	$_adodb_conn->Connect($database[host],$database[user],$database[password],$database[db_name]);
#	$_adodb_conn->debug = true;

	AdoDBRecord_Tools::version_check();
	AdoDBRecord_Tools::init();

	define("ADODBRECORD_STUB", "ADODBRECORD_STUB");

	class AdoDBRecord extends AdoDBRecord_Overloadable {
		var $_attributes = array(); # holds the attributes
		var $_has_many = array(); # holds the has-many associations (1:n)
		var $_has_one = array(); # holds the has-one associations (1:1)
		var $_belongs_to = array(); # holds the belongs-to associations (n:1)
		var $_new_record = true; # if this is a new record
		var $_table_name = false; # set this to overwrite default

		var $_columns = array(); # reserved for internal usage
		var $_type_name = NULL; # reserved for STI usage
		var $_base_class = NULL; # reserved for STI usage

		# initializer
		function AdoDBRecord($attributes = false) {
			AdoDBRecord_Base::AdoDBRecord_Base();
			if ($attributes && $attributes != ADODBRECORD_STUB) $this->_attributes = $attributes;
		}

		# standard setup hook does nothing
		# can be implemented in derived classes
		function setup() {
		}

		# add one or more has_many relations to the object, usually run inside the
		# setup method
		function has_many() {
			$this->_has_many = array_merge($this->_has_many, func_get_args());
		}

		# add one or more has_one relations to the object, usually run inside the
		# setup method
		function has_one() {
			$this->_has_one = array_merge($this->_has_one, func_get_args());
		}

		# add one or more belongs_to relations to the object, usually run inside the
		# setup method
		function belongs_to() {
			$this->_belongs_to = array_merge($this->_belongs_to, func_get_args());
		}

		# logs an error
		# FIXME to be moved to seperate class
		function log_error($message, $priority = E_USER_NOTICE, $fatal = false) {
			trigger_error($message, $priority);
			if ($fatal) die($message);
		}

		# return the id of this record as where-clause or false if new
		function _id() {
			if ($this->_new_record) return false;
			# FIXME re-add table and column quotes again later
			return sprintf("id = %d" ,$this->id);
		}

		# returns the last error message of the db connection
		function errmsg() {
			$conn =& _adodb_conn();
			return $conn->ErrorMsg();
		}

		# saves the record by update or insert depending on _new_record
		# this automagically adds updated_at and created_at which are sent
		# to the db only if the columns exist (AdoDB's automagic in AutoExecute())
		# _new_record gets cleared on successful save
		function save() {
			$conn =& _adodb_conn();
			$this->set_attributes(array(
				"type" => ($this->_type_name == $this->_base_class) ? NULL : $this->_type_name,
				"updated_at" => mktime()
			));

			if ($this->_new_record) {
				$this->set_attributes(array("created_at" => mktime()));
				if ($res = $conn->AutoExecute($this->_table_name, $this->_attributes, 'INSERT')) {
					$this->id = $conn->Insert_ID();
					$this->_new_record = false;
				}
				return $res;
			}
			return $conn->AutoExecute($this->_table_name, $this->_attributes, 'UPDATE', $this->_id());
		}

		# delete the instance from the database, sets _new_record to false to indicate it's no longer
		# stored in the database
		function delete() {
			$conn =& _adodb_conn();
			if ($this->_new_record) return false;
			# FIXME re-add table and column quotes again later
			if ($res = $conn->Execute(sprintf("DELETE FROM %s WHERE %s", $this->_table_name, $this->_id())))
				$this->_new_record = true;
			return $res;
		}

		# updates the attributes by merging the new array with the existing
		# attributes without saving the object
		function set_attributes($attributes) {
			return $this->_attributes = array_merge($this->_attributes, $attributes);
		}

		# updates the attributes by merging the new array with the existing
		# attributes and saves the object
		function update_attributes($attributes) {
			$this->set_attributes($attributes);
			return $this->save();
		}
	}

?>
