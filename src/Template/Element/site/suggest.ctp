<h3 class="title-white">Missing a package from Github?</h3>

<h4 class="title-white mb30">Let us know about it!</h4>

<?php
    echo $this->Form->create($suggestForm, [
        'class' => 'form',
        'role' => 'form',
        'url' => ['controller' => 'packages', 'action' => 'suggest'],
    ]);
?>
<div class="form-group">
    <?php
        echo $this->Form->input('github', [
            'label' => false,
            'div' => false,
            'placeholder' => __('github repository url'),
        ]);
    ?>
</div>

<?php
    echo $this->Form->button(__('Suggest!'), [
        'class' => 'btn btn-white',
        'div' => false,
    ]);

    echo $this->Form->end();
?>
