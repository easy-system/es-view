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

use Es\Services\Provider;
use Es\Services\Services;
use Es\View\View;

class ViewTraitTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        require_once 'ViewTraitTemplate.php';
    }

    public function testSetView()
    {
        $view     = new View();
        $template = new ViewTraitTemplate();
        $template->setView($view);
        $this->assertSame($view, $template->getView());
    }

    public function testGetView()
    {
        $view     = new View();
        $services = new Services();
        $services->set('View', $view);

        Provider::setServices($services);
        $template = new ViewTraitTemplate();
        $this->assertSame($view, $template->getView());
    }
}
