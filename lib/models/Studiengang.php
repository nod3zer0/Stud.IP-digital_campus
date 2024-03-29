<?php
/**
 * Studiengang.php
 * Model class for Studiengaenge (table mvv_studiengang)
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
 * @property string $id alias column for studiengang_id
 * @property string $studiengang_id database column
 * @property string|null $abschluss_id database column
 * @property string $typ database column
 * @property I18NString $name database column
 * @property I18NString|null $name_kurz database column
 * @property I18NString|null $beschreibung database column
 * @property string|null $institut_id database column
 * @property string|null $start database column
 * @property string|null $end database column
 * @property int|null $beschlussdatum database column
 * @property int|null $fassung_nr database column
 * @property string|null $fassung_typ database column
 * @property string|null $stat database column
 * @property string|null $kommentar_status database column
 * @property string|null $schlagworte database column
 * @property int|null $studienzeit database column
 * @property int|null $studienplaetze database column
 * @property string|null $abschlussgrad database column
 * @property string|null $enroll database column
 * @property string $author_id database column
 * @property string $editor_id database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property SimpleORMapCollection|StudiengangStgteil[] $stgteil_assignments has_many StudiengangStgteil
 * @property SimpleORMapCollection|MvvFile[] $documents has_many MvvFile
 * @property SimpleORMapCollection|MvvFileRange[] $document_assignments has_many MvvFileRange
 * @property SimpleORMapCollection|MvvContactRange[] $contact_assignments has_many MvvContactRange
 * @property SimpleORMapCollection|StudycourseType[] $studycourse_types has_many StudycourseType
 * @property SimpleORMapCollection|StudycourseLanguage[] $languages has_many StudycourseLanguage
 * @property SimpleORMapCollection|DatafieldEntryModel[] $datafields has_many DatafieldEntryModel
 * @property SimpleORMapCollection|Aufbaustudiengang[] $grundstg_assignments has_many Aufbaustudiengang
 * @property SimpleORMapCollection|Aufbaustudiengang[] $aufbaustg_assignments has_many Aufbaustudiengang
 * @property Abschluss|null $abschluss belongs_to Abschluss
 * @property Fachbereich|null $responsible_institute has_one Fachbereich
 * @property SimpleORMapCollection|StudiengangTeil[] $studiengangteile has_and_belongs_to_many StudiengangTeil
 * @property SimpleORMapCollection|StgteilBezeichnung[] $stgteil_bezeichnungen has_and_belongs_to_many StgteilBezeichnung
 * @property-read mixed $count_dokumente additional field
 * @property-read mixed $count_faecher additional field
 * @property-read mixed $count_module additional field
 * @property-read mixed $institut_name additional field
 * @property-read mixed $kategorie_name additional field
 * @property-read mixed $display_name additional field
 */

class Studiengang extends ModuleManagementModelTreeItem
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_studiengang';

        $config['belongs_to']['abschluss'] = [
            'class_name'  => Abschluss::class,
            'foreign_key' => 'abschluss_id',
            'assoc_func'  => 'findCached',
        ];
        $config['has_and_belongs_to_many']['studiengangteile'] = [
            'class_name'     => StudiengangTeil::class,
            'thru_table'     => 'mvv_stg_stgteil',
            'thru_key'       => 'studiengang_id',
            'thru_assoc_key' => 'stgteil_id'
        ];
        $config['has_many']['stgteil_assignments'] = [
            'class_name'  => StudiengangStgteil::class,
            'foreign_key' => 'studiengang_id',
            'order_by'    => 'ORDER BY position',
            'on_delete'   => 'delete',
            'on_store'    => 'store'
        ];
        $config['has_and_belongs_to_many']['stgteil_bezeichnungen'] = [
            'class_name'     => StgteilBezeichnung::class,
            'thru_table'     => 'mvv_stg_stgteil',
            'thru_key'       => 'studiengang_id',
            'thru_assoc_key' => 'stgteil_bez_id',
            'order_by'       => 'GROUP BY stgteil_bez_id ORDER BY position'
        ];
        $config['has_many']['documents'] = [
            'class_name'             => MvvFile::class,
            'assoc_func'             => 'findbyrange_id',
            'assoc_func_params_func' => function ($stg) { return $stg; }
        ];
        $config['has_many']['document_assignments'] = [
            'class_name'        => MvvFileRange::class,
            'assoc_foreign_key' => 'range_id',
            'order_by'          => 'ORDER BY position',
            'on_delete'         => 'delete',
            'on_store'          => 'store'
        ];
        $config['has_many']['contact_assignments'] = [
            'class_name'        => MvvContactRange::class,
            'assoc_foreign_key' => 'range_id',
            'order_by'          => 'ORDER BY position'
        ];

        $config['has_one']['responsible_institute'] = [
            'class_name'  => Fachbereich::class,
            'foreign_key' => 'institut_id',
            'assoc_func'  => 'findCached',
        ];
        $config['has_many']['studycourse_types'] = [
            'class_name'  => StudycourseType::class,
            'foreign_key' => 'studiengang_id',
            'on_delete'   => 'delete',
            'on_store'    => 'store'
        ];
        $config['has_many']['languages'] = [
            'class_name'        => StudycourseLanguage::class,
            'assoc_foreign_key' => 'studiengang_id',
            'order_by'          => 'ORDER BY position,mkdate',
            'on_delete'         => 'delete',
            'on_store'          => 'store'
        ];
        $config['has_many']['datafields'] = [
            'class_name'        => DatafieldEntryModel::class,
            'assoc_foreign_key' =>
                function($model, $params) {
                    $model->setValue('range_id', $params[0]->id);
                },
            'assoc_func'        => 'findByModel',
            'on_delete'         => 'delete',
            'on_store'          => 'store',
            'foreign_key'       =>
                function($stg) {
                    return [$stg];
                }
        ];
        $config['has_many']['grundstg_assignments'] = [
            'class_name'        => Aufbaustudiengang::class,
            'assoc_func'        => 'findByaufbau_stg_id',
            'assoc_foreign_key' => 'aufbau_stg_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store'
        ];
        $config['has_many']['aufbaustg_assignments'] = [
            'class_name'        => Aufbaustudiengang::class,
            'assoc_func'        => 'findBygrund_stg_id',
            'assoc_foreign_key' => 'grund_stg_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store'
        ];

        $config['additional_fields']['count_dokumente']['get'] =
            function ($stg) { return $stg->count_dokumente; };
        $config['additional_fields']['count_faecher']['get'] =
            function ($stg) { return $stg->count_faecher; };
        $config['additional_fields']['count_module']['get'] =
            function ($stg) { return $stg->count_module; };
        $config['additional_fields']['institut_name']['get'] =
            function ($stg) { return $stg->institut_name; };
        $config['additional_fields']['kategorie_name']['get'] =
            function ($stg) { return $stg->kategorie_name; };
        $config['additional_fields']['display_name']['get'] =
            function ($stg) { return $stg->getDisplayName(); };

        $config['i18n_fields']['name']         = true;
        $config['i18n_fields']['name_kurz']    = true;
        $config['i18n_fields']['beschreibung'] = true;

        $config['default_values']['enroll'] = $GLOBALS['MVV_STUDIENGANG']['ENROLL']['default'];

        parent::configure($config);
    }

    private $count_dokumente;
    private $count_faecher;
    private $institut_name;
    private $kategorie_name;
    private $count_module;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->object_real_name = _('Studiengang');
    }

    /**
     * Retrieves all Studiengaenge by given Abschluss.
     *
     * @param string $abschluss_id The id of an Abschluss.
     * @return SimpleORMapCollection A collection of Studiengaenge.
     */
    public static function findByAbschluss($abschluss_id)
    {
        return parent::getEnrichedByQuery('
            SELECT ms.*
            FROM mvv_studiengang ms
            WHERE ms.abschluss_id = ?',
            [$abschluss_id]
        );
    }

    /**
     * Retrieves all Studiengaenge by a given combination of Fach/Abschluss.
     *
     * @param string $fach_id The id of a Fach.
     * @param string $abschluss_id The id of an Abschluss.
     * @param array $filter Key-value pairs of names and values to filter the result set.
     * @return SimpleORMapCollection A collection of Studiengaenge.
     */
    public static function findByFachAbschluss($fach_id, $abschluss_id, $filter = null)
    {
        return parent::getEnrichedByQuery('
            SELECT mvv_studiengang.*
            FROM mvv_studiengang
                LEFT JOIN mvv_stg_stgteil USING(studiengang_id)
                LEFT JOIN mvv_stgteil USING(stgteil_id)
            WHERE mvv_studiengang.abschluss_id = ? AND mvv_stgteil.fach_id = ?
            ' . self::getFilterSql($filter),
            [$abschluss_id, $fach_id]
        );
    }

    /**
     * Retrieves all Studiengaenge by given Fachbereich. The Fachbereich is an
     * institute assigned to the fach of a Studiengangteil which is assigned to
     * Studiengaenge.
     *
     * @param string $fachbereich_id The id of an institute.
     * @return SimpleORMapCollection A collection of Studiengaenge.
     */
    public static function findByFachbereich($fachbereich_id)
    {
        return parent::getEnrichedByQuery('
            SELECT ms.*,
                COUNT(mst.fach_id) as `count_faecher`,
                mak.name AS `kategorie_name`
            FROM mvv_studiengang AS ms
                LEFT JOIN mvv_abschl_zuord maz USING (abschluss_id)
                LEFT JOIN mvv_abschl_kategorie mak USING (kategorie_id)
                LEFT JOIN mvv_stg_stgteil mss USING (studiengang_id)
                LEFT JOIN mvv_stgteil mst USING (stgteil_id)
                INNER JOIN mvv_fach_inst mfi USING (fach_id)
            WHERE mfi.institut_id = ?
                GROUP BY studiengang_id
                ORDER BY name',
            [$fachbereich_id]
        );
    }

    /**
     * Retrieves all Studiengänge ba given Abschluss-Kategorie.
     *
     * @param string $kategorie_id The id of an Abschluss-Kategorie.
     * @return SimpleORMapCollection A collection of Studiengaenge.
     */
    public static function findByAbschlussKategorie($kategorie_id)
    {
        return parent::getEnrichedByQuery('
            SELECT ms.*,
                COUNT(mst.fach_id) AS `count_faecher`
            FROM mvv_studiengang AS ms
                LEFT JOIN mvv_abschl_zuord maz USING(abschluss_id)
                LEFT JOIN mvv_stg_stgteil USING(studiengang_id)
                LEFT JOIN mvv_stgteil mst USING(stgteil_id)
            WHERE maz.kategorie_id = ?
            GROUP BY studiengang_id
            ORDER BY name',
            [$kategorie_id]
        );
    }

    /**
     * Retrieves all Studiengange by a given combination of Abschluss-Kategorie
     * and Fachbereich.
     * The Fachbereich is an institute assigned to the fach of a Studiengangteil
     * which is assigned to Studiengaenge.
     *
     * @param string $kategorie_id The id of an Abschluss-Kategorie.
     * @param string $fachbereich_id The id of an institute.
     * @return SimpleORMapCollection A collection of Studiengaenge.
     */
    public static function findByAbschlussKategorieFachbereich($kategorie_id,
            $fachbereich_id)
    {
        return parent::getEnrichedByQuery('
            SELECT ms.*,
                COUNT(mfi.fach_id) AS `count_faecher`
            FROM mvv_studiengang AS ms
                LEFT JOIN mvv_abschl_zuord AS maz USING(abschluss_id)
                LEFT JOIN mvv_stg_stgteil USING(studiengang_id)
                LEFT JOIN mvv_stgteil USING(stgteil_id)
                INNER JOIN mvv_fach_inst mfi USING(fach_id)
            WHERE maz.kategorie_id = ? AND mfi.institut_id = ?
            GROUP BY studiengang_id
            ORDER BY name',
            [$kategorie_id, $fachbereich_id]
        );
    }

    /**
     * Retrieves all Studiengaenge the given Studiengangteil is assigned to.
     *
     * @param string $stgteil_id The id of a Studiengangteil.
     * @return SimpleORMapCollection A collection of Studiengangteile.
     */
    public static function findByStgTeil($stgteil_id)
    {
        return parent::getEnrichedByQuery('
            SELECT ms.*
            FROM mvv_studiengang ms
                LEFT JOIN mvv_stg_stgteil mss USING(studiengang_id)
            WHERE mss.stgteil_id = ? ',
            [$stgteil_id]
        );
    }

    /**
     * Retrieves all Studiengaenge the given Module are assigned to.
     * The assignment is done via Studiengangabschnitte, Studiengangteil-
     * Versionen and Studiengangteil.
     * Optionallay restricted to public visible Studiengaenge.
     *
     * @param array $modul_ids An array of Modul ids.
     * @param boolean $only_public If true retrieve only public visible ones.
     * @return SimpleORMapCollection A collection of Studiengaenge.
     */
    public static function findByModule($modul_ids, $only_public = true)
    {
        if ($only_public) {
            return parent::getEnrichedByQuery('
                SELECT ms.*,
                    COUNT(DISTINCT modul_id) AS count_module
                FROM mvv_stgteilabschnitt_modul AS msm
                    INNER JOIN mvv_stgteilabschnitt USING (abschnitt_id)
                    INNER JOIN mvv_stgteilversion msv USING (version_id)
                    INNER JOIN mvv_stg_stgteil USING (stgteil_id)
                    INNER JOIN mvv_studiengang ms USING (studiengang_id)
                WHERE msm.modul_id IN (?)
                    AND msv.stat IN (?)
                    AND ms.stat IN (?)
                GROUP BY studiengang_id
                ORDER BY count_module DESC',
                [
                    $modul_ids,
                    StgteilVersion::getPublicStatus(),
                    Studiengang::getPublicStatus()
                ]
            );
        } else {
            return parent::getEnrichedByQuery('
                SELECT ms.*, COUNT(DISTINCT modul_id) AS count_module
                FROM mvv_stgteilabschnitt_modul AS msm
                    INNER JOIN mvv_stgteilabschnitt USING (abschnitt_id)
                    INNER JOIN mvv_stgteilversion USING (version_id)
                    INNER JOIN mvv_stg_stgteil USING (stgteil_id)
                    INNER JOIN mvv_studiengang ms USING (studiengang_id)
                WHERE msm.modul_id IN (?)
                GROUP BY studiengang_id
                ORDER BY count_module DESC',
                    [$modul_ids]);
        }
    }

    /**
     * Returns an array with all studiengaenge filtered by Fachbereich and
     * Abschluss-Kategorie. The associated array contains only the name and
     * the id of the Studiengang with the id as key.
     * The content is utf8 encoded.
     *
     * @param string $fachbereich_id The id of the Fachbereich
     * @param string $kategorie_id The id of the Abschluss-Kategorie
     * @return array The array with studiengaenge. Empty if no Studiengang
     * was found.
     */
    public static function toArrayFachbereichAbschlussKategorie($fachbereich_id,
            $kategorie_id)
    {
        $studiengaenge = [];
        $query = '
            SELECT ms.studiengang_id, ms.name
            FROM mvv_studiengang ms
                LEFT JOIN mvv_abschl_zuord maz USING(abschluss_id)
                LEFT JOIN mvv_stg_stgteil USING(studiengang_id)
                LEFT JOIN mvv_stgteil USING(stgteil_id)
                INNER JOIN mvv_fach_inst mfi USING(fach_id)
            WHERE maz.kategorie_id = ?
                AND mfi.institut_id = ?
            GROUP BY studiengang_id
            ORDER BY name';
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute([$kategorie_id, $fachbereich_id]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $studiengang) {
            $studiengaenge[$studiengang['studiengang_id']] = $studiengang;
        }
        return $studiengaenge;
    }

    /**
     * Returns all or a specified (by row count and offset) number of
     * Studiengaenge sorted and filtered by given parameters and enriched with
     * some additional fields. This function is mainly used in the list view.
     *
     * @param string $sortby Field name to order by.
     * @param string $order ASC or DESC direction of order.
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @param int $row_count The max number of objects to return.
     * @param int $offset The first object to return in a result set.
     * @return SimpleORMapCollection A collection of Studiengaenge.
     */
    public static function getAllEnriched($sortby = 'name', $order = 'ASC',
            $filter = null, $row_count = null, $offset = null)
    {
        $sortby = self::createSortStatement($sortby, $order, 'name',
                words('abschluss_name kategorie_name count_faecher '
                        . 'count_stgteile count_dokumente institut_name'));
        return parent::getEnrichedByQuery("
            SELECT mvv_studiengang.*,
                abschluss.name AS `abschluss_name`,
                mvv_abschl_kategorie.name AS `kategorie_name`,
                mvv_abschl_kategorie.kategorie_id,
                Institute.Name AS institut_name,
                COUNT(mvv_stgteil.stgteil_id) AS `count_faecher`,
                COUNT(mvv_stg_stgteil.stgteil_bez_id) AS `count_stgteile`,
                COUNT(DISTINCT mvv_files_filerefs.fileref_id) AS count_dokumente,
                GROUP_CONCAT(mvv_fach_inst.institut_id) AS fachbereich_ids
            FROM mvv_studiengang
                LEFT JOIN abschluss USING (abschluss_id)
                LEFT JOIN mvv_abschl_zuord USING (abschluss_id)
                LEFT JOIN mvv_abschl_kategorie USING (kategorie_id)
                LEFT JOIN mvv_stg_stgteil USING (studiengang_id)
                LEFT JOIN mvv_stgteil USING (stgteil_id)
                LEFT JOIN mvv_fach_inst USING (fach_id)
                LEFT JOIN Institute ON (mvv_studiengang.institut_id = Institute.Institut_id)
                LEFT JOIN mvv_files_ranges ON (mvv_studiengang.studiengang_id = mvv_files_ranges.range_id)
                LEFT JOIN mvv_files ON (mvv_files_ranges.mvvfile_id = mvv_files.mvvfile_id)
                LEFT JOIN mvv_files_filerefs ON (mvv_files_filerefs.mvvfile_id = mvv_files.mvvfile_id)
                LEFT JOIN semester_data start_sem ON (mvv_studiengang.start = start_sem.semester_id)
                LEFT JOIN semester_data end_sem ON (mvv_studiengang.end = end_sem.semester_id)
            " . self::getFilterSql($filter, true) . "
            GROUP BY studiengang_id
            ORDER BY " . $sortby,
            [],
            $row_count,
            $offset
        );
    }

    /**
     * Returns the number of Studiengaenge optional filtered by $filter.
     *
     * @see ModuleManagementModel::getFilterSql()
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return int The number of Studiengaenge
     */
    public static function getCount($filter = null)
    {
        $query = '
            SELECT COUNT(DISTINCT(mvv_studiengang.studiengang_id))
            FROM mvv_studiengang
                LEFT JOIN abschluss USING (abschluss_id)
                LEFT JOIN mvv_abschl_zuord USING (abschluss_id)
                LEFT JOIN mvv_abschl_kategorie USING (kategorie_id)
                LEFT JOIN mvv_stg_stgteil USING (studiengang_id)
                LEFT JOIN mvv_stgteil USING (stgteil_id)
                LEFT JOIN mvv_fach_inst USING (fach_id)
                LEFT JOIN Institute ON (mvv_studiengang.institut_id = Institute.Institut_id)
                LEFT JOIN semester_data start_sem ON (mvv_studiengang.start = start_sem.semester_id)
                LEFT JOIN semester_data end_sem ON (mvv_studiengang.end = end_sem.semester_id)
            ' . self::getFilterSql($filter, true);
        $db = DBManager::get()->query($query);
        return $db->fetchColumn(0);
    }

    /**
     * Retrieves the Studiengang and all related data and
     * some additional fields.
     *
     * @param string $studiengang_id The id of the studiengang.
     * @return object The Studiengang with additional data or a new Studiengang.
     */
    public static function getEnriched($studiengang_id)
    {
        $studiengaenge = parent::getEnrichedByQuery('
            SELECT ms.*,
                a.name AS `abschluss_name`, mak.name AS `kategorie_name`,
                mak.kategorie_id, COUNT(mst.fach_id) AS `count_faecher`,
                COUNT(mss.stgteil_bez_id) AS `count_stgteile`
            FROM mvv_studiengang AS ms
                LEFT JOIN abschluss a USING (abschluss_id)
                LEFT JOIN mvv_abschl_zuord USING (abschluss_id)
                LEFT JOIN mvv_abschl_kategorie mak USING (kategorie_id)
                LEFT JOIN mvv_stg_stgteil mss USING (studiengang_id)
                LEFT JOIN mvv_stgteil mst USING (stgteil_id)
            WHERE ms.studiengang_id = ?
            GROUP BY studiengang_id',
            [$studiengang_id]
        );
        if (sizeof($studiengaenge)) {
            return $studiengaenge->find($studiengang_id);
        }
        return self::get();
    }

    public function getDisplayName()
    {
        $template = Config::get()->MVV_TEMPLATE_NAME_STUDIENGANG;
        $placeholders = [
            'study_course_name',
            'degree_name',
            'degree_category'
        ];
        $replacements = [
            $this->name,
            $this->abschluss->name,
            $this->abschluss->category->name
        ];
        return self::formatDisplayName($template, $placeholders, $replacements);
    }

    /**
     * Returns all institutes assigned to studiengaenge.
     *
     * @see ModuleManagementModel::getFilterSql()
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return SimpleORMapCollection A collection of institutes.
     */
    public static function getAllAssignedInstitutes($filter = null)
    {
        return Fachbereich::getEnrichedByQuery('
            SELECT DISTINCT Institute.*,
                Institute.Name AS `name`,
                Institute.Institut_id AS institut_id,
                IF(Institute.Institut_id = Institute.fakultaets_id, 1, 0)
                AS is_faculty, faculties.Name AS faculty_name,
                COUNT(studiengang_id) AS count_objects
            FROM Institute
                INNER JOIN mvv_studiengang
                ON Institute.Institut_id = mvv_studiengang.institut_id
                LEFT JOIN Institute AS faculties
                ON Institute.fakultaets_id = faculties.Institut_id
                '. Fachbereich::getFilterSql($filter, true) . '
            GROUP BY Institute.Institut_id
            ORDER BY name', []);
    }

    /**
     * @see ModuleManagementModel::findBySearchTerm()
     */
    public static function findBySearchTerm($term, $filter = null)
    {
        $quoted_term = DBManager::get()->quote('%' . $term . '%');
        return parent::getEnrichedByQuery('
            SELECT mvv_studiengang.*,
                abschluss.name as `abschluss_name`,
                mvv_abschl_kategorie.name as `kategorie_name`,
                COUNT(mvv_stgteil.fach_id) as `count_faecher`
            FROM mvv_studiengang
                LEFT JOIN abschluss USING (abschluss_id)
                LEFT JOIN mvv_abschl_zuord USING (abschluss_id)
                LEFT JOIN mvv_abschl_kategorie USING (kategorie_id)
                LEFT JOIN mvv_stg_stgteil USING (studiengang_id)
                LEFT JOIN mvv_stgteil USING (stgteil_id)
                LEFT JOIN mvv_fach_inst USING (fach_id)
                LEFT JOIN semester_data start_sem ON (mvv_studiengang.start = start_sem.semester_id)
                LEFT JOIN semester_data end_sem ON (mvv_studiengang.end = end_sem.semester_id)
            WHERE (mvv_studiengang.name LIKE ' . $quoted_term . '
                OR mvv_studiengang.name_kurz LIKE ' . $quoted_term  .'
                OR abschluss.name LIKE ' . $quoted_term . '
                OR mvv_abschl_kategorie.name LIKE ' . $quoted_term . '
                OR mvv_stgteil.zusatz LIKE ' . $quoted_term . ')
                ' . self::getFilterSql($filter) . '
            GROUP BY studiengang_id
            ORDER BY `name`
        ');
    }

    /**
     * Retrieves all Studiengaenge by given ids optionally filtered.
     *
     * @see ModuleManagementModel::getFilterSql()
     * @param array $studiengang_ids An array of Studiengang ids.
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return SimpleORMapCollection A collection of Studiengaenge.
     */
    public static function findByIds($studiengang_ids, $filter = null)
    {
        if ($filter['mvv_fach_inst.institut_id']) {
            $fach_sql = 'LEFT JOIN mvv_fach_inst USING(fach_id)';
        } else {
            $fach_sql = '';
        }
        return parent::getEnrichedByQuery('
            SELECT mvv_studiengang.*,
                abschluss.name AS `abschluss_name`,
                mvv_abschl_kategorie.name AS `kategorie_name`,
                COUNT(mvv_stgteil.fach_id) AS `count_faecher`
            FROM mvv_studiengang
                LEFT JOIN abschluss USING (abschluss_id)
                LEFT JOIN mvv_abschl_zuord USING (abschluss_id)
                LEFT JOIN mvv_abschl_kategorie USING (kategorie_id)
                LEFT JOIN mvv_stg_stgteil USING (studiengang_id)
                LEFT JOIN mvv_stgteil USING (stgteil_id)
                '. $fach_sql . '
            WHERE mvv_studiengang.studiengang_id IN(?)
                '. self::getFilterSql($filter) . '
            GROUP BY studiengang_id
            ORDER BY `name`',
            [(array) $studiengang_ids]
        );
    }

    /**
     * Returns an array with all types of status found by given
     * studiengang ids as key and the number of associated Studiengaenge as
     * value.
     *
     * @param array $studiengang_ids
     * @return array
     */
    public static function findStatusByIds($studiengang_ids = [])
    {
        if (is_array($studiengang_ids) && sizeof($studiengang_ids)) {
            $stmt = DBManager::get()->prepare("
                SELECT IFNULL(stat, '__undefined__') AS stat,
                    COUNT(studiengang_id) AS count_objects
                FROM mvv_studiengang WHERE studiengang_id IN (?)
                GROUP BY stat");
            $stmt->execute([$studiengang_ids]);
        } else {
            $stmt = DBManager::get()->prepare("
                SELECT IFNULL(stat, '__undefined__') AS stat,
                    COUNT(studiengang_id) AS count_objects
                FROM mvv_studiengang
                GROUP BY stat
            ");
            $stmt->execute();
        }

        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $status) {
            $result[$status['stat']] = [
                'name' => $GLOBALS['MVV_STUDIENGANG']['STATUS']['values'][$status['stat']]['name'] ?? _('Undefinierter Status'),
                'count_objects' => $status['count_objects']
            ];
        }
        return $result;
    }

    /**
     * Returns an array with ids of all Studiengaenge found by the given filter.
     * If no filter is set an empty array will be returned.
     *
     * @see ModuleManagementModel::getFilterSql()
     * @param array $filter Key-value pairs of filed names and values
     * to filter the result set.
     * @return array An array of Studiengang ids.
     */
    public static function findByFilter($filter)
    {
        $filter_sql = self::getFilterSql($filter, true);
        if ($filter_sql == '') {
            return [];
        }
        $stmt = DBManager::get()->prepare('
            SELECT DISTINCT studiengang_id
            FROM mvv_studiengang
                LEFT JOIN abschluss USING(abschluss_id)
                LEFT JOIN mvv_abschl_zuord USING(abschluss_id)
                LEFT JOIN mvv_abschl_kategorie USING(kategorie_id)
                LEFT JOIN mvv_stg_stgteil USING(studiengang_id)
                LEFT JOIN mvv_stgteil USING(stgteil_id)
                LEFT JOIN mvv_fach_inst USING(fach_id)
                LEFT JOIN semester_data start_sem ON (mvv_studiengang.start = start_sem.semester_id)
                LEFT JOIN semester_data end_sem ON (mvv_studiengang.end = end_sem.semester_id)
            '. $filter_sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * Returns an array with Modul ids from modules related to this Studiengang.
     * The relation is done via Studiengangteile, Studiengangteil-Versionen and
     * Studiengangteil-Abschnitte.
     * Optionally restricted to only public visible modules and filtered by an
     * array of Modul ids.
     *
     * @param boolean $only_public If true only public visible modules will
     * be retrieved.
     * @param array $modul_ids An array of module ids. Only the intersection of
     * these modules and the found modules will be returned.
     * @return array An array of Modul ids.
     */
    public function getRelatedModules($only_public = true, $modul_ids = null)
    {
        if ($only_public) {
            $query = '
                SELECT DISTINCT modul_id
                FROM mvv_stg_stgteil
                    INNER JOIN mvv_stgteilversion msv USING (stgteil_id)
                    INNER JOIN mvv_stgteilabschnitt USING (version_id)
                    INNER JOIN mvv_stgteilabschnitt_modul USING (abschnitt_id)
                    INNER JOIN mvv_modul mm USING (modul_id)
                WHERE studiengang_id = ?
                    AND msv.stat IN (?)
                    AND mm.stat IN(?) ';
            $params = [$this->getId(), StgteilVersion::getPublicStatus(),
            Modul::getPublicStatus()];
        } else {
            $query = '
                SELECT DISTINCT modul_id
                FROM mvv_stg_stgteil
                INNER JOIN mvv_stgteilversion msv USING (stgteil_id)
                INNER JOIN mvv_stgteilabschnitt USING (version_id)
                INNER JOIN mvv_stgteilabschnitt_modul USING (abschnitt_id)
                INNER JOIN mvv_modul mm USING(modul_id)
                WHERE studiengang_id = ? ';
            $params = [$this->getId()];
        }
        if (is_array($modul_ids)) {
            $query .= ' AND mm.modul_id IN (?)';
            $params[] = $modul_ids;
        }
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return _('Studiengang');
    }

    /**
     * Returns the first semester this studiengang is active.
     *
     * @return object semester
     */
    public function getStartSem()
    {
        return Semester::find($this->sem);
    }

    /**
     * Returns the last semester this studiengang is active.
     *
     * @return object semester
     */
    public function getEndSem()
    {
        return Semester::find($this->end);
    }

    /**
     * @see ModuleManagementModel::getResponsibleInstitutes()
     */
    public function getResponsibleInstitutes()
    {
        if ($this->responsible_institute) {
            return [$this->responsible_institute];
        }
        return parent::getResponsibleInstitutes();
    }

    /**
     * Returns whether this studiengang is active.
     *
     * @return boolean true if active
     */
    public function isActive()
    {
        $start_sem = $this->getStartSem();
        if (is_null($start_sem)) {
            return false;
        }
        $time = time();
        $end_sem = $this->getEndSem();
        if (!$end_sem) {
            return $start_sem->beginn <= $time;
        }
        return $start_sem->beginn <= $time && $time <= $end_sem->ende;
    }

    public function getVariant() {
        return $this->typ;
    }

    /**
     * @see MvvTreeItem::getTrailParentId()
     */
    public function getTrailParentId()
    {
        return $this->abschluss_id;
    }

    /**
     * @see MvvTreeItem::getTrailParent()
     */
    public function getTrailParent()
    {
        return Abschluss::findCached($this->getTrailParentId());
    }

    /**
     * @see MvvTreeItem::getChildren()
     */
    public function getChildren()
    {
        return StudiengangTeil::findByStudiengang($this->getId());
    }

    /**
     * @see MvvTreeItem::getParents()
     */
    public function getParents($mode = null)
    {
        return [$this->responsible_institute];
    }

    /**
     * @see MvvTreeItem::hasChildren()
     */
    public function hasChildren()
    {
        return count($this->getChildren()) > 0;
    }

    /**
     * Assignes languages of instruction to this study course.
     *
     * @param array $languages An array of language keys defined in mvv_config.php.
     */
    public function assignLanguagesOfInstruction($languages)
    {
        $assigned_languages = array();
        $languages_flipped = array_flip($languages);
        foreach ($GLOBALS['MVV_STUDIENGANG']['SPRACHE']['values'] as $key => $language) {
            if (isset($languages_flipped[$key])) {
                $language = StudycourseLanguage::find([$this->id, $key]);
                if (!$language) {
                    $language = new StudycourseLanguage();
                    $language->studiengang_id = $this->id;
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
     * Assigns studycourse types to this study course.
     *
     * @param array $types An array of names of study course types.
     */
    public function assignStudycourseTypes($types)
    {
        $stc_type_proto = new StudycourseType();
        $stc_type_objects = [];
        foreach ($types as $type) {
            $stc_type_objects[$type] =
                    $this->studycourse_types->findOneBy('type', $type);
            if (!$stc_type_objects[$type]) {
                $stc_type_objects[$type] = clone $stc_type_proto;
                $stc_type_objects[$type]->type = $type;
                $stc_type_objects[$type]->studiengang_id;
            }
        }
        $this->studycourse_types =
                SimpleORMapCollection::createFromArray($stc_type_objects);
    }

    public function validate()
    {
        $ret = parent::validate();
        if ($this->isDirty()) {
            $messages = [];
            $rejected = false;
            // The name of the studiengang must be longer than 4 characters
            if (mb_strlen($this->isI18nField('name')
                    ? $this->name->original()
                    : $this->name) < 4) {
                $ret['name'] = true;
                $messages[] = _('Der Name des Studiengangs ist zu kurz (mindestens 4 Zeichen).');
                $rejected = true;
            }
            // if the short name is given it must be at least 2 characters
            if (trim($this->isI18nField('name_kurz')
                    ? $this->name_kurz->original()
                    : $this->name_kurz)
                && mb_strlen(trim($this->isI18nField('name_kurz')
                    ? $this->name_kurz->original()
                    : $this->name_kurz)) < 2) {
                $ret['name_kurz'] = true;
                $messages[] = _('Die Kurzbezeichnung muss mindestens 2 Zeichen lang sein.');
                $rejected = true;
            }
            if ($this->abschluss_id) {
                $stmt = DBManager::get()->prepare('SELECT abschluss_id '
                        . 'FROM abschluss WHERE abschluss_id = ?');
                $stmt->execute([$this->abschluss_id]);
                if (!$stmt->fetch()) {
                    $ret['abschluss_id'] = true;
                    $messages[] = _('Unbekannter Abschluss.');
                    $rejected = true;
                }
            } else {
                $ret['abschluss_id'] = true;
                $messages[] = _('Bitte einen Abschluss angeben.');
                $rejected = true;
            }
            if ($this->institut_id) {
                $stmt = DBManager::get()->prepare('SELECT institut_id '
                        . 'FROM Institute WHERE Institut_id = ?');
                $stmt->execute([$this->institut_id]);
                if (!$stmt->fetch()) {
                    $ret['institut_id'] = true;
                    $messages[] = _('Unbekannte Einrichtung');
                    $rejected = true;
                }
            } else {
                $ret['institut_id'] = true;
                $messages[] = _('Bitte eine verantwortliche Einrichtung angeben.');
                $rejected = true;
            }
            if (!$this->isNew() && $this->isFieldDirty('typ') && count($this->studiengangteile)) {
                $this->revertValue('typ');
                $ret['typ'] = true;
                $messages[] = _('Der Typ des Studiengangs kann nicht mehr verändert werden, da bereits ein Studiengangteil zugeordnet wurde.');
                $rejected = true;
            } else {
                if (!in_array($this->typ, words('einfach mehrfach'))) {
                    $ret['typ'] = true;
                    $messages[] = _('Bitte den Typ des Studiengangs wählen.');
                    $rejected = true;
                }
            }
            if ($this->start) {
                $start_sem = Semester::find($this->start);
                if (!$start_sem) {
                    $ret['start'] = true;
                    $messages[] = _('Ungültiges Semester.');
                    $rejected = true;
                } else if ($this->end) {
                    $end_sem = Semester::find($this->end);
                    if ($end_sem) {
                        if ($start_sem->beginn > $end_sem->beginn) {
                            $ret['start'] = true;
                            $messages[] = _('Das Endsemester muss nach dem Startsemester liegen.');
                            $rejected = true;
                        }
                    } else {
                        $ret['end'] = true;
                        $messages[] = _('Ungültiges Endsemester.');
                        $rejected = true;
                    }
                }
            }  else {
                $ret['start'] = true;
                $messages[] = _('Bitte ein Startsemester angeben.');
                $rejected = true;
            }
            if ($rejected) {
                throw new InvalidValuesException(join("\n", $messages), $ret);
            }
        }
        return $ret;
    }

}
