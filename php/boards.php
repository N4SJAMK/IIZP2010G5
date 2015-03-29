<?php
include_once 'sections.php';

if (isset($_POST['boards_delete']))
{
	foreach ($_POST['board_selection'] as $id)
	{
		$database->removeBoard(array('_id' => new MongoId($id)));
	}
}

$page = isset($_GET['page']) ? $_GET['page'] : 1;
if (!is_numeric($page))
{
	$page = 1;
} 

$rows = isset($_GET['rows']) ? $_GET['rows'] : 10;
if (!is_numeric($rows))
{
	$rows = 10;
} 

?>

<!DOCTYPE html>
<html lang="en">
<?php head('Boards'); ?>
<body>
	<?php sidebar(); ?>
	<div id="top-bar">
			<h2 id="top-header">Boards</h2>
	</div>
	<div id="content">
		<div class="item">
			<div class="box gray">
				<h3 class="box-label">Search</h3>
				<div class="box-content">
					<form>
						<table class="form-table">
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td>Min</td>
								<td></td>
								<td>Max</td>
								<td></td>
								<td>Start</td>
								<td></td>
								<td>End</td>
							</tr>
							<tr>
								<td class="form-label">Owner</td>
								<td><input class="email" type="text" name="search_email"></td>
								<td class="form-label" name="search_tickets_min">Tickets</td>
								<td><select class="number">
									<option value="0" selected>0</option>
									<option value="1">1</option>
									<option value="2">2</option>
									<option value="3">3</option>
								</select></td>
								<td>-</td>
								<td><select class="number" name="search_tickets_max">
									<option value="55">55</option>
									<option value="56">56</option>
									<option value="57">57</option>
									<option value="58" selected>58</option>
								</select></td>
								<td class="form-label">Created</td>
								<td><select class="date" name="search_created_start">
									<option value="02.11.2014" selected>02.11.2014</option>
								</select></td>
								<td>-</td>
								<td><select class="date" name="search_created_end">
									<option value="27.01.2015" selected>27.01.2015</option>
								</select></td>
							</tr>
							<tr>
								<td></td>
								<td></td>
								<td class="form-label" name="search_guests_min">Guests</td>
								<td><select class="number">
									<option value="0" selected>0</option>
									<option value="1">1</option>
									<option value="2">2</option>
									<option value="3">3</option>
								</select></td>
								<td>-</td>
								<td><select class="number" name="search_guests_min">
									<option value="18">18</option>
									<option value="19">19</option>
									<option value="20">20</option>
									<option value="21" selected>21</option>
								</select></td>
								<td class="form-label" name="search_active_start">Last active</td>
								<td><select class="date">
									<option value="25.12.2014" selected>25.12.2014</option>
								</select></td>
								<td>-</td>
								<td><select class="date" name="search_active_end">
									<option value="27.01.2015" selected>27.01.2015</option>
								</select></td>
							</tr>
						</table>
						<div class="button-group right">
							<button type="submit" class="big-button yellow" name="search_clear"><i class="fa fa-times fa-lg fa_fix"></i> Clear</button>
							<button type="submit" class="big-button blue" name="search_submit"><i class="fa fa-search fa-lg fa_fix"></i> Search</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="item"><?php boardTable($page, $rows); ?></div>

			</div>
		</div>
	</div>
</body>
</html>