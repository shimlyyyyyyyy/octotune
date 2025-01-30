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
			let sound = null;
			function setVolume(){
				Howler.volume(document.getElementById("volume").value / 100);
			}
			function something(){
				event.stopPropagation()
				console.log("something");
			}
			function playSong(song, artist, path, cover){
				sound = new Howl({
					src: [path],
					autoplay: true,
					loop: false,
					onplay: function(){
						requestAnimationFrame(updateProgress);
					}
				});
				const songtitle = document.getElementById("songtitle");
				songtitle.innerHTML = "<nobr>"+song+"</nobr>";
				const artistname = document.getElementById("artist");
				artistname.innerHTML = "<nobr>"+artist+"</nobr>";
				const songcover = document.getElementById("songcoverplaybar");
				songcover.src = cover;
				console.log(cover);
				sound.play();
				console.log(path);
				function updateProgress() {
					const progressbar = document.getElementById("progressbar");
					var seek = sound.seek() || 0;
					progressbar.style.width = (((seek / sound.duration()) * 100) || 0) + "%";
					requestAnimationFrame(updateProgress);
            	}

			}
			async function Songs(){
				const songs = await getSongs();
				console.log(songs);
				const songlist = document.getElementById("songlist");
				songlist.innerHTML = "";
				for (let i = 0; i < songs.length; i++){
					const song = songs[i];
					const songdiv = document.createElement("div");
					songdiv.classList.add("song");
					console.log(song.filePath);
					const filePath = song.filePath;
					songdiv.innerHTML = `
					<div class="songwrapper">
						<div class="songbutton" onclick="playSong('${song.songName}', '${song.artistName}', '${song.filePath}', '${song.coverPath}')">
							<div class="songcoverwrapper">
								<img src="${song.coverPath}" alt="song" class="songcover">
							</div>
							<div class="songinfo">
								<h3 class="songtitle">${song.songName}</h3>
								<div class="artist" onclick="something()">${song.artistName}</div>
							</div>
						</div>
						<div class="songbuttons">
							<button class="songbutton" onclick="playSong('${song.songName}', '${song.artistName}', '${song.filePath}', '${song.coverPath}')">
								<img src="./src/img/play.png" alt="play" class="songbuttonimg">
							</button>
							<button class="songbutton" onclick="addSongToPlaylist('${song.USID}', '1')">
								<img src="./src/img/plus.png" alt="add" class="songbuttonimg">
							</button>
						</div>
					</div>
					`;
					songlist.appendChild(songdiv);
				}
			}
			function addSongToPlaylist(USID, UPID){
				fetch("/src/php/addToPlaylist.php?USID=" + USID + "&UPID=" + UPID+ "", {
					method: "POST"
				});
			}
			async function showPlayListSongs(UPID){
				const songs = await fetch("/src/php/getPlaylistSongs.php?UPID=" + UPID + "");
				const songsJson = await songs.json();
				console.log(songsJson);
				const songlist = document.getElementById("songlist");
				songlist.innerHTML = "";
				const contentheader = document.getElementById("contentheader");

				contentheader.innerHTML = `${songsJson[0]["playlistName"]}`;

				for (let i = 0; i < songsJson.length; i++){
					const song = songsJson[i];
					const songdiv = document.createElement("div");
					songdiv.classList.add("song");
					console.log(song.filePath);
					const filePath = song.filePath;
					songdiv.innerHTML = `
					<div class="songwrapper">
						<div class="songbutton" onclick="playSong('${song.songName}', '${song.artistName}', '${song.filePath}', '${song.coverPath}')">
							<div class="songcoverwrapper">
								<img src="${song.coverPath}" alt="song" class="songcover">
							</div>
							<div class="songinfo">
								<h3 class="songtitle">${song.songName}</h3>
								<div class="artist" onclick="something()">${song.artistName}</div>
							</div>
						</div>
						<div class="songbuttons">
							<button class="songbutton" onclick="playSong('${song.songName}', '${song.artistName}', '${song.filePath}', '${song.coverPath}')">
								<img src="./src/img/play.png" alt="play" class="songbuttonimg">
							</button>
							<button class="songbutton" onclick="addSongToPlaylist('${song.USID}', '1')">
								<img src="./src/img/plus.png" alt="add" class="songbuttonimg">
							</button>
						</div>
					</div>
					`;
					songlist.appendChild(songdiv);
				}

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
					<div class="sidebarwrapper">
						<div class="favorites">
							<button onclick="Songs()">Home</button>
						</div>
						<div class="favorites">
							<button onclick="showFavorites()">Favorites</button>
						</div >
						<div class="listeningHistory">
						</div>
						<div class="playlists" id="playlists">
							<!-- playlists will be displayed here -->
							 <h3>Playlists</h3>
							<script>getPlaylists();</script>
						</div>
					</div>
				</div>
				<div class="spacebetweensideandlist">
				</div>
				<!--Songlist-->
				<div class="songlistwrapper">
					<div class="content">
						<div class="contentheader">
							<h2 id="contentheader">Discover new music</h2>
						</div>
						<div class="songlist" id="songlist">
							<!-- songs will be displayed here -->
							<script>
								Songs();	
							</script>
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
					</div>
					<div class="playerinfo">
						<div class="songinfoplaybar">
							<div class="songcoverwrapperplaybar">
								<img src="./src/img/songcover.png" alt="song" id="songcoverplaybar" class="songcoverplaybar">
							<div class="songandartistplaybar">
								<h3 class="songtitleplaybar" id="songtitle">Song Title</h3>
								<h4 class="artistplaybar" id="artist">Artist</h4>
							</div>
						</div>
						<div class="playerprogress">
							<div class="progress">
								<div class="progressbar" id="progressbar"></div>
							</div>
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
