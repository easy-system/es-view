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

use ArrayIterator;
use ArrayObject;
use Es\View\ViewModel;
use ReflectionProperty;

class ViewModelTest extends \PHPUnit_Framework_TestCase
{
    public function variousTypesOfVariablesDataProvider()
    {
        return [
            [ // array
                ['bat' => 'baz', 'com' => 'cot'],
                ['bat' => 'baz', 'com' => 'cot'],
            ],
            [ // stdClass
                (object) ['bat' => 'baz', 'com' => 'cot'],
                ['bat' => 'baz', 'com' => 'cot'],
            ],
            [ // Traversable
                new ArrayObject(['bat' => 'baz', 'com' => 'cot']),
                ['bat' => 'baz', 'com' => 'cot'],
            ],
        ];
    }

    /**
     * @dataProvider variousTypesOfVariablesDataProvider
     */
    public function testConstructorSetVariables($variables, $arrayRepresentation)
    {
        $model      = new ViewModel($variables);
        $reflection = new ReflectionProperty($model, 'container');
        $reflection->setAccessible(true);
        $this->assertSame($arrayRepresentation, $reflection->getValue($model));
    }

    public function testConstructorSetTemplate()
    {
        $model = new ViewModel(null, 'foo');
        $this->assertSame('foo', $model->getTemplate());
    }

    public function testConstructorSetModule()
    {
        $model = new ViewModel(null, null, 'foo');
        $this->assertSame('foo', $model->getModule());
    }

    public function testGetContentTypeReturnDefaultContentType()
    {
        $model = new ViewModel();
        $this->assertSame('text/html', $model->getContentType());
    }

    public function testSetContentType()
    {
        $type  = 'application/pdf';
        $model = new ViewModel();
        $model->setContentType($type);
        $this->assertSame($type, $model->getContentType());
    }

    /**
     * @dataProvider variousTypesOfVariablesDataProvider
     */
    public function testSetVariables($variables, $arrayRepresentation)
    {
        $model  = new ViewModel(['foo' => 'baz']);
        $return = $model->setVariables($variables);
        $this->assertSame($return, $model);
        $reflection = new ReflectionProperty($model, 'container');
        $reflection->setAccessible(true);
        $this->assertSame($arrayRepresentation, $reflection->getValue($model));
    }

    /**
     * @dataProvider variousTypesOfVariablesDataProvider
     */
    public function testAddVariables($variables, $arrayRepresentation)
    {
        $initialVariables = [
            'foo' => 'bar',
            'bat' => 'ban',
        ];
        $model  = new ViewModel($initialVariables);
        $return = $model->addVariables($variables);
        $this->assertSame($return, $model);

        $expected = array_merge($initialVariables, $arrayRepresentation);

        $reflection = new ReflectionProperty($model, 'container');
        $reflection->setAccessible(true);
        $this->assertSame($expected, $reflection->getValue($model));
    }

    public function invalidVariablesDataProvider()
    {
        $variables = [
            true,
            false,
            100,
            'string',
        ];
        $return = [];
        foreach ($variables as $variable) {
            $return[] = [$variable];
        }

        return $return;
    }

    /**
     * @dataProvider invalidVariablesDataProvider
     */
    public function testConstructorRaiseExceptionIfInvalidVariablesTypeProvided($variables)
    {
        $this->setExpectedException('InvalidArgumentException');
        $model = new ViewModel($variables);
    }

    /**
     * @dataProvider invalidVariablesDataProvider
     */
    public function testSetVariablesRaiseExceptionIfInvalidVariablesTypeProvided($variables)
    {
        $model = new ViewModel();
        $this->setExpectedException('InvalidArgumentException');
        $model->setVariables($variables);
    }

    /**
     * @dataProvider invalidVariablesDataProvider
     */
    public function testAddVariablesRaiseExceptionIfInvalidVariablesTypeProvided($variables)
    {
        $model = new ViewModel();
        $this->setExpectedException('InvalidArgumentException');
        $model->addVariables($variables);
    }

    public function testSetVariable()
    {
        $model  = new ViewModel(['foo' => 'bar']);
        $return = $model->setVariable('foo', 'bat');
        $this->assertSame($return, $model);
        $expected = [
            'foo' => 'bat',
        ];
        $reflection = new ReflectionProperty($model, 'container');
        $reflection->setAccessible(true);
        $this->assertSame($expected, $reflection->getValue($model));
    }

    public function testGetVariables()
    {
        $variables = [
            'foo' => 'bar',
            'bat' => 'baz',
        ];
        $model = new ViewModel($variables);
        $this->assertSame($variables, $model->getVariables());
    }

    public function variablesDataProvider()
    {
        return [
            ['foo', true],
            ['ban', false],
            ['bar', 100],
            ['bas', 'string'],
            ['bat', []],
            ['baz', new \stdClass()],
        ];
    }

    /**
     * @dataProvider variablesDataProvider
     */
    public function testGetVariableReturnsVariable($name, $value)
    {
        $model = new ViewModel();
        $model->setVariable($name, $value);
        $this->assertSame($value, $model->getVariable($name));
    }

    /**
     * @dataProvider variablesDataProvider
     */
    public function testGetVariableReturnsDefault($name, $value)
    {
        $model = new ViewModel();
        $this->assertSame($value, $model->getVariable($name, $value));
    }

    public function testSetModule()
    {
        $model  = new ViewModel();
        $return = $model->setModule('Foo');
        $this->assertSame($return, $model);
        $reflection = new ReflectionProperty($model, 'moduleName');
        $reflection->setAccessible(true);
        $this->assertSame('Foo', $reflection->getValue($model));
    }

    public function testGetModule()
    {
        $model = new ViewModel();
        $model->setModule('Foo');
        $this->assertSame('Foo', $model->getModule());
    }

    public function testSetTemplate()
    {
        $model  = new ViewModel();
        $return = $model->setTemplate('foo');
        $this->assertSame($return, $model);
        $reflection = new ReflectionProperty($model, 'template');
        $reflection->setAccessible(true);
        $this->assertSame('foo', $reflection->getValue($model));
    }

    public function testGetTemplate()
    {
        $model = new ViewModel();
        $model->setTemplate('foo');
        $this->assertSame('foo', $model->getTemplate());
    }

    public function testSetGroupId()
    {
        $model  = new ViewModel();
        $return = $model->setGroupId('foo');
        $this->assertSame($return, $model);
        $reflection = new ReflectionProperty($model, 'groupId');
        $reflection->setAccessible(true);
        $this->assertSame('foo', $reflection->getValue($model));
    }

    public function testGetGroupId()
    {
        $model = new ViewModel();
        $model->setGroupId('foo');
        $this->assertSame('foo', $model->getGroupId());
    }

    public function testGetGroupIdReturnValueByDefault()
    {
        $model = new ViewModel();
        $this->assertSame('content', $model->getGroupId());
    }

    public function testAddChild()
    {
        $root   = new ViewModel();
        $child  = new ViewModel();
        $return = $root->addChild($child, 'foo');
        $this->assertSame($return, $root);
        $this->assertSame('foo', $child->getGroupId());
        $reflection = new ReflectionProperty($root, 'children');
        $reflection->setAccessible(true);
        $this->assertSame([$child], $reflection->getValue($root));
    }

    public function testGetIterator()
    {
        $root  = new ViewModel();
        $child = new ViewModel();
        $root->addChild($child, 'foo');
        $iterator = $root->getIterator();
        $this->assertInstanceOf(ArrayIterator::CLASS, $iterator);
        $children = $iterator->getArrayCopy();
        $this->assertSame([$child], $children);
    }
}
