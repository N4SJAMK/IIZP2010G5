<?php

require_once 'api.php';

function head($title) 
{
    echo <<<HTML
        <head>
            <title>Contriboard Admin - $title</title>
            <meta charset="UTF-8" />
            <link href="main.css" rel="stylesheet" type="text/css" />
            <link href="http://fonts.googleapis.com/css?family=Lobster" rel="stylesheet" type="text/css" />
            <link href="http://fonts.googleapis.com/css?family=Lato" rel="stylesheet" type="text/css" />
            <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        </head>
HTML;
}

function sidebar() 
{
    echo <<<HTML
        <div id="sidebar">
            <div class="logo">
                <img src="teamboard_logo.svg">
                <span class="logo-title">Contriboard</span>
                <span class="logo-subtitle">admin</span>
            </div>
            <div id="menu">
                <ul>
                    <li><a href="stats.php"><i class="fa fa-pie-chart fa-fw"></i> Statistics</a></li>
                    <li><a href="users.php"><i class="fa fa-users fa-fw"></i> Users</a></li>
                    <li><a href="boards.php"><i class="fa fa fa-th fa-fw"></i> Boards</a></li>
                    <li><a href="backup.php"><i class="fa fa-server fa-fw"></i> Backups</a></li>
                    <li><i class="fa fa-sign-out fa-fw"></i> Log out</li>
                </ul>
            </div>
        </div>
HTML;
}

function totalStats() 
{
    global $database;

    $stats = array();

    $stats['size'] = '?';
    $stats['users'] = $database->getUserCount();
    $stats['boards'] = $database->getBoardCount();
    $stats['tickets'] = $database->getTicketCount();
    $stats['guests'] = '?';

    echo <<<HTML
        <div class="box gray">
            <h3 class="box-label">Total stats</h3>
            <div class="box-content">
                <div class="data-table-full">
                    <table class="data-table">
                        <tr>
                            <td class="header medium">Used space</td>
                            <td class="header narrow">Users</td>
                            <td class="header narrow">Boards</td>
                            <td class="header narrow">Tickets</td>
                            <td class="header narrow">Guests</td>
                        </tr>
                        <tr>
                            <td>{$stats['size']}</td>
                            <td>{$stats['users']}</td>
                            <td>{$stats['boards']}</td>
                            <td>{$stats['tickets']}</td>
                            <td>{$stats['guests']}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
HTML;
}

function activity() 
{
    global $database;

    echo <<<HTML
        <div class="box gray">
            <h3 class="box-label">Activity</h3>
            <div class="box-content">
                <div class="data-table-full">
                    <table class="data-table">
                        <tr class="header-row">
                            <td class="medium-minus"></td>
                            <td class="header medium-minus">Active users</td>
                            <td class="header medium-minus">Active guests</td>
                            <td class="header medium-minus">Active boards</td>
                            <td class="header medium-minus">New boards</td>
                            <td class="header medium-minus">New tickets</td>
                        </tr>
                        <tr>
                            <td class="header ">Last 24 hours</td>
                            <td>{$database->getActiveUserCount(time() - 24*60*60, time())}</td>
                            <td>{$database->getActiveGuestCount(time() - 24*60*60, time())}</td>
                            <td>{$database->getActiveBoardCount(time() - 24*60*60, time())}</td>
                            <td>{$database->getNewBoardsCount(time() - 24*60*60, time())}</td>
                            <td>{$database->getNewTicketsCount(time() - 24*60*60, time())}</td>
                        </tr>
                        <tr>
                            <td class="header">Last 7 days</td>
                            <td>{$database->getActiveUserCount(time() - 24*60*60*7, time())}</td>
                            <td>{$database->getActiveGuestCount(time() - 24*60*60*7, time())}</td>
                            <td>{$database->getActiveBoardCount(time() - 24*60*60*7, time())}</td>
                            <td>{$database->getNewBoardsCount(time() - 24*60*60*7, time())}</td>
                            <td>{$database->getNewTicketsCount(time() - 24*60*60*7, time())}</td>
                        </tr>
                        <tr>
                            <td class="header">Last 30 days</td>
                            <td>{$database->getActiveUserCount(time() - 24*60*60*30, time())}</td>
                            <td>{$database->getActiveGuestCount(time() - 24*60*60*30, time())}</td>
                            <td>{$database->getActiveBoardCount(time() - 24*60*60*30, time())}</td>
                            <td>{$database->getNewBoardsCount(time() - 24*60*60*30, time())}</td>
                            <td>{$database->getNewTicketsCount(time() - 24*60*60*30, time())}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
HTML;
}

function userTable($page, $rows, $filter = array()) 
{ 
    global $database;

    $users = array();

    $results = $database->getUsers($filter)->skip(($page-1) * $rows)->limit($rows);

    foreach ($results as $user)
    {
        $boards = $database->getBoards(array('createdBy' => $user['_id']));
        $boardIds = iterator_to_array($boards->fields(array('_id' => true)));

        array_walk($boardIds, function (&$item, $key) {
            $item = $item['_id'];
        });

        $data = array();
        $data['id'] = $user['_id'];
        $data['username'] = $user['email'];
        $data['boards'] = $boards->count();
        $data['tickets'] = $database->getTicketCount(array('board' => array('$in' => $boardIds)));
        $data['guests'] = '?';
        $data['registered'] = '?';
        $active = $database->getUserLastActive($user['_id']);

        if (isset($active))
        {          
            $active = $active['createdAt']->toDateTime();
            $now = new DateTime();

            $diff = $now->diff( $active );
            $diffDays = (integer)$diff->format( "%R%a" );

            switch( $diffDays ) {
                case 0:
                    $active = "Today";
                    break;
                case -1:
                    $active = "Yesterday";
                    break;
                default:
                    $active = $active->format('d.m.Y');
            }
        }
        else
        {
            $active = 'Never';
        }

        $data['active'] = $active;
        $users[] = $data;
    }

    $pageCount = intval(ceil($results->count() / $rows));

    $pageButtons = '<p class="right">Page ';

    $pageButtons .= '<button name="page" value="1" class="page-button page-button'. ($page == 1 ? '-selected' : '') .'">1</button>';
    if ($page > 4 && $pageCount > 7) $pageButtons .= ' ... ';
    
    
    $to = min($pageCount, max(7, $page + 3));        
    $from = $page - 2;
    if ($to == $pageCount) $from -= $page + 3 - $pageCount;
    $from = max(2, $from);

    for ($i = $from; $i < $to; $i++)
    {
        $pageButtons .= '<button name="page" value="'.$i.'" class="page-button page-button'. ($page == $i ? '-selected' : '') .'">'.$i.'</button>';        
    }    

    
    if ($pageCount > 1)
    {        
        if ($page < $pageCount - 3 && $pageCount > 7) $pageButtons .= ' ... ' ;
        $pageButtons .= '<button name="page" value="'.$pageCount.'" class="page-button page-button'. ($page == $pageCount ? '-selected' : '') .'">'.$pageCount.'</button>';
    }

    $pageButtons .= '</p>';

    echo <<<HTML
        <div class="data-table-full">
            <div class="data-table-top">
            <form method="get" action="">
                <p class="left">Result: {$results->count(true)} / {$results->count()} users<span class="left-space">Show <select name="rows" onchange="this.form.submit()">
                <form method="get" action="">
HTML;
    echo '<option value="10" '.($rows == 10 ? 'selected' : '').'>10</option>';
    echo '<option value="25" '.($rows == 25 ? 'selected' : '').'>25</option>';
    echo '<option value="50" '.($rows == 50 ? 'selected' : '').'>50</option>';
    echo '<option value="100" '.($rows == 100 ? 'selected' : '').'>100</option>';

    echo <<<HTML
                </select> per page</span></p>                        
                {$pageButtons}
            </form>                   
            </div>
            <form id="user_form" method="post">
            <table class="data-table">
                <tr>
                    <td class="header square"><input class="checkbox" type="checkbox" name="user-selection" value="all"></td>
                    <td class="header wide text-left"><a href="">Email</a></td>
                    <td class="header narrow"><a href="">Boards</a></td>
                    <td class="header narrow"><a href="">Tickets</a></td>
                    <td class="header narrow"><a href="">Guests</a></td>
                    <td class="header medium-minus"><a href="">Registered</a></td>
                    <td class="header medium-minus"><a href="">Last Active</a></td>
                </tr>
HTML;
    $i = 1;
    foreach($users as $user) 
    {
            echo <<<HTML
                <tr>
                    <td><input class="checkbox" type="checkbox" name="user_selection[]" value="{$user['id']}"></td>
                    <td class="text-left">{$user['username']}</td>
                    <td>{$user['boards']}</td>
                    <td>{$user['tickets']}</td>
                    <td>{$user['guests']}</td>
                    <td>{$user['registered']}</td>
                    <td>{$user['active']}</td>
                </tr>
HTML;
        $i++;
    }
    echo <<<HTML
            </table>
            </form>
            <form method="get" action="">
            <input type="hidden" name="rows" value="{$rows}">
            $pageButtons
            </form>   
        </div>
        <div>
            <div class="button-group left">
                <button form="user_form" type="submit" class="big-button blue" name="users_message"><i class="fa fa-comment fa-lg fa_fix"></i> Message</button>
                <button form="user_form" type="submit" class="big-button yellow" name="users_password_reset"><i class="fa fa-random fa-lg"></i> Reset password</button>
                <button form="user_form" type="submit" class="big-button red" name="users_delete"><i class="fa fa-trash-o fa-lg fa_fix"></i> Delete</button>
            </div>
        </div>
HTML;
}


function boardTable($page, $rows, $filter = array()) 
{ 
    global $database;

    $boards = array();

    $results = $database->getBoards($filter)->skip(($page-1) * $rows)->limit($rows);

    foreach ($results as $board)
    {
        $data = array();
        $data['id'] = $board['_id'];
        $data['owner'] = $database->getUser(array('_id' => new MongoID($board['createdBy'])))['email'];
        $data['tickets'] = $database->getTicketCount(array('board' => $board['_id']));
        $data['guests'] = '?';

        $data['created'] = $database->getEvent(array('board' => $board['_id'], 'type' => 'BOARD_CREATE'))['createdAt']->toDateTime()->format('d.m.Y H:i:s');


        $active = $database->getBoardLastActive($board['_id']);
        if (isset($active))
        {          
            $active = $active['createdAt']->toDateTime();
            $now = new DateTime();

            $diff = $now->diff( $active );
            $diffDays = (integer)$diff->format( "%R%a" );

            switch( $diffDays ) {
                case 0:
                    $active = "Today";
                    break;
                case -1:
                    $active = "Yesterday";
                    break;
                default:
                    $active = $active->format('d.m.Y');
            }
        }
        else
        {
            $active = 'Never';
        }

        $data['active'] = $active;
        $boards[] = $data;
    }

    $pageCount = intval(ceil($results->count() / $rows));

    $pageButtons = '<p class="right">Page ';

    $pageButtons .= '<button name="page" value="1" class="page-button page-button'. ($page == 1 ? '-selected' : '') .'">1</button>';
    if ($page > 4 && $pageCount > 7) $pageButtons .= ' ... ';
    
    
    $to = min($pageCount, max(7, $page + 3));        
    $from = $page - 2;
    if ($to == $pageCount) $from -= $page + 3 - $pageCount;
    $from = max(2, $from);

    for ($i = $from; $i < $to; $i++)
    {
        $pageButtons .= '<button name="page" value="'.$i.'" class="page-button page-button'. ($page == $i ? '-selected' : '') .'">'.$i.'</button>';        
    }    

    
    if ($pageCount > 1)
    {        
        if ($page < $pageCount - 3 && $pageCount > 7) $pageButtons .= ' ... ' ;
        $pageButtons .= '<button name="page" value="'.$pageCount.'" class="page-button page-button'. ($page == $pageCount ? '-selected' : '') .'">'.$pageCount.'</button>';
    }

    $pageButtons .= '</p>';

    echo <<<HTML
        <div class="data-table-full">
            <div class="data-table-top">
            <form method="get" action="">
                <p class="left">Result: {$results->count(true)} / {$results->count()} boards<span class="left-space">Show <select name="rows" onchange="this.form.submit()">
                <form method="get" action="">
HTML;
    echo '<option value="10" '.($rows == 10 ? 'selected' : '').'>10</option>';
    echo '<option value="25" '.($rows == 25 ? 'selected' : '').'>25</option>';
    echo '<option value="50" '.($rows == 50 ? 'selected' : '').'>50</option>';
    echo '<option value="100" '.($rows == 100 ? 'selected' : '').'>100</option>';

    echo <<<HTML
                </select> per page</span></p>                        
                {$pageButtons}
            </form>                
            </div>
            <form method="post" id="board_form">
            <table class="data-table">
                <tr>
                    <td class="header square"><input class="checkbox" type="checkbox" name="board-selection" value="all"></td>
                    <td class="header narrow"><a href="">Owner</a></td>
                    <td class="header narrow"><a href="">Tickets</a></td>
                    <td class="header narrow"><a href="">Guests</a></td>
                    <td class="header medium-minus"><a href="">Created</a></td>
                    <td class="header medium-minus"><a href="">Last Active</a></td>
                </tr>
HTML;
    $i = 1;
    foreach($boards as $board) 
    {
            echo <<<HTML
                <tr>
                    <td><input class="checkbox" type="checkbox" name="board_selection[]" value="{$board['id']}"></td>
                    <td>{$board['owner']}</td>
                    <td>{$board['tickets']}</td>
                    <td>{$board['guests']}</td>
                    <td>{$board['created']}</td>
                    <td>{$board['active']}</td>
                </tr>
HTML;
        $i++;
    }
    echo <<<HTML
            </table>
            </form>
            <form method="get" action="">
            <input type="hidden" name="rows" value="{$rows}">
            $pageButtons
            </form>   
        </div>
        <div>
            <div class="button-group left">
                <button  type="submit" form="board_form" class="big-button red" name="boards_delete"><i class="fa fa-trash-o fa-lg fa_fix"></i> Delete</button>
            </div>
        </div>
HTML;
}
?>