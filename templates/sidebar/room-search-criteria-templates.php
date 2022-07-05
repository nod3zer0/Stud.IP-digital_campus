<li class="template invisible"
    data-template-type="bool">
    <?= Icon::create('trash')->asImg(
        [
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
<li class="template invisible"
    data-template-type="range">
    <?= Icon::create('trash')->asImg(
        [
            'class' => 'text-bottom remove-icon'
        ]
    ) ?>
    <label class="range-search-label undecorated">
        <input type="hidden">
        <span></span>
        <div class="range-input-container">
                    <?= _('von') ?>
            <input type="number" value="10"
                   class="room-search-widget_criteria-list_input">
                    <?= _('bis') ?>
            <input type="number" value="100"
                   class="room-search-widget_criteria-list_input">
        </div>
    </label>
</li>
<li class="template invisible"
    data-template-type="num">
    <?= Icon::create('trash')->asImg(
        [
            'class' => 'text-bottom remove-icon'
        ]
    ) ?>
    <label class="undecorated">
        <span></span>
        <input type="number"
               class="room-search-widget_criteria-list_input">
    </label>
</li>
<li class="template invisible"
    data-template-type="select">
    <?= Icon::create('trash')->asImg(
        [
            'class' => 'text-bottom remove-icon'
        ]
    ) ?>
    <label class="undecorated">
        <span></span>
        <select class="room-search-widget_criteria-list_input">
        </select>
    </label>
</li>
<li class="template invisible"
    data-template-type="date">
    <?= Icon::create('trash')->asImg(
        [
            'class' => 'text-bottom remove-icon'
        ]
    ) ?>
    <label class="undecorated">
        <span></span>
        <div class="range-input-container">
            <input type="date">
            <input type="text" data-time="yes">
            <?= _('Uhr') ?>
            <input type="text" data-time="yes">
            <?= _('Uhr') ?>
        </div>
    </label>
</li>
<li class="template invisible"
    data-template-type="date_range">
    <?= Icon::create('trash')->asImg(
        [
            'class' => 'text-bottom remove-icon'
        ]
    ) ?>
    <label class="undecorated">
        <span></span>
        <div class="range-input-container">
            <input type="date">
            <input type="date">
            <input type="text" data-time="yes">
            <?= _('Uhr') ?>
            <input type="text" data-time="yes">
            <?= _('Uhr') ?>
        </div>
    </label>
</li>
<li class="template invisible"
    data-template-type="other">
    <?= Icon::create('trash')->asImg(
        [
            'class' => 'text-bottom remove-icon'
        ]
    ) ?>
    <label class="undecorated">
        <span></span>
        <input type="text"
               class="room-search-widget_criteria-list_input">
    </label>
</li>
