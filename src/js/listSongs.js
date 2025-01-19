async function listSongs(params) {
    const songs = await fetch('/src/php/getRandomSongs.php');
    const songsJson = await songs.json();

    for (let index = 0; index < songsJson.length; ++index) {
        const song = songsJson[index];
        const title = 0;
        const songElement = document.createElement('div');
        songElement.className = 'song';
        songElement.innerHTML = `
            <div class="song__title">${song.songName}</div>
            <div class="song__artist">
                <audio controls>
                    <source src="${song.filePath}" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
            </div>
            <div class="song__album">${song.album}</div>
            <div class="song__year">${song.year}</div>
        `;
        document.querySelector('.songlist').appendChild(songElement);
    }
    console.log(songsJson);
}