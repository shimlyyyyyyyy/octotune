import os
import tinytag
import mysql.connector
import pathlib

hostname="localhost"
database="octotune"
username="root"
password=""





aigen = "https://api-inference.huggingface.co/models/meta-llama/Llama-3.2-1B"
headers = {"Authorization": "Bearer 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"}


#https://stackoverflow.com/questions/3964681/find-all-files-in-a-directory-with-extension-txt-in-python
def get_files():
    paths = []
    for file in os.listdir("songs"):
        if file.endswith(".mp3"):
            paths.append(os.path.join("songs", file))
    return paths

#https://pypi.org/project/tinytag/
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

    


#https://pypi.org/project/tinytag/
#https://www.w3schools.com/python/python_mysql_insert.asp
#https://www.geeksforgeeks.org/how-to-get-file-extension-in-python/
def get_metadata(files = get_files()): 
    val = []
    for each in files:
        tag = tinytag.TinyTag.get(each)
        val.append((tag.title, tag.year, each, each.replace(pathlib.Path(each).suffix, ".jpg"), tag.genre, tag.duration, pathlib.Path(each).suffix))
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

print(str(get_files()))
print (str(get_metadata()))
push_songs()
push_artists()