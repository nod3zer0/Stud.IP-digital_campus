# Stud.IP v5.5

**23.05.23**

## Neue Features

- Neben dem Vollbild-Modus (eingeführt in der Version 5.0), der nur in bestimmten Kontexten gezeigt wird, gibt es nun einen Modus "kompakte Navigation". Der neue Modus wird über das bisherige Icon für den Vollbildmodus aktiviert. Bitte passen Sie ihre Dokumentationen an.

## Breaking changes

-

## Security related issues

-

## Deprecated Features

- Das Verwenden von LESS-Stylesheets in Plugins wurde deprecated und wird zu Stud.IP 6.0 entfernt werden. Die betroffenen Plugins müssen angepasst und auf SCSS umgestellt werden.

## Known Issues

- Der Vollbildmodus funktioniert nicht auf Apple iPads. Der Modus kann zwar initiiert werden, beendet sich aber selbsständig, wenn nach oben gescrollt wird. Dieses Verhalten ist en Fehler innerhalb von iOS/iPadOS und kann seitens Stud.IP nicht umgangen werden. Der Fehler ist bei Apple gemeldet.
