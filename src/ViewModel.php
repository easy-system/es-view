<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\View;

use ArrayIterator;
use Es\Container\AbstractContainer;
use Es\Container\ArrayAccess\ArrayAccessTrait;
use Es\Container\Property\PropertyTrait;
use Es\Mvc\ViewModelInterface;
use InvalidArgumentException;
use stdClass;
use Traversable;

/**
 * The view model.
 */
class ViewModel extends AbstractContainer implements ViewModelInterface
{
    use ArrayAccessTrait, PropertyTrait;

    /**
     * The child models.
     *
     * @var array
     */
    protected $children = [];

    /**
     * The type of content.
     *
     * @var string
     */
    protected $contentType = 'text/html';

    /**
     * The group identifier.
     *
     * @var string
     */
    protected $groupId = 'content';

    /**
     * The name of module.
     *
     * Typically, the model belongs to the same Module, whose controller
     * processes the request.
     *
     * @var null|string
     */
    protected $moduleName;

    /**
     * The template name.
     *
     * @var null|string
     */
    protected $template;

    /**
     * Constructor.
     *
     * @param null|array|\stdClass|\Traversable $variables Optional; the variables
     * @param null|string                       $template  Optional; the template
     * @param null|string                       $module    Optional; the module
     */
    public function __construct($variables = null, $template = null, $module = null)
    {
        if (null !== $variables) {
            $this->addVariables($variables);
        }
        if (null !== $template) {
            $this->setTemplate($template);
        }
        if (null !== $module) {
            $this->setModule($module);
        }
    }

    /**
     * Sets the content type.
     *
     * @param string $type The type of content
     *
     * @return self
     */
    public function setContentType($type)
    {
        $this->contentType = (string) $type;

        return $this;
    }

    /**
     * Gets the content type.
     *
     * @return string The type of content
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Sets the variables.
     *
     * @param array|\stdClass|\Traversable $variables The variables
     *
     * @return self
     */
    public function setVariables($variables)
    {
        $this->container = [];
        $this->addVariables($variables);

        return $this;
    }

    /**
     * Adds the variables.
     *
     * @param array|\stdClass|\Traversable $variables The variables
     *
     * @throws \InvalidArgumentException If invalid variables type provided
     *
     * @return self
     */
    public function addVariables($variables)
    {
        if ($variables instanceof stdClass) {
            $variables = (array) $variables;
        } elseif ($variables instanceof Traversable) {
            $variables = iterator_to_array($variables);
        } elseif (! is_array($variables)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid variables provided; must be an array or Traversable '
                . 'or instance of stdClass, "%s" received.',
                is_object($variables) ? get_class($variables) : gettype($variables)
            ));
        }
        $this->container = array_merge($this->container, $variables);

        return $this;
    }

    /**
     * Sets the variable.
     *
     * @param string $name  The variable name
     * @param mixed  $value The value of variable
     *
     * @return self
     */
    public function setVariable($name, $value)
    {
        $this->container[(string) $name] = $value;

        return $this;
    }

    /**
     * Gets the variables.
     *
     * @return array The variables
     */
    public function getVariables()
    {
        return $this->container;
    }

    /**
     * Gets the variable.
     *
     * @param string $name    The variable name
     * @param mixed  $default The default value of variable
     *
     * @return mixed Returns the value of variable if any, $default otherwise
     */
    public function getVariable($name, $default = null)
    {
        if (isset($this->container[$name])) {
            return $this->container[$name];
        }

        return $default;
    }

    /**
     * Sets the module.
     *
     * @param string $moduleName The module name
     *
     * @return self
     */
    public function setModule($moduleName)
    {
        $this->moduleName = (string) $moduleName;

        return $this;
    }

    /**
     * Gets the module.
     *
     * @return null|string Returns the module name if any, null otherwise
     */
    public function getModule()
    {
        return $this->moduleName;
    }

    /**
     * Sets the template.
     *
     * @param string $template The template
     *
     * @return self
     */
    public function setTemplate($template)
    {
        $this->template = (string) $template;

        return $this;
    }

    /**
     * Gets the template.
     *
     * @return null|string Returns template if any, null otherwise
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Sets the group identifier.
     *
     * @param string $id The group identifier
     *
     * @return self
     */
    public function setGroupId($id)
    {
        $this->groupId = (string) $id;

        return $this;
    }

    /**
     * Gets the group identifier.
     *
     * @return string The group identifier
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * Adds the child model.
     *
     * @param \Es\Mvc\ViewModelInterface $child   The child model
     * @param string                     $groupId Optional; the group identifier
     *
     * @return self
     */
    public function addChild(ViewModelInterface $child, $groupId = null)
    {
        $this->children[] = $child;
        if (! is_null($groupId)) {
            $child->setGroupId($groupId);
        }

        return $this;
    }

    /**
     * Gets the iterator.
     *
     * @return \ArrayIterator The iterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->children);
    }
}
