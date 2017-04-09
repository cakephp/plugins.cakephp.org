<?php
namespace App\Job;

use Cake\Core\Configure;
use Cake\Log\LogTrait;
use Cake\Mailer\Email;
use Exception;
use josegonzalez\Queuesadilla\Job\Base;

class DeferredEmail
{
    use LogTrait;

    /**
     * Whether email was sent or not
     *
     * @var boolean
     */
    protected $sent = false;

    /**
     * True if email was cancelled, false otherwise
     *
     * @var boolean
     */
    protected $canceled = false;

    /**
     * Test mode for emails
     *
     * @var boolean
     */
    protected $test = false;

    /**
     * Variables for the view
     *
     * @var array
     */
    public $viewVars = [];


    /**
     * Email build step
     *
     * @return void
     */
    public function build()
    {
        if (!defined('FULL_BASE_URL')) {
            define('FULL_BASE_URL', Configure::read('App.fullBaseUrl'));
        }
    }

    /**
     * Allows an email to be canceled in the build step
     *
     * @return void
     */
    public function cancel()
    {
        $this->canceled = true;
    }

    /**
     * Allow emails to be sent in a delayed fashion via
     * CakeDjjob
     *
     * Will set the view variables for the current job
     *
     * @return void
     */
    public function perform(Base $job)
    {
        $this->set($job->data());
        return $this->send();
    }

    /**
     * The name of an email profile or an array of initial settings
     *
     * @return string|array
     */
    public function profile()
    {
        return 'default';
    }

    /**
     * An Email instance
     *
     * @return \Cake\Mailer\Email
     */
    public function emailClass()
    {
        return new Email;
    }

    /**
     * Send step of email
     *
     * @return void
     */
    public function send()
    {
        if ($this->sent) {
            throw new Exception("This " . get_class($this) . " was already sent");
        }

        $this->email = $this->emailClass();
        $this->email->profile($this->profile());
        $this->email->transport($this->transport());

        $this->build(); // perform expensive work as late as possible

        if ($this->canceled) {
            return false;
        }

        if ($this->test) {
            $this->email->to($this->testEmailAddress());
        }

        try {
            if (!empty($this->viewVars)) {
                $this->email->viewVars($this->viewVars);
            }
            $this->sent = $this->email->send();
        } catch (Exception $e) {
            $this->sent = false;
            $this->log($e->getMessage());
            // TODO: trigger error handler
        }

        return $this->sent;
    }

    /**
     * Returns the default test email address
     *
     * @return string
     */
    public function testEmailAddress()
    {
        return 'mail@example.com';
    }

    /**
     * The name of an email transport
     *
     * @return string
     */
    public function transport()
    {
        return 'default';
    }

    /**
     * Saves a variable or an associative array of variables for use inside a template.
     *
     * @param string|array $name A string or an array of data.
     * @param string|array|null|bool $value Value in case $name is a string (which then works as the key).
     *   Unused if $name is an associative array, otherwise serves as the values to $name's keys.
     * @return $this
     */
    public function set($name, $value = null)
    {
        if (is_array($name)) {
            if (is_array($value)) {
                $data = array_combine($name, $value);
            } else {
                $data = $name;
            }
        } else {
            $data = [$name => $value];
        }
        $this->viewVars = $data + $this->viewVars;

        return $this;
    }
}
