import os
import tinytag
import mysql.connector
import pathlib

hostname="localhost"
database="octotune"
username="root"
password=""


def convert_path_to_forward_slashes(path):
    return path.replace("\\", "/")

def fill_album_table():
    db = mysql.connector.connect(
        host=hostname,
        user=username,
        password=password,
        database=database
    )
    mycursor = db.cursor()

    try:
        # Hole alle MP3-Dateien
        files = get_files()
        albums = set()

        for file in files:
            tag = tinytag.TinyTag.get(file)
            if tag.album:
                albums.add((tag.album, tag.year))

        # Füge Alben in die Datenbank ein
        sql = "INSERT INTO album (albumName, releaseDate) VALUES (%s, %s)"
        mycursor.executemany(sql, list(albums))
        db.commit()
        print(f"{mycursor.rowcount} Alben eingefügt.")

    except mysql.connector.Error as err:
        print(f"Fehler beim Einfügen der Alben: {err}")
    finally:
        mycursor.close()
        db.close()

def fill_song_album_relation():
    db = mysql.connector.connect(
        host=hostname,
        user=username,
        password=password,
        database=database
    )
    mycursor = db.cursor()

    try:
        # Hole alle Lieder und Alben
        mycursor.execute("SELECT USID, songName, filePath FROM lied")
        songs = mycursor.fetchall()
        mycursor.execute("SELECT UAlbID, albumName FROM album")
        albums = {album[1]: album[0] for album in mycursor.fetchall()}

        # Gruppiere Lieder nach Album
        album_songs = {}
        for song_id, song_name, file_path in songs:
            file_path = convert_path_to_forward_slashes(file_path)
            tag = tinytag.TinyTag.get(file_path)
            if tag.album and tag.album in albums:
                if tag.album not in album_songs:
                    album_songs[tag.album] = []
                album_songs[tag.album].append((song_id, tag.track))

        relations = []
        for album, songs in album_songs.items():
            # Sortiere Lieder nach Tracknummer
            sorted_songs = sorted(songs, key=lambda x: int(x[1]) if x[1] and x[1].isdigit() else float('inf'))
            for order, (song_id, _) in enumerate(sorted_songs, start=1):
                relations.append((song_id, albums[album], order))

        # Füge Beziehungen in die Datenbank ein
        sql = "INSERT INTO enthalten (USID, UAlbID, `order`) VALUES (%s, %s, %s)"
        mycursor.executemany(sql, relations)
        db.commit()
        print(f"{mycursor.rowcount} Lied-Album-Beziehungen eingefügt.")

    except mysql.connector.Error as err:
        print(f"Fehler beim Einfügen der Lied-Album-Beziehungen: {err}")
    finally:
        mycursor.close()
        db.close()

def fill_artist_album_relation():
    db = mysql.connector.connect(
        host=hostname,
        user=username,
        password=password,
        database=database
    )
    mycursor = db.cursor()

    try:
        # Hole alle Künstler und Alben
        mycursor.execute("SELECT UArtID, artistName FROM kuenstler")
        artists = {artist[1]: artist[0] for artist in mycursor.fetchall()}
        mycursor.execute("SELECT UAlbID, albumName FROM album")
        albums = mycursor.fetchall()

        relations = set()
        for album_id, album_name in albums:
            files = [f for f in get_files() if tinytag.TinyTag.get(f).album == album_name]
            for file in files:
                file = convert_path_to_forward_slashes(file)
                tag = tinytag.TinyTag.get(file)
                if tag.artist and tag.artist in artists:
                    relations.add((artists[tag.artist], album_id))

        # Füge Beziehungen in die Datenbank ein
        sql = "INSERT INTO veroeffentlichen (UArtID, UAlbID) VALUES (%s, %s)"
        mycursor.executemany(sql, list(relations))
        db.commit()
        print(f"{mycursor.rowcount} Künstler-Album-Beziehungen eingefügt.")

    except mysql.connector.Error as err:
        print(f"Fehler beim Einfügen der Künstler-Album-Beziehungen: {err}")
    finally:
        mycursor.close()
        db.close()

def get_files():
    paths = []
    for file in os.listdir("songs"):
        if file.endswith(".mp3"):
            paths.append(convert_path_to_forward_slashes(os.path.join("songs", file)))
    return paths

def get_artist(files = get_files()):
    artists = []
    artist_set = set()
        
    for each in files:
        tag = tinytag.TinyTag.get(each)
        if tag.artist and tag.artist not in artist_set:
            artists.append((tag.artist, 'placeholder'))
            artist_set.add(tag.artist)
    return artists

def push_artists(artists = get_artist()):
    db = mysql.connector.connect(
        host=hostname,
        user=username,
        password=password,
        database=database
    )
    sql = "INSERT INTO kuenstler (artistName, biografie) VALUES (%s, %s)"
    mycursor = db.cursor()
    
    try:
        mycursor.executemany(sql, artists)
        db.commit()
        print(mycursor.rowcount, "artists inserted.")
    except mysql.connector.Error as err:
        print("Something went wrong: {}".format(err))
    finally:
        mycursor.close()
        db.close() 

def get_metadata(files = get_files()): 
    val = []
    for each in files:
        tag = tinytag.TinyTag.get(each)
        val.append((tag.title, tag.year, each, each.replace(pathlib.Path(each).suffix, ".jpeg"), tag.genre, tag.duration, pathlib.Path(each).suffix))
    return val
    
def push_songs(val = get_metadata()):
    db = mysql.connector.connect(
        host=hostname,
        user=username,
        password=password,
        database=database
    )
    sql = "INSERT INTO lied (songName, releaseDate, filePath, coverPath, genre, length, filetype) VALUES (%s, %s, %s, %s, %s, %s, %s)"
    mycursor = db.cursor()
    try:
        mycursor.executemany(sql, val)
        db.commit()
    except mysql.connector.Error as err:
        print("Something went wrong: {}".format(err))
    finally:
        mycursor.close()
        db.close() 
    print(mycursor.rowcount, "songs inserted.")

def getRelations():
    db = mysql.connector.connect(
        host=hostname,
        user=username,
        password=password,
        database=database
    )
    sql_artists = "SELECT * FROM kuenstler"
    sql_songs = "SELECT * FROM lied"
    
    mycursor = db.cursor()
    
    try:
        mycursor.execute(sql_artists)
        artists = mycursor.fetchall()
        
        mycursor.execute(sql_songs)
        songs = mycursor.fetchall()
        
        relation_sql = "INSERT INTO komponieren (UArtID, USID) VALUES (%s, %s)"
        
        relations = []
        
        for artist in artists:
            artist_id = artist[0]
            for song in songs:
                song_id = song[0]
                tag = tinytag.TinyTag.get(convert_path_to_forward_slashes(song[3]))
                if tag.artist == artist[1]:
                    relations.append((artist_id, song_id))
        
        mycursor.executemany(relation_sql, relations)
        db.commit()
        
        print(mycursor.rowcount, "relations inserted.")
    
    except mysql.connector.Error as err:
        print("Something went wrong: {}".format(err))
    
    finally:
        mycursor.close()
        db.close()

def clear_all_tables():
    db = mysql.connector.connect(
        host=hostname,
        user=username,
        password=password,
        database=database
    )
    
    mycursor = db.cursor()
    
    try:
        mycursor.execute("SET FOREIGN_KEY_CHECKS = 0")
        mycursor.execute("SHOW TABLES")
        tables = mycursor.fetchall()
        
        for table in tables:
            table_name = table[0]
            if table_name == "benutzer" or table_name =="beinhalten" or table_name == "playlist" or table_name == "erstellen" or table_name == "speichern" or table_name == "wiedergabeverlauf":
                continue
            sql = f"TRUNCATE TABLE {table_name}"
            mycursor.execute(sql)
            print(f"Tabelle {table_name} wurde geleert.")
        
        db.commit()
    
    except mysql.connector.Error as err:
        print(f"Fehler beim Leeren der Tabellen: {err}")
    
    finally:
        mycursor.execute("SET FOREIGN_KEY_CHECKS = 1")
        mycursor.close()
        db.close()

clear_all_tables()
print(str(get_files()))
print (str(get_metadata()))
fill_album_table()
push_songs()
push_artists()
getRelations()
fill_song_album_relation()
fill_artist_album_relation()