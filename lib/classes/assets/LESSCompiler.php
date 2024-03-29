<?php
namespace Assets;

use Assets;
use StudipCacheFactory;

use ILess\Autoloader;
use ILess\Importer\FileSystemImporter;
use ILess\Parser;

/**
 * LESS Compiler for assets.
 *
 * Uses ILess by mishal <https://github.com/mishal/iless>.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.4
 * @deprecated since Stud.IP 5.4 and will be removed in Stud.IP 6.0
 */
class LESSCompiler implements Compiler
{
    const CACHE_KEY = '/assets/less-prefix';

    private static $instance = null;

    /**
     * Returns an instance of the compiler
     * @return Assets\LESSCompiler instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor to enforce singleton.
     */
    private function __construct()
    {
        Autoloader::register();
    }

    /**
     * Compiles a less string. This method will add all neccessary imports
     * and variables for Stud.IP so almost all mixins and variables of the
     * core system can be used. This includes colors and icons.
     *
     * @param string $input      LESS content to compile
     * @param array  $variables Additional variables for the LESS compilation
     * @return string containing the generated CSS
     */
    public function compile($input, array $variables = []): string
    {
        // Show deprecation notice
        if (\Studip\ENV === 'development') {
            \PageLayout::postMessage(
                \MessageBox::info(
                    _('Das Verwenden von LESS-Stylesheets in Plugins ist deprecated und wird zu Stud.IP 6.0 entfernt.'),
                    [
                        _('Als Alternative steht die Verwendung von SCSS bereit.'),
                        _('Bitte stellen Sie Ihre Plugins entsprechend um bzw. geben den Plugin-AutorInnen Bescheid.'),
                    ]
                ),
                'less-deprecation-notice'
            );
        }

        $less = $this->getPrefix() . $input;

        $variables['image-path'] = '"' . Assets::url('images') . '"';

        // Disable warnings since we currently have no other means to get rid
        // of them
        // TODO: Look again into this (2022-06-23)
        $error_reporting = error_reporting();
        error_reporting($error_reporting & ~E_WARNING);

        $parser = new Parser(['strictMath' => true], null, [
            new FileSystemImporter(["{$GLOBALS['STUDIP_BASE_PATH']}/resources/"])
        ]);
        $parser->setVariables($variables);
        $parser->parseString($less);
        $css = $parser->getCSS();

        // Restore error reporting
        error_reporting($error_reporting);

        return $css;
    }

    /**
     * Generates the less prefix containing the variables and mixins of the
     * Stud.IP core system.
     * This prefix will be cached in Stud.IP's cache in order to minimize
     * disk accesses.
     *
     * @return String containing the neccessary prefix
     */
    private function getPrefix()
    {
        $cache = StudipCacheFactory::getCache();

        $prefix = $cache->read(self::CACHE_KEY);

        if ($prefix === false) {
            $prefix = '';

            // Load mixins and change relative to absolute filenames
            $mixin_file = $GLOBALS['STUDIP_BASE_PATH'] . '/resources/assets/stylesheets/mixins.less';
            foreach (file($mixin_file) as $mixin) {
                if (!preg_match('/@import(.*?) "(.*)";/', $mixin, $match)) {
                    continue;
                }

                $core_file = "{$GLOBALS['STUDIP_BASE_PATH']}/resources/assets/stylesheets/{$match[2]}";
                $prefix .= sprintf('@import%s "%s";' . "\n", $match[1], $core_file);
            }

            // Add adjusted image paths
            $prefix .= sprintf('@image-path: "%s";', Assets::url('images')) . "\n";
            $prefix .= '@icon-path: "@{image-path}/icons/16";' . "\n";

            $cache->write(self::CACHE_KEY, $prefix);
        }
        return $prefix;
    }
}
