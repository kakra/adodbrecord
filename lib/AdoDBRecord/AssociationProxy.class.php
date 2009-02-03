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
	# This class implements associations proxies.

	require_once("Singleton.class.php");
	require_once("AdoDBRecord/Tools.module.php");

	class AdoDBRecord_AssociationProxy {
		var $_wrap_scope = NULL;
		var $_client = NULL;
		var $_returns_many = NULL;
		var $_source = NULL;
		var $_options = NULL;

		function AdoDBRecord_AssociationProxy($client, $returns_many, $options) {
			$this->_client = $client;
			$this->_returns_many = $returns_many;
			$this->_options = $options;

			# build base data from options
			# TODO select source from polymorphic type
			$source = $this->_source = Singleton::instance($options["class_name"]);
			if (!is_subclass_of($this->_source, 'AdoDBRecord')) die ("AdoDBRecord_AssociationProxy: associated class isn't an ancestor of AdoDBRecord");

			# extract find options
			$find_options = array();
			if (isset($options["order"])) $find_options["order"] = $options["order"];
			# TODO if (isset($options["select"])) $find_options["select"] = $options["select"];

			# parse conditions into string + parameters
			# FIXME make this DRY
			$parsed_conditions = array();
			$parsed_params = array();
			if (isset($options["conditions"])) {
				$arg = $options["conditions"];
				if (!is_array($arg)) $arg = array($arg);
				$conditions = array_merge_recursive($conditions, $arg);
				AdoDBRecord_Tools::parse_conditions($conditions, $parsed_conditions, $parsed_params);
			}

			if (isset($options["primary_key"])) {
				$parsed_conditions[] = sprintf("%s = ?", $options["foreign_key"]);
				$parsed_params[] = $client->$options["primary_key"];
			}
			else {
				$parsed_conditions[] = sprintf("%s = ?", 'id');
				$parsed_params[] = $client->$options["foreign_key"];
			}

			$find_conditions = sprintf("(%s)", join($parsed_conditions, ") AND ("));
			$find_conditions = strtr($find_conditions, array("%" => "%%", "?" => "'%s'")); # FIXME not database agnostic
			$find_conditions = vsprintf($find_conditions, $parsed_params);

			if (isset($options["primary_key"])) {
				$this->_wrap_scope = array(
					"find" => array_merge($find_options, array("conditions" => $find_conditions)),
					"create" => array(
						$options["foreign_key"] => $client->$options["primary_key"]
					)
				);
			}
			else {
				$this->_wrap_scope = array(
					"find" => array_merge($find_options, array("conditions" => $find_conditions))
				);
			}
		}

		function &find() {
			# instantiate model singleton for scoping
			$model = Singleton::instance($this->_source);

			# wrap finder into model scope
			$options = func_get_args();
			$model->with_find_scope($this->_wrap_scope["find"]);
			$result = $this->_source->find($options);
			$model->end_scope();
			return $result;
		}
	}
?>