<?php
//Build the actions as array. Ordering is done by array indexes.

$actions = [];
if ($show_user_actions) {
    $actions['0010'] = [
        $resource->getActionLink('show'),
        _('Details'),
        Icon::create('info-circle'),
        ['data-dialog' => 'size=auto']
    ];

    $actions['0020'] = [
        $resource->getActionLink('booking_plan'),
        _('Belegungsplan'),
        Icon::create('timetable')
    ];

    $actions['0030'] = [
        $resource->getActionLink('semester_plan'),
        _('Semester-Belegungsplan'),
        Icon::create('timetable'),
        ['target' => '_blank']
    ];
    if ($show_admin_actions) {
        $actions['0040'] = [
            $resource->getActionLink('permissions'),
            _('Berechtigungen verwalten'),
            Icon::create('roles2'),
            ['data-dialog' => 'size=auto']
        ];
        $actions['0050'] = [
            $resource->getActionLink('temporary_permissions'),
            _('Temporäre Berechtigungen verwalten'),
            Icon::create('roles2'),
            ['data-dialog' => 'size=auto']
        ];
        $actions['0060'] = [
            $resource->getActionLink('edit'),
            _('Bearbeiten'),
            Icon::create('edit'),
            ['data-dialog' => 'size=auto']
        ];
    }
    if ($show_autor_actions) {
        $actions['0070'] = [
            $resource->getActionLink(
                'assign-undecided',
                ['no_reload' => 1]
            ),
            _('Buchen'),
            Icon::create('lock-locked'),
            ['data-dialog' => 'size=big']
        ];
        if ($show_global_admin_actions) {
            $actions['0080'] = [
                $resource->getActionLink(
                    'delete_bookings',
                    ['no_reload' => 1]
                ),
                _('Buchungen löschen'),
                Icon::create('trash'),
                ['data-dialog' => 'size=auto']
            ];
        }
    }
    if ($show_user_actions) {
        $actions['0090'] = [
            $resource->getActionLink('export_bookings'),
            _('Buchungen exportieren'),
            Icon::create('file-excel'),
            ['data-dialog' => 'size=auto']
        ];
    }
    $actions['0100'] = [
        $resource->getActionLink('files'),
        _('Dateien anzeigen'),
        Icon::create($resource->hasFiles() ? 'folder-full' : 'folder-empty')
    ];
    if ($show_global_admin_actions) {
        $actions['0110'] = [
            $resource->getActionLink('delete'),
            _('Löschen'),
            Icon::create('trash'),
            ['data-dialog' => '']
        ];
    }
} else {
    if ($resource->propertyExists('booking_plan_is_public')) {
        if ($resource->getProperty('booking_plan_is_public')) {
            $actions['0020'] = [
                $resource->getActionLink('booking_plan'),
                _('Belegungsplan anzeigen'),
                Icon::create('timetable'),
                ['target' => '_blank']
            ];
        }
    }
}
//Add additional actions for the action menu, if set:
if (isset($additional_actions) && is_array($additional_actions)) {
    $actions = array_merge($actions, $additional_actions);
}
//Now we filter and sort the actions by key:
$actions = array_filter($actions, 'is_array');
ksort($actions);
$action_menu = ActionMenu::get()->setContext($resource);
//And finally we add the actions to the action menu:
foreach ($actions as $action) {
    $action_menu->addLink(
        $action[0],
        $action[1],
        $action[2],
        (isset($action[3]) && is_array($action[3])) ? $action[3] : []
    );
}
?>
<?= $action_menu->render() ?>
