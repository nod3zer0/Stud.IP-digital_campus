<? foreach ($course_collection as $course):
    $teachers   = CourseMember::findByCourseAndStatus($course['seminar_id'], 'dozent');
    $dozenten = SimpleCollection::createFromArray($teachers)->map(function (CourseMember $teacher) {
        return $teacher->getUserFullname('no_title');
    });

    $mvv_pathes = [];
    if ($with_modules) {
        $trail_classes = ['Modulteil', 'StgteilabschnittModul', 'StgteilAbschnitt', 'StgteilVersion'];
        $mvv_object_pathes = MvvCourse::get($course['seminar_id'])->getTrails($trail_classes);

        if ($mvv_object_pathes) {
            foreach ($mvv_object_pathes as $mvv_object_path) {
                // show only complete pathes
                if (count($mvv_object_path) === 4) {
                    $mvv_object_names = [];
                    $modul_id = '';
                    foreach ($mvv_object_path as $mvv_object) {
                        if ($mvv_object instanceof StgteilabschnittModul) {
                            $modul_id = $mvv_object->modul_id;
                        }
                        $mvv_object_names[] = $mvv_object->getDisplayName();
                    }
                    $mvv_pathes[] = [$modul_id => $mvv_object_names];
                }
            }
        }
    }
    $mvv_pathes = array_map(function ($path) {
        return implode(' > ', reset($path));
    }, $mvv_pathes);
    $mvv_pathes = array_unique($mvv_pathes);
    sort($mvv_pathes);

    $sem_class = $course['sem_class'];
?>

<table>
<tr>
<th width="2cm"><?= _('Nr.') ?></th>
<td width="16cm"><?= htmlReady($course['veranstaltungsnummer'])?></td>
</tr>
<tr>
<th><?= _('Name') ?></th>
<td><?= htmlReady($course['name']) ?></td>
</tr>
<? if (!empty($course['untertitel'])): ?>
<tr>
<th><?= _('Untertitel') ?></th>
<td><?= htmlReady($course['untertitel']) ?></td>
</tr>
<? endif; ?>
<? if ($dozenten): ?>
<tr>
<th><?= _('Lehrende') ?></th>
<td>
<ul>
<? foreach ($dozenten as $dozent): ?>
<li><?= htmlReady($dozent) ?></li>
<? endforeach; ?>
</ul>
</td>
</tr>
<? endif; ?>
<? if ($mvv_pathes): ?>
<tr nobr="true">
<th><?= _('Module') ?></th>
<td>
<ul>
<? foreach ($mvv_pathes as $mvv_path) : ?>
<li><?= htmlReady($mvv_path) ?></li>
<? endforeach; ?>
</ul>
</td>
</tr>
<? endif; ?>
</table>
<br><br>

<? if (!empty($course['children'])) : ?>
    <?= $this->render_partial('my_courses/_exportcourse', [
        'course_collection' => $course['children'],
        'children'          => true,
        'gruppe'            => $course['gruppe'],
    ]) ?>
<? endif ?>
<? endforeach ?>
