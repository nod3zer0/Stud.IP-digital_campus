<?php
/**
 * @var int $num_active_courses
 * @var int $num_registered_users
 * @var int $num_online_users
 * @var bool $logout
 * @var string[] $plugin_contents
 */

// Get background images (this should be resolved differently since mobile
// browsers might still download the desktop background)
$bg_desktop = LoginBackground::getRandomPicture('desktop');
if ($bg_desktop) {
    $bg_desktop = $bg_desktop->getURL();
} else {
    $bg_desktop = URLHelper::getURL('pictures/loginbackgrounds/1.jpg');
}
$bg_mobile = LoginBackground::getRandomPicture('mobile');
if ($bg_mobile) {
    $bg_mobile = $bg_mobile->getURL();
} else {
    $bg_mobile = URLHelper::getURL('pictures/loginbackgrounds/2.jpg');
}
?>
<!-- Startseite (nicht eingeloggt) -->
<main id="content">
<? if ($logout): ?>
    <?= MessageBox::success(_('Sie sind nun aus dem System abgemeldet.'), array_filter([$GLOBALS['UNI_LOGOUT_ADD']])) ?>
<? endif; ?>

    <div id="background-desktop" style="background: url(<?= $bg_desktop ?>) no-repeat top left/cover;"></div>
    <div id="background-mobile" style="background: url(<?= $bg_mobile ?>) no-repeat top left/cover;"></div>
    <article id="loginbox">
        <header>
            <h1><?= htmlReady(Config::get()->UNI_NAME_CLEAN) ?></h1>
        </header>
        <nav>
            <ul>
                <? foreach (Navigation::getItem('/login') as $key => $nav) : ?>
                    <? if ($nav->isVisible()) : ?>
                        <? $name_and_title = explode(' - ', $nav->getTitle()) ?>
                        <li class="login_link">
                            <? if (is_internal_url($url = $nav->getURL())) : ?>
                            <? SkipLinks::addLink($name_and_title[0], $url) ?>
                            <a href="<?= URLHelper::getLink($url) ?>">
                                <? else : ?>
                                <a href="<?= htmlReady($url) ?>" target="_blank" rel="noopener noreferrer">
                                    <? endif ?>
                                    <?= htmlReady($name_and_title[0]) ?>
                                    <p>
                                        <?= htmlReady(!empty($name_and_title[1]) ? $name_and_title[1] : $nav->getDescription()) ?>
                                    </p>
                                </a>
                        </li>
                    <? endif ?>
                <? endforeach ?>
            </ul>
        </nav>
        <footer>
            <? if ($GLOBALS['UNI_LOGIN_ADD']) : ?>
                <div class="uni_login_add">
                    <?= $GLOBALS['UNI_LOGIN_ADD'] ?>
                </div>
            <? endif; ?>

            <div id="languages">
                <? foreach ($GLOBALS['INSTALLED_LANGUAGES'] as $temp_language_key => $temp_language): ?>
                    <?= Assets::img('languages/' . $temp_language['picture'], ['alt' => $temp_language['name'], 'size' => '24']) ?>
                    <a href="index.php?set_language=<?= $temp_language_key ?>">
                        <?= htmlReady($temp_language['name']) ?>
                    </a>
                <? endforeach; ?>
            </div>

            <div id="contrast">
                <? if (isset($_SESSION['contrast'])) : ?>
                    <?= Icon::create('accessibility')->asImg(24) ?>
                    <a href="index.php?unset_contrast=1"><?= _('Normalen Kontrast aktivieren') ?></a>
                    <?= tooltipIcon(_('Aktiviert standardmäßige, nicht barrierefreie Kontraste.')); ?>
                <? else : ?>
                    <?= Icon::create('accessibility')->asImg(24) ?>
                    <a href="index.php?set_contrast=1" id="highcontrastlink"><?= _('Hohen Kontrast aktivieren')?></a>
                    <?= tooltipIcon(_('Aktiviert einen hohen Kontrast gemäß WCAG 2.1. Diese Einstellung wird nach dem Login übernommen.
                    Sie können sie in Ihren persönlichen Einstellungen ändern.')); ?>
                <? endif ?>

            </div>

            <div class="login_info">
                <div>
                    <?= _('Aktive Veranstaltungen') ?>:
                    <?= number_format($num_active_courses, 0, ',', '.') ?>
                </div>

                <div>
                    <?= _('Registrierte NutzerInnen') ?>:
                    <?= number_format($num_registered_users, 0, ',', '.') ?>
                </div>

                <div>
                    <?= _('Davon online') ?>:
                    <?= number_format($num_online_users, 0, ',', '.') ?>
                </div>

                <div>
                    <a href="dispatch.php/siteinfo/show">
                        <?= _('mehr') ?> &hellip;
                    </a>
                </div>
            </div>
        </footer>
    </article>

<? if (count($plugin_contents) > 0): ?>
    <div id="login-plugin-contents">
    <? foreach ($plugin_contents as $content): ?>
        <?= $content ?>
    <? endforeach; ?>
    </div>
<? endif; ?>
</main>

