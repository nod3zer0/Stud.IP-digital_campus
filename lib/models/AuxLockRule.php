<?php
/**
 * AuxLockRule.php - SORM for the aux data of a seminar
 *
 * Used to filter and sort the datafields of a course member
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Florian Bieringer <florian.bieringer@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.0
 *
 * @property string $id alias column for lock_id
 * @property string $lock_id database column
 * @property I18NString $name database column
 * @property I18NString $description database column
 * @property JSONArrayObject $attributes database column
 * @property JSONArrayObject $sorting database column
 * @property int|null $mkdate database column
 * @property int|null $chdate database column
 * @property SimpleORMapCollection|Course[] $courses has_many Course
 * @property mixed $datafields additional field
 */
class AuxLockRule extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'aux_lock_rules';

        $config['has_many'] = [
            'courses' => [
                'class_name'        => Course::class,
                'foreign_key'       => 'lock_id',
                'assoc_foreign_key' => 'aux_lock_rule',
            ],
        ];

        $config['additional_fields'] = [
            'datafields' => true
        ];

        $config['serialized_fields'] = [
            'attributes' => JSONArrayObject::class,
            'sorting'    => JSONArrayObject::class,
        ];

        $config['i18n_fields'] = [
            'name'        => true,
            'description' => true,
        ];

        $config['registered_callbacks'] = [
            'before_store' => [
                function (AuxLockRule $rule) {
                    $rule->sorting = array_filter($rule->sorting->getArrayCopy(), function ($id) use ($rule) {
                        return $rule->attributes->contains($id);
                    }, ARRAY_FILTER_USE_KEY);
                },
            ],
            'before_delete' => [
                function (AuxLockRule $rule) {
                    return count($rule->courses) === 0;
                },
            ]
        ];

        parent::configure($config);
    }

    public static function findOneByCourse(Course $course): ?AuxLockRule
    {
        return self::findOneByCourseId($course->id);
    }

    public static function findOneByCourseId(string $course_id): ?AuxLockRule
    {
        $condition = "JOIN seminare ON lock_id = aux_lock_rule
                      WHERE Seminar_id = ?";
        return self::findOneBySQL($condition, [$course_id]);
    }

    /**
     * Cache to avoid loading datafields for a user more than once
     */
    private $datafieldCache = [];

    /**
     * Returns the sorted and filtered datafields of an aux
     *
     * return array datafields as keys
     */
    public function getDatafields()
    {
        $attributes = $this->attributes->getArrayCopy();
        $sorting    = $this->sorting->getArrayCopy();

        foreach ($attributes as $key => $attr) {
            if (!$attr) {
                unset($sorting[$key]);
            }
        }
        asort($sorting);
        return $sorting;
    }

    /**
     * Updates a datafield of a courseMember by the given data
     */
    public function updateMember(CourseMember $member, array $data)
    {
        foreach ($data as $key => $value) {
            $datafield = current($this->getDatafield($member, $key));
            if ($datafield->isEditable()) {
                $datafield->setValueFromSubmit($value);
                $datafield->store();
            }
        }
    }

    /**
     * Returns an array of all entries of aux data in a course
     *
     * @param string $course if the course wasnt set automaticly by getting called
     * from a course it is possible to set it here
     * @return array formatted entries
     */
    public function getCourseData($course = null, $display_only = false)
    {
        // set course
        if (!$course) {
            $course = $this->course;
        }

        $mapping = [
            'vadozent'   => join(', ', $course->members->findBy('status', 'dozent')->getUserFullname()),
            'vasemester' => $course->start_semester->name,
            'vatitle'    => $course->name,
            'vanr'       => $course->veranstaltungsnummer,
        ];
        $head_mapping = [
            'vadozent'   => _('Dozenten'),
            'vasemester' => _('Semester'),
            'vatitle'    => _('Veranstaltungstitel'),
            'vanr'       => _('Veranstaltungsnummer'),
        ];

        // start collecting entries
        $result = [
            'head' => [
                'name' => _('Name'),
            ],
            'rows' => [],
        ];

        // get all autors and users
        foreach ($course->members->findBy('status', ['autor', 'user'])->orderBy('nachname,vorname') as $member) {
            $new['name'] = $member->getUserFullName('full_rev');

            // get all datafields
            foreach ($this->datafields as $field => $useless_value_pls_refactor) {

                // if standard get it from the mapping else get it from the datafield
                if ($mapping[$field]) {
                    $result['head'][$field] = $head_mapping[$field];
                    $new[$field] = htmlReady($mapping[$field]);
                } else {
                    $datafield = $this->getDatafield($member, $field);
                    if ($datafield && current($datafield)->isVisible()) {
                        $result['head'][$field] = key($datafield);
                        if (!$display_only && current($datafield)->isEditable() && $this->datafieldCache[$field]->object_type == 'usersemdata') {
                            $new[$field] = current($datafield)->getHTML($member->user_id);
                        } else {
                            $new[$field] = htmlReady(current($datafield)->getDisplayValue(false));
                        }
                    }
                }
            }

            // push the result
            $result['rows'][$member->id] = $new;
        }
        return $result;
    }

    public function getMemberData(CourseMember $member)
    {
        $datafields = SimpleCollection::createFromArray(DatafieldEntryModel::findByModel($member));

        $result = [];
        foreach ($this->attributes as $field) {
            // since we have no only datafields we have to filter!
            $new = $datafields->findOneBy('datafield_id', $field);
            if ($new) {
                $result[] = $new;
            }
        }

        usort($result, function (DatafieldEntryModel $a, DatafieldEntryModel $b) {
            $a_order = $this->sorting[$a->datafield_id] ?? 0;
            $b_order = $this->sorting[$b->datafield_id] ?? 0;
            return $a_order - $b_order;
        });

        return $result;
    }

    /**
     * Caching for the datafields
     */
     private function getDatafield(CourseMember $member, $field_id): ?array
     {
         if (mb_strlen($field_id) === 32) {
             if (!array_key_exists($field_id, $this->datafieldCache)) {
                 $this->datafieldCache[$field_id] = DataField::find($field_id);
             }
             if (isset($this->datafieldCache[$field_id])) {
                 $field = null;
                 if ($this->datafieldCache[$field_id]->object_type === 'usersemdata') {
                     $field = current(DatafieldEntryModel::findByModel($member, $field_id));
                 }
                 if ($this->datafieldCache[$field_id]->object_type === 'user') {
                     $field = current(DatafieldEntryModel::findByModel(User::find($member->user_id), $field_id));
                 }
                 if ($field) {
                     $range_id = $field->sec_range_id ? [$field->range_id, $field->sec_range_id] : $field->range_id;
                     $typed_df = DataFieldEntry::createDataFieldEntry($field->datafield, $range_id, $field->getValue('content'));
                     return [$field->name => $typed_df];
                 }
             }
         }

         return null;
     }

     public static function validateFields(array $fields): bool
     {
         $entries = DataField::getDataFields('usersemdata');
         foreach ($entries as $entry) {
             if (in_array($entry->id, $fields)) {
                 return true;
             }
         }

         return false;
     }
}
