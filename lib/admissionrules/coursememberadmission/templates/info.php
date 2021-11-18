<? if ($rule->getValidityPeriod()): ?>
    <?= $rule->getValidityPeriod() ?><br>
<? endif; ?>

<? if ($modus == CourseMemberAdmission::MODE_MAY_NOT_BE_IN_COURSES): ?>
    <?= _('Die Anmeldung ist für Teilnehmende einer der folgenden Veranstaltungen nicht erlaubt:') ?>
<? elseif ($modus == CourseMemberAdmission::MODE_MUST_BE_IN_COURSES): ?>
    <?= _('Die Anmeldung ist nur für Teilnehmende mindestens einer der folgenden Veranstaltungen erlaubt:') ?>
<? endif; ?>
<br>

<ul>
<? foreach ($courses as $course): ?>
    <li>
        <strong><?= htmlReady($course->getFullname('number-name-semester')) ?></strong>
        <a href="<?= URLHelper::getLink('dispatch.php/course/details/index/' . $course->id) ?>"  data-dialog>
            <?= Icon::create('info-circle')->asImg([
                'title' => _('Veranstaltungsdetails aufrufen')
            ]) ?>
        </a>
    </li>
<? endforeach; ?>
</ul>
