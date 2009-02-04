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
	require_once("../init.php");

	function setup_sqlite_test_db() {
		global $_adodb_conn;
		@unlink("test.db");
		$_adodb_conn = ADONewConnection("sqlite");
		$_adodb_conn->Connect(sprintf("%s/test.db", dirname(__FILE__)));
		$_adodb_conn->debug = true;
		# FIXME re-add table and column quotes again later
		$_adodb_conn->Execute("CREATE TABLE tests (id INTEGER PRIMARY KEY, type VARCHAR(50), dummy VARCHAR(50))");
	}

	require_once("AdoDBRecord://Test_Base");
	class Test extends Test_Base {
	}

	class SuperTest extends Test {
	}

	class UltraTest extends SuperTest {
	}

	class AutoTest extends Test {
	}

	class TestSTI extends UnitTestCase {

		function test_sti_field_is_saved_and_used_correctly() {
			setup_sqlite_test_db();

			# Create a dummy test entries
			$dummy = Test::create(array("dummy" => "TestDummy"));
			$this->assertEqual($dummy->type, NULL);

			$stest = new SuperTest(array("dummy" => "SuperDummy"));
			$stest->save();
			$this->assertEqual(strtolower($stest->type), "supertest");

			$utest = new UltraTest(array("dummy" => "UltraDummy"));
			$utest->save();
			$this->assertEqual(strtolower($utest->type), "ultratest");

			$atest = new AutoTest(array("dummy" => "AutoDummy"));
			$atest->save();
			$this->assertEqual(strtolower($atest->type), "autotest");

			# PHP 5.3 needed to call magic methods statically, so
			# work around by instanciating the class as singleton
			$Test = Singleton::instance("Test");

			# Load and check test entries
			$dummy2 = $Test->find_by_dummy("TestDummy");
			$this->assertEqual($dummy2->type, NULL);
			$this->assertEqual(strtolower(get_class($dummy2)), "test");

			$stest2 = $Test->find_by_dummy("SuperDummy");
			$this->assertEqual(strtolower($stest2->type), "supertest");
			$this->assertEqual(strtolower(get_class($stest2)), "supertest");

			$utest2 = $Test->find_by_dummy("UltraDummy");
			$this->assertEqual(strtolower($utest2->type), "ultratest");
			$this->assertEqual(strtolower(get_class($utest2)), "ultratest");

			$atest2 = $Test->find_by_dummy("AutoDummy");
			$this->assertEqual(strtolower($atest2->type), "autotest");
			$this->assertEqual(strtolower(get_class($atest2)), "autotest");
		}
	}
?>
