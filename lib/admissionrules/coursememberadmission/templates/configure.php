<h3><?= htmlReady($rule->getName()) ?></h3>

<?= $tpl ?>

<input type="hidden" name="search_sem_qs_choose" value="title_lecturer_number">

<? foreach ($courses as $course) : ?>
    <input type="hidden" name="mandatory_course_id_old[]" value="<?= htmlReady($course->id) ?>">

    <label class="caption">
        <?= _('Mitgliedschaft in folgender Veranstaltung überprüfen') ?>:
    </label>
    <p>
        <?=htmlReady($course->getFullName('number-name-semester'));?>
        <a href="<?=URLHelper::getLink('dispatch.php/course/details/index/' . $course->id) ?>"  data-dialog>
            <?= Icon::create('info-circle')->asImg([
                'title' =>_('Veranstaltungsdetails aufrufen')
            ]) ?>
        </a>
    </p>
<? endforeach ?>

<label class="caption">
    <?= _('Modus') ?>:
</label>
<div>
    <label>
        <input type="radio" name="modus" value="0" <? if ($rule->modus == CourseMemberAdmission::MODE_MUST_BE_IN_COURSES) echo 'checked'; ?>>
        <?=_("Mitgliedschaft ist in mindestens einer dieser Veranstaltungen notwendig")?>
    </label>
    <label>
        <input type="radio" name="modus" value="1" <? if ($rule->modus == CourseMemberAdmission::MODE_MAY_NOT_BE_IN_COURSES) echo 'checked'; ?>>
        <?=_("Mitgliedschaft ist in keiner dieser Veranstaltungen erlaubt")?>
    </label>
</div>

<label class="caption">
    <?= _('Veranstaltung suchen') ?>:
</label>

<div style="display:inline-block">

    <?=
    QuickSearch::get("mandatory_course_id", new SeminarSearch('number-name-lecturer'))
        ->fireJSFunctionOnSelect('addcourse')
        ->render();
    ?>
    <?= Semester::getSemesterSelector(
        ['name' => 'search_sem_sem'],
        Semester::getIndexById($_SESSION['_default_sem'], false, !$GLOBALS['perm']->have_perm('admin')),
        'key',
        false
    )?>
    <br><br>
    <ul>
    <? foreach ($courses as $course) : ?>
        <li>
            <input type="hidden" id="<?= htmlReady($course->id) ?>"
                   name="courses_to_add[<?= htmlReady($course->id) ?>]"
                   value="<?= htmlReady($course->name) ?>">
            <span><?= htmlReady($course->name) ?></span>
            <a href="#" onclick="return removecourse('<?= htmlReady($course->id) ?>')">
                <?= Icon::create('trash') ?>
            </a>
        </li>
    <? endforeach ?>
    </ul>
</div>

<script>
    $('#ruleform input[name="modus"]').on('change', function () {
        const message = <?= json_encode([
            _('Sie sind nicht in der Veranstaltung "%s" eingetragen.'),
            _('Sie dürfen nicht in der Veranstaltung "%s" eingetragen sein.'),
        ]) ?>;
        console.log(this, this.value);
        $('#ruleform textarea').text(message[this.value]);
    }).filter(':checked').change();

    function addcourse(id, title) {

        if ($('input[name="courses_to_add[' + id + ']"]').length === 0) {
            var wrapper = $('<li>');
            var input = $('<input>')
                .attr('id', id)
                .attr('type', 'hidden')
                .attr('name', 'courses_to_add['+ id + ']')
                .attr('value', title);
            wrapper.append(input);

            var trash = $('<input>')
                .attr('type', 'image')
                .attr('src', STUDIP.ASSETS_URL + 'images/icons/blue/trash.svg')
                .attr('name', 'remove_[' + id + ']')
                .attr('value', '1')
                .attr('onclick', "return removecourse('" + id + "')");

            var icon = $('<a>')
                .attr('onclick', "return removecourse('" + id + "')")
                .attr('href', '#');
            var img = $('<img>')
                .attr('src', STUDIP.ASSETS_URL + 'images/icons/blue/trash.svg')
                .attr('width', '16px')
                .attr('height', '16px');
            icon.append(img);

            var nametext = $('<span>')
                .html(title)
                .text();
            wrapper.append(nametext);
            wrapper.append(icon);

            $('input[name=mandatory_course_id_parameter]').parent().find('ul').append(wrapper);
        }

    }

    function removecourse(id) {
        $('input#' + id).parent().remove();
        return false;
    }

</script>
