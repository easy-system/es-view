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

use Es\Http\ServerInterface;
use Es\Mvc\ViewModelInterface;
use Es\Services\Provider;
use Es\System\SystemEvent;
use RuntimeException;
use UnexpectedValueException;

/**
 * Injects the template name to a view model.
 */
class InjectTemplateListener
{
    /**
     * Sets the server.
     *
     * @param \Es\Http\ServerInterface $server The server
     */
    public function setServer(ServerInterface $server)
    {
        Provider::getServices()->set('Server', $server);
    }

    /**
     * Gets the server.
     *
     * @return \Es\Http\ServerInterface The server
     */
    public function getServer()
    {
        return Provider::getServices()->get('Server');
    }

    /**
     * Injects the template name to a view model.
     *
     * @param \Es\System\SystemEvent $event The system event
     *
     * @throws \RuntimeException         If the module namespace is not specified
     * @throws \UnexpectedValueException If the context of system event is
     *                                   not object
     */
    public function __invoke(SystemEvent $event)
    {
        $result = $event->getResult(SystemEvent::DISPATCH);
        if ($result instanceof ViewModelInterface && ! $result->getTemplate()) {
            $module = $result->getModule();

            if (! $module) {
                throw new RuntimeException(
                    'Unable to resolve template for View Model. '
                    . 'The module namespace is not specified.'
                );
            }
            $context = $event->getContext();

            if (! is_object($context)) {
                throw new UnexpectedValueException(sprintf(
                    'Invalid context of system event; must be an object, '
                    . '"%s" received.',
                    gettype($context)
                ));
            }
            $template = $this->resolveTemplate($context, $module);
            $result->setTemplate($template);
        }
    }

    /**
     * Resolves template for received controller.
     *
     * @param object $controller      The controller
     * @param string $moduleNamespace The module namespace
     *
     * @throws \UnexpectedValueException If the controller namespace does not
     *                                   match with namespace of module
     *
     * @return string The template name
     */
    protected function resolveTemplate($controller, $moduleNamespace)
    {
        $class = get_class($controller);

        $server  = $this->getServer();
        $request = $server->getRequest();
        $action  = $request->getAttribute('action', 'index');

        if (0 !== strpos($class, $moduleNamespace)) {
            throw new UnexpectedValueException(sprintf(
                'The View Model of Controller "%s" returned unexpected module '
                . 'namespace "%s". If you want to use this module namespace, '
                . 'you need manually set a template to View Model.',
                $class,
                $moduleNamespace
            ));
        }

        $subNamespace = substr($class, strlen($moduleNamespace) + 1);
        $subNamespace = str_replace('Controller', '', $subNamespace);
        $subNamespace = str_replace('\\\\', '\\', $subNamespace);

        $path     = str_replace('\\', '/', $subNamespace) . '/' . $action;
        $template = strtolower(
            preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $path)
        );

        return $template;
    }
}
