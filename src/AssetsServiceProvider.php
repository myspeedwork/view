<?php

/*
 * This file is part of the Speedwork package.
 *
 * (c) Sankar <sankar.suda@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Speedwork\View;

use Speedwork\Container\Container;
use Speedwork\Container\ServiceProvider;

/**
 * AssetsServiceProvider to manage web assets.
 *
 * @author sankar <sankar.suda@gmail.com>>
 */
class AssetsServiceProvider extends ServiceProvider
{
    public function register(Container $app)
    {
        $app['assets'] = function ($app) {
            return new Assets($app['assetic.add']);
        };
    }
}
