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
			let currentSongIndex = 0;
			let songs = [];
			let isPlaying = false;

			async function search(searchtext) {
				const songs = await fetch(`src/php/search.php?searchText=${searchtext}`, {
					method: "GET"
				});
				const songsJson = await songs.json();
				console.log(songsJson);
				return listSongs(songsJson);
			}
		

			//zeugs was regelmäßig ausgeführt wird
			document.addEventListener("DOMContentLoaded", function() {
				
				//suche: songs werden gesucht, wenn etwas eingegeben wird
				
				const input = document.getElementById("searchtext");
				input.addEventListener("input", function(event) {
					if (input.value != null && input.value != "") {
						search(input.value);
						document.getElementById("contentheader").innerHTML = `Search: ${input.value}`;
					}
					else {
						Songs();
					}
				});


				const volumeControl = document.getElementById("volume");
				if (volumeControl) {
				volumeControl.addEventListener("input", function() {
					setVolume();
					updateVolumeIcon();
				});
			}
			});

			
			function setVolume(){
				Howler.volume(document.getElementById("volume").value / 100);
			}

			function updateVolumeIcon() {
				const volume = document.getElementById("volume").value;
				const volumeIcon = document.getElementById("volumeIcon");
				if (volume == 0) {
					volumeIcon.src = "./src/img/mute.png";
				} else {
					volumeIcon.src = "./src/img/speaker.png";
				}
			}

			function showArtist(){
				event.stopPropagation();
				console.log("Artist");
			}

			async function listSongs(songs) {
				song = songs
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
						<div class="songbutton" onclick="playSong('${song.songName}', '${song.artistName}', '${song.filePath}', '${song.coverPath}', ${i})">
							<div class="songcoverwrapper">
								<img src="${song.coverPath}" alt="song" class="songcover">
							</div>
							<div class="songinfo">
								<h3 class="songtitle">${song.songName}</h3>
								<div class="artist" onclick="something()">${song.artistName}</div>
							</div>
						</div>
						<div class="songbuttons">
							<button class="songbutton" onclick="playSong('${song.songName}', '${song.artistName}', '${song.filePath}', '${song.coverPath}', ${i})">
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

			async function playSong(song, artist, path, cover, index){
				//stop all Howler processeses
				Howler.stop();
				sound = new Howl({
					src: [path],
					autoplay: true,
					loop: false,
					onplay: function(){
						requestAnimationFrame(updateProgress);
						togglePlayPauseButton(true);
					},
					onend: function(){
						playNextSong();
					}
				});
				currentSongIndex = index;
				const songtitle = document.getElementById("songtitle");
				songtitle.innerHTML = "<nobr>"+song+"</nobr>";
				const artistname = document.getElementById("artist");
				artistname.innerHTML = "<nobr>"+artist+"</nobr>";
				const songcover = document.getElementById("songcoverplaybar");
				songcover.src = cover;
				console.log(cover);
				sound.play();
				isPlaying = true;
				console.log(path);
			}
			function updateProgress() {
				const progressbar = document.getElementById("progressbar");
				if (sound) {
					var seek = sound.seek() || 0;
					var width = (((seek / sound.duration()) * 100) || 0) + "%";
					progressbar.style.backgroundImage = `linear-gradient(to right, rgba(107, 19, 103, 0.6) ${width},rgba(168, 63, 168) ${width})`;
					requestAnimationFrame(updateProgress);
				}
			}
			function playNextSong() {
				if (currentSongIndex + 1 < songs.length) {
					const nextSong = songs[currentSongIndex + 1];
					playSong(nextSong.songName, nextSong.artistName, nextSong.filePath, nextSong.coverPath, currentSongIndex + 1);
				}
			}
			function playLastSong() {
				if (currentSongIndex - 1 >= 0) {
					const lastSong = songs[currentSongIndex - 1];
					playSong(lastSong.songName, lastSong.artistName, lastSong.filePath, lastSong.coverPath, currentSongIndex - 1);
				}
			}
			function togglePlayPause() {
				if (isPlaying) {
					sound.pause();
					togglePlayPauseButton(false);
				} else {
					sound.play();
					togglePlayPauseButton(true);
				}
				isPlaying = !isPlaying;
			}
			function togglePlayPauseButton(isPlaying) {
				const playPauseButton = document.getElementById("playPauseButton");
				if (isPlaying) {
					playPauseButton.src = "./src/img/pause.png";
				} else {
					playPauseButton.src = "./src/img/play.png";
				}
			}
			async function Songs(){
				songs = await getSongs();
				console.log(songs);
				document.getElementById("contentheader").innerHTML = `Discover new music`;
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
						<div class="songbutton" onclick="playSong('${song.songName}', '${song.artistName}', '${song.filePath}', '${song.coverPath}', ${i})">
							<div class="songcoverwrapper">
								<img src="${song.coverPath}" alt="song" class="songcover">
							</div>
							<div class="songinfo">
								<h3 class="songtitle">${song.songName}</h3>
								<div class="artist" onclick="something()">${song.artistName}</div>
							</div>
						</div>
						<div class="songbuttons">
							<button class="songbutton" onclick="playSong('${song.songName}', '${song.artistName}', '${song.filePath}', '${song.coverPath}', ${i})">
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
						<div class="songbutton" onclick="playSong('${song.songName}', '${song.artistName}', '${song.filePath}', '${song.coverPath}', ${i})">
							<div class="songcoverwrapper">
								<img src="${song.coverPath}" alt="song" class="songcover">
							</div>
							<div class="songinfo">
								<h3 class="songtitle">${song.songName}</h3>
								<div class="artist" onclick="something()">${song.artistName}</div>
							</div>
						</div>
						<div class="songbuttons">
							<button class="songbutton" onclick="playSong('${song.songName}', '${song.artistName}', '${song.filePath}', '${song.coverPath}', ${i})">
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
			async function createPlaylist(){
				const playlistname = document.getElementById("playlistname").value;
				const response = await fetch("/src/php/createPlaylist.php?playlistname=" + playlistname + "", {
					method: "POST"
				});
				getPlaylists();
			}


			function deletePlaylist(UPID){
				event.stopPropagation();
				fetch("/src/php/deletePlaylist.php?UPID=" + UPID + "", {
					method: "POST"
				});
				getPlaylists();
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
				<div class="searchwrapper">
					<input type="text" class="searchtext" id="searchtext" placeholder="Search for songs, artists, albums...">
					<input type="image" src="./src/img/search.png" class="searchbutton" name="search" onclick="search(searchtext)">
				</div>
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
							 <div class="createplaylistwrapper">
								<nobr>
									<div class="createplaylist">
										<input type="text" id="playlistname" placeholder="Playlist name">
										<button onclick="createPlaylist()">Create</button>
									</div>
								</nobr>
							 </div>
							 <div class="playlistlist" id="playlistlist">
								 <script>getPlaylists();</script>
							 </div>
							
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
							<button class="playerbutton" id="prevbutton" onclick="playLastSong()">
								<img src="./src/img/back.png" alt="prev" class="playerbuttonimg">
							</button>
							<button class="playerbutton" id="playbutton">
								<img src="./src/img/play.png" alt="play" class="playerbuttonimg" id="playPauseButton" onclick="togglePlayPause()">
							</button>
							<button class="playerbutton" id="nextbutton" onclick="playNextSong()">
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
								<div class="progressbar" id="progressbar" onclick="setTimestamp()"></div>
							</div>
						</div>
						<div class="volumecontrol">
							<img src="./src/img/speaker.png" id="volumeIcon" alt="">
							<input type="range" class="volume" id="volume" min="0" max="100" value="100" >
						</div>
					</div>
				</div>
			</div>
		</footer>
  	</body>
</html>
