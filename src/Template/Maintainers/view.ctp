<div class="col-lg-12 user-details">
    <div class="user-image">
        <?php echo $this->Resource->gravatar($maintainer->username, $maintainer->avatar_url, $maintainer->gravatar_id); ?>
    </div>
    <div class="user-info-block">
        <div class="user-heading">
            <h3 class="user-username">@<?php echo $maintainer->username; ?></h3>
            <h4 class="user-name"><?php echo $maintainer->name ? '&nbsp;(' . $maintainer->name . ')': ''; ?></h4>
        </div>
    </div>
</div>

<?php echo $this->element('site/package-results', ['packages' => $packages->toArray()]); ?>
