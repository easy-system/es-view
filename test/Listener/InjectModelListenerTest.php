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

use Es\System\SystemEvent;
use Es\View\Listener\InjectModelListener;
use Es\View\View;
use Es\View\ViewModel;

class InjectModelListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testInvokeOnSuccess()
    {
        $view     = new View();
        $listener = new InjectModelListener();
        $listener->setView($view);

        $event = new SystemEvent();
        $model = new ViewModel();
        $event->setResult(SystemEvent::DISPATCH, $model);
        $listener($event);

        $layout   = $view->getLayout();
        $iterator = $layout->getIterator();
        $array    = iterator_to_array($iterator);
        $this->assertContains($model, $array);
    }
}
