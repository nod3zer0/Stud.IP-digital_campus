<div class="helpbar-container">
    <?= SkipLinks::addIndex(_('Tipps & Hilfe'), 'helpbar_icon', 920) ?>
    <a id="helpbar_icon" href="#" class="helpbar-toggler" data-toggles=".helpbar" title="<?= _('Hilfelasche anzeigen/verstecken') ?>">
        <?= Icon::create('question-circle')->asImg(24, ['alt' => '']) ?>
    </a>
    <div class="helpbar" <? if (!$open) echo 'style="display: none"'; ?>>

        <h2 class="helpbar-title">
            <?= _('Tipps & Hilfe') ?>
            <a href="#" class="helpbar-toggler" data-toggles=".helpbar" aria-hidden="true" title="<?= _('Hilfelasche verstecken') ?>">
                <?= Icon::create('decline-circle', Icon::ROLE_INFO_ALT)->asImg(24, ['alt' => '']) ?>
            </a>
        </h2>
        <ul class="helpbar-widgets">
        <? foreach ($widgets as $index => $widget): ?>
            <li>
            <? if ($widget->icon): ?>
                <?= is_string($widget->icon) ? Assets::img($widget->icon, ['class' => 'helpbar-widget-icon']) : $widget->icon->asImg(['class' => 'helpbar-widget-icon', 'alt' => '']) ?>
            <? endif; ?>
                <?= $widget->render(['base_class' => 'helpbar'])?>
                <div class="helpbar-widget-admin-icons">
                <? if ($widget->edit_link): ?>
                    <a href="<?=$widget->edit_link?>" data-dialog="size=auto;reload-on-close">
                    <?= Icon::create('edit', 'info_alt')->asImg(['title' => _('Hilfe-Text bearbeiten'), 'alt' => '']) ?></a>
                <? endif; ?>
                <? if ($widget->delete_link): ?>
                    <a href="<?=$widget->delete_link?>" data-dialog="size=auto;reload-on-close">
                    <?= Icon::create('trash', 'info_alt')->asImg(['title' => _('Hilfe-Text löschen'), 'alt' => '']) ?></a>
                <? endif; ?>
                <? if ($widget->add_link): ?>
                    <a href="<?=$widget->add_link?>" data-dialog="size=auto;reload-on-close">
                    <?= Icon::create('add', 'info_alt')->asImg(['title' => _('Neuer Hilfe-Text'), 'alt' => '']) ?></a>
                <? endif; ?>
                </div>
            </li>
        <? endforeach; ?>
        </ul>
    </div>
</div>
<? if ($tour_data['active_tour_id']) : ?>
    <script>
        STUDIP.Tour.init('<?=$tour_data['active_tour_id']?>', '<?=$tour_data['active_tour_step_nr']?>')
    </script>
<? endif ?>
