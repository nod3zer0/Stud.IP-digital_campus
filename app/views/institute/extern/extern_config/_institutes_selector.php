<?php
/**
 * @var ExternPageCourses $page
 */
?>

<? $institutes = $page->getInstitutes() ?>
<? if (count($institutes) > 1) : ?>
    <label>
        <?= _('Einrichtungen') ?>
        <select name="institutes[]" class="nested-select" multiple>
            <? foreach ($institutes as $institute) : ?>
                <option
                    class="<?= $institute['is_fak'] ? 'nested-item-header' : 'nested-item' ?>"
                    value="<?= htmlReady($institute['Institut_id']) ?>"
                    <?= in_array($institute['Institut_id'], (array) $page->institutes) ? 'selected' : '' ?>>
                    <?= htmlReady($institute['Name']) ?>
                </option>
            <? endforeach ?>
        </select>
    </label>
<? endif ?>
