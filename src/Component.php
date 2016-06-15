<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\View;

use Es\Component\ComponentInterface;
use Es\Modules\ModulesEvent;
use Es\System\SystemEvent;

/**
 * The system component.
 */
class Component implements ComponentInterface
{
    /**
     * The configuration of services.
     *
     * @var array
     */
    protected $servicesConfig = [
        'View'         => 'Es\View\View',
        'ViewResolver' => 'Es\View\Resolver',
    ];

    /**
     * The configuration of listeners.
     *
     * @var array
     */
    protected $listenersConfig = [
        'Es.View.Listener.ViewRenderer'              => 'Es\View\Listener\RendererListener',
        'Es.View.Listener.ViewStrategy'              => 'Es\View\Listener\StrategyListener',
        'Es.View.Listener.ConfigureResolverListener' => 'Es\View\Listener\ConfigureResolverListener',
        'Es.View.Listener.ConfigureStrategyListener' => 'Es\View\Listener\ConfigureStrategyListener',
        'Es.View.Listener.CreateModelListener'       => 'Es\View\Listener\CreateModelListener',
        'Es.View.Listener.InjectModelListener'       => 'Es\View\Listener\InjectModelListener',
        'Es.View.Listener.InjectModuleListener'      => 'Es\View\Listener\InjectModuleListener',
        'Es.View.Listener.InjectTemplateListener'    => 'Es\View\Listener\InjectTemplateListener',
        'Es.View.Listener.ClearOutputListener'       => 'Es\View\Listener\ClearOutputListener',
    ];

    /**
     * The configuration of events.
     *
     * @var array
     */
    protected $eventsConfig = [
        'ConfigureResolverListener::__invoke' => [
            ModulesEvent::APPLY_CONFIG,
            'Es.View.Listener.ConfigureResolverListener',
            '__invoke',
            1000,
        ],
        'ConfigureStrategyListener::__invoke' => [
            ModulesEvent::APPLY_CONFIG,
            'Es.View.Listener.ConfigureStrategyListener',
            '__invoke',
            900,
        ],
        'CreateModelListener::__invoke' => [
            SystemEvent::DISPATCH,
            'Es.View.Listener.CreateModelListener',
            '__invoke',
            9000,
        ],
        'InjectModuleListener::__invoke' => [
            SystemEvent::DISPATCH,
            'Es.View.Listener.InjectModuleListener',
            '__invoke',
            8000,
        ],
        'InjectTemplateListener::__invoke' => [
            SystemEvent::DISPATCH,
            'Es.View.Listener.InjectTemplateListener',
            '__invoke',
            7000,
        ],
        'InjectModelListener::__invoke' => [
            SystemEvent::DISPATCH,
            'Es.View.Listener.InjectModelListener',
            '__invoke',
            6000,
        ],
        'ViewRenderer::__invoke' => [
            SystemEvent::RENDER,
            'Es.View.Listener.ViewRenderer',
            '__invoke',
            1000,
        ],
        'ViewStrategy::__invoke' => [
            ViewEvent::CLASS,
            'Es.View.Listener.ViewStrategy',
            '__invoke',
            1000,
        ],
        'ClearOutputListener::__invoke' => [
            SystemEvent::FINISH,
            'Es.View.Listener.ClearOutputListener',
            '__invoke',
            10500,
        ],
    ];

    /**
     * The current version of component.
     *
     * @var string
     */
    protected $version = '0.1.0';

    /**
     * Gets the current version of component.
     *
     * @return string The version of component
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Gets the configuration of services.
     *
     * @return array The configuration of services
     */
    public function getServicesConfig()
    {
        return $this->servicesConfig;
    }

    /**
     * Gets the configuration of listeners.
     *
     * @return array The configuration of listeners
     */
    public function getListenersConfig()
    {
        return $this->listenersConfig;
    }

    /**
     * Gets the configuration of events.
     *
     * @return array The configuration of events
     */
    public function getEventsConfig()
    {
        return $this->eventsConfig;
    }
}
