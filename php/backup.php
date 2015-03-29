<?php
include_once 'sections.php';

// Tietokantaolion luominen yms.
	
?>

<!DOCTYPE html>
<html lang="en">
<?php head('Backup'); ?>
<body>
	<?php sidebar(); ?>
	<div id="top-bar">
			<h2 id="top-header">Backups</h2>
	</div>
	<div id="content">
		<div class="item">
			<div class="box gray">
				<h3 class="box-label">Current database</h3>
				<div class="box-content">
					<div class="data-table-full">
						<table class="data-table">
							<tr>
								<td class="header narrow"><a href="">Size</a></td>
								<td class="header narrow"><a href="">Users</a></td>
								<td class="header narrow"><a href="">Boards</a></td>
								<td class="header narrow"><a href="">Tickets</a></td>
								<td class="header narrow"><a href="">Guests</a></td>
							</tr>
							<tr>
								<td>1.86 GB</td>
								<td>129</td>
								<td>547</td>
								<td>1346</td>
								<td>1000</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div class="item">
			<div class="data-table-full">
				<form>
				<table class="data-table">
					<tr>
						<td class="header square"><input class="checkbox" type="checkbox" name="user-selection" value="all"></td>
						<td class="header medium"><a href="">Date</a></td>
						<td class="header medium-plus text-left"><a href="">Name</a></td>
						<td class="header narrow"><a href="">Size</a></td>
						<td class="header narrow"><a href="">Users</a></td>
						<td class="header narrow"><a href="">Boards</a></td>
						<td class="header narrow"><a href="">Tickets</a></td>
						<td class="header narrow"><a href="">Guests</a></td>
					</tr>
					<tr>
						<td><input class="checkbox" type="checkbox" name="user-selection" value="1"></td>
						<td>09.02.2015 19:45</td>
						<td class="text-left">Backup-2015-02-09</td>
						<td>1.85 GB</td>
						<td>128</td>
						<td>546</td>
						<td>1345</td>
						<td>999</td>
					</tr>
					<tr>
						<td><input class="checkbox" type="checkbox" name="user-selection" value="1"></td>
						<td>08.02.2015 16:21</td>
						<td class="text-left">Backup-2015-02-08</td>
						<td>1.83 GB</td>
						<td>121</td>
						<td>538</td>
						<td>1300</td>
						<td>981</td>
					</tr>
					<tr>
						<td><input class="checkbox" type="checkbox" name="user-selection" value="1"></td>
						<td>01.02.2015 16:30</td>
						<td class="text-left">Backup-2015-02-01</td>
						<td>1.64 GB</td>
						<td>114</td>
						<td>520</td>
						<td>1150</td>
						<td>915</td>
					</tr>
					<tr>
						<td><input class="checkbox" type="checkbox" name="user-selection" value="1"></td>
						<td>24.01.2015 14:14</td>
						<td class="text-left">Backup-2015-01-24</td>
						<td>1.38 GB</td>
						<td>98</td>
						<td>437</td>
						<td>941</td>
						<td>821</td>
					</tr>
					<tr>
						<td><input class="checkbox" type="checkbox" name="user-selection" value="1"></td>
						<td>15.01.2015 22:00</td>
						<td class="text-left">Backup-2015-01-15</td>
						<td>1.09 GB</td>
						<td>86</td>
						<td>399</td>
						<td>870</td>
						<td>760</td>
					</tr>
					<tr>
						<td><input class="checkbox" type="checkbox" name="user-selection" value="1"></td>
						<td>01.01.2015 00:01</td>
						<td class="text-left">Backup-2015-01-01</td>
						<td>0.87 GB</td>
						<td>62</td>
						<td>301</td>
						<td>651</td>
						<td>599</td>
					</tr>
				</table>
				</form>
			</div>
			<div>
				<div class="button-group left">
					<button type="button" class="big-button blue" name="users_message"><i class="fa fa fa-camera fa-lg fa_fix"></i> Create new</button>
					<button type="button" class="big-button yellow" name="users_password_reset"><i class="fa fa-reply fa-lg"></i> Restore</button>
					<button type="button" class="big-button red" name="users_delete"><i class="fa fa-trash-o fa-lg fa_fix"></i> Delete</button>
				</div>
			</div>
		</div>
	</div>
</body>
</html>