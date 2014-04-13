[![Build Status](https://travis-ci.org/josegonzalez/cakephp-sham.png?branch=master)](https://travis-ci.org/josegonzalez/cakephp-sham) [![Coverage Status](https://coveralls.io/repos/josegonzalez/cakephp-sham/badge.png?branch=master)](https://coveralls.io/r/josegonzalez/cakephp-sham?branch=master) [![Total Downloads](https://poser.pugx.org/josegonzalez/cakephp-sham/d/total.png)](https://packagist.org/packages/josegonzalez/cakephp-sham) [![Latest Stable Version](https://poser.pugx.org/josegonzalez/cakephp-sham/v/stable.png)](https://packagist.org/packages/josegonzalez/cakephp-sham)


## Requirements

* CakePHP 2.x

## Installation

_[Using [Composer](http://getcomposer.org/)]_

[View on Packagist](https://packagist.org/packages/josegonzalez/cakephp-sham), and copy the json snippet for the latest version into your project's `composer.json`. Eg, v. 1.0.0 would look like this:

```javascript
{
    "require": {
        "josegonzalez/cakephp-sham": "1.0.0"
    }
}
```

Because this plugin has the type `cakephp-plugin` set in it's own `composer.json`, composer knows to install it inside your `/Plugins` directory, rather than in the usual vendors file. It is recommended that you add `/Plugins/Sham` to your .gitignore file. (Why? [read this](http://getcomposer.org/doc/faqs/should-i-commit-the-dependencies-in-my-vendor-directory.md).)

_[Manual]_

* Download this: [https://github.com/josegonzalez/cakephp-sham/zipball/master](https://github.com/josegonzalez/cakephp-sham/zipball/master)
* Unzip that download.
* Copy the resulting folder to `app/Plugin`
* Rename the folder you just copied to `Sham`

_[GIT Submodule]_

In your app directory type:

    git submodule add git://github.com/josegonzalez/cakephp-sham.git Plugin/Sham
    git submodule init
    git submodule update

_[GIT Clone]_

In your plugin directory type

    git clone git://github.com/josegonzalez/cakephp-sham.git Sham

### Enable plugin

Before using, you MUST enable the plugin:

    CakePlugin::load('Sham');

If you are already using `CakePlugin::loadAll();` before usage, then this is not necessary.

## Usage

Add the component and helper to your AppController `$components` and `$helpers` arrays:

```php
<?php
class AppController extends Controller {
    public $components = array('Sham.Sham');
    public $helpers = array('Sham.Sham');
}
?>
```

By default, Sham does not automatically load SEO data from the database. You should create callbacks in your controllers to do that. Callbacks are on a per-action basis, with `_seo` prepending the name of the action, where the first letter of the action is upper-cased:

```php
<?php
class UsersController extends AppController {

    public function profile($username = null) {
        // Some code that works with user profiles
    }

    public function _seoProfile() {
        // Called in the beforeRender() if the action was successfully processed
        $user = $this->viewVars['user'];
        $this->Sham->loadBySlug('view/' . $user['User']['username']);

        // Set some defaults in case the record could not be loaded from the DB
        $description = "awesome description of the page, with some good default keywords, referencing {$user['User']['username']}";
        $keywords = array($user['User']['username'] . ' profile', 'profiles', 'social network');

        $this->Sham->setMeta('title', "{$user['User']['username']}'s Profile  | Social Network");
        $this->Sham->setMeta('description', $description);
        $this->Sham->setMeta('keywords', implode(', ', $keywords));
        $this->Sham->setMeta('canonical', "/view/{$user['User']['username']}/", array('escape' => false));
    }
}
?>
```

If you do not have a callback for a given action, there is always the option of specifying a "fallback" method. This is configurable in the components settings, but is `Controller::_seoFallback()` by default:

```php
<?php
class AppController extends Controller {
    public $components = array('Sham.Sham');
    public $helpers = array('Sham.Sham');

    public function _seoFallback() {
        // ... code ...
    }
}
?>
```

Once you've loaded seo data, it's time to set it for the view. Included is a `ShamHelper` which automatically will deal with these details:

```php
<!DOCTYPE html>
<html lang="en">
    <head>
        <?php echo $this->Sham->out('charset'); ?>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <?php echo $this->Sham->out(null, array('skip' => array('charset'))); ?>
        <?php echo $this->Html->css(array('style', 'uniform.default')); ?>
    <head>
    <body>
        ...
    </body>
</html>
```

As you can see, we can call individual SEO information - in this case the charset - if necessary, and then call the rest by passing `null` as an option to the helper. This is useful in some cases where you might need to have the SEO data in a specific order.

## Todo

* Document Helper and Component options

## License

Copyright (c) 2011 Jose Diaz-Gonzalez

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
