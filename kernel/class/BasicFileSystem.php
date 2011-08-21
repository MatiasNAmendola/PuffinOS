<?php

require_once("kernel/abstract/aFileSystem.php");

class BasicFileSystem extends aFileSystem {
	
	private $data_source_connector;
	
	public function __construct($data_source_connector) {
		$this->data_source_connector = $data_source_connector;
	}
	
	public function read($filename) {}
	
	public function read_bytes($filename, $start, $length) {}
	
	public function write($filename, $data) {}
	
	public function append($filename, $data) {}
	
}

?>
