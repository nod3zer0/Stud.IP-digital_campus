<?php
/**
 * Modulteil.php
 * Model class for Modulteile (table mvv_modulteil)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 *
 * @property string $id alias column for modulteil_id
 * @property string $modulteil_id database column
 * @property string $modul_id database column
 * @property int $position database column
 * @property string|null $flexnow_modul database column
 * @property string|null $nummer database column
 * @property string|null $num_bezeichnung database column
 * @property string|null $lernlehrform database column
 * @property string|null $semester database column
 * @property string|null $kapazitaet database column
 * @property float|null $kp database column
 * @property int|null $sws database column
 * @property int|null $wl_praesenz database column
 * @property int|null $wl_bereitung database column
 * @property int|null $wl_selbst database column
 * @property int|null $wl_pruef database column
 * @property int|null $anteil_note database column
 * @property int $ausgleichbar database column
 * @property int $pflicht database column
 * @property string $author_id database column
 * @property string $editor_id database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property SimpleORMapCollection|ModulteilStgteilabschnitt[] $abschnitt_assignments has_many ModulteilStgteilabschnitt
 * @property SimpleORMapCollection|LvgruppeModulteil[] $lvgruppen_assignments has_many LvgruppeModulteil
 * @property SimpleORMapCollection|ModulteilLanguage[] $languages has_many ModulteilLanguage
 * @property Modul $modul belongs_to Modul
 * @property ModulteilDeskriptor $deskriptoren has_one ModulteilDeskriptor
 * @property SimpleORMapCollection|StgteilAbschnitt[] $abschnitte has_and_belongs_to_many StgteilAbschnitt
 * @property SimpleORMapCollection|Lvgruppe[] $lvgruppen has_and_belongs_to_many Lvgruppe
 * @property mixed $count_lvgruppen additional field
 */

class Modulteil extends ModuleManagementModelTreeItem
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_modulteil';

        $config['belongs_to']['modul'] = [
            'class_name'  => Modul::class,
            'foreign_key' => 'modul_id',
            'assoc_func'  => 'findCached',
        ];
        $config['has_and_belongs_to_many']['abschnitte'] = [
            'class_name'     => StgteilAbschnitt::class,
            'thru_table'     => 'mvv_modulteil_stgteilabschnitt',
            'thru_key'       => 'modulteil_id',
            'thru_assoc_key' => 'abschnitt_id',
            'on_delete'      => 'delete',
            'on_store'       => 'store'
        ];
        $config['has_many']['abschnitt_assignments'] = [
            'class_name' => ModulteilStgteilabschnitt::class,
            'order_by'   => 'ORDER BY fachsemester,mkdate',
            'on_delete'  => 'delete',
            'on_store'   => 'store'
        ];
        $config['has_one']['deskriptoren'] = [
            'class_name'        => ModulteilDeskriptor::class,
            'assoc_foreign_key' => 'modulteil_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store'
        ];
        $config['has_and_belongs_to_many']['lvgruppen'] = [
            'class_name'     => Lvgruppe::class,
            'thru_table'     => 'mvv_lvgruppe_modulteil',
            'thru_key'       => 'modulteil_id',
            'thru_assoc_key' => 'lvgruppe_id',
            'order_by'       => 'ORDER BY position,mkdate',
            'on_delete'      => 'delete',
            'on_store'       => 'store'
        ];
        $config['has_many']['lvgruppen_assignments'] = [
            'class_name' => LvgruppeModulteil::class,
            'order_by'   => 'ORDER BY position,mkdate',
            'on_delete'  => 'delete',
            'on_store'   => 'store'
        ];
        $config['has_many']['languages'] = [
            'class_name'        => ModulteilLanguage::class,
            'assoc_foreign_key' => 'modulteil_id',
            'order_by'          => 'ORDER BY position,mkdate',
            'on_delete'         => 'delete',
            'on_store'          => 'store'
        ];

        $config['additional_fields']['count_lvgruppen']['get'] = function ($mt) {
            return $mt->count_lvgruppen;
        };
        $config['additional_fields']['count_lvgruppen']['set'] = false;

        parent::configure($config);
    }

    private $default_language;
    private $count_lvgruppen;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->object_real_name = _('Modulteil');
        $this->default_language = $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['default'];
    }

    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return _('Modulteil');
    }

    /**
     * Retrieves all Modulteile of the given Modul.
     *
     * @param string $modul_id The id of a Modul.
     * @return SimpleORMapCollection A collection of Modulteile.
     */
    public static function findByModul($modul_id)
    {
        return parent::getEnrichedByQuery('
                SELECT mmt.*,
                COUNT(lvgruppe_id) AS count_lvgruppen
                FROM mvv_modulteil AS mmt
                LEFT JOIN mvv_lvgruppe_modulteil USING(modulteil_id)
                WHERE mmt.modul_id = ?
                GROUP BY modulteil_id
                ORDER BY position, mmt.mkdate',
            [$modul_id]
        );
    }

    /**
     * Retrieves all Modulteile the given LV-Gruppe is assigned to.
     *
     * @param string $lvgruppe_id The id of a LV-Gruppe.
     * @return SimpleORMapCollection A collection of Modulteile.
     */
    public static function findByLvgruppe($lvgruppe_id)
    {
        return parent::getEnrichedByQuery('
                SELECT mmt.*
                FROM mvv_modulteil mmt
                    LEFT JOIN mvv_lvgruppe_modulteil mlm USING(modulteil_id)
                WHERE mlm.lvgruppe_id = ?
                ORDER BY position',
            [$lvgruppe_id]
        );
    }

    public function getDisplayName()
    {
        $deskriptor = $this->getDeskriptor(self::getLanguage());
        $template = Config::get()->MVV_TEMPLATE_NAME_MODULTEIL;
        if (trim($template)) {
            $placeholders = [
                'part_number',
                'part_number_label',
                'part_name',
                'teaching_method'
            ];
            $replacements = [
                $this->nummer,
                $GLOBALS['MVV_MODULTEIL']['NUM_BEZEICHNUNG']['values'][$this->num_bezeichnung]['name'] ?? '',
                trim($deskriptor->bezeichnung),
                $GLOBALS['MVV_MODULTEIL']['LERNLEHRFORM']['values'][$this->lernlehrform]['name'] ?? ''
            ];
            return self::formatDisplayName($template, $placeholders, $replacements);
        }
        $name = '';
        if ($this->num_bezeichnung) {
            $name .= $GLOBALS['MVV_MODULTEIL']['NUM_BEZEICHNUNG']['values'][$this->num_bezeichnung]['name'];
            $name .= !is_null($this->nummer) ? ' ' . $this->nummer : '';
            $name .= ': ';
        } else if ($this->nummer) {
            $name .= $this->nummer . ': ';
        }
        $name .= $GLOBALS['MVV_MODULTEIL']['LERNLEHRFORM']['values'][$this->lernlehrform]['name'] ?? '';
        if (strlen(trim($deskriptor->bezeichnung))) {
            $name .= $name == '' ? $deskriptor->bezeichnung
                    : ' (' . $deskriptor->bezeichnung . ')';
        }
        return trim($name);
    }

    /**
     * Returns the default language (of the descriptor) for this Modulteil
     *
     * @see mvv_config.php for defined languages.
     * @return string The key of the default language.
     */
    public function getDefaultLanguage()
    {
        return $this->default_language;
    }

    /**
     * Returns the Deskriptor in the given language. A Modul has always a
     * Deskriptor in the default language. If the given language is unknown, the
     * method returns the deskriptor in the default language.
     *
     * @param string $language The id of the language
     * @param bool If true returns always a new descriptor
     * @return object The Deskriptor.
     */
    public function getDeskriptor($language = null, $force_new = false) {
        if (!isset($GLOBALS['MVV_MODULTEIL_DESKRIPTOR']['SPRACHE']['values'][$language])) {
            $language = $this->default_language;
        }

        if (!$this->deskriptoren) {
            // the module is new and has no descriptor
            // return a new descriptor in the default language
            $deskriptor = new ModulteilDeskriptor();
            $deskriptor->setNewId();
            $deskriptor->modulteil_id = $this->getId();
            $this->deskriptoren = $deskriptor;
        }
        return $this->deskriptoren;
    }

    /**
     * Returns a copy of this object.
     * If $deep is true, copy the connection to the Lvgruppen also.
     *
     * @return \Modulteil
     */
    public function copy($deep = false, $with_assignments = false)
    {
        $copy = clone $this;
        $copy->setNew(true);
        $copy->setNewId();

        $copy->deskriptoren= clone $this->deskriptoren;
        $copy->deskriptoren->setNewId();
        $copy->deskriptoren->setNew(true);

        $languages = [];
        foreach ($this->languages as $language) {
            $cloned_language = clone $language;
            $cloned_language->setNew(true);
            $languages[] = $cloned_language;
        }
        $copy->languages = SimpleORMapCollection::createFromArray($languages);

        if ($deep) {
            $lvgruppen = [];
            foreach ($this->lvgruppen_assignments as $lvgruppe) {
                $cloned_lvgruppe = clone $lvgruppe;
                $cloned_lvgruppe->setNew(true);
                $lvgruppen[] = $cloned_lvgruppe;
            }
            $copy->lvgruppen_assignments = SimpleORMapCollection::createFromArray($lvgruppen);
            if ($with_assignments) {
                $abschnitt_assignments = [];
                foreach ($this->abschnitt_assignments as $abschnitt_assignment) {
                    $cloned_abschnitt_assignment = clone $abschnitt_assignment;
                    $cloned_abschnitt_assignment->setNew(true);
                    $abschnitt_assignments[] = $cloned_abschnitt_assignment;
                }
                $copy->abschnitt_assignments = SimpleORMapCollection::createFromArray($abschnitt_assignments);
            }
        }

        return $copy;
    }

    /**
     * @see MvvTreeItem::getTrailParentId()
     */
    public function getTrailParentId()
    {
        return $this->modul_id;
    }

    /**
     * @see MvvTreeItem::getTrailParent()
     */
    public function getTrailParent()
    {
        return Modul::findCached($this->getTrailParentId());
    }

    /**
     * @see MvvTreeItem::getChildren()
     */
    public function getChildren()
    {
        $_SESSION['MVV/Lvgruppe/trail_parent_id'] =  $this->getId();
        return Lvgruppe::findByModulteil($this->getId());
    }

    /**
     * @see MvvTreeItem::hasChildren()
     */
    public function hasChildren()
    {
        return count($this->getChildren()) > 0;
    }

    /**
     * @see MvvTreeItem::getParents()
     */
    public function getParents($mode = null)
    {
        return [$this->modul];
    }

    /**
     * Assignes languages of instruction to this part-module.
     *
     * @param type $languages An array of language keys defined in mvv_config.php.
     */
    public function assignLanguagesOfInstruction($languages)
    {
        $assigned_languages = [];
        $languages_flipped = array_flip($languages);
        foreach ($GLOBALS['MVV_MODULTEIL']['SPRACHE']['values'] as $key => $language) {
            if (isset($languages_flipped[$key])) {
                $language = ModulteilLanguage::find([$this->id, $key]);
                if (!$language) {
                    $language = new ModulteilLanguage();
                    $language->modulteil_id = $this->id;
                    $language->lang = $key;
                }
                $language->position = $languages_flipped[$key];
                $assigned_languages[] = $language;
            }
        }

        $this->languages = SimpleORMapCollection::createFromArray(
                $assigned_languages);
    }

    /**
     * Inherits the status of the parent module.
     *
     * @return string The status (see mvv_config.php)
     */
    public function getStatus()
    {
        if ($this->modul) {
            return $this->modul->getStatus();
        }
        if ($this->isNew()) {
            return $GLOBALS['MVV_MODUL']['STATUS']['default'];
        }
        return $GLOBALS['MVV_MODUL']['STATUS']['default'];
    }

    public function getResponsibleInstitutes()
    {
        $institutes = [];
        $modul_insts = ModulInst::findByModul($this->modul_id, 'hauptverantwortlich');
        foreach ($modul_insts as $modul_inst) {
            $institute = Institute::find($modul_inst->institut_id);
            if ($institute) {
                $institutes[] = $institute;
            }
        }
        return $institutes;
    }

    /**
     * Retrieves all courses this Modulteil is assigned by its LV-Gruppen.
     * Filtered by a given semester considering the global visibility or the
     * the visibility for a given user.
     *
     * @param string $semester_id The id of a semester.
     * @param mixed $only_visible Boolean true retrieves only visible courses, false
     * retrieves all courses. If $only_visible is an user id it depends on the users
     * status which courses will be retrieved.
     * @return array An array of course data.
     */
    public function getAssignedCoursesBySemester($semester_id, $only_visible = true)
    {
        $courses = [];
        foreach ($this->lvgruppen as $lvgruppe) {
            $lvg_courses = $lvgruppe->getAssignedCoursesBySemester($semester_id, $only_visible);
            foreach ($lvg_courses as $course) {
                $courses[$course['seminar_id']] = $course;
            }
        }
        return $courses;
    }

}
