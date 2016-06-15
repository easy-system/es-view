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

use Es\Events\ListenersTrait;
use Es\Modules\ModulesEvent;
use Es\System\ConfigTrait;

/**
 * Configures the view strategy.
 */
class ConfigureStrategyListener
{
    use ConfigTrait, ListenersTrait;

    /**
     * Sets the view strategy.
     *
     * @param StrategyListener $strategy The view strategy
     */
    public function setStrategy(StrategyListener $strategy)
    {
        $this->getListeners()->set('Es.View.Listener.ViewStrategy', $strategy);
    }

    /**
     * Gets the view strategy.
     *
     * @return StrategyListener The view strategy
     */
    public function getStrategy()
    {
        return $this->getListeners()->get('Es.View.Listener.ViewStrategy');
    }

    /**
     * Configures the view strategy.
     *
     * @param \Es\Modules\ModulesEvent $event The modules event
     */
    public function __invoke(ModulesEvent $event)
    {
        $config = $this->getConfig();
        if (isset($config['view']['strategy'])) {
            $strategy = $this->getStrategy();
            $strategy->setConfig((array) $config['view']['strategy']);
        }
    }
}
