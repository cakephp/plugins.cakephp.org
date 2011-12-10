<?php
App::uses('Package', 'Model');

class PackageTestCase extends CakeTestCase {

    var $fixtures = array('app.package', 'app.maintainer', 'app.setting');

    function startTest() {
        $this->Package = ClassRegistry::init('Package');
    }

    function endTest() {
        unset($this->Package);
        ClassRegistry::flush();
    }

    function testInstance() {
        $this->assertTrue(is_a($this->Package, 'Package'));
    }

    function testFindAutocomplete() {
        $result = $this->Package->find('autocomplete', array('term' => 'lol'));
        $this->assertEqual($result, json_encode(array()));

        $result = $this->Package->find('autocomplete', array('term' => 'Lorem'));
        $result = json_decode($result);
        $this->assertEqual(count($result), 1);
        $this->assertEqual(count(get_object_vars($result[0])), 4);
        $this->assertEqual($result[0]->id, 1);
        $this->assertEqual($result[0]->slug, 'Lorem-ipsum-dolor-sit-amet/Lorem-ipsum-dolor-sit-amet');

        $this->expectException('InvalidArgumentException');
        $this->Package->find('autocomplete');
    }


}