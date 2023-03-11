<?php
/**
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @since Stud.IP 5.4
 */
class ActionMenuTest extends \Codeception\Test\Unit
{
    public function setup(): void
    {
        Config::set(new Config(['ACTION_MENU_THRESHOLD' => 1]));
    }

    public function testClassShouldExist(): void
    {
        $this->assertTrue(class_exists(ActionMenu::class));
    }

    /**
     * @covers ActionMenu::get
     */
    public function testInstanceGeneration(): void
    {
        $actionmenu = ActionMenu::get();

        $this->assertInstanceOf(ActionMenu::class, $actionmenu);
        $this->assertNotSame($actionmenu, ActionMenu::get());
    }

    public function testThreshold(): void
    {
        $actionmenu = ActionMenu::get();

        $this->assertEquals(ActionMenu::RENDERING_MODE_ICONS, $actionmenu->getRenderingMode());

        $actionmenu->addLink('#1', 'foo');
        $this->assertEquals(ActionMenu::RENDERING_MODE_ICONS, $actionmenu->getRenderingMode());

        $actionmenu->addLink('#2', 'bar');
        $this->assertEquals(ActionMenu::RENDERING_MODE_MENU, $actionmenu->getRenderingMode());
    }

    /**
     * @covers ActionMenu::setRenderingMode
     * @covers ActionMenu::getRenderingMode
     */
    public function testForcingOfRenderingMode(): void
    {
        $actionmenu = ActionMenu::get();
        $this->assertEquals(ActionMenu::RENDERING_MODE_ICONS, $actionmenu->getRenderingMode());

        $actionmenu->setRenderingMode(ActionMenu::RENDERING_MODE_ICONS);
        $this->assertEquals(ActionMenu::RENDERING_MODE_ICONS, $actionmenu->getRenderingMode());

        $actionmenu->setRenderingMode(ActionMenu::RENDERING_MODE_MENU);
        $this->assertEquals(ActionMenu::RENDERING_MODE_MENU, $actionmenu->getRenderingMode());

        $actionmenu->setRenderingMode(null);
        $this->assertEquals(ActionMenu::RENDERING_MODE_ICONS, $actionmenu->getRenderingMode());
    }
}
