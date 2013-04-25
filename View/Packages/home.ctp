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
