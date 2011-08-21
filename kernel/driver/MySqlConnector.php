<?php

require_once("kernel/abstract/aDataSourceConnector.php");

class MySqlConnector extends aDataSourceConnector {

	private $connection;

	public function __construct($location, $schema, $protected, $port, $user, $password) {
		parent::__construct($location, $schema, $protected, $port, $user, $password);
		$this->connection = new mysqli(
			$this->location,
			$this->user,
			$this->password,
			$this->schema,
			$this->port
		);
	}
	
	public function read($object_name, $fields, $field = null, $value = null) {
		$query = "SELECT `{$fields[0]}`";
		for ($i = 1; $i < count($fields); ++$i) {
			foreach ($fields as $f) {
				$query .= ", `$f`";
			}
		}
		
		$query .= " FROM `$object_name`";
		
		if ($field != null) {
			$query .= " WHERE `$field` = ";
			if (is_string($value))
				$query .= "'";
			$query .= $value;
			if (is_string($value))
				$query .= "'";
		}
		
		$to_return = $this->query($query);

		return $to_return;
	}

	public function write($object_name, $fields, $values) {
		$query = "INSERT INTO `$object_name` (`{$fields[0]}`";
		
		if (count($fields) > 1)
			for ($i = 1; $i < count($fields); ++$i)
				$query .= ", `{$fields[$i]}`";
		
		$query .= ") VALUES (";
		
		if (is_string($values[0]))
			$query .= '"';
		
		$query .= $values[0];
		
		if (is_string($values[0]))
			$query .= '"';
		
		if (count($values) > 1) {
			for ($i = 1; $i < count($values); ++$i) {
				$query .= ", ";
				if (is_string($values[$i]))
					$query .= '"';
				$query .= "{$values[$i]}";
				if (is_string($values[$i]))
					$query .= '"';
			}
		}
		
		$query .= ")";
		
		return $this->query($query);
	}

	public function delete($object_name, $field, $value) {
		echo "Field: $field\n";
		if ($field == null) {
			$query = "TRUNCATE TABLE `$object_name`";
		} else {
			$query = "DELETE FROM `$object_name` WHERE `$field` = ";
			if (is_string($value)) $query .= '"';
			$query .= $value;
			if (is_string($value)) $query .= '"';
		}
		
		return $this->query($query);
	}

	public function create($object_name, $fields, $data_types) {
		$query = "CREATE TABLE `$object_name` (";
		
		$query .= "`{$fields[0]}` {$data_types[0]}";
		for ($i = 1; $i < count($fields); ++$i)
			$query .= ", `{$fields[$i]}` {$data_types[$i]}";
		
		$query .= ")";
		
		return $this->query($query);
	}

	public function drop($object_name) {
		$query = "DROP TABLE `$object_name`";
		return $this->query($query);
	}

	public function close() {
		$this->connection->close();
	}
	
	public function query($query, $return_results = true) {
		// print_r($query);  // Debug
		if ($return_results) {
			$results = $this->connection->query($query);
			$to_return = array();
			while ($row = $results->fetch_assoc())
				$to_return[] = $row;
			$this->connection->next_result();
			return $to_return;
		} else {
			$this->connection->query($query);
		}
	}
	
	public function explain_error_code($err_code) {
		switch ($err_code) {
			case 0:
				return "Query executed successfully, but was not a query type that returns results.";
			default:
				return "Unknown error code.";
		}
	}
	
	public function object_exists($object_name) {
		$results = $this->query("SHOW TABLES");
		$tables = array();
		foreach ($results as $r)
			$tables[] = $r["Tables_in_{$this->schema}"];
		
		foreach ($tables as $t)
			if ($t == $object_name)
				return true;
				
		return false;
	}
	
}
	
?>
