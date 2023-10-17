
<?php

class CreatePageEasyRead extends Migration {

    public function description()
    {
        return 'Creates a page for information in Easy Read (Leichte Sprache).';

    }
    public function up()
    {
        DBManager::Get()->exec("INSERT INTO `config` (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`)
            VALUES ('EASY_READ_URL', '', 'string', 'global', 'accessibility', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'URL zur Seite \"Leichte Sprache\"')");

        DBManager::Get()->exec("INSERT INTO `siteinfo_details` (`detail_id`, `rubric_id`, `position`, `draft_status`, `name`, `content`)
            VALUES (NULL, '1', NULL, '1', 'Leichte Sprache',
            '++**Leichte Sprache**

1) Beschreibung des Anbieters und des Zwecks der Seite
Dies sind die Internet-Seiten für Inhalte zum Lernen und Lehren von **[Einrichtung einsetzen]** .
Eine [anpassen: Universität/Hochschule/Volks-Hochschule/ oder anderes] ist ein Ort an dem man nach der normalen Schule weiter lernen kann.
Wenn man hier lernt [ggf. anpassen/arbeitet/eine Ausbildung macht] oder als Lehrer arbeitet, bekommt man Anmelde-Daten.
Wenn man angemeldet ist, findet man Material zum Unterricht.
Außerdem kann man seinen Kalender und Stundenplan sehen.
Man kann mit anderen Nachrichten schreiben.

2) Hinweise zur Navigation
Um sich anzumelden, braucht man einen Benutzer-Namen und ein Passwort.
Benutzer-Name und Passwort bekommt man von **[Name bzw. Einrichtung angeben].**
Man meldet sich in dem Kasten an, wo Login steht.
Hilfe bei der Anmeldung findet man [Link von Einrichtung einzusetzen oder Text von Einrichtung zu ergänzen].
[Falls auf der Startseite vorhanden:
Hilfe gibt es oben rechts [ggf. anpassen] bei dem Fragezeichen.
Ganz unten bei Impressum findet man Angaben dazu, wer die Seite gemacht hat.
Ganz unten bei Datenschutz steht, welche Daten von Besuchern der Seite verwendet werden.
Ganz unten kann man unter Barriere melden sich beschweren, wenn man die Seite nicht bedienen kann.

3) Erläuterung der wesentlichen Inhalte der Erklärung zur Barrierefreiheit
[Je nach Standort sind ggf. die Gesetzesstellen und Behörden anzupassen.]

Erklärung zur Barriere-Freiheit in leichter Sprache
Die [Betreibername einsetzen] ist für Barriere-Freiheit im Internet.
Das bedeutet: Alle Menschen bekommen alle wichtigen Infos.
Zum Beispiel können blinde Menschen Vorlese-Programme nutzen.

Die [Betreibername einsetzen] beachtet die Vorschriften.
Dazu ist man gesetzlich verpflichtet.

Das sind:
- das Behinderten-Gleichstellungs-Gesetz (BGG)
- Verordnung zur Schaffung barrierefreier Informations-Technik nach dem Behinderten-Gleichstellungs-Gesetz (BITV)
- das Behinderten-Gleichstellungs-Gesetz des [Bundesland oder Bund einfügen]

[Da es Pflicht ist, auf bekannte Barrieren hinzuweisen, sind diese hier vom jeweiligen Betreiber zusammenzufassen und in leichter Sprache zu erläutern.
In etwa:
- Auf manchen Seiten sind die Überschriften ein bisschen durcheinander. Zum Beispiel: Da steht was unten mit kleinen Buchstaben. Das müsste aber oben mit größeren Buchstaben stehen.
- Manche Sachen werden so vorgelesen, dass blinde Menschen sie schlecht verstehen. Sie sehen das ja nicht.
…]

Sind Sie nicht zufrieden?
Haben Sie eine Barriere gefunden?
Sie können uns schreiben.
**Hier ist ein Formular:**
[jeweiliges Barriere-melden-Formular am Standort verlinken]

**Hier ist unsere Adresse:**
[Adresse einfügen]

**Sie können uns anrufen:**
[Telefonnummer einfügen]

Es gibt die **Schlichtungs-Stelle.**

**Schlichtung** bedeutet:
- Sich einigen.
- Sich vertragen.
Die Schlichtungs-Stelle **hilft bei einem Streit.**

Zum Beispiel:
1. Es gibt eine Barriere bei der [Einrichtung einfügen] auf den Internet-Seiten.
2. Sie haben sich darüber beschwert.
3. Die Barriere bleibt aber.

Jetzt kann die **Schlichtungs-Stelle helfen.**
Der Streit muss dann nicht vor ein Gericht.
Beide Seiten sollen sich vertragen.
Sie können eine **Schlichtung beantragen.**

Zum Beispiel:
Sie sind mit einer Antwort von [Einrichtung einfügen] zur Barriere-Freiheit nicht zufrieden.

Eine **Schlichtung** kostet nichts.
Sie brauchen **keinen Anwalt.**
Sie können den **Antrag in Leichter Sprache oder in Deutscher Gebärden-Sprache** stellen.

**Hier gibt es weitere Informationen:**
[Idealerweise Link auf jeweilige Schlichtungsstelle und deren Informationen in leichter Sprache einfügen]

**Hier ist die Adresse der Schlichtungs-Stelle:**
[jeweils zuständige Stelle einfügen]

**Hier ist die Telefonnummer:**
[Telefonnummer einfügen]

4) Hinweise auf weitere in diesem Auftritt vorhandene Informationen in Deutscher Gebärdensprache und in Leichter Sprache

[So weitere Hinweise in Leichter Sprache oder Gebärdensprache vorhanden sind, muss auf die entsprechenden Orte verwiesen werden.]
Hier finden Sie weitere Hinweise in Leichter Sprache: [Link einfügen]
Oder:
Es sind keine weiteren Informationen auf diesen Seiten in leichter oder Gebärden-Sprache enthalten.++
')");


        $query = "SELECT `rubric_id`, `detail_id`
                  FROM `siteinfo_details`
                  WHERE `name` = 'Leichte Sprache'
                  ORDER BY `detail_id` DESC";
        $result = DBManager::get()->fetchOne($query);
        $easy_read_url = "dispatch.php/siteinfo/show/{$result['rubric_id']}/{$result['detail_id']}";
        DBManager::Get()->execute("INSERT INTO `config_values` (`field`, `range_id`, `value`, `mkdate`, `chdate`, `comment`) VALUES ('EASY_READ_URL', 'studip', ?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), ' zur Seite \"Leichte Sprache\"')", [$easy_read_url]);

    }

    public function down()
    {
        $db = DBManager::get();
        $db->exec("DELETE FROM `config` WHERE `field` = 'EASY_READ_URL'");
        $db->exec("DELETE FROM `config_values` WHERE `field` = 'EASY_READ_URL'");
        $db->exec("DELETE FROM `siteinfo_details` WHERE name = 'Leichte Sprache'");
    }


}
