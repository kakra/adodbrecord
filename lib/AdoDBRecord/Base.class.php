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
	#
	# TODO: Implement association proxies (parse_* functions, wrap into scope)
	# TODO: Implement scoping

	require_once("BaseImplementer.class.php");
	require_once("Tools.module.php");
	require_once("Inflector.class.php");
	require_once("Singleton.class.php");
	require_once("AssociationProxy.class.php");

	# class to polymorphically implement AdoDBRecord functionality
	# This makes use of PHP's behaviour to always pass the $this variable
	# if a method is called statically from an instance method call. This one
	# has intentionally no parent class to provide a private namespace.
	class AdoDBRecord_Base {

		function AdoDBRecord_Base() {
			$this->initialize();
		}

		function initialize() {
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
			# TODO respect scoped create options
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

		# destroy one or more id's by finding each id and running destroy() on it
		# if called on an instance it runs delete() on it
		function destroy($id) {
			if (is_array($id)) {
				foreach ($id as $one_id) AdoDBRecord_Base::destroy($one_id);
				return;
			}
			$obj = AdoDBRecord_Base::find($id);
			$obj->delete();
		}

		# returns the records found by $arguments
		# as an instance or array of instances of $class
		function &find($arguments) {
			$conditions = array();

			# Instantiate model singleton to access scope
			$model =& Singleton::instance();
			$scope = $model->_scope["find"];

			$where = $order = $limit = $offset = NULL;
			$options = array();

			# preset scoped options
			if (isset($scope["order"])) $order = $scope["order"];

			# flatten all arguments if their key is numeric
			# this resolves e.g. array(1, 2, array("conditions" => "foo")) into array(1, 2, "conditions" => "foo")
			while (true) {
				$break = true;
				foreach ($arguments as $key => $value) if (is_numeric($key) && is_array($value))
				{
					unset($arguments[$key]);
					$arguments = array_merge_recursive($arguments, $value);
					$break = false;
					break;
				}
				if ($break) break;
			}

			# parse arguments into query parameters
			foreach ($arguments as $key => $arg) {
				if (is_numeric($key)) {
					switch ($arg) {
						case "all":
							$limit = $offset = NULL;
							$options[] = $arg;
							continue 2;
						case "first":
							$limit = 1;
							$offset = NULL;
							$options[] = $arg;
							continue 2;
						default:
							$key = $this->_primary_key;
					}
				}
				switch ($key) {
					case "conditions":
						# FIXME make this DRY (see also AdoDBRecord_AssociationProxy)
						if (!is_array($arg)) $arg = array($arg);
						$conditions = array_merge_recursive($conditions, $arg);
						break;
					case "limit":
					case "offset":
					case "order":
						$$key = $arg;
						break;
					default:
						if (is_array($arg))
							$conditions[] = sprintf("{$key} IN (?)", $arg);
						else
							$conditions[] = array("{$key} = ?", $arg);
				}
			}

			# parse conditions
			$parsed_conditions = array();
			$parsed_params = array();
			AdoDBRecord_Tools::parse_conditions($conditions, $parsed_conditions, $parsed_params);

			# join scoped conditions
			if (!empty($scope["conditions"])) $parsed_conditions[] = $scope["conditions"];

			# convert parsed options to sql
			if ($order !== NULL) $order = " ORDER BY {$order}";
			if (!empty($parsed_conditions)) {
				$where = sprintf(" WHERE (%s)", join($parsed_conditions, ") AND ("));
				AdoDBRecord_Tools::convert_sql_params($where, $parsed_params);
			}

			$objs = array();
			$conn =& _adodb_conn();
			# FIXME re-add table and column quotes again later
			if ($limit === NULL) $limit = -1;
			if ($offset === NULL) $offset = -1;
			if ($rs =& $conn->SelectLimit("SELECT * FROM {$this->_table_name}{$where}{$order}", $limit, $offset, $parsed_params)) {
				$rows =& $rs->GetRows();
				foreach ($rows as $row) {
					$class = (empty($row["type"]) ? get_class($this) : $row["type"]);
					$obj = new $class($row);
					$obj->_new_record = false;
					$objs[] = $obj;
				}
				if (in_array("all", $options)) return $objs;
				if (in_array("first", $options)) return $objs[0];
				if (count($objs) == 1) return $objs[0];
				return $objs;
			}
			return NULL;
		}

		# returns an array of instances
		function &find_all($arguments) {
			array_unshift($arguments, "all");
			return AdoDBRecord_Base::find($arguments);
		}

		# parses the member access initiated by __set() or __get()
		# and checks if it is available as column or association (TODO)
		# and results in an error otherwise
		function parse_member() {
			$args = func_get_args();
			switch (count($args)) {
				case 1:
					# this call was made by __get()
					list($property) = $args;
					if (AdoDBRecord_Tools::is_column_property($property)) return $this->_attributes[$property];
					if ($property == "id") return $this->_attributes[$this->_primary_key];

					# if no column property check for association or proxy
					# TODO cache proxy
					$use_proxy = (substr($property, -1) == "_");
					if ($use_proxy) $property = substr($property, 0, -1);
					if (AdoDBRecord_Tools::is_association_property($property)) {
						if (AdoDBRecord_Tools::is_has_many_property($property)) {
							$returns_many = true;
							$options = $this->_has_many[$property];
						}
						elseif (AdoDBRecord_Tools::is_belongs_to_property($property)) {
							$returns_many = false;
							$options = $this->_belongs_to[$property];
						}
						elseif (AdoDBRecord_Tools::is_has_one_property($property)) {
							$returns_many = false;
							$options = $this->_has_one[$property];
						}
						else
							die("AdoDBRecord_Base::parse_member(): fatal association inconsistency");
						$proxy = new AdoDBRecord_AssociationProxy($this, $returns_many, $options);
						if ($use_proxy) return $proxy;
						return $proxy->find();
					}

					# TODO write a real error handler
					die(get_class($this) . "->{$property}: No such property");

				case 2:
					# this call was made by __set()
					# TODO check property is valid (_associations)
					list($property, $value) = $args;
					if (AdoDBRecord_Tools::is_column_property($property)) return $this->set_attributes(array($property => $value));
					if ($property == "id") return $this->set_attributes(array($this->_primary_key => $value));

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
		function parse_method($arg, $args) {
			$methods = array("find_by", "find_all_by", "find_first_by");
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
