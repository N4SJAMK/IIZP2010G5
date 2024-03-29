<?php
require_once 'api.php';
require_once 'functions.php';

function head($title) {
	echo <<<HTML
        <head>
        <title>Contriboard Admin - $title</title>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js" defer></script>
        <script src="js/jquery-ui.min.js" defer></script>
        <script src="js/contra.js" defer></script>
        <link rel="stylesheet" href="css/bootstrap.css" type="text/css"/>
        <link rel="stylesheet" href="css/jquery-ui.min.css">
        <link rel="stylesheet" href="css/contra.css" type="text/css" />
        <link href="http://fonts.googleapis.com/css?family=Lato" rel="stylesheet" type="text/css" />
        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        </head>
HTML;
}

function sidebar() {
	echo <<<HTML
    <nav id="sidebar">
        <div class="logo">
            <h1 class="title"><img src="img/teamboard_logo.svg">Contr<span class="turquoise">A</span></span></h1>
            <h2 class="subtitle">Contriboard <span class="turquoise">Admin</span></h2>
        </div>
        <div class="menu">
            <ul>
                <li><a href="stats.php"><i class="fa fa-pie-chart fa-fw"></i> Statistics</a></li>
                <li><a href="users.php"><i class="fa fa-users fa-fw"></i> Users</a></li>
                <li><a href="boards.php"><i class="fa fa fa-th fa-fw"></i> Boards</a></li>
                <li><a href="backup.php"><i class="fa fa-server fa-fw"></i> Backups</a></li>
                <li><a href="logout.php"><i class="fa fa-sign-out fa-fw"></i> Log out</a></li>
            </ul>
        </div>
    </nav>
HTML;
}

function topbar() {
	echo <<<HTML
    <nav id="topbar">
        <div class="menu">
            <ul>
                <li><a href="stats.php"><i class="fa fa-pie-chart fa-fw"></i> Statistics</a></li>
                <li><a href="users.php"><i class="fa fa-users fa-fw"></i> Users</a></li>
                <li><a href="boards.php"><i class="fa fa fa-th fa-fw"></i> Boards</a></li>
                <li><a href="backup.php"><i class="fa fa-server fa-fw"></i> Backups</a></li>
                <li><a href="#"><i class="fa fa-sign-out fa-fw"></i> Log out</a></li>
            </ul>
        </div>
    </nav>
HTML;
}

function pageButtons($page, $pageCount) {
	$pageButtons = '<p class="right">Page ';

	$pageButtons .= '<button form="search" name="page" value="1" class="page-button page-button' . ($page == 1 ? '-selected' : '') . '">1</button>';
	if ($page > 4 && $pageCount > 7) {
		$pageButtons .= ' ... ';
	}

	$to = min($pageCount, max(7, $page + 3));
	$from = $page - 2;
	if ($to == $pageCount) {
		$from -= $page + 3 - $pageCount;
	}

	$from = max(2, $from);

	for ($i = $from; $i < $to; $i++) {
		$pageButtons .= '<button name="page" form="search" value="' . $i . '" class="page-button page-button' . ($page == $i ? '-selected' : '') . '">' . $i . '</button>';
	}

	if ($pageCount > 1) {
		if ($page < $pageCount - 3 && $pageCount > 7) {
			$pageButtons .= ' ... ';
		}

		$pageButtons .= '<button name="page" form="search" value="' . $pageCount . '" class="page-button page-button' . ($page == $pageCount ? '-selected' : '') . '">' . $pageCount . '</button>';
	}

	$pageButtons .= '</p>';

	return $pageButtons;
}

function activityText($date) {
	if (!isset($date)) {
		return 'Never';
	}

	$now = new DateTime();

	$diff = $now->diff($date);
	$diffDays = (integer) $diff->format("%R%a");

	switch ($diffDays) {
		case 0:return "Today";
		case -1:return "Yesterday";
	}

	return $date->format('d.m.Y H:i');
}

function totalStats() {
	global $database;

	$stats = array();

	$stats['size'] = formatBytes($database->getDatabaseSize(), 2);
	$stats['reserved'] = formatBytes($database->getDatabaseSize(true), 2);
	$stats['users'] = $database->getUserCount();
	$stats['boards'] = $database->getBoardCount();
	$stats['tickets'] = $database->getTicketCount();
	$stats['guests'] = '?';

	echo <<<HTML
        <div class="box full gray">
            <h3 class="box-label">Total stats</h3>
            <div class="box-content">
                <div class="box-content-block">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Used space</th>
                                <th>Reserved space</th>
                                <th>Users</th>
                                <th>Boards</th>
                                <th>Tickets</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                            <td>{$stats['size']}</td>
                            <td>{$stats['reserved']}</td>
                            <td>{$stats['users']}</td>
                            <td>{$stats['boards']}</td>
                            <td>{$stats['tickets']}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
HTML;
}

function activity() {
	global $database;

	echo <<<HTML
        <div class="box full gray">
            <h3 class="box-label">Activity</h3>
            <div class="box-content">
                <div class="box-content-block">
                    <table class="data-table">
                        <thead>
                            <tr class="header-row">
                                <th class="empty"></th>
                                <th>Active users</th>
                                <th>Active guests</th>
                                <th>Active boards</th>
                                <th>New boards</th>
                                <th>New tickets</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th class="header">Last 24 hours</th>
                                <td>{$database->getActiveUserCount(time()-24*60*60,time())}</td>
                                <td>{$database->getActiveGuestCount(time()-24*60*60,time())}</td>
                                <td>{$database->getActiveBoardCount(time()-24*60*60,time())}</td>
                                <td>{$database->getNewBoardsCount(time()-24*60*60,time())}</td>
                                <td>{$database->getNewTicketsCount(time()-24*60*60,time())}</td>
                            </tr>
                            <tr>
                                <th class="header">Last 7 days</th>
                                <td>{$database->getActiveUserCount(time()-24*60*60*7,time())}</td>
                                <td>{$database->getActiveGuestCount(time()-24*60*60*7,time())}</td>
                                <td>{$database->getActiveBoardCount(time()-24*60*60*7,time())}</td>
                                <td>{$database->getNewBoardsCount(time()-24*60*60*7,time())}</td>
                                <td>{$database->getNewTicketsCount(time()-24*60*60*7,time())}</td>
                            </tr>
                            <tr>
                                <th class="header">Last 30 days</th>
                                <td>{$database->getActiveUserCount(time()-24*60*60*30,time())}</td>
                                <td>{$database->getActiveGuestCount(time()-24*60*60*30,time())}</td>
                                <td>{$database->getActiveBoardCount(time()-24*60*60*30,time())}</td>
                                <td>{$database->getNewBoardsCount(time()-24*60*60*30,time())}</td>
                                <td>{$database->getNewTicketsCount(time()-24*60*60*30,time())}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
HTML;
}

function userTable($data, $page, $pageCount, $userCount, $userCountTotal, $usersStatus, $prevSearch) {
	$pageButtons = pageButtons($page, $pageCount);

	echo <<<HTML
        <div class="table-box">
            <div class="box-content-row">
                <p class="left">Result: {$userCount} / {$userCountTotal} users</p>
                {$pageButtons}
            </div>
            <div class="box-content-row">

HTML;
	echo '
                    <form id="prev-search">
                    <input type="hidden" name="users_status" value="' . $usersStatus . '">
                    <input type="hidden" class="prev-search" name="filter_results" value="' . (isset($userCountTotal) ? $userCountTotal : '') . '">
                    <input type="hidden" class="prev-search" name="filter_status" value="' . (isset($prevSearch['status']) ? $prevSearch['status'] : 'all') . '">
                    <input type="hidden" class="prev-search" name="filter_email" value="' . (isset($prevSearch['email']) ? $prevSearch['email'] : '') . '">
                    <input type="hidden" class="prev-search" name="filter_active_start" value="' . (isset($prevSearch['active_start']) ? $prevSearch['active_start'] : '') . '">
                    <input type="hidden" class="prev-search" name="filter_active_end" value="' . (isset($prevSearch['active_end']) ? $prevSearch['active_end'] : '') . '">
                    <input type="hidden" class="prev-search" name="filter_boards_min" value="' . (isset($prevSearch['boards_min']) ? $prevSearch['boards_min'] : 0) . '">
                    <input type="hidden" class="prev-search" name="filter_boards_max" value="' . (isset($prevSearch['boards_max']) ? $prevSearch['boards_max'] : 10000) . '">
                    <input type="hidden" class="prev-search" name="filter_tickets_min" value="' . (isset($prevSearch['tickets_min']) ? $prevSearch['tickets_min'] : 0) . '">
                    <input type="hidden" class="prev-search" name="filter_tickets_max" value="' . (isset($prevSearch['tickets_max']) ? $prevSearch['tickets_max'] : 10000) . '">
                    <input type="hidden" class="prev-search" name="filter_guests_min" value="' . (isset($prevSearch['guests_min']) ? $prevSearch['guests_min'] : 0) . '">
                    <input type="hidden" class="prev-search" name="filter_guests_max" value="' . (isset($prevSearch['guests_max']) ? $prevSearch['guests_max'] : 10000) . '">
                    </form>
        ';
	echo <<<HTML
                    <table class="data-table">
                        <thead>
                            <tr class="header-row">
                                <th id="selection-dropdown-switch" class="square ">
                                    <div id="selection-dropdown">
                                        <ul>
                                            <li><a onclick="selectAll(0); switchDropdown();">Select none</a></li>
                                            <li><a onclick="selectAll(1); switchDropdown();">Select all on page</a></li>
                                            <li><a onclick="selectAll(2); switchDropdown();">Select all results</a></li>
                                        </ul>
                                    </div>
                                    <input type="checkbox" id="selection-all" name="selection-all" value="allResults" onclick="event.preventDefault(); switchDropdown();"><label for="selection-all"></label>
                                </th>
HTML;
	echo '
                                <th class="text-left"><button form="search" name="sort" value="' .
	($prevSearch['sort'] == 'username_desc' ? 'username_asc' : 'username_desc') . '" class="table-button">' .
	($prevSearch['sort'] == 'username_desc' ? '<i class="fa fa-fw fa-caret-down"></i>' : ($prevSearch['sort'] == 'username_asc' ? '<i class="fa fa-fw fa-caret-up"></i>' : '')) . 'Username</button></th>
                                <th><button form="search" name="sort" value="' .
	($prevSearch['sort'] == 'boards_desc' ? 'boards_asc' : 'boards_desc') . '" class="table-button">' .
	($prevSearch['sort'] == 'boards_desc' ? '<i class="fa fa-fw fa-caret-down"></i>' : ($prevSearch['sort'] == 'boards_asc' ? '<i class="fa fa-fw fa-caret-up"></i>' : '')) . 'Boards</button></th>
                                        <th><button form="search" name="sort" value="' .
	($prevSearch['sort'] == 'tickets_desc' ? 'tickets_asc' : 'tickets_desc') . '" class="table-button">' .
	($prevSearch['sort'] == 'tickets_desc' ? '<i class="fa fa-fw fa-caret-down"></i>' : ($prevSearch['sort'] == 'tickets_asc' ? '<i class="fa fa-fw fa-caret-up"></i>' : '')) . 'Tickets</button></th>
                                <th><button form="search" name="sort" value="' .
	($prevSearch['sort'] == 'active_desc' ? 'active_asc' : 'active_desc') . '" class="table-button">' .
	($prevSearch['sort'] == 'active_desc' ? '<i class="fa fa-fw fa-caret-down"></i>' : ($prevSearch['sort'] == 'active_asc' ? '<i class="fa fa-fw fa-caret-up"></i>' : '')) . 'Last active</button></th>
    ';
	echo <<<HTML
                            </tr>
                        </thead>
                        <tbody>
HTML;
	$i = 1;
	foreach ($data as $user) {
		echo '
                <tr ' . (isset($user['banned']) ? 'class="banned"' : '') . '>
                    <td class="square"><input type="checkbox" id="selection' . $i . '" name="selection" value="' . $user['_id'] . '" onclick="check(this, \'ban-button\');"><label for="selection' . $i . '"></label></td>
                    <td class="text-left break"><a href="boards.php?käyttäjähaku">' . $user['email'] . '</a> <i ' . ($user['banned'] ? 'class="fa fa-gavel fa_fix"' : '') . '></i></td>
                    <td>' . $user['boards'] . '</td>
                    <td>' . $user['tickets'] . '</td>
                    <td>' . activityText($user['active']) . '</td>
                </tr>
            ';
		$i++;
	}
	echo <<<HTML
                    </tbody>
                </table>
            </form>
        </div>
        <div class="box-content-row">
            {$pageButtons}
        </div>
        <div class="box-content-row">
            <div class="left">
                <button type="button" form="prev-search" class="big-button blue" name="message_user" onclick="loadLightbox(this)"><i class="fa fa-comment fa-lg fa_fix"></i> Message</button>
                <button type="button" form="prev-search" class="big-button purple" name="reset_password" onclick="loadLightbox(this)"><i class="fa fa-random fa-lg"></i> Reset password</button>
                <button type="button" form="prev-search" id="ban-button" class="big-button red" name="ban_user" onclick="loadLightbox(this)"><i class="fa fa-gavel fa-lg fa_fix"></i> Ban</button>
                <button type="button" form="prev-search" class="big-button red" name="delete_user" onclick="loadLightbox(this)"><i class="fa fa-trash-o fa-lg fa_fix"></i> Delete</button>
            </div>
        </div>
    </div>
HTML;
}

function backupTable($data, $prevSearch) {
	$count = count($data);
	echo <<<HTML
    <div class="table-box">
        <div class="box-content-row">
            <form>
                <input type="hidden" class="prev-search" name="filter_results" value="{$count}">
                <table class="data-table">
                    <thead>
                        <tr class="header-row">
                            <th id="selection-dropdown-switch" class="square ">
                                <div id="selection-dropdown">
                                    <ul>
                                        <li><a onclick="selectAll(0); switchDropdown();">Select none</a></li>
                                        <li><a onclick="selectAll(2); switchDropdown();">Select all</a></li>
                                    </ul>
                                </div>
                                <input type="checkbox" id="selection-all" name="selection-all" value="allResults" onclick="event.preventDefault(); switchDropdown();"><label for="selection-all"></label>
                            </th>
HTML;
	echo '
                                <th><button form="search" name="sort" value="' .
	($prevSearch['sort'] == 'date_desc' ? 'date_asc' : 'date_desc') . '" class="table-button">' .
	($prevSearch['sort'] == 'date_desc' ? '<i class="fa fa-fw fa-caret-down"></i>' : ($prevSearch['sort'] == 'date_asc' ? '<i class="fa fa-fw fa-caret-up"></i>' : '')) . 'Date</button></th>
                                <th class="text-left"><button form="search" name="sort" value="' .
	($prevSearch['sort'] == 'name_desc' ? 'name_asc' : 'name_desc') . '" class="table-button">' .
	($prevSearch['sort'] == 'name_desc' ? '<i class="fa fa-fw fa-caret-down"></i>' : ($prevSearch['sort'] == 'name_asc' ? '<i class="fa fa-fw fa-caret-up"></i>' : '')) . 'Name</button></th>
                                        <th><button form="search" name="sort" value="' .
	($prevSearch['sort'] == 'size_desc' ? 'size_asc' : 'size_desc') . '" class="table-button">' .
	($prevSearch['sort'] == 'size_desc' ? '<i class="fa fa-fw fa-caret-down"></i>' : ($prevSearch['sort'] == 'size_asc' ? '<i class="fa fa-fw fa-caret-up"></i>' : '')) . 'Size</button></th>
                                                <th><button form="search" name="sort" value="' .
	($prevSearch['sort'] == 'users_desc' ? 'users_asc' : 'users_desc') . '" class="table-button">' .
	($prevSearch['sort'] == 'users_desc' ? '<i class="fa fa-fw fa-caret-down"></i>' : ($prevSearch['sort'] == 'users_asc' ? '<i class="fa fa-fw fa-caret-up"></i>' : '')) . 'Users</button></th>
                                        <th><button form="search" name="sort" value="' .
	($prevSearch['sort'] == 'boards_desc' ? 'boards_asc' : 'boards_desc') . '" class="table-button">' .
	($prevSearch['sort'] == 'boards_desc' ? '<i class="fa fa-fw fa-caret-down"></i>' : ($prevSearch['sort'] == 'boards_asc' ? '<i class="fa fa-fw fa-caret-up"></i>' : '')) . 'Boards</button></th>
                                        <th><button form="search" name="sort" value="' .
	($prevSearch['sort'] == 'tickets_desc' ? 'tickets_asc' : 'tickets_desc') . '" class="table-button">' .
	($prevSearch['sort'] == 'tickets_desc' ? '<i class="fa fa-fw fa-caret-down"></i>' : ($prevSearch['sort'] == 'tickets_asc' ? '<i class="fa fa-fw fa-caret-up"></i>' : '')) . 'Tickets</button></th>
    ';
	echo <<<HTML
                            </tr>
                        </thead>
                        <tbody>
HTML;

	$i = 0;
	foreach ($data as $backup) {
		echo '<tr>
                <td class="square"><input type="checkbox" id="selection' . $i . '" name="selection" value="' . $backup['name'] . '" onclick="check(this);"><label for="selection' . $i . '"></label></td>
                <td>' . $backup['time'] . '</td>
                <td class="text-left break">' . $backup['name'] . '</td>
                <td>' . formatBytes($backup['size'], 2) . '</td>
                <td>' . $backup['users'] . '</td>
                <td>' . $backup['boards'] . '</td>
                <td>' . $backup['tickets'] . '</td>
            </tr>';
		$i++;
	}

	echo <<<HTML
                    </tbody>
                </table>
            </form>
        </div>
        <div class="box-content-row">
            <div class="left">
                <button type="button" class="big-button blue" name="create_backup" onclick="loadLightbox(this, 0)"><i class="fa fa fa-camera fa-lg fa_fix"></i> Create new</button>
                <button type="button" class="big-button red" name="delete_backup" onclick="loadLightbox(this)"><i class="fa fa-trash-o fa-lg fa_fix"></i> Delete</button>
                <button type="button" class="big-button red" name="restore_backup" onclick="loadLightbox(this, 1)"><i class="fa fa-reply fa-lg"></i> Restore</button>
            </div>
        </div>
    </div>
HTML;
}

function boardTable($data, $page, $pageCount, $boardCount, $boardCountTotal, $prevSearch) {
	$pageButtons = pageButtons($page, $pageCount);
	echo <<<HTML
        <div class="table-box">
            <div class="box-content-row">
                <p class="left">Result: $boardCount / $boardCountTotal boards</p>
                {$pageButtons}
            </div>
            <div class="box-content-row">
HTML;
	echo '
                    <form id="prev-search">
                    <input type="hidden" class="prev-search" name="filter_results" value="' . $boardCountTotal . '">
                    <input type="hidden" class="prev-search" name="filter_owner" value="' . (isset($prevSearch['owner']) ? $prevSearch['owner'] : '') . '">
                    <input type="hidden" class="prev-search" name="filter_created_start" value="' . (isset($prevSearch['created_start']) ? $prevSearch['created_start'] : '') . '">
                    <input type="hidden" class="prev-search" name="filter_created_end" value="' . (isset($prevSearch['created_end']) ? $prevSearch['created_end'] : '') . '">
                    <input type="hidden" class="prev-search" name="filter_active_start" value="' . (isset($prevSearch['active_start']) ? $prevSearch['active_start'] : '') . '">
                    <input type="hidden" class="prev-search" name="filter_active_end" value="' . (isset($prevSearch['active_end']) ? $prevSearch['active_end'] : '') . '">
                    <input type="hidden" class="prev-search" name="filter_boards_min" value="' . (isset($prevSearch['boards_min']) ? $prevSearch['boards_min'] : 0) . '">
                    <input type="hidden" class="prev-search" name="filter_boards_max" value="' . (isset($prevSearch['boards_max']) ? $prevSearch['boards_max'] : 10000) . '">
                    <input type="hidden" class="prev-search" name="filter_tickets_min" value="' . (isset($prevSearch['tickets_min']) ? $prevSearch['tickets_min'] : 0) . '">
                    <input type="hidden" class="prev-search" name="filter_tickets_max" value="' . (isset($prevSearch['tickets_max']) ? $prevSearch['tickets_max'] : 10000) . '">
                    <input type="hidden" class="prev-search" name="filter_guests_min" value="' . (isset($prevSearch['guests_min']) ? $prevSearch['guests_min'] : 0) . '">
                    <input type="hidden" class="prev-search" name="filter_guests_max" value="' . (isset($prevSearch['guests_max']) ? $prevSearch['guests_max'] : 10000) . '">
                    </form>
                    ';
	echo <<<HTML
                        <table class="data-table">
                        <thead>
                            <tr class="header-row">
                                <th id="selection-dropdown-switch" class="square ">
                                    <div id="selection-dropdown">
                                        <ul>
                                            <li><a onclick="selectAll(0); switchDropdown();">Select none</a></li>
                                            <li><a onclick="selectAll(1); switchDropdown();">Select all on page</a></li>
                                            <li><a onclick="selectAll(2); switchDropdown();">Select all results</a></li>
                                        </ul>
                                    </div>
                                <input type="checkbox" id="selection-all" name="selection-all" value="allResults" onclick="event.preventDefault(); switchDropdown();"><label for="selection-all"></label>
                                </th>
HTML;
	echo '
                                <th class="text-left"><button form="search" name="sort" value="' .
	($prevSearch['sort'] == 'owner_desc' ? 'owner_asc' : 'owner_desc') . '" class="table-button">' .
	($prevSearch['sort'] == 'owner_desc' ? '<i class="fa fa-fw fa-caret-down"></i>' : ($prevSearch['sort'] == 'owner_asc' ? '<i class="fa fa-fw fa-caret-up"></i>' : '')) . 'Owner</button></th>
                                <th><button form="search" name="sort" value="' .
	($prevSearch['sort'] == 'tickets_desc' ? 'tickets_asc' : 'tickets_desc') . '" class="table-button">' .
	($prevSearch['sort'] == 'tickets_desc' ? '<i class="fa fa-fw fa-caret-down"></i>' : ($prevSearch['sort'] == 'tickets_asc' ? '<i class="fa fa-fw fa-caret-up"></i>' : '')) . 'Tickets</button></th>
                                        <th><button form="search" name="sort" value="' .
	($prevSearch['sort'] == 'guests_desc' ? 'guests_asc' : 'guests_desc') . '" class="table-button">' .
	($prevSearch['sort'] == 'guests_desc' ? '<i class="fa fa-fw fa-caret-down"></i>' : ($prevSearch['sort'] == 'guests_asc' ? '<i class="fa fa-fw fa-caret-up"></i>' : '')) . 'Guests</button></th>
                                <th><button form="search" name="sort" value="' .
	($prevSearch['sort'] == 'created_desc' ? 'created_asc' : 'created_desc') . '" class="table-button">' .
	($prevSearch['sort'] == 'created_desc' ? '<i class="fa fa-fw fa-caret-down"></i>' : ($prevSearch['sort'] == 'created_asc' ? '<i class="fa fa-fw fa-caret-up"></i>' : '')) . 'Created at</button></th>
                                <th><button form="search" name="sort" value="' .
	($prevSearch['sort'] == 'active_desc' ? 'active_asc' : 'active_desc') . '" class="table-button">' .
	($prevSearch['sort'] == 'active_desc' ? '<i class="fa fa-fw fa-caret-down"></i>' : ($prevSearch['sort'] == 'active_asc' ? '<i class="fa fa-fw fa-caret-up"></i>' : '')) . 'Last active</button></th>
    ';
	echo <<<HTML
                            </tr>
                        </thead>
                        <tbody>
HTML;
	$i = 1;
	foreach ($data as $board) {
		echo '<tr>
                    <td class="square"><input type="checkbox" id="selection' . $i . '" name="selection" value="' . $board['_id'] . '" onclick="check(this);"><label for="selection' . $i . '"></label></td>
                    <td class="text-left break">' . $board['owner'] . '</td>
                    <td>' . $board['tickets'] . '</td>
                    <td>' . ($board['shared'] ? $board['guests'] : '<i class="fa fa-chain-broken"></i>') . '</td>
                    <td>' . activityText($board['createdAt']) . '</td>
                    <td>' . activityText($board['active']) . '</td>
                </tr>';
		$i++;
	}
	echo <<<HTML
                    </tbody>
                </table>
        </div>
        <div class="box-content-row">
            {$pageButtons}
        </div>
        <div class="box-content-row">
            <div class="left">
                <button type="button" class="big-button purple" name="unshare_board" onclick="loadLightbox(this)"><i class="fa fa-chain-broken fa-lg"></i> Unshare</button>
                <button type="button" class="big-button red" name="delete_board" onclick="loadLightbox(this)"><i class="fa fa-trash-o fa-lg fa_fix"></i> Delete</button>
            </div>
        </div>
    </div>
HTML;
}
?>