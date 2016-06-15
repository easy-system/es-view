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

use Es\Services\Provider;
use Es\Services\Services;
use Es\View\Listener\StrategyListener;
use Es\View\Resolver;
use Es\View\ViewEvent;
use Es\View\ViewModel;

class StrategyListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $fooTemplate;

    public function setUp()
    {
        require_once 'FakeTemplateEngine.php';

        $files = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files';

        $this->fooTemplate = $files . DIRECTORY_SEPARATOR
                           . 'Foo' . DIRECTORY_SEPARATOR
                           . 'view' . DIRECTORY_SEPARATOR
                           . 'index' . DIRECTORY_SEPARATOR
                           . 'index.phtml';
    }

    public function testGetResolver()
    {
        $resolver = new Resolver();
        $services = new Services();
        $services->set('ViewResolver', $resolver);

        Provider::setServices($services);
        $listener = new StrategyListener();
        $this->assertSame($resolver, $listener->getResolver());
    }

    public function testSetResolver()
    {
        $services = new Services();
        Provider::setServices($services);

        $resolver = new Resolver();
        $listener = new StrategyListener();
        $listener->setResolver($resolver);
        $this->assertSame($resolver, $services->get('ViewResolver'));
    }

    public function testSetConfigGetConfig()
    {
        $config = [
            'foo' => 'bar',
            'bat' => 'baz',
        ];
        $listener = new StrategyListener();
        $listener->setConfig($config);
        $this->assertSame($config, $listener->getConfig());
    }

    public function testInvokeRaiseExceptionIfUnableToDetermineTemplateEngine()
    {
        $resolver = $this->getMock(Resolver::CLASS);
        $listener = new StrategyListener();
        $listener->setResolver($resolver);

        $model = new ViewModel();
        $event = new ViewEvent($model);

        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->will($this->returnValue($this->fooTemplate));

        $this->setExpectedException('UnexpectedValueException');
        $listener($event);
    }

    public function testInvokeRaiseExceptionIfTemplateEngineNotImplementTheTemplateEngineInterface()
    {
        $services = new Services();
        $services->set('FooTemplateEngine', new \stdClass());
        Provider::setServices($services);

        $resolver = $this->getMock(Resolver::CLASS);
        $listener = new StrategyListener();
        $listener->setResolver($resolver);

        $listener->setConfig(['phtml' => 'FooTemplateEngine']);

        $model = new ViewModel();
        $event = new ViewEvent($model);

        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->will($this->returnValue($this->fooTemplate));

        $this->setExpectedException('UnexpectedValueException');
        $listener($event);
    }

    public function testInvokeOnSuccess()
    {
        $result = 'Lorem ipsum dolor sit amet';
        $engine = $this->getMock(FakeTemplateEngine::CLASS);

        $services = new Services();
        $services->set('FooTemplateEngine', $engine);
        Provider::setServices($services);

        $resolver = $this->getMock(Resolver::CLASS);
        $listener = new StrategyListener();
        $listener->setResolver($resolver);

        $listener->setConfig(['phtml' => 'FooTemplateEngine']);

        $model = new ViewModel();
        $event = new ViewEvent($model);

        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->will($this->returnValue($this->fooTemplate));

        $engine
            ->expects($this->once())
            ->method('render')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($result));

        $listener($event);

        $this->assertSame($result, $event->getResult());
    }
}
