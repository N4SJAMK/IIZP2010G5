<?php
require_once 'auth.php';
include_once 'sections.php';

$search = array();
$search['sort'] = '';
foreach ($_GET as $key => $value) {
	if (!empty($value) || is_numeric($value)) {
		$search[$key] = $value;
	}
}

if (isset($_POST['boards_delete'])):
	foreach ($_POST['board_selection'] as $id):
		$database->removeBoard(array('_id' => new MongoId($id)));
	endforeach;
endif;

$page = isset($_GET['page']) ? $_GET['page'] : 1;
if (!is_numeric($page)):
	$page = 1;
endif;

$rows = isset($_GET['rows']) ? $_GET['rows'] : 10;
if (!is_numeric($rows)):
	$rows = 10;
endif;

$filter = array();

/*
filtering here
 */

$boardCount = $rows;

if (empty($search)) {
	$boards = $database->getBoardArray($filter, ($page - 1) * $rows, $rows);
	$boardCount = count($boards);
	$boardCountTotal = $database->getBoardCount();
} else {
	$boards = $database->getBoardArray($filter, 0, 0);

	if (isset($search['sort'])) {
		switch ($search['sort']) {
			case 'owner_desc':usort($boards, function ($a, $b) {return strcmp($b['owner'], $a['owner']);	});
				break;
			case 'owner_asc':usort($boards, function ($a, $b) {return strcmp($a['owner'], $b['owner']);	});
				break;
			case 'tickets_asc':usort($boards, function ($a, $b) {return $a['tickets'] > $b['tickets'];	});
				break;
			case 'tickets_desc':usort($boards, function ($a, $b) {return $a['tickets'] < $b['tickets'];	});
				break;
			case 'guests_asc':usort($boards, function ($a, $b) {return $a['guests'] > $b['guests'];	});
				break;
			case 'guests_desc':usort($boards, function ($a, $b) {return $a['guests'] < $b['guests'];	});
				break;
			case 'active_desc':usort($boards, function ($a, $b) {return ($a['active'] == $b['active']) ? 0 : ($a['active'] < $b['active']) ? 1 : -1;	});
				break;
			case 'active_asc':usort($boards, function ($a, $b) {return ($a['active'] == $b['active']) ? 0 : ($a['active'] < $b['active']) ? -1 : 1;	});
				break;
			case 'created_desc':usort($boards, function ($a, $b) {return ($a['createdAt'] == $b['createdAt']) ? 0 : ($a['createdAt'] < $b['createdAt']) ? 1 : -1;	});
				break;
			case 'created_asc':usort($boards, function ($a, $b) {return ($a['createdAt'] == $b['createdAt']) ? 0 : ($a['createdAt'] < $b['createdAt']) ? -1 : 1;	});
				break;
		}
	}

	foreach ($boards as $key => $board) {
		if ((isset($search['guests_min']) && $board['guests'] < $search['guests_min']) ||
			(isset($search['guests_max']) && $board['guests'] > $search['guests_max']) ||
			(isset($search['tickets_min']) && $board['tickets'] < $search['tickets_min']) ||
			(isset($search['tickets_max']) && $board['tickets'] > $search['tickets_max']) ||
			(!isset($board['active']) && (isset($search['active_start']) || isset($search['active_end']))) ||
			(isset($search['active_start']) && (int) $board['active']->format('U') < strtotime($search['active_start'])) ||
			(isset($search['active_end']) && (int) $board['active']->format('U') > strtotime($search['active_end'])) ||
			(isset($search['created_start']) && (int) $board['createdAt']->format('U') < strtotime($search['created_start'])) ||
			(isset($search['created_end']) && (int) $board['createdAt']->format('U') > strtotime($search['created_end'])) ||
			(isset($search['owner']) && !preg_match('/^.*?' . addslashes($search['owner']) . '.*?$/', $board['owner']))
		) {
			unset($boards[$key]);
		}
	}

	$boardCountTotal = count($boards);
	if ($boardCountTotal < $rows) {
		$boardCount = $boardCountTotal;
	}

	$boards = array_slice($boards, ($page - 1) * $rows, $rows);

}

$pageCount = intval(ceil($boardCountTotal / $rows));

?>

<!DOCTYPE html>
<html lang="en">
<?php head('Boards');?>
<body>
    <?php sidebar();?>
    <div id="main">
        <?php topbar();?>
        <div id="header">
            <h1>Boards</h1>
        </div>
        <div class='col-sm-12'>
            <div class="row">
                <div class="col-sm-12">
                    <div class="item">
                        <div class="box gray">
                            <h3 class="box-label">Search</h3>
                            <div class="box-content">
                                <form method="get" id="search">
                                    <?php
if (isset($search['sort'])) {
	echo '<input type="hidden" name="sort" value="' . $search['sort'] . '">';
}
if (isset($search['page'])) {
	echo '<input type="hidden" name="page" value="' . $search['page'] . '">';
}

?>
                                    <div class="box-content-block">
                                        <table class="form-table">
                                            <tr>
                                                <td class="input-label">Owner</td>
                                                <td colspan="3"><input class="full" type="text" name="owner" value="<?php echo isset($search['owner']) ? $search['owner'] : ''?>"></td>
                                            </tr>
                                            <tr>
                                                <th></th>
                                                <th>Start</th>
                                                <th></th>
                                                <th>End</th>
                                            </tr>
                                            <tr>
                                                <td class="input-label" name="created_start">Created at</td>
                                                <td><input class="datepicker" type="text" name="created_start" value="<?php echo isset($search['created_start']) ? $search['created_start'] : ''?>"></td>
                                                <td class="hyphen">-</td>
                                                <td><input class="datepicker" type="text" name="created_end" value="<?php echo isset($search['created_end']) ? $search['created_end'] : ''?>"></td>
                                            </tr>
                                            <tr>
                                                <td class="input-label" name="active_start">Last active</td>
                                                <td><input class="datepicker" type="text" name="active_start" value="<?php echo isset($search['active_start']) ? $search['active_start'] : ''?>"></td>
                                                <td class="hyphen">-</td>
                                                <td><input class="datepicker" type="text" name="active_end" value="<?php echo isset($search['active_end']) ? $search['active_end'] : ''?>"></td>
                                            </tr>
                                        </table>
                                        <table class="form-table">
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>Min</th>
                                                    <th></th>
                                                    <th>Max</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="input-label">Tickets</td>
                                                    <td><input type="number" name="tickets_min" min="0" value="<?php echo isset($search['tickets_min']) ? $search['tickets_min'] : 0?>"></td>
                                                    <td class="hyphen">-</td>
                                                    <td><input type="number" name="tickets_max" min="0" value="<?php echo isset($search['tickets_max']) ? $search['tickets_max'] : 10000?>"></td>
                                                </tr>
                                                <tr>
                                                    <td class="input-label">Guests</td>
                                                    <td><input type="number" name="guests_min" min="0" value="<?php echo isset($search['guests_min']) ? $search['guests_min'] : 0?>"></td>
                                                    <td class="hyphen">-</td>
                                                    <td><input type="number" name="guests_max" min="0" value="<?php echo isset($search['guests_max']) ? $search['guests_max'] : 10000?>"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="box-content-block">
                                        <div class="right">
                                            <button type="submit" class="big-button gray" name="clear"><i class="fa fa-times fa-lg fa_fix"></i> Clear</button>
                                            <button type="submit" class="big-button blue" name="submit"><i class="fa fa-search fa-lg fa_fix"></i> Search</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-lg-10">
                    <div class="item">
                    	<?php boardTable($boards, $page, $pageCount, $boardCount, $boardCountTotal, $search);?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="lightbox-container" onclick="closeLightbox()">
        <div class="col-sm-12 col-md-12">
            <div class="row"></div>
                <div id="lightbox-loader" class="col-xs-10 col-sm-8 col-md-6 col-lg-4 col-xs-push-1 col-sm-push-2 col-md-push-3 col-lg-push-4"></div>
        </div>
    </div>
</body>
</html>