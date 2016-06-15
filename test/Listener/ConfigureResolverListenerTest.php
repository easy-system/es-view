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

use Es\Modules\AbstractModule;
use Es\Modules\Modules;
use Es\Modules\ModulesEvent;
use Es\Services\Provider;
use Es\Services\Services;
use Es\System\SystemConfig;
use Es\View\Listener\ConfigureResolverListener;
use Es\View\Resolver;

class ConfigureResolverListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetResolver()
    {
        $services = new Services();
        Provider::setServices($services);
        $resolver = new Resolver();

        $listener = new ConfigureResolverListener();
        $listener->setResolver($resolver);
        $this->assertSame($resolver, $services->get('ViewResolver'));
    }

    public function testSetResolver()
    {
        $resolver = new Resolver();
        $services = new Services();
        $services->set('ViewResolver', $resolver);
        Provider::setServices($services);

        $listener = new ConfigureResolverListener();
        $this->assertSame($resolver, $listener->getResolver());
    }

    public function invalidConfigItemDataProvider()
    {
        $items = [
            null,
            true,
            false,
            100,
            new \stdClass(),
        ];
        $return = [];
        foreach ($items as $item) {
            $return[] = [$item];
        }

        return $return;
    }

    /**
     * @dataProvider invalidConfigItemDataProvider
     */
    public function testInvokeRaiseExceptionIfInvalidItemOfConfigurationProvided($item)
    {
        $viewConfig = [
            'resolver' => [
                'foo' => $item,
            ],
        ];
        $config         = new SystemConfig();
        $config['view'] = $viewConfig;
        $modules        = new Modules();

        $listener = new ConfigureResolverListener();

        $listener->setConfig($config);
        $listener->setModules($modules);

        $this->setExpectedException('UnexpectedValueException');
        $listener(new ModulesEvent());
    }

    public function invalidTemplatePathDataProvider()
    {
        $paths = [
            null,
            true,
            false,
            100,
            [],
            new \stdClass(),
        ];
        $return = [];
        foreach ($paths as $path) {
            $return[] = [$path];
        }

        return $return;
    }

    /**
     * @dataProvider invalidTemplatePathDataProvider
     */
    public function testInvokeRaiseExceptionIfInvalidTemplatePathProvided($path)
    {
        $viewConfig = [
            'resolver' => [
                'foo-module' => [
                    'foo-template' => $path,
                ],
            ],
        ];
        $config         = new SystemConfig();
        $config['view'] = $viewConfig;
        $modules        = new Modules();

        $listener = new ConfigureResolverListener();

        $listener->setConfig($config);
        $listener->setModules($modules);

        $this->setExpectedException('UnexpectedValueException');
        $listener(new ModulesEvent());
    }

    public function testInvokeRegisterModulePath()
    {
        $moduleName = 'Foo';
        $module     = $this->getMock(AbstractModule::CLASS);
        $moduleDir  = $module->getModuleDir();

        $modules = new Modules();
        $modules->set($moduleName, $module);
        $resolver = $this->getMock(Resolver::CLASS);
        $config   = new SystemConfig();

        $listener = new ConfigureResolverListener();

        $listener->setModules($modules);
        $listener->setResolver($resolver);
        $listener->setConfig($config);

        $resolver
            ->expects($this->once())
            ->method('registerModulePath')
            ->with(
                $this->identicalTo($moduleName),
                $this->identicalTo($moduleDir)
            );

        $listener(new ModulesEvent());
    }

    public function testInvokeRegisterPathForShortTemplateName()
    {
        $viewConfig = [
            'resolver' => [
                'bar' => 'foo/bar.tpl',
            ],
        ];
        $config         = new SystemConfig();
        $config['view'] = $viewConfig;
        $resolver       = $this->getMock(Resolver::CLASS);
        $modules        = new Modules();

        $listener = new ConfigureResolverListener();

        $listener->setResolver($resolver);
        $listener->setModules($modules);
        $listener->setConfig($config);

        $resolver
            ->expects($this->once())
            ->method('registerTemplatePath')
            ->with(
                $this->identicalTo('bar'),
                $this->identicalTo('foo/bar.tpl')
            );

        $listener(new ModulesEvent());
    }

    public function testInvokeRegisterPathForFullTemplateName()
    {
        $viewConfig = [
            'resolver' => [
                'foo' => [
                    'bar' => 'foo/bar.tpl',
                ],
            ],
        ];
        $config         = new SystemConfig();
        $config['view'] = $viewConfig;
        $resolver       = $this->getMock(Resolver::CLASS);
        $modules        = new Modules();

        $listener = new ConfigureResolverListener();

        $listener->setResolver($resolver);
        $listener->setModules($modules);
        $listener->setConfig($config);

        $resolver
            ->expects($this->once())
            ->method('registerTemplatePath')
            ->with(
                $this->identicalTo(Resolver::getFullTemplateName('foo', 'bar')),
                $this->identicalTo('foo/bar.tpl')
            );

        $listener(new ModulesEvent());
    }
}
