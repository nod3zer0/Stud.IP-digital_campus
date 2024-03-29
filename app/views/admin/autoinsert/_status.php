<?php
/**
 * @var Admin_AutoinsertController $controller
 * @var array $domains
 * @var array $auto_sems
 * @var string $status
 */
?>
<td>
    <? foreach ($domains as $domain) : ?>
        <div>

            <? if (isset($auto_sem['status'][$domain['id']]) && in_array($status, $auto_sem['status'][$domain['id']])) : ?>
                <a href="<?= $controller->url_for('admin/autoinsert/edit/' . $auto_sem['seminar_id'], ['domain_id' => $domain['id'], 'status' => $status, 'remove' => true]) ?>">
                    <?= Icon::create('checkbox-checked') ?>
                </a>
            <? else : ?>
                <a href="<?= $controller->url_for('admin/autoinsert/edit/' . $auto_sem['seminar_id'], ['domain_id' => $domain['id'], 'status' => $status]) ?>">
                    <?= Icon::create('checkbox-unchecked') ?>
                </a>
            <? endif ?>
            <?= htmlReady($domain['name']) ?></div>
    <? endforeach ?>

</td>
