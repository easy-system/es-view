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

use Es\Http\Response;
use Es\Http\Stream;
use Es\System\SystemEvent;
use Es\View\Listener\ClearOutputListener;
use Psr\Http\Message\ResponseInterface;

class ClearOutputListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testInvokeWithTextHtmlContent()
    {
        $source   = "Lorem ipsum\n\n dolor sit amet,\n\n\n consectetur adipiscing elit";
        $expected = "Lorem ipsum\n dolor sit amet,\n consectetur adipiscing elit";

        $body     = Stream::make($source);
        $headers  = ['Content-type' => ['text/html']];
        $response = new Response(200, $body, $headers);

        $event = new SystemEvent();
        $event->setResult(SystemEvent::FINISH, $response);

        $listener = new ClearOutputListener();
        $listener($event);

        $result = $event->getResult(SystemEvent::FINISH);
        $this->assertInstanceOf(ResponseInterface::CLASS, $result);

        $this->assertSame($expected, (string) $result->getBody());
    }

    public function testInvokeDoesNothingIfResultOfEventIsNotResponse()
    {
        $event = new SystemEvent();
        $event->setResult(SystemEvent::FINISH, "foo\n\n bar");

        $listener = new ClearOutputListener();
        $listener($event);

        $this->assertSame("foo\n\n bar", $event->getResult(SystemEvent::FINISH));
    }

    public function testInvokeDoesNothingIfContentTypeIsNotTextHtml()
    {
        $source = "Lorem ipsum\n\n dolor sit amet,\n\n\n consectetur adipiscing elit";

        $body     = Stream::make($source);
        $headers  = ['Content-type' => ['something']];
        $response = new Response(200, $body, $headers);

        $event = new SystemEvent();
        $event->setResult(SystemEvent::FINISH, $response);

        $listener = new ClearOutputListener();
        $listener($event);

        $result = $event->getResult(SystemEvent::FINISH);
        $this->assertSame($source, (string) $result->getBody());
    }
}
