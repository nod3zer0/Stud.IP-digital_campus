<?= $this->render_partial('admin/oauth2/_notices') ?>

<article class="studip admin-oauth2--setup">
    <header>
        <h1>
            <a name="setup">
                <?= _('OAuth2-Setup') ?>
            </a>
        </h1>
    </header>
    <?= $this->render_partial('admin/oauth2/_setup') ?>
</article>

<?= $this->render_partial('admin/oauth2/_clients') ?>
