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

use Es\Events\Listeners;
use Es\Modules\ModulesEvent;
use Es\Services\Provider;
use Es\Services\Services;
use Es\System\SystemConfig;
use Es\View\Listener\ConfigureStrategyListener;
use Es\View\Listener\StrategyListener;

class ConfigureStrategyListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetStrategy()
    {
        $services = new Services();
        $listeners = new Listeners();
        $services->set('Listeners', $listeners);

        $strategy  = new StrategyListener();
        $listeners->set('Es.View.Listener.ViewStrategy', $strategy);

        Provider::setServices($services);
        $listener = new ConfigureStrategyListener();
        $this->assertSame($strategy, $listener->getStrategy());
    }

    public function testSetStrategy()
    {
        $services = new Services();
        $listeners = new Listeners();
        $services->set('Listeners', $listeners);
        Provider::setServices($services);

        $strategy = new StrategyListener();
        $listener = new ConfigureStrategyListener();
        $listener->setStrategy($strategy);
        $this->assertSame($strategy, $listeners->get('Es.View.Listener.ViewStrategy'));
    }

    public function testInvoke()
    {
        $config = new SystemConfig();

        $strategyConfig = [
            'foo' => 'bar',
            'bat' => 'baz',
        ];
        $config['view']['strategy'] = $strategyConfig;

        $strategy = $this->getMock(StrategyListener::CLASS);
        $listener = new ConfigureStrategyListener();

        $listener->setConfig($config);
        $listener->setStrategy($strategy);

        $strategy
            ->expects($this->once())
            ->method('setConfig')
            ->with($this->identicalTo($strategyConfig));

        $listener(new ModulesEvent());
    }
}
