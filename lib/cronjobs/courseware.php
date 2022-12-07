<?php
/**
 * courseware.php
 *
 * @author Thomas Hackl <hackl@data-quest.de>
 * @access public
 * @since  5.3
 */

class CoursewareCronjob extends CronJob
{
    public static function getName()
    {
        return _('Courseware-Erinnerungen und -zertifikate verschicken sowie Fortschritt zurücksetzen');
    }

    public static function getDescription()
    {
        return _('Versendet Erinnerungen, Zertifikate bei Erreichen eines bestimmten Fortschritts und setzt ' .
            'Fortschritt bei derartig konfigurierten Coursewares zurück.');
    }

    public static function getParameters()
    {
        return [
            'verbose' => [
                'type'        => 'boolean',
                'default'     => false,
                'status'      => 'optional',
                'description' => _('Sollen Ausgaben erzeugt werden (sind später im Log des Cronjobs sichtbar)'),
            ]
        ];
    }

    public function setUp()
    {
    }

    public function execute($last_result, $parameters = [])
    {
        $verbose = $parameters['verbose'];

        /*
         * Fetch all courses that have some relevant settings.
         */
        $todo = DBManager::get()->fetchAll(
            "SELECT c.`range_id`, c. `field`, c.`value`
            FROM `config_values` c
                JOIN `seminare` s ON (s.`Seminar_id` = c.`range_id`)
            WHERE c.`field` IN (:fields)",
            ['fields' => [
                // Send certificate when this progress is reached
                'COURSEWARE_CERTIFICATE_SETTINGS',
                // Remind all users about courseware
                'COURSEWARE_REMINDER_SETTINGS',
                // Reset user progress to 0
                'COURSEWARE_RESET_PROGRESS_SETTINGS'
            ]
            ]
        );

        if (count($todo) > 0) {

            if ($verbose) {
                echo sprintf("Found %u courses to process.\n", count($todo));
            }

            $timezone = Config::get()->DEFAULT_TIMEZONE;

            // Process all found entries...
            foreach ($todo as $one) {

                // Fetch all courseware blocks belonging to the current course.
                $blocks = DBManager::get()->fetchFirst(
                    "SELECT DISTINCT b.`id`
                            FROM `cw_blocks` b
                                JOIN `cw_containers` c ON (c.`id` = b.`container_id`)
                                JOIN `cw_structural_elements` e ON (e.`id` = c.`structural_element_id`)
                            WHERE e.`range_id` = :course",
                    ['course' => $one['range_id']]
                );

                // extract details from JSON
                $settings = json_decode($one['value'], true);

                // differentiate by setting type
                switch ($one['field']) {
                    // Send certificates to those who have progressed far enough and have not yet gotten one.
                    case 'COURSEWARE_CERTIFICATE_SETTINGS':

                        if ($verbose) {
                            echo sprintf("Generating certificates for course %s.\n",
                                $one['range_id']);
                        }

                        // Fetch accumulated progress values for all users in this course.
                        $progresses = DBManager::get()->fetchAll(
                            "SELECT DISTINCT p.`user_id`, SUM(p.`grade`) AS progress
                            FROM `cw_user_progresses` p
                            WHERE `block_id` IN (:blocks)
                                AND NOT EXISTS (
                                    SELECT `id` FROM `cw_certificates` WHERE `user_id` = p.`user_id` AND `course_id` = :course
                                )
                            GROUP BY `user_id`",
                            ['blocks' => $blocks, 'course' => $one['range_id']]
                        );

                        // Calculate percentual progress and send certificates if necessary.
                        foreach ($progresses as $progress) {
                            $percent = ($progress['progress'] / count($blocks)) * 100;
                            if ($percent >= $settings['threshold']) {
                                if ($verbose) {
                                    echo sprintf("User %s will get a certificate for course %s.\n",
                                        $progress['user_id'], $one['range_id']);
                                }

                                $this->sendCertificate($one['range_id'], $progress['user_id'],
                                    $percent, $settings);

                                /*
                                 * Insert a new entry into database for tracking who already got a certificate.
                                 * This can be useful if certificates get a validity time or such.
                                 */
                                $entry = new Courseware\Certificate();
                                $entry->user_id = $progress['user_id'];
                                $entry->course_id = $one['range_id'];
                                $entry->store();
                            }
                        }

                        break;

                    // Send reminders to all course participants.
                    case 'COURSEWARE_REMINDER_SETTINGS':

                        // Check when the last reminder was sent...
                        $now = new DateTime('', new DateTimeZone($timezone));

                        // What would be the minimum date for the last reminder?
                        $minReminder = clone $now;

                        // The last reminder has been sent at?
                        $lastReminder = new DateTime('', new DateTimeZone($timezone));
                        $lastReminder->setTimestamp(
                            UserConfig::get($one['range_id'])->COURSEWARE_LAST_REMINDER ?: 0
                        );

                        // Check if the settings specify a start and/or end date for reminders
                        $start = new DateTime($settings['startDate'] ?: '1970-01-01',
                            new DateTimeZone($timezone));
                        $end = new DateTime($settings['endDate'] ?: '2199-12-31',
                            new DateTimeZone($timezone));

                        $interval = new DateInterval('P1D');
                        switch ($settings['interval']) {
                            case 7:
                                $interval = new DateInterval('P7D');
                                break;
                            case 14:
                                $interval = new DateInterval('P14D');
                                break;
                            case 30:
                                $interval = new DateInterval('P1M');
                                break;
                            case 90:
                                $interval = new DateInterval('P3M');
                                break;
                            case 180:
                                $interval = new DateInterval('P6M');
                                break;
                            case 365:
                                $interval = new DateInterval('P1Y');
                                break;
                        }
                        $minReminder->sub($interval);

                        // ... and send a new one if necessary.
                        if ($lastReminder <= $minReminder && $now >= $start && $now <= $end) {
                            if ($verbose) {
                                echo sprintf("Sending reminders for course %s.\n",
                                    $one['range_id']);
                            }

                            if ($this->sendReminders($one['range_id'], $settings)) {
                                UserConfig::get($one['range_id'])->store('COURSEWARE_LAST_REMINDER',
                                    $now->getTimestamp()
                                );
                            }
                        }

                        break;

                    // Reset courseware progress to 0 for all course participants.
                    case 'COURSEWARE_RESET_PROGRESS_SETTINGS':

                        // Check when the last reset was performed...
                        $now = new DateTime('', new DateTimeZone($timezone));
                        $checkLast = clone $now;
                        $lastReset = new DateTime('', new DateTimeZone($timezone));
                        $lastReset->setTimestamp(
                            UserConfig::get($one['range_id'])->COURSEWARE_LAST_PROGRESS_RESET ?: 0
                        );

                        $interval = new DateInterval('P1D');
                        switch ($one['value']) {
                            case 14:
                                $interval = new DateInterval('P14D');
                                break;
                            case 30:
                                $interval = new DateInterval('P1M');
                                break;
                            case 90:
                                $interval = new DateInterval('P3M');
                                break;
                            case 180:
                                $interval = new DateInterval('P6M');
                                break;
                            case 365:
                                $interval = new DateInterval('P1Y');
                                break;
                        }

                        // ... and reset again if necessary.
                        if ($lastReset <= $checkLast->sub($interval)) {
                            if ($verbose) {
                                echo sprintf("Resetting all progress for courseware in course %s.\n",
                                    $one['range_id']);
                            }

                            // Remove all progress in the given blocks.
                            $this->resetProgress($one['range_id'], $blocks, $settings);

                            UserConfig::get($one['range_id'])->store('COURSEWARE_LAST_PROGRESS_RESET',
                                $now->getTimestamp()
                            );
                        }
                }
            }

        } else if ($verbose) {
            echo "Nothing to do.\n";
        }
    }

    private function sendCertificate($course_id, $user_id, $progress, $settings)
    {
        $user = User::find($user_id);
        $course = Course::find($course_id);

        $template = $GLOBALS['template_factory']->open('courseware/mails/certificate');
        $html = $template->render(
            compact('user', 'course')
        );

        // Generate the PDF.
        $pdf = new CoursewarePDFCertificate($settings['image']);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf_file_name = $user->nachname . '_' . $course->name . '_' . _('Zertifikat') . '.pdf';
        $filename = $GLOBALS['TMP_PATH'] . '/' . $pdf_file_name;
        $pdf->Output($filename, 'F');

        // Send the mail with PDF attached.
        $mail = new StudipMail();

        $message = sprintf(
            _('Anbei erhalten Sie Ihr Courseware-Zertifikat zur Veranstaltung %1$s, in der Sie einen Fortschritt ' .
                'von %2$u %% erreicht haben.'), $course->getFullname(), $progress);
        $message .= "\n\n" . _('Über folgenden Link kommen Sie direkt zur Courseware') . ': ' .
            URLHelper::getURL('seminar_main.php', ['auswahl' => $course->id,
                'redirect_to' => 'dispatch.php/course/courseware']);

        $mail->addRecipient($user->email, $user->getFullname())
            ->setSubject(_('Courseware: Zertifikat') . ' - ' . $course->getFullname())
            ->setBodyText($message)
            ->addFileAttachment($filename, $pdf_file_name)
            ->send();

        @unlink($filename);

        // Add database entry for the certificate.

    }

    private function sendReminders($course_id, $settings)
    {
        $course = Course::find($course_id);

        $recipients = $course->getMembersWithStatus('autor', true);

        $mail = new StudipMail();

        foreach ($recipients as $rec) {
            $mail->addRecipient(
                $rec->email,
                $rec->getUserFullname(),
                'bcc'
            );
        }

        $message = $settings['mailText'] . "\n\n" . _('Über folgenden Link kommen Sie direkt zur Courseware') . ': ' .
            URLHelper::getURL('seminar_main.php', ['auswahl' => $course->id,
                'redirect_to' => 'dispatch.php/course/courseware']);

        $mail->setSubject(_('Courseware: Erinnerung') . ' - ' . $course->getFullname())
            ->setBodyText($message);

        return $mail->send();
    }

    private function resetProgress($course_id, $block_ids, $settings)
    {
        $course = Course::find($course_id);

        DBManager::get()->execute(
            "DELETE FROM `cw_user_progresses` WHERE `block_id` IN (:blocks)",
            ['blocks' => $block_ids]
        );

        $recipients = $course->getMembersWithStatus('autor', true);

        $mail = new StudipMail();

        foreach ($recipients as $rec) {
            $mail->addRecipient(
                $rec->email,
                $rec->getUserFullname(),
                'bcc'
            );
        }

        $message = $settings['mailText'] . "\n\n" . _('Über folgenden Link kommen Sie direkt zur Courseware') . ': ' .
            URLHelper::getURL('seminar_main.php', ['auswahl' => $course->id,
                'redirect_to' => 'dispatch.php/course/courseware']);

        $mail->setSubject(_('Courseware: Fortschritt zurückgesetzt') . ' - ' . $course->getFullname())
            ->setBodyText($message);

        return $mail->send();
    }
}
