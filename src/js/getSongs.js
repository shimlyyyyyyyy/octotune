async function getSongs() {
    
    const songs = await fetch('/src/php/getRandomSongs.php');
    const songsJson = await songs.json();

    return songsJson;
}