<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementPersondetailsHeader.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementTableHeader
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementPersondetailsHeader.class.php
//
// Copyright (C) 2003 Peter Thienel <pthienel@web.de>,
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


class ExternElementPersondetailsHeader extends ExternElement {

    var $attributes = ["table_width", "table_align", "table_border", "table_bgcolor",
                "table_bordercolor", "table_cellpadding", "table_cellspacing", "table_class",
                "table_style", "tr_class", "tr_style", "headlinetd_height", "headlinetd_align",
                "headlinetd_valign", "headlinetd_bgcolor", "headlinetd_class", "headlinetd_style",
                "picturetd_width", "picturetd_align", "picturetd_valign", "picturetd_bgcolor",
                "picturetd_class", "picturetd_style", "contacttd_width", "contacttd_align",
                "contacttd_valign", "contacttd_bgcolor", "contacttd_class", "contacttd_style",
                "font_face", "font_size", "font_color", "font_class", "font_style", "img_align",
                "img_border", "img_width", "img_height", "hidename"];

    /**
    * Constructor
    *
    * @param array config
    */
    function __construct($config = "") {
        if ($config)
            $this->config = $config;

        $this->name = "PersondetailsHeader";
        $this->real_name = _("Seitenkopf/Bild");
        $this->description = _("Angaben zur Gestaltung des Seitenkopfes.");
    }

    function getDefaultConfig () {
        $config = [
            "table_width" => "100%",
            "table_border" => "0",
            "table_bordercolor" => "",
            "table_cellpadding" => "0",
            "table_cellspacing" => "0",
            "hidename" => ""
        ];

        return $config;
    }

    /**
    *
    */
    function toStringEdit ($post_vars = "", $faulty_values = "",
            $edit_form = "", $anker = "") {

        $out = "";
        $table = "";
        if ($edit_form == "")
            $edit_form = new ExternEditModule($this->config, $post_vars, $faulty_values, $anker);

        $edit_form->setElementName($this->getName());
        $element_headline = $edit_form->editElementHeadline($this->real_name,
                $this->config->getName(), $this->config->getId(), TRUE, $anker);

        $headline = $edit_form->editHeadline(_("Allgemeine Angaben zum Element Seitenkopf/Bild"));

        $title = _('Überschrift (Name) ausblenden') . ':';
        $info = _("Unterdrückt die Anzeige des Namens als Überschrift.");
        $table .= $edit_form->editCheckboxGeneric("hidename", $title, $info, '1', '');

        $content_table = $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $attributes = ["table_width", "table_align", "table_border", "table_bgcolor",
                "table_bordercolor", "table_cellpadding", "table_cellspacing", "table_class",
                "table_style", "tr_class", "tr_style", "headlinetd_height", "headlinetd_align",
                "headlinetd_valign", "headlinetd_bgcolor", "headlinetd_class", "headlinetd_style",
                "font_face", "font_size", "font_color", "font_class", "font_style",
                "picturetd_width", "picturetd_align", "picturetd_valign", "picturetd_bgcolor",
                "picturetd_class", "picturetd_style", "contacttd_width", "contacttd_align",
                "contacttd_valign", "contacttd_bgcolor", "contacttd_class", "contacttd_style",];
        $headlines = ["table" => _("Tabelle Seitenkopf/Bild (HTML-Tag &lt;table&gt;)"),
                "tr" => _("Tabellenzeile Name (HTML-Tag &lt;tr&gt;)"),
                "headlinetd" => _("Tabellenzelle Name (HTML-Tag &lt;td&gt;)"),
                "font" => _("Schriftformatierung Name (HTML-Tag &lt;font&gt;)"),
                "picturetd" => _("Tabellenzelle Bild (HTML-Tag &lt;td&gt;)"),
                "contacttd" => _("Tabellenzelle Kontakt (HTML-Tag &lt;td&gt;)")];
        $content_table .= $edit_form->getEditFormContent($attributes, $headlines);
        $content_table .= $edit_form->editBlankContent();

        $headline = $edit_form->editHeadline(_("Bild"));

        $title = _('Ausrichtung') . ':';
        $info = _("Ausrichtung des Bildes.");
        $names = [_("zentriert"), _("linksbündig"), _("rechtsbündig"),
                _("obenbündig"), _("untenbündig")];
        $values = ["center", "left", "right", "top", "bottom"];
        $table = $edit_form->editOptionGeneric("img_align", $title, $info, $values, $names);

        $title = _('Rahmenbreite') . ':';
        $info = _("Breite des Bildrahmens.");
        $table .= $edit_form->editTextfieldGeneric("img_border", $title, $info, 3, 3);

        $title = _('Breite') . ':';
        $info = _("Breite des Bildes.");
        $table .= $edit_form->editTextfieldGeneric("img_width", $title, $info, 3, 3);

        $title = _('Höhe') . ':';
        $info = _("Breite des Bildes.");
        $table .= $edit_form->editTextfieldGeneric("img_height", $title, $info, 3, 3);

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();

        return $element_headline . $out;
    }

    function toString ($args = null)
    {
        return '';
    }

    function checkValue ($attribute, $value) {
        if ($attribute == "hidename") {
            if (!isset($_POST["PersondetailsHeader_$attribute"])) {
                $_POST["PersondetailsHeader_$attribute"] = 0;
                return FALSE;
            }

            return !($value == "1" || $value == "");
        }

        return FALSE;
    }

}
