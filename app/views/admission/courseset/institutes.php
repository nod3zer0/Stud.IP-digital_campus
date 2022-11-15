<?= Icon::create('arr_2down', Icon::ROLE_SORT)->asImg([
    'title'   => _('Einrichtung hinzufügen'),
    'onclick' => "STUDIP.Admission.updateInstitutes($('input[name=\"institute_id\"]').val(), '" . $controller->url_for('admission/courseset/institutes', !empty($courseset) ? $courseset->getId() : '') . "', '" . $controller->url_for('admission/courseset/instcourses', !empty($courseset) ? $courseset->getId() : '') . "', 'add')",
]) ?>
<?= $instSearch ?>
<?= Icon::create('search', Icon::ROLE_CLICKABLE, ['title' => _("Suche starten")]) ?>

<ul>
    <?php foreach (SimpleCollection::createFromArray($selectedInstitutes)->orderBy('Name') as $institute => $data) { ?>
    <li id="<?= $institute ?>">
        <input type="hidden" name="institutes[]" value="<?= $institute ?>" class="institute">
        <span class="hover_box">
            <?= htmlReady($data['Name']) ?>
            <span class="action_icons">
                <?= Icon::create('trash')->asImg([
                    'title'   => _('Einrichtung entfernen'),
                    'onclick' => "STUDIP.Admission.updateInstitutes('{$institute}', '" . $controller->url_for('admission/courseset/institutes',$institute) . "', '" . $controller->url_for('admission/courseset/instcourses',$institute)."', 'delete')"
                ]); ?>
            </span>
        </span>
    </li>
    <?php } ?>
</ul>
