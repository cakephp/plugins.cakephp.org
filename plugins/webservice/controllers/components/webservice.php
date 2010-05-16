<?php
/**
 * WebservicesComponent
 * 
 * Triggers the Webservice View
 *
 * @package webservice
 * @author Jose Diaz-Gonzalez
 * @copyright SeatGeek
 * @version 1.0
 **/

class WebserviceComponent extends Object {

/**
 * Array containing the names of components this controller uses. Component names
 * should not contain the "Component" portion of the classname.
 *
 * @var array
 * @access public
 */
	var $components = array('RequestHandler');

/**
 * Called before the Controller::beforeFilter().
 *
 * @param object  A reference to the controller
 * @return void
 * @access public
 * @link http://book.cakephp.org/view/65/MVC-Class-Access-Within-Components
 */
	function initialize(&$controller, $settings = array()) {
		if (in_array($this->RequestHandler->ext, array('json', 'xml'))) {
			$controller->view = 'Webservice.Webservice';
		}
	}
}
?>