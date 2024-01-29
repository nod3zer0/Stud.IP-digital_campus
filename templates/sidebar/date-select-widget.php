<form method="post" name="date_select_form" class="default">
    <input type="text" id="date_select"
           name="date_select"
           value="<?= $date->format('d.m.Y') ?>"
           data-date-picker
           <?
           if ($calendar_control) {
               echo 'data-calendar-control';
           } else {
               echo 'onchange="jQuery(this).closest(\'form\').submit()"';
           }
           ?>>
</form>
