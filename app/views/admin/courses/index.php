<?php
/**
 * @var Admin_CoursesController $controller
 * @var int $count_courses
 * @var Semester $semester
 * @var array $fields
 * @var array $activated_fields
 * @var string $sortby
 * @var string $sortflag
 * @var array $activeSidebarElements
 * @var int $max_show_courses
 */

$unsortable_fields = [
    'avatar',
    'room_time',
    'contents'
];
?>

<? if (empty($insts)): ?>
    <?= MessageBox::info(sprintf(_('Sie wurden noch keinen Einrichtungen zugeordnet. Bitte wenden Sie sich an einen der zuständigen %sAdministratoren%s.'), '<a href="' . URLHelper::getLink('dispatch.php/siteinfo/show') . '">', '</a>')) ?>
<? else :

    $attributes = [
        ':show-complete' => json_encode((bool) Config::get()->ADMIN_COURSES_SHOW_COMPLETE),
        ':fields' => json_encode($fields),
        ':unsortable-fields' => json_encode($unsortable_fields),
        ':max-courses' => (int) $max_show_courses,
        'sort-by' => $sortby,
        'sort-flag' => $sortflag,
    ];
?>
    <form method="post">
        <?= CSRFProtection::tokenTag() ?>

        <div class="admin-courses-vue-app course-admin"
             is="AdminCourses"
             v-cloak
             ref="app"
             <?= arrayToHtmlAttributes($attributes) ?>
        ></div>
    </form>

<? endif; ?>
