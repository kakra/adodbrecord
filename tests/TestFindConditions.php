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
	# This implements testing if finder conditions work properly.

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
		$_adodb_conn->Execute("CREATE TABLE tests (id INTEGER PRIMARY KEY, dummy VARCHAR(50))");
	}

	require_once("AdoDBRecord://Test_Base");
	class Test extends Test_Base {
	}

	class TestFindConditions extends UnitTestCase {

		function test_find_conditions_for_single_results() {
			setup_sqlite_test_db();

			# create two test entries to be found
			Test::create(
				array("dummy" => "Test 1"),
				array("dummy" => "Test 2")
			);

			$result = Test::find(array("conditions" => "id = 1"));
			$this->assertFalse(is_array($result));
			$this->assertEqual(count($result), 1);

			$result = Test::find(array("conditions" => array("id = ?", 1)));
			$this->assertFalse(is_array($result));
			$this->assertEqual(count($result), 1);
		}

		function test_find_conditions_for_multiple_results() {
			setup_sqlite_test_db();

			# create two test entries to be found
			Test::create(
				array("dummy" => "Test 1"),
				array("dummy" => "Test 2")
			);

			$result = Test::find(array("conditions" => "id = 1 OR id = 2", "order" => "id"));
			$this->assertEqual(count($result), 2);

			$result = Test::find(array("conditions" => array("id = ? OR dummy = ?", 1, "Test 2"), "order" => "id"));
			$this->assertEqual(count($result), 2);

			$result = Test::find(array("conditions" => array("id IN (?)", array(1, 2)), "order" => "id"));
			$this->assertEqual(count($result), 2);

			$result = Test::find(array(
				"conditions" => array(
					array("id IN (?)", array(1, 2)),
					array("dummy IN (?)", array("Test 1", "Test 2"))
				),
				"order" => "id"
			));
			$this->assertEqual(count($result), 2);
		}

		function test_find_all_returns_array() {
			# create two test entries to be found
			Test::create(
				array("dummy" => "Test 1"),
				array("dummy" => "Test 2")
			);

			$result = Test::find_all(array("conditions" => "id = 1"));
			$this->assertTrue(is_array($result));
			$this->assertEqual(count($result), 1);

			$result = Test::find("all", array("conditions" => array("id = ?", 2)));
			$this->assertTrue(is_array($result));
			$this->assertEqual(count($result), 1);
		}
	}
?>
