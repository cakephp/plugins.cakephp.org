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
        <p class="package-tags">
            <?php echo $this->element('site/version-picker', ['package' => $package]); ?>
            <?php echo $this->element('site/category', ['category' => $package->category]); ?>

            <?php $tags = explode(',', $package->tags); ?>
            <?php if (in_array('version:3', $tags)) : ?>
                <span class="label category-label" style="background-color:#27a4dd">3.x</span>
            <?php endif; ?>
            <?php if (in_array('version:2', $tags)) : ?>
                <span class="label category-label" style="background-color:#9dd5c0">2.x</span>
            <?php endif; ?>
            <?php if (in_array('version:1.3', $tags)) : ?>
                <span class="label category-label" style="background-color:#ffaaa5">1.3</span>
            <?php endif; ?>
            <?php if (in_array('version:1.2', $tags)) : ?>
                <span class="label category-label" style="background-color:#ffd3b6">1.2</span>
            <?php endif; ?>
        </p>
        <p class="package-description"><?php echo $package->description ?></p>
    </div>
    <?php endforeach; ?>
</div>
