<?php
/**
 * @var AdmissionRule $rule
 * @var Admission_CoursesetController $controller
 */
?>
<div class="hover_box admissionrule" id="rule_<?= $rule->getId() ?>">
    <div id="rule_data_<?= $rule->getId() ?>" class="col-3" style="margin-top: unset">
        <?= $rule->toString() ?>
        <input type="hidden" name="rules[]" value="<?= htmlReady(ObjectBuilder::exportAsJson($rule)) ?>"/>
    </div>
    <div class="action_icons col-1" id="rule_actions_<?= $rule->getId() ?>" style="margin-top: unset">
        <a href="#" onclick="return STUDIP.Admission.configureRule('<?= get_class($rule) ?>', '<?=
        $controller->url_for('admission/rule/configure', get_class($rule), $rule->getId()) ?>', '<?=
        $rule->getId() ?>')">
            <?= Icon::create('edit', 'clickable')->asImg(); ?></a>
        <a href="javascript:STUDIP.Admission.removeRule('rule_<?= $rule->getId() ?>', 'rules')"
           data-confirm="<?= _('Soll die Anmelderegel wirklich gelöscht werden?') ?>">
            <?= Icon::create('trash', 'clickable')->asImg(); ?></a>
    </div>
</div>
