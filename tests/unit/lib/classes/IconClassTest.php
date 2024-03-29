<?php
/*
 * Copyright (C) 2015 <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class IconClassTest extends \Codeception\Test\Unit
{
    private $memo_assets_url;

    function setUp(): void
    {
        $this->memo_assets_url = Assets::url();
        Assets::set_assets_url('');
    }

    function tearDown(): void
    {
        Assets::set_assets_url($this->memo_assets_url);
    }

    function testIconCreateAsImg()
    {
        $this->assertEquals(
            '<img width="16" height="16" src="images/icons/blue/vote.svg" alt="" class="icon-role-clickable icon-shape-vote">',
            Icon::create('vote', 'clickable')->asImg()
        );
    }

    function testIconCreateAsImgWithAddition()
    {
        $this->assertEquals(
            '<img width="16" height="16" src="images/icons/blue/vote.svg" alt="" class="icon-role-clickable icon-shape-vote">',
            Icon::create('vote', 'clickable')->asImg()
        );
    }

    function testIconCreateAsImgWithSize()
    {
        $this->assertEquals(
            '<img width="20" height="20" src="images/icons/blue/vote.svg" alt="" class="icon-role-clickable icon-shape-vote">',
            Icon::create('vote', 'clickable')->asImg(20)
        );
    }

    function testIconCreateAsImgWithTitle()
    {
        $this->assertEquals(
            '<img title="Mit Anhang" width="20" height="20" src="images/icons/blue/vote.svg" class="icon-role-clickable icon-shape-vote">',
            Icon::create('vote', 'clickable', ['title' => _("Mit Anhang")])->asImg(20)
        );
    }

    function testIconCreateAsImgWithHspace()
    {
        $this->assertEquals(
            '<img hspace="3" width="16" height="16" src="images/icons/blue/arr_2left.svg" alt="" class="icon-role-clickable icon-shape-arr_2left">',
            Icon::create('arr_2left', 'clickable')->asImg(['hspace' => 3])
        );
    }

    function testIconCreateAsImgWithClass()
    {
        $this->assertEquals(
            '<img class="text-bottom icon-role-info icon-shape-staple" width="20" height="20" src="images/icons/black/staple.svg" alt="">',
            Icon::create('staple', 'info')->asImg(20, ['class' => 'text-bottom'])
        );
    }

    function testIconCreateAsImgWithClassAndTitle()
    {
        $this->assertEquals(
            '<img title="Datei hochladen" class="text-bottom icon-role-new icon-shape-upload" width="20" height="20" src="images/icons/red/upload.svg">',
            Icon::create('upload', 'new', ['title' => _("Datei hochladen")])
                ->asImg(20, ['class' => 'text-bottom'])
        );
    }

    function testIconCreateAsInput()
    {
        $this->assertEquals(
            '<input type="image" class="text-bottom icon-role-clickable icon-shape-upload" width="20" height="20" src="images/icons/blue/upload.svg" alt="">',
            Icon::create('upload', 'clickable')->asInput(20, ['class' => 'text-bottom'])
        );
    }

    function testIconIsImmutable()
    {
        $icon = Icon::create('upload', 'clickable', ['title' => _('a title')]);
        $copy = $icon->copyWithRole('clickable');

        $this->assertNotSame($icon, $copy);
    }

    function testIconCopyWithRole()
    {
        $icon = Icon::create('upload', 'clickable', ['title' => _('a title')]);
        $copy = $icon->copyWithRole('info');

        $this->assertEquals($icon->getShape(),      $copy->getShape());
        $this->assertNotEquals($icon->getRole(),    $copy->getRole());
        $this->assertEquals($icon->getAttributes(), $copy->getAttributes());
    }

    function testIconCopyWithShape()
    {
        $icon = Icon::create('upload', 'clickable', ['title' => _('a title')]);
        $copy = $icon->copyWithShape('staple');

        $this->assertNotEquals($icon->getShape(),   $copy->getShape());
        $this->assertEquals($icon->getRole(),       $copy->getRole());
        $this->assertEquals($icon->getAttributes(), $copy->getAttributes());
    }

    function testIconCopyWithAttributes()
    {
        $icon = Icon::create('upload', 'clickable', ['title' => _('a title')]);
        $copy = $icon->copyWithAttributes(['title' => _('another title')]);

        $this->assertEquals($icon->getShape(),         $copy->getShape());
        $this->assertEquals($icon->getRole(),          $copy->getRole());
        $this->assertNotEquals($icon->getAttributes(), $copy->getAttributes());
    }

    function testStaticIcon()
    {
        $icon = Icon::create('https://i.imgur.com/kpTtTh.gif');
        $this->assertEquals($icon->asImagePath(), 'https://i.imgur.com/kpTtTh.gif');
    }

    function testIconCreateAsCSSWithSize()
    {
        $this->assertEquals(
            'background-image:url(images/icons/blue/vote.svg);background-size:17px 17px;',
            Icon::create('vote', 'clickable')->asCSS(17)
        );
    }

    function testIconCreateAsImagePath()
    {
        $this->assertEquals(
            'images/icons/blue/vote.svg',
            Icon::create('vote', 'clickable')->asImagePath()
        );
    }

    function testIconCreateAsImgWithoutSize()
    {
        $this->assertEquals(
            '<img src="images/icons/blue/vote.svg" alt="" class="icon-role-clickable icon-shape-vote">',
            Icon::create('vote', 'clickable')->asImg(false)
        );
    }

    function testIconCreateAsInputWithoutSize()
    {
        $this->assertEquals(
            '<input type="image" src="images/icons/blue/upload.svg" alt="" class="icon-role-clickable icon-shape-upload">',
            Icon::create('upload', 'clickable')->asInput(false)
        );
    }
}
