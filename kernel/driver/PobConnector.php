<?php

// PobConnector
//   A data source connector for Puffin Objects
//   This format does not support indexing, so don't use it for large datasets.

require_once("kernel/abstract/aDataSourceConnector.php");

class PobConnector extends aDataSourceConnector {
		
		private $obj_del;
		private $prop_del;
		private $data;
		private $fields;

		public function __construct($location, $schema, $protected, $port=0, $user='', $password='') {
			parent::__construct($location, $schema, $protected, $port, $user, $password);
			$this->obj_del = '\n';
			$this->prop_del = ',';
			$this->data = array();
			$this->fields = array();

			$this->file = fopen($location, "r");
			while(!feof($this->file)) {
				$line = fgets($this->file);
				$this->parse_line($line);
			}
		}

		private function parse_line($line) {
			switch (substr($line, 0, 1)) {
				case '+':
					$line = trim(substr($line, 1));
					$this->fields = explode($this->prop_del, $line);
					break;
				case '>':
					// Directives
					if (substr($line, 2, 18) == "PROPERTY_DELIMITER") {
						$this->prop_del = substr($line, 21, 1);
					} else if (substr($line, 2, 16) == "OBJECT_DELIMITER") {
						$this->obj_del = substr($line, 19, 1);
					}
					break;
				case '#':
					// Ignore these.  They're comments.  So just don't do anything.
					break;
				default:
					if (strlen(trim($line)) > 0) {
						$objects = explode($this->obj_del, $line);
						// Assign each property to an array index accessible by property name
						
						foreach ($objects as $o) {
							$instance = array();
							foreach ($this->fields as $f) {
								$instance[$f] = null;
							}
							$properties = explode($this->prop_del, $o);
							$i = 0;
							foreach ($properties as $p) {
								$instance[$this->fields[$i]] = trim($p);
								++$i;
							}
							$this->data[] = $instance;
						}
					}
					break;
			}
		}

		// In SQL, these params might read something like this:
		// SELECT $fields FROM $object_name WHERE $field = $value;
		public function read($object_name, $fields, $field = null, $value = null) {
			if ($object_name != null && $object_name != "" && $object_name != $this->location)
				return -1;  // Object Name differs from location and is not a blank string or null.
			
			if ($field == null)
				return $this->data;
			
			$to_return = array();
			
			foreach ($this->data as $d) {
				if ($d[$field] == $value)
					foreach ($fields as $f)
						$to_return[$f] = $d[$f];
			}
			
			return $to_return;
		}

        public function write($object_name, $fields, $values) {
			return -2;  // POB files are read-only.
		}

        public function delete($object_name, $field, $value) {
			return -2;
		}

        public function create($object_name, $fields, $data_types) {
			return -2;
		}

        public function drop($object_name) {
			return -2;
		}
		
		public function close() {
			fclose($this->file);
		}
		
		public function get_object_delimiter() {
			return $this->obj_del;
		}
		
		public function query($query, $return_results = false) {
			// Format: "field1,field2,field3:field:value"
			$parts = explode(':', $query);
			$fields = explode(',', $parts[0]);
				return $this->read(null, $fields, $parts[1], $parts[2]);
		}
		
		public function explain_error_code($err_code) {
			switch ($err_code) {
				case -1:
					return "The object name differs from the POB file's location, and is not a blank string or null.";
					break;
				case -2:
					return "POB files are read-only through the kernel.";
					break;
				default:
					return "Unrecognized error code.";
			}
		}
		
		public function object_exists($object_name) {
			if (isset($this->data[$object_name]))
				return true;
			return false;
		}

}

?>
