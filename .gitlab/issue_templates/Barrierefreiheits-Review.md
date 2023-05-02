## Barrierefreiheits-Review

### Prüfung auf ausreichenden Kontrast und Kenntlichmachung bei GUI-Elementen

- [ ] Vordergrund- und Hintergrundfarbe haben einen ausreichenden Kontrast.
  - Mindestens 4,5:1 bei Text und 3:1 bei Icons, idealerweise 7:1.
- [ ] Links werden passend hervorgehoben.
  - Kontrast mindestens 3:1 und mit einer weiteren Hervorhebung.
- [ ] Die angezeigten Informationen sind auch ohne Farbsehen erkennbar.

### Prüfung auf Tastaturbedienbarkeit von Seitenelementen

- [ ] Interaktive Elemente (Link, Button) sind per TAB erreichbar.
- [ ] Elemente verwenden übliche Tasten zur Bedienung
  - Eingabetaste für Links und Buttons.
  - Pfeiltasten für Select-Felder und Radio-Buttons.
  - Leertaste zum Aktivieren von Checkboxen, Radio-Buttons und zum Öffnen von Select-Feldern.
- [ ] Fokusfallen sind nicht vorhanden
- [ ] Die „natürliche“ Reihenfolge der Fokussierung bleibt erhalten.
  - tabindex > 0 wird nicht verwendet.
- [ ] Die Fokussierung wird beim Aufruf von Aktionen nicht zurückgesetzt.

### Prüfung auf Nutzbarkeit von Seitenelementen mit Screenreadern

- [ ] Elemente werden korrekt vorgelesen.
  - Button: „Schalter“
  - Link: „Link“
  - Select-Feld: „Auswahlfeld“/„Auswahlschalter“
- [ ] Icons, die nur Schmuckelemente sind, sind für Screenreader unsichtbar.
- [ ] Icons und Bilder, die eine Information liefern, haben einen Alternativtext, der vorgelesen wird.
- [ ] Vorgelesene Texte referenzieren andere Elemente der Seite ohne Positionsangaben.
- [ ] Anhand des vorgelesenen Textes ist die Struktur der Seite erkennbar.
- [ ] Dopplungen von Text (durch ein Icon neben einem Text) tauchen nicht auf.


/label ~BIEST ~Accessibility ~"Version::5.4"
