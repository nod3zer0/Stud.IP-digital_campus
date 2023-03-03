<?
if (!isset($show_room)) :
    // show rooms only if there is more than one
    if (empty($dates['rooms']) || sizeof($dates['rooms']) === 1) :
        $show_room = false;
    else :
        $show_room = true;
    endif;
endif;

if (!empty($dates['regular']['turnus_data']) || !empty($dates['irregular'])) :
  $output = [];
  if (is_array($dates['regular']['turnus_data'])) foreach ($dates['regular']['turnus_data'] as $cycle) :
    $first_date = sprintf(_("ab %s"), strftime('%x', $cycle['first_date']['date']));
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
                'plain'    => true]
        );
    endif;

    $output[] = $cycle_output;
  endforeach;

  echo implode(", \n", $output);

  $freetext_rooms = [];
  $irregular_rooms = [];

  if (!empty($dates['irregular'])):
    foreach ($dates['irregular'] as $date) :
        $irregular[] = $date;
        $irregular_strings[] = $date['tostring'];

        if (!empty($date['resource_id'])) :
            if (!isset($irregular_rooms[$date['resource_id']])) {
                $irregular_rooms[$date['resource_id']] = 0;
            }
            $irregular_rooms[$date['resource_id']]++;
        elseif (!empty($date['raum'])) :
            if (!isset($freetext_rooms['('. $date['raum'] .')'])) {
                $freetext_rooms['('. $date['raum'] .')'] = 0;
            }
            $freetext_rooms['('. $date['raum'] .')']++;
        endif;
    endforeach;
    unset($irregular_rooms['']);
    echo count($output) ? ", \n" : '';

    $rooms = array_merge(getPlainRooms($irregular_rooms, false), array_keys($freetext_rooms));

    if (!empty($irregular)) :
        if (isset($shrink) && !$shrink && count($irregular) < 20) :
            foreach ($irregular as $date) :
                echo $date['tostring'];

                if ($show_room && !empty($date['resource_id'])) :
                    echo ', '. _('Ort:') . ' ';
                    echo Room::find($date['resource_id']);
                endif;
                echo "\n";
            endforeach;
        else :
            echo _("Termine am") . implode(', ', shrink_dates($irregular));
            if (!empty($rooms)) :
                if (count($rooms) > 3) :
                    $rooms = array_slice($rooms, count($rooms) - 3, count($rooms));
                endif;

                if ($show_room) :
                    echo ', ' . _("Ort:") . ' ';
                    echo implode(', ', $rooms);
                endif;
            endif;
        endif;
    endif;
  endif;
endif;
