<?
/**
 * Template parameters:
 * - $title: The list title
 * - $grouped_rooms: The rooms, grouped by RoomManager::groupRooms
 * - $link_template: An optional link template where the room-ID is
 * represented by the only "%s" placeholder.
 * If $link_template is not set, the link to the booking plan
 * of the room is generated.
 * - $show_in_dialog: Whether to show the room link in a dialog (true)
 * or not (false).
 */
?>
<? if ($grouped_rooms) : ?>
    <? if (!empty($title)) : ?>
        <h1><?= htmlReady($title) ?></h1>
    <? endif ?>
    <? foreach ($grouped_rooms as $group) : ?>
        <?
        $location  = $group['location'];
        $buildings = $group['buildings'];
        ?>
        <div class="studip-widget-wrapper">
            <article class="studip">
                <header><h1><?= htmlReady($location->name) ?></h1></header>
                <? foreach ($buildings as $building_group) : ?>
                    <article class="studip toggle">
                        <header><h1><a href="#"><?= htmlReady($building_group['building']->name) ?></a></h1></header>
                        <section>
                            <table class="default">
                                <thead>
                                    <tr>
                                        <th>
                                            <?= _('Raum') ?>
                                        </th>
                                        <th class="actions">
                                            <?= _('Aktionen') ?>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <? foreach ($building_group['rooms'] as $resource) : ?>
                                        <?
                                        $room_link = '';
                                        if (!empty($link_template)) {
                                            $room_link = $controller->link_for(
                                                sprintf(
                                                    $link_template,
                                                    $resource->id
                                                )
                                            );
                                        } else {
                                            $room_link = $resource->getActionLink('booking_plan');
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <a href="<?= $room_link ?>"
                                                    <?= !empty($show_in_dialog) ? 'data-dialog="size=big"' : '' ?>>
                                                    <?= htmlReady($resource->name) ?>
                                                </a>
                                            </td>
                                            <td class="actions">
                                                <? if ($user) :?>
                                                    <?
                                                    $perms = [
                                                        'show_global_admin_actions' => $show_global_admin_actions,
                                                        'show_admin_actions'        => $resource->userHasPermission($user, 'admin'),
                                                        'show_tutor_actions'        => $resource->userHasPermission($user, 'tutor'),
                                                        'show_autor_actions'        => $resource->userHasPermission($user, 'autor'),
                                                        'show_user_actions'         => $resource->userHasPermission($user, 'user'),
                                                        'user_has_booking_rights'   => $resource->userHasBookingRights($user)];
                                                    ?>
                                                    <?= $this->render_partial('resources/_common/_action_menu.php',
                                                        compact('resource') + $perms
                                                    )?>
                                                <? endif ?>
                                            </td>
                                        </tr>
                                    <? endforeach ?>
                                </tbody>
                            </table>
                        </section>
                    </article>
                <? endforeach ?>
            </article>
        </div>
    <? endforeach ?>
<? endif ?>
