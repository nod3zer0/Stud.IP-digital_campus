<?php
/**
 * @var CourseSet $courseset
 * @var array $flash
 * @var Admission_CoursesetController|Course_AdmissionController $controller
 * @var bool $instant_course_set_view
 * @var array $myInstitutes
 * @var array $selectedInstitutes
 * @var QuickSearch $instSearch
 * @var string $instTpl
 * @var string $coursesTpl
 * @var string $selectedSemester
 * @var AdmissionUserList[] $myUserlists
 */
use Studip\Button, Studip\LinkButton;

Helpbar::get()->addPlainText(_('Regeln'), _('Hier können Sie die Regeln, Eigenschaften und Zuordnungen des Anmeldesets bearbeiten.'));
Helpbar::get()->addPlainText(_('Info'), _('Sie können das Anmeldeset allen Einrichtungen zuordnen, an denen Sie mindestens Lehrendenrechte haben.'));
Helpbar::get()->addPlainText(_('Sichtbarkeit'), _('Alle Veranstaltungen der Einrichtungen, an denen Sie mindestens Lehrendenrechte haben, können zum Anmeldeset hinzugefügt werden.'));

// Load assigned course IDs.
$courseIds = $courseset ? $courseset->getCourses() : [];
// Load assigned user list IDs.
$userlistIds = $courseset ? $courseset->getUserlists() : [];

if (isset($flash['error'])) {
    echo MessageBox::error($flash['error']);
}
?>
<div class="hidden-alert" style="display:none">
    <?= MessageBox::info(_("Diese Daten sind noch nicht gespeichert."));?>
</div>
<h1><?= $courseset ? _('Anmeldeset bearbeiten') : _('Anmeldeset anlegen') ?></h1>
<form class="default" id="courseset-form" action="<?= $controller->url_for(!$instant_course_set_view ?
    'admission/courseset/save/' . ($courseset ? $courseset->getId() : '') :
    'course/admission/save_courseset/' . $courseset->getId()) ?>" method="post">
    <fieldset>
        <legend><?= _('Grunddaten') ?></legend>
        <label>
            <span class="required"><?= _('Name des Anmeldesets') ?></span>
            <input type="text" maxlength="255" name="name"
                   value="<?= $courseset ? htmlReady($courseset->getName()) : '' ?>"
                   required aria-required="true"/>
        </label>
        <? if (!$courseset || ($courseset->isUserAllowedToEdit($GLOBALS['user']->id) && !$instant_course_set_view)) : ?>
            <label for="private">
                <?= _('Sichtbarkeit') ?>
            </label>
            <input type="checkbox" id="private" name="private"<?= $courseset ? ($courseset->getPrivate() ? ' checked="checked"' : '') : 'checked' ?>/>
            <?= _('Dieses Anmeldeset soll nur für mich selbst und alle Administratoren sichtbar und benutzbar sein.') ?>
        <?  endif ?>
        <? if ($courseset) : ?>
        <label>
            <?= _('Besitzer des Anmeldesets') ?>
        </label>
        <div>
            <? $user = User::find($courseset->getUserId()) ?>
            <? if (isset($user)) : ?>
                <a target="_blank" href="<?= $controller->url_for('profile', ['username' => $user->username]) ?>" >
                    <?= htmlReady($user->getFullName()) ?> (<?= htmlReady($user->username) ?>)
                </a>
            <? else : ?>
                <?= _('unbekannt') ?>
            <? endif ?>
        </div>
        <? endif ;?>
        <label for="institutes">
            <span class="required"><?= _('Einrichtungszuordnung') ?></span>
        </label>
        <? if ($GLOBALS['perm']->have_perm('admin') || $GLOBALS['perm']->have_perm('dozent') && Config::get()->ALLOW_DOZENT_COURSESET_ADMIN) : ?>
            <div id="institutes">
            <?php if ($myInstitutes) { ?>
                <?php if ($instSearch) { ?>
                    <?= $instTpl ?>
                <?php } else { ?>
                    <?php foreach ($myInstitutes as $institute) { ?>
                        <?php if (count($myInstitutes) !== 1) { ?>
                    <input type="checkbox" name="institutes[]" value="<?= $institute['Institut_id'] ?>"
                        <?= !empty($selectedInstitutes[$institute['Institut_id']]) ? 'checked' : '' ?>
                        class="institute" onclick="STUDIP.Admission.getCourses(
                        '<?= $controller->url_for('admission/courseset/instcourses', $courseset ? $courseset->getId() : '') ?>')"/>
                        <?php } else { ?>
                    <input type="hidden" name="institutes[]" value="<?= $institute['Institut_id'] ?>"/>
                        <?php } ?>
                        <?= htmlReady($institute['Name']) ?>
                    <br/>
                    <?php } ?>
                <?php } ?>
            <?php } else { ?>
                <?php if ($instSearch) { ?>
                <div id="institutes">
                    <?= Icon::create('arr_2down', Icon::ROLE_SORT)->asImg([
                        'title' => _('Einrichtung hinzufügen'),
                        'alt' => _('Einrichtung hinzufügen'),
                        'onclick' => "STUDIP.Admission.updateInstitutes($('input[name=\"institute_id\"]').val(), '"  .$controller->url_for('admission/courseset/institutes',$courseset?$courseset->getId() : '') . "', '" . $controller->url_for('admission/courseset/instcourses',$courseset?$courseset->getId() : '') . "', 'add')"
                    ]) ?>
                    <?= $instSearch ?>
                    <?= Icon::create('search')->asImg(['title' => _("Suche starten")])?>
                </div>
                <i><?=  _('Sie haben noch keine Einrichtung ausgewählt. Benutzen Sie obige Suche, um dies zu tun.') ?></i>
                <?php } else { ?>
                <i><?=  _('Sie sind keiner Einrichtung zugeordnet.') ?></i>
                <?php } ?>
            <?php } ?>
            </div>
        <? else : ?>
            <? foreach (SimpleCollection::createFromArray($selectedInstitutes)->orderBy('Name') as $institute) : ?>
                <?= htmlReady($institute['Name']) ?>
                <br>
            <?  endforeach ?>
        <?  endif ?>
    </fieldset>
    <fieldset>
        <legend><?= _('Veranstaltungen') ?></legend>
        <? if (!$instant_course_set_view) : ?>
            <label>
                <?= _('Semester') ?>
                <select name="semester" onchange="STUDIP.Admission.getCourses('<?= $controller->url_for('admission/courseset/instcourses', $courseset ? $courseset->getId() : '') ?>')">
                    <?php foreach(array_reverse(Semester::getAll(), true) as $id => $semester) { ?>
                    <option value="<?= $id ?>"<?= $id === $selectedSemester ? ' selected' : '' ?>>
                        <?= htmlReady($semester->name) ?>
                    </option>
                    <?php } ?>
                </select>
            </label>
            <label>
                <?= _('Filter auf Name/Nummer/Lehrperson') ?><br>
                <input style="display:inline-block" type="text" onKeypress="if (event.which==13) return STUDIP.Admission.getCourses('<?= $controller->url_for('admission/courseset/instcourses', $courseset ? $courseset->getId() : '') ?>')"
                       value="<?= htmlReady($current_course_filter ?? '') ?>" name="course_filter" >
                <?= Icon::create('search')->asImg([
                    'title' => _("Veranstaltungen anzeigen"),
                    'onClick' => "return STUDIP.Admission.getCourses('" . $controller->url_for('admission/courseset/instcourses', $courseset ? $courseset->getId() : '') ."')"
                ]) ?>
            </label>
            <div id="instcourses">
            <?= $coursesTpl; ?>
            </div>
            <? if (count($courseIds)) : ?>
                <div>
                        <?= LinkButton::create(_('Ausgewählte Veranstaltungen konfigurieren'),
                            $controller->url_for('admission/courseset/configure_courses/' . $courseset->getId()),
                            ['data-dialog' => 'size=big', 'class' => 'autosave']
                            ); ?>
                        <? if ($num_applicants = $courseset->getNumApplicants()) :?>
                        <?= LinkButton::create(sprintf(_('Liste der Anmeldungen (%s Nutzer)'), $num_applicants),
                            $controller->url_for('admission/courseset/applications_list/' . $courseset->getId()),
                            ['data-dialog' => '', 'class' => 'autosave']
                            ); ?>
                        <?= LinkButton::create(_('Nachricht an alle Angemeldeten'),
                                $controller->url_for('admission/courseset/applicants_message/' . $courseset->getId()),
                                ['data-dialog' => '', 'class' => 'autosave']
                            ); ?>
                        <? endif ?>
                </div>
            <? endif ?>
        <? else :?>
            <? if (count($courseIds) > 100) :?>
                <?= sprintf(_("%s zugewiesene Veranstaltungen"), count($courseIds)) ?>
            <? else : ?>
            <?
            Course::findEachMany(function($c) {
                echo htmlReady($c->getFullname('number-name-semester'));
                echo '<br>';
            },
                $courseIds,
                'ORDER BY start_time,VeranstaltungsNummer,Name');
            ?>
            <? endif ?>
        <? endif ?>
    </fieldset>
    <fieldset>
        <legend><?= _('Anmelderegeln') ?></legend>
        <div id="rules">
            <?php if ($courseset) { ?>
            <div id="rulelist">
                <?php foreach ($courseset->getAdmissionRules() as $rule) { ?>
                    <?= $this->render_partial('admission/rule/save', ['rule' => $rule]) ?>
                <?php } ?>
            </div>
            <?php } else { ?>
            <span id="norules">
                <i><?= _('Sie haben noch keine Anmelderegeln festgelegt.') ?></i>
            </span>
            <br/>
            <?php } ?>
            <div style="clear: both;">
                    <?= LinkButton::create(_('Anmelderegel hinzufügen'),
                        $controller->url_for('admission/rule/select_type' . ($courseset ? '/'.$courseset->getId() : '')),
                        [
                            'onclick' => "return STUDIP.Admission.selectRuleType(this)"
                            ]
                        ); ?>
            </div>
        </div>
    </fieldset>
    <div class="hidden-alert" style="display:none">
        <?= MessageBox::info(_("Diese Daten sind noch nicht gespeichert."));?>
    </div>
    <fieldset>
        <legend><?= _('Weitere Daten') ?></legend>
   <? if (!$instant_course_set_view) : ?>

    <? if ($courseset && $courseset->getSeatDistributionTime()) :?>
        <label>
            <?= _('Personenlisten zuordnen') ?>
            </label>
            <?php if ($myUserlists) { ?>
                <?php
                foreach ($myUserlists as $list) {
                    $checked = '';
                    if (in_array($list->getId(), $userlistIds)) {
                        $checked = ' checked="checked"';
                    }
                ?>
                <input type="checkbox" name="userlists[]" value="<?= $list->getId() ?>"<?= $checked ?>/> <?= $list->getName() ?><br/>
                <?php } ?>

            <?php } else { ?>
                <i><?=  _('Sie haben noch keine Personenlisten angelegt.') ?></i>
            <?php
            }?>
            <div>
                    <?= LinkButton::create(_('Liste der Nutzer'),
                        $controller->url_for('admission/courseset/factored_users/' . $courseset->getId()),
                        ['data-dialog' => '']
                        ); ?>
            </div>
            <?php
            // Keep lists that were assigned by other users.
            foreach ($userlistIds as $list) {
                if (!in_array($list, array_keys($myUserlists))) {
            ?>
            <input type="hidden" name="userlists[]" value="<?= $list ?>"/>
            <?php
                }
            }
            ?>
        <? endif ?>
        <? endif ?>
        <label for="infotext">
            <?= _('Weitere Hinweise für die Teilnehmenden') ?>
        </label>
        <textarea cols="60" rows="3" name="infotext"><?= $courseset ? htmlReady($courseset->getInfoText()) : '' ?></textarea>
    </fieldset>

    <footer class="submit_wrapper" data-dialog-button>
        <?= CSRFProtection::tokenTag() ?>
        <?= Button::createAccept(_('Speichern'), 'submit',
            $instant_course_set_view ? ['data-dialog' => ''] : []) ?>
        <?php if (Request::option('is_copy')) : ?>
            <?= LinkButton::createCancel(_('Abbrechen'),
                URLHelper::getURL('dispatch.php/admission/courseset/delete/' . $courseset->getId(),
                ['really' => 1])) ?>
        <?php else : ?>
            <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admission/courseset')) ?>
        <?php endif ?>
    </footer>

</form>
<? if (Request::get('is_copy')) :?>
    <script>STUDIP.Admission.toggleNotSavedAlert();</script>
<? endif ?>
