<?php

/*
 * This file is part of the Speedwork package.
 *
 * (c) Sankar <sankar.suda@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Speedwork\View\Parser;

use Smarty;
use Smarty_Security;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class SmartySecurity extends Smarty_Security
{
    public $php_handling    = Smarty::PHP_REMOVE;
    public $static_classes  = 'none';
    public $streams         = null;
    public $allow_constants = false;
     // allow everthing as modifier
    public $modifiers = [];

    public function isTrustedResourceDir($filepath, $isConfig = null)
    {
        return true;
    }
}
