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
	# This class is a generic interface for returning virtual data streams.
	# It is usually meant to implement PHP code virtually for inclusion by
	# require_once() and friends. Derived classes are used to choose the
	# implementation.
	#
	# Implementation interface:
	# http://php.net/manual/en/function.stream-wrapper-register.php

	class AdoDBRecord_Implementer {
		var $stream = NULL;
		var $position = 0;

		# customize this in derived classes to create the actual stream
		function create_stream($name) {
			die("Virtual method called for loading '$name'.");
		}

		function stream_open($path, $mode, $options, &$opened_path) {
			$this->position = 0;
			$this->stream = '<?php ' . $this->create_stream(preg_replace("#^.*://#", "", $path, 1)) or die("Stream creation failed for '$path'") . ' ?>';
			$opened_path = $path;
			return true;
		}

		function stream_read($count) {
			$this->position += strlen($data = substr($this->stream, $this->position, $count));
			return $data;
		}

		function stream_write($data) {
			return 0;
		}

		function stream_tell() {
			return $this->position;
		}

		function stream_eof() {
			return strlen($this->stream) < $this->position;
		}

		function stream_seek($offset, $whence) {
			switch ($whence) {
				case SEEK_SET:
					if (($offset > 0) && ($offset <= strlen($this->stream))) {
						$this->position = $offset;
						return true;
					}
					return false;
				case SEEK_CUR:
					return $this->stream_seek($this->position + $offset, SEEK_SET);
				case SEEK_END:
					return $this->stream_seek(strlen($this->stream) + $offset, SEEK_SET);
			}
			return false;
		}
	}
?>
