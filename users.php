<?php
include_once 'sections.php';
include_once 'functions.php';

$search = array();
foreach ($_GET as $key => $value) {
	if (!empty($value)) {
		$search[$key] = $value;
	}
}

if (isset($_POST['users_delete'])):
	foreach ($_POST['user_selection'] as $id):
		$database->removeUser(array('_id' => new MongoId($id)));
	endforeach;
endif;

if (isset($_POST['users_message'])):
	foreach ($_POST['user_selection'] as $id):
		$user = $database->getUser(array('_id' => new MongoId($id)));
		sendMail($user['email'], "Message", "Hello, World!");
	endforeach;
endif;

$filter = array();

if (isset($search['email'])):
	$filter['email'] = array('$regex' => new MongoRegex('/^.*?' . addslashes($search['email']) . '.*?$/'));
endif;

if (isset($search['status']) && ($search['status'] == 'active' || $search['status'] == 'banned')):
	$filter['banned'] = array('$exists' => ($search['status'] == 'banned'));
endif;

$page = isset($search['page']) ? $search['page'] : 1;
if (!is_numeric($page)):
	$page = 1;
endif;

$rows = isset($search['rows']) ? $search['rows'] : 10;
if (!is_numeric($rows)):
	$rows = 10;
endif;

$sort = array();

$userCount = $rows;

if (!isset($search)) {
	$users = $database->getUserArray($filter, ($page - 1) * $rows, $rows);
	$userCount = count($users);
	$userCountTotal = $database->getUserCount();
} else {
	$users = $database->getUserArray($filter, 0, 0);

	foreach ($users as $key => $user) {
		if ((isset($search['boards_min']) && $user['boards'] < $search['boards_min']) ||
			(isset($search['boards_max']) && $user['boards'] > $search['boards_max']) ||
			(isset($search['tickets_min']) && $user['tickets'] < $search['tickets_min']) ||
			(isset($search['tickets_max']) && $user['tickets'] > $search['tickets_max']) ||
			(!isset($user['active']) && (isset($search['active_start']) || isset($search['active_end']))) ||
			(isset($search['active_start']) && (int) $user['active']->format('U') < strtotime($search['active_start'])) ||
			(isset($search['active_end']) && (int) $user['active']->format('U') > strtotime($search['active_end']))
		) {
			unset($users[$key]);
		}
	}

	$userCountTotal = count($users);
	if ($userCountTotal < $rows) {
		$userCount = $userCountTotal;
	}

	$users = array_slice($users, ($page - 1) * $rows, $rows);

}
$pageCount = intval(ceil($userCountTotal / $rows));

?>

<!DOCTYPE html>
<html lang="en">
<?php head('Users');?>
<body>
	<?php sidebar();?>
	<div id="main">
		<?php topbar();?>
		<div id="header">
			<h1>Users</h1>
		</div>
		<div class='col-sm-12'>
			<div class="row">
				<div class="col-sm-12">
					<div class="item">
						<div class="box gray">
							<h3 class="box-label">Search</h3>
							<div class="box-content">
								<form method="get">
									<div class="box-content-block">
										<table class="form-table">
											<tr>
												<td class="input-label">Status</td>
												<td><select name="status">
                                                    <option value="all">Show all</option>
                                                    <option value="active" <?php if (isset($search['status'])) {echo $search['status'] == 'active' ? 'selected' : '';}?> >Active</option>
                                                    <option value="banned" <?php if (isset($search['status'])) {echo $search['status'] == 'banned' ? 'selected' : '';}?>>Banned</option>
                                                </select></td>
											</tr>
											<tr>
												<td class="input-label">Email</td>
												<td colspan="3"><input class="full" type="text" name="email" value="<?php echo isset($search['email']) ? $search['email'] : ''?>"></td>
											</tr>
                                            <tr>
                                                <th></th>
                                                <th>Start</th>
                                                <th></th>
                                                <th>End</th>
                                            </tr>
                                            <tr>
                                                <td class="input-label">Last active</td>
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
											        <td class="input-label">Boards</td>
											        <td><input type="number" name="boards_min" value="<?php echo isset($search['boards_min']) ? $search['boards_min'] : 0?>"></td>
											        <td class="hyphen">-</td>
											        <td><input type="number" name="boards_max" value="<?php echo isset($search['boards_max']) ? $search['boards_max'] : 10000?>"></td>
											    </tr>
											    <tr>
											        <td class="input-label">Tickets</td>
											        <td><input type="number" name="tickets_min" value="<?php echo isset($search['tickets_min']) ? $search['tickets_min'] : 0?>"></td>
											        <td class="hyphen">-</td>
											        <td><input type="number" name="tickets_max" value="<?php echo isset($search['tickets_max']) ? $search['tickets_max'] : 10000?>"></td>
											    </tr>
											</tbody>
										</table>
									</div>
                                    <div class="box-content-block">
	                                    <div class="right">
									        <button type="submit" class="big-button gray" name="clear"><i class="fa fa-times fa-lg fa_fix"></i> Clear</button>
									        <button type="submit" class="big-button blue"><i class="fa fa-search fa-lg fa_fix"></i> Search</button>
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
						<?php userTable($users, $page, $pageCount, $userCount, $userCountTotal, $search);?>
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