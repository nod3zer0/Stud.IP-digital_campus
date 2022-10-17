<?php
/**
 * Template documentation:
 *
 * @param Array $criteria: A search criteria with the following structure:
 *     [
 *         'name' => The criteria's internal name.
 *         'title' => The title of the criteria.
 *         'enabled' => Whether this criteria is enabled (true) or not.
 *         'value' => The value of the search criteria.
 *             For range search criteria the values are split by ':'.
 *     ]
 */
?>
<li class="item">
    <label class="undecorated">
        <input type="checkbox" class="special-item-switch" value="1"
               title="<?= _('Kriterium ausgewählt'); ?>"
               name="<?= htmlReady($criteria['name'] . '_enabled')?>"
               <?= !empty($criteria['enabled']) ? 'checked' : ''?>>
        <?= htmlReady($criteria['title']) ?>
    </label>
    <div class="hgroup special-item-content">
        <label class="undecorated">
            <?= _('von') ?>
            <input type="number"
                   name="<?= htmlReady($criteria['name'])?>_min"
                   value="<?= empty($criteria['value'][0])?'':intval($criteria['value'][0])?>">
        </label>
        <label class="undecorated">
            <?= _('bis') ?>
            <input type="number"
                   name="<?= htmlReady($criteria['name'])?>_max"
                   value="<?= empty($criteria['value'][1])?'':intval($criteria['value'][1])?>">
        </label>
    </div>
</li>
