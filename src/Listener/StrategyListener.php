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

use Es\Services\Provider;
use Es\Services\ServicesTrait;
use Es\View\Resolver;
use Es\View\TemplateEngineInterface;
use Es\View\ViewEvent;
use UnexpectedValueException;

/**
 * The view strategy.
 */
class StrategyListener
{
    use ServicesTrait;

    /**
     * The array with extension of template file as key and name of template
     * engine as value.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Sets the reaolver.
     *
     * @param \Es\View\Resolver $resolver The view resolver
     */
    public function setResolver(Resolver $resolver)
    {
        Provider::getServices()->set('ViewResolver', $resolver);
    }

    /**
     * Gets the view resolver.
     *
     * @return \Es\View\Resolver The view resolver
     */
    public function getResolver()
    {
        return Provider::getServices()->get('ViewResolver');
    }

    /**
     * Sets the configuration.
     *
     * @param array $config The configuration of view strategy
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * Gets the configuration.
     *
     * @return array The configuration of view strategy
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Selects the template engine and trigger it.
     *
     * @param \Es\View\ViewEvent $event The view event
     *
     * @throws \UnexpectedValueException
     *
     * - If the extension of template file is not associated with any
     *   template engine
     * - If the specified template engine not implements
     *   the Es\View\TemplateEngineInterface
     */
    public function __invoke(ViewEvent $event)
    {
        $resolver  = $this->getResolver();
        $model     = $event->getContext();
        $template  = $model->getTemplate();
        $module    = $model->getModule();
        $file      = $resolver->resolve($template, $module);
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        if (! isset($this->config[$extension])) {
            throw new UnexpectedValueException(sprintf(
                'Unable to render template "%s" of module "%s". the extension '
                . '"%s" of template file is not associated with any '
                . 'template engine.',
                $template,
                $module,
                $extension
            ));
        }
        $services = $this->getServices();
        $engine   = $services->get($this->config[$extension]);

        if (! $engine instanceof TemplateEngineInterface) {
            throw new UnexpectedValueException(sprintf(
                'The template engine "%s" must implement the "%s".',
                $this->config[$extension],
                TemplateEngineInterface::CLASS
            ));
        }
        $result = $engine->render($model);

        $event->setResult($result);
    }
}
