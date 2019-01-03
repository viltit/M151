# Projektarbeit für das Modul 151 an der Gewerbeschule Muttenz

## Installation auf localhost
###Datenbank
- das Projekt benötigt zunächst ein Docker-Image mit einem mySQL-Server:
  `docker pull mysql`
  Der Container kann einen beliegigen Namen haben. Auch das root-Passwort spielt keine Rolle.
- auf dem mySQL-Server muss ein Benutzer 'webuser' mit dem Passwort 'viti@webuser' exisieren. Alternativ kann ein anderer User mit anderem Passwort eingerichtet werden, dann muss die Datei "includes/database.php" angepasst werden.
- die Datenbank kann mit dem Script a3.sql erstellt werden. Dieses beinhaltet neben der Grundstruktur auch schon einige User, einen Admin und Inventar.
###PHP
- auf meinem eigenen Rechner (Ubuntu) starte ich php vom Webroot aus schlicht mit "php -S localhost:3030"
