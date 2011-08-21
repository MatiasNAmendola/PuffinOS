<?php

abstract class aAuthenticator {
	
	protected $username;
	protected $uid;
	protected $comment;
	protected $superuser;
	protected $groups;
	
	public function __construct($username, $uid, $comment, $superuser, $groups) {
		$this->username = $username;
		$this->uid = $uid;
		$this->comment = $comment;
		$this->superuser = $superuser;
		$this->groups = $groups;
	}
	
	// This function should always return an instance of the authenticator.
	abstract public static function authenticate($username, $password);
	
	abstract public static function explain_error_code($err_code);
	
	public function get_uid() { return $this->uid; }
	public function get_groups() { return $this->groups; }
	public function get_username() { return $this->username; }
	public function is_superuser() { return $this->superuser; }
	public function get_comment() { return $this->comment; }
	
}

?>
