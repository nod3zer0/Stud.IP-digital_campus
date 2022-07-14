<script>
    STUDIP.courseware_progress_data = <?= json_encode($courseware_progress_data);?>;
    STUDIP.courseware_chapter_counter = <?= json_encode($courseware_chapter_counter);?>;
    STUDIP.is_teacher = <?= json_encode($is_teacher);?>;
</script>

<div
    id="courseware-dashboard-app"
    entry-type="courses"
    entry-id="<?= Context::getId() ?>"
>
</div>
