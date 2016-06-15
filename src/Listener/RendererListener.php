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
use Es\Http\Stream;
use Es\Services\Provider;
use Es\System\SystemEvent;
use Es\View\ViewTrait;

/**
 * Renders the view.
 */
class RendererListener
{
    use ViewTrait;

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
     * Renders the view.
     *
     * @param \Es\System\SystemEvent $event The system event
     */
    public function __invoke(SystemEvent $event)
    {
        $view   = $this->getView();
        $layout = $view->getLayout();
        $result = $view->render($layout);

        $contentType = $layout->getContentType();

        $server   = $this->getServer();
        $response = $server->getResponse()
            ->withHeader('Content-Type', $contentType)
            ->withBody(Stream::make($result));

        $event->setResult(SystemEvent::FINISH, $response);
    }
}
