<?php
    if(Request::submitted('defaultDate')){
        $submitted_date = explode('-', Request::get('defaultDate'));
        $default_date = $submitted_date[2] . '.' . $submitted_date[1] . '.' . $submitted_date[0];
    } else {
        $default_date = strftime('%x', time());
    }
?>
<?= \Studip\LinkButton::create(
        _('Heute'),
        URLHelper::getURL('', ['defaultDate' => date('Y-m-d', time())])
    ); ?>

<input id="booking-plan-jmpdate" type="text"
 name="booking-plan-jmpdate" value="<?= $default_date; ?>">
