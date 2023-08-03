<?php
/**
 * @var Course $course
 */
?>
<label>
    <input name="assign_semtree[]" type="checkbox" value="<?= htmlReady($course->id) ?>"
           aria-label="<?= htmlReady(sprintf(_('Veranstaltung %s mehreren Studienbereichen zuordnen'),
               $course->getFullName())) ?>">
</label>
