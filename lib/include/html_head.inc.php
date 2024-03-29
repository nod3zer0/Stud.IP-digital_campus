<?php
/**
* output of html-head for all Stud.IP pages
*
* @author  Stefan Suchi <suchi@data-quest.de>
* @license GPL2 or any later version
*/

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

        document.querySelector('html').className = 'js';

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
            jsupdate_enable: <?= json_encode(
                             is_object($GLOBALS['perm']) &&
                             $GLOBALS['perm']->have_perm('autor') &&
                             PersonalNotifications::isActivated()) ?>,
            wysiwyg_enabled: true,
            editor_enabled: true
        }
    </script>

    <?= PageLayout::getHeadElements() ?>
</head>

<body id="<?= PageLayout::getBodyElementId() ?>">
    <nav id="skip_link_navigation" aria-busy="true"></nav>
    <?= PageLayout::getBodyElements() ?>
