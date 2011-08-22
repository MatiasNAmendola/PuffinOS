<?php

require_once("kernel/abstract/aApplication.php");

class PLM extends aApplication {
	
	private final APP_NAME = "Puffin Login Manager";
	
	public function __construct($pid) {
		parent::__construct($pid, "Puffin Login Manager");
	}
	
	public static function create($pid) {
		return new PLM($pid);
	}
	
	public function get_name() {
		return APP_NAME;
	}
	
	public function run($args = null) {
		
	}
	
	public function stop() {
		// Nothing yet
	}

	public function handle_ajax_request() {
		// Nothing yet
	}
}

?>
