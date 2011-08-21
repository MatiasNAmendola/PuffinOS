<?php

abstract class aFileSystem {
	
	private $data_source_connector;
	
	public function __construct($data_source_connector) {
		$this->data_source_connector = $data_source_connector;
	}
	
	abstract public function read($filename);
	
	abstract public function read_bytes($filename, $start, $length);
	
	abstract public function write($filename, $data);
	
	abstract public function append($filename, $data);
	
}

?>
