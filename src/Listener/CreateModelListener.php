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
use Es\View\ViewModel;
use Psr\Http\Message\ResponseInterface;

/**
 * Creates the view model from result of dispatching.
 */
class CreateModelListener
{
    /**
     * Creates the view model.
     *
     * @param SystemEvent $event The system event
     */
    public function __invoke(SystemEvent $event)
    {
        $result = $event->getResult(SystemEvent::DISPATCH);
        if (false !== $result
            && ! $result instanceof ViewModelInterface
            && ! $result instanceof ResponseInterface
        ) {
            $model = new ViewModel($result);
            $event->setResult(SystemEvent::DISPATCH, $model);
        }
    }
}
