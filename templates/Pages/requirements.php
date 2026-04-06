<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Listing Requirements');
?>
<section class="px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl space-y-8">
        <header class="space-y-3">
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cake-red">Plugin Directory</p>
            <h1 class="text-4xl font-semibold text-base-content">Minimum requirements for being listed</h1>
            <p class="max-w-3xl text-base leading-7 text-base-content/70">
                Packages are only listed when they are actual CakePHP plugins with an explicit Composer dependency on the CakePHP framework package or one of the official split packages.
            </p>
        </header>

        <div class="grid gap-6 md:grid-cols-2">
            <article class="rounded-3xl border border-base-300 bg-base-100 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-base-content">Required</h2>
                <ul class="mt-4 space-y-3 text-sm leading-6 text-base-content/75">
                    <li>
                        The package must be published on Packagist as a
                        <?= $this->element('code_chip', ['text' => 'cakephp-plugin', 'variant' => 'accent']) ?>.
                    </li>
                    <li>The package must not be abandoned.</li>
                    <li>The package must have at least 10 downloads on packagist.org.</li>
                    <li>
                        Its
                        <?= $this->element('code_chip', ['text' => 'require']) ?>
                        section must explicitly contain either
                        <?= $this->element('code_chip', ['text' => 'cakephp/cakephp', 'variant' => 'accent']) ?>
                        or one of the official CakePHP split packages.
                    </li>
                </ul>
            </article>

            <article class="rounded-3xl border border-base-300 bg-base-100 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-base-content">Not enough on its own</h2>
                <ul class="mt-4 space-y-3 text-sm leading-6 text-base-content/75">
                    <li>Depending only on PSR interfaces or generic libraries does not qualify a package for listing.</li>
                    <li>Suggesting CakePHP support in the README without a Composer dependency does not qualify.</li>
                    <li>
                        Only
                        <?= $this->element('code_chip', ['text' => 'require']) ?>
                        dependencies are considered for framework compatibility tags.
                    </li>
                </ul>
            </article>
        </div>

        <article class="rounded-3xl border border-cake-red/20 bg-cake-red/5 p-6">
            <h2 class="text-xl font-semibold text-base-content">Accepted Composer examples</h2>
            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                <div>
                    <p class="mb-2 text-sm font-medium text-base-content/70">Framework package</p>
                    <pre class="overflow-x-auto rounded-2xl bg-base-200 p-4 text-sm"><code>{
  "name": "myusername/my-package-name",
  "type": "cakephp-plugin",
  "require": {
    "cakephp/cakephp": "^5.0"
  }
}</code></pre>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-base-content/70">Split package</p>
                    <pre class="overflow-x-auto rounded-2xl bg-base-200 p-4 text-sm"><code>{
  "name": "myusername/my-package-name",
  "type": "cakephp-plugin",
  "require": {
    "cakephp/orm": "^5.0"
  }
}</code></pre>
                </div>
            </div>
        </article>
    </div>
</section>
