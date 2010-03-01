<?php
class AppController extends Controller {
	var $components = array('DebugKit.Toolbar', 'Session');
	var $helpers = array('Form', 'Html', 'Resource', 'Session');
}
?>