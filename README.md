ViewServiceProvider
===================
[![codecov](https://codecov.io/gh/speedwork/view/branch/master/graph/badge.svg)](https://codecov.io/gh/speedwork/view)
[![StyleCI](https://styleci.io/repos/37059354/shield)](https://styleci.io/repos/37059354)
[![Latest Stable Version](https://poser.pugx.org/speedwork/view/v/stable)](https://packagist.org/packages/speedwork/view)
[![Latest Unstable Version](https://poser.pugx.org/speedwork/view/v/unstable)](https://packagist.org/packages/speedwork/view)
[![License](https://poser.pugx.org/speedwork/view/license)](https://packagist.org/packages/speedwork/view)
[![Total Downloads](https://poser.pugx.org/speedwork/view/downloads)](https://packagist.org/packages/speedwork/view)
[![Build status](https://ci.appveyor.com/api/projects/status/10aw52t4ga4kek27?svg=true)](https://ci.appveyor.com/project/2stech/view)
[![Build Status](https://travis-ci.org/speedwork/view.svg?branch=master)](https://travis-ci.org/speedwork/view)

The ViewServiceProvider gives engine-agnostic templating capabilities to your [Speedwork][1] application.

Installation
------------

Use [Composer][2] to install the speedwork/view library by adding it to your `composer.json`. You'll also need a rendering engine, such as [Mustache][3].

```json
{
    "require": {
        "speedwork/view": "dev-master",
        "mustache/mustache": "~2.4"
    }
}
```

Usage
-----

Just register the service provider and optionally pass in some defaults.

```php
$app->register(new Speedwork\View\ViewServiceProvider(), array(
    'view.globals' => array('foo' => 'bar'),
    'view.default_engine' => 'mustache'
));
```

The provider registers the `ArrayToViewListener` which intercepts the output from your controllers and wraps it with a `View` object. For it to work, you have to return an array of data from your controller function.

Views
-----

Normally you do not need to instantiate any view entities on your own; the listener will convert your controller output. If you wish to do it manually, the syntax is as follows:

```php
$view = $app['engine']->create($template = '/path/to/template', $context = array('foo' => 'bar'));
```

Views can be rendered by calling the `render()` function, or casting to string:

```php
$output = $view->render();
$output = (string) $view;
```

Again, you should not need to render your views manually since they will be handled by the `Response` object.

View Context
------------

The view entity is simply an instance of `ArrayObject`, so you can use regular array notation to set the context, along with convenience functions like `with()`:

```php
$view['foo'] = 'bar';
$view->with(array('foo' => 'bar'));
```

To insert into the global context, use `share()`:

```php
$view->share(array('foo' => 'bar'));
```

You can initialize the global context by overriding `view.globals`.

Engines
-------

This library does not handle any actual view rendering; that task is delegated to the templating library of your choice. Currently adapters are provided for:

* [Mustache][3]
* [Smarty][4]
* [Twig][5]
* [Aura.View][6]
* [Plates][7]
* Raw PHP
* Token replacement using strtr()

There is a special `DelegatingEngine` which acts as a registry for multiple different engines, selecting the appropriate one based on the template file extension. Since Aura.View, Plates and Raw PHP all use the same default file extension (.php), you will need to manually configure the extension mapping as follows:

```php
$app->register(new Speedwork\View\ViewServiceProvider(), array(
    'view.default_engine' => 'php',
    'view.engines' => array(
        'php' => 'view.engine.plates'
    )
));
```

Composite Views
---------------

Views can be nested inside another:

```php
$view->nest($app['engine']->create('foobar.html'), 'section');
```

For a single view, it is equivalent to:

```php
$view['section'] = $app['engine']->create('foobar.html');
```

However, the difference lies in nesting multiple views in the same location. Doing this will place the child views adjacent to each other rather than overwriting:

```php
$view->nest($app['engine']->create('foobar.html'), 'section');
$view->nest($app['engine']->create('foobar.html'), 'section'); // foobar.html is now repeated twice
```

What's more, you can mix and match different engines:

```php
$mustacheView = $app['engine']->create('foo.mustache');
$smartyView = $app['engine']->create('bar.tpl')->nest($mustacheView, 'section');
```

Nested views will inherit the context of their parent views.

Exception Handling
------------------

All rendering exceptions are captured and stored in a shared `ExceptionBag`.

To access the last thrown exception, or return all of them:

```php
$exception = $app['engine']->getExceptionBag()->pop();
$exceptions = $app['engine']->getExceptionBag()->all();
```

License
-------

Released under the MIT license. See the LICENSE file for details.

[1]: http://github.com/speedwork
[2]: http://getcomposer.org
[3]: http://mustache.github.io
[4]: http://www.smarty.net
[5]: http://twig.sensiolabs.org
[6]: http://github.com/auraphp/Aura.View
[7]: http://platesphp.com
