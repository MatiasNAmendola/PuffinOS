<?php

abstract class aApplication {
	
	protected $pid;
	
	public function __construct($pid) {
		$this->pid = $pid;
	}
	
	public function get_pid() {
		return $this->pid;
	}
	
	abstract static public function create($pid);
	
	abstract public function get_name();
	
	abstract public function run($args = null);
	
	abstract public function stop();

	abstract public function handle_ajax_request();
	
}

?>
