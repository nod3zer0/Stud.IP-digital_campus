<li class="template invisible" data-template-type="bool">
    <?= Icon::create('trash')->asInput(
        [
            'title' => _('Kriterium entfernen'),
            'aria-label' => _('Kriterium entfernen'),
            'class' => 'text-bottom remove-icon'
        ]
    ) ?>
    <input type="hidden" value="1">
    <label class="undecorated">
        <input type="checkbox"
               value="1"
               checked
               class="room-search-widget_criteria-list_input">
        <span></span>
    </label>
</li>
<li class="template invisible" data-template-type="range">
    <?= Icon::create('trash')->asImg(
        [
            'class' => 'text-bottom remove-icon',
            'aria-label' => _('Kriterium entfernen'),
            'title' => _('Kriterium entfernen'),
        ]
    ) ?>
    <label class="range-search-label undecorated"><span></span></label>
        <input type="hidden">
        <div class="range-input-container hgroup">
            <label>
                <?= _('von') ?>
                <input type="number" value="10" class="room-search-widget_criteria-list_input">
            </label>
            <label>
                <?= _('bis') ?>
                <input type="number" value="100" class="room-search-widget_criteria-list_input">
            </label>
        </div>
    </label>
</li>
<li class="template invisible" data-template-type="num">
    <?= Icon::create('trash')->asImg(
        [
            'class' => 'text-bottom remove-icon',
            'aria-label' => _('Kriterium entfernen'),
            'title' => _('Kriterium entfernen'),
        ]
    ) ?>
    <label class="undecorated">
        <span></span>
        <input type="number" class="room-search-widget_criteria-list_input">
    </label>
</li>
<li class="template invisible" data-template-type="select">
    <?= Icon::create('trash')->asImg(['class' => 'text-bottom remove-icon']) ?>
    <label class="undecorated">
        <span></span>
        <select class="room-search-widget_criteria-list_input">
        </select>
    </label>
</li>
<li class="template invisible"
    data-template-type="other">
    <?= Icon::create('trash')->asImg(
        [
            'class' => 'text-bottom remove-icon',
            'aria-label' => _('Kriterium entfernen'),
            'title' => _('Kriterium entfernen'),
        ]
    ) ?>
    <label class="undecorated">
        <span></span>
        <input type="text" class="room-search-widget_criteria-list_input">
    </label>
</li>
