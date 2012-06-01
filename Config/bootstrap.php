<?php
App::uses('MiCache', 'Lib');

function diebug($var = false, $showHtml = true, $showFrom = true, $die = true) {
    if (Configure::read('debug') == 0) return;

	$file = '';
	$line = '';
	if ($showFrom) {
		$calledFrom = debug_backtrace();
		$file = substr(str_replace(ROOT, '', $calledFrom[0]['file']), 1);
		$line = $calledFrom[0]['line'];
	}
	$html = <<<HTML
<strong>%s</strong> (line <strong>%s</strong>)
<pre class="cake-debug">
%s
</pre>
HTML;
	$text = <<<TEXT

%s (line %s)
########## DEBUG ##########
%s
###########################

TEXT;
	$template = $html;
	if (php_sapi_name() == 'cli') {
		$template = $text;
	}
	if ($showHtml === null && $template !== $text) {
		$showHtml = true;
	}
	$var = print_r($var, true);
	if ($showHtml && php_sapi_name() != 'cli') {
		$var = str_replace(array('<', '>'), array('&lt;', '&gt;'), $var);
	}
	printf($template, $file, $line, $var);
	if ($die) die;
}

// Output debug info as log in CLI
if (php_sapi_name() == 'cli') {
	Debugger::outputAs('log');
}

// Setup defaults for Resque
Configure::write('Resque', array(
	'Redis' => array('host' => 'localhost', 'port' => 6379),
	'environment_variables' => array('CAKE_ENV'),
	'default' => array(
		'queue' => 'default',
		'interval' => 5,
		'workers' => 1
	),
));

require_once APP . 'Plugin' . DS . 'Resque' . DS . 'Vendor' . DS . 'php-resque' . DS . 'lib' . DS . 'Resque.php';
Resque::setBackend(Configure::read('Resque.Redis.host') . ':' . Configure::read('Resque.Redis.port'));

config('environments');
CakePlugin::loadAll();