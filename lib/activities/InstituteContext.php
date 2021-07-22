<?php

/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class InstituteContext extends Context
{
    private $institute;

    /**
     * create new institute-context
     *
     * @param string $institute_id
     */
    public function __construct($institute, $observer)
    {
        $this->institute = $institute;
        $this->observer = $observer;
    }

    /**
     * {@inheritdoc}
     */
    protected function getProvider()
    {
        if (!$this->provider) {
            $institute = $this->institute;

            $module_provider = [
                'CoreForum' => 'ForumProvider',
                'CoreDocuments' => 'DocumentsProvider',
                'CoreWiki' => 'WikiProvider',
            ];

            foreach ($institute->tools as $tool) {
                $studip_module = $tool->getStudipModule();
                if($studip_module) {
                    if (isset($module_provider[get_class($studip_module)])) {
                        $this->addProvider('Studip\Activity\\'. $module_provider[get_class($studip_module)]);
                    } elseif ($studip_module instanceof ActivityProvider) {
                        $this->provider[$studip_module->getPluginName()] = $studip_module;
                    }
                }
            }
            //news
            $this->addProvider('Studip\Activity\NewsProvider');
        }

        return $this->provider;
    }

    /**
     * {@inheritdoc}
     */
    public function getRangeId()
    {
        return $this->institute->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getContextType()
    {
        return \Context::INSTITUTE;
    }

        /**
     * {@inheritdoc}
     */
    public function getContextFullname($format = 'default')
    {
        return $this->institute->getFullname($format);
    }
}
