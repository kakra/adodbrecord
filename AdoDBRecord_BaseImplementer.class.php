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
	# This class is used to virtually implement a base class for derived
	# "ActiveRecord" classes. It virtually implements a ClassName_Base
	# which ClassName can extend and creates some basic methods like
	# attribute accessors.

	require_once("AdoDBRecord_Implementer.class.php");

	class AdoDBRecord_BaseImplementer extends AdoDBRecord_Implementer {
		function create_stream($class_name) {
			# extract the class name from the stream name first
			preg_match('#([a-z0-9_]*)(\.(.*))?#i', $class_name, $parts) or die("Invalid class name '$class_name'");
			$class = $parts[1];
			$derived_class = preg_replace('/_Base$/i', '', $class);

			# build polymorphic method interface
			$polymorphic_methods = "";
			foreach(array("find", "find_all") as $method)
				$polymorphic_methods .= $this->write_polymorphic_method($derived_class, $method);

			# build class
			return <<<EOC
				class $class extends AdoDBRecord {
					$polymorphic_methods
				}
EOC;
		}

		function write_polymorphic_method($derived_class, $method) {
			return <<<EOP
				function __polymorphic_$method(\$params) { return AdoDBRecord_Base::$method(\$params); }
				function $method() {
					\$instance = new $derived_class(ADODBRECORD_STUB);
					\$params = func_get_args();
					return \$instance->__polymorphic_$method(\$params);
				}
EOP;
		}

		function write_attr_accessor($attribute) {
			return <<<EOA
				function $attribute(\$value = false) {
					if (\$value) return \$this->_attributes["$attribute"] = \$value;
					return \$this->_attributes["$attribute"];
				}
EOA;
		}
	}
?>
