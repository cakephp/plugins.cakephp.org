<?php

namespace App\Test\TestCase\View\Helper;

use App\View\Helper\ResourceHelper;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Cake\View\View;

class ResourceHelperTest extends TestCase
{
    /**
     * @var \App\View\Helper\ResourceHelper
     */
    public $Resource;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->View = new View();
        $this->Resource = new ResourceHelper($this->View);
        $this->_appNamespace = Configure::read('App.namespace');
        static::setAppNamespace();
    }

    /**
     * Test tagLink method.
     */
    public function testTagLink()
    {
        $expected = '<a href="/packages?has%5B0%5D=readme" class="label category-label" style="background-color:gray;color:white">has:readme</a>';
        $result = $this->Resource->tagLink('has:readme');
        $this->assertSame($expected, $result);
    }

    /**
     * Test tagLink method.
     */
    public function testTagLinkVersion()
    {
        $expected = '<a href="/packages?version=2" class="label category-label" style="background-color:#9dd5c0;color:#363637">version:2.x</a>';
        $result = $this->Resource->tagLink('version:2');
        $this->assertSame($expected, $result);
    }

    /**
     * Test tagLink method.
     */
    public function testTagContrastText()
    {
        $expected = '<a href="/packages?keyword%5B0%5D=cakephp" class="label category-label" style="background-color:darkgray;color:white">keyword:cakephp</a>';
        $result = $this->Resource->tagLink('keyword:cakephp');
        $this->assertSame($expected, $result);
    }

    /**
     * Test contrastColor method.
     */
    public function testContrastColor()
    {
        $expected = '#363637';
        $result = $this->Resource->contrastColor('#FFFFFF');
        $this->assertSame($expected, $result);

        $expected = 'white';
        $result = $this->Resource->contrastColor('#000000');
        $this->assertSame($expected, $result);
    }
}
