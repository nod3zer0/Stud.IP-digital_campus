<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreDocuments extends CorePlugin implements StudipModule, OERModule
{


    /**
     * Determines if the StudipModule wants to handle the OERMaterial. Returns false if not.
     * @param OERMaterial $material
     * @return false|Icon
     */
    static public function oerModuleWantsToUseMaterial(OERMaterial $material)
    {
        return true;
    }

    /**
     * Returns an Icon class object with the given role.
     * @param string $role
     * @return null|Icon
     */
    public function oerGetIcon($role = Icon::ROLE_CLICKABLE)
    {
        return Icon::create("file", $role);
    }

    /**
     * This function is triggered i a user chose to use this module as the target of the oermaterial.
     * Now this module should put a copy of $material in its own area of the given course.
     * @param OERMaterial $material
     * @param Course $course
     * @return array|FileType
     */
    static public function oerModuleIntegrateMaterialToCourse(OERMaterial $material, Course $course)
    {
        $folder = Folder::findTopFolder($course->id);
        $folder = $folder->getTypedFolder();
        $uploaded_file = [
            'name' => $material['filename'],
            'type' => $material['content_type'],
            'content_terms_of_use_id' => "FREE_LICENSE",
            'description' => $material['description']
        ];
        $url = $material->getDownloadUrl();
        if ($url) {
            if ($material['host_id']) {
                $tmp_name = $GLOBALS['TMP_PATH'] . '/oer_' . $material->getId();
                file_put_contents($tmp_name, file_get_contents($url, false, get_default_http_stream_context($url)));
                $uploaded_file['tmp_name'] = $tmp_name;
                $uploaded_file['type'] = filesize($tmp_name);
            } else {
                $uploaded_file['tmp_name'] = $material->getFilePath();
                $uploaded_file['size'] = filesize($material->getFilePath());
            }

            $standardfile = StandardFile::create($uploaded_file);
        } elseif($material['source_url']) {
            $standardfile = URLFile::create([
                'url' => $material['source_url'],
                'name' => $material['name'],
                'author_name' => implode(', ', array_map(function ($a) { return $a['name']; }, $material)),
                'description' => $material['description'],
                'content_terms_of_use_id' => "FREE_LICENSE"
            ]);
        }

        if ($standardfile->getSize() || is_a($standardfile, 'URLFile')) {
            $error = $folder->validateUpload($standardfile, User::findCurrent()->id);
            if ($error && is_string($error)) {
                if ($tmp_name) {
                    @unlink($tmp_name);
                }
                return [$error];
            }

            $newfile = $folder->addFile($standardfile);
            if ($tmp_name) {
                @unlink($tmp_name);
            }
            if (!$newfile) {
                return [_('Daten konnten nicht kopiert werden!')];
            }
            return $newfile;
        } else {
            if ($tmp_name) {
                @unlink($tmp_name);
            }
            return [_('Daten konnten nicht kopiert werden!')];
        }
    }


    /**
     * {@inheritdoc}
     */
    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        $range_type = get_object_type($course_id, ['sem', 'inst']) === 'sem' ? 'course' : 'institute';
        $navigation = new Navigation(
            _('Dateibereich'),
            "dispatch.php/{$range_type}/files"
        );
        $navigation->setImage(Icon::create('files', Icon::ROLE_CLICKABLE, ['title' => _('Dateien')]));

        $condition = "INNER JOIN folders ON (folders.id = file_refs.folder_id)
                      WHERE folders.range_type = :range_type
                        AND folders.range_id = :context_id
                        AND GREATEST(file_refs.mkdate, file_refs.chdate) >= :last_visit
                        AND file_refs.user_id != :me";
        $file_refs = FileRef::findBySQL($condition, [
            'me'         => $user_id,
            'last_visit' => $last_visit,
            'context_id' => $course_id,
            'range_type' => $range_type
        ]);
        foreach ($file_refs as $fileref) {
            $foldertype = $fileref->folder->getTypedFolder();
            if ($foldertype->isFileDownloadable($fileref->getId(), $user_id)) {
                $navigation->setImage(Icon::create('files', Icon::ROLE_ATTENTION), [
                    'title' => _('Es gibt neue Dateien.'),
                ]);
                $navigation->setURL("dispatch.php/{$range_type}/files/flat", ['select' => 'new']);
                break;
            }
        }

        return $navigation;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabNavigation($course_id)
    {
        $range_type = get_object_type($course_id, ['sem', 'inst']) === 'sem' ? 'course' : 'institute';
        $newFilesNavigation = new Navigation(_('Dateien'), "dispatch.php/{$range_type}/files");
        $newFilesNavigation->setImage(Icon::create('files', Icon::ROLE_INFO_ALT));
        $newFilesNavigation->setActiveImage(Icon::create('files', Icon::ROLE_INFO));
        return ['files' => $newFilesNavigation];
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        return [
            'summary'          => _('Austausch von Dateien, Hausaufgabenordner & Terminordner'),
            'description'      => _('Im Dateibereich können Dateien sowohl von ' .
                'Lehrenden als auch von Studierenden hoch- bzw. ' .
                'heruntergeladen werden. Es können Ordner angelegt und ' .
                'individuell benannt werden (nur Lehrende). Die Dateien ' .
                'können somit strukturiert zur Verfügung gestellt werden. ' .
                'Multimediadateien wie Grafiken, Audio- und Videodateien ' .
                'können sofort angezeigt bzw. abgespielt werden. Über das ' .
                'PlugIn "Dateiordnerberechtigung" können Im Dateibereich ' .
                'bestimmte Rechte für Studierende, wie z.B. das Leserecht, ' .
                'festgelegt werden.'),
            'displayname'      => _('Dateien'),
            'category'         => _('Lehr- und Lernorganisation'),
            'keywords'         => _('Hoch- und Herunterladen von Dateien;
                            Anlegen von Ordnern und Unterordnern;
                            Verschieben einer Datei/eines Ordners per drag and drop innerhalb einer Veranstaltung;
                            Verschieben einer Datei/eines Ordners in eine andere Veranstaltung;
                            Kopieren einer Datei/eines Ordners in eine andere oder mehrere Veranstaltungen;
                            Verlinkung auf abgelegte Dateien möglich;
                            Erstellung Hausaufgabenordner durch Aktivierung der Funktion "Dateiordnerberechtigung"'),
            'descriptionshort' => _('Austausch von Dateien'),
            'descriptionlong'  => _('Dateien können sowohl von Lehrenden als auch von Studierenden hoch- bzw. ' .
                'heruntergeladen werden. Ordner können angelegt und individuell benannt werden ' .
                '(Standard: nur Lehrende), so dass Dateien strukuriert zur Verfügung gestellt ' .
                'werden können. Multimediadateien wie Grafiken, Audio- und Videodateien werden ' .
                'sofort angezeigt bzw. abspielbar dargestellt. Über das PlugIn "Dateiordnerberechtigungen" ' .
                'können Im Dateibereich bestimmte Rechte (r, w, x, f) für Studierende, wie z.B. das ' .
                'Leserecht (r), festgelegt werden.'),
            'icon'             => Icon::create('files', Icon::ROLE_INFO),
            'icon_clickable'   => Icon::create('files', Icon::ROLE_CLICKABLE),
            'screenshots'      => [
                'path'     => 'assets/images/plus/screenshots/Dateibereich_-_Dateiordnerberechtigung',
                'pictures' => [
                    0 => ['source' => 'Ordneransicht_mit_geoeffnetem_Ordner.jpg', 'title' => _('Ordneransicht mit geöffnetem Ordner')],
                    1 => ['source' => 'Ordneransicht_mit_Dateiinformationen.jpg', 'title' => _('Ordneransicht mit Dateiinformationen')],
                    2 => ['source' => 'Neuen_Ordner_erstellen.jpg', 'title' => _('Neuen Ordner erstellen')],
                    3 => ['source' => 'Ordner_zum_Hausaufgabenordner_umwandeln.jpg', 'title' => _('Ordner zum Hausaufgabenordner umwandeln')],
                    4 => ['source' => 'Ansicht_alle_Dateien.jpg', 'title' => _('Ansicht alle Dateien')]
                ]
            ]
        ];
    }

    public function getInfoTemplate($course_id)
    {
        // TODO: Implement getInfoTemplate() method.
        return null;
    }
}
