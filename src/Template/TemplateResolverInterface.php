<?php

/*
 * This file is part of the Speedwork package.
 *
 * (c) Sankar <sankar.suda@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Speedwork\View\Template;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for resolving templates.
 *
 * @author Chris Heng <bigblah@gmail.com>
 */
interface TemplateResolverInterface
{
    /**
     * Returns the template path.
     *
     * @param Request $request          The request entity
     * @param mixed   $controllerResult The controller output
     *
     * @return string The template path
     */
    public function resolve(Request $request, $controllerResult = null);
}
