<div id="globalsearch-searchbar" role="search" aria-label="<?= _('Globale Suche') ?>">
    <input class="hidden-small-down" type="text" name="globalsearchterm" id="globalsearch-input"
           placeholder="<?= _('Was suchen Sie?') ?>" role="searchbox">
    <?= Icon::create('decline', Icon::ROLE_INACTIVE)->asInput([
        'id'    => 'globalsearch-clear',
        'class' => 'hidden-small-down',
        'alt'   => _('Suche zurücksetzen'),
    ]) ?>
    <?= Icon::create('search', Icon::ROLE_INFO_ALT)->asInput([
        'id'  => 'globalsearch-icon',
        'alt' => _('Suche starten')
    ]) ?>
    <div id="globalsearch-list">
        <a href="#" id="globalsearch-togglehints" data-toggle-text="<?= _('Tipps ausblenden') ?>">
            <?= _('Tipps einblenden') ?>
        </a>
        <?= $GLOBALS['template_factory']->render('globalsearch/_hints') ?>
        <div id="globalsearch-searching" aria-live="polite">
            <?= _('Suche...') ?>
        </div>
        <div id="globalsearch-results" data-more-results="<?= _('alle anzeigen') ?>"
             data-no-result="<?= _('Keine Ergebnisse gefunden.') ?>"
             aria-live="polite"
             data-results-per-type="<?= Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE ?>"
        ></div>
    </div>
</div>
