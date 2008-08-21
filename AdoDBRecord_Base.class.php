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
	# This class is used to "magically" extend derived classes by additional
	# methods for attribute setters and getters and other functions like method
	# name parsers. This works by registering hooks which define derived classes
	# in global space and by defining base methods.

	require_once("AdoDBRecord_BaseImplementer.class.php");

	# class to polymorphic implement AdoDBRecord functionality
	# This makes use of PHP's behaviour to always pass the $this variable
	# if a method is called statically from an instance method call. This one
	# has intentionally no parent class to provide a private namespace.
	class AdoDBRecord_Base {

		function AdoDBRecord_Base() {
			# setup database configuration
			if (!$this->_table_name) {
				$class = get_class($this);
				while (get_parent_class($class) && !preg_match('/_Base$/i', get_parent_class($class)))
					$class = get_parent_class($class);
				$this->_base_class = $class;
				$this->_table_name = Inflector::tableize($class);
			}
			$this->_type_name = get_class($this);
			$this->_columns = AdoDBRecord_Tools::get_columns();

			# call the setup hook
			$this->setup();
		}

		# register the hook which defines a derived class
		function register_hooks() {
			stream_wrapper_register("AdoDBRecord", "AdoDBRecord_BaseImplementer")
			   	or die("Cannot register extension hook.");
		}

		# returns the one record found by $id
		# as an instance of $class
		function find($params) {
			$id = array_shift($params);

			$limit = '';
			switch ($id) {
				case "all":
					return $this->find_all($params);
				case "first":
					$limit = " LIMIT 1";
					break;
			}

			# FIXME this is inconsistent with find_all()
			$options = array_shift($params);
			$append_sql = "";
			if (!empty($options)) $append_sql = " ${options}";

			$conn = _adodb_conn();
			if ($row = $conn->GetRow("SELECT * FROM `" . $this->_table_name . "` WHERE `id` = ?{$append_sql}{$limit}", array($id))) {
				# FIXME make dry
				$class = (empty($row["type"]) ? get_class($this) : $row["type"]);
				$obj = new $class($row);
				$obj->_new_record = false;
				return $obj;
			}
			return NULL;
		}

		# returns an array of instances
		function find_all($params) {
			$conn = _adodb_conn();

			# FIXME this is inconsistent with find()
			$options = array_shift($params);
			$append_sql = "";
			if (!empty($options)) $append_sql = " ${options}";

			if ($rows =& $conn->GetAll("SELECT * FROM `" . $this->_table_name . "`${append_sql}")) {
				$base_class = get_class($this);
				$objs = array();
				foreach ($rows as $row) {
					# FIXME make dry
					$class = (empty($row["type"]) ? $base_class : $row["type"]);
					$objs[] = new $class($row);
					$obj->_new_record = false;
				}
				return $objs;
			}
			return NULL;
		}

		# sets or reads an attribute depending on parameter count
		# 1 parameter  => return named attribute
        # 2 parameters => set and return named attribute
		# 3 or more    => set and return named attribute as array
		function attribute($params) {
			$name = array_shift($params);
			switch (count($params)) {
				case 0:
					return @$this->_attributes[$name];
				case 1:
					$params = array_shift($params);
				default:
					return $this->_attributes[$name] = $params;
			}
		}
	}
?>
