<?php
  	const servername = "localhost";
	const username = "root";
	const password = "";
	const dbname = "octotune";

  	function isUserLoggedIn(): bool{
		$conn = new PDO("mysql:host=".servername."; dbname=".dbname, username, password);	
		$uuid = $_COOKIE['uuid'];
		$sql = "SELECT * FROM benutzer where UUID = '$uuid'";
		$result = $conn->query($sql);
		if (!(isset($_COOKIE['uuid']) && ($result->rowCount() > 0))){
			unset($_COOKIE['uuid']);
			header("Location: index.php");
		}
		return true;
  	}

	function welcomeUser(): string{
		try{
			$conn = new PDO("mysql:host=".servername."; dbname=".dbname, username, password);	
			$uuid = $_COOKIE['uuid'];
			$sql = "SELECT * FROM benutzer WHERE UUID = '$uuid'";
			$result = $conn->query($sql);
			$row = $result->fetch();
			return "<h3 class='welcomeuser'><nobr>Welcome " . $row['username'] . "!</nobr></h3>";    
		}
		catch (Exception $e){
			return "<h3 class='welcomeuser'><nobr>Welcome User!</nobr></h3>";
		}
		
	}


  
  if (!isUserLoggedIn()) die("index.php");
?>

<!DOCTYPE html>
<html lang="en">
 	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>OctoTune</title>
		<link rel="stylesheet" href="/src/css/style.css">
		<script type="text/javascript" src="/src/js/listSongs.js"></script>
		<link rel="shortcut icon" href="/src/logo/logonotext.png" type="image/x-icon">
  	</head>
  	<body class="appbody">
      	<!-- topbar -->
    	<div class="topbar">
			<div class="title">
				<img src="/src/logo/logonotext.png" alt="logo" class="logoimg">
        <h1 class="appname">OctoTune</h1>
      	</div>
      	<div class="searchbar">
        <form method="POST" class="searchform">
          <div class="searchwrapper">
            <input type="text" class="searchtext" placeholder="Search for songs, artists, albums...">
            <input type="image" src="/src/img/search.png" class="searchbutton" name="search">
          </div>
        </form>
      	</div>
		  	<div class="user">
				<?php echo welcomeUser(); ?>
			</div>
        </div>
    </div>
    <!-- sidebar -->
    <div id="mydiv">
	</div>
    <!--main content-->
	<main>
		<div class="content">
			<div class="contentheader">
				<h2>Discover new music</h2>
			</div>
			<div class="songlist" id="songlist">
				<!-- songs will be displayed here -->
				 <script>listSongs();</script>
			</div>
		</div>
	</main>
    
    	
  	</body>
</html>
