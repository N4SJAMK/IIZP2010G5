<?php
session_start();

require 'sections.php';

$errors = '';

if (isset($_POST['login'])) {
	$username = $_POST['username'];
	$password = $_POST['password'];

	if (empty($username) || empty($password) || $username != ADMIN_USER || $password != ADMIN_PASS):
		$errors .= '<p class="error">Invalid username or password!</p>';
	else:
		$_SESSION['id'] = hash("sha256", $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);

		if (isset($_POST['remember'])) {
			setcookie('id', $_SESSION['id'], time() + (86400 * 30), '/');
		}

		header('Location: index.php');
	endif;
}
?>
<!DOCTYPE html>
<html>
<?php head('');?>
<body id="login-page">
	<div class="container">
		<div class="row">
			<div class="col-xs-10 col-sm-8 col-md-6 col-lg-4 col-xs-push-1 col-sm-push-2 col-md-push-3 col-lg-push-4">
				<div id="login-wrapper">
					<div class="logo">
						<h1 class="title"><img src="img/teamboard_logo.svg">Contr<span class="turquoise">A</span></span></h1>
						<h2 class="subtitle">Contriboard <span class="turquoise">Admin</span></h2>
					</div>
					<form method="post">
						<?php echo $errors;?>
						<input class="top" type="text" name="username" placeholder="Username">
						<input class="bottom" type="password" name="password" placeholder="Password">
						<p><input type="checkbox" id="remember-me" name="remember" value="1"><label for="remember-me">Remember me</label></p>
						<button type="submit" name="login" class="blue">Sign in</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</body>
</html>