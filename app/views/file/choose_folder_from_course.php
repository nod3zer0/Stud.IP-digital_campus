<?php
$options = array_filter([
    'to_plugin'   => Request::get('to_plugin'),
    'from_plugin' => Request::get('from_plugin'),
    'range_type'  => Request::get('range_type'),
    'fileref_id'  => Request::getArray('fileref_id'),
    'isfolder'    => Request::get('isfolder'),
    'copymode'    => Request::get('copymode'),
], function ($value) {
    return $value !== null;
});
?>

<script>
jQuery(function ($) {
    $('#folderchooser_course_search select option').on('click', function () {
    	$('#folderchooser_course_search').submit();
    });
});
</script>

<? if ($GLOBALS['perm']->have_perm('admin')) : ?>
<form id="folderchooser_course_search" method="post"
      action="<?= $controller->link_for('file/choose_folder_from_course') ?>"
      data-dialog>
    <?= QuickSearch::get('course_id', new StandardSearch('AnySeminar_id'))
        ->fireJSFunctionOnSelect("function () { jQuery('#folderchooser_course_search').submit(); }")
        ->setInputStyle('width: calc(100% - 40px); margin: 20px;')
        ->render() ?>
<? else : ?>
<form action="#" method="post" data-dialog>
    <table class="default sortable-table">
        <thead>
            <tr>
                <th><?= _('Bild') ?></th>
                <th data-sort="text"><?= _('Name') ?></th>
                <th data-sort="htmldata"><?= _('Semester') ?></th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($courses as $course) : ?>
            <tr>
                <td>
                    <!-- neu -->
                    <button formaction="<?= $controller->link_for('file/choose_folder_from_course') ?>"
                            name="course_id"
                            value="<?= htmlReady($course->id) ?>"
                            class="undecorated">
                        <?= CourseAvatar::getAvatar($course->id)->getImageTag(Avatar::MEDIUM, ['style' => 'width: 20px; height: 20px;']) ?>
                    </button>
                </td>
                <td data-sort-value="<?= Semester::find($course->id)->beginn ?>">
                    <!-- neu -->
                    <button formaction="<?= $controller->link_for('file/choose_folder_from_course') ?>"
                            name="course_id"
                            value="<?= htmlReady($course->id) ?>"
                            class="undecorated">
                        <?= htmlReady($course->getFullname()) ?>
                    </button>
                </td>
                <td data-sort-value="<?= htmlReady($course->getTextualSemester()) ?>">
                    <?= htmlReady($course->getTextualSemester()) ?>
                </td>
            </tr>
        <? endforeach; ?>
        </tbody>
    </table>
<? endif; ?>

<? foreach ($options as $key => $value): ?>
    <?= addHiddenFields($key, $value) ?>
<? endforeach; ?>

    <footer data-dialog-button>
        <?= Studip\Button::create(_('Zurück'), [
            'formaction' => $controller->url_for('file/choose_destination/' . $options['copymode']),
            'data-dialog' => 'size=auto'
        ]) ?>
    </footer>
</form>
