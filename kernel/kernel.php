<?php

require_once("kernel/driver/PobConnector.php");

class Kernel {
	
	private $config;
	private $data_connectors;
	private $file_systems;
	private $authenticators;
	private $applications;
	
	public function __construct() {
		$this->config = array();
		$this->data_connectors = array();
		$this->file_systems = array();
		$this->authenticators = array();
		$this->applications = array();
	}
	
	public function load($generate_html = false) {
		
		// Load kernel configuration
		$pob_kernel_config = new PobConnector("kernel/config/kernel_config.pob", null, null);
		$kernel_config = $pob_kernel_config->read(null, array("key", "value"));
		foreach ($kernel_config as $k)
			$this->config[$k["key"]] = $k["value"];
			
		if ($generate_html) {
			echo $this->config["doctype"];
			echo "<html><head><title>{$this->config["title"]}</title>";
			echo "<script type=\"text/javascript\" src=\"{$this->config["jquery_location"]}\"></script>";
			echo "<script type=\"text/javascript\" src=\"{$this->config["client_config_location"]}\"></script>";
			echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$this->config["stylesheet_location"]}\" />";
		}
		
		// Load the list of data sources (PobConnector is parsed from driver/ directory)
		$pob_data_sources = new PobConnector("kernel/config/data_sources.pob", null, null);
		$this->data_connectors = $pob_data_sources->read(null, array("name", "type", "location", "schema", "protected", "port", "user", "password"));
				
		foreach ($this->data_connectors as $s) {
			require_once("kernel/driver/{$s["type"]}.php");
			$this->data_connectors[$s["name"]] = new $s["type"](
				$s["location"], $s["schema"], $s["protected"], $s["port"], $s["user"], $s["password"]
			);
		}
		
		// Load file systems
		$pob_file_systems = new PobConnector("kernel/config/file_systems.pob", null, null);
		$file_systems = $pob_file_systems->read(null, array("name", "data_source_name"));
		
		foreach ($file_systems as $f) {
			require_once("kernel/class/{$f["file_system_type"]}.php");
			$this->file_systems[$f["name"]] = new $f["file_system_type"]($this->data_connectors[$f["data_source_name"]]);
		}
		
		// Load authentication mechanisms
		$pob_authenticators = new PobConnector("kernel/config/authenticators.pob", null, null);
		$authenticators = $pob_authenticators->read(null, array("name", "display_name", "class", "enabled"));
		
		foreach ($authenticators as $a) {
			require_once("kernel/class/{$a["authenticator_type"]}.php");
			$this->authenticators[$a["name"]] = $a;
		}
		
		// Ensure that we have a null value by default
		if (!isset($_SESSION["user"]))
			$_SESSION["user"] = null;
		
		// Load startup applications
		if ($generate_html) {
			$startup_applications = $this->data_connectors["kernelspace"]->query("CALL `get_startup_applications` ()");
			foreach ($startup_applications as $s) {
				if (file_exists("application/{$s["application"]}/{$s["application"]}.js"))
					echo "<script type=\"text/javascript\" src=\"application/{$s["application"]}/{$s["application"]}.js\"></script>";
			}
			
			echo "</head><body></body></html>";
		}
		
	}
	
	public function get_data_connector($name) {
		if (isset($this->data_connectors[$name]))
			return $this->data_connectors[$name];
		return false;
	}
	
	public function get_file_system($name) {
		if (isset($this->file_systems[$name]))
			return $this->file_systems[$name];
		return false;
	}
	
	public function get_authenticators() {
		return $this->authenticators;
	}
	
	public function get_authenticator($name) {
		if (isset($this->authenticators[$name]))
			return $this->authenticators[$name];
		return -2;
	}
	
	public function get_application($pid) {
		if (isset($this->applications["$pid"]))
			return $this->applications["$pid"];
	}
	
	public function create_process($application_name) {
		require_once("application/$application_name/$application_name.php");
		$pid = $this->get_next_pid();
		$this->applications["$pid"] = $application_name::create($pid);
		return $this->applications["$pid"];
	}
	
	public function destroy_process($pid) {
		if ($_SESSION["user"] != null && $_SESSION["user"]->is_superuser()) {
			$this->applications["$pid"]->stop();
			$this->applications["$pid"] = null;
		}
	}
	
	private function get_next_pid() {
		$high_pid = 0;
		foreach ($this->applications as $pid => $a) {
			if ($a == null) {
				// Reserve the PID so we're a little more thread-safe
				require_once("kernel/class/PlaceholderApplication.php");
				$this->applications["$pid"] = PlaceholderApplication::create($pid);
				return $pid;
			} else {
				if ($pid > $high_pid)
					$high_pid = $pid;
			}
		}
		
		return $high_pid + 1;
	}
	
	public function get_config_key($key) {
		if ($_SESSION["user"] != null && $_SESSION["user"]->is_superuser()) {
			if (isset($this->config["$key"]))
				return $this->config["$key"];
		}
		return -1;
	}
	
	public static function explain_error_code($err_code) {
		switch ($err_code) {
			case -1:
				return "The requested kernel configuration key does not exist.";
				break;
			case -2:
				return "The requestion authentication type does not exist.";
				break;
			default:
				return "Unrecognized error code.";
		}
	}
	
}

?>
