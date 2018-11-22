<?php
/**
 * Created by PhpStorm.
 * User: zvekete
 * Date: 24.2.2016.
 * Time: 13.58
 */

namespace App\Modules;

use App\Exceptions\TemplateException;

class Template
{
    /** @var callable[] */
    private $sections = [];

    /** @var array */
    private $values = [];

    /** @var string */
    private $view;

    /** @var string[] */
    private $parents = [];

    /**
     * @param string $section
     * @param callable $content
     * @return Template|$this
     */
    public function setSectionContent($section, callable $content)
    {
        $this->sections[$section][] = $content;

        return $this;
    }

    /**
     * @param string $path
     * @return Template|$this
     * @throws TemplateException
     */
    public function setParent($path)
    {
        $path = __DIR__ . "/../../src/{$path}";

        if (false === file_exists($path)) {
            throw new TemplateException("Template '{$path}' not found");
        }

        $this->parents[] = $path;

        return $this;
    }

    /**
     * @param array $values
     * @return Template|$this
     */
    public function setValues(array $values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @param string $section
     * @return Template|$this|null
     * @throws TemplateException
     */
    public function showSection($section)
    {
        if (empty($this->sections[$section])) {
            return null;
        }

        foreach ($this->sections[$section] as $section) {
            $reflection = new \ReflectionFunction($section);

            $values = $this->getSectionParameterValues($reflection);

            $reflection->invokeArgs($values);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param $path
     * @return $this
     * @throws TemplateException
     */
    public function make($path)
    {
        $path = __DIR__ . "/../../src/{$path}";

        if (false === file_exists($path)) {
            throw new TemplateException("Template '{$path}' not found");
        }

        $this->view = $path;

        return $this;
    }

    /**
     * @return bool
     */
    public function canRender()
    {
        return false === empty($this->view);
    }

    /**
     *
     * Renders all
     *
     * @throws TemplateException
     */
    public function render()
    {
        if (empty($this->view)) {
            throw new TemplateException("There is no view to render.");
        }

        $this->values['template'] = $this;

        extract($this->values);

        require_once $this->view;

        foreach ($this->parents as $parent) {
            require_once $parent;
        }
    }

    /**
     * @param \ReflectionFunction $function
     * @return array
     * @throws TemplateException
     */
    private function getSectionParameterValues(\ReflectionFunction $function)
    {
        $values = [];

        foreach ($function->getParameters() as $parameter) {
            if (false === $parameter->isOptional() && empty($this->values[$parameter->getName()])) {
                throw new TemplateException("Template value '{$parameter->getName()}'' is required.");
            }

            $values[] =  empty($this->values[$parameter->getName()]) ? null : $this->values[$parameter->getName()];
        }

        return $values;
    }
}