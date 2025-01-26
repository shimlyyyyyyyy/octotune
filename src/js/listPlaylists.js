async function getPlaylists(){
    const playlists = await fetch('/src/php/getUserPlaylists.php');
    const playlistsJson = await playlists.json();
    console.log(playlistsJson);

    for (let index = 0; index < playlistsJson.length; ++index) {
        const playlist = playlistsJson[index];
        const playlistElement = document.createElement('div');
        playlistElement.className = 'playlist';
        playlistElement.innerHTML = `
            <div class="playlist__title">${playlist.playlistName}</div>
            <div class="playlist__songs">
                <button class="playlist__songs__button" onclick="listSongs()">Show Songs</button>
            </div>
        `;
        document.querySelector('.playlists').appendChild(playlistElement);
    }
}

