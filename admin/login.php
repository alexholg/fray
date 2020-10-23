<?php

require_once "includes.php";

$login_error = "";

// Logout
if (isset($_GET['logout'])) {
	$login = Configuration::getPrivateArea();
	$login->logout();
	@header("Location: ../");
	exit(0);
}

// Login Error
if (isset($_GET['error'])) {
	$login_error = $l10n['private_area_login_error'];
}

// Login via form
if (isset($_POST['uname']) && $_POST['uname'] != "" && isset($_POST['pwd']) && $_POST['pwd'] != "") {
	$login = Configuration::getPrivateArea();
	if ($login->login($_POST['uname'], $_POST['pwd']) == 0) {
		$url = $login->getSavedPage() ? $login->getSavedPage() : "index.php";
		exit('<!DOCTYPE html><html><head><title>Loading...</title><meta http-equiv="refresh" content="1; url=' . $url . '"></head><body><p style="text-align: center;">Loading...</p></body></html>');
	} else {
		$login_error = $l10n['private_area_login_error'];
	}
}


if (isset($_POST['token_request'])) {
	header("HTTP/1.0 403 Forbidden");
	header("Content-type: application/json");
	echo "{ \"result\": \"error\", \"message\": \"not_supported\" }";
	exit(0);
}

if (isset($_GET['token'])) {
	header("HTTP/1.0 403 Forbidden");
	echo '<script>parent.postMessage(\'{"code": 403}\', "*");</script>';
	echo "Login via mobile app is not supported.";
	exit(0);
}


// Redirect to a specific section
$redirect = Configuration::getControlPanel()->getRedirectFromArray($_GET);
if ($redirect) {
	header("Location: " . $redirect);
	exit(0);
}

// If a session is already set, try to redirect to the dashboard
Configuration::getControlPanel()->attemptAutoLogin();

// Show the login form

$loginT = Configuration::getControlPanel()->getTemplate("templates/login.php");
$loginT->pagetitle = "Login";
$loginT->error = $login_error;
echo $loginT->render();
