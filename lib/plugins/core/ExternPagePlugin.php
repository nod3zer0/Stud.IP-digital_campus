<?php
interface ExternPagePlugin
{
    /**
     * Returns a Navigation-object. Only the title, description and the image will be used.
     * The title is used as a link to open the configuration form.
     *
     * @return Navigation with title, description and image
     */
    public function getConfigurationFormNavigation(): Navigation;

    /**
     * The name is one word describing the scope of the page. This word is part of
     * the class name of the provided ExternPage object e.g. "ExternPageModules" the
     * name is "Modules". Do not translate this string...
     *
     * @return string The name of the external page.
     */
    public function getExternPageName(): string;

    /**
     * Returns true if this is a system-wide external page (located under "Location" ;-) )
     *
     * @return boolean
     */
    public function isSystemPage(): bool;

    /**
     * Returns true if this is an external page for institutes and faculties (located under "Institutes")
     *
     * @return boolean
     */
    public function isInstitutePage(): bool;

    /**
     * Returns an object from type ExternPage. The class name begins with "ExternPage" followed by the
     * name of the external page.
     * If the name is "ModuleSearch" the class name is "ExternalPageModuleSearch"
     *
     * @return ExternPage
     */
    public function getExternPage(ExternPageConfig $config): ExternPage;

    /**
     * Returns a Flexi_Template or a path to the template file. This template contains the form
     * to configure the external page.
     *
     * @return string|Flexi_Template
     */
    public function getConfigurationFormTemplate();

}
