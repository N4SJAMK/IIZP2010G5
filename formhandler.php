<?php
require_once 'MongoBackup.class.php';
require_once 'api.php';

if (!isset($_POST['jsonData'])) {
	die();
}

$data = json_decode($_POST['jsonData'], true);

var_dump($data);

$filter = null;

if (array_key_exists('filter_results', $data)) {
	$filter = array();

	if ($data['filter_status'] == 'active' || $data['filter_status'] == 'banned'):
		$filter['banned'] = array('$exists' => ($data['filter_status'] == 'banned'));
	endif;

	if (!empty($data['filter_email'])):
		$filter['email'] = array('$regex' => new MongoRegex('/^.*?' . addslashes($data['filter_email']) . '.*?$/'));
	endif;
}

switch ($data['type']) {
	case 'delete_user':deleteUsers($data, $filter);
		break;
	case 'ban_user':banUsers($data, array_key_exists('delete_boards', $data), true, $filter);
		break;
	case 'unban_user':banUsers($data, false, false, $filter);
		break;
	case 'reset_password':resetPasswords($data, $filter);
		break;

	case 'unshare_board':unshareBoards($data, $filter);
		break;
	case 'delete_board':deleteBoards($data, $filter);
		break;

	case 'create_backup':createBackup($data['backup_name']);
		break;
	case 'restore_backup':restoreBackup($data['selected'][0]);
		break;
	case 'delete_backup':deleteBackups($data, $filter);
		break;

}

function deleteUsers($data, $filter = null) {
	global $database;

	foreach ($data['selected'] as $id) {
		if (!$database->removeUser(array('_id' => new MongoId($id)))) {
			die();
		}
	}
	echo 'Successfully deleted ' . count($data['selected']) . ' users!';
}

function banUsers($data, $deleteBoards, $ban, $filter = null) {
	global $database;

	if (isset($filter)):
		$users = $database->getUserArray($filter, 0, 0);

		$banned = 0;

		foreach ($users as $user):
			if (((isset($data['filter_boards_min']) && $user['boards'] < $data['filter_boards_min']) ||
			(isset($data['filter_boards_max']) && $user['boards'] > $data['filter_boards_max']) ||
			(isset($data['filter_tickets_min']) && $user['tickets'] < $data['filter_tickets_min']) ||
			(isset($data['filter_tickets_max']) && $user['tickets'] > $data['filter_tickets_max']) ||
			(!isset($user['active']) && (isset($data['filter_active_start']) || isset($data['filter_active_end']))) ||
			(isset($data['filter_active_start']) && (int) $user['active']->format('U') < strtotime($data['filter_active_start'])) ||
			(isset($data['filter_active_end']) && (int) $user['active']->format('U') > strtotime($data['filter_active_end'])))):
				if ($ban):
					if (!$database->setUserData(array('_id' => $user['_id']), array('banned' => true))):
						die();
					endif;
				else:
					if (!$database->unsetUserData(array('_id' => $user['_id']), array('banned' => true))):
						die();
					endif;
				endif;

				if ($deleteBoards):
					$database->removeBoard(array('createdBy' => $user['_id']));
				endif;

				$banned += 1;
			endif;

		endforeach;
	else:
		foreach ($data['selected'] as $id):
			if ($ban):
				if (!$database->setUserData(array('_id' => new MongoId($id)), array('banned' => true))):
					die();
				endif;
			else:
				if (!$database->unsetUserData(array('_id' => new MongoId($id)), array('banned' => true))):
					die();
				endif;
			endif;

			if ($deleteBoards):
				$database->removeBoard(array('createdBy' => new MongoId($id)));
			endif;
		endforeach;

		$banned = count($data['selected']);
	endif;

	if ($ban):
		echo 'Successfully banned ' . $banned . ' users!';
	else:
		echo 'Successfully unbanned ' . $banned . ' users!';
	endif;
}

function resetPasswords($data, $filter = null) {
	global $database;

	if (isset($filter)):
		$users = $database->getUserArray($filter, 0, 0);

		$banned = 0;

		foreach ($users as $user):
			if (((isset($data['filter_boards_min']) && $user['boards'] < $data['filter_boards_min']) ||
			(isset($data['filter_boards_max']) && $user['boards'] > $data['filter_boards_max']) ||
			(isset($data['filter_tickets_min']) && $user['tickets'] < $data['filter_tickets_min']) ||
			(isset($data['filter_tickets_max']) && $user['tickets'] > $data['filter_tickets_max']) ||
			(!isset($user['active']) && (isset($data['filter_active_start']) || isset($data['filter_active_end']))) ||
			(isset($data['filter_active_start']) && (int) $user['active']->format('U') < strtotime($data['filter_active_start'])) ||
			(isset($data['filter_active_end']) && (int) $user['active']->format('U') > strtotime($data['filter_active_end'])))):
				if ($ban):
					if (!$database->setUserData(array('_id' => $user['_id']), array('banned' => true))):
						die();
					endif;
				else:
					if (!$database->unsetUserData(array('_id' => $user['_id']), array('banned' => true))):
						die();
					endif;
				endif;

				if ($deleteBoards):
					$database->removeBoard(array('createdBy' => $user['_id']));
				endif;

				$banned += 1;
			endif;

		endforeach;
	else:
	foreach ($data as $id) {
		$user = $database->getUser(array('_id' => new MongoId($id)));

		$newPassword = generatePassword(12);

		if (!$database->setUserData(array('_id' => new MongoId($id)), array('password' => encryptPassword($newPassword)))) {
			die();
		}

		sendMail(
			$user['email'],
			'Contriboard password reset.',
			'New password: ' . $newPassword);

		//echo $newPassword . '<br>';
	}
	endif;

	echo 'Successfully reset password of ' . count($data) . ' users!';
}

function unshareBoards($data, $filter = null) {
	global $database;

	foreach ($data as $id) {
		if (!$database->unshareBoard(array('_id' => new MongoId($id)))) {
			die();
		}
	}

	echo 'Successfully unshared ' . count($data) . ' boards!';
}

function deleteBoards($data, $filter = null) {
	global $database;

	if (isset($filter)) {

	} else {
		foreach ($data as $id) {
			if (!$database->removeBoard(array('_id' => new MongoId($id)))) {
				die();
			}
		}
	}

	echo 'Successfully deleted ' . count($data) . ' boards!';
}

function createBackup($name) {
	$mongoBackup = new MongoBackup();

	if (!$mongoBackup->backup(DB_NAME, $name)) {
		echo "Could not create new backup \"$name\"!";
	} else {
		echo "Successfully created new backup \"$name\"!";
	}
}

function deleteBackups($data, $filter = null) {
	$mongoBackup = new MongoBackup();

	if (isset($filter)) {
		if (!$mongoBackup->remove()) {
			echo "Could not remove backups!";
		} else {
			echo "Successfully removed all backups!";
		}
	} else {
		foreach ($data as $name) {
			if (!$mongoBackup->remove($name)) {
				echo "Could not remove backup \"$name\"!";
			} else {
				echo "Successfully removed backup \"$name\"!";
			}
		}
	}
}

function restoreBackup($name) {
	$mongoBackup = new MongoBackup();

	if (!$mongoBackup->restore(DB_NAME, $name)) {
		echo "Could not restore backup \"$name\"!";
	} else {
		echo "Successfully restored backup \"$name\"!";
	}
}

?>