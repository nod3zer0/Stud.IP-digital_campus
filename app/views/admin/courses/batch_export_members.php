<?php
/**
 * @var Course $course
 */
?>
<label>
    <input name="export_members[]" type="checkbox" value="<?= htmlReady($course->id) ?>"
           aria-label="<?= htmlReady(_('Teilnehmende exportieren')) ?>">
</label>
