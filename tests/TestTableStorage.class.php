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
	# This implements testing if object storage on tables and restoration
	# works properly.

	require_once("simpletest/autorun.php");

	$PREFIX_ADODB = "adodb/";
	require_once("../AdoDBRecord.class.php");

	function setup_sqlite_test_db() {
		global $_adodb_conn;
		@unlink("test.db");
		$_adodb_conn =& ADONewConnection("sqlite");
		$_adodb_conn->Connect(sprintf("%s/test.db", dirname(__FILE__)));
		$_adodb_conn->debug = true;
		$_adodb_conn->Execute("CREATE TABLE 'tests' ('id' INT(12) PRIMARY KEY, 'dummy' VARCHAR(50))");
	}

	require_once("AdoDBRecord://Test_Base");
	class Test extends Test_Base {
	}

	class TestTableStorage extends UnitTestCase {

		function test_new_and_save() {
			setup_sqlite_test_db();

			# Create a dummy test entry, ensure it got its parameters and that it's
			# successfully saved
			$dummy = new Test(array ("dummy" => "Test123"));
			$this->assertEqual($dummy->_attributes["dummy"], "Test123");
			$this->assertTrue($dummy->save());
			$this->assertTrue($dummy->_attributes["id"] > 0);

			# Return the complete table and check there is exactly one row
			$all_rows = Test::find("all");
			$this->assertEqual(count($all_rows), 1);

			# Return the first row and check it matches our dummy test entry
			$first = Test::find("first");
			$this->assertEqual($first->_attributes["dummy"], "Test123");
			$this->assertTrue($first->_attributes["id"] > 0);
		}

		function test_create() {
			setup_sqlite_test_db();

			# Create a dummy test entry, ensure it is of correct type and got its
			# parameters correctly saved
			$dummy = Test::create(array ("dummy" => "Test456"));
			$this->assertEqual(get_class($dummy), "Test");
			$this->assertEqual($dummy->_attributes["dummy"], "Test456");
			$this->assertTrue($dummy->_attributes["id"] > 0);

			# Return the complete table and check there is exactly one row
			$all_rows = Test::find("all");
			$this->assertEqual(count($all_rows), 1);

			# Return the first row and check it matches our dummy test entry
			$first = Test::find("first");
			$this->assertEqual($first->_attributes["dummy"], "Test456");
			$this->assertTrue($first->_attributes["id"] > 0);
		}
	}
?>