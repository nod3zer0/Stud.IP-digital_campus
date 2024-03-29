<form class="default" onsubmit="return false;" autocomplete="off">
    <div id="div-search-input" class="input-group files-search">
        <input type="text" autofocus name="searchtext" id="search-input"
            value="<?= htmlReady($_SESSION['search_text'] ?? '') ?>"
            placeholder="<?= _('Was suchen Sie?') ?>">

        <span class="input-group-append">
            <button type="submit" class="button" id="reset-search">
                <?= Icon::create('decline')->asImg(['title' => _('Suche zurücksetzen')]) ?>
            </button>

            <button type="submit" class="button" id="search-btn">
                <?= Icon::create('search')->asImg(['title' => _('Suche starten')]) ?>
            </button>

        </span>
    </div>
</form>

<div id="search-active-filters">
    <h5><?= _('Aktive Filter') . ': ' ?></h5>
    <div class="filter-items"></div>
</div>

<div id="search">
    <div id="searching-gif">
        <?= _('Suche...') ?>
    </div>

    <div id="search-term-invalid">
        <?= MessageBox::warning(sprintf(
            _('Leider ist Ihr Suchbegriff zu kurz. Der Suchbegriff muss mindestens "%s" Zeichen lang sein.'),
            '<span class="searchtermlen"></span>'
        )) ?>
    </div>

    <div id="search-results" data-loading-text="<?= _('Suche...') ?>"
        data-all-results="<?= _('Filter aufheben') ?>"
        data-searchterm="<?= htmlReady(Request::get('q')) ?>"
        data-category="<?= htmlReady(Request::get('category')) ?>"
        data-img-add="<?= htmlReady(Icon::create('add')->asImg(['title' => _('Unterveranstaltungen anzeigen')])) ?>"
        data-img-remove="<?= htmlReady(Icon::create('remove')->asImg(['title' => _('Unterveranstaltungen ausblenden')])) ?>"
        data-results-per-type="<?= Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE ?>"
        data-filters="<?= htmlReady(json_encode($filters)) ?>">
    </div>

    <div id="search-no-result">
        <?= MessageBox::warning(sprintf(
            _('Leider konnten zu Ihrem Suchbegriff "%s" keine Treffer gefunden werden. '
            . ' Haben Sie sich vielleicht verschrieben?'),
            '<span class="searchterm"></span>'
        )) ?>
    </div>
</div>
