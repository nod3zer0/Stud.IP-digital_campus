<?php

/*
 * Copyright (C) 2011 - Rasmus Fuhse <fuhse@data-quest.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


class UpdateInformationCase extends \Codeception\Test\Unit
{

    function setUp(): void {
    }


    function tearDown(): void {
    }


    function test_setgetinformation() {
        $old_data = ['test' => "TestValue"];
        UpdateInformation::setInformation("test.myfunc", $old_data);
        UpdateInformation::setInformation("test.myfunc2", "strangedata");
        $new_data = UpdateInformation::getInformation();
        $this->assertIsArray($new_data);
        $this->assertIsArray($new_data['test.myfunc']);
        $this->assertIsString($new_data['test.myfunc2']);
        $this->assertEquals("TestValue", $new_data['test.myfunc']['test']);
        $this->assertEquals("strangedata", $new_data['test.myfunc2']);
    }

    //kann man nicht automatischtesten, da abhängig von $_SERVER
    //function test_iscollecting() {}
}
