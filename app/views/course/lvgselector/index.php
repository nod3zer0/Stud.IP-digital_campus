<? if (!$locked) : ?>
<form action="<?= $controller->link_for('course/lvgselector/index/' . $course_id, $url_params ?? []) ?>"
    <?= Request::isDialog() ? 'data-dialog' : '' ?>
      method="post" class="default">
    <? endif ?>
    <fieldset>
        <legend><?= _('Lehrveranstaltungsgruppen') ?></legend>
        <div id="assigned" data-ajax-url="<?= $ajax_url ?>" data-forward-url="<?= $no_js_url ?>">
            <h2>
        <span class="required">
            <?= _('Bereits zugewiesen') ?>
        </span>
            </h2>
            <ul class="css-tree">
                <li class="lvgroup-tree-assigned-root keep-node" data-id="root">
                    <ul id="lvgroup-tree-assigned-selected">
                        <? foreach ($selection->getAreas() as $area) : ?>
                            <?= $this->render_partial('course/wizard/steps/lvgroups/lvgroup_entry', compact('area')) ?>
                        <? endforeach ?>
                    </ul>
                </li>
            </ul>
        </div>
        <? if (!$locked) : ?>
            <div id="lvgroup-tree-open-nodes">
                <? foreach ($open_lvg_nodes as $opennode): ?>
                    <input type="hidden" name="open_lvg_nodes[]" value="<?= htmlReady($opennode) ?>">
                <? endforeach ?>
            </div>
            <div id="studyareas" data-ajax-url="<?= $ajax_url ?>"
                 data-forward-url="<?= $no_js_url ?>"
                 data-no-search-result="<?= _('Es wurde kein Suchergebnis gefunden.') ?>">
                <h2><?= _('Lehrveranstaltungsgruppen Suche') ?></h2>
                <div>
                    <input type="text" style="width: auto;" size="40" name="search" id="lvgroup-tree-search">
                    <span id="lvgroup-tree-search-start">
                <?= Icon::create('search')->asInput([
                    'name'    => 'start_search',
                    'onclick' => 'return STUDIP.MVV.CourseWizard.searchTree()',
                ]) ?>
            </span>
                </div>

                <div id="lvgsearchresults" style="display: none;">
                    <h2><?= _('Suchergebnisse') ?></h2>
                    <ul class="collapsable css-tree">

                    </ul>
                </div>
                <h2><?= _('Alle Lehrveranstaltungsgruppen') ?></h2>
                <ul class="collapsable css-tree">
                    <li class="lvgroup-tree-root tree-loaded keep-node">
                        <input type="checkbox" id="root" checked="checked"/>
                        <label for="root" class="undecorated">
                            <?= htmlReady(Config::get()->UNI_NAME_CLEAN) ?>
                        </label>
                        <ul>
                            <? $pos_id = 1; ?>
                            <? foreach ((array)$tree as $node) : ?>
                                <? $children = $node->getChildren(); ?>
                                <? if (count($children) || $node->isAssignable()) : ?>
                                    <?= $this->render_partial('course/wizard/steps/lvgroups/_node', [
                                        'node'       => $node, 'pos_id' => $pos_id++,
                                        'open_nodes' => $open_lvg_nodes ?? [],
                                        'children'   => $children,
                                    ]) ?>
                                <? endif ?>
                            <? endforeach ?>
                        </ul>
                    </li>
                </ul>
            </div>
        <? if ($open_lvg_nodes) : ?>
        <input type="hidden" name="open_nodes" value="<?= json_encode($open_lvg_nodes) ?>">
        <? endif ?>
            <script>
                //<!--
                $(function () {
                    let element = $('#lvgroup-tree-search');
                    element.on('keypress', function (e) {
                        if (e.keyCode === 13) {
                            if (element.val() !== '') {
                                return STUDIP.MVV.CourseWizard.searchTree();
                            } else {
                                return STUDIP.MVV.CourseWizard.resetSearch();
                            }
                        }
                    });
                });
                //-->
            </script>
        <? endif ?>
    </fieldset>
    <? if (!$locked) : ?>
    <footer data-dialog-button class="hidden-no-js">
        <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
    </footer>
</form>
<? endif ?>
