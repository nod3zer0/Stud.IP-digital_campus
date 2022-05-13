<?php
NotificationCenter::postNotification('PageWillRender', PageLayout::getBodyElementId());
$navigation = PageLayout::getTabNavigation();
$tab_root_path = PageLayout::getTabNavigationPath();
if ($navigation) {
    $subnavigation = $navigation->activeSubNavigation();
    if ($subnavigation !== null) {
        $nav_links = new NavigationWidget();
        $nav_links->setId('sidebar-navigation');
        if (!$navigation->getImage()) {
            $nav_links->addLayoutCSSClass('show');
        }
        foreach ($subnavigation as $path => $nav) {
            if (!$nav->isVisible()) {
                continue;
            }
            $nav_id = "nav_".implode("_", preg_split("/\//", $tab_root_path, -1, PREG_SPLIT_NO_EMPTY))."_".$path;
            $link = $nav_links->addLink(
                $nav->getTitle(),
                URLHelper::getURL($nav->getURL()),
                null,
                array_merge($nav->getLinkAttributes(), ['id' => $nav_id])
            );
            $link->setActive($nav->isActive());
            if (!$nav->isEnabled()) {
                $link['disabled'] = true;
                $link->addClass('quiet');
            }
        }
        if ($nav_links->hasElements()) {
            Sidebar::get()->insertWidget($nav_links, ':first');
        }
    }
}

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
?>
<!DOCTYPE html>
<html class="no-js" lang="<?= htmlReady(strtr($_SESSION['_language'], '_', '-')) ?>">
<head>
    <meta charset="utf-8">
    <title data-original="<?= htmlReady(PageLayout::getTitle()) ?>">
        <?= htmlReady(PageLayout::getTitle() . ' - ' . Config::get()->UNI_NAME_CLEAN) ?>
    </title>
    <script>
        CKEDITOR_BASEPATH = "<?= Assets::url('javascripts/ckeditor/') ?>";
        String.locale = "<?= htmlReady(strtr($_SESSION['_language'], '_', '-')) ?>";

        document.querySelector('html').classList.replace('no-js', 'js');
        setTimeout(() => {
            // This needs to be put in a timeout since otherwise it will not match
            if (window.matchMedia('(max-width: 767px)').matches) {
                document.querySelector('html').classList.add('responsive-display');
            }
        }, 0);

        window.STUDIP = {
            ABSOLUTE_URI_STUDIP: "<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>",
            ASSETS_URL: "<?= $GLOBALS['ASSETS_URL'] ?>",
            CSRF_TOKEN: {
                name: '<?=CSRFProtection::TOKEN?>',
                value: '<? try {echo CSRFProtection::token();} catch (SessionRequiredException $e){}?>'
            },
            INSTALLED_LANGUAGES: <?= json_encode($getInstalledLanguages()) ?>,
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
    window.STUDIP.editor_enabled = <?= json_encode((bool) Studip\Markup::editorEnabled()) ?> && CKEDITOR.env.isCompatible;
    </script>
</head>

<body id="<?= PageLayout::getBodyElementId() ?>" <? if (SkipLinks::isEnabled()) echo 'class="enable-skiplinks"'; ?>>
<div id="layout_wrapper">
    <? SkipLinks::insertContainer() ?>
    <? SkipLinks::addIndex(_('Hauptinhalt'), 'layout_content', 100) ?>
    <?= PageLayout::getBodyElements() ?>

    <? include 'lib/include/header.php' ?>

    <? $contextable = Context::get() && (
        (Navigation::hasItem('/course') && Navigation::getItem('/course')->isActive()) ||
        (Navigation::hasItem('/admin/institute') && Navigation::getItem('/admin/institute')->isActive())); ?>
    <div id="layout_page" <? if (!($contextable)) echo 'class="contextless"'; ?>>

    <? if (PageLayout::isHeaderEnabled() && Navigation::hasItem('/course') && Navigation::getItem('/course')->isActive() && $_SESSION['seminar_change_view_'.Context::getId()]) : ?>
        <?= $this->render_partial('change_view', ['changed_status' => $_SESSION['seminar_change_view_'.Context::getId()]]) ?>
    <? endif ?>

    <? if (Context::get() || PageLayout::isHeaderEnabled()): ?>
        <nav class="secondary-navigation">
        <? if (is_object($GLOBALS['perm']) && !$GLOBALS['perm']->have_perm('admin') && $contextable) : ?>
            <? $membership = CourseMember::find([Context::get()->id, $GLOBALS['user']->id]) ?>
            <? if ($membership) : ?>
                <a href="<?= URLHelper::getLink('dispatch.php/my_courses/groups') ?>"
                   data-dialog
                   class="colorblock gruppe<?= $membership ? $membership['gruppe'] : 1 ?>"></a>
            <? endif ?>
        <? endif ?>
        <? if ($contextable) : ?>
            <div id="layout_context_title">
            <? if (Context::isCourse()) : ?>
                <?= Icon::create('seminar', Icon::ROLE_INFO)->asImg(20, ['class' => 'context_icon']) ?>
                <?= htmlReady(Context::get()->getFullname()) ?>
                <? if ($GLOBALS['user']->config->SHOWSEM_ENABLE && !Context::get()->isStudygroup()): ?>
                    (<?= htmlReady(Context::get()->getTextualSemester()) ?>)
                <? endif ?>
            <? elseif (Context::isInstitute()) : ?>
                <?= Icon::create('institute', Icon::ROLE_INFO)->asImg(20, ['class' => 'context_icon']) ?>
                <?= htmlReady(Context::get()->name) ?>
            <? endif ?>
            </div>
        <? endif ?>

        <? if (PageLayout::isHeaderEnabled() /*&& isset($navigation)*/) : ?>
            <?= $this->render_partial('tabs', compact('navigation')) ?>
        <? endif; ?>
        </nav>
    <? endif; ?>

        <?
        if (is_object($GLOBALS['user']) && $GLOBALS['user']->id != 'nobody') {
            // only mark course if user is logged in and free access enabled
            $is_public_course = Context::isCourse() && Config::get()->ENABLE_FREE_ACCESS;
            $is_public_institute = Context::isInstitute()
                                && Config::get()->ENABLE_FREE_ACCESS
                                && Config::get()->ENABLE_FREE_ACCESS != 'courses_only';
            if (($is_public_course || $is_public_institute)
                && Navigation::hasItem('/course')
                && Navigation::getItem('/course')->isActive())
            {
                // indicate to the template that this course is publicly visible
                // need to handle institutes separately (always visible)
                if ($GLOBALS['SessSemName']['class'] == 'inst') {
                    $header_template->public_hint = _('öffentliche Einrichtung');
                } else if (Course::findCurrent()->lesezugriff == 0) {
                    $header_template->public_hint = _('öffentliche Veranstaltung');
                }
            }
        }
        ?>
        <div id="page_title_container" class="hidden-medium-up">
            <div id="current_page_title">
                <? if (Context::get() && strpos(PageLayout::getTitle(), Context::getHeaderLine() . ' - ') !== FALSE) : ?>
                    <?= htmlReady(str_replace(Context::getHeaderLine() . ' - ' , '', PageLayout::getTitle())) ?>
                <? else: ?>
                    <?= htmlReady( PageLayout::getTitle()) ?>
                <? endif ?>
                <?= !empty($public_hint) ? '(' . htmlReady($public_hint) . ')' : '' ?>
            </div>
        </div>

        <div id="layout_container">
            <?= Sidebar::get()->render() ?>
            <div id="layout_content">
            <? if (PageLayout::isFullscreenModeAllowed()): ?>
                <?= $this->render_partial('shared/fullscreen-toggle.php') ?>
            <? endif; ?>
                <?= implode(PageLayout::getMessages()) ?>
                <?= $content_for_layout ?>
            </div>
        </div>
    </div> <? // Closes #layout_page opened in included templates/header.php ?>

    <?= $this->render_partial('footer', ['link_params' => $header_template->link_params]); ?>
    <!-- Ende Page -->
    <? /* <div id="layout_push"></div> */ ?>
</div>


    <?= SkipLinks::getHTML() ?>
    <a id="scroll-to-top" class="hide">
        <?= Icon::create('arr_1up', 'info_alt')->asImg(24, ['class' => '']) ?>
    </a>
</body>
</html>
<?php NotificationCenter::postNotification('PageDidRender', PageLayout::getBodyElementId());
