<?php
/**
 * @var WikiPage $page
 * @var Course_WikiController $controller
 * @var WikiOnlineEditingUser $me_online
 */
?>

<div class="wiki-editor-container"
     data-page_id="<?= htmlReady($page->id) ?>"
     data-editing="<?= htmlReady($me_online->editing) ?>"
     data-content="<?= htmlReady(wikiReady($page->content, true, $page->range_id, $page->id)) ?>"
     data-chdate="<?= htmlReady($page->chdate) ?>"
     data-users="<?= htmlReady(json_encode($page->getOnlineUsers())) ?>">

    <?= $contentbar ?>

    <form action="<?= $controller->save($page) ?>" method="post" class="default" v-show="editing">
        <?= CSRFProtection::tokenTag() ?>
        <textarea class="wiki-editor size-l"
                  ref="wiki_editor"
                  data-editor="extraPlugins=WikiLink"
                  name="content"><?= wysiwygReady($page->content) ?></textarea>

        <div></div>
        <label>
            <input type="checkbox" v-model="autosave">
            <?= _('Autosave aktiv') ?>
        </label>

        <div data-dialog-button="">
            <button class="button" :disabled="!isChanged" :title="isChanged ? '<?= _('Speichern Sie den aktuellen Stand.') ?>' : '<?= _('Der aktuelle Stand ist schon gespeichert.') ?>'">
                <?= _('Speichern') ?>
            </button>
            <?= \Studip\LinkButton::create(_('Verlassen'), $controller->leave_editing($page))?>
            <button v-for="user in requestingUsers"
                    :key="user.user_id"
                    @click.prevent="delegateEditMode(user.user_id)"
                    class="button">
                {{ $gettextInterpolate($gettext('Schreibmodus an %{name} übergeben'), { name: user.fullname }) }}
            </button>
        </div>
    </form>

    <div v-if="!editing" class="">
        <div v-html="content"></div>
        <div data-dialog-button="">
            <button class="button"
                    title="<?= _('Beantragen Sie, dass Sie den Text jetzt bearbeiten wollen.') ?>"
                    @click.prevent="applyEditing">
                <?= _('Bearbeiten beantragen') ?>
            </button>
            <?= \Studip\LinkButton::create(_('Verlassen'), $controller->leave_editing($page))?>
        </div>
    </div>

    <wiki-editor-online-users :users="users"></wiki-editor-online-users>

</div>
