<section class="contentbox course-statusgroups" data-sortable="<?=$controller->action_link('sorttools', ['order' => 1]) ?>">
<? if ($sem->tools): ?>
    <? foreach ($sem->tools as $tool): ?>
    <?php if (!$tool->getStudipModule()) continue; ?>
        <article class="draggable" id="plugin_<?= $tool->plugin_id ?>">
            <header>
                <span class="drag-handle"></span>
                <h1><?= htmlready($tool->getDisplayName()) ?></h1>
            </header>
        </article>
    <? endforeach ?>
<? endif ?>
</section>


