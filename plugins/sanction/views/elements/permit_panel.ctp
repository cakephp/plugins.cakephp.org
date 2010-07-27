<h2> <?php __d('sanction', 'Permit Component'); ?></h2>
<?php
echo '<h4>' . __d('sanction', 'Executed Rule', true) . '</h4>';
echo $toolbar->makeNeatArray(array('rule' => $content['executed']));

echo '<h4>' . __d('sanction', 'Access Rules', true) . '</h4>';
echo $toolbar->makeNeatArray($content['clearances']);
?>