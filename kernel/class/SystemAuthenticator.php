<?php

require_once("kernel/abstract/aAuthenticator.php");
require_once("kernel/driver/MySqlConnector.php");

class SystemAuthenticator extends aAuthenticator {
	
	public function __construct($username, $uid, $comment, $superuser, $groups) {
		parent::__construct($username, $uid, $comment, $superuser, $groups);
	}
	
	public static function authenticate($username, $password) {
		$data = $_SESSION["kernel"]->get_data_connector("kernelspace");
		$results = $data->read("user", array("password", "locked"), "username", $username);
		if (count($results) == 0) {
			return -1;
		} else if (count($results) > 1) {
			return -2;
		} else {
			$password_db = $results[0]["password"];
			$password = $data->query("SELECT SHA1('$password') AS `password`");
			$password = $password[0]["password"];
			if ($password != $password_db) {
				return -3;
			} else {
				
				if ($results[0]["locked"] == 1)
					return -4;
				$results = $data->read("user", array("id", "superuser", "comment"), "username", $username);
				$uid = $results[0]["id"];
				$superuser = $results[0]["superuser"];
				$comment = $results[0]["comment"];
				if ($superuser == 1) $superuser = true;
				if ($superuser == 0) $superuser = false;
				
				$groups = $data->query("CALL `get_group_ids` ($uid)");
				$groups = $groups[0];
				return new SystemAuthenticator($username, $uid, $comment, $superuser, $groups);
			}
		}
	}
	
	public static function explain_error_code($err_code) {
		switch ($err_code) {
			case -1:
				return "No such username exists.";
				break;
			case -2:
				return "There are multiple instances of the same username.";
				break;
			case -3:
				return "Incorrect password.";
				break;
			case -4:
				return "Account is locked.";
				break;
			default:
				return "Unrecognized error code.";
		}
	}
	
}

?>
