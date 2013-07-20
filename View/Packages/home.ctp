<section class="highlight clearfix">
  <span class="tag">featured</span>
  <p class="description">
    <span class="title">debug_kit:</span>&nbsp;
    The official method of debugging your applications
  </p>
  <p class="button">
    <a href="/p/52-debug_kit" rel="nofollow">Get it!</a>
  </p>
</section>

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

<section class="lists">
  <article class="popular-packages">
    <h3 class="section-heading">Popular Packages</h3>
    <ul class="popular-list">
      <li><?php echo $this->Resource->package('CakePHP', 'debug_kit'); ?></li>
      <li><?php echo $this->Resource->package('lorenzo', 'MongoCake'); ?></li>
      <li><?php echo $this->Resource->package('markstory', 'asset_compress'); ?></li>
      <li><?php echo $this->Resource->package('dkullmann', 'CakePHP-Elastic-Search-DataSource'); ?></li>
      <li><?php echo $this->Resource->package('josegonzalez', 'upload'); ?></li>
      <li><?php echo $this->Resource->package('CakeDC', 'migrations'); ?></li>
      <li><?php echo $this->Resource->package('jippi', 'cakephp-crud'); ?></li>
    </ul>
  </article>

  <article class="popular-maintainers">
    <h3 class="section-heading">Popular Maintainers</h3>
    <ul class="popular-list">
      <li><?php echo $this->Resource->maintainer('CakePHP'); ?></li>
      <li><?php echo $this->Resource->maintainer('CakeDC'); ?></li>
      <li><?php echo $this->Resource->maintainer('lorenzo'); ?></li>
      <li><?php echo $this->Resource->maintainer('markstory'); ?></li>
      <li><?php echo $this->Resource->maintainer('josegonzalez'); ?></li>
    </ul>
  </article>
</section>
