<?php

class NewExternalPages extends Migration
{
    public function description()
    {
        return 'Adds a new table for the configuration and creates a new config entry for error message texts on external pages.';
    }

    public function up()
    {
        $db = \DBManager::get();

        $db->exec("CREATE TABLE `extern_pages_configs` (
            `config_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `type` varchar(50) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
            `description` tinytext COLLATE utf8mb4_unicode_ci NOT NULL,
            `conf` text COLLATE utf8mb4_unicode_ci NOT NULL,
            `template` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
            `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `mkdate` int(11) UNSIGNED NOT NULL DEFAULT '0',
            `chdate` int(11) UNSIGNED NOT NULL DEFAULT '0',
            PRIMARY KEY (`config_id`),
            KEY `range_id` (`range_id`),
            KEY `type` (`type`))
        ");

        $this->migrate_configurations();

        $description = 'Allgemeine Fehlermeldung,die auf der Webseite ausgegeben wird, auf der der Inhalt der '
            . 'externe Seite angezeigt werden soll. Diese Meldung wird ausgegeben, '
            . 'wenn z.B. das Template fehlerhaft ist.';
        $message = 'Ein Fehler ist aufgetreten. Die Inhalte können nicht angezeigt werden.';
        DBManager::get()->exec(
            "INSERT IGNORE INTO `config`
            (`field`, `value`, `type`, `range`,
            `section`,
            `mkdate`, `chdate`,
            `description`)
            VALUES
            ('EXTERN_PAGES_ERROR_MESSAGE', '{$message}', 'string', 'global',
            'external_pages', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
            '{$description}')"
        );

        $db->exec(
            "DELETE FROM `config` WHERE `field` = 'EXTERN_ALLOW_ACCESS_WITHOUT_CONFIG'"
        );
        $db->exec(
            "DELETE FROM `config` WHERE `field` = 'EXTERN_SRI_ENABLE'"
        );
        $db->exec(
            "DELETE FROM `config` WHERE `field` = 'EXTERN_SRI_ENABLE_BY_ROOT'"
        );
        $db->exec("DROP TABLE `extern_config`");

    }

    public function down()
    {
        $db = \DBManager::get();

        $db->exec(
            "DELETE FROM `config` WHERE `config_id` = 'EXTERN_PAGES_ERROR_MESSAGE'"
        );
        $db->exec('DROP TABLE IF EXISTS `extern_pages_configs`');

        $db->exec("CREATE TABLE `extern_config` (
            `config_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
            `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
            `config_type` int(4) NOT NULL DEFAULT '0',
            `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
            `is_standard` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
            `config` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
            `mkdate` int(11) UNSIGNED NOT NULL DEFAULT '0',
            `chdate` int(11) UNSIGNED NOT NULL DEFAULT '0',
            PRIMARY KEY (`config_id`,`range_id`))
        ");

        $configs = [
            'EXTERN_ALLOW_ACCESS_WITHOUT_CONFIG' =>
                'Free access to external pages (without the need of a configuration), independent of SRI settings above',
            'EXTERN_SRI_ENABLE' =>
                'Allow the usage of SRI-interface (Stud.IP Remote Include)',
            'EXTERN_SRI_ENABLE_BY_ROOT' =>
                'Only root allows the usage of SRI-interface for specific institutes'
        ];
        foreach ($configs as $field => $description) {
            $db->exec(
                "INSERT IGNORE INTO `config`
            (`field`, `value`, `type`, `range`,
            `section`,
            `mkdate`, `chdate`,
            `description`)
            VALUES
            ({$field}, '0', 'boolean', 'global',
            'global', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
            '{$description}')"
            );
        }

    }

    private function migrate_configurations()
    {
        $skip_text = 'Die Konfiguration (ID: %s, TYP: %s, NAME: %s) kann nicht migriert werden. ';
        $db = DBManager::get();
        $db->fetchAll('SELECT * FROM `extern_config`', [],
            function ($config) use ($skip_text)
            {
                // skip non template based configs
                if ($config['config_type'] < 9) {
                    $this->announce($skip_text . 'Die Seite ist nicht Template-basiert.',
                        $config['config_id'], $config['config_type'], $config['name']);
                    return;
                }
                $new_conf = new ExternPageConfig($config['config_id']);
                $new_conf->range_id = $config['range_id'];
                $new_conf->name = $config['name'];
                $new_conf->mkdate = $config['mkdate'];
                $new_conf->chdate = time();
                $old_data = json_decode($config['config']);
                $new_data = [
                    'language' => 'de_DE',
                    'escaping' => 'htmlReady',
                    // this is used to show a hint that this configuration has still to be fixed after the migration
                    'not_fixed_after_migration' => 1
                ];
                if (isset($old_data->Main->language) && strlen((string) $old_data->Main->language) < 5) {
                    $new_data['language'] = $old_data->Main->language;
                }
                switch ($config['config_type']) {
                    // Persons
                    case 9:
                        $new_conf->type = 'Persons';
                        // The new sort fields are different, so match as good as possible.
                        // Use "nachname" as default.
                        if (isset($old_data->Main->sort)) {
                            asort($old_data->Main->sort, SORT_NUMERIC);
                            if (key($old_data->Main->sort) === '3') {
                                $new_data['sort'] = 'email';
                            } else {
                                $new_data['sort'] = 'nachname';
                            }
                        }
                        if (isset($old_data->Main->grouping) && $old_data->Main->grouping === '1') {
                            $new_data['grouping'] = true;
                        }
                        $new_data['groupsvisible'] = [];
                        if (isset($old_data->Main->groupsvisible)) {
                            foreach ($old_data->Main->groupsvisible as $groupsvisible) {
                                $new_data['groupsvisible'][$groupsvisible] = 1;
                            }
                        }
                        $new_data['groupsalias'] = [];
                        if (isset($old_data->Main->groupsalias)) {
                            $new_data['groupsalias'] = $old_data->Main->groupsalias;
                        }
                        $replace = [
                            'PERSONS' => 'GROUPS',
                            'PERSON'  => 'PERSONS',
                            'GROUP'   => ''
                        ];
                        $template = $this->replaceSubparts(
                            $replace,
                            $this->replaceDatafieldMarkers($old_data->TemplateGeneric)
                        );
                        $replace = [
                            'IMAGE-URL-NORMAL' => 'IMAGE_URL_NORMAL',
                            'IMAGE-URL-MEDIUM' => 'IMAGE_URL_MEDIUM',
                            'IMAGE-URL-SMALL'  => 'IMAGE_URL_SMALL',
                            'HOMEPAGE-HREF'    => 'HOMEPAGE_URL'
                        ];
                        $template = $this->replaceMarkers($replace, $template);
                        $replace = [
                            'PERSONS' => ''
                        ];
                        $new_conf->template = $this->replaceAllOldMarkers(
                            $this->replaceNoMarkerSubparts($replace, $template)
                        );
                        break;
                    // Download
                    case 10:
                        $new_conf->type = 'Download';
                        $sort_fields = [
                            'filetype',
                            'name',
                            '',
                            'mkdate',
                            'size',
                            'author'
                        ];
                        if (isset($old_data->Main->sort)) {
                            arsort($old_data->Main->sort, SORT_NUMERIC);
                            $new_data['sort'] = $sort_fields[key($old_data->Main->sort)];
                        }
                        $new_data['subfolders'] = 1;
                        $template = $this->replaceDatafieldMarkers($old_data->TemplateGeneric);
                        $replace = [
                            'FILE_NAME'        => 'NAME',
                            'FILE_FILE-NAME'   => 'NAME',
                            'FILE_SIZE'        => 'SIZE',
                            'FILE_DESCRIPTION' => 'DESCRIPTION',
                            'FILE_UPLOAD-DATE' => 'UPLOAD_DATE',
                            'FILE_HREF'        => 'URL',
                            'FILE_ICON-HREF'   => 'ICON_URL'
                        ];
                        $template = $this->replaceMarkers($replace, $template);
                        $replace = [
                            'DOWNLOAD' => '',
                            'FILE'     => '',
                            'FILES'    => 'FILES'
                        ];
                        $template = $this->replaceSubparts($replace, $template);
                        $replace = [
                            'FILES' => ''
                        ];
                        $new_conf->template = $this->replaceAllOldMarkers(
                            $this->replaceNoMarkerSubparts($replace, $template)
                        );
                        break;
                    // news
                    case 11:
                        $this->announce($skip_text .
                            'Die Ausgabe von Ankündigungen an Einrichtungen wird nicht mehr unterstützt.',
                            $config['config_id'], $config['config_type'], $config['name']);
                        return;
                    // courses
                    case 12:
                        $new_conf->type = 'Courses';
                        $new_data['groupby'] = $old_data->Main->grouping;
                        $new_data['startsem'] = $this->convertSemesters($old_data->Main->semstart);
                        $new_data['semcount'] = $old_data->Main->semrange;
                        $new_data['semswitch'] = $old_data->Main->semswitch;
                        $new_data['participating'] = $old_data->Main->allseminars;
                        $new_data['semtypes'] = (array) $old_data->ReplaceTextSemType->visibility;
                        $new_data['studyareas'] = (array) $old_data->SelectSubjectAreas->subjectareasselected;
                        $replace = [
                            'START_SEMESTER'                 => 'START_SEMESTER_NAME',
                            'LECTURES-SUBSTITUTE-GROUPED-BY' => 'GROUPED_BY',
                            'GROUP'                          => 'NAME',
                            'SEMTYPE'                        => 'SEMTYPE_NAME',
                            'UNAME'                          => 'USERNAME'
                        ];
                        $template = $this->replaceMarkers(
                            $replace,
                            $this->replaceDatafieldMarkers($old_data->TemplateGeneric)
                        );
                        $replace = [
                            'LECTURES'  => '',
                            'GROUP'     => 'GROUPED_COURSES',
                            'LECTURE'   => 'COURSES',
                            'LECTURERS' => 'LECTURERS'
                        ];
                        $template = $this->replaceSubparts($replace, $template);
                        $replace = [
                            'LECTURES' => 'COURSES'
                        ];
                        $new_conf->template = $this->replaceAllOldMarkers(
                            $this->replaceNoMarkerSubparts($replace, $template)
                        );
                        break;
                    // course details
                    case 13:
                        $new_conf->type = 'CourseDetails';
                        $new_data['rangepathlevel'] = $old_data->Main->rangepathlevel;
                        // combine templates
                        $pattern = [
                            '/(\{%\s+|###)NEWS(###|\s+%})/',
                            '/(\{%\s+|###)STUDIP-DATA(###|\s+%})/'
                        ];
                        $replacement = [
                            $old_data->TemplateNews->template,
                            $old_data->TemplateStudipData->template
                        ];
                        $template = preg_replace(
                            $pattern,
                            $replacement,
                            $this->replaceDatafieldMarkers($old_data->TemplateLectureData)
                        );
                        $replace = [
                            'MISC'              => 'MISCELLANEOUS',
                            'LEISTUNGSNACHWEIS' => 'CERTIFICATE',
                            'UNAME'             => 'USERNAME',
                            'TUTOR_FULLNAME'    => 'FULLNAME',
                            'TUTOR_LASTNAME'    => 'LASTNAME',
                            'TUTOR_FIRSTNAME'   => 'FIRSTNAME',
                            'TUTOR_TITLEFRONT'  => 'TITLEFRONT',
                            'TUTOR_TITLEREAR'   => 'TITLEREAR',
                            'TUTOR_UNAME'       => 'USERNAME',
                            'NEWS_TOPIC'        => 'TOPIC',
                            'NEWS_BODY'         => 'BODY',
                            'NEWS_DATE'         => 'DATE',
                            'PRELIM-DISCUSSION' => 'PRELIM_DISCUSSION',
                            'FIRST-MEETING'     => 'FIRST_MEETING',
                            'HOME-INST-NAME'    => 'HOME_INST_NAME',
                            'COUNT-USER'        => 'COUNT_USER',
                            'INVOLVED-INSTITUTE_NAME' => 'NAME'
                        ];
                        $template = $this->replaceMarkers($replace, $template);
                        $replace = [
                            'LECTUREDETAILS' => '',
                            'LECTURER'       => '',
                            'LECTURERS'      => 'LECTURERS',
                            'TUTOR'          => '',
                            'TUTORS'         => 'TUTORS',
                            'NEWS'           => '',
                            'ALL-NEWS'       => 'NEWS',
                            'SINGLE-NEWS'    => '',
                            'STUDIP-DATA'    => '',
                            'RANGE-PATHES'   => 'RANGE_PATHS',
                            'RANGE-PATH'     => '',
                            'MODULES'        => 'MODULES',
                            'MODULE'         => '',
                            'INVOLVED-INSTITUTES' => 'INVOLVED_INSTITUTES',
                            'INVOLVED-INSTITUTE'  => '',
                        ];
                        $template = $this->replaceSubparts($replace, $template);
                        $replace = [
                            'NEWS' => ''
                        ];
                        $new_conf->template = $this->replaceAllOldMarkers(
                            $this->replaceNoMarkerSubparts($replace, $template)
                        );
                        break;
                    // person details
                    case 14:
                        $new_conf->type = 'PersonDetails';
                        $new_data['defaultaddr'] = $old_data->Main->defaultaddr;
                        $new_data['startsem'] = $this->convertSemesters($old_data->PersondetailsLectures->semstart);
                        $new_data['semcount'] = $old_data->PersondetailsLectures->semrange;
                        $new_data['semswitch'] = $old_data->PersondetailsLectures->semswitch;
                        $new_data['semclass'] = $old_data->PersondetailsLectures->semclass;
                        // combine templates
                        $pattern = [
                            '/(\{%\s+|###)LECTURES(###|\s+%})/',
                            '/(\{%\s+|###)NEWS(###|\s+%})/',
                            '/(\{%\s+|###)APPOINTMENTS(###|\s+%})/',
                            '/(\{%\s+|###)OWNCATEGORIES(###|\s+%})/'
                        ];
                        $replacement = [
                            $old_data->TemplateLectures->template,
                            $old_data->TemplateNews->template,
                            $old_data->TemplateAppointments->template,
                            $old_data->TemplateOwnCategories->template
                        ];
                        $template = preg_replace(
                            $pattern,
                            $replacement,
                            $this->replaceDatafieldMarkers($old_data->TemplateMain)
                        );
                        $replace = [
                            'IMAGE-HREF'               => 'IMAGE_URL_NORMAL',
                            'INST-NAME'                => 'NAME',
                            'SINGLE-INST-NAME'         => 'NAME',
                            'SINGLE-INST-STREET'       => 'STREET',
                            'SINGLE-INST-ZIPCODE'      => 'ZIPCODE',
                            'SINGLE-INST-EMAIL'        => 'EMAIL',
                            'SINGLE-INST-ROOM'         => 'MEMBER_ROOM',
                            'SINGLE-INST-PHONE'        => 'MEMBER_PHONE',
                            'SINGLE-INST-FAX'          => 'MEMBER_FAX',
                            'SINGLE-INST-HREF'         => 'HOMEPAGE',
                            'SINGLE-INST-OFFICE-HOURS' => 'MEMBER_OFFICEHOURS',
                            'NEWS_TOPIC'               => 'TOPIC',
                            'NEWS_BODY'                => 'BODY',
                            'NEWS_DATE'                => 'DATE',
                            'LIST-START'               => 'APPOINTMENTS_START',
                            'LIST-END'                 => 'APPOINTMENTS_END',
                            'REPETITION'               => 'RECURRENCE',
                            'BEGIN'                    => 'START',
                            'OWNCATEGORY_TITLE'        => 'TITLE',
                            'OWNCATEGORY_CONTENT'      => 'CONTENT',
                            'OFFICE-HOURS'             => 'OFFICEHOURS',
                            'HOMEPAGE-HREF'            => 'HOMEPAGE_URL'
                        ];
                        $template = $this->replaceMarkers($replace, $template);
                        $replace = [
                            'PERSONDETAILS'      => '',
                            'SINGLE-INST'        => '',
                            'SEMESTER'           => '',
                            'NEWS'               => '',
                            'SINGLE-NEWS'        => '',
                            'APPOINTMENTS'       => '',
                            'SINGLE-APPOINTMENT' => '',
                            'ALL-INST'           => 'INSTITUTES',
                            'LECTURES'           => 'SEMESTERS',
                            'LECTURE'            => 'LECTURES',
                            'ALL-NEWS'           => 'NEWS',
                            'ALL-APPOINTMENTS'   => 'APPOINTMENTS',
                            'OWNCATEGORIES'      => 'OWNCATEGORIES',
                            'OWNCATEGORY'        => ''
                        ];
                        $template = $this->replaceSubparts($replace, $template);
                        $replace = [
                            'NEWS'         => '',
                            'APPOINTMENTS' => ''
                        ];
                        $new_conf->template = $this->replaceAllOldMarkers(
                            $this->replaceNoMarkerSubparts($replace, $template)
                        );
                        break;
                    // sembrowser
                    case 15:
                        $this->announce($skip_text .
                            'Der Seminarbrowser wird nicht mehr unterstützt.',
                            $config['config_id'], $config['config_type'], $config['name']);
                        return;
                    // persbrowser
                    case 16:
                        $new_conf->type = 'PersBrowse';
                        $new_data['instperms'] = $old_data->Main->instperms;
                        $new_data['onlylecturers'] = $old_data->Main->onlylecturers;
                        $new_data['institutes'] = $old_data->SelectInstitutes->institutesselected;
                        // combine templates
                        $pattern = [
                            '/(\{%\s+|###)LISTCHARACTERS(###|\s+%})/',
                            '/(\{%\s+|###)LISTINSTITUTES(###|\s+%})/',
                            '/(\{%\s+|###)LISTPERSONS(###|\s+%})/'
                        ];
                        $replacement = [
                            $old_data->TemplateListCharacters->template,
                            $old_data->TemplateListInstitutes->template,
                            $old_data->TemplateListPersons->template
                        ];
                        $template = preg_replace(
                            $pattern,
                            $replacement,
                            $old_data->TemplateMain->template
                        );
                        $new_data['language'] = $old_data->Main->language;
                        $replace = [
                            'CHARACTER_COUNT_USER' => 'CHARACTER_COUNT',
                            'INSTITUTE_NAME'       => 'NAME',
                            'INSTITUTE_COUNT_USER' => 'COUNT_USERS'
                        ];
                        $template = $this->replaceMarkers($replace, $template);

                        $replace = [
                            'PERS_BROWSER'    => '',
                            'CHARACTER'       => '',
                            'LIST_CHARACTERS' => 'CHARACTERS',
                            'INSTITUTE'       => '',
                            'LIST_INSTITUTES' => 'INSTITUTES',
                            'LIST_PERSONS'    => '',
                            'PERSON'          => '',
                            'PERSONS'         => 'PERSONS'
                        ];
                        $template = $this->replaceSubparts($replace, $template);

                        $replace = [
                            'PERSONS' => ''
                        ];
                        $new_conf->template = $this->replaceAllOldMarkers(
                            $this->replaceNoMarkerSubparts($replace, $template)
                        );
                }
                $new_conf->conf = json_encode($new_data);
                $new_conf->store();
            });
    }

    private function replaceMarkers(array $marker_replacements, $template)
    {
        foreach ($marker_replacements as $old_marker => $new_marker) {
            $patterns = [
                '/###' . $old_marker . '###/',
                '/\{%(\s.*)' . $old_marker . '(.*\s)%}/'
            ];
            $replacements = [
                '###' . $new_marker . '###',
                '{%${1}' . $new_marker . '${2}%}'
            ];
            $template = preg_replace($patterns, $replacements, $template);
        };
        return $template;
    }

    private function replaceSubparts(array $marker_replacements, $template)
    {
        foreach ($marker_replacements as $old_marker => $new_marker) {
            $patterns = [
                '/<!--\s+BEGIN\s+' . $old_marker . '\s+-->/',
                '/\{%\s+foreach.+' . $old_marker . '(\s.+)%}/',
                '/<!--\s+END\s+' . $old_marker . '\s+-->/'
            ];
            if ($new_marker === '') {
                $replacements = ['', '', ''];
            } else {
                $replacements = [
                    '{% foreach ' . $new_marker . ' %}',
                    '{% foreach ' . $new_marker . '${1}%}',
                    '{% endforeach %}'
                ];
            }
            $template = preg_replace($patterns, $replacements, $template);
        }
        return $template;

    }

    private function replaceAllOldMarkers($template)
    {
        return preg_replace(
            '/###(([A-Z0-9_-]+)|(DATAFIELD_[0-9a-f]+))###/',
            '{% ${1} %}',
            $template);
    }

    private function replaceNoMarkerSubparts(array $markers, $template)
    {
        foreach ($markers as $marker => $replace) {
            $patterns = [
                '/<!--\s+BEGIN\s+NO-' . $marker . '\s+-->/',
                '/<!--\s+END\s+NO-' . $marker . '\s+-->/'
            ];
            $new_marker = $replace !== '' ? $replace : $marker;
            $replacements = [
                '{% if count(' . $new_marker . ') == 0 %}',
                '{% endif %}'
            ];
            $template = preg_replace($patterns, $replacements, $template);
        }
        return $template;
    }

    /**
     * Replaces all old datafield markers with new ones.
     *
     * @param object $template Array with template and datafield_ids.
     * @return array|string|string[]
     */
    private function replaceDatafieldMarkers($template)
    {
        if (isset($template->genericdatafields)) {
            $search = [];
            $replace = [];
            $key = 1;
            foreach ($template->genericdatafields as $df_id) {
                $search[]  = 'DATAFIELD_' . $key++;
                $replace[] = 'DATAFIELD_' . $df_id;
            }
            return str_replace($search, $replace, $template->template);
        }
        return $template->template;
    }

    private function convertSemesters($old_semester_config)
    {
        $semester_mapping = [
            'previous' => 'previous',
            'current'  => 'current',
            'next'     => 'next'
        ];
        $key = 1;
        foreach (Semester::findAllVisible(false) as $sem) {
            $semester_mapping[$key++] = $sem['semester_id'];
        }
        return $semester_mapping[$old_semester_config] ?? '';
    }

    private function insertConfig($field, $message, $description)
    {
        DBManager::get()->exec(
            "INSERT IGNORE INTO `config`
            (`field`, `value`, `type`, `range`,
            `section`,
            `mkdate`, `chdate`,
            `description`)
            VALUES
            ({$field}, '{$message}', 'string', 'global',
            'external_pages', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
            '{$description}')"
        );
    }
}
