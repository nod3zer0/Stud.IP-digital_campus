<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
//
// Copyright (C) 2014 Arne Schröder <schroeder@data-quest>,
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+
/**
 * HelpTourSteps.class.php - model class for tour steps
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Arne Schröder <schroeder@data-quest>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property array $id alias for pk
 * @property string $tour_id database column
 * @property int $step database column
 * @property string $title database column
 * @property string $tip database column
 * @property string $orientation database column
 * @property int $interactive database column
 * @property string $css_selector database column
 * @property string $route database column
 * @property string $action_prev database column
 * @property string $action_next database column
 * @property string $author_email database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property HelpTour $help_tour belongs_to HelpTour
 * @property User $author has_one User
 */
class HelpTourStep extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'help_tour_steps';

        $config['belongs_to']['help_tour'] = [
            'class_name'  => HelpTour::class,
            'foreign_key' => 'tour_id',
        ];

        $config['has_one']['author'] = [
            'class_name'  => User::class,
            'foreign_key' => 'author_email',
            'assoc_func'  => 'findOneByEmail',
        ];

        $config['registered_callbacks']['after_store'][] = 'cbUpdateTour';

        parent::configure($config);
    }


    public function cbUpdateTour()
    {
        if (!$this->help_tour) {
            return;
        }

        $this->help_tour->author_email = $this->author_email;
        $this->help_tour->chdate = $this->chdate;
        if ($this->help_tour->isDirty()) {
            $this->help_tour->store();
        }
    }


    /**
     * checks, if tour step data is complete
     *
     * @todo Das Model sollte nix über PageLayout wissen, das sollte anders raus transportiert werden
     * @return boolean true or false
     */
    public function validate() {
        if (!$this->orientation)
            $this->orientation = 'B';
        if (!$this->title && !$this->tip) {
            PageLayout::postMessage(MessageBox::error(_('Der Schritt muss einen Titel oder Inhalt besitzen.')));
            return false;
        }
        if (!$this->route) {
            PageLayout::postMessage(MessageBox::error(_('Ungültige oder fehlende Angabe zur Seite, für die der Schritt angezeigt werden soll.')));
            return false;
        }
        return true;
    }
}

