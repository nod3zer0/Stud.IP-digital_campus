<?php
/**
 * @var array $institutes
 * @var string $current_institut_id
 */
?>
<? reset($institutes); ?>
<label>
    <?=_("Einrichtung:")?>
    <select name="choose_institut_id" class="nested-select">
        <? while (($institut_id = key($institutes)) && ($institute = current($institutes))): ?>
            <option value="<?= $institut_id ?>" <? if ($current_institut_id === $institut_id) echo 'selected'; ?> class="<?= $institute['is_fak'] ? 'nested-item-header' : 'nested-item' ?>">
                <?= htmlReady(my_substr($institute["name"] . ' (' . $institute["count"] . ')',0,100));?>
            </option>
            <? if ($institute['is_fak'] === 'all') : ?>
                <? $num_inst = $institute['num_inst']; for ($i = 0; $i < $num_inst; ++$i) : ?>
                    <?
                    $institute = next($institutes);
                    $institut_id = key($institutes);
                    ?>
                    <option value="<?= $institut_id?>" <?=($current_institut_id == $institut_id ? 'selected' : '')?> class="nested-item">
                        <?= htmlReady(my_substr($institute['name'] . ' (' . $institute['count'] . ')',0,100));?>
                    </option>
                <? endfor ?>
            <? endif ?>
            <? next($institutes); ?>
        <? endwhile; ?>
    </select>
</label>
