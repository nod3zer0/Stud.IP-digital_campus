<?php

use Courseware\StructuralElement;
use Courseware\Unit;

abstract class CoursewareController extends AuthenticatedController
{
    public function before_filter(&$action, &$args): void
    {
        parent::before_filter($action, $args);

        if ($action === 'index' && Request::int('element_id')) {
            $element = StructuralElement::find(Request::int('element_id'));
            $this->redirect($this->action_url('courseware#/structural_element/' . $element->id, $element->findUnit()->id));
        }
    }

    public function redirectToFirstUnit(string $context, string $rangeId, array $last): void
    {
        $path = $context === 'user' ? 'contents' : $context;
        $last_element = $this->getLastElement($last, $context, $rangeId);
        if ($last_element) {
            $unit = $last_element->findUnit();
        } else {
            $unit = Unit::findOneBySql('range_id = ? ORDER BY mkdate ASC', [$rangeId]);
        }
        $this->redirect($path . '/courseware/courseware/' . $unit->id);
    }

    public function setEntryElement(string $context, Unit $unit, array $last, string $rangeId): void
    {
        $this->unit_id = $unit->id;
        $last_element = $this->getLastElement($last, $context, $rangeId);

        if ($last_element) {
            $last_element_unit = $last_element->findUnit();
        }
        if (isset($last_element_unit) && $last_element_unit->id === $unit->id && $last_element_unit->hasRootLayout()) {
            $this->entry_element_id = $last_element->id;
        } else {
            $this->entry_element_id = $unit->findOrCreateFirstElement()->id;
        }
        if ($this->entry_element_id) {
            $last_element_item = $context === 'user' ? 'global' : $rangeId;
            $last[$last_element_item] = $this->entry_element_id;
            UserConfig::get($GLOBALS['user']->id)->store('COURSEWARE_LAST_ELEMENT', $last);
        }
    }

    public function getLastElement(array $last, string $context, string $rangeId): ?StructuralElement
    {
        $last_element_item = $context === 'user' ? 'global' : $rangeId;
        $last_element_id = $last[$last_element_item] ?? false;

        if ($last_element_id) {
            return StructuralElement::findOneBySQL("id = ? AND range_id = ? AND range_type = ?", [
                $last_element_id,
                $rangeId,
                $context
            ]);
        }

        return null;
    }

    public function getLicenses(): string
    {
        $licenses = License::findAndMapBySQL(
            function (License $license) {
                return $license->toArray();
            },
            '1 ORDER BY name ASC'
        );

        return json_encode($licenses);
    }

    public function setCoursewareSidebar(): void
    {
        $sidebar = \Sidebar::Get();
        $sidebar->addWidget(new VueWidget('courseware-action-widget'));
        SkipLinks::addIndex(_('Aktionen'), 'courseware-action-widget', 21);
        $sidebar->addWidget(new VueWidget('courseware-search-widget'));
    }
}
