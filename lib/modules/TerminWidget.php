<?php
/*
 * TerminWidget.php - A portal plugin for dates
 *
 * Copyright (C) 2014 - André Klaßen <klassen@elan-ev.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class TerminWidget extends CorePlugin implements PortalPlugin
{
    public function getPluginName()
    {
        return _('Meine aktuellen Termine');
    }

    public function getMetadata()
    {
        return [
            'description' => _('Mit diesem Widget haben Sie ihre aktuellen Termine im Überlick.')
        ];
    }

    public function getPortalTemplate()
    {
        $controller = app(\Trails_Dispatcher::class)->load_controller('calendar/contentbox');
        $response = $controller->relay('calendar/contentbox/display/'.$GLOBALS['user']->id);
        $template = $GLOBALS['template_factory']->open('shared/string');
        $template->content = $response->body;

        $navigation = new Navigation('', 'dispatch.php/calendar/single/week', ['self' => true]);
        $navigation->setImage(Icon::create('add', 'clickable', ["title" => _('Neuen Termin anlegen')]));
        $template->icons = [$navigation];

        return $template;
    }
}
