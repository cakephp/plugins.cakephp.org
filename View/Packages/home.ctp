<!-- <section class="highlight clearfix">
  <span class="tag">featured</span>
  <p class="description">
    <span class="title">debug_kit:</span>&nbsp;
    The official method of debugging your applications
  </p>
  <p class="button">
    <a href="/p/52-debug_kit" rel="nofollow">Get it!</a>
  </p>
</section>
 -->
<section class="contribute">
  <div class="bubble-top bubble-border">
    <h2 class="header">Use and Share Open Source CakePHP Code</h2>
  </div>
  <div class="bubble-bottom clearfix">
    <article>
      Signup
    </article>
    <article>
      Create a Package
    </article>
    <article>
      Install Plugins
    </article>
    <article>
      Follow Repositories
    </article>
  </div>
</section>

<section class="search">
  <h2>Search For Packages</h2>
  <div>
    <?php echo $this->Form->create(false, array('action' => 'index'));?>
      <?php
        echo $this->Form->input('query', array(
          'class' => 'query',
          'div' => false,
          'label' => false,
          'placeholder' => __('search (ex. debug watchers:5 forks:8 has:component)')
        ));
      ?>
      <?php
        echo $this->Form->button(__('Search'), array(
          'class' => 'button big icon search',
          'div' => false,
        ));
      ?>
    <?php echo $this->Form->end();?>
  </div>
</section>

<!-- <section class="lists">
  <article class="popular-packages">
    <h3 class="section-heading">Popular Packages</h3>
    <ul class="popular-list">
      <li><?php echo $this->Resource->package('CakePHP', 'debug_kit'); ?></li>
      <li><?php echo $this->Resource->package('lorenzo', 'MongoCake'); ?></li>
      <li><?php echo $this->Resource->package('markstory', 'asset_compress'); ?></li>
      <li><?php echo $this->Resource->package('dkullmann', 'CakePHP-Elastic-Search-DataSource'); ?></li>
      <li><?php echo $this->Resource->package('josegonzalez', 'cakephp-upload'); ?></li>
      <li><?php echo $this->Resource->package('CakeDC', 'migrations'); ?></li>
      <li><?php echo $this->Resource->package('FriendsOfCake', 'crud'); ?></li>
      <li><?php echo $this->Resource->package('slywalker', 'cakephp-plugin-boost_cake'); ?></li>
      <li><?php echo $this->Resource->package('FriendsOfCake', 'app-template'); ?></li>
    </ul>
  </article>
</section>
 -->

<table class="table table-hover table-condensed">
  <thead>
    <tr>
      <th>Package</th>
      <th>Watchers</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($packages as $package) : ?>
      <tr>
        <td>
          <a href="/p/<?php echo $package['Package']['id']; ?>-<?php echo $package['Package']['name']; ?>" class="package-name">
            <?php echo $package['Maintainer']['username']; ?> / <?php echo $package['Package']['name']; ?>
          </a>
          <p class="description">
            <?php if (!empty($package['Category']['slug'])) : ?>
              <a href="/packages?category=<?php echo $package['Category']['slug']; ?>" class="label category-label" style="background-color:<?php echo $package['Category']['color']; ?>">
                <?php echo $package['Category']['name']; ?>
              </a>&nbsp;
            <?php endif; ?>
            <?php echo $this->Text->truncate($package['Package']['description']) ?>
          </p>
        </td>
        <td class="watchers"><?php echo $package['Package']['watchers']; ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

