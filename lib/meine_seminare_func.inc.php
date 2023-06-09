<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/**
 *
 * @param string $group_field
 * @param array $groups
 */
function get_group_names(string $group_field, array $groups): array
{
    $mapper = function (): string {
        return 'unknown';
    };
    if ($group_field === 'sem_number') {
        $all_semester = Semester::findAllVisible();
        $mapper = function ($key) use ($all_semester): string {
            return (string) $all_semester[$key]['name'];
        };
    } elseif ($group_field === 'sem_tree_id') {
        $the_tree = TreeAbstract::GetInstance(StudipSemTree::class, ['build_index' => true]);
        $mapper = function ($key) use ($the_tree): string {
            if (!empty($the_tree->tree_data[$key])) {
                return implode(' > ', array_filter([
                    $the_tree->getShortPath($the_tree->tree_data[$key]['parent_id']),
                    $the_tree->tree_data[$key]['name'],
                ]));
            }

            return _('keine Studienbereiche eingetragen');
        };
    } elseif ($group_field === 'sem_status') {
        $mapper = function ($key): string {
            $sem_type = $GLOBALS['SEM_TYPE'][$key];
            return "{$sem_type['name']} ({$GLOBALS['SEM_CLASS'][$sem_type['class']]['name']})";
        };
    } elseif ($group_field === 'no_grouped') {
        $mapper = function (): string {
            return _('keine Gruppierung');
        };
    } elseif ($group_field === 'gruppe') {
        $groupcount = 0;
        $mapper = function () use (&$groupcount): string {
            $groupcount += 1;
            return _('Gruppe') . " {$groupcount}";
        };
    } elseif ($group_field === 'dozent_id') {
        $mapper = function ($key): string {
            return get_fullname($key, 'no_title_short');
        };
    } elseif ($group_field === 'mvv') {
        $mapper = function ($key): string {
            $module = Modul::find($key);
            return $module ? (string) $module->getDisplayName() : _('Keinem Modul zugeordnet');
        };
    }

    $result = [];
    foreach (array_keys($groups) as $key) {
        $result[$key] = $mapper($key);
    }
    return $result;
}

/**
 *
 * @param string $group_field
 * @param array $groups
 */
function sort_groups($group_field, &$groups)
{
    switch ($group_field) {
        case 'sem_number':
            krsort($groups, SORT_NUMERIC);
            break;

        case 'gruppe':
            ksort($groups, SORT_NUMERIC);
            break;

        case 'sem_tree_id':
            uksort($groups, function ($a, $b) {
                $the_tree = TreeAbstract::GetInstance('StudipSemTree', ['build_index' => true]);
                return $the_tree->tree_data[$a]['index'] - $the_tree->tree_data[$b]['index'];
            });
            break;

        case 'sem_status':
            uksort($groups, function ($a, $b) {
                global $SEM_CLASS,$SEM_TYPE;
                return strnatcasecmp(
                    $SEM_TYPE[$a]['name'] . ' (' . $SEM_CLASS[$SEM_TYPE[$a]['class']]['name'] . ')',
                    $SEM_TYPE[$b]['name'] . ' (' . $SEM_CLASS[$SEM_TYPE[$b]['class']]['name'] . ')'
                );
            });
            break;

        case 'dozent_id':
            uksort($groups, function ($a,$b) {
                $replacements = ['ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue'];
                return strnatcasecmp(
                    str_replace(array_keys($replacements), array_values($replacements), mb_strtolower(get_fullname($a, 'no_title_short'))),
                    str_replace(array_keys($replacements), array_values($replacements), mb_strtolower(get_fullname($b, 'no_title_short')))
                );
            });
            break;

        case 'mvv':
            uksort($groups, function ($a, $b): int {
                $module_a = Modul::find($a);
                $module_b = Modul::find($b);

                if (!$module_a) {
                    return 1;
                }
                if (!$module_b) {
                    return -1;
                }
                return strnatcasecmp($module_a->getDisplayName(), $module_b->getDisplayName());
            });
            break;
    }

    foreach ($groups as $key => &$value) {
        usort($value, function ($a, $b) {
            if ($a['gruppe'] != $b['gruppe']) {
                return (int)($a['gruppe'] - $b['gruppe']);
            } else {
                if (Config::get()->IMPORTANT_SEMNUMBER) {
                    return strnatcasecmp($a['sem_nr'], $b['sem_nr']);
                } else {
                    return strnatcmp($a['name'], $b['name']);
                }
            }
        });
    }
    return true;
}

/**
 *
 * @param array $groups
 * @param array $my_obj
 */
function correct_group_sem_number(&$groups, &$my_obj): bool
{
    if (is_array($groups) && is_array($my_obj)) {
        $sem_data = Semester::findAllVisible();
        //end($sem_data);
        //$max_sem = key($sem_data);
        foreach ($sem_data as $sem_key => $one_sem){
            $current_sem = $sem_key;
            if (!$one_sem['past']) {
                break;
            }
        }
        if (isset($sem_data[$current_sem + 1])){
            $max_sem = $current_sem + 1;
        } else {
            $max_sem = $current_sem;
        }
        foreach ($my_obj as $seminar_id => $values){
            if ($values['obj_type'] == 'sem' && $values['sem_number'] != $values['sem_number_end']){
                if ($values['sem_number_end'] == -1 && $values['sem_number'] < $current_sem) {
                    unset($groups[$values['sem_number']][$seminar_id]);
                    fill_groups($groups, $current_sem, ['seminar_id' => $seminar_id, 'name' => $values['name'], 'gruppe' => $values['gruppe']]);
                    if (!count($groups[$values['sem_number']])) {
                        unset($groups[$values['sem_number']]);
                    }
                } else {
                    $to_sem = $values['sem_number_end'];
                    for ($i = $values['sem_number']; $i <= $to_sem; ++$i){
                        fill_groups($groups, $i, ['seminar_id' => $seminar_id, 'name' => $values['name'], 'gruppe' => $values['gruppe']]);
                    }
                }
                if ($GLOBALS['user']->cfg->getValue('SHOWSEM_ENABLE')){
                    $sem_name = " (" . $sem_data[$values['sem_number']]['name'] . " - ";
                    $sem_name .= (($values['sem_number_end'] == -1) ? _("unbegrenzt") : $sem_data[$values['sem_number_end']]['name']) . ")";
                    $my_obj[$seminar_id]['name'] .= $sem_name;
                }
            }
        }
        return true;
    }
    return false;
}

/**
 *
 * @param mixed $my_obj
 */
function add_sem_name(&$my_obj): bool
{
    if ($GLOBALS['user']->cfg->getValue('SHOWSEM_ENABLE')) {
        $sem_data = Semester::findAllVisible();
        if (is_array($my_obj)) {
            foreach ($my_obj as $seminar_id => $values){
                if ($values['obj_type'] == 'sem' && $values['sem_number'] != $values['sem_number_end']){
                    $sem_name = " (" . $sem_data[$values['sem_number']]['name'] . " - ";
                    $sem_name .= (($values['sem_number_end'] == -1) ? _("unbegrenzt") : $sem_data[$values['sem_number_end']]['name']) . ")";
                    $my_obj[$seminar_id]['name'] .= $sem_name;
                } else {
                    $my_obj[$seminar_id]['name'] .= " (" . $sem_data[$values['sem_number']]['name'] . ") ";
                }
            }
        }
    }
    return true;
}

/**
 *
 * @param array       $groups
 * @param string|null $group_key
 * @param array       $group_entry
 *
 * @return bool
 */
function fill_groups(array &$groups, ?string $group_key, array $group_entry): bool
{
    if (is_null($group_key)){
        $group_key = 'not_grouped';
    }

    if (!isset($groups[$group_key]) || !is_array($groups[$group_key])) {
        $groups[$group_key] = [];
    }

    $group_entry['name'] = str_replace(
        ['ä', 'ö', 'ü'],
        ['ae', 'oe', 'ue'],
        mb_strtolower($group_entry['name'])
    );
    if (!in_array($group_entry, $groups[$group_key])) {
        $groups[$group_key][$group_entry['seminar_id']] = $group_entry;
        return true;
    }

    return false;
}

/**
 * This function returns all valid fields that may be used for course
 * grouping in "My Courses".
 *
 * @return array All fields that may be specified for course grouping
 */
function getValidGroupingFields(): array
{
    $valid = [
        'not_grouped',
        'sem_number',
        'sem_tree_id',
        'sem_status',
        'gruppe',
        'dozent_id',
    ];

    if (LvgruppeSeminar::countBySql('1') > 0) {
        $valid[] = 'mvv';
    }

    return $valid;
}
