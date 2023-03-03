<?
if (!isset($link)) $link = true;

$output = [];
$output_dates = [];

// condense regular dates by room
if (is_array($dates['regular']['turnus_data'])) foreach ($dates['regular']['turnus_data'] as $cycle) :
    $first_date   = sprintf(_("ab %s"), strftime('%x', $cycle['first_date']['date']));
    $cycle_output = $cycle['tostring'] . ' (' . $first_date . ')';
    if ($cycle['desc'])
        $cycle_output .= ', <i>' . htmlReady($cycle['desc']) . '</i>';

    if (!empty($show_room)) :
        $cycle_output .= $this->render_partial('dates/_seminar_rooms', ['assigned' => $cycle['assigned_rooms'],
                                                                        'freetext' => $cycle['freetext_rooms'],
                                                                        'link'     => $link
        ]);
    endif;

    if (is_array($cycle['assigned_rooms'])) foreach ($cycle['assigned_rooms'] as $room_id => $count) :
        $room_obj = Room::find($room_id);
        if ($link) {
            $output[
                '<a href="' . $room_obj->getActionLink('show') . '" data-dialog="1">'
                . htmlReady($room_obj->name)
                . '</a>'
                ][] = $cycle['tostring'] .' ('. $count .'x)';
        } else {
            $output[htmlReady($room_obj->name)][] = $cycle['tostring_short'] .' ('. $count .'x)';
        }
    endforeach;
    if (is_array($cycle['freetext_rooms'])) foreach ($cycle['freetext_rooms'] as $room => $count) :
        if ($room) :
            $output['(' . htmlReady($room) . ')'][] = $cycle['tostring'] . ' (' . $count . 'x)';
        elseif ($cycle['tostring']) :
            $without_location = $cycle['tostring'];
            if($count) :
                $without_location .=  '(' . $count . 'x)';
            endif;
            $output[_('Keine Raumangabe')][] = $without_location;
        endif;
    endforeach;
endforeach;


// condense irregular dates by room
if (isset($dates['irregular']) && is_array($dates['irregular'])) {
    foreach ($dates['irregular'] as $date) :
        if (isset($date['resource_id'])) :
            $output_dates[$date['resource_id']][] = $date;
        elseif (!empty($date['raum'])) :
            $output_dates[$date['raum']][] = $date;
        else :
            $output_dates[_('Keine Raumangabe')][]  = $date['tostring'];
        endif;
    endforeach;
}

// now shrink the dates for each room/freetext and add them to the output
foreach ($output_dates as $dates) :
    if (isset($dates[0]['resource_id'])) :
        $room_obj = Room::find($dates[0]['resource_id']);
        if ($link) {
            $output[
                '<a href="' . $room_obj->getActionLink('show') . '" data-dialog="1">'
                . htmlReady($room_obj->name)
                . '</a>'
                ][] = implode('<br>', shrink_dates($dates));
        } else {
            $output[htmlReady($room_obj->name)][] = implode('<br>', shrink_dates($dates));
        }
    elseif (isset($dates[0]['raum'])) :
        $output['(' . htmlReady($dates[0]['raum']) . ')'][] = implode('<br>', shrink_dates($dates));
    else :
        $output[_('Keine Raumangabe')][] = implode('<br>', $dates);
    endif;
endforeach;
?>


<? if (count($output) === 0) : ?>
    <?= htmlReady($ort) ?: _('Keine Raumangabe') ?>
<? else: ?>
    <dl>
        <? foreach ($output as $room_html => $dates) : ?>
            <dt><?= $room_html ?></dt>
            <? foreach ($dates as $date) : ?>
                <dd>
                    <?= $date ?>
                </dd>
            <? endforeach ?>
        <? endforeach ?>
    </table>
<? endif ?>
