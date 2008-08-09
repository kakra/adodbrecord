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
	# Based on Rails' inflectors.
	#
	# This class is used to manage string inflections.

	require_once("Module.class.php");

	class Inflections extends Module {
		var $irregulars = array();
		var $plurals = array();
		var $singulars = array();
		var $uncountables = array();

		function plural($rule, $replacement) {
			array_unshift($this->plurals, array($rule, $replacement));
		}

		function singular($rule, $replacement) {
			array_unshift($this->singulars, array($rule, $replacement));
		}

		function irregular($singular, $plural) {
			$s01 = substr($singular, 0, 1); $s01u = strtoupper($s01); $s01d = strtolower($s01);
			$p01 = substr($plural, 0, 1); $p01u = strtoupper($p01); $p01d = strtolower($p01);
			$s1rest = substr($singular, 1); $p1rest = substr($plural, 1);
			if ($s01u == $p01u) {
				$this->plural("/({$s01}){$s1rest}\$/i", '\1' . $p1rest);
				$this->singular("/({$p01}){$p1rest}\$/i", '\1' . $s1rest);
			}
			else {
				$this->plural("/{$s01u}(?i){$s1rest}\$/", $p01u . $p1rest);
				$this->plural("/{$s01d}(?i){$s1rest}\$/", $p01d . $p1rest);
				$this->singular("/{$p01u}(?i){$p1rest}\$/", $s01u . $s1rest);
				$this->singular("/{$p01d}(?i){$p1rest}\$/", $s01d . $s1rest);
			}
		}

		function uncountable($words) {
			if (!is_array($words)) $words = array($words);
			foreach ($words as $word) $this->uncountables[] = $word;
		}
	}
?>