<?php
/**
 * @var array $loginerror
 * @var string $error_msg
 */

// Get background images (this should be resolved differently since mobile
// browsers might still download the desktop background)
if (!match_route('web_migrate.php')) {
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
} else {
    $bg_desktop = URLHelper::getURL('pictures/loginbackgrounds/1.jpg');
    $bg_mobile = URLHelper::getURL('pictures/loginbackgrounds/2.jpg');
}
$show_login = !(current(StudipAuthAbstract::getInstance()) instanceOf StudipAuthSSO) && StudipAuthAbstract::isLoginEnabled();
$show_hidden_login = !$show_login && StudipAuthAbstract::isLoginEnabled();
?>
<main id="content" class="loginpage">
    <div id="background-desktop" style="background: url(<?= $bg_desktop ?>) no-repeat top left/cover;"></div>
    <div id="background-mobile" style="background: url(<?= $bg_mobile ?>) no-repeat top left/cover;"></div>

    <div id="login_flex">
        <div>
            <? if ($loginerror): ?>
                <!-- failed login code -->
                <?= MessageBox::error(_('Bei der Anmeldung trat ein Fehler auf!'), [
                    $error_msg,
                    sprintf(
                        _('Bitte wenden Sie sich bei Problemen an: <a href="mailto:%1$s">%1$s</a>'),
                        $GLOBALS['UNI_CONTACT']
                    )
                ]) ?>
            <? endif ?>

            <?= implode('', PageLayout::getMessages()); ?>
            <div id="loginbox">
                <header>
                    <h1><?= htmlReady(Config::get()->UNI_NAME_CLEAN) ?></h1>
                </header>

                <? if ($show_login) : ?>
                    <?= $this->render_partial('_standard_loginform', [
                        'hidden' => false,
                        'login_footer_id' => 'login-footer-top'
                    ]) ?>
                <? endif ?>
                <nav>
                    <ul>
                        <? foreach (Navigation::getItem('/login') as $key => $nav) : ?>
                            <? if ($nav->isVisible()) : ?>
                                <? if ($key === 'standard_login' && $show_login) continue; ?>
                                <? $name_and_title = explode(' - ', $nav->getTitle()) ?>
                                <li class="login_link">
                                    <? if (is_internal_url($url = $nav->getURL())) : ?>
                                    <? SkipLinks::addLink($name_and_title[0], URLHelper::getLink($url, ['cancel_login' => 1])) ?>
                                    <a href="<?= URLHelper::getLink($url, ['cancel_login' => 1]) ?>" <?= arrayToHtmlAttributes($nav->getLinkAttributes()) ?>>
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
                <? if ($show_hidden_login) : ?>
                    <?= $this->render_partial('_standard_loginform', [
                        'hidden' => empty($loginerror),
                        'login_footer_id' => 'login-footer-bottom'
                    ]) ?>
                <? endif ?>

                <footer>
                    <? if ($GLOBALS['UNI_LOGIN_ADD']) : ?>
                        <div class="uni_login_add">
                            <?= $GLOBALS['UNI_LOGIN_ADD'] ?>
                        </div>
                    <? endif ?>

                <form method="POST" action="<?=URLHelper::getLink(Request::url(), ['cancel_login' => null])?>">
                    <?=CSRFProtection::tokenTag()?>
                    <input type="hidden" name="user_config_submitted" value="1">
                    <div id="languages">
                        <? foreach ($GLOBALS['INSTALLED_LANGUAGES'] as $temp_language_key => $temp_language): ?>
                            <?= Assets::img('languages/' . $temp_language['picture'], ['alt' => $temp_language['name'], 'size' => '24']) ?>
                            <button class="as-link" name="set_language_<?=$temp_language_key?>">
                                <?= htmlReady($temp_language['name']) ?>
                            </button>
                        <? endforeach; ?>
                    </div>
                    <div id="contrast">
                        <?=CSRFProtection::tokenTag()?>
                        <? if (!empty($_SESSION['contrast'])) : ?>
                            <?= Icon::create('accessibility')->asImg(24) ?>
                            <button class="as-link" name="unset_contrast"><?= _('Normalen Kontrast aktivieren') ?></button>
                            <?= tooltipIcon(_('Aktiviert standardmäßige, nicht barrierefreie Kontraste.')); ?>
                        <? else : ?>
                            <?= Icon::create('accessibility')->asImg(24) ?>
                            <button class="as-link" name="set_contrast"><?= _('Hohen Kontrast aktivieren') ?></button>
                            <?= tooltipIcon(_('Aktiviert einen hohen Kontrast gemäß WCAG 2.1. Diese Einstellung wird nach dem Login übernommen.
                        Sie können sie in Ihren persönlichen Einstellungen ändern.')); ?>
                        <? endif ?>
                    </div>
                </form>

                </footer>
            </div>
        </div>

        <? if (Config::get()->LOGIN_FAQ_VISIBILITY && count($faq_entries) > 0) : ?>
            <div id="faq_box">
                <header><h1><?= htmlReady(Config::get()->LOGIN_FAQ_TITLE) ?></h1></header>
                <? foreach ($faq_entries as $entry) : ?>
                    <article class="studip toggle">
                        <header>
                            <h1><a href="#"><?= htmlReady($entry->title) ?></a></h1>
                        </header>
                        <section><?= formatReady($entry->description) ?>
                        </section>
                    </article>
                <? endforeach ?>
            </div>
        <? endif ?>

    </div>


</main>

<script type="text/javascript" language="javascript">
//<![CDATA[
$(function () {
    $('form[name=login]').submit(function () {
        $('input[name=resolution]', this).val( screen.width + 'x' + screen.height );
        $('input[name=device_pixel_ratio]').val(window.devicePixelRatio || 1);
    });
});
// -->
</script>
