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
		<link rel="stylesheet" href="./src/css/style.css">
		<link rel="shortcut icon" href="./src/logo/logonotext.png" type="image/x-icon">
		<script type="text/javascript" src="./src/js/getSongs.js"></script>
		<script type="text/javascript" src="./src/js/listPlaylists.js"></script>
		<script type="text/javascript" src="./node_modules/howler/dist/howler.js"></script>
		<script>
			function setVolume(){
				Howler.volume(document.getElementById("volume").value / 100);
			}
		</script>
  	</head>
  	<body class="appbody">
      	<!-- topbar -->
    	<div class="topbar">
			<div class="title">
				<img src="./src/logo/logonotext.png" alt="logo" class="logoimg">
        	<h1 class="appname">OctoTune</h1>
      		</div>
			<div class="searchbar">
				<form method="POST" class="searchform" id="searchform">
					<div class="searchwrapper">
						<input type="text" class="searchtext" placeholder="Search for songs, artists, albums...">
						<input type="image" src="./src/img/search.png" class="searchbutton" name="search">
					</div>
				</form>
			</div>
			<div class="user">
				<?php echo welcomeUser(); ?>
			</div>
		</div>
		
		
		<!--main content-->
		<main>
			<div class="sidebarSonglistWrapper">
				<!-- sidebar -->
				<div class="sidebar">
					<div class="favorites">
						
					</div >
					<div class="listeningHistory">
					</div>
					<div class="playlists" id="playlists">
						<!-- playlists will be displayed here -->
						<script>getPlaylists();</script>
					</div>
				</div>
				<div class="spacebetweensideandlist">
				</div>
				<!--Songlist-->
				<div class="songlistwrapper">
					<div class="content">
						<div class="contentheader">
							<h2>Discover new music</h2>
						</div>
						<div class="songlist" id="songlist">
							<!-- songs will be displayed here -->
							<script>
								async function Songs(){
									

									const songs = await getSongs();
									console.log(songs);
									const songlist = document.getElementById("songlist");
									for (let i = 0; i < songs.length; i++){
										const song = songs[i];
										const songdiv = document.createElement("div");
										songdiv.classList.add("song");
										songdiv.innerHTML = `
											<div class="songcoverwrapper">
												<img src="${song.coverPath}" alt="song" class="songcover">
											</div>
											<div class="songinfo">
												<h3 class="songtitle">${song.songName}</h3>
												<h4 class="artist">${song.artistName}</h4>
											</div>
											<div class="songbuttons">
												<button class="songbutton" onclick="playSong('${song.title}', '${song.artist}', '${song.path}')">
													<img src="./src/img/play.png" alt="play" class="songbuttonimg">
												</button>
												<button class="songbutton" onclick="addSongToPlaylist('${song.title}', '${song.artist}', '${song.path}')">
													<img src="./src/img/plus.png" alt="add" class="songbuttonimg">
												</button>
											</div>
										`;
										songlist.appendChild(songdiv);
									}
								}
								Songs();
								
								
								function sound(){
									var sound = new Howl({
										src: ['/songs/Shmunk.mp3'],
										autoplay: false,
										loop: false,
									});
									if (sound.playing()) {
										sound.stop();
									} else{
										sound.play();
									}

								}
								
							</script>
							<button onclick="sound()" class=""></button>
						</div>
					</div>
				</div>
			</div>
		</main>
		<!--footer-->
		<footer class="playerfooter">
			<div class="player">
				<div class="playercontent">
					<div class="playercontrols">
						<div class="playerbuttons">
							<button class="playerbutton" id="prevbutton">
								<img src="./src/img/back.png" alt="prev" class="playerbuttonimg">
							</button>
							<button class="playerbutton" id="playbutton">
								<img src="./src/img/play.png" alt="play" class="playerbuttonimg">
							</button>
							<button class="playerbutton" id="nextbutton">
								<img src="./src/img/next.png" alt="next" class="playerbuttonimg">
							</button>
						</div>
						<div class="playerprogress">
							<div class="progress">
								<div class="progressbar" id="progressbar"></div>
							</div>
						</div>
					</div>
					<div class="playerinfo">
						<div class="songinfo">
							<h3 class="songtitle" id="songtitle">Song Title</h3>
							<h4 class="artist" id="artist">Artist</h4>
						</div>
						<div class="volumecontrol">
							<input type="range" class="volume" id="volume" min="0" max="100" value="100" onclick="setVolume()">
							
						</div>
					</div>
				</div>
			</div>
		</footer>
  	</body>
</html>
