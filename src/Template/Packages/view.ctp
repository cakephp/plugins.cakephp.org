<?php
$this->Html->addCrumb('packages', ['action' => 'index']);
$this->Html->addCrumb($package->maintainer->username, $package->maintainer->route());
$this->Html->addCrumb($package->name, $package->route());
?>

<div class="container">
    <div class="row">
        <?php echo $this->Html->getCrumbList(['class' => 'breadcrumbs']); ?>
        <h3 class="package-header">
            <?php echo $this->Text->truncate($this->Text->autoLink($package->description), 100, ['html' => true]) ?>
        </h3>

        <table class="table">
            <tbody>
                <tr>
                    <td>Repo Url</td>
                    <td><?php echo $this->Resource->githubUrl($package->maintainer->username, $package->name) ?></td>
                </tr>
                <tr>
                    <td>Clone Url:</td>
                    <td>
                        <form class="clone-url-form" role="form">
                            <?php echo $this->Resource->cloneUrl($package->maintainer->username, $package->name) ?>
                        </form>
                    </td>
                </tr>
                <?php if ($this->request->session()->read('Auth.User')) : ?>
                    <tr>
                        <td>Disable:</td>
                        <td>
                            <?php echo $this->Html->link('Disable', $package->disableRoute()) ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td>Watchers</td>
                    <td><?php echo $package->watchers ?></td>
                </tr>
                <tr>
                    <td>Issues</td>
                    <td><?php echo $package->open_issues ?></td>
                </tr>
                <tr>
                    <td>Forks</td>
                    <td><?php echo $package->forks ?></td>
                </tr>
                <tr>
                    <td>Last Pushed At</td>
                    <td><?php echo $package->last_pushed_at->format('Y-m-d') ?></td>
                </tr>

                <?php if (!empty($package->category->name)) : ?>
                    <tr>
                        <td>Category</td>
                        <td>
                            <?php echo $this->element('site/category', ['category' => $package->category]); ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if (!empty($package->tags)) : ?>
                    <tr>
                        <td>Tags</td>
                        <td>
                            <?php echo $this->element('site/tags', ['tags' => $package]); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="package-last-fetched">Last fetched: <?php echo $this->Time->timeAgoInWords($package->modified) ?></div>

        <?php $rss = $package->rss() ?>
        <?php if (!empty($rss)) : ?>
            <h4><?php echo __('Recent Activity') ?></h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Commit Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rss as $entry) : ?>
                        <tr>
                            <td><?php echo date('Y-m-d', $entry['created_at']); ?></td>
                            <td>
                                <?php echo $this->Html->link($entry['message'], $entry['url'], ['target' => '_blank', 'rel' => 'nofollow', 'escape' => false]); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- <div id="disqus_thread"></div> -->

        <!-- <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript> -->
        <!-- <a href="http://disqus.com" class="dsq-brlink">blog comments powered by <span class="logo-disqus">Disqus</span></a> -->

        <!-- <script>
        var disqus_developer = 1;
        <?php foreach ($package->disqus() as $k => $v) : ?>
            var <?php echo $k ?> = <?php echo json_encode($v) ?>;
        <?php endforeach; ?>

        (function() {
            var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
            dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
        })();
        </script>
         -->
    </div>
</div>
