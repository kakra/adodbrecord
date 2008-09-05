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
	# This implements testing if classes resolve to table names properly.

	require_once("simpletest/autorun.php");

	$PREFIX_ADODB = "adodb/";
	require_once("../lib/AdoDBRecord.class.php");

	function setup_sqlite_test_db() {
		global $_adodb_conn;
		@unlink("test.db");
		$_adodb_conn = ADONewConnection("sqlite");
		$_adodb_conn->Connect(sprintf("%s/test.db", dirname(__FILE__)));
		$_adodb_conn->debug = true;
		# FIXME re-add table and column quotes again later
		$_adodb_conn->Execute("CREATE TABLE tests (id INTEGER PRIMARY KEY, dummy VARCHAR(10))");
	}

	require_once("AdoDBRecord://Test_Base");
	class Test extends Test_Base {
	}

	class STITest extends Test {
	}

	require_once("AdoDBRecord://CamelCase_Base");
	class CamelCase extends CamelCase_Base {
	}

	class TestClassToTableName extends UnitTestCase {

		function test_class_to_table_name() {
			setup_sqlite_test_db();

			# Basic test to ensure inflections for table names work and
			# auto-inheritance from class name resolves correctly
			$test = new Test();
			$this->assertEqual($test->_table_name, "tests");

			# Now test the same works for STI and resolves to the base
			# class name
			$sti_test = new STITest();
			$this->assertEqual($sti_test->_table_name, "tests");

			# Ensure CamelCase class names resolve properly to their
			# "tableized" camel_cases counterpart
			$camel_case = new CamelCase();
			$this->assertEqual($camel_case->_table_name, "camel_cases");
		}
	}
?>
