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

use Es\Modules\AbstractModule;
use Es\Modules\Modules;
use Es\System\SystemEvent;
use Es\View\Listener\InjectModuleListener;
use Es\View\View;
use Es\View\ViewModel;

class InjectModuleListenerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        require_once 'FakeController.php';
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
        $event = new SystemEvent();
        $event->setContext($controller);
        $listener = new InjectModuleListener();
        $this->setExpectedException('UnexpectedValueException');
        $listener($event);
    }

    public function testInvokeRaiseExceptionIfUnableToResolveModuleNamespace()
    {
        $modules  = new Modules();
        $listener = new InjectModuleListener();
        $listener->setModules($modules);

        $event = new SystemEvent();
        // context with complex namespace
        $event->setContext(new self());

        $this->setExpectedException('RuntimeException');
        $listener($event);
    }

    public function testInvokeDoesNothingIfModuleIsAlreadySpecifiedInViewModel()
    {
        $modules = new Modules();
        $module  = $this->getMock(AbstractModule::CLASS);
        $modules->set(__NAMESPACE__, $module);

        $view   = new View();
        $layout = $view->getLayout();
        $layout->setModule('Foo');

        $listener = new InjectModuleListener();
        $listener->setModules($modules);
        $listener->setView($view);

        $event = new SystemEvent();

        $model = new ViewModel();
        $model->setModule('Bar');
        $controller = new FakeController();

        $event->setContext($controller);
        $event->setResult(SystemEvent::DISPATCH, $model);

        $listener($event);
        $this->assertSame('Foo', $layout->getModule());
        $this->assertSame('Bar', $model->getModule());
    }

    public function testInvokeOnSuccess()
    {
        $modules = new Modules();
        $module  = $this->getMock(AbstractModule::CLASS);
        $modules->set(__NAMESPACE__, $module);

        $view = new View();

        $listener = new InjectModuleListener();
        $listener->setModules($modules);
        $listener->setView($view);

        $event = new SystemEvent();

        $model      = new ViewModel();
        $controller = new FakeController();

        $event->setContext($controller);
        $event->setResult(SystemEvent::DISPATCH, $model);

        $listener($event);

        $layout = $view->getLayout();

        $this->assertSame(__NAMESPACE__, $layout->getModule());
        $this->assertSame(__NAMESPACE__, $model->getModule());
    }
}
