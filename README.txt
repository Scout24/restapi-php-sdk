Immocaster PHP SDK v1.1.76
==========================
Author:     Norman Braun (http://www.medienopfer98.de)
Copyright:  Immobilien Scout GmbH
Link:       http://www.immobilienscout24.de

Das PHP SDK von ImmobilienScout24 steht unter der FreeBSD Lizenz zur Verfügung und kann für private sowie kommerzielle Projekte eingesetzt werden. Lediglich die Verweise wie Copyright, Autor, etc. müssen in den Dateien erhalten bleiben. Weitere Infos zur Lizenz befinden sich unter Immocaster/LICENSE.txt.

History
=======

SDK Version 1.1.76
- GET Publish zum Ermitteln der Publishchannels von einem Objekt möglich. Optional kann auch der Channel mitgegeben werden.
- WICHTIG: Ab dem 01.06.2015 werden die Publishchannels nicht mehr in der GETbyID Reealestate Response auftachen. Diese Informationen spielt IS24 demnächst nur noch via GET Publish (SDK: getPublish()).

SDK Version 1.1.75
- DELETE Contact möglich. Powered by amenk.

SDK Version 1.1.74
- PUT Attachment möglich. Hinweis: Aktualisiert die Metdadaten vom Attachment jeder Art (Video, Bild, PDF und URL), aber nicht die binären!

SDK Version 1.1.73
- GET/PUT Attachmentsorder möglich. Bitte beachten, dass nur die attachmentids von Bildern und PDFs sortiert werden.

SDK Version 1.1.72
- POST Attachment (URL) innerhalb exportObjectAttachment() möglich. Je nach Attachmenttyp (Picture, PDFDocument oder Link) wird der Body erzeugt. Außerdem Bugfix in OAuth.php powered by kaischi70.

SDK Version 1.1.71
- POST Attachment (StreamingVideo) nun möglich. Dieser Upload erfolgt in 3 Schritten: Upload Ticket erhalten, Video hochladen, Video verknüpfen. Powered by onOffice Software AG.

SDK Version 1.1.70
- Besseres Handling für POSTbyList OnTop Placement. Nun kann neben dem fertigen Body auch eine Liste von komma separierten ScoutIDs bzw. ext-ObjektNr übergeben werden.

SDK Version 1.1.69
- DELETEbyList OnTop Placement möglich.

SDK Version 1.1.68
- Real Estate Requests nutzen nun den Parameter "usenewenergysourceenev2014values=true" bei jedem Request. Damit ist es möglich 8 neue enum Werte des Feldes zu POST, PUT und GETen.

SDK Version 1.1.67
- Verbesserungen Rest.php: getExpose und __call.
- Spalten ic_username beim Erstellen der Tabelle nullable und konsequentes Setzen von ic_expire.

SDK Version 1.1.66
- Besseres Handling mit der Authentifizierung. Bekomme ganzes Array in index zurück, wenn ich ohne MySQL Datenbank authentifiziere.

SDK Version 1.1.65
- Nicht mehr verwenden von Session.php beim Authentifizieren ohne MySQL Datenbank. Session wird in Rest.php gestartet und gelöscht.

SDK Version 1.1.64
- Authentifizierung ohne MySQL Datenbank möglich. Dazu in der index.php authenticateWithoutDB(true) aufrufen. Benutzername muss nicht eingegeben werden. Request Token und Secret werden in einer Session gespeichert, nicht aber Access Token und Secret. Weiterverwendung des erzeugten Access Token und Secret in Requests des SDKs noch nicht möglich.
- Bei Authentifizierung mit MySQL wird nun gar keine Session mehr benutzt.

SDK Version 1.1.63
- Hinzufügen von bereits verfügbaren Requests in index.php.

SDK Version 1.1.62
- OnTop Placement Ressource verfügbar. Damit können OnTop Platzierungen (Top-, Premium- und Schaufensterplatzierung) für Objekte gebucht, abgerufen und gelöscht werden.

SDK Version 1.1.61
- Array mit Username für Funktion fullUserSearch(); hinzugefügt. Führte zu Missverständnissen, da immer "me" benutzt wurde.

SDK Version 1.1.60
- Beispiel XML Code für das Exportieren von Objekten in der index.php. Bitte zukünftig nur noch diesen Weg zum Exportieren benutzen! (früher mit Hilfe von XML Writer Dateien)

SDK Version 1.1.59
- Data/Session zur Erstellung von Sessions wird innerhalb von Immocaster nur für die Zertifizierung benötigt und kann ab jetzt deaktiviert werden.

SDK Version 1.1.58
- Debuggen mit Response. Im Array ist nun auch noch die Response des Requests enthalten.

SDK Version 1.1.57
- Debug-Möglichkeit für Requests. Request-Informationen können nun als Array zurückgegeben und ausgewertet werden.

SDK Version 1.1.56
- Update index.php & Rest.php. Man kann nun Objekte löschen.

SDK Version 1.1.55
- Update Rest.php & Immobilienscout.php. Man kann beim Export von Bildern eine
externalId mitgeben.

SDK Version 1.1.54
- Mimetype Unterstützung für PHP Versionen unter 5.3.

SDK Version 1.1.53
- Anpassungen im XML Writer für Mietwohnungen. (Der XML Writer wird nicht mehr unterstützt! Mehr dazu im Wiki.)

SDK Version 1.1.52
- Update der mysql.php. Bei anlegen der Tabelle für die Token wird nun das Feld "ic_username" mit angelegt.

SDK Version 1.1.51
- Update der Links zum Wiki in der index.php.

SDK Version 1.1.50
- Unterstützung des PDF-Export

SDK Version 1.1.49
- Bugfix verschiedener Funktionen aufgrund des neuen Multi-Token-Feature.
! Evtl. muss die Applikation neu zertifiziert werden.

SDK Version 1.1.48
- Funktion zum Ändern von Kontaktinformationen

SDK Version 1.1.47
- Bugfix in "getApplicationToken" (Username darf nicht leer sein)

SDK Version 1.1.46
- Support von Strict-Mode
- Feature für Multi-Token
- Auslesen von bereits zertifizierten Benutzernamen

SDK Version 1.1.44
- Erweiterung um Kontaktinformationen auszulesen und zu exportieren (exportContact und getContact).

SDK Version 1.1.43
- Bug in Sandbox-URL behoben

SDK Version 1.1.42
- Korrektur der XML-Dateien

SDK Version 1.1.41
- Methode zum löschen von Attachments
- cURL als Standard für den Datenaustausch (file_get_contents() wird nicht mehr unterstützt)
- Umstellung auf die neue ImmobilienScout24-URL für Sandbox-Anfragen

SDK Version 1.1.38
- Auslesen von Attachements von selbst exportieren Objekten (*BETA)

SDK Version 1.1.36
- Aktivieren von Objekten

SDK Version 1.1.35
- Aktualisieren von Objektdaten
- Deaktivierung von Objekten

SDK Version 1.1.33
- Composer.json für Packagist. (https://packagist.org/packages/immocaster/php-sdk)

SDK Version 1.1.32
- Eigene Exposes via Offer-API auslesen.

SDK Version 1.1.31
- Eigenen XML für den Objektexport durchreichen.

SDK Version 1.1.30
- Bugfix: Exportproblem bei HouseRent und HouseBuy behoben.

SDK Version 1.1.29
- Funktion zum Auslesen aller Objekte eines Maklers.

SDK Version 1.1.28
- Exportfunktion für Objekt-Bilder (JPG,GIF,PNG).

SDK Version 1.1.26
- Exportfunktion für Wohnungen und Häuser zu ImmobilienScout24 (ohne Dateianhänge).

SDK Version 1.1.25
- Möglichkeit für ein Listing von Channels in die ein zertifizierter Nutzer exportieren darf

SDK Version 1.1.24
- Neue Funktion zum auslesen eines Anbieterlogos anhand des Benutzernamen

SDK Version 1.1.23
- Neue Funktion zum auslesen eines Impressums für ein Objekt

SDK Version 1.1.22
- Probleme bei der Registrierung mit cURL behoben
- History in Readme Datei

SDK Version 1.1.20
- Bugs von Version 1.1.19 behoben
- POST Support
- Neue Funktion zum versenden von Kontaktanfragen
- Neue Funktion zum empfehlen von Objekten

SDK Version 1.1.19 - Nicht mehr nutzen!
- Bug: Beim Aufruf von Exposes
- JSON Support

SDK Version 1.1.18
- Support von cURL
- Verbesserte Fehlerausgabe

SDK Version 1.1.15
- Bug von Version 1.1.14 behoben

SDK Version 1.1.14 - Nicht mehr nutzen!
- Bug: Beim Aufruf von Exposes per 3-legged-oauth
- Problem mit SDK bei Hosting-Paketen gelöst (mit "php.ini")
- Objektaufrufe nun über 2 und 3-legged-oauth möglich

SDK Version 1.1.13
- Kleine Updates von Funktionen und Kommentaren

SDK Version 1.1.12
- Integriertes 3-Legged-Oauth zum Zertifizieren von Applikationen
- Neue Möglichkeiten innerhalb der Funktionen (z.B. nur innerhalb von Maklerobjekten suchen)

SDK Version 1.0.6
- Call-Funktionen von private auf public gesetzt um Warnmeldungen zu verhindern

SDK Version 1.0.5
- Erste Version des SDK
