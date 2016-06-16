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

use Es\Server\Server;
use Es\System\SystemEvent;
use Es\View\Listener\InjectTemplateListener;
use Es\View\ViewModel;

class InjectTemplateListenerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        require_once 'FakeController.php';
    }

    public function testInvokeDoesNothingIfDispatchingResultIsNotViewModel()
    {
        $event    = $this->getMock(SystemEvent::CLASS);
        $listener = new InjectTemplateListener();

        $event
            ->expects($this->once())
            ->method('getResult')
            ->with($this->identicalTo(SystemEvent::DISPATCH))
            ->will($this->returnValue(false));

        $event
            ->expects($this->never())
            ->method('getContext');

        $listener($event);
    }

    public function testInvokeRaiseExceptionIfModuleNamespaceIsNotSpecified()
    {
        $model = new ViewModel();
        $event = new SystemEvent();
        $event->setResult(SystemEvent::DISPATCH, $model);

        $listener = new InjectTemplateListener();
        $this->setExpectedException('RuntimeException');
        $listener($event);
    }

    public function invalidControllersDataProvider()
    {
        $controllers = [
            null,
            true,
            false,
            100,
            'string',
        ];
        $return = [];
        foreach ($controllers as $controller) {
            $return[] = [$controller];
        }

        return $return;
    }

    /**
     * @dataProvider invalidControllersDataProvider
     */
    public function testInvokeRaiseExceptionIfContextOfSystemEventIsNotObject($controller)
    {
        $model = new ViewModel();
        $model->setModule('Foo');
        $event = new SystemEvent();

        $event->setResult(SystemEvent::DISPATCH, $model);
        $event->setContext($controller);

        $listener = new InjectTemplateListener();
        $this->setExpectedException('UnexpectedValueException');

        $listener($event);
    }

    public function testInvokeRaiseExceptionIfControllerNamespaceNotMatchWithNamespaceOfModule()
    {
        $model = new ViewModel();
        $model->setModule('Foo');
        $event = new SystemEvent();

        $event->setResult(SystemEvent::DISPATCH, $model);
        $event->setContext(new \stdClass());

        $server   = new Server();
        $listener = new InjectTemplateListener();
        $listener->setServer($server);
        $this->setExpectedException('UnexpectedValueException');

        $listener($event);
    }

    public function testInvokeOnSuccess()
    {
        $server  = new Server();
        $request = $server->getRequest();
        $server->setRequest($request->withAttribute('action', 'foo'));

        $model = new ViewModel();
        $model->setModule(__NAMESPACE__);
        $event = new SystemEvent();

        $event->setResult(SystemEvent::DISPATCH, $model);
        $event->setContext(new FakeController());

        $listener = new InjectTemplateListener();
        $listener->setServer($server);

        $listener($event);

        $this->assertSame('fake/foo', $model->getTemplate());
    }
}
