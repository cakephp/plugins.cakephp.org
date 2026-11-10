// Apply the saved theme before styles paint. Page visits reuse the Alpine store.
try {
    let theme = localStorage.getItem('theme')
    if (theme !== 'cakephp' && theme !== 'cakephp-dark') {
        theme = matchMedia('(prefers-color-scheme: dark)').matches ? 'cakephp-dark' : 'cakephp'
    }
    document.documentElement.dataset.theme = theme
} catch {
    document.documentElement.dataset.theme = 'cakephp'
}
