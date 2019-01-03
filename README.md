# Projektarbeit für das Modul 151 an der Gewerbeschule Muttenz

## Installation auf localhost
### Datenbank
- das Projekt benötigt zunächst ein Docker-Image mit einem mySQL-Server:
  `docker pull mysql`
  Der Container kann einen beliegigen Namen haben. Auch das root-Passwort spielt keine Rolle.
- auf dem mySQL-Server muss ein Benutzer 'webuser2' mit dem Passwort '151@web' exisieren. Alternativ kann ein anderer User mit anderem Passwort eingerichtet werden, dann muss die Datei "includes/database.php" angepasst werden.
- die Datenbank kann mit dem Script a3.sql erstellt werden. Dieses beinhaltet neben der Grundstruktur auch schon einige User, einen Admin und Inventar.
### PHP - simple Variante
- auf meinem eigenen Rechner (Ubuntu) starte ich php vom Webroot-Verzeichnis aus schlicht mit "php -S localhost:3030". Dies ist die einfachste Option.
### PHP mit eigenem Apache-Container
- alternativ kann ein Docker-Container mit PHP und Apache eingerichtet werden:
  `docker run -d --name apache --link a3:db -v "$PWD":/usr/local/apache2/htdocs/  -p 8080 httpd:2.4`
  dieses Kommando muss vom lokalen Webroot-Verzeichnis ausgeführt werden: es verbindet das aktuelle Verzeichnis ($PWD) mit dem        htdocs-Verzeichnis auf dem Container. Der MySQL-Container muss schon existieren, da er hier mit --link verbunden wird.
  - dem neuen Docker-Container fehlt noch die PDO-Extension: Mit `docker exec -it apache /bin/bash` kann auf die Shell des Containers gewechselt werden. Die Extension installiert man über:
    `docker-php-ext-install pdo_mysql`
    
 ## Troubleshooting
 - Bei einer ungünstigen Kombination der PHP und MySQL-Version tritt beim Aufrufen der Website möglichwerweise folgender Fehler auf:
  `The server requested authentication method unknown to the client`
Workaround:  
  `CREATE USER webuser2 identified with mysql_native_password by '151@web';`
