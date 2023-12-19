<?php
/**
 * @var Institute_ExternController $controller
 * @var array $config_types
 * @var ExternPageConfig[] $configs
 */

if (count($configs) == 0)  :
    PageLayout::postInfo(_('Es wurde noch keine externe Seite angelegt.'),
        [
            sprintf(_('Um eine neue externe Seite anzulegen, klicken sie %shier%s.'),
                '<a href="' . $controller->new() . '" data-dialog="size=870x500">',
                '</a>')
        ]);
else : ?>
<form id="extern-page-index" action="#" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <section class="contentbox">
        <header>
            <h1>
                <?= _('Externe Seiten') ?>
            </h1>
        </header>
        <? foreach ($config_types as $type_id => $config_type): ?>
            <? if ($configs[$type_id]) : ?>
                <article id="<?= $type_id ?>" <? if (Request::option('open_type') === $type_id) echo 'class="open"'; ?>>
                    <header>
                        <h1>
                            <a href="<?= URLHelper::getLink('?#' . $type_id)?>">
                                <?= htmlReady($config_type['name']) ?>
                                (<?= count($configs[$type_id]) ?>)
                            </a>
                        </h1>
                    </header>
                    <section>
                        <table class="default sortable-table">
                            <?= $this->render_partial('institute/extern/_table-header.php') ?>
                            <? foreach ($configs[$type_id] as $config) : ?>
                                <?= $this->render_partial('institute/extern/_table-row.php', ['config' => $config]) ?>
                            <? endforeach ?>
                        </table>
                    </section>
                </article>
            <? endif ?>
        <? endforeach ?>
    </section>
</form>
<?php endif ?>
