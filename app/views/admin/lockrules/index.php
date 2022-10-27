<?php
# Lifter010: TODO

/**
 * @var Admin_LockrulesController $controller
 * @var array $rule_type_names
 * @var string $lock_rule_type
 * @var LockRule[] $lock_rules
 */
?>

<form method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default">
        <caption>
            <?= _('Sperrebenen für den Bereich:') ?> <?= $rule_type_names[$lock_rule_type]; ?>
        </caption>
        <colgroup>
            <col style="width: 30%">
            <col style="width: 50%">
            <col style="width: 20%">
        </colgroup>
        <thead>
        <tr>
            <th><?= _('Name') ?></th>
            <th><?= _('Beschreibung') ?></th>
            <th><?= _('Besitzer') ?></th>
            <th><?= _('Aktionen') ?></th>
        </tr>
        </thead>
        <tbody>
        <? if(count($lock_rules) > 0) : ?>
            <? foreach ($lock_rules as $rule): ?>
                <tr>
                    <td>
                        <?= htmlReady($rule->name) ?>
                    </td>
                    <td>
                        <?= htmlReady(my_substr($rule->description, 0, 100)) ?>
                    </td>
                    <td>
                        <?= htmlReady($rule->user_id ? get_fullname($rule->user_id) : '') ?>
                    </td>
                    <td class="actions">
                        <a href="<?= $controller->url_for('admin/lockrules/edit/' . $rule->lock_id) ?>">
                            <?= Icon::create('edit', Icon::ROLE_CLICKABLE, ['title' => _('Diese Regel bearbeiten')])->asImg() ?>
                        </a>

                        <?
                        if ($rule->getUsage()) :?>
                            <? $msg = sprintf(_('Sie beabsichtigen die Ebene %s zu löschen. Diese Ebene wird von %s Objekten benutzt. Soll sie trotzdem gelöscht werden?'),
                                htmlReady($rule->name), htmlReady($rule->getUsage())) ?>
                        <? else : ?>
                            <? $msg = sprintf(_('Möchten Sie die Ebene %s löschen?'), $rule->name) ?>
                        <? endif ?>
                        <?= Icon::create('trash', Icon::ROLE_CLICKABLE, [
                                'title' => _('Diese Regel löschen'),
                                'style' => 'vertical-align: middle'
                            ])->asInput(['data-confirm'=>$msg,'formaction'=>$controller->url_for('admin/lockrules/delete/'.$rule->lock_id)]) ?>
                    </td>
                </tr>
            <? endforeach; ?>
        <? else :?>
            <tr>
                <td colspan="4" style="text-align: center">
                    <?=_('Keine Sperrebenen vorhanden')?>
                </td>
            </tr>
        <? endif?>
        </tbody>
    </table>
</form>
