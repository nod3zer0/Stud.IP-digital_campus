<? if (!$stepnumber) : ?>
    <fieldset>
<? endif ?>
    <legend><?= _('Studienbereiche') ?></legend>
    <div id="assigned">
        <h2>
        <span class="required">
            <?= _('Bereits zugewiesen') ?>
        </span>
        </h2>
        <ul class="css-tree">
            <li class="sem-tree-assigned-root keep-node" data-id="root"<?=
            $assigned ? ' class="hidden-no-js hidden-js"' : '' ?>>
                <?= htmlReady(Config::get()->UNI_NAME_CLEAN) ?>
                <ul>
                    <?php foreach ($assigned as $element) : ?>
                        <?= $this->render_partial('studyareas/_assigned_node',
                            ['element' => $element, 'studyareas' => $values['studyareas']]) ?>
                    <?php endforeach ?>
                </ul>
            </li>
        </ul>
    </div>
    <? if (empty($values['locked'])) : ?>
    <div id="studyareas" data-ajax-url="<?= $ajax_url ?>"
         data-forward-url="<?= $no_js_url ?>"
         data-no-search-result="<?= _('Es wurde kein Suchergebnis gefunden.') ?>">
        <h2><?= _('Alle Studienbereiche') ?></h2>
        <div>
            <input style="width:auto" type="text" size="40" name="search" id="sem-tree-search">
            <span id="sem-tree-search-start">
                <?= Icon::create('search')->asInput([
                    'name'    => 'start_search',
                    'onclick' => 'return STUDIP.CourseWizard.searchTree()',
                ]) ?>
            </span>
        </div>
        <div id="sem-tree-assign-all" class="hidden-js hidden-no-js">
            <a href="" onclick="return STUDIP.CourseWizard.assignAllNodes()">
                <?= Icon::create('arr_2left', Icon::ROLE_SORT) ?>
                <?= _('Alle Suchergebnisse zuweisen') ?>
            </a>
        </div>
        <ul class="collapsable css-tree">
            <li class="sem-tree-root tree-loaded keep-node">
                <input type="checkbox" id="root" checked="checked">
                <label for="root" class="undecorated">
                    <?= htmlReady(Config::get()->UNI_NAME_CLEAN) ?>
                </label>
                <ul>
                    <?php foreach ($tree as $node) : ?>
                        <?= $this->render_partial('studyareas/_node', [
                            'node'       => $node,
                            'stepnumber' => $stepnumber,
                            'temp_id'    => $temp_id,
                            'values'     => $values,
                            'open_nodes' => $open_nodes ?? [],
                        ]) ?>
                    <?php endforeach ?>
                </ul>
            </li>
        </ul>
    </div>
    <?php if (!empty($values['open_node'])) : ?>
        <input type="hidden" name="open_node" value="<?= $values['open_node'] ?>"/>
    <?php endif ?>
    <div class="clear"></div>
<? if (!$stepnumber) : ?>
    </fieldset>
<? endif ?>
<? if (!$stepnumber && empty($values['locked'])) : ?>
    <footer data-dialog-button class="hidden-no-js">
        <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
    </footer>
<? endif ?>
<script>
    //<!--
    $(function () {
        let element = $('#sem-tree-search');
        element.on('keypress', function (e) {
            if (e.keyCode === 13) {
                if (element.val() !== '') {
                    return STUDIP.CourseWizard.searchTree();
                } else {
                    return STUDIP.CourseWizard.resetSearch();
                }
            }
        });
    });
    //-->
</script>
<? endif ?>
