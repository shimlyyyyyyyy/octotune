async function getPlaylists(){
    const playlists = await fetch('/src/php/getUserPlaylists.php');
    const playlistsJson = await playlists.json();
    console.log(playlistsJson);
    document.querySelector('.playlistlist').innerHTML = '';
    for (let index = 0; index < playlistsJson.length; ++index) {
        const playlist = playlistsJson[index];
        const playlistElement = document.createElement('div');
        playlistElement.className = 'playlist';
        playlistElement.innerHTML = `
            <div onclick="showPlayListSongs(${playlist.UPID})" class="playlistbutton">
                <div class="playlist_title">${playlist.playlistName}</div>
                <div class="playlist_delete">
                    <img src="/src/img/bin.png" alt="delete" onclick="deletePlaylist(${playlist.UPID})">
                </div>
            </div>
        `;
        document.querySelector('.playlistlist').appendChild(playlistElement);
    }
}

