<? if (!$unitsNotFound): ?>
    <div
        id="courseware-index-app"
        entry-element-id="<?= htmlReady($entry_element_id) ?>"
        entry-type="users"
        entry-id="<?= htmlReady($user_id) ?>"
        unit-id="<?= htmlReady($unit_id) ?>"
        oer-enabled='<?= htmlReady($oer_enabled) ?>'
        licenses='<?= htmlReady($licenses) ?>'
        >
    </div>
<? endif; ?>
