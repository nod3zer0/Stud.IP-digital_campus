<div
    id="courseware-index-app"
    entry-element-id="<?= htmlReady($entry_element_id) ?>"
    entry-type="courses"
    entry-id="<?= htmlReady(Context::getId()) ?>"
    unit-id="<?= htmlReady($unit_id) ?>"
    oer-enabled="<?= htmlReady(Config::get()->OERCAMPUS_ENABLED) ?>"
    licenses='<?= htmlReady($licenses) ?>'
    >
</div>