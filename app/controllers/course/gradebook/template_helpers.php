<?php

use Grading\Definition;

trait GradebookTemplateHelpers
{
    public function formatAsPercent($value)
    {
        return (float) (round($value * 1000) / 10);
    }

    public function formatCategory($category)
    {
        return htmlReady(Definition::CUSTOM_DEFINITIONS_CATEGORY === $category ? _('Manuell eingetragen') : $category);
    }

    public function getNormalizedWeight(Definition $definition)
    {
        return $this->sumOfWeights ? $definition->weight / $this->sumOfWeights : 0;
    }

    protected function getSumOfWeights($gradingDefinitions)
    {
        $sumOfWeights = 0;
        foreach ($gradingDefinitions as $def) {
            $sumOfWeights += $def->weight;
        }

        return $sumOfWeights;
    }

    protected function getGroupedDefinitions($gradingDefinitions)
    {
        $groupedDefinitions = [];
        foreach ($gradingDefinitions as $def) {
            if (!isset($groupedDefinitions[$def->category])) {
                $groupedDefinitions[$def->category] = [];
            }
            $groupedDefinitions[$def->category][] = $def;
        }

        return $groupedDefinitions;
    }

    protected function setupLecturerSidebar()
    {
        $export = new \ExportWidget();
        $export->addLink(
            _('Leistungen als CSV exportieren'),
            $this->url_for('course/gradebook/lecturers/export'),
            Icon::create('export')
        );
        \Sidebar::Get()->addWidget($export);
    }

    protected function setupStudentsSidebar()
    {
        $export = new \ExportWidget();
        $export->addLink(
            _('Leistungen exportieren'),
            $this->url_for('course/gradebook/students/export'),
            Icon::create('export')
        );
        \Sidebar::Get()->addWidget($export);
    }

    protected function viewerIsStudent()
    {
        return !$this->viewerHasPerm('tutor');
    }

    protected function viewerIsLecturer()
    {
        return $this->viewerHasPerm('tutor');
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function viewerHasPerm($perm)
    {
        return $GLOBALS['perm']->have_studip_perm($perm, \Context::getId());
    }

    protected function setDefaultPageTitle()
    {
        \PageLayout::setTitle(Context::getHeaderLine().' - Gradebook');
        \PageLayout::setHelpKeyword("Basis.Gradebook");
    }

    protected function setupIliasSidebar($num_definitions = 0)
    {
        $ilias = new \LinksWidget();
        $ilias->setTitle(_('ILIAS'));
        $ilias->addLink(
            _('Test als Leistung hinzufügen'),
            $this->url_for('course/gradebook/lecturers/new_ilias_definition'),
            Icon::create('learnmodule+add')
        )->asDialog();
        if ($num_definitions) {
            $ilias->addLink(
                _('Ergebnisse aus ILIAS importieren'),
                $this->url_for('course/gradebook/lecturers/import_ilias_results'),
                Icon::create('refresh')
            );
        }
        \Sidebar::Get()->addWidget($ilias);
    }
}
