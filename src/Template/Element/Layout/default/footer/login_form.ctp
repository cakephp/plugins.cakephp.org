<div>
    <div style="display: none">
        <?= $this->Form->create(null, ['class' => 'form']) ?>
            <h4><?= __('My CakePHP') ?></h4>
            <div class="col-md-12 p0 form-user">
                <label class="mb10">
                    <?= __('Username') ?>
                    <input type="text" name="val_fname" id="val_fname" class="form-control">
                </label>
                <label class="mb10">
                    <?= __('Password') ?>
                    <input type="password" name="val_lname" id="val_lname" class="form-control">
                </label>
            </div>

            <div class="col-md-4  col-xs-6 p0 pull-right">
                <?= $this->Form->button(__('Login'), ['class' => 'btn-user']) ?>
            </div>

            <div class="col-md-8 col-xs-6 p0 register">
                <label><input type="checkbox"><?= __('Remember me') ?></label>
                <p>
                    <?= __('Forgot your password?') ?><br>
                    <?= __('New user?') ?> <?= $this->Html->link(__('Register!'), '#') ?>
                </p>
                <?= $this->Html->link(
                    $this->Html->image('open-hub.png'),
                    'https://www.openhub.net/p/cakephp',
                    ['escape' => false]
                ) ?>
                <div class="mt10">
                    <?= $this->Html->link(
                        $this->Html->image('rackspace.png'),
                        'https://www.rackspace.com/',
                        ['escape' => false]
                    ) ?>
                </div>
            </div>
        <?= $this->Form->end() ?>
    </div>
    <div class="col-md-8 col-xs-6 p0 register">
        <?= $this->Html->link(
            $this->Html->image('open-hub.png'),
            'https://www.openhub.net/p/cakephp',
            ['escape' => false, 'target' => '_blank']
        ) ?>
        <div class="mt10">
            <?= $this->Html->link(
                $this->Html->image('pingping.png'),
                'https://pingping.io',
                ['escape' => false, 'target' => '_blank']
            ) ?>
        </div>
        <div class="mt10">
            <?= $this->Html->link(
                $this->Html->image('linode.png'),
                'https://www.linode.com/',
                ['escape' => false, 'target' => '_blank']
            ) ?>
        </div>
    </div>
</div>
