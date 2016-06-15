<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\View\Test;

use Es\Events\Events;
use Es\Mvc\ViewModelInterface;
use Es\Services\Services;
use Es\View\View;
use Es\View\ViewEvent;
use Es\View\ViewModel;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    public function testGetLayout()
    {
        $view   = new View();
        $layout = $view->getLayout();
        $this->assertInstanceOf(ViewModelInterface::CLASS, $layout);
        $this->assertSame('layout/layout', $layout->getTemplate());
    }

    public function testSetLayout()
    {
        $layout = new ViewModel();
        $view   = new View();
        $view->setLayout($layout);
        $this->assertSame($layout, $view->getLayout());
    }

    public function testGetEvents()
    {
        $events   = new Events();
        $services = new Services();
        $services->set('Events', $events);
        $view = new View();
        $view->setServices($services);
        $this->assertSame($events, $view->getEvents());
    }

    public function testSetEvents()
    {
        $events = new Events();
        $view   = new View();
        $view->setEvents($events);
        $this->assertSame($events, $view->getEvents());
    }

    public function testRenderOnSuccess()
    {
        $root = new ViewModel(['id' => 'root']);
        $foo  = new ViewModel(['id' => 'foo']);
        $bar  = new ViewModel(['id' => 'bar']);

        $root->addChild($foo);
        $root->addChild($bar);

        $events = $this->getMock(Events::CLASS, ['trigger']);
        $view   = new View();
        $view->setEvents($events);

        $events
            ->expects($this->atLeastOnce())
            ->method('trigger')
            ->with($this->callback(function ($event) {
                $this->assertInstanceOf(ViewEvent::CLASS, $event);
                $model = $event->getContext();
                $this->assertInstanceOf(ViewModelInterface::CLASS, $model);
                $event->setResult($model->getVariable('id') . $model->getVariable('content'));

                return true;
            }));

        $this->assertSame('rootfoobar', $view->render($root));
    }

    public function testRenderIgnoreModelWithoutGroupId()
    {
        $root    = new ViewModel();
        $ignored = new ViewModel();
        $root->addChild($ignored);

        $ignored->setGroupId(null);

        $events = $this->getMock(Events::CLASS, ['trigger']);
        $view   = new View();
        $view->setEvents($events);

        $events
            ->expects($this->atLeastOnce())
            ->method('trigger')
            ->with($this->callback(function ($event) use ($ignored) {
                $this->assertInstanceOf(ViewEvent::CLASS, $event);
                $model = $event->getContext();
                $this->assertInstanceOf(ViewModelInterface::CLASS, $model);

                if ($model === $ignored) {
                    return false;
                }

                return true;
            }));

        $view->render($root);
    }
}
