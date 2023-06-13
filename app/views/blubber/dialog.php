<?= $this->render_partial('blubber/index') ?>

<div data-dialog-button>
    <?= \Studip\LinkButton::create(_('Zum Kontext springen'), $thread->getURL()) ?>
</div>
