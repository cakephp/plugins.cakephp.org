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

Cache::config('default', array(
	'engine' => $engine,
	'prefix' => 'DEFAULT_',
	'path' => CACHE . 'data' . DS,
	'serialize' => ($engine === 'File'),
	'duration' => $duration,
));

config('environments');

CakePlugin::loadAll();