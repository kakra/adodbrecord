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
	# This implements testing if magic methods on finders work properly.

	require_once("simpletest/autorun.php");
	require_once("../init.php");

	function setup_sqlite_test_db() {
		global $_adodb_conn;
		@unlink("test.db");
		$_adodb_conn = ADONewConnection("sqlite");
		$_adodb_conn->Connect(sprintf("%s/test.db", dirname(__FILE__)));
		$_adodb_conn->debug = true;
		# FIXME re-add table and column quotes again later
		$_adodb_conn->Execute("CREATE TABLE tests (id INTEGER PRIMARY KEY, dummy VARCHAR(50))");
		$_adodb_conn->Execute("CREATE TABLE test_results (id INTEGER PRIMARY KEY, test_id INTEGER, result VARCHAR(50))");
	}

	function preload_sqlite_data() {
		global $_adodb_conn;
		$_adodb_conn->Execute("INSERT INTO tests (id, dummy) VALUES (?, ?)", array(1, "Test1"));
		$_adodb_conn->Execute("INSERT INTO tests (id, dummy) VALUES (?, ?)", array(2, "Test 2"));
		$_adodb_conn->Execute("INSERT INTO test_results (test_id, result) VALUES (?, ?)", array(1, "Yes fine, only one"));
		$_adodb_conn->Execute("INSERT INTO test_results (test_id, result) VALUES (?, ?)", array(2, "The first"));
		$_adodb_conn->Execute("INSERT INTO test_results (test_id, result) VALUES (?, ?)", array(2, "The second"));
	}

	require_once("AdoDBRecord://TestResult_Base");
	class TestResult extends TestResult_Base {
		function setup() {
			$this->belongs_to("test");
		}
	}

	require_once("AdoDBRecord://Test_Base");
	class Test extends Test_Base {
		function setup() {
			$this->has_many("test_results");
		}
	}

	class TestAssociationProxies extends UnitTestCase {

		function test_has_many_with_preloaded_data() {
			setup_sqlite_test_db();
			preload_sqlite_data();

			$test1 = Test::find(1);
			$this->assertEqual(count($test1->test_results), 1);

			$test2 = Test::find(2);
			$this->assertEqual(count($test2->test_results), 2);
		}

		function test_belongs_to_with_preloaded_data() {
			setup_sqlite_test_db();
			preload_sqlite_data();
		}
	}
?>
