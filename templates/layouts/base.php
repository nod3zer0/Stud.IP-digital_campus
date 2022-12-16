<?php
NotificationCenter::postNotification('PageWillRender', PageLayout::getBodyElementId());
$getInstalledLanguages = function () {
    $languages = [];
    foreach ($GLOBALS['INSTALLED_LANGUAGES'] as $key => $value) {
        $languages[$key] = array_merge(
            $value,
            ['selected' => $_SESSION['_language'] === $key]
        );
    }

    return $languages;
};

$lang_attr = str_replace('_', '-', $_SESSION['_language']);
?>
<!DOCTYPE html>
<html class="no-js" lang="<?= htmlReady($lang_attr) ?>">
<head>
    <meta charset="utf-8">
    <title data-original="<?= htmlReady(PageLayout::getTitle()) ?>">
        <?= htmlReady(PageLayout::getTitle() . ' - ' . Config::get()->UNI_NAME_CLEAN) ?>
    </title>
    <script>
        CKEDITOR_BASEPATH = "<?= Assets::url('javascripts/ckeditor/') ?>";
        String.locale = "<?= htmlReady(strtr($_SESSION['_language'], '_', '-')) ?>";

        document.querySelector('html').classList.replace('no-js', 'js');

        window.STUDIP = {
            ABSOLUTE_URI_STUDIP: "<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>",
            ASSETS_URL: "<?= $GLOBALS['ASSETS_URL'] ?>",
            CSRF_TOKEN: {
                name: '<?=CSRFProtection::TOKEN?>',
                value: '<? try {echo CSRFProtection::token();} catch (SessionRequiredException $e){}?>'
            },
            INSTALLED_LANGUAGES: <?= json_encode($getInstalledLanguages()) ?>,
            CONTENT_LANGUAGES: <?= json_encode(array_keys($GLOBALS['CONTENT_LANGUAGES'])) ?>,
            STUDIP_SHORT_NAME: "<?= htmlReady(Config::get()->STUDIP_SHORT_NAME) ?>",
            URLHelper: {
                base_url: "<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>",
                parameters: <?= json_encode(URLHelper::getLinkParams(), JSON_FORCE_OBJECT) ?>
            },
            USER_ID: <?= json_encode($GLOBALS['user']->id) ?>,
            jsupdate_enable: <?= json_encode(
                             is_object($GLOBALS['perm']) &&
                             $GLOBALS['perm']->have_perm('autor') &&
                             PersonalNotifications::isActivated()) ?>,
            wysiwyg_enabled: <?= json_encode((bool) Config::get()->WYSIWYG) ?>,
            server_timestamp: <?= time() ?>,
            config: <?= json_encode([
                'ACTIONMENU_THRESHOLD' => Config::get()->ACTION_MENU_THRESHOLD,
                'ENTRIES_PER_PAGE'     => Config::get()->ENTRIES_PER_PAGE,
                'OPENGRAPH_ENABLE'     => Config::get()->OPENGRAPH_ENABLE,
            ]) ?>,
        }
    </script>

    <?= PageLayout::getHeadElements() ?>

    <script>
    window.STUDIP.editor_enabled = <?= json_encode((bool) Studip\Markup::editorEnabled()) ?>;

    setTimeout(() => {
        // This needs to be put in a timeout since otherwise it will not match
        if (STUDIP.Responsive.isResponsive()) {
            document.querySelector('html').classList.add('responsive-display');
        }
    }, 0);
</script>
</head>

<body id="<?= PageLayout::getBodyElementId() ?>">
    <div id="skip_link_navigation" aria-busy="true"></div>
    <?= PageLayout::getBodyElements() ?>

    <? include 'lib/include/header.php' ?>

    <?= Sidebar::get()->render() ?>

    <!-- Start main page content -->
    <main id="content-wrapper">
        <? SkipLinks::addIndex(_('Hauptinhalt'), 'content', 100) ?>
        <div id="content">
            <h1 class="sr-only"><?= htmlReady(PageLayout::getTitle()) ?></h1>
            <? if (PageLayout::isFullscreenModeAllowed()): ?>
                <button hidden class="fullscreen-toggle unfullscreen" aria-label="<?= _('Vollbildmodus verlassen') ?>" title="<?= _('Vollbildmodus verlassen') ?>">
                    <?= Icon::create('zoom-out2')->asImg(24) ?>
                </button>
            <? endif; ?>
            <?= implode(PageLayout::getMessages()) ?>
            <?= $content_for_layout ?>
        </div>
    </main>
    <!-- End main content -->

    <a id="scroll-to-top" class="hide">
        <?= Icon::create('arr_1up', 'info_alt')->asImg(24, ['class' => '']) ?>
    </a>

    <?= $this->render_partial('footer', ['link_params' => $header_template->link_params]); ?>
    <?= SkipLinks::getHTML() ?>
</body>
</html>
<?php NotificationCenter::postNotification('PageDidRender', PageLayout::getBodyElementId());
