<?
/**
 * This is a general table row template for resources.
 *
 * Template variables:
 *
 * $resource: A Resource object.
 * $booking_plan_link_on_name: Boolean: Whether the link to the booking plan
 *     shall be wrapped around the name (true) or not (false). In the latter
 *     case, the link will point to the info dialog of the resource instead.
 * $show_global_admin_actions: Boolean: Whether to display actions which are
 *     designed for users with global 'admin' resource permissions.
 *     Defaults to false (do not show actions).
 * $show_admin_actions: Boolean: Whether to display actions which are
 *     designed for users with 'admin' resource permissions.
 *     Defaults to false (do not show actions).
 * $show_tutor_actions: Boolean: Whether to display actions which are
 *     designed for users with 'tutor' resource permissions.
 *     Defaults to false (do not show actions).
 * $show_autor_actions: Boolean: Whether to display actions which are
 *     designed for users with 'autor' resource permissions.
 *     Defaults to false (do not show actions).
 * $show_user_actions: Boolean: Whether to display actions which are
 *     designed for users with 'user' resource permissions.
 *     Defaults to false (do not show actions).
 * $user_has_booking_rights: Boolean: Whether the user for which this template
 *     is rendered has booking rights on the resource (true) or not (false).
 * $checkbox_data: Array: Data for an optional checkbox at the start
 *     of the row. If this is not set no checkbox is shown.
 *     The checkbox will get the resource-ID as value.
 *     Special array indexes:
 *     'name' => The name of the checkbox. This index must be set.
 *     'checked' => Boolean: True, if the checkbox shall be set (checked).
 *         false if it shall be unset (unchecked). Defaults to false.
 *     All other indexes will be added as HTML attributes.
 * $show_picture: Boolean: Whether to display the resource picture or not.
 *     Defaults to false (do not show picture).
 * $show_full_name: Boolean: Whether to display the full name
 *     (with resource type) or just the name field from the database.
 *     Defaults to false (do not show full name).
 * $clipboard_range_type: String: The range type for the drag and drop
 *     functionality of the clipboard system.
 *     Defaults to 'Resource'.
 * $additional_properties: Array: Additional properties
 *     that shall be displayed in extra columns.
 * $additional_columns: Array: Additional columns for the table.
 *     This array contains HTML code for each column (without the td element).
 * $additional_actions: Array: Additional actions for the action menu.
 *     This array contains associative arrays where each of those arrays
 *     has the following structure and indexes:
 *
 *     $position_index => [
 *         0 => Link
 *         1 => Label
 *         2 => Icon
 *         3 => Link attributes
 *     ]
 *
 *     $position_index is a string consisting of four letters with the
 *     first letter being either '0' or another letter. Depending on the
 *     value of $position_index the additional actions are placed
 *     before or after a standard action.
 *     The indexes for the standard actions are:
 *     - '0010': Show details
 *     - '0020': Show booking plan
 *     - '0030': Show semester plan
 *     - '0040': Manage permissions
 *     - '0050': Manage temporary permissions
 *     - '0060': Edit resource
 *     - '0070': Book resource
 *     - '0080': Mass deletion of bookings
 *     - '0090': Export bookings
 *     - '0100': Show files
 *     - '0110': Delete resource
 */
?>
<tr>
    <? if (!empty($checkbox_data) && $checkbox_data['name']): ?>
        <?
        if ($checkbox_data['checked']) {
            $checkbox_data['checked'] = 'checked';
        }
        ?>
        <td>
            <input type="checkbox" class="select-resource"
                   value="<?= htmlReady($resource->id) ?>"
                <?= arrayToHtmlAttributes($checkbox_data) ?>>
        </td>
    <? endif ?>
    <td>
        <a href="<?= (
        $booking_plan_link_on_name
            ? $resource->getActionLink('booking_plan')
            : $resource->getActionLink('show')
        ) ?>"
            <?= !empty($user_has_booking_rights) ? '' : 'data-dialog' ?>
           data-id="<?= htmlReady($resource->id) ?>"
           data-range_type="<?= $clipboard_range_type
               ? htmlReady($clipboard_range_type)
               : 'Resource' ?>"
           data-name="<?= htmlReady($resource->name) ?>"
           <?= $clipboard_range_type ? 'class="clipboard-draggable-item"' : '' ?>>
            <? if ($show_picture): ?>
                <? $picture_url = $resource->getPictureUrl(); ?>
                <? if ($picture_url): ?>
                    <img class="small-resource-picture"
                         src="<?= htmlReady($picture_url) ?>">
                <? else: ?>
                    <?= $resource->getIcon('clickable') ?>
                <? endif ?>
                <span class="text-bottom">
                    <?= htmlReady(
                        $show_full_name
                            ? $resource->getFullName()
                            : $resource->name
                    ) ?>
                </span>
            <? else: ?>
                <?= htmlReady($resource->name) ?>
                <?= Icon::create('link-intern')->asImg(['class' => 'text-bottom']) ?>
            <? endif ?>
        </a>
        <? if (!empty($resource_tooltip)): ?>
            <span class="text-bottom">
                <?= tooltipIcon($resource_tooltip) ?>
            </span>
        <? endif ?>
    </td>
    <? if ($additional_properties): ?>
        <? foreach ($additional_properties as $additional_property): ?>
            <td>
                <? $value = null;
                $property = $resource->getPropertyObject($additional_property);
                if ($property instanceof ResourceProperty) {
                    $value = $property->__toString();
                } elseif($resource->isField($additional_property)) {
                    //There is a SORM field with the name $additional_property.
                    $value = $resource->__get($additional_property);
                }
                ?>
                <?= htmlReady($value) ?>
            </td>
        <? endforeach ?>
    <? endif ?>
    <? if (!empty($additional_columns)): ?>
        <? foreach ($additional_columns as $column): ?>
            <td>
                <?= htmlReady($column) ?>
            </td>
        <? endforeach ?>
    <? endif ?>
    <? if ($show_user_actions || $show_autor_actions
        || $show_tutor_actions || $show_admin_actions
        || $show_global_admin_actions || $additional_actions): ?>
        <td class="actions">
            <?= $this->render_partial('resources/_common/_action_menu.php',
                compact(
                    'show_user_actions',
                    'show_autor_actions',
                    'show_autor_actions',
                    'show_admin_actions',
                    'show_global_admin_actions',
                    'additional_actions',
                    'resource'
                )
            );?>
        </td>
    <? endif ?>
</tr>
