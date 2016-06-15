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

use Es\Mvc\ViewInterface;
use Es\Services\Provider;

/**
 * The accessors of View.
 */
trait ViewTrait
{
    /**
     * Sets the view.
     *
     * @param \Es\Mvc\ViewInterface $view The view
     */
    public function setView(ViewInterface $view)
    {
        Provider::getServices()->set('View', $view);
    }

    /**
     * Gets the view.
     *
     * @return \Es\Mvc\ViewInterface The view
     */
    public function getView()
    {
        return Provider::getServices()->get('View');
    }
}
