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

use Es\Modules\ModulesTrait;
use Es\Mvc\ViewModelInterface;
use Es\System\SystemEvent;
use Es\View\ViewTrait;
use RuntimeException;
use UnexpectedValueException;

/**
 * Injects the module namespace of the dispatched controller to a view model
 * and to layout.
 */
class InjectModuleListener
{
    use ModulesTrait, ViewTrait;

    /**
     * Injects the module namespace of the dispatched controller to a view model
     * and to layout.
     *
     * @param \Es\System\SystemEvent $event The system event
     *
     * @throws \UnexpectedValueException If the context of system event is
     *                                   not object
     */
    public function __invoke(SystemEvent $event)
    {
        $context = $event->getContext();

        if (! is_object($context)) {
            throw new UnexpectedValueException(sprintf(
                'Invalid context of system event; must be an object, '
                . '"%s" received.',
                gettype($context)
            ));
        }

        $module = $this->resolveModule($context);

        $view   = $this->getView();
        $layout = $view->getLayout();

        if (! $layout->getModule()) {
            $layout->setModule($module);
        }

        $result = $event->getResult(SystemEvent::DISPATCH);

        if ($result instanceof ViewModelInterface && ! $result->getModule()) {
            $result->setModule($module);
        }
    }

    /**
     * Resolves the module namespace of received controller.
     *
     * @param object $controller The controller
     *
     * @throws \RuntimeException If failed to resolve the module namespace
     *
     * @return string The module namespace
     */
    protected function resolveModule($controller)
    {
        $modules = $this->getModules();

        $module = get_class($controller);
        while (false !== $pos = strrpos($module, '\\')) {
            $module = substr($module, 0, $pos);
            if ($modules->has($module)) {
                return $module;
            }
        }

        throw new RuntimeException(sprintf(
            'Failed to resolve the module namespace of the "%s" controller.',
            get_class($controller)
        ));
    }
}
