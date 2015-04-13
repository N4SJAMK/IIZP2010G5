<?php
require_once 'MongoBackup.class.php';
require_once 'api.php';

if (!isset($_POST['jsonData'])) {
	die();
}

$data = json_decode($_POST['jsonData'], true);

//var_dump($data);

$filter = null;

$ids = isset($data['selected']) ? $data['selected'] : array();

if (array_key_exists('filter_results', $data)) {
	$filter = array();

	if (in_array($data['type'], array('delete_user', 'ban_user', 'unban_user', 'reset_password', 'message_user'))):
		if ($data['filter_status'] == 'active' || $data['filter_status'] == 'banned'):
			$filter['banned'] = array('$exists' => ($data['filter_status'] == 'banned'));
		endif;

		if (!empty($data['filter_email'])):
			$filter['email'] = array('$regex' => new MongoRegex('/^.*?' . addslashes($data['filter_email']) . '.*?$/'));
		endif;

		$users = $database->getUserArray($filter, 0, 0);

		foreach ($users as $user):
			if (!(
			($user['boards'] < (int) $data['filter_boards_min']) ||
			($user['boards'] > (int) $data['filter_boards_max']) ||
			($user['tickets'] < (int) $data['filter_tickets_min']) ||
			($user['tickets'] > (int) $data['filter_tickets_max']) ||
			(!isset($user['active']) && (!empty($data['filter_active_start']) || !empty($data['filter_active_end']))) ||
			(!empty($data['filter_active_start']) && (int) $user['active']->format('U') < strtotime($data['filter_active_start'])) ||
			(!empty($data['filter_active_end']) && (int) $user['active']->format('U') > strtotime($data['filter_active_end'])))):
				$ids[] = $user['_id'];
			endif;
		endforeach;
	elseif (in_array($data['type'], array('delete_board', 'unshare_board'))):
		$boards = $database->getBoardArray($filter, 0, 0);

		foreach ($boards as $board):
			if (!(
			($board['guests'] < (int) $data['filter_guests_min']) ||
			($board['guests'] > (int) $data['filter_guests_max']) ||
			($board['tickets'] < (int) $data['filter_tickets_min']) ||
			($board['tickets'] > (int) $data['filter_tickets_max']) ||
			(!isset($board['active']) && (!empty($data['filter_active_start']) || !empty($data['filter_active_end']))) ||
			(!empty($data['filter_active_start']) && (int) $board['active']->format('U') < strtotime($data['filter_active_start'])) ||
			(!empty($data['filter_active_end']) && (int) $board['active']->format('U') > strtotime($data['filter_active_end'])) ||
			(!empty($data['filter_created_start']) && (int) $board['createdAt']->format('U') < strtotime($data['filter_created_start'])) ||
			(!empty($data['filter_created_end']) && (int) $board['createdAt']->format('U') > strtotime($data['filter_created_end'])) ||
			(!empty($data['filter_owner']) && !preg_match('/^.*?' . addslashes($data['filter_owner']) . '.*?$/', $board['owner'])))):
				echo 'board: ' . $board['_id'] . ', owner: ' . $board['owner'] . '<br>';
				$ids[] = $board['_id'];
			endif;
		endforeach;
	endif;
}

switch ($data['type']) {
	case 'delete_user':deleteUsers($ids);
		break;
	case 'ban_user':banUsers($ids, array_key_exists('delete_boards', $data), true);
		break;
	case 'unban_user':banUsers($ids, false, false);
		break;
	case 'reset_password':resetPasswords($ids);
		break;
	case 'message_user':sendMessage($ids, $data['subject'], $data['message']);
		break;

	case 'unshare_board':unshareBoards($ids);
		break;
	case 'delete_board':deleteBoards($ids);
		break;

	case 'create_backup':createBackup($data['backup_name']);
		break;
	case 'restore_backup':restoreBackup($data['selected'][0]);
		break;
	case 'delete_backup':deleteBackups(isset($filter) ? null : $data['selected']);
		break;

}

function deleteUsers($ids) {
	global $database;

	foreach ($ids as $id):
		if (!$database->removeUser(array('_id' => new MongoId($id)))):
			die();
		endif;
	endforeach;
	echo 'Successfully deleted ' . count($ids) . ' users!';
}

function banUsers($ids, $deleteBoards, $ban) {
	global $database;

	foreach ($ids as $id):
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

	echo 'Successfully ' . ($ban ? '' : 'un') . 'banned ' . count($ids) . ' user' . (count($ids) > 1 ? 's' : '') . '!';
}

function sendMessage($ids, $subject, $message) {
	global $database;

	foreach ($ids as $id):
		$user = $database->getUser(array('_id' => new MongoId($id)));

		sendMail(
			$user['email'],
			$subject,
			$message);

	endforeach;

	echo 'Successfully sent message to ' . count($ids) . ' user' . (count($ids) > 1 ? 's' : '') . '!';
}

function resetPasswords($ids) {
	global $database;

	foreach ($ids as $id):
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
	endforeach;

	echo 'Successfully reset password of ' . count($ids) . ' user' . (count($ids) > 1 ? 's' : '') . '!';
}

function unshareBoards($ids) {
	global $database;

	foreach ($ids as $id):
		if (!$database->unshareBoard(array('_id' => new MongoId($id)))):
			die();
		endif;
	endforeach;

	echo 'Successfully unshared ' . count($ids) . ' board' . (count($ids) > 1 ? 's' : '') . '!';
}

function deleteBoards($ids) {
	global $database;

	foreach ($ids as $id):
		if (!$database->removeBoard(array('_id' => new MongoId($id)))):
			die();
		endif;
	endforeach;

	echo 'Successfully deleted ' . count($ids) . ' board' . (count($ids) > 1 ? 's' : '') . '!';
}

function createBackup($name) {
	$mongoBackup = new MongoBackup();

	if (!$mongoBackup->backup(DB_NAME, $name)) {
		echo "Could not create new backup \"$name\"!";
	} else {
		echo "Successfully created new backup \"$name\"!";
	}
}

function restoreBackup($name) {
	$mongoBackup = new MongoBackup();

	if (!$mongoBackup->restore(DB_NAME, $name)):
		echo "Could not restore backup \"$name\"!";
	else:
		echo "Successfully restored backup \"$name\"!";
	endif;
}

function deleteBackups($names) {
	$mongoBackup = new MongoBackup();

	if (!isset($names)):
		echo ($mongoBackup->remove() ? "Successfully deleted all backups!" : "Could not remove backups!");
	else:
		foreach ($names as $name):
			echo '<p>' . ($mongoBackup->remove($name) ? "Successfully deleted backup \"$name\"!" : "Could not delete backup \"$name\"!") . '</p>';
		endforeach;
	endif;
}

?>