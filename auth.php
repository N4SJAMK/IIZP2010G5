<?php
session_start();

if (isset($_COOKIE['id'])) {
	$_SESSION['id'] = $_COOKIE['id'];
}

if (!($_SESSION['id'] == hash("sha256", $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']))) {
	header('Location: login.php');
}
?>