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
use Es\View\Listener\CreateModelListener;
use Es\View\ViewModel;

class CreateModelListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $result = [
            'foo' => 'bar',
            'bat' => 'baz',
        ];
        $event = new SystemEvent();
        $event->setResult(SystemEvent::DISPATCH, $result);
        $listener = new CreateModelListener();
        $listener($event);
        $model = $event->getResult(SystemEvent::DISPATCH);
        $this->assertInstanceOf(ViewModel::CLASS, $model);
        $this->assertSame($result, $model->getVariables());
    }
}
