<?= $this->render_partial('calendar/date/_add_edit_form', [
    'action_link' => $controller->link_for('calendar/date/edit/' . $date->id),
    'date' => $date
]) ?>
