<?php

/*
 * Copyright (C) 2011 - <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

use \Studip\LinkButton;

class LinkButtonTestCase extends \Codeception\Test\Unit
{
    function testCreate()
    {
        $this->assertNotNull(LinkButton::create());
    }

    function testCreateWithLabel()
    {
        $this->assertXmlStringEqualsXmlString(
            '<a class="button" href="?">yes</a>',
            (string) LinkButton::create('yes')
        );
    }

    function testCreateWithLabelAndUrl()
    {
        $this->assertXmlStringEqualsXmlString(
            '<a class="button" href="http://example.net">yes</a>',
            (string) LinkButton::create('yes', 'http://example.net')
        );
    }

    function testCreateWithLabelAndArray()
    {
        $this->assertXmlStringEqualsXmlString(
            '<a a="1" b="2" class="button" href="?">yes</a>',
            (string) LinkButton::create('yes', ['a' => 1, 'b' => 2])
        );
    }

    function testCreateWithLabelUrlAndArray()
    {
        $this->assertXmlStringEqualsXmlString(
            '<a a="1" b="2" class="button" href="http://example.net">yes</a>',
            (string) LinkButton::create('yes', 'http://example.net', ['a' => 1, 'b' => 2])
        );
    }

    function testCreateAccept()
    {
        $this->assertXmlStringEqualsXmlString(
            '<a class="accept button" href="?" name="accept">Übernehmen</a>',
            (string) LinkButton::createAccept()
        );
    }

    function testCreateCancel()
    {
        $this->assertXmlStringEqualsXmlString(
            '<a class="cancel button" href="?" name="cancel">Abbrechen</a>',
            (string) LinkButton::createCancel()
        );
    }

    function testCreatePreOrder()
    {
        $this->assertXmlStringEqualsXmlString(
            '<a class="pre-order button" href="?" name="pre-order">ok</a>',
            (string) LinkButton::createPreOrder()
        );
    }

    function testCreateWithInsaneArguments()
    {
        $this->assertXmlStringEqualsXmlString(
            '<a class="button" href="http://example.net?m=&amp;m=" mad="&lt;S&gt;tu&quot;ff">&gt;ok&lt;</a>',
            (string) LinkButton::create('>ok<', 'http://example.net?m=&m=', ['mad' => '<S>tu"ff'])
        );
    }
}
