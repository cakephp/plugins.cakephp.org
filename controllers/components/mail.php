<?php
class MailComponent extends Object {
	var $components = array('Email', 'SwiftMailer');

	var $controller = null;

	var $mailer = 'php';

	function initialize(&$controller, $settings = array()) {
		$this->controller =& $controller;
	}

	function send($params = array()) {
		if (!isset($params['to']) || !isset($params['subject'])) return false;

		if (!isset($params['mailer'])) $params['mailer'] = $this->mailer;

		if (!isset($params['element'])) $params['element'] = $this->controller->action;

		if ($params['mailer'] == 'php') {
			$headers  = "MIME-Version: 1.0\r\nContent-type: text/html; charset=iso-8859-1\r\n";
			$headers .= 'From: ' . Configure::read('Settings.SiteTitle') . ' ' . Configure::read('Settings.ServerEmail') . "\r\n";
			$viewClass = $this->controller->view;

			if ($viewClass != 'View') {
				list($plugin, $viewClass) = pluginSplit($viewClass);
				$viewClass = $viewClass . 'View';
				App::import('View', $this->controller->view);
			}

			$View = new $viewClass($this->controller, false);
			$View->layout = $params['element'];
			$View->set($params['variables']);
			$content = $View->element('email' . DS . 'html' . DS . $params['element'], array(), true);
			$View->layoutPath = 'email' . DS . 'html';
			$content = explode("\n", $rendered = str_replace(array("\r\n", "\r"), "\n", $View->renderLayout($content)));

			return mail($params['to'], $params['subject'], $rendered, $headers);
		}
		if ($params['mailer'] == 'swift') {
			$this->SwiftMailer->smtpType = 'tls';
			$this->SwiftMailer->smtpHost = 'smtp.gmail.com';
			$this->SwiftMailer->smtpPort = 465;
			$this->SwiftMailer->smtpTimeout = '30';
			$this->SwiftMailer->smtpUsername = Configure::read('Settings.SmtpUsername');
			$this->SwiftMailer->smtpPassword = Configure::read('Settings.SmtpPassword');
			$this->SwiftMailer->from = Configure::read('Settings.ServerEmail');
			$this->SwiftMailer->fromName = Configure::read('Settings.SiteTitle');
			$this->SwiftMailer->replyTo = Configure::read('Settings.ServerEmail');
			$this->SwiftMailer->sendAs = 'html';
			$this->SwiftMailer->to = $params['to'];
			$this->SwiftMailer->subject = $params['subject'];
			$this->controller->set($params['variables']);
			return $this->SwiftMailer->send($params['element'], $params['subject']);
		}
		if ($params['mailer'] == 'email') {
			$this->Email->reset();
			$this->Email->delivery = 'smtp';
			$this->Email->smtpOptions = array(
				'host' => 'smtp.gmail.com',
				'port' => 465,
				'timeout' => '30',
				'username' => Configure::read('Settings.SmtpUsername'),
				'password' => Configure::read('Settings.SmtpPassword')
			);
			$this->Email->from = Configure::read('Settings.ServerEmail');
			$this->Email->return = Configure::read('Settings.ServerEmail');
			$this->Email->replyTo = Configure::read('Settings.ServerEmail');
			$this->Email->sendAs = 'both';
			$this->Email->layout = $params['element'];
			$this->Email->to = $params['to'];
			$this->Email->subject = $params['subject'];
			$this->controller->set($params['variables']);
			return $this->Email->send();
		}

		return false;
	}
}
?>