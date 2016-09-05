<?php echo $this->Form->create(false, array('url' => ['action' => 'index'], 'class' => 'form search-bar', 'role' => 'form'));?>
    <div class="form-group has-feedback">
        <?php
            echo $this->Form->input('query', array(
                'before' => '<label class="control-label sr-only" for="query">Hidden label</label>',
                'after' => '<span class="glyphicon glyphicon-search form-control-feedback"></span>',
                'class' => 'form-control',
                'div' => false,
                'label' => false,
                'placeholder' => __('search (ex. debug watchers:5 forks:8 has:component)')
            ));
        ?>
    </div>
    <?php
        echo $this->Form->button(__('Search'), array(
            'class' => 'btn btn-default',
            'div' => false,
        ));
    ?>
<?php echo $this->Form->end();?>
