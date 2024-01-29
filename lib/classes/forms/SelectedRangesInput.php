<?php

namespace Studip\Forms;

class SelectedRangesInput extends Input
{
    protected $selectable_items = [];

    protected $selected_items = [];

    protected $search_type = null;


    public function render()
    {
        $template = $GLOBALS['template_factory']->open('forms/selected_ranges_input');
        $template->name = $this->name;
        $template->selectable_items = $this->selectable_items;
        $template->selected_items = [];
        foreach ($this->selected_items as $item) {
            $item_data = [];
            if ($item instanceof \Range) {
                $item_data['name'] = $item->getFullname();
                $item_data['id'] = $item->getRangeId();
            } elseif ($item instanceof \StudipItem) {
                $item_data['name'] = $item->getItemName();
                $item_data['id'] = $item->id;
            } elseif (
                is_array($item)
                && array_key_exists('name', $item)
                && array_key_exists('id', $item)
            ) {
                $item_data['name'] = $item['name'];
                $item_data['id'] = $item['id'];
            }
            if ($item_data) {
                $template->selected_items[] = $item_data;
            }
        }
        $template->searchtype = $this->search_type;
        return $template->render();
    }

    public function getRequestValue()
    {
        return \Request::getArray($this->name);
    }

    public function setSelectedItems(array $items)
    {
        $this->selected_items = $items;
    }

    public function setSearchType(\SearchType $search_type)
    {
        $this->search_type = $search_type;
    }
}
