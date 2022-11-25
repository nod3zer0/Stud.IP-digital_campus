<?
$inputs = [];
$allinputs = $form->getAllInputs();
$required_inputs = [];
foreach ($allinputs as $input) {
    foreach ($input->getAllInputNames() as $name) {
        $inputs[$name] = $input->getValue();
    }

    if ($input->required) {
        $required_inputs[] = $input->getName();
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
      novalidate
      id="<?= htmlReady($form_id) ?>"
      data-inputs="<?= htmlReady(json_encode($inputs)) ?>"
      data-required="<?= htmlReady(json_encode($required_inputs)) ?>"
      class="default studipform<?= $form->isCollapsable() ? ' collapsable' : '' ?>">

    <?= CSRFProtection::tokenTag(['ref' => 'securityToken']) ?>

    <article aria-live="assertive"
             class="validation_notes studip"
             v-if="(STUDIPFORM_REQUIRED.length > 0) || STUDIPFORM_DISPLAYVALIDATION">
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
                <li v-for="note in STUDIPFORM_VALIDATIONNOTES" :aria-describedby="note.describedby">{{ note.name + ": " + note.description }}</li>
            </ul>
        </div>
    </article>

    <div aria-live="polite">
    <? foreach ($form->getParts() as $part) : ?>
        <?= $part->renderWithCondition() ?>
    <? endforeach ?>
    </div>
</form>

<footer data-dialog-button class="formbuilderfooter">
    <?= \Studip\Button::create(_('Speichern'), null, ['form' => $form_id]) ?>
</footer>
