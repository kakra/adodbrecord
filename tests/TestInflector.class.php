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
	# This implements inflector unit tests.

	require_once("simpletest/autorun.php");
	require_once("../init.php");

	class TestInflector extends UnitTestCase {
	
		function test_singularize_and_pluralize() {
			$plurals = array(
				"cow" => "kine",
				"news" => "news",
				"person" => "people",
				"fish" => "fish",
				"attachment" => "attachments"
			);

			foreach ($plurals as $singular => $plural) {
				$this->assertEqual($singular, Inflector::singularize($plural));
				$this->assertEqual(Inflector::pluralize($singular), $plural);
			}
		}

		function test_tableize() {
			$tables = array(
				"SimpleCow" => "simple_kine",
				"ChannelNews" => "channel_news",
				"Person" => "people",
				"Child" => "children",
				"MailAttachment" => "mail_attachments"
			);
			foreach ($tables as $class => $table) {
				$this->assertEqual($table, Inflector::tableize($class));
			}
		}
	}
?>
