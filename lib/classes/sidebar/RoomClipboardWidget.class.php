<?php

/**
 * This class is a specialisation of the ClipboardWidget class.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2018-2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.5
 */
class RoomClipboardWidget extends ClipboardWidget
{
    public function __construct()
    {
        parent::__construct(['Room']);
        $this->allowed_item_class = '';
        $this->setTitle(_('Individuelle Raumgruppen'));
        $this->template = 'sidebar/room-clipboard-widget';

        $current_user = User::findCurrent();

        $this->addLink(
            _('Gruppenbelegungsplan anzeigen'),
            URLHelper::getURL('dispatch.php/room_management/planning/index/CLIPBOARD_ID'),
            Icon::create('link-intern'),
            [
                'class' => 'room-clipboard-group-action',
                'target' => '_blank'
            ]
        );

        if (ResourceManager::userHasGlobalPermission($current_user, 'autor')) {
            $this->addLink(
                _('Raumgruppe buchen'),
                URLHelper::getURL('dispatch.php/resources/booking/add/clipboard_CLIPBOARD_ID'),
                Icon::create('link-intern'),
                [
                    'class' => 'room-clipboard-group-action',
                    'data-show_in_dialog' => 'size=auto',
                    'data-needs_items '=> '1'
                ]
            );
        }
        if (ResourceManager::userHasGlobalPermission($current_user, 'admin')) {
            $this->addLink(
                _('Berechtigungen für die gesamte Raumgruppe setzen'),
                URLHelper::getURL('dispatch.php/resources/room_group/permissions/CLIPBOARD_ID'),
                Icon::create('link-intern'),
                [
                    'class' => 'room-clipboard-group-action',
                    'data-show_in_dialog' => '1'
                ]
            );
        }
    }
}
