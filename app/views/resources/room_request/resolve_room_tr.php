<tr class="nohover">
    <td class="nowrap">
        <? if ($room->bookingPlanVisibleForUser($current_user)): ?>
            <?
            $booking_plan_params = [];
            if (isset($time_intervals[0]) && $time_intervals[0]['begin']) {
                $booking_plan_params = [
                    'defaultDate' => date('Y-m-d', $time_intervals[0]['begin'])
                ];
            }
            ?>
            <a href="<?= $controller->link_for(
                     'resources/room_planning/booking_plan/' . $room->id,
                     $booking_plan_params
                     ) ?>" target="_blank"
               title="<?= _('Zum Belegungsplan') ?>">
                <?= htmlReady($room->name) ?>
            </a>
        <? else: ?>
            <?= htmlReady($room->name) ?>
        <? endif ?>
        <?= tooltipIcon($room->room_type) ?>
        – <?= htmlReady(sprintf('%d Sitzplätze', $room->seats)) ?>
        <? if ($underload) : ?>
            [<?= htmlReady($underload) ?>%]
        <? endif ?>
    </td>
    <? if (!empty($time_intervals) && count($time_intervals) > 1) : ?>
        <td>
            <input type="checkbox" data-proxyfor="input.radio-<?= htmlReady($room->id) ?>"
                   name="all_in_room" value="<?= htmlReady($room->id) ?>"
                   <?= $room_availability_share[$room->id] <= 0.0  ? 'disabled="disabled"' : '' ?>>
            <? if ($room_availability_share[$room->id] >= 1.0) : ?>
                <?= Icon::create('check-circle', Icon::ROLE_STATUS_GREEN)->asImg(['class' => 'text-bottom']) ?>
            <? elseif ($room_availability_share[$room->id] <= 0.0) : ?>
                <?= Icon::create('decline-circle', Icon::ROLE_STATUS_RED)->asImg(['class' => 'text-bottom']) ?>
            <? else : ?>
                <?= Icon::create('exclaim-circle', Icon::ROLE_STATUS_YELLOW)->asImg(['class' => 'text-bottom']) ?>
                <?= tooltipIcon(sprintf(
                    _('%u von %u Terminen nicht verfügbar'),
                    $unavailable_dates[$room->id],
                    $amount_of_dates[$room->id]
                )) ?>
            <? endif ?>
        </td>
    <? endif ?>
    <? foreach ($time_intervals as $metadate_id => $data): ?>
        <? if (($data['metadate'] instanceof SeminarCycleDate)) : ?>
            <?
            $availability = $metadate_availability_share[$room->id][$metadate_id];
            $range_index = 'SeminarCycleDate' . '_' . $metadate_id;
            $room_radio_name = 'selected_rooms[' . $range_index . ']';
            ?>
            <td>
                <input type="radio" name="<?= htmlReady($room_radio_name) ?>"
                       class="text-bottom radio-<?= htmlReady($room->id) ?>"
                       value="<?= htmlReady($room->id) ?>"
                    <?= $availability <= 0.0 ? 'disabled="disabled"' : '' ?>
                <?= ($availability > 0 && $selected_rooms[$range_index] == $room->id)
                    ? 'checked="checked"'
                    : ''?>>
                <? if ($availability >= 1.0) : ?>
                    <?= Icon::create('check-circle', Icon::ROLE_STATUS_GREEN)->asImg(['class' => 'text-bottom']) ?>
                <? elseif ($availability <= 0.0) : ?>
                    <?= Icon::create('decline-circle', Icon::ROLE_STATUS_RED)->asImg(['class' => 'text-bottom']) ?>
                <? else : ?>
                    <?= Icon::create('exclaim-circle', Icon::ROLE_STATUS_YELLOW)->asImg(['class' => 'text-bottom']) ?>
                    <?= tooltipIcon(sprintf(
                        _('%u von %u Terminen nicht verfügbar'),
                        $unavailable_metadate_dates[$room->id][$metadate_id],
                        $amount_of_metadate_dates[$room->id][$metadate_id]
                    )) ?>
                <? endif ?>
                <? $stats = 0; array_walk($data['intervals'], function(&$item, $key, $room_id) use (&$stats) {
                    if ($item['booked_room'] == $room_id) {
                        $stats++;
                    }
                }, $room->id) ?>
                <? if ($stats > 0) : ?>
                    <?= tooltipIcon(sprintf(
                        _('%s von %s Terminen sind in diesem Raum'),
                        $stats, sizeof($data['intervals'])
                    ));
                    ?>
                <? endif ?>
            </td>
        <? else : ?>
            <? $i = 0 ?>
            <? foreach($data['intervals'] as $interval) : ?>
                <?
                $available = $room_availability[$room->id][$metadate_id][$i];
                $range_index = $interval['range'] . '_' . $interval['range_id'];
                $room_radio_name = 'selected_rooms[' . $range_index . ']';
                ?>
                <td>
                    <? if ($available || $interval['booked_room'] == $room->id): ?>
                        <input type="radio" name="<?= htmlReady($room_radio_name) ?>"
                               class="text-bottom radio-<?= htmlReady($room->id) ?>"
                               value="<?= htmlReady($room->id) ?>"
                               <?= ($selected_rooms[$range_index] == $room->id
                                     || $interval['booked_room'] == $room->id)
                                 ? 'checked="checked"'
                                 : ''?>>
                        <?= Icon::create('check-circle', Icon::ROLE_STATUS_GREEN)->asImg(['class' => 'text-bottom']) ?>
                    <? else: ?>
                        <input type="radio" name="<?= htmlReady($room_radio_name) ?>"
                               value="1" disabled="disabled"
                               class="text-bottom">
                        <?= Icon::create('decline-circle', Icon::ROLE_STATUS_RED)->asImg(['class' => 'text-bottom']) ?>
                    <? endif ?>
                </td>
                <? $i++ ?>
            <? endforeach ?>
        <? endif ?>
    <? endforeach ?>
</tr>
