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
         * Fetch all units that have some relevant settings.
         */
        $todo = Courseware\Unit::findBySQL(
            "`range_type` = 'course' AND (`config` LIKE (:cert) OR `config` LIKE (:reminder) OR `config` LIKE (:reset))",
            ['cert' => '%"certificate":%', 'reminder' => '%"reminder":%', 'reset' => '%"reset_progress":%']
        );

        if (count($todo) > 0) {

            if ($verbose) {
                printf("Found %u units to process.\n", count($todo));
            }

            $timezone = Config::get()->DEFAULT_TIMEZONE;

            // Process all found entries...
            foreach ($todo as $unit) {

                // Fetch all courseware block IDs belonging to the current unit.
                $instance = new Courseware\Instance($unit->structural_element);
                $blocks = array_column($instance->findAllBlocks(), 'id');

                // Send certificates to those who have progressed far enough and have not yet gotten one.
                if (isset($unit->config['certificate'])) {
                    if ($verbose) {
                        printf("Generating certificates for course %s, unit %u.\n",
                            $unit->range_id, $unit->id);
                    }

                    // Fetch accumulated progress values for all users in this course.
                    $progresses = DBManager::get()->fetchAll(
                        "SELECT DISTINCT p.`user_id`, SUM(p.`grade`) AS progress
                        FROM `cw_user_progresses` p
                        WHERE `block_id` IN (:blocks)
                            AND NOT EXISTS (
                                SELECT `id` FROM `cw_certificates` WHERE `user_id` = p.`user_id` AND `unit_id` = :unit
                            )
                        GROUP BY `user_id`",
                        ['blocks' => $blocks, 'unit' => $unit->id]
                    );

                    // Calculate percentual progress and send certificates if necessary.
                    foreach ($progresses as $progress) {
                        $percent = ($progress['progress'] / count($blocks)) * 100;
                        printf("User %s has progress %u.\n", $progress['user_id'], $percent);
                        if ($percent >= $unit->config['certificate']['threshold']) {
                            if ($verbose) {
                                printf("User %s will get a certificate for course %s and unit %u.\n",
                                    $progress['user_id'], $unit->range_id, $unit->id);
                            }

                            if (!$this->sendCertificate($unit, $progress['user_id'], $percent,
                                $unit->config['certificate']['image'])) {
                                printf("Could not send certificate for course %s and unit %u to user %s.\n",
                                    $unit->range_id, $unit->id, $progress['user_id']);
                            }
                        }
                    }
                }

                // Send reminder messages to participants if necessary.
                if (isset($unit->config['reminder'])) {
                    // Check when the last reminder was sent...
                    $now = new DateTime('', new DateTimeZone($timezone));

                    // What would be the minimum date for the last reminder?
                    $minReminder = clone $now;

                    // The last reminder has been sent at?
                    $lastReminder = new DateTime('', new DateTimeZone($timezone));
                    $lastReminder->setTimestamp($unit->config['last_reminder'] ?? 0);

                    // Check if the settings specify a start and/or end date for reminders
                    $start = new DateTime($unit->config['reminder']['startDate'] ?? '1970-01-01',
                        new DateTimeZone($timezone));
                    $end = new DateTime($unit->config['reminder']['endDate'] ?? '2199-12-31',
                        new DateTimeZone($timezone));

                    $interval = new DateInterval('P1D');
                    switch ($unit->config['reminder']['interval']) {
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
                            printf("Sending reminders for course %s and unit %u.\n",
                                $unit->range_id, $unit->id);
                        }

                        if ($this->sendReminders($unit)) {
                            $unit->config['last_reminder'] = time();
                        }
                    }
                }

                // Reset progress if necessary.
                if (isset($unit->config['reset_progress'])) {
                    // Check when the last rest took place...
                    $now = new DateTime('', new DateTimeZone($timezone));

                    // What would be the minimum date for the last reset?
                    $minReset = clone $now;

                    // The last reset was done at:
                    $lastReset = new DateTime('', new DateTimeZone($timezone));
                    $lastReset->setTimestamp($unit->config['last_progress_reset'] ?? 0);

                    // Check if the settings specify a start and/or end date for reminders
                    $start = new DateTime($unit->config['reset_progress']['startDate'] ?? '1970-01-01',
                        new DateTimeZone($timezone));
                    $end = new DateTime($unit->config['reset_progress']['endDate'] ?? '2199-12-31',
                        new DateTimeZone($timezone));

                    $interval = new DateInterval('P1D');
                    switch ($unit->config['reset_progress']['interval']) {
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
                    $minReset->sub($interval);

                    // ... and send a new one if necessary.
                    if ($lastReset <= $minReset && $now >= $start && $now <= $end) {
                        if ($verbose) {
                            printf("Resetting progress for course %s and unit %u.\n",
                                $unit->range_id, $unit->id);
                        }

                        if ($this->resetProgress($unit, $blocks, $unit->config['reset_progress']['mailText'])) {
                            $unit->config['last_progress_reset'] = time();
                        }
                    }
                }

                // Store config back, saving timestamps for reminders and progress reset.
                $unit->store();
            }

        } else if ($verbose) {
            echo "Nothing to do.\n";
        }
    }

    private function sendCertificate($unit, $user_id, $progress, $image = '')
    {
        $user = User::find($user_id);
        $course = Course::find($unit->range_id);

        $template = $GLOBALS['template_factory']->open('courseware/mails/certificate');
        $html = $template->render(
            compact('user', 'course')
        );

        // Generate the PDF.
        $pdf = new CoursewarePDFCertificate($image);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf_file_name = $user->nachname . '_' . $course->name . '_' . _('Zertifikat') . '.pdf';
        $filename = $GLOBALS['TMP_PATH'] . '/' . $pdf_file_name;
        $pdf->Output($filename, 'F');

        // Send the mail with PDF attached.
        $mail = new StudipMail();

        $message = sprintf(
            _('Anbei erhalten Sie Ihr Courseware-Zertifikat zur Veranstaltung %1$s, in der Sie einen Fortschritt ' .
                'von %2$u %% im Lernmaterial "%s" erreicht haben.'),
            $course->getFullname(), $progress, $unit->structural_element->title);
        $message .= "\n\n" . _('Über folgenden Link kommen Sie direkt zur Courseware') . ': ' .
            URLHelper::getURL('dispatch.php/course/courseware/courseware/' . $unit->id, ['cid' => $course->id]);

        $sent = $mail->addRecipient($user->email, $user->getFullname())
            ->setSubject(_('Courseware: Zertifikat') . ' - ' . $course->getFullname())
            ->setBodyText($message)
            ->addFileAttachment($filename, $pdf_file_name)
            ->send();

        @unlink($filename);

        // Add database entry for the certificate.
        if ($sent) {
            $cert = new Courseware\Certificate();
            $cert->user_id = $user_id;
            $cert->course_id = $course->id;
            $cert->unit_id = $unit->id;
            return $cert->store();
        } else {
            return false;
        }
    }

    private function sendReminders($unit)
    {
        $course = Course::find($unit->range_id);

        $recipients = $course->getMembersWithStatus('autor', true);

        $mail = new StudipMail();

        foreach ($recipients as $rec) {
            $mail->addRecipient(
                $rec->email,
                $rec->getUserFullname(),
                'bcc'
            );
        }

        $message = $unit->config['reminder']['mailText'] . "\n\n" . _('Über folgenden Link kommen Sie direkt zur Courseware') . ': ' .
            URLHelper::getURL('dispatch.php/course/courseware/courseware/' . $unit->id, ['cid' => $course->id]);

        $mail->setSubject(_('Courseware: Erinnerung') . ' - ' . $course->getFullname() .
                ', ' . $unit->structural_element->title)
            ->setBodyText($message);

        return $mail->send();
    }

    private function resetProgress($unit, $block_ids)
    {
        $course = Course::find($unit->range_id);

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

        $message = $unit->config['reset_progress']['mailText'] . "\n\n" .
            _('Über folgenden Link kommen Sie direkt zur Courseware') . ': ' .
            URLHelper::getURL('dispatch.php/course/courseware/courseware/' . $unit->id, ['cid' => $course->id]);

        $mail->setSubject(_('Courseware: Fortschritt zurückgesetzt') . ' - ' . $course->getFullname())
            ->setBodyText($message);

        return $mail->send();
    }
}
