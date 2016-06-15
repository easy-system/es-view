<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\View\Exception;

use Es\Exception\NotFoundExceptionInterface;
use Exception;

class TemplateNotFoundException extends Exception implements NotFoundExceptionInterface
{
}
