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

use Es\View\Exception\TemplateNotFoundException;
use Es\View\Resolver;
use ReflectionProperty;

class ResolverTest extends \PHPUnit_Framework_TestCase
{
    protected $fooDir   = '';
    protected $barDir   = '';
    protected $fooIndex = '';
    protected $barIndex = '';
    protected $barBar   = '';

    public function setUp()
    {
        $files = __DIR__ . DIRECTORY_SEPARATOR . 'files';

        $this->fooDir = $files . DIRECTORY_SEPARATOR . 'Foo';
        $this->barDir = $files . DIRECTORY_SEPARATOR . 'Bar';

        $this->fooIndex = $this->fooDir . DIRECTORY_SEPARATOR
                        . Resolver::DEFAULT_PREFIX . DIRECTORY_SEPARATOR
                        . 'index' . DIRECTORY_SEPARATOR
                        . 'index.phtml';

        $this->barIndex = $this->barDir . DIRECTORY_SEPARATOR
                        . Resolver::DEFAULT_PREFIX . DIRECTORY_SEPARATOR
                        . 'index' . DIRECTORY_SEPARATOR
                        . 'index.md';

        $this->barBar = $this->barDir . DIRECTORY_SEPARATOR
                      . Resolver::DEFAULT_PREFIX . DIRECTORY_SEPARATOR
                      . 'bar' . DIRECTORY_SEPARATOR
                      . 'bar.twig';
    }

    public function templateNameDataProvider()
    {
        return [
            // module namespace | short tpl name | expected
            ['FooBarBaz\BatBan', '/foo/index', 'foo-bar-baz/bat-ban::foo/index'],
            ['ComCCCorCot',      '/com/index', 'com-c-c-cor-cot::com/index'],
            ['Foo\Bar\Baz',      '/baz/index', 'foo/bar/baz::baz/index'],
            ['EsUser',           '/foo/index', 'es-user::foo/index'],
        ];
    }

    /**
     * @dataProvider templateNameDataProvider
     */
    public function testGetFullTemplateName($module, $template, $expected)
    {
        $this->assertSame($expected, Resolver::getFullTemplateName($module, $template));
    }

    public function testRegisterModulePathRegistersPathWithPrefix()
    {
        $resolver = new Resolver();
        $return   = $resolver->registerModulePath('Foo', '/foo', true);
        $this->assertSame($return, $resolver);
        $expected = DIRECTORY_SEPARATOR . 'foo'
                  . DIRECTORY_SEPARATOR . Resolver::DEFAULT_PREFIX
                  . DIRECTORY_SEPARATOR;

        $reflection = new ReflectionProperty($resolver, 'modulesMap');
        $reflection->setAccessible(true);
        $map = $reflection->getValue($resolver);
        $this->assertTrue(isset($map['foo']));
        $this->assertSame($map['foo'], $expected);
    }

    public function testRegisterModulePathRegistersPathWithoutPrefix()
    {
        $resolver = new Resolver();
        $return   = $resolver->registerModulePath('Foo', '/foo', false);
        $this->assertSame($return, $resolver);
        $expected = DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR;

        $reflection = new ReflectionProperty($resolver, 'modulesMap');
        $reflection->setAccessible(true);
        $map = $reflection->getValue($resolver);
        $this->assertTrue(isset($map['foo']));
        $this->assertSame($map['foo'], $expected);
    }

    public function testGetModulesMap()
    {
        $resolver = new Resolver();
        $map      = [
            'FooSome'      => DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR,
            'BarSomeOther' => DIRECTORY_SEPARATOR . 'bar' . DIRECTORY_SEPARATOR,
            'Baz\Example'  => DIRECTORY_SEPARATOR . 'baz' . DIRECTORY_SEPARATOR,
        ];
        $expected = [
            'foo-some'       => DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR,
            'bar-some-other' => DIRECTORY_SEPARATOR . 'bar' . DIRECTORY_SEPARATOR,
            'baz/example'    => DIRECTORY_SEPARATOR . 'baz' . DIRECTORY_SEPARATOR,
        ];
        foreach ($map as $module => $path) {
            $resolver->registerModulePath($module, $path, false);
        }
        $this->assertSame($expected, $resolver->getModulesMap());
    }

    public function templatesMapDataProvider()
    {
        return [[
            $map = [
                '/foo/bar' => __DIR__ . '/foo/bar.phtml',
                '/bak/ban' => __DIR__ . '/bak/ban.twig',
                '/bat/baz' => __DIR__ . '/bat/baz.md',
            ],
        ]];
    }

    /**
     * @dataProvider templatesMapDataProvider
     */
    public function testRegisterTemplatesMap($map)
    {
        $resolver = new Resolver();
        $return   = $resolver->registerTemplatesMap($map);
        $this->assertSame($return, $resolver);
        $reflection = new ReflectionProperty($resolver, 'templatesMap');
        $reflection->setAccessible(true);
        $this->assertSame($map, $reflection->getValue($resolver));
    }

    /**
     * @dataProvider templatesMapDataProvider
     */
    public function testRegisterTemplatePath($map)
    {
        $resolver = new Resolver();
        foreach ($map as $template => $path) {
            $return = $resolver->registerTemplatePath($template, $path);
            $this->assertSame($return, $resolver);
        }
        $reflection = new ReflectionProperty($resolver, 'templatesMap');
        $reflection->setAccessible(true);
        $this->assertSame($map, $reflection->getValue($resolver));
    }

    /**
     * @dataProvider templatesMapDataProvider
     */
    public function testGetTemplatesMap($map)
    {
        $resolver = new Resolver();
        $resolver->registerTemplatesMap($map);
        $this->assertSame($map, $resolver->getTemplatesMap());
    }

    public function testHasTemplateOnSuccess()
    {
        $resolver = new Resolver();
        $resolver->registerTemplatePath('/foo/index', '/foo/index.phtml');
        $this->assertTrue($resolver->hasTemplate('/foo/index'));
    }

    public function testHasTemplateOnFailure()
    {
        $resolver = new Resolver();
        $this->assertFalse($resolver->hasTemplate('/foo/index'));
    }

    public function invalidTemplateDataProvider()
    {
        return [
            [__DIR__ . DIRECTORY_SEPARATOR . 'foo', 'foo', null],
            [__DIR__ . DIRECTORY_SEPARATOR . 'foo', 'bar::foo', 'bar'],
            [__DIR__ . DIRECTORY_SEPARATOR . 'foo', 'bar::foo', 'bar'],
        ];
    }

    /**
     * @dataProvider invalidTemplateDataProvider
     */
    public function testResolveRaiseExceptionIfInvalidTemplateRegistered($path, $template, $module)
    {
        $resolver = new Resolver();
        $resolver->registerTemplatePath($template, $path);
        $this->setExpectedException('RuntimeException');
        $resolver->resolve($template, $module);
    }

    public function notExistedTemplateDataProvider()
    {
        return [
            ['foo',      null],
            ['foo::bar', null],
            ['foo',      'bar'],
        ];
    }

    /**
     * @dataProvider notExistedTemplateDataProvider
     */
    public function testResolverRaiseExceptionIfTemplateNotFound($template, $module)
    {
        $resolver = new Resolver();
        if ($module) {
            $resolver->registerModulePath($module, __DIR__);
        }
        $this->setExpectedException(TemplateNotFoundException::CLASS);
        $resolver->resolve($template, $module);
    }

    public function testResolverResolveTemplateFromFullTemplatePath()
    {
        $resolver = new Resolver();

        $resolver->registerTemplatePath('foo::index/index', $this->fooIndex);
        $resolver->registerTemplatePath('bar::index/index', $this->barIndex);
        $resolver->registerTemplatePath('bar::bar/bar',     $this->barBar);

        $this->assertSame($this->fooIndex, $resolver->resolve('/index/index', 'Foo'));
        $this->assertSame($this->barIndex, $resolver->resolve('/index/index', 'Bar'));
        $this->assertSame($this->barBar,   $resolver->resolve('/bar/bar',     'Bar'));

        $this->assertSame($this->fooIndex, $resolver->resolve('foo::index/index'));
        $this->assertSame($this->barIndex, $resolver->resolve('bar::index/index'));
        $this->assertSame($this->barBar,   $resolver->resolve('bar::bar/bar'));
    }

    public function testResolverResolveTemplateFromShortTemplatePath()
    {
        $resolver = new Resolver();
        $resolver->registerTemplatePath('index/index', $this->fooIndex);
        $resolver->registerTemplatePath('bar/bar',     $this->barBar);

        $this->assertSame($this->fooIndex, $resolver->resolve('/index/index'));
        $this->assertSame($this->barBar,   $resolver->resolve('/bar/bar'));
    }

    public function testResolverResolveTemplateFromSpecifiedModule()
    {
        $resolver = new Resolver();
        $resolver->registerModulePath('Foo', $this->fooDir);
        $resolver->registerModulePath('Bar', $this->barDir);

        $this->assertSame($this->fooIndex, $resolver->resolve('/index/index', 'Foo'));
        $this->assertSame($this->barIndex, $resolver->resolve('/index/index', 'Bar'));
        $this->assertSame($this->barBar,   $resolver->resolve('/bar/bar',     'Bar'));

        $this->assertSame($this->fooIndex, $resolver->resolve('foo::index/index'));
        $this->assertSame($this->barIndex, $resolver->resolve('bar::index/index'));
        $this->assertSame($this->barBar,   $resolver->resolve('bar::bar/bar'));
    }

    public function testResolverResolveTemplateOfLastUsedModule()
    {
        $resolver = new Resolver();
        $resolver->registerModulePath('Foo', $this->fooDir);
        $resolver->registerModulePath('Bar', $this->barDir);

        $this->assertSame($this->barIndex, $resolver->resolve('/index/index', 'Bar'));
        $this->assertSame($this->barBar,   $resolver->resolve('/bar/bar'));
    }

    public function testResolverRaiseExceptionIfLastUsedModuleNotContainTemplate()
    {
        $resolver = new Resolver();
        $resolver->registerModulePath('Bar', $this->barDir);

        $this->assertSame($this->barIndex, $resolver->resolve('/index/index', 'Bar'));

        $this->setExpectedException(TemplateNotFoundException::CLASS);
        $resolver->resolve('foo/bar');
    }
}
