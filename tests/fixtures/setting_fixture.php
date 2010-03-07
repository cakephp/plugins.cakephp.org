<?php
/* Setting Fixture generated on: 2010-03-07 01:03:53 : 1267926773 */
class SettingFixture extends CakeTestFixture {
	var $name = 'Setting';

	var $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'key' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 64, 'key' => 'unique'),
		'value' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'title' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'description' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'input_type' => array('type' => 'string', 'null' => false, 'default' => 'text'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'key' => array('column' => 'key', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	var $records = array(
		array(
			'id' => 1,
			'key' => 'RegistrationNotificationEmail',
			'value' => 'support@savant.be',
			'title' => 'Registration Notification Email',
			'description' => 'This is the email to which notification of new users is sent',
			'input_type' => 'text'
		),
		array(
			'id' => 2,
			'key' => 'ServerEmail',
			'value' => 'mail@example.com',
			'title' => 'Server Email',
			'description' => 'This is the email from which application emails are sent from',
			'input_type' => 'text'
		),
		array(
			'id' => 3,
			'key' => 'SmtpUsername',
			'value' => 'dreamsavant@gmail.com',
			'title' => 'SMTP Username',
			'description' => 'This is the email from which registration emails are sent',
			'input_type' => 'text'
		),
		array(
			'id' => 4,
			'key' => 'SmtpPassword',
			'value' => 'mission13111',
			'title' => 'SMTP Password',
			'description' => 'This is the password for the SMTP Username email',
			'input_type' => 'text'
		),
	);
}
?>