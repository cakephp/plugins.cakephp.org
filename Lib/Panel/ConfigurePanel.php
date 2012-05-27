<?php
class ConfigurePanel extends DebugPanel {

  var $elementName = 'configure_panel';
  var $title = 'Configure';

  function beforeRender(&$controller) {
    return array(
      'configure' => Configure::read(),
    );
  }
}