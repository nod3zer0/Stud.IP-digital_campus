<? if (!$unitsNotFound): ?>
    <div
        id="courseware-index-app"
        entry-element-id="<?= htmlReady($entry_element_id) ?>"
        entry-type="courses"
        entry-id="<?= htmlReady(Context::getId()) ?>"
        unit-id="<?= htmlReady($unit_id) ?>"
        licenses='<?= htmlReady($licenses) ?>'
        feedback-settings='<?= htmlReady($feedback_settings) ?>'
        >
    </div>
<? endif; ?>
