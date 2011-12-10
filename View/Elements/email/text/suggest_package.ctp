Hey Admin!

Someone suggested you add a package to CakePackages:

Click here to see the package on github: <?php echo "https://github.com/{$username}/{$repository}"; ?>

Click here to approve or deny: <?php echo Router::url(array(
		'full_base' => true,
		'admin' => false,
		'controller' => 'github',
		'action' => 'add_package',
		$username,
		$repository),
	true);
?>

Thanks,

CakePackages Plugin Indexer