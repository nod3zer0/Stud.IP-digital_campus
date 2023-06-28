<form class="default" action="<?= $controller->link_for('admin/tree/store', get_class($node), $node->id) ?>" method="post">
    <section>
        <label>
            <?= (get_class($node) === RangeTreeNode::class && $node->studip_object_id)
                ? _('Name (kann hier nicht bearbeitet werden, da es sich um ein Stud.IP-Objekt handelt)')
                : _('Name') ?>
            <input type="text" name="name"
                   value="<?= htmlReady($node->getName()) ?>"
                   <?= get_class($node) === RangeTreeNode::class && $node->studip_object_id ? ' disabled' : '' ?>>
        </label>
    </section>
    <? if (get_class($node) === StudipStudyArea::class): ?>
        <section>
            <label>
                <?= _('Infotext') ?>
                <textarea name="description" rows="3"><?= htmlReady($node->info) ?></textarea>
            </label>
        </section>
        <section>
            <label>
                <?= _('Typ') ?>
                <select name="type"<?= empty($GLOBALS['SEM_TREE_TYPES'][$node->type]['editable']) ? ' disabled' : '' ?>>
                    <? foreach ($GLOBALS['SEM_TREE_TYPES'] as $index => $type) : ?>
                        <option value="<?= htmlReady($index) ?>"<?= $node->type == $index ? ' selected' : '' ?>>
                            <?= $type['name'] ?: _('Standard') ?>
                            <?= !$type['editable'] ? _('(nicht mehr nachträglich änderbar)') : '' ?>
                            <?= $type['hidden'] ? _('(dieser Knoten ist versteckt)') : '' ?>
                        </option>
                    <? endforeach ?>
                </select>
            </label>
        </section>
    <? endif ?>
    <section>
        <label>
            <?= _('Elternelement') ?>
            <?= $treesearch->render() ?>
        </label>
    </section>
    <? if (get_class($node) === RangeTreeNode::class): ?>
        <section>
            <label>
                <?= _('Zu einer Stud.IP-Einrichtung zuordnen') ?>
                <?= $instsearch->render() ?>
            </label>
        </section>
    <? endif ?>
    <input type="hidden" name="from" value="<?= $from ?>">
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'store') ?>
        <?= Studip\Button::createCancel(_('Abbrechen'), 'cancel', ['data-dialog' => 'close']) ?>
    </footer>
</form>
