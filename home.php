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
		<script type="text/javascript" src="./node_modules/howler/dist/howler.js"></script>
		<script>
			let sound = null; //Howler sound Objekt
			let currentSongIndex = 0; //Index des aktuellen Songs in dem songs-Array
			let songs = []; //Array mit allen Songs
			let isPlaying = false; //Boolean ob Song gerade abgespielt wird
			let currentSongUSID = null; //USID des aktuellen Songs, um den Song zu identifizieren
			let playlist = null; //Playlist, werwedendet um zu wissen, ob eine Playlist angezeigt wird

			
			function changeContentHeader(header){
				document.getElementById("contentheader").innerHTML = header; //Ändert den Header des Inhalts
			}
			

			async function getPlaylists(){ //Holt alle Playlists des Users
				const playlists = await fetch('/src/php/getUserPlaylists.php'); //Holt die Playlists des Users
				const playlistsJson = await playlists.json(); //Wandelt die Playlists in ein JSON-Objekt um
				document.querySelector('.playlistlist').innerHTML = ''; //Leert die Songliste, um nur die Playlist anzuzeigen
				for (let index = 0; index < playlistsJson.length; ++index) { //Fügt jede Playlist in die Playlistliste ein
					const playlist = playlistsJson[index];
					const playlistElement = document.createElement('div'); //Erstellt ein neues Div-Element
					playlistElement.className = 'playlist'; //gibt der div die Klasse playlist
					playlistElement.innerHTML = `
						<div onclick="showPlaylistSongs(${playlist.UPID})" class="playlistbutton">
							<div class="playlist_title">${playlist.playlistName}</div>
							<div class="playlist_delete">
								<img src="/src/img/bin.png" alt="delete" onclick="deletePlaylist(${playlist.UPID})">
							</div>
						</div>
					`;
					document.querySelector('.playlistlist').appendChild(playlistElement); //playlist wird in die playlistliste eingefügt
				}
			}

			async function search(searchtext) {
				const songs = await fetch(`src/php/search.php?searchText=${searchtext}`, {
					method: "GET"
				}); //Sucht nach Songs, die irgendwelche eigenschaften des Suchtextes enthalten
				const songsJson = await songs.json(); 
				changeContentHeader(`Search: ${searchtext}`); //Contentheader wird auf den Suchtext gesetzt
				return listSongs(songsJson); //zeigt die Songs an
			}
		
			//zeugs was regelmäßig ausgeführt wird
			document.addEventListener("DOMContentLoaded", function() { //wird konstant ausgeführt, sobald die Seite geladen ist
				
				//suche: songs werden gesucht, wenn etwas eingegeben wird
				const input = document.getElementById("searchtext");
				input.addEventListener("input", function(event) { //bei jeder eingabe wird die suche ausgeführt
					if (input.value != null && input.value != "") {
						search(input.value);
					}
					else {
						Songs(); //ist die suchleiste leer, wird wieder die Startseite (10 zufällige Lieder) angezeigt
					}
				});

				//lautstärke: lautstärke wird geändert, wenn der slider bewegt wird
				const volumeControl = document.getElementById("volume"); //slider für die lautstärke
				if (volumeControl) { 
				volumeControl.addEventListener("input", function() {
					setVolume(); //setzt die Lautstärke fest
					updateVolumeIcon(); //überprüft, ob das Icon geändert werden muss 
				});
			}
			});

			function setVolume(){
				Howler.volume(document.getElementById("volume").value / 100); //setzt lautstärke auf den wert des sliders
			}

			function updateVolumeIcon() {
				const volume = document.getElementById("volume").value; //holt den wert des sliders
				const volumeIcon = document.getElementById("volumeIcon"); //holt das img-Element des icons
				if (volume == 0) {
					volumeIcon.src = "./src/img/mute.png"; //wenn die lautstärke 0 ist, wird das mute-icon angezeigt
				} else {
					volumeIcon.src = "./src/img/speaker.png"; //ansonsten das speaker-icon
				}
			}

			async function showArtist(artistName){
				playlist = null; //Playlist wird auf null gesetzt, da keine Playlist angezeigt wird
				event.stopPropagation(); //dahinterligende buttons werden nicht gedrückt/aktiviert 
				list = await fetch(`/src/php/getArtistSongs.php?artistName=${artistName}`); //holt alle Songs des Künstlers
				songs = await list.json(); 
				changeContentHeader(`Artist: ${artistName}`); //ändert contentheader auf den Künstler
				listSongs(songs); //zeigt lieder an
			}

			async function listSongs(songList) {
				songs = songList; //setzt die globalen songs auf die übergebenen songs
				const songlist = document.getElementById("songlist"); //songliste ausgewählt
				songlist.innerHTML = ""; //leert die songliste
				for (let i = 0; i < songs.length; i++){ //fügt alle songs in die songliste ein
					const song = songs[i];
					const songdiv = document.createElement("div");
					songdiv.classList.add("song");
					songdiv.innerHTML = `
					<div class="songwrapper">
						<div class="songbutton" onclick="playSong('${song.songName}', '${song.artistName}', '${song.filePath}', '${song.coverPath}', ${i}, ${song.USID})">
							<div class="songcoverwrapper">
								<img src="${song.coverPath}" alt="song" class="songcover" loading="lazy">
							</div>
							<div class="songinfo">
								<h3 class="songtitle">${song.songName}</h3>
								<div class="artist" onclick="showArtist('${song.artistName}')">${song.artistName}</div>
							</div>
						</div>
						<div class="songbuttons">
							<button class="songbutton" onclick="playSong('${song.songName}', '${song.artistName}', '${song.filePath}', '${song.coverPath}', ${i}, ${song.USID})">
								<img src="./src/img/play.png" alt="play" class="songbuttonimg">
							</button>
							<button class="songbutton" onclick="showAddToPlaylistModal(${song.USID})">
								<img src="./src/img/plus.png" alt="add" class="songbuttonimg">
							</button>
							${playlist ? `<button class="songbutton" onclick="removeFromPlaylist(${song.USID}, ${playlist})">
								<img src="./src/img/bin.png" alt="delete" class="songbuttonimg">
							</button>` : ''}
							<button class="songbutton" onclick="playNext('${song.songName}', '${song.artistName}', '${song.filePath}', '${song.coverPath}', ${i}, ${song.USID})"> 
								<img src="./src/img/nextsong.png" alt="next" class="songbuttonimg">
							</button>
						</div>
					</div>
					`;
					songlist.appendChild(songdiv); //fügt den song in die songliste ein
				}
			}
			
			function addToHistory(USID){
				fetch(`/src/php/addToHistory.php?USID=${USID}`); //fügt den Song zum Wiedergabeverlauf hinzu
			}

			async function playSong(song, artist, path, cover, index, USID){
				//stop all Howler processeses
				Howler.stop(); //stoppt das aktuelle Lied
				Howler.unload(); //entlädt das aktuelle Lied
				sound = new Howl({ //erstellt ein neues Howler-Objekt
					src: [path], //setzt die Quelle des Liedes
					autoplay: true, //spielt das Lied automatisch ab
					loop: false, //wiederholt das Lied nicht
					onplay: function(){ //wird ausgeführt, wenn das Lied abgespielt wird
						requestAnimationFrame(updateProgress); //startet die Aktualisierung der Fortschrittsanzeige
						togglePlayPauseButton(true); //wenn ein lied abgespielt wird, wird das Spiel-Symbol mit dem Pause Symbol getauscht
						addToHistory(USID); //fügt den Song zum Wiedergabeverlauf hinzu
					},
					onend: function(){
						playNextSong(); //spielt das nächste Lied ab, wenn das aktuelle Lied zuende ist
					}
				});
				sound.play(); //spielt das Lied ab
				updateProgressbar(song, cover, artist); //aktualisiert die Spielleiste im Footer
				currentSongIndex = index; //setzt den aktuellen Songindex auf den Index des aktuellen Songs
				isPlaying = true; //setzt isPlaying auf true
			}

			function playNext(song, artist, path, cover, index, USID) {
				// Insert the song after the current song index
				songs.splice(currentSongIndex + 1, 0, {songName: song, artistName: artist, filePath: path, coverPath: cover, index: index, USID: USID}); //fügt den Song nach dem aktuellen Songindex ein	
			}

			function updateProgressbar(title, cover, artist){ //aktualisiert die Spielleiste im Footer
				const songtitle = document.getElementById("songtitle");
				songtitle.innerHTML = title; 
				const artistname = document.getElementById("artist");
				artistname.innerHTML = artist;
				artistname.onclick = function() {showArtist(artist)}; //wenn auf den Künstlernamen geklickt wird, werden alle Songs des Künstlers angezeigt
				const songcover = document.getElementById("songcoverplaybar");
				songcover.src = cover;
			}
			
			function updateProgress() {
				const progressbar = document.getElementById("progressbar"); //holt die Fortschrittsanzeige
				if (sound) {
					var seek = sound.seek() || 0; //holt den aktuellen Fortschritt des Liedes, gibt es keins, wird er auf 0 gesetzt
					var width = (((seek / sound.duration()) * 100) || 0) + "%"; //berechnet die Breite des Fortschrittsbalkens
					progressbar.style.backgroundImage = `linear-gradient(to right, rgba(107, 19, 103, 0.6) ${width},rgba(168, 63, 168, 0) ${width})`; //markiert den Fortschritt des Liedes
					requestAnimationFrame(updateProgress); //startet die Aktualisierung der Fortschrittsanzeige
				}
			}
			
			function playNextSong() {
				if (currentSongIndex + 1 < songs.length) { //wenn das array nach dem lied nicht aufhört
					const nextSong = songs[currentSongIndex + 1]; //holt das nächste Lied
					playSong(nextSong.songName, nextSong.artistName, nextSong.filePath, nextSong.coverPath, currentSongIndex + 1, nextSong.USID); //spielt das nächste
				}
			}
			
			function playLastSong() {
				if (currentSongIndex - 1 >= 0) { //wenn das array vor dem lied nicht aufhört
					const lastSong = songs[currentSongIndex - 1]; //holt das vorherige Lied
					playSong(lastSong.songName, lastSong.artistName, lastSong.filePath, lastSong.coverPath, currentSongIndex - 1, lastSong.USID); //spielt das vorherige Lied
				}
			}
			
			function togglePlayPause() { //wenn der Pause Button gedrückt wird, wird das Lied pausiert, wenn das Play-Symbol gedrückt wird, wird das Lied abgespielt
				if (isPlaying) {
					sound.pause();
					togglePlayPauseButton(false);
				} else {
					sound.play();
					togglePlayPauseButton(true);
				}
				isPlaying = !isPlaying;
			}
			
			function togglePlayPauseButton(isPlaying) { //tauscht das Play-Symbol mit dem Pause-Symbol
				const playPauseButton = document.getElementById("playPauseButton");
				if (isPlaying) {
					playPauseButton.src = "./src/img/pause.png";
				} else {
					playPauseButton.src = "./src/img/play.png";
				}
			}
			
			async function Songs(){ 
				playlist = null; //keine playlist!!
				list = await fetch('/src/php/getRandomSongs.php');//holt 10 zufällige Lieder
				songs = await list.json();
				changeContentHeader(`Discover new music`); //ändert den Contentheader auf "Discover new music"
				listSongs(songs); //offensichtlich solangsam
			}
			
			function addSongToPlaylist(USID, UPID){ //fügt den Song zur Playlist hinzu
				fetch(`/src/php/addToPlaylist.php?USID=${USID}&UPID=${UPID}` , {
					method: "POST"
				});
				closeModal(); //schließt das Modal zur Auswahl der Playlist
			}
			
			async function removeFromPlaylist(USID, playlist){
				await fetch(`/src/php/removeFromPlaylist.php?USID=${USID}&UPID=${playlist}`); //entfernt den Song aus der Playlist
				showPlayListSongs(playlist); //aktualisiert die Liedliste, da man ja grad n lied gelöscht hat; wär blöd, wär das lied noch da, oder?
			}

			async function showPlaylistSongs(UPID){
				playlist = UPID; //Playlist wird auf die übergebene Playlist gesetzt
				const list = await fetch("/src/php/getPlaylistSongs.php?UPID=" + UPID + ""); //Songs in der Playlist werden geholt
				const songsJson = await list.json();
				songs = songsJson; //globales array muss aktualisiert werden, sonst kabuum bei nächstem lied und vorherigem (glaub ich, ich kriegs nd besser hin; liebe grüße wenn sie das lesen. es ist 4:59 Uhr)
				try {// Contentheader wird auf den Namen der Playlist gesetzt, wenn in der Playlist lieder sind
					const playlistName = songsJson[0].playlistName; 
					changeContentHeader(`Playlist: ${playlistName}`);
					listSongs(songsJson);

				} catch (error) { //ist die Playlist leer, wird der Contentheader auf "Selected playlist is empty!" gesetzt und songliste = leer; ist ja nichts drin
					changeContentHeader(`Selected playlist is empty!`);
					listSongs([]);
				}
			}

			async function createPlaylist(){
				const playlistname = document.getElementById("playlistname").value; //Playlistname wird geholt
				const response = await fetch(`./src/php/createPlaylist.php?playlistname=${playlistname}`) //Playlist wird erstellt
				getPlaylists(); //Playlistliste wird aktualisiert	
			}

			async function deletePlaylist(UPID){
				event.stopPropagation(); //unterliegende Buttons werden nicht aktiviert
				await fetch(`/src/php/deletePlaylist.php?UPID=${UPID}`); //playlist == null -> löscht die Playlist
				getPlaylists(); //Playlistliste wird aktualisiert, wäre auch blöd, wenn die gelöschte Playlist noch da wäre
			}

			async function showListeningHistory(){
				playlist = null; //keine Playlist
				dingens = await fetch("/src/php/getListeningHistory.php"); //holt den Wiedergabeverlauf
				songs = await dingens.json();
				changeContentHeader(`Listening History`); //contentheader wird auf "Listening History" gesetzt, was auch sonst?
				listSongs(songs); //hab echt keine ahnung was diese zeile macht, aber sie ist wichtig
			}

			function closeModal() {
				document.getElementById("modal").style.display = "none"; //versteckt das Modal
			}

			function setTimestamp(event) { //setzt den Zeitstempel des Liedes
				if (sound) { //wenn ein Lied abgespielt wird
					const rect = progressbar.getBoundingClientRect(); //holt die Position der Fortschrittsanzeige
					const offsetX = event.clientX - rect.left; //berechnet die Position des Klicks
					const width = rect.width; //holt die Breite der Fortschrittsanzeige
					const percentage = offsetX / width; //berechnet den Prozentsatz des Fortschritts
					const newTime = percentage * sound.duration(); //berechnet die neue Zeit
					sound.seek(newTime);//setzt den neuen Zeitstempel
				} 
			}

			function showUserModal() {
				document.getElementById("userModal").style.display = "block"; //zeigt das User-Modal an
			}

			function closeUserModal() {
				document.getElementById("userModal").style.display = "none"; //versteckt das User-Modal
			}

			async function deleteUser() {
				const response = await fetch(`/src/php/deleteUser.php`); //löscht den User
				if (response.ok) { //wenn das Löschen erfolgreich war
					location.reload(); //wird die seite neugeladen
				} else {
					alert('ALAAAAAARMMM: Error deleting user'); //andernfalls "ALAAAAAARMMM"
				}
			}

			async function renameUser() {
				const newUsername = document.getElementById('newUsername').value; //holt den neuen Benutzernamen
				const response = await fetch(`/src/php/renameUser.php?newUsername=${newUsername}`); //ändert den Benutzernamen
				if (response.ok) {
					closeUserModal();
					location.reload(); //lädt die Seite neu
				} else {
					alert('Error renaming user'); //ALAAAAAARMMM
				}
			}
			
			function Logout(){
				document.cookie = "uuid=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;"; //löscht den Cookie
				window.location.href = "index.php"; //leitet auf die Login-Seite weiter
			}

			async function showAddToPlaylistModal(USID) {
				currentSongUSID = USID; //setzt die globale Variable auf die USID des aktuellen Songs
				const playlists = await fetch('/src/php/getUserPlaylists.php'); //holt die Playlists des Users
				const playlistsJson = await playlists.json(); 
				const modalContent = document.getElementById("modal-content"); //holt das Modal
				modalContent.innerHTML = `
					<span class="close" onclick="closeModal()">&times;</span>
					<h3>Select Playlist</h3>
				`; //setzt die Überschrift und das Schließsymbol des Modals
				for (let i = 0; i < playlistsJson.length; i++) {//zeigt alle Playlists an
					const playlist = playlistsJson[i];
					const playlistElement = document.createElement('div');
					playlistElement.className = 'playlist';
					playlistElement.innerHTML = `
						<div onclick="addSongToPlaylist(${USID}, ${playlist.UPID})" class="playlistbutton">
							<div class="playlist_title">${playlist.playlistName}</div>
						</div>
					`;
					modalContent.appendChild(playlistElement);
				}
				document.getElementById("modal").style.display = "block"; //zeigt modal an
			}
			//wieso hab ich das nur getan :(
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
			<div class="user" onclick="showUserModal()">
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
							<button onclick="showListeningHistory()">Listening History</button>
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
								<img src="./src/img/songcover.jpg" alt="song" id="songcoverplaybar" class="songcoverplaybar">
							<div class="songandartistplaybar">
								<h3 class="songtitleplaybar" id="songtitle">Song Title</h3>
								<h4 class="artistplaybar" id="artist">Artist</h4>
							</div>
						</div>
						<div class="playerprogress">
							<div class="progress" id="progress">
								<div class="progressbar" id="progressbar" onclick="setTimestamp(event)"></div>
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

		<!-- Modal for adding song to playlist -->
		<div id="modal" class="modal">
			<div class="modal-content" id="modal-content">
				<span class="close" onclick="closeModal()">&times;</span>
				<h3>Select Playlist</h3>
				<!-- Playlists will be dynamically added here -->
			</div>
		</div>

		<!-- User Modal -->
		<div id="userModal" class="modal">
			<div class="modal-content-user">
				<span class="close" onclick="closeUserModal()">&times;</span>
				<h3>User Settings</h3>
				<div class="renameUser">
					<label for="newUsername"><nobr>New Username:</nobr></label>
					<input type="text" id="newUsername" name="newUsername" placeholder="New Username">
					<button onclick="renameUser()">Rename</button>
				</div>
				<div class="deleteUser">
					<button onclick="Logout()">Logout</button>
				</div>
				<div class="deleteUser">
					<button onclick="deleteUser()">Delete User</button>
				</div>
			</div>
		</div>
	</body>
</html>