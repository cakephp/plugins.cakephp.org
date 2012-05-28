<?php
App::uses('AppShell', 'Console/Command');
App::uses('Router', 'Routing');
App::uses('CakeEmail', 'Network/Email');

class DeferredEmail extends AppShell {

/**
 * Whether email was sent or not
 *
 * @var boolean
 */
	protected $_sent = false;

/**
 * True if email was cancelled, false otherwise
 *
 * @var boolean
 */
	protected $_canceled = false;

/**
 * Email address of recipient
 *
 * @var string
 */
	protected $_email = null;

/**
 * Variables to be set for emailing
 *
 * @var array
 */
	protected $_vars = null;

/**
 * Original Variables set for the call
 *
 * @var array
 */
	protected $_originalVars = null;

/**
 * Test mode for emails
 *
 * @var boolean
 */
	protected $_test = false;

/**
 * Template for the view
 *
 * @var string
 */
	protected $_template = 'default';

/**
 * Message for the email
 *
 * @var string
 */
	protected $_message = null;

	public function build() {
		if (!defined('FULL_BASE_URL')) {
			define('FULL_BASE_URL', Configure::read('Settings.FULL_BASE_URL'));
		}

		$this->updateVars(array(
			'transport' => 'Smtp',
			'host' => 'ssl://smtp.gmail.com',
			'port' => 465,
			'timeout' => '30',
			'username' => Configure::read('Email.username'),
			'password' => Configure::read('Email.password')
		));
	}

	public function cancel() {
		$this->_canceled = true;
	}

/**
 * Send step of email
 *
 * @return void
 */
	public function send() {
		if ($this->_sent) {
			throw new Exception("This " . get_class($this) . " was already sent");
		}

		$this->build(); // perform expensive work as late as possible

		if ($this->_canceled) return false;

		// Convert booleans to ints, otherwise the signature will be incorrect
		foreach ($this->_vars as &$var)
			if (is_bool($var)) $var = ($var) ? 1 : 0;

		$this->_vars['to'] = $this->_email;
		if ($this->_test) {
			$this->_vars['to'] = Configure::read('Email.test');
			if (!$this->_vars['to']) {
				$this->_vars['to'] = 'mail@example.com';
			}
		}

		if (!isset($this->_vars['from'])) {
			$this->_vars['from'] = Configure::read('Email.from');
		}
		if (!isset($this->_vars['replyTo'])) {
			$this->_vars['replyTo'] = $this->_vars['from'];
		}
		if (!isset($this->_vars['return'])) {
			$this->_vars['return'] = $this->_vars['from'];
		}

		if (!isset($this->_vars['template'])) {
			$this->_vars['template'] = $this->_template;
		}

		if (!isset($this->_vars['sendAs'])) {
			$this->_vars['sendAs'] = 'both';
		}

		$email = new CakeEmail();
		try {
			$email->config($this->_vars);
			if (isset($this->_vars['variables'])) {
				$email->viewVars($this->_vars['variables']);
			}
			$this->_sent = $email->send($this->_message);
		} catch (Exception $e) {
			$this->_sent = false;
			$this->out($e->getMessage(), 'email');
			$this->sendLater();
		}

		return $this->_sent;
	}

/**
 * Enables requeing of an email
 *
 * @param datetime $send_at MySQL-compatible datetime
 * @param string $queue Name of queue
 * @return void
 */
	public function sendLater($queue = "email") {
		Resque::enqueue($this->job->queue, get_called_class(), $this->_originalVars);
	}

/**
 * Gets the current variables
 *
 * @return array
 */
	public function getVars() {
		if ($this->_vars === null) {
			$this->_vars = $this->args[0];
			$this->_originalVars = array($this->args[0]);
			array_unshift($this->_originalVars, 'work');
		}
		return $this->_vars;
	}

/**
 * Handles merging of current email variables, as well as setting
 * public properties for later ease of usage
 *
 * @param array $vars
 * @return void
 */
	protected function updateVars($vars) {
		$this->_vars = array_merge($this->_vars, $vars);

		if (isset($this->_vars['variables'])) {
			foreach ($this->_vars['variables'] as $name => $value) {
				$this->$name = $value;
			}
		}
	}

/**
 * Allow emails to be sent in a delayed fashion via
 * CakeDjjob
 *
 * @return void
 */
	public function work() {
		$this->send();
	}

}