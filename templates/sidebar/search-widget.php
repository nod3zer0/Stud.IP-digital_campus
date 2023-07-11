<form action="<?= URLHelper::getLink($url) ?>"
      method="<?= $method ?>"
      <? if (isset($id)) printf('id="%s"', htmlReady($id)); ?>
      <?= $onsubmit ? 'onsubmit="'.htmlReady($onsubmit).'"' : '' ?>
      class="sidebar-search">
<? foreach ($url_params as $key => $value): ?>
    <?=addHiddenFields($key,$value)?>
<? endforeach; ?>
    <ul class="needles">
    <? foreach ($needles as $needle): ?>
        <li <? if ($needle['quick_search'] && $needle['quick_search']->hasExtendedLayout()) echo 'class="extendedLayout" id="' . $needle['quick_search']->getId() . '_frame"'; ?>>
            <div class="input-group files-search">
                <label for="needle-<?= $hash = md5($url . '|' . $needle['name']) ?>" <? if ($needle['placeholder']) echo 'style="display:none;"'; ?>>
                    <?= htmlReady($needle['label']) ?>
                </label>
                <? if ($needle['quick_search']): ?>
                    <?= $needle['quick_search']->render() ?>
                <? else: ?>
                <input type="text" id="needle-<?= $hash ?>"
                       name="<?= htmlReady($needle['name']) ?>"
                       value="<?= htmlReady($needle['value']) ?>"
                       <? if ($needle['placeholder']) printf('placeholder="%s"', htmlReady($needle['label'])); ?>
                       <?= arrayToHtmlAttributes($needle['attributes']) ?>>
                <? endif; ?>
                <? if ($reset_link): ?>
                    <a class="reset-search" href="<?= $reset_link ?>" tabindex="0" role="button"
                       title="<?= _('Suche zurücksetzen') ?>">
                        <?= Icon::create('decline')->asImg(20) ?>
                    </a>
                <? endif; ?>
                <button type="submit" class="submit-search<?= $reset_link ? ' is-executed' : '' ?>"
                        title="<?= _('Suche ausführen') ?>">
                    <?= Icon::create('search')->asImg(20) ?>
                </button>
        </li>
    <? endforeach; ?>
    </ul>
<? if (!empty($filters)): ?>
    <ul class="filters">
    <? foreach ($filters as $key => $label): ?>
        <input type="hidden" name="<?= htmlReady($key) ?>" value="0" id="<?= md5($url . $key) ?>">
        <label>
            <input type="checkbox" name="<?= htmlReady($key) ?>" value="1"
                   <? if (!$has_data || Request::int($key)) echo 'checked'; ?>
                   data-deactivates="#<?= md5($url . $key) ?>">
            <?= htmlReady($label) ?>
        </label>
    <? endforeach; ?>
    </ul>
<? endif; ?>
<? if (!empty($quick_search)): ?>
<script>
(function ($) {
<? foreach ($quick_search as $needle): ?>
    STUDIP.QuickSearch.autocomplete('needle-<?= md5($url . '|' . $needle['name']) ?>', '<?= $url ?>');
<? endforeach; ?>
}(jQuery));
</script>
<? endif; ?>
</form>
