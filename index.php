<?php

require_once("kernel/kernel.php");

session_start();

if (isset($_POST["application"])) {
	$_SESSION["kernel"]->load();
} elseif (isset($_POST["command"])) {
	switch ($_POST["command"]) {
		case "GET_AUTHENTICATORS":
			echo format_authenticators();
			break;
		case "AUTHENTICATE":
			handle_authentication();
			break;
		default:
			echo "No such command";
	}
} else {
	$_SESSION["kernel"] = new Kernel();
	$_SESSION["kernel"]->load(true);
}

function format_authenticators() {
	$to_return = "[";
	$authenticators = $_SESSION["kernel"]->get_authenticators();
	
	foreach ($authenticators as $a)
		$to_return .= "{\"name\":\"{$a["name"]}\",\"displayName\":\"{$a["display_name"]}\"},";
	
	$to_return = substr($to_return, 0, strlen($to_return) - 1);
	
	$to_return .= "]";
	return $to_return;
}

function handle_authentication() {
	$_SESSION["kernel"]->load();
	$authenticator = $_SESSION["kernel"]->get_authenticator($_POST["type"]);
	if ($authenticator < 0) {
		echo "{\"success\":false, message:\"" + Kernel::explain_error_code($authenticator) + "\"}";
	} else {
		require_once("kernel/class/{$authenticator["authenticator_type"]}.php");
		$success = $authenticator["authenticator_type"]::authenticate($_POST["username"], $_POST["password"]);
		if ($success instanceof aAuthenticator) {
			$_SESSION["user"] = $success;
			echo "{\"success\":true}";
		} else {
			echo "{\"success\":false, \"message\":\"" . $authenticator["authenticator_type"]::explain_error_code($success) . "\"}";
		}
	}
}

?>
