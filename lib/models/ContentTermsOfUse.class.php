<?php
/**
 * ContentTermsOfUse.class.php
 * model class for table licenses
 *
 * The ContentTermsOfUse class provides information about the terms under which
 * a content object in Stud.IP can be used. Each entry in the database table
 * content_terms_of_use_entries corresponds to one terms of use variant.
 *
 * Content can be a file or another Stud.IP object that is capable
 * of storing copyrighted material.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2016 data-quest
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string $id database column
 * @property I18NString $name database column
 * @property int $position database column
 * @property I18NString $description database column
 * @property I18NString $student_description database column
 * @property int $download_condition database column
 * @property string $icon database column
 * @property int $is_default database column
 * @property int $mkdate database column
 * @property int $chdate database column
 */

class ContentTermsOfUse extends SimpleORMap
{
    const DOWNLOAD_CONDITION_NONE = 0; // no conditions (downloadable by anyone)
    const DOWNLOAD_CONDITION_CLOSED_GROUPS = 1; // closed groups (e.g. courses with signup rules)
    const DOWNLOAD_CONDITION_OWNER_ONLY = 2; // only for owner

    /**
     * @var
     */
    private static $cache = null;

    /**
     * @param array $config
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'content_terms_of_use_entries';

        $config['i18n_fields']['name'] = true;
        $config['i18n_fields']['description'] = true;
        $config['i18n_fields']['student_description'] = true;

        $config['default_values']['download_condition'] = self::DOWNLOAD_CONDITION_NONE;
        $config['default_values']['icon'] = 'license';
        $config['default_values']['position'] = 0;
        $config['default_values']['is_default'] = false;

        $config['registered_callbacks']['after_store'][] = 'cbCheckDefault';

        parent::configure($config);
    }

    /**
     * @return ContentTermsOfUse[]
     */
    public static function findAll()
    {
        if (self::$cache === null) {
            self::$cache = new SimpleCollection(self::findBySQL('1 ORDER by position, id'));
        }
        return self::$cache;
    }

    /**
     * @param $id string
     * @return ContentTermsOfUse
     */
    public static function find($id)
    {
        return self::findAll()->findOneBy('id', $id);
    }

    /**
     * @param $id string
     * @return ContentTermsOfUse
     */
    public static function findOrBuild($id)
    {
        return self::find($id) ?: self::build(['id' => 'UNDEFINED', 'name' => 'unbekannt']);
    }

    /**
     * @return ContentTermsOfUse
     */
    public static function findDefault()
    {
        return self::findAll()->findOneBy('is_default', 1);
    }

    /**
     * Returns a list of all valid conditions.
     *
     * @return array
     */
    public static function getConditions()
    {
        return [
            self::DOWNLOAD_CONDITION_NONE => _('Ohne Bedingung'),
            self::DOWNLOAD_CONDITION_CLOSED_GROUPS => _('Nur innerhalb geschlossener Veranstaltungen erlaubt'),
            self::DOWNLOAD_CONDITION_OWNER_ONLY => _('Nur für EigentümerIn erlaubt'),
        ];
    }

    /**
     * Returns the textual representation of a condition.
     *
     * @param int $condition
     * @return string
     */
    public static function describeCondition($condition)
    {
        $conditions = self::getConditions();
        return $conditions[$condition] ?? _('Nicht definiert');
    }

    /**
     *
     */
    public function cbCheckDefault()
    {
        if ($this->is_default) {
            $query = "UPDATE `content_terms_of_use_entries`
                      SET `is_default` = 0
                      WHERE id != :id";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':id', $this->id);
            $statement->execute();
        }
        self::$cache = null;
    }

    /**
     * Validates this entry
     *
     * @return array with error messages, if it's empty everyhting is fine
     */
    public function validate()
    {
        $errors = [];
        if ($this->isNew() && self::exists($this->id)) {
            $errors[] = sprintf(
                _('Es existiert bereits ein Eintrag mit der ID %s!'),
                $this->id
            );
        }
        if (!$this->name) {
            $errors[] = _('Es wurde kein Name für den Eintrag gesetzt!');
        }
        return $errors;
    }

    /**
     * Determines if a user is permitted to download a file.
     *
     * Depening on the value of the download_condition attribute a decision
     * is made regarding the permission of the given user to download
     * a file, given by one of its associated FileRef objects.
     *
     * The folder condition can have the values 0, 1 and 2.
     * - 0 means that there are no conditions for downloading, therefore the
     *   file is downloadable by anyone.
     * - 1 means that the file is only downloadable inside a closed group.
     *   Such a group can be a course or study group with closed admission.
     *   In this case this method checks if the user is a member of the
     *   course or study group.
     * - 2 means that the file is only downloadable for the owner.
     *   The user's ID must therefore match the user_id attribute
     *   of the FileRef object.
     */
    public function isDownloadable($context_id, $context_type, $allow_owner = true, $user_id = null)
    {
        $user_id = $user_id ?: $GLOBALS['user']->id;
        if ($allow_owner) {
            if (in_array($context_type, ['course', 'institute'])
                && Seminar_Perm::get()->have_studip_perm(
                    'tutor', $context_id, $user_id
                )
            ) {
                return true;
            } elseif ($context_type === "profile" && $context_id === $user_id) {
                return true;
            }
        }
        if ($this->download_condition == self::DOWNLOAD_CONDITION_CLOSED_GROUPS) {

            //the content is only downloadable when the user is inside a closed group
            //(referenced by range_id). If download_condition is set to 2
            //the group must also have a terminated signup deadline.
            if ($context_type === "course") {
                //check where this range_id comes from:
                $seminar = Seminar::GetInstance($context_id);
                $timed_admission = $seminar->getAdmissionTimeFrame();

                if ($seminar->admission_prelim
                    || $seminar->isPasswordProtected()
                    || $seminar->isAdmissionLocked()
                    || (is_array($timed_admission) && $timed_admission['end_time'] > 0 && $timed_admission['end_time'] < time())
                ) {
                    return true;
                }
            }
            return false;
        }

        if ($this->download_condition == self::DOWNLOAD_CONDITION_OWNER_ONLY) {
            return false;
        }

        return true;
    }
}
