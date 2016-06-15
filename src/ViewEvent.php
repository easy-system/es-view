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

use Es\Events\AbstractEvent;
use Es\Mvc\ViewModelInterface;

/**
 * The event of view.
 */
class ViewEvent extends AbstractEvent
{
    /**
     * Constructor.
     *
     * @param \Es\Mvc\ViewModelInterface $model The view model
     */
    public function __construct(ViewModelInterface $model)
    {
        $this->context = $model;
    }
}
