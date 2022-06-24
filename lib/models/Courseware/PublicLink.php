<?php

namespace Courseware;

/**
* Courseware's template.
*
* @author  Ron Lucke <lucke@elan-ev.de>
* @license GPL2 or any later version
*
* @since   Stud.IP 5.2
*
* @property string                         $id                     database column
* @property int                            $structural_element_id  database column
* @property string                         $password               database column
* @property int                            $expire_date            database column
* @property int                            $mkdate                 database column
* @property int                            $chdate                 database column
*/
class PublicLink extends \SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_public_links';

        $config['belongs_to']['structural_element'] = [
            'class_name' => StructuralElement::class,
            'foreign_key' => 'structural_element_id',
        ];

        parent::configure($config);
    }

    public function canVisitElement(StructuralElement $structuralElement): bool
    {
        if (!$structuralElement) {
            return false;
        }

        if ($structuralElement->isRootNode()) {
            return $this->structural_element_id === $structuralElement->id;
        }

        if ($this->structural_element_id === $structuralElement->id) {
            return true;
        }

        return $this->canVisitElement($structuralElement->parent);
    }

    public function isExpired(): bool
    {
        if (!$this->expire_date) {
            return false;
        }

        return time() > $this->expire_date;
    }
}
