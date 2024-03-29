<? if (!empty($selected_status)
    || !empty($selected_fach)
    || !empty($selected_kategorie)
    || !empty($selected_abschluss)
    || !empty($selected_fachbereich)
    || !empty($selected_zuordnung)
    || !empty($selected_institut)
    || !empty($selected_name)
    || (
        !empty($selected_semester)
        && !empty($default_semester)
        && $selected_semester !== $default_semester)
    ) : ?>
<div style="width: 100%; text-align: right;">
    <a href="<?= $action_reset ?>">
        <?= Icon::create('refresh', 'clickable', ['title' => _('Filter zurücksetzen')])->asImg(); ?>
        <?= _('Zurücksetzen') ?>
    </a>
</div>
<? endif; ?>
<form id="index_filter" action="<?= $action ?>" method="post">
    <? if (!empty($name_search)) : ?>
        <label class="mvv-name-search">
            <?= $name_caption ?: _('Name') ?>
            <input type="text" name="name_filter" value="<?= htmlReady($selected_name) ?>">
            <input type="submit" value="<?= _('Suchen') ?>">
        </label>
    <? endif ?>
    <? if (!empty($semester)) : ?>
    <label>
        <?= $semester_caption ?? _('Semester') ?><br>
        <select name="semester_filter" class="sidebar-selectlist submit-upon-select">
            <option value="all"<?= (!$selected_semester ? ' selected' : '') ?>><?= _('Alle Semester') ?></option>
            <? foreach ($semester as $sem) : ?>
            <option value="<?= $sem['semester_id'] ?>"<?= ($sem['semester_id'] == $selected_semester ? ' selected' : '') ?>><?= htmlReady($sem['name']) ?></option>
            <? endforeach; ?>
        </select>
    </label>
    <? endif; ?>
    <? if (!empty($zuordnungen)) : ?>
    <label>
        <?= _('Zugeordnet zu Objekten') ?>
        <select name="zuordnung_filter" class="sidebar-selectlist submit-upon-select">
            <option value=""><?= _('Alle') ?></option>
            <? foreach ($zuordnungen as $object_type => $zuordnung) : ?>
            <option value="<?= $object_type ?>"
                <?= ($object_type == $selected_zuordnung ? ' selected' : '') ?>><?= htmlReady($object_type::getClassDisplayName()) ?></option>
            <? endforeach; ?>
        </select>
    </label>
    <? endif; ?>
    <? if (!empty($status)) : ?>
    <label>
        <?= _('Status') ?><br>
        <select name="status_filter" class="sidebar-selectlist submit-upon-select">
            <option value=""><?= _('Alle') ?></option>
            <? foreach ($status_array as $key => $stat) : ?>
            <? if (isset($status[$key]['count_objects'])) : ?>
            <option value="<?= $key ?>"
                <?= ($key === $selected_status ? ' selected' : '') ?>><?= htmlReady($stat['name']) . ' (' . ($status[$key] ? $status[$key]['count_objects'] : '0') . ')' ?></option>
            <? endif; ?>
            <? endforeach; ?>
            <? if (isset($status['__undefined__'])) : ?>
                <option value="__undefined__"<?= $selected_status == '__undefined__' ? ' selected' : '' ?>><?= _('nicht angegeben')  . ' (' . ($stat['count_objects'] ?? '0') . ')' ?></option>
            <? endif; ?>
        </select>
    </label>
    <? endif; ?>
    <? if (!empty($faecher)) : ?>
        <label>
            <?= _('Fach') ?><br>
            <select name="fach_filter" class="sidebar-selectlist submit-upon-select nested-select">
                <option value=""><?= _('Alle') ?></option>
                <? foreach ($faecher as $fach) : ?>
                    <option value="<?= htmlReady($fach->id) ?>"
                        <?= $fach->id === $selected_fach ? 'selected' : '' ?>
                    >
                        <?= htmlReady($fach->name) . ' (' . $count_faecher[$fach->id] . ')' ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>
    <? endif; ?>
    <? if (!empty($kategorien)) : ?>
    <label>
        <?= _('Kategorie') ?><br>
        <select name="kategorie_filter" class="sidebar-selectlist submit-upon-select">
            <option value=""><?= _('Alle') ?></option>
            <? foreach ($kategorien as $kategorie) : ?>
                <option value="<?= $kategorie->id ?>"
                    <? if (
                        $kategorie->id === $selected_kategorie
                        || (
                            isset($abschluesse, $abschluesse[$selected_abschluss])
                            && $abschluesse[$selected_abschluss]->kategorie_id == $kategorie->id
                        )
                    ) echo 'selected'; ?>>
                    <?= htmlReady($kategorie->name) . ' (' . $kategorie->count_objects . ')'  ?>
                </option>
            <? endforeach; ?>
        </select>
    </label>
    <? endif; ?>
    <? if (!empty($abschluesse)) : ?>
    <label>
        <?= _('Abschluss') ?><br>
        <select name="abschluss_filter" class="sidebar-selectlist submit-upon-select">
            <option value=""><?= _('Alle') ?></option>
            <? foreach ($abschluesse as $abschluss) : ?>
            <option value="<?= htmlReady($abschluss->id) ?>"
                <?= $abschluss->getId() == $selected_abschluss ? ' selected' : '' ?>
            >
                <?= htmlReady($abschluss->name) . ' (' . ($abschluss->count_objects ?? $count_abschluesse[$abschluss->id]) . ')' ?>
            </option>
            <? endforeach; ?>
        </select>
    </label>
    <? endif; ?>
    <? if (!empty($institute)) : ?>
        <? $perm_institutes = MvvPerm::getOwnInstitutes() ?>
        <? if ($perm_institutes !== false) : ?>
        <label>
            <?= _('Verantwortliche Einrichtung') ?><br>
            <select name="institut_filter" class="sidebar-selectlist nested-select submit-upon-select">
                <option value=""><?= _('Alle') ?></option>
                <? foreach ($institute as $institut) : ?>
                    <?
                    if (count($perm_institutes) == 0
                            || in_array($institut->getId(), $perm_institutes)) {
                            echo '<option value="' . $institut->getId()
                                . ($institut->getId() == $selected_institut ?
                                    '" selected' : '"')
                                . ' class="nested-item">'
                                . htmlReady($institut->name
                                . ' (' . $institut->count_objects . ')')
                                . '</option>';
                    }
                    ?>
                <? endforeach; ?>
            </select>
        </label>
        <? endif ?>
    <? endif; ?>
    <? if (!empty($fachbereiche)) : ?>
        <? $perm_institutes = MvvPerm::getOwnInstitutes() ?>
        <? if ($perm_institutes !== false) : ?>
        <label>
            <?= _('Fachbereiche') ?><br>
            <select name="fachbereich_filter" class="sidebar-selectlist nested-select institute-list submit-upon-select">
                <option value=""><?= _('Alle') ?></option>
                <? foreach ($fachbereiche as $fachbereich) : ?>
                    <? if (count($perm_institutes) == 0
                            || in_array($fachbereich->getId(), $perm_institutes)) : ?>
                    <option class="nested-item" value="<?= $fachbereich->getId() ?>"<?= ($fachbereich->getId() == $selected_fachbereich ? ' selected' : '') ?>><?= htmlReady($fachbereich->getDisplayName()) . ' (' . $fachbereich->count_objects . ')' ?></option>
                    <? endif; ?>
                <? endforeach; ?>
            </select>
        </label>
        <? endif; ?>
    <? endif; ?>
</form>
