<?php

abstract class aDataSourceConnector {
	
	protected $location;
	protected $schema;
	protected $protected;
	protected $port;
	protected $user;
	protected $password;
	
	// Constructor
	public function __construct($location, $schema, $protected, $port, $user, $password) {
		$this->location = $location;
		$this->schema = $schema;
		$this->protected = $protected;
		$this->port = $port;
		$this->user = $user;
		$this->password = $password;
	}
	
	public function is_protected() { return $this->protected; }
	
	// $object_name will refer to database tables or files depending on the data source
	// $fields is an array of field names
	// $field is the name of a field to check against $value.  This function
	// returns any records from the data source where the value in $field == $value
	abstract public function read($object_name, $fields, $field, $value);

	// $values is an array of values in sequence with $fields
	abstract public function write($object_name, $fields, $values);

	// $field is a field to match $value to.  This function should delete any records in
	// $object_name where the value in $field == $value.
	abstract public function delete($object_name, $field, $value);

	// Should create a new data source (file/table/etc.) called $object_name.
	// It will have $fields of matching $data_types.
	abstract public function create($object_name, $fields, $data_types);

	// Completely destroys $object_name.
	abstract public function drop($object_name);
	
	// Closes the connection to the data source.
	abstract public function close();
	
	// Returns a plaintext explanation of an error code.
	abstract public function explain_error_code($err_code);
	
	// Performs a direct query against the data source.
	abstract public function query($query, $return_results);
	
	abstract public function object_exists($object_name);
	
}

?>
