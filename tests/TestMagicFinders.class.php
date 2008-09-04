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
		$_adodb_conn = ADONewConnection("sqlite");
		$_adodb_conn->Connect(sprintf("%s/test.db", dirname(__FILE__)));
		$_adodb_conn->debug = true;
		# FIXME re-add table and column quotes again later
		$_adodb_conn->Execute("CREATE TABLE tests (id INTEGER PRIMARY KEY, dummy VARCHAR(50))");
	}

	require_once("AdoDBRecord://Test_Base");
	class Test extends Test_Base {
	}

	class TestMagicFinders extends UnitTestCase {

		function test_find_by() {
			setup_sqlite_test_db();

			# PHP 5.3 needed to call magic methods statically, so
			# work around by instanciating the class as singleton
			$Test = Singleton::instance("Test");

			# create two test entries to be found
			Test::create(
				array("dummy" => "Test 1"),
				array("dummy" => "Test 2")
			);

			# Test find_by single id
			$dummy = $Test->find_by_id(1);
			$this->assertEqual(strtolower(get_class($dummy)), "test");
			$this->assertEqual($dummy->dummy, "Test 1");

			# Test find_by two single ids
			$dummies = $Test->find_by_id(1, 2);
			$this->assertEqual(count($dummies), 2);
			$this->assertEqual($dummies[0]->dummy, "Test 1");
			$this->assertEqual($dummies[1]->dummy, "Test 2");

			# Test find_by id array
			$dummies = $Test->find_by_id(array(1, 2));
			$this->assertEqual(count($dummies), 2);
			$this->assertEqual($dummies[0]->dummy, "Test 1");
			$this->assertEqual($dummies[1]->dummy, "Test 2");

			# Test it works with strings, too
			$dummy = $Test->find_by_dummy("Test 2");
			$this->assertEqual($dummy->id, 2);
		}
	}
?>
