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

	require_once("BaseImplementer.class.php");

	# class to polymorphic implement AdoDBRecord functionality
	# This makes use of PHP's behaviour to always pass the $this variable
	# if a method is called statically from an instance method call. This one
	# has intentionally no parent class to provide a private namespace.
	class AdoDBRecord_Base {

		function AdoDBRecord_Base() {
			# setup database configuration
			if (!$this->_table_name) {
				if (!$this->_base_class) {
					$class = get_class($this);
					while (get_parent_class($class) && !preg_match('/_Base$/i', get_parent_class($class)))
						$class = get_parent_class($class);
					$this->_base_class = $class;
				}
				$this->_table_name = Inflector::tableize($this->_base_class);
			}
			$this->_type_name = get_class($this);
			$this->_columns = AdoDBRecord_Tools::get_columns();

			# dynamically overload current class in PHP4 because it doesn't
			# propagate through the class hierarchy
			if (version_compare(PHP_VERSION, "5.0.0") < 0) {
				$const = "OVERLOADED_" . $this->_type_name;
				if (!defined($const)) {
					define($const, $const);
					overload($this->_type_name);
				}
			}

			# call the setup hook
			$this->setup();
		}

		# register the hook which defines a derived class
		function register_hooks() {
			stream_wrapper_register("AdoDBRecord", "AdoDBRecord_BaseImplementer")
			   	or die("Cannot register extension hook.");
		}

		# instanciate and save one or many new objects
		function create($attribute_list) {
			while($attributes = array_shift($attribute_list)) {
				if (is_array($attributes) && empty($attributes))
					$attributes = false;
				$class = get_class($this);
				$obj = new $class(&$attributes);
				$obj->save();
				$objs[] = $obj;
			}
			return (count($objs) > 1) ? $objs : $obj;
		}

		# returns the one record found by $id
		# as an instance of $class
		function find($params) {
			$id = array_shift($params);

			switch ($id) {
				case "all":
					return $this->find_all($params);
				case "first":
					return array_shift($this->find_all("LIMIT 1"));
			}

			# FIXME this is inconsistent with find_all()
			$options = array_shift($params);
			$append_sql = "";
			if (!empty($options)) $append_sql = " ${options}";

			$conn =& _adodb_conn();
			# FIXME re-add table and column quotes again later
			if ($row =& $conn->GetRow("SELECT * FROM {$this->_table_name} WHERE id = ?{$append_sql}", array($id))) {
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
			$conn =& _adodb_conn();

			# FIXME this is inconsistent with find()
			$options = array_shift($params);
			$append_sql = "";
			if (!empty($options)) $append_sql = " ${options}";

			# FIXME re-add table and column quotes again later
			if ($rows =& $conn->GetAll("SELECT * FROM {$this->_table_name}${append_sql}")) {
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
					return $this->set_attributes(array($name => $params));
			}
		}

		# parses the member access initiated by __set() or __get()
		# and checks if it is available as column or association (TODO)
		# and results in an error otherwise
		function parse_member() {
			$args = func_get_args();
			switch (count($args)) {
				case 1:
					# this call was made by __get()
					# TODO check property is valid (_associations)
					list($property) = $args;
					if (in_array($property, $this->_columns))
						return $this->_attributes[$property];
					# TODO write a real error handler
					die(get_class($this) . "->{$property}: No such property");

				case 2:
					# this call was made by __set()
					# TODO check property is valid (_associations)
					list($property, $value) = $args;
					if (in_array($property, $this->_columns))
						return $this->set_attributes(array($property => $value));
					# TODO write a real error handler
					die(get_class($this) . "->{$property}: No such property");

				default:
					# this call was made by some chaotic wizard and is invalid
					# TODO write a real error handler
					die("AdoDBRecord_Base::parse_member(): unexpected arguments received");
			}
		}

		# parses the method access initiated by __call() and reduces it to its
		# associated real method attaching appropriate parameters
		function parse_method() {
			$args = func_get_args();
			$methods = array("find_by", "find_all_by", "find_first_by");
			$arg = array_shift($args);
			foreach ($methods as $method)
				if (substr($arg, 0, $len = (strlen($method) + 1)) === "{$method}_") {
					$condition = substr($arg, $len);
					$method = "_parse_{$method}";
					if (in_array($method, get_class_methods($this)))
						return call_user_method($method, $this, $condition, $args);
					break;
				}
			# TODO improve error handler
			die("Unknown method called: {$this->_type_name}::{$arg}\n");
		}
	}
?>