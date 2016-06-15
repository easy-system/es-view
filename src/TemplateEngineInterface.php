<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\View;

use Es\Mvc\ViewModelInterface;

/**
 * The interface of template engine.
 */
interface TemplateEngineInterface
{
    /**
     * Renders the view model.
     *
     * @param \Es\Mvc\ViewModelInterface $model The view model
     *
     * @return string The result of rendering
     */
    public function render(ViewModelInterface $model);
}
