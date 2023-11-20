<form class="default" action="<?= $controller->link_for('admin/tree/store', get_class($node), $node->id ?: null) ?>" method="post">
    <section>
        <label>
            <?= _('Name') ?>
            <input type="text" name="name"
                   placeholder="<?= get_class($node) === RangeTreeNode::class ? _('Name des Eintrags (wird bei Zuweisung zu einer Stud.IP-Einrichtung überschrieben)') : _('Name des Eintrags') ?>">
        </label>
    </section>
    <? if (get_class($node) === StudipStudyArea::class): ?>
        <section>
            <label>
                <?= _('Infotext') ?>
                <textarea name="description" rows="3"></textarea>
            </label>
        </section>
        <section>
            <label>
                <?= _('Typ') ?>
                <select name="type">
                    <? foreach ($GLOBALS['SEM_TREE_TYPES'] as $index => $type) : ?>
                        <option value="<?= htmlReady($index) ?>">
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
    <input type="hidden" name="from" value="<?= htmlReady($from) ?>">
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'store') ?>
        <?= Studip\Button::createCancel(_('Abbrechen'), 'cancel', ['data-dialog' => 'close']) ?>
    </footer>
</form>
