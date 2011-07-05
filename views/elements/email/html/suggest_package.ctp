<p>Hey Admin!</p>

<p>Someone suggested you add a package to CakePackages:</p>

<p>Click <?php echo $this->Html->link("https://github.com/{$username}/{$repository}", "https://github.com/{$username}/{$repository}"); ?> to see the package on github.</p>

<p>Click <?php echo $this->Html->link('here', array('full_base' => true, 'admin' => false, 'controller' => 'github', 'action' => 'add_package', $username, $repository)); ?> to approve.</p>

<p>Thanks,</p>

<p>CakePackages Plugin Indexer</p>