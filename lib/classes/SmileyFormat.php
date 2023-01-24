<?php
/**
 * SmileyFormat.php
 *
 * Provides a formatting object for smileys.
 *
 * @author   Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @category Stud.IP
 * @package  smiley
 * @since    2.3
 * @uses     Smiley
 */
class SmileyFormat extends TextFormat
{
    const REGEXP = '(\>|^|\s):([_a-zA-Z][_a-z0-9A-Z-]*):(?=$|\<|\s)';

    public function __construct()
    {
        $rules = [];

        // Smiley rule
        $rules['smileys'] = [
            'start'    => self::REGEXP,
            'callback' => 'SmileyFormat::smiley'
        ];

        // Smiley short notation rule
        $needles = array_keys(Smiley::getShort());
        $needles = array_map('preg_quote', $needles);
        $rules['smileys_short'] = [
            'start'    => '(>|^|\s)(' . implode('|', $needles) . ')(?=$|<|\s)',
            'callback' => 'SmileyFormat::short'
        ];

        parent::__construct($rules);
    }

    /**
     * Smiley notation defined by name (:name:)
     */
    public static function smiley($markup, $matches)
    {
        return $matches[1] . Smiley::getByName($matches[2])->getImageTag();
    }

    /**
     * Smiley short notation as defined in database
     */
    public static function short($markup, $matches)
    {
        $smileys = Smiley::getShort();
        $name    = $smileys[$matches[2]] ?? '';
        return $name
            ? $matches[1] . Smiley::getByName($name)->getImageTag()
            : $matches[0];
    }
}
