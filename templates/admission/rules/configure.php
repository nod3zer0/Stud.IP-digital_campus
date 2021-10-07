<label for="message" class="caption">
    <?= _('Nachricht bei fehlgeschlagener Anmeldung') ?>:
    <?= (mb_strpos($rule->getMessage(),'%s') ? tooltipicon(_("Die Zeichen %s sind ein Platzhalter für änderbare Bedingungen")) : '')?>
</label>
<textarea name="message" rows="4" cols="50"><?= htmlReady($rule->getMessage()) ?></textarea>
<br/>
<div id="toggle-date-container">
    <a href="#" id="toggle-date-link">
        <?= Icon::create('date') ?>
        <?= _('Gültigkeitszeitraum dieser Regel festlegen') ?>
    </a>
</div>
<div id="admissionrule-valid-date"<?= $rule->getStartTime() || $rule->getEndTime() ?
        '' : ' class="hidden-js"' ?>>
    <b><?= _('Hiermit verändern Sie nur, wann die in dieser Regel getroffenen ' .
        'Einstellungen gelten sollen, und nicht den generellen Anmeldezeitraum!') ?></b>
    <section class="form_group hgroup">
        <label>
            <?= _('von') ?>
            <input type="text" maxlength="16" name="start_date" class="size-s no-hint"
                   id="start_date" value="<?= $rule->getStartTime() ?
                date('d.m.Y H:i', $rule->getStartTime()) : '' ?>"
                placeholder="tt.mm.jjjj --:--" data-datetime-picker>
        </label>
        <label>
            <?= _('bis') ?>
            <input type="text" maxlength="16" name="end_date" class="size-s no-hint"
                   id="end_date" value="<?= $rule->getEndTime() ?
                        date('d.m.Y H:i', $rule->getEndTime()) : '' ?>"
                        placeholder="tt.mm.jjjj --:--" data-datetimepicker='{">":"#start_date"}'>
        </label>
        <script>
            $('#start_date').datetimepicker();
            $('#end_date').datetimepicker();
        </script>
    </section>
</div>
