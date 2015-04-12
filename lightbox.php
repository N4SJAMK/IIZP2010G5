<?php

require_once 'api.php';

$lightboxSettings = array(
	'message_user' => array(
		'title' => 'Message',
		'buttonText' => 'Send',
		'buttonColor' => 'blue',
	), 'reset_password' => array(
		'title' => 'Reset password',
		'buttonText' => 'Reset',
		'buttonColor' => 'purple',
	), 'ban_user' => array(
		'title' => 'Ban user',
		'buttonText' => 'Ban',
		'buttonColor' => 'red',
	), 'unban_user' => array(
		'title' => 'Unban user',
		'buttonText' => 'Unban',
		'buttonColor' => 'purple',
	), 'delete_user' => array(
		'title' => 'Delete user',
		'buttonText' => 'Delete',
		'buttonColor' => 'red',
	), 'unshare_board' => array(
		'title' => 'Unshare board',
		'buttonText' => 'Unshare',
		'buttonColor' => 'purple',
	), 'delete_board' => array(
		'title' => 'Delete board',
		'buttonText' => 'Delete',
		'buttonColor' => 'red',
	), 'create_backup' => array(
		'title' => 'Create backup',
		'buttonText' => 'Create',
		'buttonColor' => 'blue',
	), 'delete_backup' => array(
		'title' => 'Delete backup',
		'buttonText' => 'Delete',
		'buttonColor' => 'red',
	), 'restore_backup' => array(
		'title' => 'Restore backup',
		'buttonText' => 'Restore',
		'buttonColor' => 'red',
	),
);

function lightboxHTML($type, $content) {
	global $lightboxSettings;
	echo <<<HTML
            <div id="lightbox" onclick="event.stopPropagation()">
                <div id="lightbox-header">
                    <a class="box-close" onclick="closeLightbox()"><i class="fa fa-times"></i></a>
                    <h1>{$lightboxSettings[$type]['title']}</h1>
                </div>
                <div id="lightbox-content">
                    $content
                </div>
                <div id="lightbox-footer">
                    <button type="button" class="cancel gray" name="cancel" onclick="closeLightbox()">Cancel</button>
                    <button type="button" class="action {$lightboxSettings[$type]['buttonColor']}" name="$type" onclick="sendFormData(this)">{$lightboxSettings[$type]['buttonText']}</button>
                </div>
            </div>
HTML;
}

if (!isset($_POST['type'])) {
	die();
}

$type = $_POST['type'];

if ($type == 'message_user' || $type == 'reset_password' || $type == 'ban_user' || $type == 'unban_user' || $type == 'delete_user') {

	if (!isset($_POST['selected'])) {
		die();
	}

	$selected = $_POST['selected'];
	$singleUser = $userList = $userCount = "";
	if (!is_array($selected)) {
		$userCount = $selected . " users";
	} else {
		if (sizeof($selected) == 0) {
			die();
		}

		if (sizeof($selected) == 1) {
			$singleUser = $database->getUser(array('_id' => new MongoId($selected[0])))['email'];
		} else if (sizeof($selected) <= 5) {
			$userList = "<ul>";
			for ($i = 0; $i < sizeof($selected); $i++) {
				$email = $database->getUser(array('_id' => new MongoId($selected[$i])))['email'];
				if ($i < sizeof($selected) - 1) {
					$userList .= "<li>{$email},</li>";
				} else {
					$userList .= "<li>{$email}</li>";
				}

			}
			$userList .= "</ul>";
		} else {
			$userCount = sizeof($selected) . " users";
		}

	}

	if ($type == 'message_user') {

		if (!isset($_POST['selected'])) {
			die();
		}

		$content = <<<HTML
            <table>
                <tr>
                    <td class="text-right">To</td>
                    <td>$singleUser$userCount$userList</td>
                </tr>
                <tr>
                    <td class="text-right">Subject</td>
                    <td class="full-width"><input class="full-width" type="text" name="users_message_subject"></td>
                </tr>
                <tr>
                    <td colspan="2"><textarea class="full-width"></textarea></td>
                </tr>
            </table>
HTML;
		lightboxHTML($type, $content);

	} else if ($type == 'reset_password') {

		if ($singleUser != "") {
			$content = "<p>You are about to reset the password of user \"$singleUser\".</p><p>Are you sure?</p>";
		} else if ($userList != "") {
			$content = "<p>You are about to reset the passwords of users</p>$userList<p>Are you sure?</p>";
		} else {
			$content = "<p>You are about to reset the passwords of $userCount.</p><p>Are you sure?</p>";
		}

		lightboxHTML($type, $content);

	} else if ($type == 'ban_user') {

		if ($singleUser != "") {
			$content = "<p>You are about to ban user \"$singleUser\" and</p>";
		} else if ($userList != "") {
			$content = "<p>You are about to ban users</p>$userList<p>and</p>";
		} else {
			$content = "<p>You are about to ban $userCount and</p>";
		}

		$content .= '<p><input type="checkbox" class="form-addition" id="delete_boards" name="delete_boards" value="1"><label for="delete_boards">delete their boards.</label></p><p>Are you sure?</p>';
		lightboxHTML($type, $content);

	} else if ($type == 'unban_user') {

		if ($singleUser != "") {
			$content = "<p>You are about to unban user \"$singleUser\".</p><p>Are you sure?</p>";
		} else if ($userList != "") {
			$content = "<p>You are about to unban users</p>$userList<p>Are you sure?</p>";
		} else {
			$content = "<p>You are about to unban $userCount.</p><p>Are you sure?</p>";
		}

		lightboxHTML($type, $content);

	} else if ($type == 'delete_user') {

		if ($singleUser != "") {
			$content = "<p>You are about to delete user \"$singleUser\".</p><p>Are you sure?</p>";
		} else if ($userList != "") {
			$content = "<p>You are about to delete users</p>$userList<p>Are you sure?</p>";
		} else {
			$content = "<p>You are about to delete $userCount.</p><p>Are you sure?</p>";
		}

		lightboxHTML($type, $content);
	}
}

if ($type == 'unshare_board' || $type == 'delete_board') {

	if (!isset($_POST['selected'])) {
		die();
	}

	$selected = $_POST['selected'];
	if (is_array($selected)) {
		if (sizeof($selected) == 0) {
			die();
		}

		if (sizeof($selected) == 1) {
			$boardCount = "1 board";
		} else {
			$boardCount = sizeof($selected) . " boards";
		}

	} else {
		$boardCount = $selected . " boards";
	}

	if ($type == 'unshare_board') {
		$content = "<p>You are about to disable the public ";
		if (sizeof($selected) == 1) {
			$content .= "URL ";
		} else {
			$content = "URLs ";
		}

		$content .= "of $boardCount.</p><p>Are you sure?</p>";
		lightboxHTML($type, $content);

	} else if ($type == 'delete_board') {

		$content = "<p>You are about to delete $boardCount.</p><p>Are you sure?</p>";
		lightboxHTML($type, $content);
	}
}

if ($type == 'create_backup') {

	$defaultName = "Backup-" . time();
	$content = <<<HTML
    Creating a new backup file named
    <p><input type="text" class="form-addition" name="backup_name" maxlength="32" size="32" value="$defaultName"></p>
HTML;
	lightboxHTML($type, $content);

} else if ($type == 'delete_backup' || $type == 'restore_backup') {

	if (!isset($_POST['selected']) || !is_array($_POST['selected'])) {
		die();
	}

	$selected = $_POST['selected'];
	if (sizeof($selected) == 0) {
		die();
	}

	if ($type == 'delete_backup') {

		if (sizeof($selected) > 5) {
			$content = "<p>You are about to delete " . sizeof($selected) . " backups.</p><p>Are you sure?</p>";
		} else {
			$content = "<p>You are about to delete backups</p><ul>";
			for ($i = 0; $i < sizeof($selected); $i++) {
				if ($i < sizeof($selected) - 1) {
					$content .= "<li>{$selected[$i]},</li>";
				} else {
					$content .= "<li>{$selected[$i]}</li>";
				}

			}
			$content .= "</ul><p>Are you sure?</p>";
		}
		lightboxHTML($type, $content);

	} else if ($type == 'restore_backup') {

		if (sizeof($selected) != 1) {
			die();
		}

		$content = "<p>You are about to restore backup \"{$selected[0]}\".</p><p>This action will delete all data from the current database and cannot be undone.</p><p>Are you absolutely sure?</p>";
		lightboxHTML($type, $content);
	}
}
?>