<?php
/**
 * @var string $content_for_layout
 * @var string|null $icon_url
 * @var string $title
 * @var string|null $admin_url
 * @var string|null $admin_title
 */
?>
<? if ($content_for_layout != ''): ?>
    <table class="index_box">
        <tr>
            <td class="table_header_bold" style="font-weight: bold;">
                <? if (isset($icon_url)): ?>
                    <?= Assets::img($icon_url, ['class' => 'middle']) ?>
                <? endif ?>
                <?= htmlReady($title) ?>
            </td>

            <td class="table_header_bold" style="text-align: right;">
            <? if (isset($admin_url)): ?>
                <a href="<?= URLHelper::getLink($admin_url) ?>" title="<?= htmlReady($admin_title ?? _('Administration')) ?>">
                    <?= Icon::create('admin', Icon::ROLE_INFO_ALT)->asImg([
                        'alt' => $admin_title ??  ('Administration'),
                    ]) ?>
                </a>
            <? endif ?>
            </td>
        </tr>

        <tr>
            <td class="index_box_cell" colspan="2">
                <?= $content_for_layout ?>
            </td>
        </tr>
    </table>
<? endif ?>
