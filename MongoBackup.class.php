<?php

require_once 'functions.php';

class MongoBackup {

	private $backupDirectory = 'backups';

	function __construct() {
		putenv("LC_ALL=C");
	}

	function backup($database, $backupName) {
		$backupPath = $this->backupDirectory . '/' . $backupName;

		$pattern = "/(?'db'[^\s]+?)\.(?'name'[a-zA-Z]+?)\s.*?(?P=name).bson.*?(?'count'\d+)\sobjects/s";

		$exec = shell_exec("mongodump -d \"$database\" -o \"$backupPath\"");

		$results = array();

		if (preg_match_all($pattern, $exec, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $val) {
				$results[$val['db']][$val['name']] = (int) $val['count'];
			}

			$results['name'] = $backupName;
			$results['size'] = folderSize($backupPath);
			$results['time'] = date('d.m.Y H:i');

			$file = fopen($backupPath . '/stats.json', 'w');
			fwrite($file, json_encode($results));
			fclose($file);

			return true;
		}

		return false;
	}

	function restore($database, $backupName) {

		$backupPath = $this->backupDirectory . '/' . $backupName . '/' . $database;
		$exec = shell_exec("mongorestore -d $database --drop $backupPath");

		return !preg_match('/ERROR/', $exec, $matches);
	}

	function remove($backupName) {
		return deleteDirectory($this->backupDirectory . '/' . $backupName);
	}

	function getBackups($database) {
		$paths = array_filter(glob($this->backupDirectory . '/*', GLOB_ONLYDIR),
			function ($dir) {
				return file_exists($dir . '/stats.json');
			});

		$backups = array();

		foreach ($paths as $path) {
			$stats = json_decode(file_get_contents($path . '/stats.json'), true);

			if (isset($stats)) {
				if (isset($database) && array_key_exists($database, $stats)) {
					$backups[] = array_merge($stats[$database], array('name' => $stats['name'], 'size' => $stats['size'], 'time' => $stats['time']));
				} else {
					$backups[] = $stats;
				}
			}
		}

		return $backups;
	}
};

?>