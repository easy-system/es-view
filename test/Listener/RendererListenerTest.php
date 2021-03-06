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

use Es\Http\Response;
use Es\Server\Server;
use Es\System\SystemEvent;
use Es\View\Listener\RendererListener;
use Es\View\View;
use Es\View\ViewModel;
use Psr\Http\Message\ResponseInterface;

class RendererListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $result = 'Lorem ipsum dolor sit amet';

        $model = new ViewModel();
        $event = new SystemEvent();
        $view  = $this->getMock(View::CLASS);

        $response = new Response();
        $server   = $this->getMock(Server::CLASS);

        $listener = new RendererListener();
        $listener->setServer($server);
        $listener->setView($view);

        $server
            ->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($response));

        $view
            ->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($model));

        $view
            ->expects($this->once())
            ->method('render')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($result));

        $listener($event);

        $outcome = $event->getResult(SystemEvent::FINISH);
        $this->assertInstanceOf(ResponseInterface::CLASS, $response);
        $this->assertSame($result, (string) $outcome->getBody());
    }
}
