--
-- Daten für Tabelle `abschluss`
--

REPLACE INTO `abschluss` (`abschluss_id`, `name`, `name_kurz`, `beschreibung`, `author_id`, `editor_id`, `mkdate`, `chdate`) VALUES('228234544820cdf75db55b42d1ea3ecc', 'Bachelor', NULL, '', '', '', 1311416359, 1311416359);
REPLACE INTO `abschluss` (`abschluss_id`, `name`, `name_kurz`, `beschreibung`, `author_id`, `editor_id`, `mkdate`, `chdate`) VALUES('c7f569e815a35cf24a515a0e67928072', 'Master', NULL, '', '', '', 1311416385, 1311416385);

--
-- Daten für Tabelle `auth_user_md5`
--

REPLACE INTO `auth_user_md5` (`user_id`, `username`, `password`, `perms`, `Vorname`, `Nachname`, `Email`, `validation_key`, `auth_plugin`, `locked`, `lock_comment`, `locked_by`, `visible`) VALUES('76ed43ef286fb55cf9e41beadb484a9f', 'root@studip', '$2a$08$SRoCYxAhWPFVF8V8CO15TOyzr.PpLRfVD9lVWVrmmBw4brkRTE/2G', 'root', 'Root', 'Studip', 'root@localhost', '', 'standard', 0, NULL, NULL, 'unknown');
REPLACE INTO `auth_user_md5` (`user_id`, `username`, `password`, `perms`, `Vorname`, `Nachname`, `Email`, `validation_key`, `auth_plugin`, `locked`, `lock_comment`, `locked_by`, `visible`) VALUES('205f3efb7997a0fc9755da2b535038da', 'test_dozent', '$2a$08$ajIvgEjd17MiiDcFr6msc.xldknH/tTGajUXVhDxDKNJVX0H0iv0i', 'dozent', 'Testaccount', 'Dozent', 'dozent@studip.de', '', 'standard', 0, NULL, NULL, 'unknown');
REPLACE INTO `auth_user_md5` (`user_id`, `username`, `password`, `perms`, `Vorname`, `Nachname`, `Email`, `validation_key`, `auth_plugin`, `locked`, `lock_comment`, `locked_by`, `visible`) VALUES('6235c46eb9e962866ebdceece739ace5', 'test_admin', '$2a$08$svvSma20vIxIR4J5gc0jIu31gws1WibmiQ/HDhCTukFA5GqhscY1G', 'admin', 'Testaccount', 'Admin', 'admin@studip.de', '', 'standard', 0, NULL, NULL, 'unknown');
REPLACE INTO `auth_user_md5` (`user_id`, `username`, `password`, `perms`, `Vorname`, `Nachname`, `Email`, `validation_key`, `auth_plugin`, `locked`, `lock_comment`, `locked_by`, `visible`) VALUES('7e81ec247c151c02ffd479511e24cc03', 'test_tutor', '$2a$08$mGhBl85TPsiItumZ4xjbgOnQ1vqIhLAC9giCfWcFzpkE1jqe4lmby', 'tutor', 'Testaccount', 'Tutor', 'tutor@studip.de', '', 'standard', 0, NULL, NULL, 'unknown');
REPLACE INTO `auth_user_md5` (`user_id`, `username`, `password`, `perms`, `Vorname`, `Nachname`, `Email`, `validation_key`, `auth_plugin`, `locked`, `lock_comment`, `locked_by`, `visible`) VALUES('e7a0a84b161f3e8c09b4a0a2e8a58147', 'test_autor', '$2a$08$xvbrvPhkcsvkzPZsNh.kceLw2IIwiNJ.1jGOwY3.H/dR2f8PG5X3O', 'autor', 'Testaccount', 'Autor', 'autor@studip.de', '', 'standard', 0, NULL, NULL, 'unknown');

--
-- Daten für Tabelle `config_values`
--

REPLACE INTO `config_values` (`field`, `range_id`, `value`, `mkdate`, `chdate`, `comment`) VALUES('STUDYGROUPS_ENABLE', 'studip', '1', 1268739461, 1268739461, 'Studiengruppen');
REPLACE INTO `config_values` (`field`, `range_id`, `value`, `mkdate`, `chdate`, `comment`) VALUES('STUDYGROUP_DEFAULT_INST', 'studip', 'ec2e364b28357106c0f8c282733dbe56', 1268739461, 1268739461, '');
REPLACE INTO `config_values` (`field`, `range_id`, `value`, `mkdate`, `chdate`, `comment`) VALUES('STUDYGROUP_TERMS', 'studip', 'Mir ist bekannt, dass ich die Gruppe nicht zu rechtswidrigen Zwecken nutzen darf. Dazu zählen u.a. Urheberrechtsverletzungen, Beleidigungen und andere Persönlichkeitsdelikte.\r\n\r\nIch erkläre mich damit einverstanden, dass Administratorinnen und Administratoren die Inhalte der Gruppe zu Kontrollzwecken einsehen dürfen.', 1268739461, 1268739461, '');

--
-- Daten für Tabelle `datafields`
--

REPLACE INTO `datafields` (`datafield_id`, `name`, `object_type`, `object_class`, `edit_perms`, `view_perms`, `priority`, `mkdate`, `chdate`, `type`, `typeparam`, `is_required`, `default_value`, `is_userfilter`, `description`, `system`) VALUES('ce73a10d07b3bb13c0132d363549efda', 'Matrikelnummer', 'user', '7', 'user', 'dozent', 0, NULL, NULL, 'textline', '', 0, NULL, 0, '', 0);

REPLACE INTO `datafields_entries` (`datafield_id`, `range_id`, `content`, `mkdate`, `chdate`, `sec_range_id`, `lang`) VALUES('ce73a10d07b3bb13c0132d363549efda', 'e7a0a84b161f3e8c09b4a0a2e8a58147', '1234567', 1530292487, 1530292487, '', '');



--
-- Dumping data for table `fach`
--

REPLACE INTO `fach` (`fach_id`, `name`, `name_kurz`, `beschreibung`, `schlagworte`, `author_id`, `editor_id`, `mkdate`, `chdate`) VALUES('6b9ac09535885ca55e29dd011e377c0a', 'Geschichte', NULL, '', NULL, '', '', 1311416418, 1311416418);
REPLACE INTO `fach` (`fach_id`, `name`, `name_kurz`, `beschreibung`, `schlagworte`, `author_id`, `editor_id`, `mkdate`, `chdate`) VALUES('f981c9b42ca72788a09da4a45794a737', 'Informatik', NULL, '', NULL, '', '', 1311416397, 1311416397);

--
-- Dumping data for table `files`
--

REPLACE INTO `files` (`id`, `user_id`, `mime_type`, `name`, `filetype`, `size`, `metadata`, `author_name`, `mkdate`, `chdate`) VALUES('b86665948274be968d78777830990eb5', '76ed43ef286fb55cf9e41beadb484a9f', '0', 'mappe_studip-el.pdf', 'URLFile', 314146, '{\"url\":\"https:\\/\\/www.studip.de\\/download\\/mappe_studip-el.pdf\",\"access_type\":\"redirect\"}', 'Root Studip', 1607705144, 1607705144);

--
-- Dumping data for table `file_refs`
--

REPLACE INTO `file_refs` (`id`, `file_id`, `folder_id`, `downloads`, `description`, `content_terms_of_use_id`, `user_id`, `name`, `mkdate`, `chdate`) VALUES('f62768f0cbd0bcdc4912ce5e4f84eb14', 'b86665948274be968d78777830990eb5', 'f7fc5ae64d2c453daa9619a820a6467e', 1, '', 'FREE_LICENSE', '76ed43ef286fb55cf9e41beadb484a9f', 'mappe_studip-el.pdf', 1607705144, 1607705144);

--
-- Dumping data for table `folders`
--

REPLACE INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES('012cb91a42cf85e5fafdd84e54ed2ec3', '76ed43ef286fb55cf9e41beadb484a9f', 'f7fc5ae64d2c453daa9619a820a6467e', 'a07535cf2f8a72df33c12ddfa4b53dde', 'course', 'CourseDateFolder', '15. Klausur am Do., 21.02.2019, 12:00 - 14:00', '{\"termin_id\":\"9ff59e18112a686c553412761a5df85c\",\"permission\":5}', '', 1543859055, 1543859055);
REPLACE INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES('3cc9006789bceef5d3ea7bed680790b4', '76ed43ef286fb55cf9e41beadb484a9f', '', '110ce78ffefaf1e5f167cd7019b728bf', 'institute', 'RootFolder', 'externe Einrichtung B', '', '', 1510849315, 1510849315);
REPLACE INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES('694cdcef09c2b8e70a7313b028e36fb6', '', '3cc9006789bceef5d3ea7bed680790b4', '110ce78ffefaf1e5f167cd7019b728bf', 'institute', 'StandardFolder', 'Allgemeiner Dateiordner', '', 'Ablage für allgemeine Ordner und Dokumente der Einrichtung', 1156516698, 1156516698);
REPLACE INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES('76b822dcc7f1458ae6e144c3c0fb544e', '76ed43ef286fb55cf9e41beadb484a9f', '', 'ec2e364b28357106c0f8c282733dbe56', 'institute', 'RootFolder', 'externe Bildungseinrichtungen', '', '', 1510849315, 1510849315);
REPLACE INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES('9082368f7e01b24af15178d0d954f4dc', '76ed43ef286fb55cf9e41beadb484a9f', '', '7a4f19a0a2c321ab2b8f7b798881af7c', 'institute', 'RootFolder', 'externe Einrichtung A', '', '', 1510849315, 1510849315);
REPLACE INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES('ad8dc6a6162fb0fe022af4a62a15e309', '76ed43ef286fb55cf9e41beadb484a9f', 'f7fc5ae64d2c453daa9619a820a6467e', 'a07535cf2f8a72df33c12ddfa4b53dde', 'course', 'HomeworkFolder', 'Hausaufgaben', '{\"permission\":\"3\"}', '', 1343924873, 1343924877);
REPLACE INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES('b58081c411c76814bc8f78425fb2ab81', '', '9082368f7e01b24af15178d0d954f4dc', '7a4f19a0a2c321ab2b8f7b798881af7c', 'institute', 'StandardFolder', 'Allgemeiner Dateiordner', '', 'Ablage für allgemeine Ordner und Dokumente der Einrichtung', 1156516698, 1156516698);
REPLACE INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES('bc63814f56ec1bbbba731e07d0074b45', '76ed43ef286fb55cf9e41beadb484a9f', '', '76ed43ef286fb55cf9e41beadb484a9f', 'user', 'RootFolder', '', '[]', '', 1543858972, 1543858972);
REPLACE INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES('ca002fbae136b07e4df29e0136e3bd32', '76ed43ef286fb55cf9e41beadb484a9f', 'f7fc5ae64d2c453daa9619a820a6467e', 'a07535cf2f8a72df33c12ddfa4b53dde', 'course', 'StandardFolder', 'Allgemeiner Dateiordner', '', 'Ablage für allgemeine Ordner und Dokumente der Veranstaltung', 1343924407, 1343924894);
REPLACE INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES('da3c2c2b4ea4c9781dccbae6eade5721', '76ed43ef286fb55cf9e41beadb484a9f', '', '7cb72dab1bf896a0b55c6aa7a70a3a86', 'course', 'RootFolder', 'Test Studiengruppe', '', '', 1510849315, 1510849315);
REPLACE INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES('dad53cd0f0d9f36817c3c9c7c124bda3', '', '76b822dcc7f1458ae6e144c3c0fb544e', 'ec2e364b28357106c0f8c282733dbe56', 'institute', 'StandardFolder', 'Allgemeiner Dateiordner', '', 'Ablage für allgemeine Ordner und Dokumente der Einrichtung', 1156516698, 1156516698);
REPLACE INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES('df122112a21812ff4ffcf1965cb48fc3', '76ed43ef286fb55cf9e41beadb484a9f', 'f7fc5ae64d2c453daa9619a820a6467e', 'a07535cf2f8a72df33c12ddfa4b53dde', 'course', 'CourseGroupFolder', 'Dateiordner der Gruppe: Studierende', '{\"group\":\"2f597139a049a768dbf8345a0a0af3de\"}', 'Ablage für Ordner und Dokumente dieser Gruppe', 1343924860, 1343924860);
REPLACE INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES('f7fc5ae64d2c453daa9619a820a6467e', '76ed43ef286fb55cf9e41beadb484a9f', '', 'a07535cf2f8a72df33c12ddfa4b53dde', 'course', 'RootFolder', 'Test Lehrveranstaltung', '', '', 1510849315, 1510849315);

--
-- Daten für Tabelle `Institute`
--

REPLACE INTO `Institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `mkdate`, `chdate`, `lit_plugin_name`, `srienabled`, `lock_rule`) VALUES('110ce78ffefaf1e5f167cd7019b728bf', 'externe Einrichtung B', 'ec2e364b28357106c0f8c282733dbe56', '', '', '', '', '', '', 1, 1156516698, 1156516698, 'Studip', 0, '');
REPLACE INTO `Institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `mkdate`, `chdate`, `lit_plugin_name`, `srienabled`, `lock_rule`) VALUES('1535795b0d6ddecac6813f5f6ac47ef2', 'Test Fakultät', '1535795b0d6ddecac6813f5f6ac47ef2', 'Geismar Landstr. 17b', '37083 Göttingen', 'http://www.studip.de', '0551 / 381 985 0', 'testfakultaet@studip.de', '0551 / 381 985 3', 1, 1156516698, 1156516698, 'Studip', 0, '');
REPLACE INTO `Institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `mkdate`, `chdate`, `lit_plugin_name`, `srienabled`, `lock_rule`) VALUES('2560f7c7674942a7dce8eeb238e15d93', 'Test Einrichtung', '1535795b0d6ddecac6813f5f6ac47ef2', '', '', '', '', '', '', 1, 1156516698, 1156516698, 'Studip', 0, '');
REPLACE INTO `Institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `mkdate`, `chdate`, `lit_plugin_name`, `srienabled`, `lock_rule`) VALUES('536249daa596905f433e1f73578019db', 'Test Lehrstuhl', '1535795b0d6ddecac6813f5f6ac47ef2', '', '', '', '', '', '', 3, 1156516698, 1156516698, 'Studip', 0, '');
REPLACE INTO `Institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `mkdate`, `chdate`, `lit_plugin_name`, `srienabled`, `lock_rule`) VALUES('7a4f19a0a2c321ab2b8f7b798881af7c', 'externe Einrichtung A', 'ec2e364b28357106c0f8c282733dbe56', '', '', '', '', '', '', 1, 1156516698, 1156516698, 'Studip', 0, '');
REPLACE INTO `Institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `mkdate`, `chdate`, `lit_plugin_name`, `srienabled`, `lock_rule`) VALUES('ec2e364b28357106c0f8c282733dbe56', 'externe Bildungseinrichtungen', 'ec2e364b28357106c0f8c282733dbe56', '', '', '', '', '', '', 1, 1156516698, 1156516698, 'Studip', 0, '');
REPLACE INTO `Institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `mkdate`, `chdate`, `lit_plugin_name`, `srienabled`, `lock_rule`) VALUES('f02e2b17bc0e99fc885da6ac4c2532dc', 'Test Abteilung', '1535795b0d6ddecac6813f5f6ac47ef2', '', '', '', '', '', '', 4, 1156516698, 1156516698, 'Studip', 0, '');

--
-- Daten für Tabelle `news_rss_range`
--

REPLACE INTO `news_rss_range` (`range_id`, `rss_id`, `range_type`) VALUES('studip', '70cefd1e80398bb20ff599636546cdff', 'global');

--
-- Daten für Tabelle `tools_activated`
--

REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('110ce78ffefaf1e5f167cd7019b728bf', 'institute', 14, 0, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('110ce78ffefaf1e5f167cd7019b728bf', 'institute', 15, 1, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('110ce78ffefaf1e5f167cd7019b728bf', 'institute', 25, 2, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('1535795b0d6ddecac6813f5f6ac47ef2', 'institute', 14, 0, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('1535795b0d6ddecac6813f5f6ac47ef2', 'institute', 15, 1, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('1535795b0d6ddecac6813f5f6ac47ef2', 'institute', 25, 2, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('2560f7c7674942a7dce8eeb238e15d93', 'institute', 14, 0, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('2560f7c7674942a7dce8eeb238e15d93', 'institute', 15, 1, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('2560f7c7674942a7dce8eeb238e15d93', 'institute', 25, 2, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('536249daa596905f433e1f73578019db', 'institute', 14, 0, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('536249daa596905f433e1f73578019db', 'institute', 15, 1, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('536249daa596905f433e1f73578019db', 'institute', 25, 2, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('7a4f19a0a2c321ab2b8f7b798881af7c', 'institute', 14, 0, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('7a4f19a0a2c321ab2b8f7b798881af7c', 'institute', 15, 1, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('7a4f19a0a2c321ab2b8f7b798881af7c', 'institute', 25, 2, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('7cb72dab1bf896a0b55c6aa7a70a3a86', 'course', 1, 2, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('7cb72dab1bf896a0b55c6aa7a70a3a86', 'course', 14, 0, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('7cb72dab1bf896a0b55c6aa7a70a3a86', 'course', 16, 1, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('7cb72dab1bf896a0b55c6aa7a70a3a86', 'course', 17, 3, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('7cb72dab1bf896a0b55c6aa7a70a3a86', 'course', 19, 4, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('7cb72dab1bf896a0b55c6aa7a70a3a86', 'course', 22, 5, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', 'course', 1, 3, '[]', 1640880515, 1640885052);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', 'course', 2, 4, '[]', 1640880515, 1640885052);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', 'course', 13, 10, '[]', 1640885044, 1640885052);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', 'course', 14, 0, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', 'course', 15, 1, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', 'course', 17, 2, '[]', 1640880515, 1640885052);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', 'course', 18, 7, '[]', 1640880515, 1640885063);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', 'course', 20, 8, '[]', 1640880515, 1640885063);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', 'course', 21, 9, '[]', 1640880515, 1640885052);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', 'course', 22, 6, '[]', 1640880515, 1640885061);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', 'course', 26, 5, '[]', 1640885037, 1640885052);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('ec2e364b28357106c0f8c282733dbe56', 'institute', 14, 0, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('ec2e364b28357106c0f8c282733dbe56', 'institute', 15, 1, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('ec2e364b28357106c0f8c282733dbe56', 'institute', 25, 2, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('f02e2b17bc0e99fc885da6ac4c2532dc', 'institute', 14, 0, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('f02e2b17bc0e99fc885da6ac4c2532dc', 'institute', 15, 1, NULL, 1640880515, 1640880515);
REPLACE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES('f02e2b17bc0e99fc885da6ac4c2532dc', 'institute', 25, 2, NULL, 1640880515, 1640880515);


--
-- Daten für Tabelle `range_tree`
--

REPLACE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES('3f93863e3d37ba0df286a6e7e26974ef', 'root', 0, 0, 'Einrichtungen der Universität', '', '');
REPLACE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES('1323254564871354786157481484621', '3f93863e3d37ba0df286a6e7e26974ef', 1, 0, '', 'inst', '1535795b0d6ddecac6813f5f6ac47ef2');
REPLACE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES('ce6c87bbf759b4cfd6f92d0c5560da5c', '1323254564871354786157481484621', 0, 0, 'Test Einrichtung', 'inst', '2560f7c7674942a7dce8eeb238e15d93');
REPLACE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES('2f4f90ac9d8d832cc8c8a95910fde4eb', '1323254564871354786157481484621', 0, 1, 'Test Lehrstuhl', 'inst', '536249daa596905f433e1f73578019db');
REPLACE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES('5d032f70c255f3e57cf8aa85a429ad4e', '1323254564871354786157481484621', 0, 2, 'Test Abteilung', 'inst', 'f02e2b17bc0e99fc885da6ac4c2532dc');
REPLACE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES('a3d977a66f0010fa8e15c27dd71aff63', 'root', 0, 1, 'externe Bildungseinrichtungen', 'fak', 'ec2e364b28357106c0f8c282733dbe56');
REPLACE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES('e0ff0ead6a8c5191078ed787cd7c0c1f', 'a3d977a66f0010fa8e15c27dd71aff63', 0, 0, 'externe Einrichtung A', 'inst', '7a4f19a0a2c321ab2b8f7b798881af7c');
REPLACE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES('105b70b72dc1908ce2925e057c4a8daa', 'a3d977a66f0010fa8e15c27dd71aff63', 0, 1, 'externe Einrichtung B', 'inst', '110ce78ffefaf1e5f167cd7019b728bf');

--
-- Daten für Tabelle `scm`
--

REPLACE INTO `scm` (`scm_id`, `range_id`, `user_id`, `tab_name`, `content`, `mkdate`, `chdate`, `position`) VALUES('a07df31918cc8e5ca0597e959a4a5297', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', 'Informationen', '', 1343924407, 1343924407, 0);

--
-- Dumping data for table `semester_courses`
--

REPLACE INTO `semester_courses` (`semester_id`, `course_id`, `mkdate`, `chdate`) VALUES('4967f0a483e36554b77e3dc47aa58941', 'a07535cf2f8a72df33c12ddfa4b53dde', 1641490271, 1641490271);

--
-- Dumping data for table `seminare`
--

REPLACE INTO `seminare` (`Seminar_id`, `VeranstaltungsNummer`, `Institut_id`, `Name`, `Untertitel`, `status`, `Beschreibung`, `Ort`, `Sonstiges`, `Lesezugriff`, `Schreibzugriff`, `start_time`, `duration_time`, `art`, `teilnehmer`, `vorrausetzungen`, `lernorga`, `leistungsnachweis`, `mkdate`, `chdate`, `ects`, `admission_turnout`, `admission_binding`, `admission_prelim`, `admission_prelim_txt`, `admission_disable_waitlist`, `visible`, `showscore`, `aux_lock_rule`, `aux_lock_rule_forced`, `lock_rule`, `admission_waitlist_max`, `admission_disable_waitlist_move`, `completion`, `parent_course`) VALUES('7cb72dab1bf896a0b55c6aa7a70a3a86', '', 'ec2e364b28357106c0f8c282733dbe56', 'Test Studiengruppe', '', 99, 'Studiengruppen sind eine einfache Möglichkeit, mit KommilitonInnen, KollegInnen und anderen zusammenzuarbeiten.', '', '', 1, 1, 1680300000, -1, '', '', '', '', '', 1268739824, 1607705186, '', 0, 0, 0, '', 0, 1, 0, NULL, 0, NULL, 0, 0, 0, NULL);
REPLACE INTO `seminare` (`Seminar_id`, `VeranstaltungsNummer`, `Institut_id`, `Name`, `Untertitel`, `status`, `Beschreibung`, `Ort`, `Sonstiges`, `Lesezugriff`, `Schreibzugriff`, `start_time`, `duration_time`, `art`, `teilnehmer`, `vorrausetzungen`, `lernorga`, `leistungsnachweis`, `mkdate`, `chdate`, `ects`, `admission_turnout`, `admission_binding`, `admission_prelim`, `admission_prelim_txt`, `admission_disable_waitlist`, `visible`, `showscore`, `aux_lock_rule`, `aux_lock_rule_forced`, `lock_rule`, `admission_waitlist_max`, `admission_disable_waitlist_move`, `completion`, `parent_course`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', '12345', '2560f7c7674942a7dce8eeb238e15d93', 'Test Lehrveranstaltung', 'eine normale Lehrveranstaltung', 1, '', '', '', 1, 1, 1696111200, 0, '', 'für alle Studierenden', 'abgeschlossenes Grundstudium', 'Referate in Gruppenarbeit', 'Klausur', 1343924407, 1641229951, '4', 0, 0, 0, '', 0, 1, 0, NULL, 0, NULL, 0, 0, 0, NULL);

--
-- Dumping data for table `seminar_cycle_dates`
--

REPLACE INTO `seminar_cycle_dates` (`metadate_id`, `seminar_id`, `start_time`, `end_time`, `weekday`, `description`, `sws`, `cycle`, `week_offset`, `end_offset`, `sorter`, `mkdate`, `chdate`) VALUES('fc3c44f257e448e3cd36a88406a8a9c1', 'a07535cf2f8a72df33c12ddfa4b53dde', '09:00:00', '11:00:00', 1, '', '0.0', 0, 0, 15, 0, 1530291739, 1698856934);

--
-- Daten für Tabelle `seminar_inst`
--

REPLACE INTO `seminar_inst` (`seminar_id`, `institut_id`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', '2560f7c7674942a7dce8eeb238e15d93');

--
-- Daten für Tabelle `seminar_sem_tree`
--

REPLACE INTO `seminar_sem_tree` (`seminar_id`, `sem_tree_id`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', '3d39528c1d560441fd4a8cb0b7717285');
REPLACE INTO `seminar_sem_tree` (`seminar_id`, `sem_tree_id`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', '5c41d2b4a5a8338e069dda987a624b74');
REPLACE INTO `seminar_sem_tree` (`seminar_id`, `sem_tree_id`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', 'dd7fff9151e85e7130cdb684edf0c370');

--
-- Daten für Tabelle `seminar_user`
--

REPLACE INTO `seminar_user` (`Seminar_id`, `user_id`, `status`, `position`, `gruppe`, `mkdate`, `comment`, `visible`, `label`, `bind_calendar`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', 'e7a0a84b161f3e8c09b4a0a2e8a58147', 'autor', 0, 5, 1343924589, '', 'unknown', '', 1);
REPLACE INTO `seminar_user` (`Seminar_id`, `user_id`, `status`, `position`, `gruppe`, `mkdate`, `comment`, `visible`, `label`, `bind_calendar`) VALUES('7cb72dab1bf896a0b55c6aa7a70a3a86', 'e7a0a84b161f3e8c09b4a0a2e8a58147', 'dozent', 0, 8, 0, '', 'unknown', '', 1);
REPLACE INTO `seminar_user` (`Seminar_id`, `user_id`, `status`, `position`, `gruppe`, `mkdate`, `comment`, `visible`, `label`, `bind_calendar`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', '205f3efb7997a0fc9755da2b535038da', 'dozent', 0, 5, 1343924407, '', 'yes', '', 1);
REPLACE INTO `seminar_user` (`Seminar_id`, `user_id`, `status`, `position`, `gruppe`, `mkdate`, `comment`, `visible`, `label`, `bind_calendar`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', '7e81ec247c151c02ffd479511e24cc03', 'tutor', 0, 5, 1343924407, '', 'yes', '', 1);

--
-- Daten für Tabelle `sem_tree`
--

REPLACE INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`, `type`) VALUES('5b73e28644a3e259a6e0bc1e1499773c', 'root', 1, '', 'Test Fakultät', NULL, 0);
REPLACE INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`, `type`) VALUES('439618ae57d8c10dcaabcf7e21bcc1d9', '5b73e28644a3e259a6e0bc1e1499773c', 0, '', 'Test Studienbereich A', NULL, 0);
REPLACE INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`, `type`) VALUES('5c41d2b4a5a8338e069dda987a624b74', '5b73e28644a3e259a6e0bc1e1499773c', 1, '', 'Test Studienbereich B', NULL, 0);
REPLACE INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`, `type`) VALUES('3d39528c1d560441fd4a8cb0b7717285', '439618ae57d8c10dcaabcf7e21bcc1d9', 0, '', 'Test Studienbereich A-1', NULL, 0);
REPLACE INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`, `type`) VALUES('dd7fff9151e85e7130cdb684edf0c370', '439618ae57d8c10dcaabcf7e21bcc1d9', 1, '', 'Test Studienbereich A-2', NULL, 0);
REPLACE INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`, `type`) VALUES('01c8b1d188be40c5ac64b54a01aae294', '5b73e28644a3e259a6e0bc1e1499773c', 2, '', 'Test Studienbereich C', NULL, 0);

--
-- Daten für Tabelle `statusgruppen`
--

REPLACE INTO `statusgruppen` (`statusgruppe_id`, `name`, `range_id`, `position`, `size`, `selfassign`, `mkdate`, `chdate`, `calendar_group`) VALUES('86498c641ccf4f4d4e02f4961ccc3829', 'Lehrbeauftragte', '2560f7c7674942a7dce8eeb238e15d93', 3, 0, 0, 1156516698, 1156516698, 0);
REPLACE INTO `statusgruppen` (`statusgruppe_id`, `name`, `range_id`, `position`, `size`, `selfassign`, `mkdate`, `chdate`, `calendar_group`) VALUES('600403561c21a50ae8b4d41655bd2191', 'Hochschullehrer/-in', '2560f7c7674942a7dce8eeb238e15d93', 4, 0, 0, 1156516698, 1156516698, 0);
REPLACE INTO `statusgruppen` (`statusgruppe_id`, `name`, `range_id`, `position`, `size`, `selfassign`, `mkdate`, `chdate`, `calendar_group`) VALUES('efb56e092f33cb78a8766676042dc1c5', 'wiss. Mitarbeiter/-in', '2560f7c7674942a7dce8eeb238e15d93', 2, 0, 0, 1156516698, 1156516698, 0);
REPLACE INTO `statusgruppen` (`statusgruppe_id`, `name`, `range_id`, `position`, `size`, `selfassign`, `mkdate`, `chdate`, `calendar_group`) VALUES('5d40b1fc0434e6589d7341a3ee742baf', 'Direktor/-in', '2560f7c7674942a7dce8eeb238e15d93', 1, 0, 0, 1156516698, 1156516698, 0);
REPLACE INTO `statusgruppen` (`statusgruppe_id`, `name`, `range_id`, `position`, `size`, `selfassign`, `mkdate`, `chdate`, `calendar_group`) VALUES('2f597139a049a768dbf8345a0a0af3de', 'Studierende', 'a07535cf2f8a72df33c12ddfa4b53dde', 1, 0, 0, 1343924562, 1343924562, 0);
REPLACE INTO `statusgruppen` (`statusgruppe_id`, `name`, `range_id`, `position`, `size`, `selfassign`, `mkdate`, `chdate`, `calendar_group`) VALUES('f4319d9909e9f7cb4692c16771887f22', 'Lehrende', 'a07535cf2f8a72df33c12ddfa4b53dde', 0, 0, 0, 1343924551, 1343924551, 0);

--
-- Daten für Tabelle `statusgruppe_user`
--

REPLACE INTO `statusgruppe_user` (`statusgruppe_id`, `user_id`, `position`, `visible`, `inherit`) VALUES('efb56e092f33cb78a8766676042dc1c5', '7e81ec247c151c02ffd479511e24cc03', 1, 1, 1);
REPLACE INTO `statusgruppe_user` (`statusgruppe_id`, `user_id`, `position`, `visible`, `inherit`) VALUES('5d40b1fc0434e6589d7341a3ee742baf', '205f3efb7997a0fc9755da2b535038da', 1, 1, 1);
REPLACE INTO `statusgruppe_user` (`statusgruppe_id`, `user_id`, `position`, `visible`, `inherit`) VALUES('f4319d9909e9f7cb4692c16771887f22', '205f3efb7997a0fc9755da2b535038da', 1, 1, 1);
REPLACE INTO `statusgruppe_user` (`statusgruppe_id`, `user_id`, `position`, `visible`, `inherit`) VALUES('f4319d9909e9f7cb4692c16771887f22', '7e81ec247c151c02ffd479511e24cc03', 2, 1, 1);
REPLACE INTO `statusgruppe_user` (`statusgruppe_id`, `user_id`, `position`, `visible`, `inherit`) VALUES('2f597139a049a768dbf8345a0a0af3de', 'e7a0a84b161f3e8c09b4a0a2e8a58147', 1, 1, 1);

--
-- Dumping data for table `termine`
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `raum`, `metadate_id`) VALUES('005be9fa51cae40cb7864420ef20cc21', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', 1700467200, 1700474400, 1698856934, 1698857418, 1, '', 'fc3c44f257e448e3cd36a88406a8a9c1');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `raum`, `metadate_id`) VALUES('04907423f4fecaf2326bde7d595e3fa6', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', 1706515200, 1706522400, 1698856934, 1698857418, 1, '', 'fc3c44f257e448e3cd36a88406a8a9c1');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `raum`, `metadate_id`) VALUES('0a32d2bb1d24c471fd62d2f542fee471', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', 1701676800, 1701684000, 1698856934, 1698857418, 1, '', 'fc3c44f257e448e3cd36a88406a8a9c1');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `raum`, `metadate_id`) VALUES('3b74ff2f7ce0964146a90b3d723ee594', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', 1702281600, 1702288800, 1698856934, 1698857418, 1, '', 'fc3c44f257e448e3cd36a88406a8a9c1');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `raum`, `metadate_id`) VALUES('68985894e2389e16c9922f28bc88447b', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', 1704700800, 1704708000, 1698856934, 1698857418, 1, '', 'fc3c44f257e448e3cd36a88406a8a9c1');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `raum`, `metadate_id`) VALUES('86de155d92a8f2da7ed6cd8ed9c08d71', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', 1707991200, 1707996600, 1698856983, 1698856983, 3, '', NULL);
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `raum`, `metadate_id`) VALUES('8c145ae92eef055db022e79df007af19', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', 1705305600, 1705312800, 1698856934, 1698857418, 1, '', 'fc3c44f257e448e3cd36a88406a8a9c1');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `raum`, `metadate_id`) VALUES('953ce88c783cbc921f82e41e4af7a6af', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', 1702886400, 1702893600, 1698856934, 1698857418, 1, '', 'fc3c44f257e448e3cd36a88406a8a9c1');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `raum`, `metadate_id`) VALUES('ab8e1dca6db2eb7dec5a2c99af8338ec', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', 1699257600, 1699264800, 1698856934, 1698857418, 1, '', 'fc3c44f257e448e3cd36a88406a8a9c1');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `raum`, `metadate_id`) VALUES('afde51cfbc49f0e5fcd8be6bd32cc1d1', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', 1698652800, 1698660000, 1698856934, 1698857418, 1, '', 'fc3c44f257e448e3cd36a88406a8a9c1');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `raum`, `metadate_id`) VALUES('ba543c308e144270c44406288393c041', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', 1699862400, 1699869600, 1698856934, 1698857418, 1, '', 'fc3c44f257e448e3cd36a88406a8a9c1');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `raum`, `metadate_id`) VALUES('bb87ee9eb1711bf15d84e3814c1cd4ce', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', 1707120000, 1707127200, 1698856934, 1698857418, 1, '', 'fc3c44f257e448e3cd36a88406a8a9c1');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `raum`, `metadate_id`) VALUES('dfaef63fdf5d7b7349190b5ae131463e', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', 1701072000, 1701079200, 1698856934, 1698857418, 1, '', 'fc3c44f257e448e3cd36a88406a8a9c1');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `raum`, `metadate_id`) VALUES('f08b3ef4fe781e7e32d3153832ca5e21', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', 1698044400, 1698051600, 1698856934, 1698857418, 1, '', 'fc3c44f257e448e3cd36a88406a8a9c1');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `raum`, `metadate_id`) VALUES('fe11571844e66b495fc12114b48ec161', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', 1705910400, 1705917600, 1698856934, 1698857418, 1, '', 'fc3c44f257e448e3cd36a88406a8a9c1');

--
-- Daten für Tabelle `ex_termine`
--

REPLACE INTO `ex_termine` (`termin_id`, `range_id`, `autor_id`, `content`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `raum`, `metadate_id`, `resource_id`) VALUES('3887db95c939fc701fddc2f906a87287', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', 1704096000, 1704103200, 1698856934, 1698856934, 1, NULL, 'fc3c44f257e448e3cd36a88406a8a9c1', '');
REPLACE INTO `ex_termine` (`termin_id`, `range_id`, `autor_id`, `content`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `raum`, `metadate_id`, `resource_id`) VALUES('d1f461421c4357b55bbfd6b16a6d1c81', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', 1703491200, 1703498400, 1698856934, 1698856934, 1, NULL, 'fc3c44f257e448e3cd36a88406a8a9c1', '');

--
-- Daten für Tabelle `user_info`
--
REPLACE INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `publi`, `schwerp`, `Home`, `privatnr`, `privatcell`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `title_front`, `title_rear`, `preferred_language`, `smsforward_copy`, `smsforward_rec`, `email_forward`, `motto`, `lock_rule`, `oercampus_description`) VALUES('205f3efb7997a0fc9755da2b535038da', '', '', '', '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 0, '', '', NULL);
REPLACE INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `publi`, `schwerp`, `Home`, `privatnr`, `privatcell`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `title_front`, `title_rear`, `preferred_language`, `smsforward_copy`, `smsforward_rec`, `email_forward`, `motto`, `lock_rule`, `oercampus_description`) VALUES('6235c46eb9e962866ebdceece739ace5', '', '', '', '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 0, '', '', NULL);
REPLACE INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `publi`, `schwerp`, `Home`, `privatnr`, `privatcell`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `title_front`, `title_rear`, `preferred_language`, `smsforward_copy`, `smsforward_rec`, `email_forward`, `motto`, `lock_rule`, `oercampus_description`) VALUES('76ed43ef286fb55cf9e41beadb484a9f', '', '', '', '', '', '', '', '', 0, 0, 0, 1698855190, '', '', NULL, 1, '', 0, '', '', NULL);
REPLACE INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `publi`, `schwerp`, `Home`, `privatnr`, `privatcell`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `title_front`, `title_rear`, `preferred_language`, `smsforward_copy`, `smsforward_rec`, `email_forward`, `motto`, `lock_rule`, `oercampus_description`) VALUES('7e81ec247c151c02ffd479511e24cc03', '', '', '', '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 0, '', '', NULL);
REPLACE INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `publi`, `schwerp`, `Home`, `privatnr`, `privatcell`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `title_front`, `title_rear`, `preferred_language`, `smsforward_copy`, `smsforward_rec`, `email_forward`, `motto`, `lock_rule`, `oercampus_description`) VALUES('e7a0a84b161f3e8c09b4a0a2e8a58147', '', '', '', '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 0, '', '', NULL);


--
-- Daten für Tabelle `user_inst`
--

REPLACE INTO `user_inst` (`user_id`, `Institut_id`, `inst_perms`, `sprechzeiten`, `raum`, `Telefon`, `Fax`, `externdefault`, `priority`, `visible`) VALUES('205f3efb7997a0fc9755da2b535038da', '2560f7c7674942a7dce8eeb238e15d93', 'dozent', '', '', '', '', 0, 0, 1);
REPLACE INTO `user_inst` (`user_id`, `Institut_id`, `inst_perms`, `sprechzeiten`, `raum`, `Telefon`, `Fax`, `externdefault`, `priority`, `visible`) VALUES('6235c46eb9e962866ebdceece739ace5', '2560f7c7674942a7dce8eeb238e15d93', 'admin', '', '', '', '', 0, 0, 1);
REPLACE INTO `user_inst` (`user_id`, `Institut_id`, `inst_perms`, `sprechzeiten`, `raum`, `Telefon`, `Fax`, `externdefault`, `priority`, `visible`) VALUES('7e81ec247c151c02ffd479511e24cc03', '2560f7c7674942a7dce8eeb238e15d93', 'tutor', '', '', '', '', 0, 0, 1);
REPLACE INTO `user_inst` (`user_id`, `Institut_id`, `inst_perms`, `sprechzeiten`, `raum`, `Telefon`, `Fax`, `externdefault`, `priority`, `visible`) VALUES('e7a0a84b161f3e8c09b4a0a2e8a58147', '2560f7c7674942a7dce8eeb238e15d93', 'user', '', '', '', '', 1, 0, 1);

--
-- Daten für Tabelle `user_studiengang`
--

REPLACE INTO `user_studiengang` (`user_id`, `fach_id`, `semester`, `abschluss_id`) VALUES('e7a0a84b161f3e8c09b4a0a2e8a58147', '6b9ac09535885ca55e29dd011e377c0a', 2, '228234544820cdf75db55b42d1ea3ecc');
REPLACE INTO `user_studiengang` (`user_id`, `fach_id`, `semester`, `abschluss_id`) VALUES('7e81ec247c151c02ffd479511e24cc03', 'f981c9b42ca72788a09da4a45794a737', 1, '228234544820cdf75db55b42d1ea3ecc');


REPLACE INTO `news` (`news_id`, `topic`, `body`, `author`, `date`, `user_id`, `expire`, `allow_comments`, `chdate`, `chdate_uid`, `mkdate`) VALUES ('29f2932ce32be989022c6f43b866e744', 'Herzlich Willkommen!', '<!--HTML-->
Das Stud.IP-Team heisst sie herzlich willkommen.<br />
Bitte schauen Sie sich ruhig um!<br /><br />
Wenn Sie das System selbst installiert haben und diese News sehen, haben Sie die Demonstrationsdaten in die Datenbank eingefügt. Wenn Sie produktiv mit dem System arbeiten wollen, sollten Sie diese Daten später wieder löschen, <strong>da die Passwörter der Accounts öffentlich bekannt sind</strong>.<br />
 
<p>Wenn Sie mit der Stud.IP Open Source-Community in Kontakt treten wollen und Fragen oder Ideen haben, können Sie sich auf unserem eigenen Stud.IP-System mit uns austauschen: <a href="https://develop.studip.de/studip" class="link-extern" target="_blank" rel="noreferrer noopener">develop.studip.de</a></p>

<p>Falls Sie die Einführung an einer Bildungseinrichtung planen und Unterstützung bei der Einführung oder Beratung zum Einsatz von digitalen Tools für die Online Lehre haben, steht Ihnen die Firma data-quest als Dienstleister rund um Stud.IP zur Seite: <a href="https://www.data-quest.de" class="link-extern" target="_blank" rel="noreferrer noopener">https://www.data-quest.de</a></p>', 'Root Studip', UNIX_TIMESTAMP(), '76ed43ef286fb55cf9e41beadb484a9f', 14562502, 1, UNIX_TIMESTAMP(), '', UNIX_TIMESTAMP());

REPLACE INTO news_range (`news_id`, `range_id`) VALUES ('29f2932ce32be989022c6f43b866e744', '76ed43ef286fb55cf9e41beadb484a9f');
REPLACE INTO news_range (`news_id`, `range_id`) VALUES ('29f2932ce32be989022c6f43b866e744', 'studip');
