<?
$input_attr = [
    'class' => 'text-bottom',
    'name' => 'only_bookable_rooms',
    'style' => 'margin-left: 0.2em; margin-top: 0.6em;',
    'value' => '1',
    'title' => _('Nur buchbare Räume anzeigen')
];
if (Request::isDialog()) {
    $input_attr['data-dialog'] = 'size=big';
}
?>
<?= Icon::create('room-request')->asInput(20, $input_attr) ?>
