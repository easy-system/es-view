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

use Es\View\Exception\TemplateNotFoundException;
use RuntimeException;

/**
 * The template resolver.
 */
class Resolver
{
    /**
     * The prefix of paths to templates directory.
     *
     * @var string
     */
    const DEFAULT_PREFIX = 'view';

    /**
     * The cache of normalized module names.
     *
     * @var array
     */
    protected static $moduleNames = [];

    /**
     * The array with namespaces of modules as keys and paths to directory of
     * module templates as values.
     *
     * @var array
     */
    protected $modulesMap = [];

    /**
     * The array with template names as keys and paths to template files
     * as values.
     *
     * @var array
     */
    protected $templatesMap = [];

    /**
     * The last used module.
     *
     * @var null|string
     */
    protected $lastModule;

    /**
     * Gets the full template name.
     *
     * @param string $module            The module namespace
     * @param string $shortTemplateName The short template name
     *
     * @return string Returns the full template name
     */
    public static function getFullTemplateName($module, $shortTemplateName)
    {
        return static::normalizeModule($module) . '::' . ltrim($shortTemplateName, '/');
    }

    /**
     * Registers module path.
     *
     * @param string $moduleName The module name
     * @param string $path       The path to module directory
     * @param bool   $addPrefix  Optional; true by default. To add a prefix to
     *                           the directory path
     *
     * @return self
     */
    public function registerModulePath($moduleName, $path, $addPrefix = true)
    {
        $normalized = static::normalizePath($path) . PHP_DS;
        if ($addPrefix) {
            $normalized .= static::DEFAULT_PREFIX . PHP_DS;
        }
        $this->modulesMap[static::normalizeModule($moduleName)] = $normalized;

        return $this;
    }

    /**
     * Gets the modules map.
     *
     * @return array The modules map
     */
    public function getModulesMap()
    {
        return $this->modulesMap;
    }

    /**
     * Register the templates map.
     *
     * @param array $map The map of templates
     *
     * @return self
     */
    public function registerTemplatesMap(array $map)
    {
        $this->templatesMap = array_merge($this->templatesMap, $map);

        return $this;
    }

    /**
     * Register the template path.
     *
     * @param string $templateName The template name
     * @param string $path         The path to template
     *
     * @return self
     */
    public function registerTemplatePath($templateName, $path)
    {
        $this->templatesMap[(string) $templateName] = (string) $path;

        return $this;
    }

    /**
     * Gets templates map.
     *
     * @return array The map of templates
     */
    public function getTemplatesMap()
    {
        return $this->templatesMap;
    }

    /**
     * Is the template registered?
     *
     * @param string $name The template name
     *
     * @return bool Returns true on success, false otherwise
     */
    public function hasTemplate($name)
    {
        return isset($this->templatesMap[$name]);
    }

    /**
     * Resolves template.
     *
     * @param string $template The template name
     * @param string $module   Optional; the module namespace
     *
     * @throws \Es\View\Exception\TemplateNotFoundException If template not found
     * @throws RuntimeException                             If the registered file
     *                                                      of template not exists
     *
     * @return string Returns path to template file
     */
    public function resolve($template, $module = null)
    {
        if ($module) {
            $module = static::normalizeModule($module);
        }
        $pos = strpos($template, '::');
        if (false !== $pos) {
            if (! $module) {
                $module = substr($template, 0, $pos);
            }
            $template = substr($template, $pos + 2);
        }
        $template = ltrim($template, '/');
        if ($module) {
            $file = $this->resolveModule($template, $module);
            if (! $file) {
                throw new TemplateNotFoundException(sprintf(
                    'Failed to found template "%s" of module "%s".',
                    $template,
                    $module
                ));
            }
            $this->lastModule = $module;

            return $file;
        }
        if (isset($this->templatesMap[$template])) {
            $file = $this->normalizePath($this->templatesMap[$template]);
            if (! file_exists($file)) {
                throw new RuntimeException(sprintf(
                    'The file "%s" of template "%s" not exists.',
                    $file,
                    $template
                ));
            }

            return $file;
        }
        if ($this->lastModule) {
            $file = $this->resolveModule($template, $this->lastModule);
            if ($file) {
                return $file;
            }
        }

        throw new TemplateNotFoundException(sprintf(
            'Template "%s" not found.',
            $template
        ));
    }

    /**
     * Resolves the specified module.
     *
     * @param string $template The template
     * @param string $module   The normalized module name
     *
     * @throws \RuntimeException If the registered template file not exists
     *
     * @return string|false Returns path to file if it found or false otherwise
     */
    protected function resolveModule($template, $module)
    {
        $fullName = $module . '::' . $template;
        if (isset($this->templatesMap[$fullName])) {
            $file = $this->normalizePath($this->templatesMap[$fullName]);
            if (! file_exists($file)) {
                throw new RuntimeException(sprintf(
                    'The file "%s" of template "%s" not exists.',
                    $file,
                    $template
                ));
            }

            return $file;
        }

        return $this->findFile($module, $template);
    }

    /**
     * Finds file.
     *
     * @param string $module   The normalized module namespace
     * @param string $template The template name
     *
     * @return string|false Returns path to file if it found or false otherwise
     */
    protected function findFile($module, $template)
    {
        if (! isset($this->modulesMap[$module])) {
            return false;
        }
        $tpl   = str_replace('/', PHP_DS, $template);
        $found = glob($this->modulesMap[$module] . $tpl . '.*');
        if (! $found) {
            return false;
        }
        $file = $found[0];

        $this->templatesMap[$module . '::' . $template] = $file;

        return $file;
    }

    /**
     * Normalizes module name.
     *
     * @param string $module The module namespace
     *
     * @return string The normalized module name
     */
    public static function normalizeModule($module)
    {
        $module = (string) $module;

        if (isset(static::$moduleNames[$module])) {
            return static::$moduleNames[$module];
        }
        $normalized = trim(str_replace('\\', '/', $module), '/');

        $result = strtolower(
            preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $normalized)
        );
        static::$moduleNames[$module] = $result;

        return $result;
    }

    /**
     * Normalizes path.
     *
     * @param string $path The path
     *
     * @return string Returns the normalized path
     */
    protected static function normalizePath($path)
    {
        $normalized = str_replace(['\\', '/'], PHP_DS, (string) $path);

        return rtrim($normalized, PHP_DS);
    }
}
