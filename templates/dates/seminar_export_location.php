<?
// condense regular dates by room
if (!empty($dates['regular']['turnus_data'])) foreach ($dates['regular']['turnus_data'] as $cycle) :
  if (!empty($cycle['assigned_rooms'])) foreach ($cycle['assigned_rooms'] as $room_id => $count) :
    $room_object = Room::find($room_id);
    $output[$room_object->name][] = $cycle['tostring_short'] .' ('. $count .'x)';
  endforeach;

  if (!empty($cycle['freetext_rooms'])) foreach ($cycle['freetext_rooms'] as $room => $count) :
    if ($room) :
      $output['('. $room .')'][] = $cycle['tostring_short']  .' ('. $count .'x)';
    endif;
  endforeach;

endforeach;


// condense irregular dates by room
if (!empty($dates['irregular'])) foreach ($dates['irregular'] as $date) :
    if ($date['resource_id']) :
        $output_dates[$date['resource_id']][] = $date;
    elseif ($date['raum']) :
        $output_dates[$date['raum']][] = $date;
    endif;
endforeach;

// now shrink the dates for each room/freetext and add them to the output
if (!empty($output_dates)) foreach ($output_dates as $dates) :
    if ($dates[0]['resource_id']) :
        $room_object = Room::find($dates[0]['resource_id']);
        $output[$room_object->name][] = implode(", ", shrink_dates($dates));
    elseif ($dates[0]['raum']) :
        $output['('. $dates[0]['raum'] .')'][] = implode(", ", shrink_dates($dates));
    endif;
endforeach;

if (!isset($output) || count($output) === 0) :
  echo _('nicht angegeben');
elseif (count($output) === 1) :
    $keys = array_keys($output);
    echo array_pop($keys);
else :
    $pos = 1;
    foreach ($output as $room => $dates) :
        echo $room .': '. implode("\n", $dates) . (count($output) > $pos ? ', ' : '') . "\n";
        $pos++;
    endforeach;
endif;
