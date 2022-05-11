<?php
/**
 * Contents  widget.
 *
 * @author  David Siegfried <ds.siegfried@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 5.0
 */

class ContentsWidget extends CorePlugin implements PortalPlugin
{
    public function getPluginName()
    {
        return _('Mein Arbeitsplatz');
    }

    public function getMetadata()
    {
        return [
            'description' => _('Mit diesem Widget haben Sie einen Überblick über Ihre Inhalte.')
        ];
    }

    public function getPortalTemplate()
    {
        $template = $GLOBALS['template_factory']->open('start/contents');
        $template->tiles = Navigation::getItem('/contents');
        return $template;
    }
}
