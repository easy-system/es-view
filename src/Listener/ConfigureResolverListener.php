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

use Es\Modules\ModulesEvent;
use Es\Modules\ModulesTrait;
use Es\Services\Provider;
use Es\System\ConfigTrait;
use Es\View\Resolver;
use UnexpectedValueException;

/**
 * Configures the view resolver.
 */
class ConfigureResolverListener
{
    use ConfigTrait, ModulesTrait;

    /**
     * Sets the view resolver.
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
     * Configures the view resolver.
     *
     * @param \Es\Modules\ModulesEvent $event The modules event
     *
     * @throws \UnexpectedValueException If configuration has invalid
     *                                   specification of view resolver
     */
    public function __invoke(ModulesEvent $event)
    {
        $modules  = $this->getModules();
        $resolver = $this->getResolver();

        foreach ($modules as $name => $module) {
            $resolver->registerModulePath($name, $module->getModuleDir());
        }

        $config = $this->getConfig();
        if (! isset($config['view']['resolver'])) {
            return;
        }
        $map = (array) $config['view']['resolver'];
        foreach ($map as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $template => $path) {
                    if (! is_string($path)) {
                        throw new UnexpectedValueException(sprintf(
                            'Invalid specification of view resolver for "%s" '
                            . 'provided; the path of template "%s" must be an '
                            . 'string, "%s" received.',
                            $key,
                            $template,
                            is_object($path) ? get_class($path) : gettype($path)
                        ));
                    }
                    $template = Resolver::getFullTemplateName($key, $template);
                    $resolver->registerTemplatePath($template, $path);
                }
            } elseif (is_string($value)) {
                $resolver->registerTemplatePath($key, $value);
            } else {
                throw new UnexpectedValueException(sprintf(
                    'Invalid specification of view resolver provided; '
                    . '"%s" must be an string or an array, "%s" received.',
                    $key,
                    is_object($value) ? get_class($value) : gettype($value)
                ));
            }
        }
    }
}
