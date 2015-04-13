<?php
require_once 'auth.php';
include_once 'sections.php';

if (isset($_POST['adduser'])) {
	for ($i = 0; $i < 1; $i++):
		$database->addUser(substr(md5(rand()), 0, 5) . '@' . substr(md5(rand()), 0, 7) . '.' . 'fi', 'salasana');
	endfor;
}

if (isset($_POST['addboard'])) {
	for ($i = 0; $i < 50; $i++):
		$users = $database->getUsers();
		$database->addBoard(substr(md5(rand()), 0, 5), 3, 3, "", $users->limit(-1)->skip(rand() % $users->count())->getNext()['_id']);
	endfor;
}

if (isset($_POST['addticket'])) {
	$users = $database->getUsers();
	$boards = $database->getBoards();

	$database->addTicket(
		$boards->limit(-1)->skip(rand() % $boards->count())->getNext()['_id'],
		$users->limit(-1)->skip(rand() % $users->count())->getNext()['_id'],
		substr(md5(rand()), 0, 5),
		'#FFFFFF',
		rand() % 3, rand() % 3, rand() % 3);
}

function test() {
	global $database;

	echo <<<HTML
        <div class="box gray">
            <form action="", method="post">
            <input type="submit" name="adduser" value="Add new user">
            </form>
            <form action="", method="post">
            <input type="submit" name="addboard" value="Add new board">
            </form>
            <form action="", method="post">
            <input type="submit" name="addticket" value="Add new ticket">
            </form>
        </div>
HTML;
}

// Tietokantaolion luominen yms.

?>

<!DOCTYPE html>
<html lang="en">
<?php head('Statistics');?>
<body>
	<?php sidebar();?>
	<div id="main">
		<?php topbar();?>
		<div id="header">
			<h1>Statistics</h1>
		</div>
		<div class='col-sm-12'>
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-10 col-lg-8">
					<div class="item">
						<?php test();?>
					</div>
					<div class="item">
						<?php totalStats();?>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-10 col-lg-8">
					<div class="item">
						<?php activity();?>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>