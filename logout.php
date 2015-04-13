<?php
session_start();
session_unset();
setcookie('id', '', time() - 1, '/');
header('Location: login.php');
?>