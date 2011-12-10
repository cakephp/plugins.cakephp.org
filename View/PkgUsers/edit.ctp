<h2><?php __d('spactare', 'Your Account Settings'); ?></h2>
<?php
	echo $this->Form->create($model, array());
?>
<fieldset>
	<legend><?php __d('spactare', 'Account information'); ?></legend>
	<p>
		<?php echo $this->Html->link(__d('spactare', 'Change your password'), array('action' => 'change_password')); ?>
	</p>
	<?php
		echo $this->Form->input('email', array(
			'label' => __d('spactare', 'Email')));
	?>
</fieldset>
<fieldset>
	<legend><?php __d('spactare', 'Personal information'); ?></legend>
	<?php
		echo $this->Form->input('Detail.firstname', array(
			'label' => __d('spactare', 'First Name')));
		echo $this->Form->input('Detail.middlename', array(
			'label' => __d('spactare', 'Middle Name')));
		echo $this->Form->input('Detail.lastname', array(
			'label' => __d('spactare', 'Last Name')));
		echo $this->Form->input('Detail.birthday', array(
			'label' => __d('spactare', 'Birthday'),
			'type' => 'date',
			'empty' => true,
			'minYear' => date('Y') - 100,
			'maxYear' => date('Y')));
		echo $this->element('users/country_list', array(
			'fieldName' => 'Detail.country'));
		echo $this->Form->input('Detail.language', array(
			'label' => __d('spactare', 'Language'),
			'type' => 'select',
			'empty' => true,
			'options' => $languages));
		echo $this->Form->input('Detail.biography', array(
			'label' => __d('spactare', 'Biography'),
			'type' => 'textarea'));
	?>
</fieldset>
<fieldset>
	<legend><?php __d('spactare', 'Your Gravatar'); ?></legend>
		<p>
		<?php
			$gravatarEmail = (empty($this->data['Detail']['gravatar_email'])) ? $this->data[$model]['email'] : $this->data['Detail']['gravatar_email'];
			echo $gravatar->image($gravatarEmail);
		?>
		</p>
	<?php if (empty($this->data['Detail']['gravatar_email'])) : ?>
		<p class="error-message">
			<?php printf(__d('spactare', 'If you want to use an email address different from "%s", please fill the input below'), $gravatarEmail); ?>
		</p>
	<?php endif; ?>

	<p>
		<?php
			echo __d('spactare', 'A Gravatar is a global avatar.') . ' ';
			echo $this->Html->link(__d('spactare', 'Click here for more info.'), 'http://www.gravatar.com/');
		?>
	</p>
	<?php echo $this->Form->input('Detail.gravatar_email', array(
			'label' => __d('spactare', 'Your gravatar email address'),
			'default' => $this->data[$model]['email']));?>
</fieldset>
<?php
	echo $this->Form->end(__d('spactare', 'Submit'));
?>