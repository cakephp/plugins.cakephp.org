<?php $icons = array(
    'be' => 'behavior',        'h' => 'helper',    'cp' => 'component',
    'm' => 'model',            'v' => 'view',      'c' => 'controller',
    'ds' => 'datasource',      't' => 'theme',     's' => 'shell',
); ?>
<?php if ($search) : ?>
    <div class="icons">
<?php
foreach ($icons as $class => $label) {
    if (isset($package) && $package[sha1('Package.contains_'.$label)]) {
        $htmlOptions = array(
            'class' => 'tooltip ' . $class,
            'label' => $label,
            'title' => 'Includes ' . $label,
        );
    } else {
        $htmlOptions = array('class' => 'hasno ' . $class, 'label' => $label);
    }

    echo $this->Html->link($class,
        array('plugin' => null, 'controller' => 'packages', 'action' => 'filter', 'by' => $label . 's'),
        $htmlOptions
    );
}
?>
</div>
    <?php if ($meta) : ?>
    <div class="meta-data">
        <span class="tooltip forks" title="Has <?php echo $package[sha1('Package.forks')]; ?> forks"><?php echo $package[sha1('Package.forks')]; ?></span>
        <span class="tooltip watchers" title="Has <?php echo $package[sha1('Package.watchers')]; ?> watchers"><?php echo $package[sha1('Package.watchers')]; ?></span>
        <span class="tooltip contributors" title="Has <?php echo $package[sha1('Package.contributors')]; ?> contributors"><?php echo $package[sha1('Package.contributors')]; ?></span>
    </div>
    <?php endif; ?>
<?php else : ?>
<div class="icons">
<?php
foreach ($icons as $class => $label) {
    if (isset($package) && $package['contains_'.$label]) {
        $htmlOptions = array(
            'class' => 'tooltip ' . $class,
            'label' => $label,
            'title' => 'Includes ' . $label,
        );
    } else {
        $htmlOptions = array('class' => 'hasno ' . $class, 'label' => $label);
    }

    echo $this->Html->link($class,
        array('plugin' => null, 'controller' => 'packages', 'action' => 'filter', 'by' => $label . 's'),
        $htmlOptions
    );
}
?>
</div>
    <?php if ($meta) : ?>
    <div class="meta-data">
        <span class="tooltip forks" title="Has <?php echo $package['forks']; ?> forks"><?php echo $package['forks']; ?></span>
        <span class="tooltip watchers" title="Has <?php echo $package['watchers']; ?> watchers"><?php echo $package['watchers']; ?></span>
        <span class="tooltip contributors" title="Has <?php echo $package['contributors']; ?> contributors"><?php echo $package['contributors']; ?></span>
    </div>
    <?php endif; ?>
<?php endif; ?>