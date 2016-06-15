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

use Es\View\ViewEvent;
use Es\View\ViewModel;

class ViewEventTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $model = new ViewModel();
        $event = new ViewEvent($model);
        $this->assertSame($model, $event->getContext());
    }
}
