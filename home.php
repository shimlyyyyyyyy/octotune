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
		$conn = new PDO("mysql:host=".servername."; dbname=".dbname, username, password);	
		$uuid = $_COOKIE['uuid'];
		$sql = "SELECT * FROM benutzer WHERE UUID = '$uuid'";
		$result = $conn->query($sql);
		$row = $result->fetch();
		return "<h3 class='welcomeuser'><nobr>Welcome " . $row['username'] . "!</nobr></h3>";    
	}
  
  if (isUserLoggedIn()){
	echo '
<!DOCTYPE html>
<html lang="en">
 	<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OctoTune</title>
    <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <link rel="stylesheet" href="/src/css/style.css">
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
				'.welcomeUser().'
			</div>
        </div>
    </div>
    <!-- sidebar -->
    <div id="mydiv">
      
    </div>
    	<script type="text/babel">
			function Hello() {
				return <h1>Hello World!</h1>;
			}
			const container = document.getElementById("mydiv");
      		const root = ReactDOM.createRoot(container);
      		root.render(<Hello />)
    	</script>
  	</body>
</html>';
}
?>


