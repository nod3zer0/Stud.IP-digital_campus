<?php
/**
 * @var string $name
 * @var string $id
 * @var AuxLockRule $rule
 */
?>

<section>
<? if (!empty($required)) : ?>
    <span class="required">
        <?= htmlReady($name) ?>
    </span>
<? else: ?>
    <?= htmlReady($name) ?>
<? endif ?>

    <div class="hgroup">
        <label class="col-2">
            <?= _('Sortierung') ?>
            <input id="order_<?= htmlReady($id) ?>" min="0" type="number" size="3" name="order[<?= htmlReady($id) ?>]"
                   value="<?= ($rule->sorting[$id] ?? 0) ?>">
        </label>
        <label class="col-2">
            <input type="checkbox"
                   name="fields[]"
                   value="<?= htmlReady($id) ?>"
                    <? if ($rule->attributes->contains($id)) echo 'checked'; ?>>
            <?= _('Aktivieren') ?>
        </label>
    <? if (!empty($institution)) : ?>
        <label class="col-1">
            <?= htmlReady($institution->name )?>
        </label>
    <? endif; ?>
    </div>
</section>
