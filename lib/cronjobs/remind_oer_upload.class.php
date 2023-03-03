<?php
/**
 * remind_oer_upload.class.php - Sends reminder emails for uploading files to OER Campus.
 *
 * @author Michaela Brückner <brueckner@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
 * @access public
 * @since  5.2
 */

require_once 'lib/classes/CronJob.class.php';

class RemindOerUpload extends CronJob
{

    public static function getName()
    {
        return _('An OER-Campus Upload erinnern');
    }

    public static function getDescription()
    {
        return _('Erinnert den Autor am Ende des Semesters an eine Datei, die in den OER-Campus hochgeladen werden soll.');
    }

    public function execute($last_result, $parameters = [])
    {
        // check the reminder date, which now is in past
        $query = "SELECT `file_ref_id` FROM `oer_post_upload`
                    WHERE `reminder_date` < UNIX_TIMESTAMP()";
        $results = DBManager::get()->fetchAll($query);

        // get file information from file_ref_id
        foreach ($results as $result) {
            $file_ref = FileRef::find($result['file_ref_id']);

            if (!FileRef::countBySql('id = ?', [$result['file_ref_id']])) {
                // file might be deleted meanwhile, so do not try to send a reminder for it
            } else {
                $filetype = $file_ref->getFileType();
                $file_to_suggest = $filetype->convertToStandardFile();

                $author = $file_ref->owner->username;
                $link_to_share = URLHelper::getURL('dispatch.php/file/share_oer/' . $result['file_ref_id']);
                $linktext = _('Klicken Sie hier, um das Material im OER-Campus zu veröffentlichen.');
                $formatted_link = '['. $linktext .']' . $link_to_share;

                $oer_reminder_message = sprintf(_("Sie wollten daran erinnert werden, die folgende Datei im OER-Campus zu veröffentlichen:\n\n"
                    . "Dateiname: %s \n"
                    . "Beschreibung: %s \n"
                    . "%s \n\n"),
                    $file_to_suggest->getFilename(),
                    $file_to_suggest->getDescription(),
                    $formatted_link
                );

                $messaging = new messaging();

                $messaging->insert_message(
                    $oer_reminder_message,
                    $author,
                    '____%system%____',
                    '',
                    Request::option('message_id'),
                    '',
                    null,
                    _('Erinnerung zur Veröffentlichung einer Datei im OER-Campus')
                );

                OERPostUpload::deleteBySQL("file_ref_id = ?", [$result['file_ref_id']]);
            }
        }
    }
}
