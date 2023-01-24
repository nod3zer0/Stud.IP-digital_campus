<?php
# Lifter010: TODO

$nav_items = Navigation::getItem('/')->getIterator()->getArrayCopy();
$nav_items = array_filter($nav_items, function ($item) {
    return $item->isVisible(true);
});

$header_nav = ['visible' => $nav_items, 'hidden' => []];
if (isset($_COOKIE['navigation-length'])) {
    $header_nav['hidden'] = array_splice(
        $header_nav['visible'],
        $_COOKIE['navigation-length']
    );
}

$navigation = PageLayout::getTabNavigation();
$tab_root_path = PageLayout::getTabNavigationPath();
if ($navigation) {
    $subnavigation = $navigation->activeSubNavigation();
    if ($subnavigation !== null) {
        $nav_links = new NavigationWidget();
        $nav_links->setId('sidebar-navigation');
        $nav_links->addCSSClass('navigation-level-3');
        $nav_links->setTitle(_('Dritte Navigationsebene'));
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

?>
<!-- Begin main site header -->
<header id="main-header">

    <!-- Top bar with site title, quick search and avatar menu -->
    <div id="top-bar" role="banner">
        <div id="responsive-menu">
            <?
            $user = User::findCurrent();
            if ($user) {
                $me = [
                    'avatar' => Avatar::getAvatar($user->id)->getURL(Avatar::MEDIUM),
                    'email' => $user->email,
                    'fullname' => $user->getFullName(),
                    'username' => $user->username,
                    'perm' => $GLOBALS['perm']->get_perm()
                ];

                $navWidget = Sidebar::get()->countWidgets(NavigationWidget::class);
                $allWidgets = Sidebar::get()->countWidgets();
                $hasSidebar = $allWidgets - $navWidget > 0;
                ?>
            <? } else {
                $me = ['username' => 'nobody'];
                $hasSidebar = false;
            } ?>
            <responsive-navigation :me="<?= htmlReady(json_encode($me)) ?>"
                                   context="<?= htmlReady(Context::get() ? Context::get()->getFullname() : '') ?>"
                                   :has-sidebar="<?= $hasSidebar ? 'true' : 'false' ?>"
                                   :navigation="<?= htmlReady(json_encode(ResponsiveHelper::getNavigationObject($_COOKIE['responsive-navigation-hash'] ?? null))) ?>"
            ></responsive-navigation>
        </div>
        <div id="site-title">
            <?= htmlReady(Config::get()->UNI_NAME_CLEAN) ?>
        </div>

        <!-- Dynamische Links ohne Icons -->
        <div id="header-links">
            <ul>

            <? if (Navigation::hasItem('/links')): ?>
                <? foreach (Navigation::getItem('/links') as $nav): ?>
                    <? if ($nav->isVisible()) : ?>
                        <li class="<? if ($nav->isActive()) echo 'active'; ?> <?= htmlReady($nav->getLinkAttributes()['class'] ?? '') ?>">
                            <a
                                <? if (is_internal_url($url = $nav->getURL())) : ?>
                                    href="<?= URLHelper::getLink($url) ?>"
                                <? else: ?>
                                    href="<?= htmlReady($url) ?>" target="_blank" rel="noopener noreferrer"
                                <? endif; ?>
                                <? if ($nav->getDescription()): ?>
                                    title="<?= htmlReady($nav->getDescription()) ?>"
                                <? endif; ?>
                                    <?= arrayToHtmlAttributes(array_diff_key($nav->getLinkAttributes(), array_flip(['class', 'title']))) ?>
                                ><?= htmlReady($nav->getTitle()) ?></a>
                        </li>
                    <? endif; ?>
                <? endforeach; ?>
            <? endif; ?>

            <? if (isset($show_quicksearch)) : ?>
                <? if (PageLayout::hasCustomQuicksearch()): ?>
                    <?= PageLayout::getCustomQuicksearch() ?>
                <? else: ?>
                    <? SkipLinks::addIndex(_('Suche'), 'globalsearch-input', 910, false) ?>
                    <li id="quicksearch_item">
                        <script>
                            var selectSem = function (seminar_id, name) {
                                document.location = "<?= URLHelper::getURL("dispatch.php/course/details/", ["send_from_search" => 1, "send_from_search_page" => URLHelper::getURL("dispatch.php/search/courses?keep_result_set=1")])  ?>&sem_id=" + seminar_id;
                            };
                        </script>
                        <?= $GLOBALS['template_factory']->render('globalsearch/searchbar') ?>
                    </li>
                <? endif; ?>
            <? endif; ?>

            <? if (is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_perm('user')): ?>
                <? $active = Navigation::hasItem('/profile')
                          && Navigation::getItem('/profile')->isActive();
                ?>

                <!-- User-Avatar -->
                <li class="header_avatar_container <? if ($active) echo 'active'; ?>" id="avatar-menu-container">

                <? if (is_object($GLOBALS['perm']) && PersonalNotifications::isActivated() && $GLOBALS['perm']->have_perm('autor')) : ?>
                    <? $notifications = PersonalNotifications::getMyNotifications() ?>
                    <? $lastvisit = (int)UserConfig::get($GLOBALS['user']->id)->getValue('NOTIFICATIONS_SEEN_LAST_DATE') ?>
                    <div id="notification-container"<?= count($notifications) > 0 ? ' class="hoverable"' : '' ?>>
                        <? foreach ($notifications as $notification) {
                            if ($notification['mkdate'] > $lastvisit) {
                                $alert = true;
                            }
                        } ?>
                        <button id="notification_marker" data-toggles="#notification_checkbox" <?= !empty($alert) ? ' class="alert"' : "" ?>
                                title="<?= sprintf(
                                    ngettext('%u Benachrichtigung', '%u Benachrichtigungen', count($notifications)),
                                    count($notifications)
                                ) ?>" data-lastvisit="<?= $lastvisit ?>"
                                <?= count($notifications) == 0 ? 'disabled' : '' ?>>
                            <span class="count" aria-hidden="true"><?= count($notifications) ?></span>
                        </button>
                        <input type="checkbox" id="notification_checkbox">
                        <div class="list below" id="notification_list">
                            <a class="mark-all-as-read <? if (count($notifications) < 2) echo 'invisible'; ?>" href="<?= URLHelper::getLink('dispatch.php/jsupdater/mark_notification_read/all', ['return_to' => $_SERVER['REQUEST_URI']]) ?>">
                                <?= _('Alle Benachrichtigungen als gelesen markieren') ?>
                            </a>
                            <a class="enable-desktop-notifications" href="#" style="display: none;">
                                <?= _('Desktop-Benachrichtigungen aktivieren') ?>
                            </a>
                            <ul>
                            <? foreach ($notifications as $notification) : ?>
                                <?= $notification->getLiElement() ?>
                            <? endforeach ?>
                            </ul>
                        </div>
                    <? if (PersonalNotifications::isAudioActivated()): ?>
                        <audio id="audio_notification" preload="none">
                            <source src="<?= Assets::url('sounds/blubb.ogg') ?>" type="audio/ogg">
                            <source src="<?= Assets::url('sounds/blubb.mp3') ?>" type="audio/mpeg">
                        </audio>
                    <? endif; ?>
                    </div>
                <? else: ?>
                    <div id="notification-container"></div>
                <? endif; ?>

                <? if (Navigation::hasItem('/avatar')): ?>
                    <div id="avatar-menu">
                    <?php
                    $action_menu = ContentGroupMenu::get();
                    $action_menu->addCSSClass('avatar-menu');
                    $action_menu->addAttribute('data-action-menu-reposition', 'false');
                    $action_menu->setLabel(User::findCurrent()->getFullName());
                    $action_menu->setAriaLabel(_('Profilmenü'));
                    $action_menu->setIcon(
                        Avatar::getAvatar(User::findCurrent()->id)->getImageTag(Avatar::MEDIUM),
                        ['id' => 'header_avatar_image_link']
                    );

                    foreach (Navigation::getItem('/avatar') as $subnav) {
                        $action_menu->addLink(
                            URLHelper::getURL($subnav->getURL(), [], true),
                            $subnav->getTitle(),
                            $subnav->getImage()
                        );
                    }
                    SkipLinks::addIndex(_('Profilmenü'), 'header_avatar_image_link', 1, false);
                    ?>
                    <?= $action_menu->render(); ?>
                    </div>
                <? endif; ?>
                </li>
            <? endif; ?>

                <li id="responsive-toggle-fullscreen"></li>
            </ul>
        </div>
    </div>
    <!-- End top bar -->

    <!-- Main navigation and right-hand logo -->
    <nav id="navigation-level-1" aria-label="<?= _('Hauptnavigation') ?>">
        <? SkipLinks::addIndex(_('Hauptnavigation'), 'navigation-level-1', 2, false); ?>
        <ul id="navigation-level-1-items" <? if (count($header_nav['hidden']) > 0) echo 'class="overflown"'; ?>>
        <? foreach ($header_nav['visible'] as $path => $nav): ?>
            <?= $this->render_partial(
                'header-navigation-item.php',
                compact('path', 'nav')
            ) ?>
        <? endforeach; ?>
            <li class="overflow">
                <input type="checkbox" id="header-sink">
                <label for="header-sink">
                    <a class="canvasready" href="#">
                        <?= Icon::create('action', 'navigation')->asImg(28, [
                            'class'  => 'headericon original',
                            'title'  => '',
                            'alt'    => '',
                        ]) ?>
                        <div class="navtitle">
                            <?= _('Mehr') ?>&hellip;
                        </div>
                    </a>
                </label>

                <ul>
                <? foreach ($header_nav['hidden'] as $path => $nav) : ?>
                    <?= $this->render_partial(
                        'header-navigation-item.php',
                        compact('path', 'nav')
                    ) ?>
                <? endforeach; ?>
                </ul>
            </li>
        </ul>

        <!-- Stud.IP Logo -->
        <a class="studip-logo" id="top-logo" href="http://www.studip.de/" title="Stud.IP Homepage" target="_blank" rel="noopener noreferrer">
            Stud.IP Homepage
        </a>
    </nav>
    <!-- End main navigation -->

    <? $contextable = Context::get() && (
            (Navigation::hasItem('/course') && Navigation::getItem('/course')->isActive()) ||
            (Navigation::hasItem('/admin/institute') && Navigation::getItem('/admin/institute')->isActive())); ?>

    <div id="current-page-structure" <? if (!($contextable)) echo 'class="contextless"'; ?>>

        <? if (
            PageLayout::isHeaderEnabled()
            && Navigation::hasItem('/course')
            && Navigation::getItem('/course')->isActive()
            && !empty($_SESSION['seminar_change_view_'.Context::getId()])
        ) : ?>
            <?= $this->render_partial('change_view', ['changed_status' => $_SESSION['seminar_change_view_'.Context::getId()]]) ?>
        <? endif ?>

        <? if (Context::get() || PageLayout::isHeaderEnabled()): ?>
            <? if (is_object($GLOBALS['perm']) && !$GLOBALS['perm']->have_perm('admin') && $contextable) : ?>
                <? $membership = CourseMember::find([Context::get()->id, $GLOBALS['user']->id]) ?>
                <? if ($membership) : ?>
                    <a href="<?= URLHelper::getLink('dispatch.php/my_courses/groups') ?>"
                       data-dialog
                       class="colorblock gruppe<?= $membership ? $membership['gruppe'] : 1 ?>"></a>
                <? endif ?>
            <? endif ?>
            <? if ($contextable) : ?>
                <div id="context-title">
                    <? if (Context::isCourse()) : ?>
                        <?= Icon::create('seminar', Icon::ROLE_INFO)->asImg(20, ['class' => 'context_icon']) ?>
                        <?= htmlReady(Context::get()->getFullname()) ?>
                    <? elseif (Context::isInstitute()) : ?>
                        <?= Icon::create('institute', Icon::ROLE_INFO)->asImg(20, ['class' => 'context_icon']) ?>
                        <?= htmlReady(Context::get()->name) ?>
                    <? endif ?>
                </div>
            <? endif ?>

            <? SkipLinks::addIndex(_('Zweite Navigationsebene'), 'navigation-level-2', 910) ?>
            <nav id="navigation-level-2" aria-label="<?= _('Zweite Navigationsebene') ?>">

                <? if (PageLayout::isHeaderEnabled() /*&& isset($navigation)*/) : ?>
                    <?= $this->render_partial('tabs', compact('navigation')) ?>
                <? endif; ?>
            </nav>
        <? endif; ?>

        <?
        $public_hint = '';
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
                if (isset($GLOBALS['SessSemName']['class']) && $GLOBALS['SessSemName']['class'] === 'inst') {
                    $public_hint = _('öffentliche Einrichtung');
                } else if (Course::findCurrent()->lesezugriff == 0) {
                    $public_hint = _('öffentliche Veranstaltung');
                }
            }
        }
        ?>
        <div id="page-title-container" class="hidden-medium-up">
            <div id="page-title">
                <? if (Context::get() && strpos(PageLayout::getTitle(), Context::getHeaderLine() . ' - ') !== FALSE) : ?>
                    <?= htmlReady(str_replace(Context::getHeaderLine() . ' - ' , '', PageLayout::getTitle())) ?>
                <? else: ?>
                    <?= htmlReady( PageLayout::getTitle()) ?>
                <? endif ?>
                <?= !empty($public_hint) ? '(' . htmlReady($public_hint) . ')' : '' ?>
            </div>
        </div>
    </div>

    <div id="responsive-contentbar-container"></div>

<!-- End main site header -->
</header>
