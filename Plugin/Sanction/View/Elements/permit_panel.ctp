<h2> <?php echo __d('sanction', 'Permit Component'); ?></h2>
<?php
  echo '<h4>' . __d('sanction', 'Executed Rule') . '</h4>';
  echo $this->Toolbar->makeNeatArray(array('rule' => $content['executed']));

  echo '<h4>' . __d('sanction', 'Access Rules') . '</h4>';
  echo $this->Toolbar->makeNeatArray($content['routes']);

  echo '<h4>' . __d('sanction', 'Identified User') . '</h4>';
  echo $this->Toolbar->makeNeatArray($content['user']);
?>