<?
$inputs = [];
$allinputs = $form->getAllInputs();
$required_inputs = [];
$server_validation = false;
foreach ($allinputs as $input) {
    foreach ($input->getAllInputNames() as $name) {
        $inputs[$name] = $input->getValue();
    }
    if ($input->required) {
        $required_inputs[] = $input->getName();
    }
    if ($input->hasValidation()) {
        $server_validation = true;
    }
}
$form_id = md5(uniqid());
?><form v-cloak
      method="post"
      <? if (!$form->isAutoStoring()) : ?>
          action="<?= htmlReady($form->getURL()) ?>"
      <? else : ?>
          data-autosave="<?= htmlReady($_SERVER['REQUEST_URI']) ?>"
          data-url="<?= htmlReady($form->getURL()) ?>"
      <? endif ?>
      @submit="submit"
      @cancel=""
      novalidate
      <?= $form->getDataSecure() ? 'data-secure' : '' ?>
      id="<?= htmlReady($form_id) ?>"
      data-inputs="<?= htmlReady(json_encode($inputs)) ?>"
      data-debugmode="<?= htmlReady(json_encode($form->getDebugMode())) ?>"
      data-required="<?= htmlReady(json_encode($required_inputs)) ?>"
      data-server_validation="<?= $server_validation ? 1 : 0?>"
      class="default studipform<?= $form->isCollapsable() ? ' collapsable' : '' ?>">

    <?= CSRFProtection::tokenTag(['ref' => 'securityToken']) ?>

    <article aria-live="assertive"
             class="validation_notes studip"
             v-if="STUDIPFORM_REQUIRED.length > 0 || STUDIPFORM_VALIDATIONNOTES.length > 0">
        <header>
            <h1>
                <?= Icon::create('info-circle', Icon::ROLE_INFO)->asImg(17, ['class' => "text-bottom validation_notes_icon"]) ?>
                <?= _('Hinweise zum Ausfüllen des Formulars') ?>
            </h1>
        </header>
        <div class="required_note" v-if="STUDIPFORM_REQUIRED.length > 0">
            <div aria-hidden="true">
                <?= _('Pflichtfelder sind mit Sternchen gekennzeichnet.') ?>
            </div>
            <div class="sr-only">
                <?= _('Dieses Formular enthält Pflichtfelder.') ?>
            </div>

        </div>
        <div v-if="STUDIPFORM_DISPLAYVALIDATION && (STUDIPFORM_VALIDATIONNOTES.length > 0)">
            <?= _('Folgende Angaben müssen korrigiert werden, um das Formular abschicken zu können:') ?>
            <ul>
                <li v-for="note in ordererValidationNotes" :aria-describedby="note.describedby">{{ note.label.trim() + ": " + note.description }}</li>
            </ul>
        </div>
    </article>

    <div aria-live="polite">
    <? foreach ($form->getParts() as $part) : ?>
        <?= $part->renderWithCondition() ?>
    <? endforeach ?>
    </div>
    <? if (!Request::isDialog()) : ?>
        <footer>
            <?= \Studip\Button::createAccept($form->getSaveButtonText(), $form->getSaveButtonName(), ['form' => $form_id]) ?>
            <?= \Studip\LinkButton::createCancel($form->getCancelButtonText(), $form->getCancelButtonName()) ?>
        </footer>
    <? endif ?>
</form>
<? if (Request::isDialog()) : ?>
    <footer data-dialog-button>
        <?= \Studip\Button::create($form->getSaveButtonText(), $form->getSaveButtonName(), ['form' => $form_id]) ?>
    </footer>
<? endif ?>
