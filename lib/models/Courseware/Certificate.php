<?php

namespace Courseware;

use \User, \Course, \CoursewarePDFCertificate;

/**
 * Courseware's certificates.
 *
 * @author  Thomas Hackl <hackl@data-quest.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.3
 *
 * @property string $id database column
 * @property string $user_id database column
 * @property string $course_id database column
 * @property int $unit_id database column
 * @property int $mkdate database column
 * @property \User $user belongs_to \User
 * @property \Course $course belongs_to \Course
 */
class Certificate extends \SimpleORMap
{
    /**
     * Generates a PDF certificate for
     * @param Courseeware\Unit $unit
     * @param User|null $user
     * @param int $timestamp timestamp that shall be used as certificate date
     * @param string|null $image optional background image fileref ID
     * @return string Full path to the generated PDF file
     */
    public static function createPDF(Unit $unit, int $timestamp, ?\User $user = null, $image = null)
    {
        if ($user === null) {
            $user = new User();
            $user->vorname = 'Vorname';
            $user->nachname = 'Nachname';
            $user->geschlecht = 3;
        }

        $template = $GLOBALS['template_factory']->open('courseware/mails/certificate');
        $html = $template->render(
            compact('user', 'unit', 'timestamp')
        );

        // Generate the PDF.
        $pdf = new CoursewarePDFCertificate($image ?? false);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf_file_name = ($user->isNew() ? 'Vorschau' : $user->nachname) . '_' . $unit->course->name . '_' .
            _('Zertifikat') . '.pdf';
        $filename = $GLOBALS['TMP_PATH'] . '/' . \FileManager::cleanFileName($pdf_file_name);
        $pdf->Output($filename, 'F');

        return $filename;
    }

    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_certificates';

        $config['belongs_to']['user'] = [
            'class_name' => User::class,
            'foreign_key' => 'user_id',
        ];

        $config['belongs_to']['course'] = [
            'class_name' => Course::class,
            'foreign_key' => 'course_id',
        ];

        parent::configure($config);
    }
}
