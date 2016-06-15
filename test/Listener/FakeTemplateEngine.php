<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\View\Test\Listener;

use Es\View\TemplateEngineInterface;
use Es\Mvc\ViewModelInterface;
use LogicException;

class FakeTemplateEngine implements TemplateEngineInterface
{
    public function render(ViewModelInterface $model)
    {
        throw new LogicException('The "%s" is stub.', __METHOD__);
    }
}
