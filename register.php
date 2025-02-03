<!DOCTYPE html>
<html lang="en">
<head>
  	<meta charset="UTF-8">
 	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login - Octotune</title>
	<link rel="stylesheet" href="./src/css/style.css">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="shortcut icon" href="./src/logo/logonotext.png" type="image/x-icon">
	<link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
</head>
<body class="login">
	<header class="login">
		<h1 class="h1header">Welcome to OctoTune!</h1>
	</header>
	<main>
		<div class="loginForm">
			<h2 class="formHeader">Register</h2>
			<form method="post" class="inputForm">
				<div class="inputBlockUser">
					<label for="username" >Username</label>
					<input type="text" name="username" id="username" required placeholder="username"><br>
				</div>
				<div class="inputBlockPass">
					<label for="password" >Password</label>
					<input type="password" name="password" id="password" required placeholder="password"><br>
				</div>
                <div class="inputBlockPassConf">
					<label for="password" >Confirm your Password</label>
					<input type="password" name="passwordConf" id="passwordConf" required placeholder="confirm your password"><br>
                    <?php
                        if (isset($_POST['register'])){
                            if(empty($_POST['username']) || empty($_POST['password']) || empty($_POST['passwordConf'])){
                                echo "<p>Please fill in all fields!</p>";
                            }
                            elseif ($_POST['password'] != $_POST['passwordConf']){ 
                                echo "<p>Passwords do not match!</p>";
                            }
                        }
                    ?>
				</div>
				<div class="inputBlockButton">
					<input type="submit" value="Register" class="primaryButton" name="register">
				</div>
				<div class="inputBlockRegister">
					<p>Already have an account? <a href="index.php" class="secondaryButton">Login</a></p>
				</div>
                <?php
					if(isset($_POST['register'])){
						$servername = "localhost";
						$username = "root";
						$password = "";
						$dbname = "octotune";
						$conn = new PDO("mysql:host=$servername; dbname=$dbname", $username, $password);
						
                        $username = $_POST['username'];
                        $password = hash('sha256', $_POST['password']);
                        $date = date("Y-m-d");
                        $uuid = uniqid();
                        
                        
                        $sql = "SELECT * FROM benutzer WHERE username = '$username'";
                        $result = $conn->query($sql);
                        $result->fetchColumn(0);
                        if ($result->rowCount() > 0){
                            $isDupe = true;
                        }
                        else{
                            $isDupe = false;
                        }
                        
                        if (!$isDupe){
                            $sql = "INSERT INTO benutzer (UUID, username, password, registeredOn) VALUES ('$uuid', '$username', '$password', '$date')";
                            $conn->exec($sql);
							setcookie("uuid", $uuid, 0, "/", "localhost", true);
							header("Location: index.php");
                        }
                        else{
                            echo "<h2 style='text-align: center;'>Username already taken!</h2>";
                        }	
					}
				?>
			</form>
		</div>
	</main>
	<footer class="loginfooter">
		<div>
			<p>2024 OctoTune</p>
		</div>
	</footer>
</body>
</html>