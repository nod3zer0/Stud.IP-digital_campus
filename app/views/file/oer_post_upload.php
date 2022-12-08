<?php
if (!isset($selected_oer_upload)) {
    $selected_oer_upload = 0;
}
?>
<form action="<?= $controller->link_for('file/oer_post_upload/', $file_ref_id)?>"
      method="post" class="default" data-dialog="reload-on-close">
    <?= CSRFProtection::tokenTag() ?>

    <div id="select_oer_upload_info">
        <span><?= _('Wenn Sie möchten, können Sie die hochgeladene Datei für den OER-Campus bereitstellen.') ?></span>
        <span><?= sprintf(_('Falls Sie die Datei zu einem späteren Zeitpunkt bereitstellen möchten,
            wird Ihnen am Ende des Semesters (%s) eine Nachricht zugeschickt.'), $semester_ende) ?></span>
    </div>
    <fieldset class="select_oer_upload">
        <input type="radio" name="oer_upload" id="oer-upload-no" value="0"
            <? if (0 == $selected_oer_upload) echo 'checked'; ?>>
        <label for="oer-upload-no">
            <div class="icon">
                <?= Icon::create('decline')->asImg(32) ?>
            </div>
            <div class="text">
                <?= _('Nicht für den OER-Campus bereitstellen.') ?>
            </div>
            <?= Icon::create('arr_1down')->asImg(24, ['class' => 'arrow']) ?>
            <?= Icon::create('check-circle')->asImg(32, ['class' => 'check']) ?>
        </label>
        <div class="oer_upload_description">
            <div class="description">
                <?= _('Ich möchte die hochgeladene Datei jetzt nicht im OER-Campus bereitstellen.
                Ich habe jedoch später jederzeit die Möglichkeit dazu.') ?>
            </div>
        </div>

        <input type="radio" name="oer_upload" id="oer-upload-yes" value="1"
            <? if (1 == $selected_oer_upload) echo 'checked'; ?>>
        <label for="oer-upload-yes">
            <div class="icon">
                <?= Icon::create('accept')->asImg(32) ?>
            </div>
            <div class="text">
                <?= _('Jetzt für den OER-Campus bereitstellen.') ?>
            </div>
            <?= Icon::create('arr_1down')->asImg(24, ['class' => 'arrow']) ?>
            <?= Icon::create('check-circle')->asImg(32, ['class' => 'check']) ?>
        </label>
        <div class="oer_upload_description">
            <div class="description">
                <?= _('Die Datei wird direkt im OER-Campus bereitgestellt. Sie ist dann neben vielen weiteren freien Lernmaterialien an allen Stud.IP-Standorten mit aktiviertem OER-Campus sichtbar.') ?>
            </div>
        </div>

        <input type="radio" name="oer_upload" id="oer-upload-later" value="2"
            <? if (2 == $selected_oer_upload) echo 'checked'; ?>>
        <label for="oer-upload-later">
            <div class="icon">
                <?= Icon::create('date')->asImg(32) ?>
            </div>
            <div class="text">
                <?= _('Zu einem späteren Zeitpunkt für den OER-Campus bereitstellen.') ?>
            </div>
            <?= Icon::create('arr_1down')->asImg(24, ['class' => 'arrow']) ?>
            <?= Icon::create('check-circle')->asImg(32, ['class' => 'check']) ?>
        </label>
        <div class="oer_upload_description">
            <div class="description">
                <?= sprintf(_('Ich möchte am Semesterende (%s) daran erinnert werden, diese Datei gegebenenfalls im OER-Campus bereitzustellen.'), $semester_ende) ?>
            </div>
        </div>
    </fieldset>

    <input type="hidden" name="redirect_to_files" value="redirect_to_files">
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'))?>
        <?= Studip\Button::createCancel(_('Abbrechen'))?>

    </footer>
</form>
