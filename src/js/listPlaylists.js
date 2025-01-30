async function getPlaylists(){
    const playlists = await fetch('/src/php/getUserPlaylists.php');
    const playlistsJson = await playlists.json();
    console.log(playlistsJson);

    for (let index = 0; index < playlistsJson.length; ++index) {
        const playlist = playlistsJson[index];
        const playlistElement = document.createElement('div');
        playlistElement.className = 'playlist';
        playlistElement.innerHTML = `
            <div onclick="showPlayListSongs(${playlist.UPID})" class="playlistbutton">
                <div class="playlist_title">${playlist.playlistName}</div>
            </div>
        `;
        document.querySelector('.playlists').appendChild(playlistElement);
    }
}

