<form class="default" method="post" data-dialog="reload-on-close"
      action="<?= $controller->link_for('resources/booking/transform' . '/' . $booking->id) ?>">
    <?= CSRFProtection::tokenTag() ?>
    <?= $this->render_partial('resources/booking/index') ?>
    <div data-dialog-button>
        <?= \Studip\Button::create(_('Umwandeln'), 'transform') ?>
    </div>
</form>
