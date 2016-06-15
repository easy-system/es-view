<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\View\Listener;

use Es\Mvc\ViewModelInterface;
use Es\System\SystemEvent;
use Es\View\ViewTrait;

/**
 * Injects the view model to Layout.
 */
class InjectModelListener
{
    use ViewTrait;

    /**
     * Injects the view model to Layout.
     *
     * @param \Es\System\SystemEvent $event The system event
     */
    public function __invoke(SystemEvent $event)
    {
        $result = $event->getResult(SystemEvent::DISPATCH);
        if ($result instanceof ViewModelInterface) {
            $view   = $this->getView();
            $layout = $view->getLayout();
            $layout->addChild($result);
        }
    }
}
