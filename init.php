<?php
	if ($ADODB_RECORD_OLD_INCLUDE = set_include_path(dirname(__FILE__) . '/lib/' . PATH_SEPARATOR . get_include_path())) {
		global $PREFIX_ADODB;
		if (!isset($PREFIX_ADODB)) $PREFIX_ADODB = "adodb/";
		require_once("lib/AdoDBRecord.class.php");
		set_include_path($ADODB_RECORD_OLD_INCLUDE);
	}
	else
		die("Cannot set include path in " . __FILE__);
?>
