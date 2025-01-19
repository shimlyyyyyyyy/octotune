<?php
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "octotune";
	$conn = new PDO("mysql:host=$servername; dbname=$dbname", $username, $password);
	if (isset($_COOKIE['uuid'])){
		$uuid = $_COOKIE['uuid'];
	}
	else{
		$uuid = "";
	}
	
	$sql = "SELECT * FROM benutzer WHERE UUID = '$uuid'";
	$result = $conn->query($sql);
	
	if(isset($_COOKIE['uuid']) && ($result->rowCount() > 0)){
		header("Location: home.php");
	}
	else{
		unset($_COOKIE['uuid']);
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  	<meta charset="UTF-8">
 	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login - Octotune</title>
	<link rel="stylesheet" href="/src/css/style.css">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="shortcut icon" href="/src/logo/logonotext.png" type="image/x-icon">
	<link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
</head>
<body class="login">
	<header class="login">
		<h1 class="h1header">Welcome back to OctoTune!</h1>
	</header>
	<main>
		<div class="loginForm">
			<h2 class="formHeader">Login</h2><br>
			<form method="POST" class="inputForm">
				<div class="inputBlockUser">
					<label for="username">Username</label>
					<input type="text" name="username" id="username" required placeholder="username"><br>
				</div>
				<div class="inputBlockPass">
					<label for="password" >Password</label>
					<input type="password" name="password" id="password" required placeholder="password"><br>
				</div>
				<div class="inputBlockButton">
					<input type="submit" value="Login" name="login" class="primaryButton" >
				</div>
				<div class="inputBlockRegister">
					<p>Don't have an account? <a href="register.php" class="secondaryButton">Register</a></p>
				</div>
				<?php
					if(isset($_POST['login'])){
						$servername = "localhost";
						$username = "root";
						$password = "";
						$dbname = "octotune";
						$conn = new PDO("mysql:host=$servername; dbname=$dbname", $username, $password);
						
						$username = $_POST['username'];
						$password = hash('sha256', $_POST['password']);

						$sql = "SELECT * FROM benutzer WHERE username = '$username' AND password = '$password'";
						$result = $conn->query($sql);
						if ($result->rowCount() == 0){
							echo "<p>Invalid username or password!</p>";
						}
						else{
							$uuid = $result->fetchColumn(0);
							setcookie("uuid", $uuid, 0, "/", "localhost", true);
							header("Location: home.php");
						}

					}
				?>
			</form>
		</div>
	</main>
	<footer>
		<div>
			<p>2024 OctoTune</p>
		</div>
	</footer>
</body>
</html>