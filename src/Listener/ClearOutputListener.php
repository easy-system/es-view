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

use Es\Http\Stream;
use Es\System\SystemEvent;
use Psr\Http\Message\ResponseInterface;

/**
 * Clears the output.
 */
class ClearOutputListener
{
    /**
     * Removes from the response of type "text/html" the blank lines.
     *
     * @param \Es\System\SystemEvent $event The system event
     */
    public function __invoke(SystemEvent $event)
    {
        $result = $event->getResult(SystemEvent::FINISH);

        if (! $result instanceof ResponseInterface) {
            return;
        }

        $contentType = $result->getHeaderLine('Content-Type');
        if (0 === strpos($contentType, 'text/html')) {
            $body    = (string) $result->getBody();
            $cleaned = preg_replace("#(\s)*(\n)+(\r)*#", "\n", $body);
            $stream  = Stream::make($cleaned);
            $event->setResult(SystemEvent::FINISH, $result->withBody($stream));
        }
    }
}
