<div class="package-list">
    <?php foreach ($packages as $package) : ?>
    <div class="package-snippet">
        <h4>
            <?php
            echo $this->Resource->packageLink(
                sprintf('%s / %s', $package->maintainer->username, $package->name),
                $package->id,
                $package->name
            );
            ?>
        </h4>
        <?php if (empty($disableAdmin)) : ?>
            <p><?php echo $this->element('site/version-picker', ['package' => $package]); ?></p>
        <?php endif; ?>
        <p class="package-tags">
            <?php echo $this->element('site/category', ['category' => $package->category]); ?>
            <?php echo $this->Resource->tagCloud($package->tags); ?>
        </p>
        <p class="package-description"><?php echo $package->description ?></p>
    </div>
    <?php endforeach; ?>
</div>
