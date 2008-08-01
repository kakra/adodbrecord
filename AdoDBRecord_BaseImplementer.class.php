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
			return <<<EOC
				class $class_name extends AdoDBRecord {
				}
EOC;
		}

		function write_attr_accessors($attribute) {
			return <<<EOF
				function $attribute(\$value = false) {
					if (\$value) return \$this->_attributes["$attribute"] = \$value;
					return \$this->_attributes["$attribute"];
				}
EOF;
		}
	}
?>
