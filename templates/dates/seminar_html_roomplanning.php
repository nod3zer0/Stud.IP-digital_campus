<?
if (!isset($show_room)) :
    // show rooms only if there is more than one
    if (empty($dates['rooms']) || count($dates['rooms']) === 1) :
        $show_room = false;
    else :
        $show_room = true;
    endif;
endif;

$now = time();

if (!empty($dates['regular']['turnus_data']) || !empty($dates['irregular'])) :
  $output = [];
  if (is_array($dates['regular']['turnus_data'])) foreach ($dates['regular']['turnus_data'] as $cycle) :
    $first_date = sprintf(_("ab %s"), strftime('%x', $cycle['first_date']['date']));
    $last_date = $cycle['last_date']['date'];
    if (empty($with_past_intervals) && $last_date < $now) {
        continue;
    }
    if ($cycle['cycle'] == 1) :
        $cycle_output = $cycle['tostring_short'] . ' (' . sprintf(_("zweiwöchentlich, %s"), $first_date) . ')';
    elseif ($cycle['cycle'] == 2) :
        $cycle_output = $cycle['tostring_short'] . ' (' . sprintf(_("dreiwöchentlich, %s"), $first_date) . ')';
    else :
      $cycle_output = $cycle['tostring_short'] . ' (' . _("wöchentlich") . ')';
    endif;
    if ($cycle['desc'])
      $cycle_output .= ' - '. $cycle['desc'];

    if ($show_room) :
        $cycle_output .= $this->render_partial('dates/_seminar_rooms',
            [
                'assigned' => $cycle['assigned_rooms'],
                'freetext' => $cycle['freetext_rooms'],
                'link'     => true
            ]
        );
    endif;

    $output[] = $cycle_output;
  endforeach;

  echo implode(", <br>", $output);

  $freetext_rooms = [];
  $irregular_rooms = [];
  $irregular = [];

  if (is_array($dates['irregular'])):
    foreach ($dates['irregular'] as $date) :
        if (empty($with_past_intervals) && $date->end_time < $now) {
            continue;
        }
        $irregular[] = $date;
        $irregular_strings[] = $date['tostring'];
        if (!empty($date['resource_id'])) :
            if (!isset($irregular_rooms[$date['resource_id']])) :
                $irregular_rooms[$date['resource_id']] = 0;
            endif;
            $irregular_rooms[$date['resource_id']]++;
        elseif (!empty($date['raum'])) :
            if (!isset($freetext_rooms['('. $date['raum'] .')'])) :
                $freetext_rooms['('. $date['raum'] .')'] = 0;
            endif;
            $freetext_rooms['('. $date['raum'] .')']++;
        endif;
    endforeach;
    unset($irregular_rooms['']);
    echo count($output) ? ", <br>" : '';

    $rooms = array_merge(getPlainRooms($irregular_rooms), array_keys($freetext_rooms));

    if (is_array($irregular) && count($irregular)) :
        if (isset($shrink) && !$shrink && count($irregular) < 20) :
            foreach ($irregular as $date) :
                if (empty($with_past_intervals) && $date->end_time < $now) {
                    continue;
                }
                echo $date['tostring'] ?? '';

                if ($show_room && !empty($date['resource_id'])) :
                    echo ', '. _('Ort:') . ' ';
                    $room_obj = Room::find($date['resource_id']);
                    echo '<a href="' . $room_obj->getActionLink('show') . '" target="_blank">'
                    . htmlReady($room_obj->name) . '</a>';
                endif;
                echo "<br>";
            endforeach;
        else :
            echo _("Termine am") . implode(', ', shrink_dates($irregular));
            if (count($rooms) > 0) :
                if (count($rooms) > 3) :
                    $rooms = array_slice($rooms, count($rooms) - 3, count($rooms));
                endif;

                if ($show_room) :
                    echo ', ' . _("Ort:") . ' ';
                    echo implode(', ', $rooms);
                endif;
            endif;
            echo "<br>";
        endif;
    endif;
  endif;
endif;
