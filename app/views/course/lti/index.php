<?php
/**
 * @var Course_LtiController $controller
 * @var LtiData[] $lti_data_array
 * @var bool $edit_perm
 */
?>
<? if (empty($lti_data_array)): ?>
    <?= MessageBox::info(_('Es wurden noch keine Inhalte angelegt.')) ?>
<? endif ?>

<? foreach ($lti_data_array as $lti_data): ?>
    <? $launch_url = $lti_data->getLaunchURL() ?>

    <article class="studip">
        <header>
            <h1><?= htmlReady($lti_data->title) ?></h1>

            <? if ($edit_perm): ?>
                <nav>
                    <form action="" method="post">
                        <?= CSRFProtection::tokenTag() ?>
                        <? if ($lti_data->position > 0): ?>
                            <?= Icon::create('arr_2up', Icon::ROLE_SORT)->asInput([
                                'formaction' => $controller->url_for('course/lti/move/' . $lti_data->position . '/up')
                            ]) ?>
                        <? endif ?>
                        <? if ($lti_data->position < count($lti_data_array) - 1): ?>
                            <?= Icon::create('arr_2down', Icon::ROLE_SORT)->asInput([
                                'formaction' => $controller->url_for('course/lti/move/' . $lti_data->position . '/down')
                            ]) ?>
                        <? endif ?>

                        <?= Icon::create('edit')->asInput([
                            'formaction' => $controller->url_for('course/lti/edit/' . $lti_data->position),
                            'title' => _('Abschnitt bearbeiten'),
                            'data-dialog' => ''
                        ]) ?>
                        <?= Icon::create('trash')->asInput([
                            'formaction' => $controller->url_for('course/lti/delete/' . $lti_data->position),
                            'title' => _('Abschnitt löschen'),
                            'data-confirm' => sprintf(_('Wollen Sie wirklich den Abschnitt "%s" löschen?'), $lti_data->title)
                        ]) ?>
                    </form>
                </nav>
            <? endif ?>
        </header>

        <section>
            <?= formatReady($lti_data->description) ?>

            <? if ($launch_url && $lti_data->options['document_target'] === 'iframe'): ?>
                <iframe style="border: none; height: 640px; width: 100%;"
                        src="<?= $controller->link_for('course/lti/iframe', $lti_data->position) ?>"></iframe>
            <? endif ?>
        </section>

        <? if ($launch_url && $lti_data->options['document_target'] !== 'iframe'): ?>
            <footer>
                <?= Studip\LinkButton::create(
                    _('Anwendung starten'),
                    $controller->link_for('course/lti/iframe', $lti_data->position),
                    ['target' => '_blank']
                ) ?>
            </footer>
        <? endif ?>
    </article>
<? endforeach ?>
