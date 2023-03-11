<?php
$children     = $resource->children;
$has_children = count($children) > 0;
$current_user_is_resource_admin = $resource->userHasPermission(User::findCurrent(), 'admin');
$current_user_is_resource_autor = $resource->userHasPermission(User::findCurrent(), 'autor');
$current_user_is_resource_tutor = $resource->userHasPermission(User::findCurrent(), 'tutor');
$booking_plan_is_visible        = $resource->bookingPlanVisibleForUser(User::findCurrent());
?>
<article class="studip <?= $has_children ? 'toggle' : ((string)$resource->description === '' ? 'empty' : '') ?> <?= $open ? 'open' : '' ?>">
    <header>
        <h1>
            <a href="#"><?= htmlReady($resource->getFullName()) ?></a>
        </h1>
        <?= ActionMenu::get()
            ->addLink(
                $resource->getActionURL('show'),
                _('Informationen anzeigen'),
                Icon::create('info-circle'),
                ['data-dialog' => 'sitze=auto']
            )
            ->condition($current_user_is_resource_admin)
            ->addLink(
                $resource->getActionURL('edit'),
                _('Bearbeiten'),
                Icon::create('edit'),
                ['data-dialog' => 'size=auto']
            )
            ->condition($current_user_is_resource_admin)
            ->addLink(
                $resource->getActionURL('permissions'),
                _('Rechte bearbeiten'),
                Icon::create('roles'),
                ['data-dialog' => 'size=auto']
            )
            ->conditionAll(($resource instanceof Room && $current_user_is_resource_autor) || $booking_plan_is_visible)
            ->addLink(
                $resource->getActionURL('booking_plan'),
                _('Wochenbelegung'),
                Icon::create('timetable'),
                ['target' => '_blank']
            )
            ->addLink(
                $resource->getActionURL('semester_plan'),
                _('Semesterbelegung'),
                Icon::create('timetable'),
                ['target' => '_blank']
            )
            ->conditionAll(null)
            ->condition($resource instanceof Room && $current_user_is_resource_tutor && $resource->requestable)
            ->addLink(
                $resource->getActionURL('request_list'),
                _('Raumanfragen anzeigen'),
                Icon::create('room-request'),
                ['target' => '_blank']
            )
            ->render();
        ?>
    </header>
    <? if ($has_children) : ?>
        <section>
            <? foreach ($children as $child) : ?>
                <?= $this->render_partial(
                    'resources/_common/_resource_tree_item',
                    [
                        'resource' => $child,
                        'open'     => false
                    ]
                ) ?>
            <? endforeach ?>
        </section>
    <? else : ?>
        <? if ((string)$resource->description !== '') : ?>
            <section>
                <?= htmlReady($resource->description) ?>
            </section>
        <? endif ?>
    <? endif ?>
</article>
