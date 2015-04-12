<?php

function generatePassword($length) {
	$chars = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
	$count = strlen($chars) - 1;

	$password = array();
	for ($i = 0; $i < $length; $i++) {
		$n = rand(0, $count);
		$password[] = $chars[$n];
	}

	return implode($password);
}

function encryptPassword($password) {
	return crypt($password, '$2a$10$' . generateSalt() . '$');
}

function generateSalt() {
	return bin2hex(mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB), MCRYPT_DEV_URANDOM));
}

function sendMail($to, $subject, $message) {
	$headers = 'From: admin@n4sjamk.org' . "\r\n" .
	'X-Mailer: PHP/' . phpversion();

	mail($to, $subject, $message, $headers);
}

function formatBytes($size, $precision = 1) {
	$base = log($size, 1024);
	$suffixes = array('bytes', 'KB', 'MB', 'GB', 'TB');

	return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}

function folderSize($path) {
	$total_size = 0;
	$files = scandir($path);
	$cleanPath = rtrim($path, '/') . '/';

	foreach ($files as $t) {
		if ($t != "." && $t != "..") {
			$currentFile = $cleanPath . $t;
			if (is_dir($currentFile)) {
				$size = foldersize($currentFile);
				$total_size += $size;
			} else {
				$size = filesize($currentFile);
				$total_size += $size;
			}
		}
	}

	return $total_size;
}

function deleteDirectory($path) {
	$it = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
	$files = new RecursiveIteratorIterator($it,
		RecursiveIteratorIterator::CHILD_FIRST);
	foreach ($files as $file) {
		if ($file->isDir()) {
			rmdir($file->getRealPath());
		} else {
			unlink($file->getRealPath());
		}
	}

	return rmdir($path);
}

?>