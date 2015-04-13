<?php
require_once 'auth.php';
require_once 'sections.php';
require_once 'MongoBackup.class.php';
require_once 'api.php';

$search = array();
$search['sort'] = '';
foreach ($_GET as $key => $value) {
	if (!empty($value) || is_numeric($value)) {
		$search[$key] = $value;
	}
}

$backups = (new MongoBackup())->getBackups(DB_NAME);

if (isset($search['sort'])) {
	switch ($search['sort']) {
		case 'date_desc':usort($backups, function ($a, $b) {
				return (strtotime($a['time']) == strtotime($b['time'])) ? 0 : (strtotime($a['time']) < strtotime($b['time'])) ? 1 : -1;
			});
			break;
		case 'date_asc':usort($backups, function ($a, $b) {
				return (strtotime($a['time']) == strtotime($b['time'])) ? 0 : (strtotime($a['time']) < strtotime($b['time'])) ? -1 : 1;
			});
			break;
		case 'name_asc':usort($backups, function ($a, $b) {return $a['name'] > $b['name'];	});
			break;
		case 'name_desc':usort($backups, function ($a, $b) {return $a['name'] < $b['name'];	});
			break;
		case 'size_asc':usort($backups, function ($a, $b) {return $a['size'] > $b['size'];	});
			break;
		case 'size_desc':usort($backups, function ($a, $b) {return $a['size'] < $b['size'];	});
			break;
		case 'users_asc':usort($backups, function ($a, $b) {return $a['users'] > $b['users'];	});
			break;
		case 'users_desc':usort($backups, function ($a, $b) {return $a['users'] < $b['users'];	});
			break;
		case 'boards_asc':usort($backups, function ($a, $b) {return $a['boards'] > $b['boards'];	});
			break;
		case 'boards_desc':usort($backups, function ($a, $b) {return $a['boards'] < $b['boards'];	});
			break;
		case 'tickets_asc':usort($backups, function ($a, $b) {return $a['tickets'] > $b['tickets'];	});
			break;
		case 'tickets_desc':usort($backups, function ($a, $b) {return $a['tickets'] < $b['tickets'];	});
			break;
	}
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Contriboard Admin</title>
	<meta charset="UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js" defer></script>
    <script src="js/contra.js" defer></script>
    <link rel="stylesheet" href="css/bootstrap.css" type="text/css"/>
	<link rel="stylesheet" href="css/contra.css" type="text/css" />
	<link href="http://fonts.googleapis.com/css?family=Lato" rel="stylesheet" type="text/css" />
	<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<?php sidebar();?>
	<div id="main">
		<?php topbar();?>
		<div id="header">
			<h1>Backup</h1>
		</div>
		<div class='col-sm-12'>
			<div class="row">
				<div class="col-xs-12 col-sm-11 col-md-9 col-lg-7">
					<div class="item">
						<?php totalStats();?>
						<form id="search"></form>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12 col-lg-10">
					<div class="item">
						<?php backupTable($backups, $search);?>
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