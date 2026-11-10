<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var bool $mobile
 */
$dark = $this->getRequest()->getCookie('theme') === 'cakephp-dark';
?>
<button
    type="button"
    id="<?= $mobile ? 'theme-toggle-mobile' : 'theme-toggle' ?>"
    class="<?= $mobile ? 'flex items-center gap-2' : 'btn btn-ghost btn-circle hover:bg-white/10 text-white hidden lg:flex ml-2' ?>"
    aria-label="<?= __('Toggle theme') ?>"
    aria-pressed="<?= $dark ? 'true' : 'false' ?>"
    :aria-pressed="$store.theme.dark"
    x-data
    @click="$server.call('toggle')"
    :disabled="$server.pending()"
    @theme-changed.window="$store.theme.apply($event)"
>
    <svg
        style="<?= $dark ? 'display: none' : '' ?>"
        x-show="!$store.theme.dark"
        xmlns="http://www.w3.org/2000/svg"
        class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"
    >
        <path stroke-linecap="round" stroke-linejoin="round" d="
            M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0
            3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
    </svg>
    <svg
        style="<?= $dark ? '' : 'display: none' ?>"
        x-show="$store.theme.dark"
        xmlns="http://www.w3.org/2000/svg"
        class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"
    >
        <path stroke-linecap="round" stroke-linejoin="round" d="
            M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25
            12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
    </svg>
    <?php if ($mobile) : ?>
        <span x-text="$store.theme.dark ? '<?= __('Light mode') ?>' : '<?= __('Dark mode') ?>'"><?= $dark ? __('Light mode') : __('Dark mode') ?></span>
    <?php endif; ?>
</button>
