<?php

declare(strict_types=1);

namespace Sober\Controller;

use Sober\Controller\Utils;
use Sober\Controller\Module\Acf;

class Controller
{
    protected bool $active = true;
    protected string|false $template = false;
    protected bool $tree = false;
    protected bool|string|array $acf = false;
    protected \WP_Post|false $post = false;
    protected array $data = [];

    private \ReflectionClass $class;
    private array $methods;
    private array $dataMethods;
    private array $staticMethods;

    private array $incomingData;

    private ?Acf $classAcf = null;

    public function __before(): void
    {
    }

    public function __after(): void
    {
    }

    final public function __setParams(): void
    {
        $this->class = new \ReflectionClass($this);

        if (class_exists('Acf')) {
            $this->classAcf = new Acf();
        }

        if (!$this->template) {
            $this->template = Utils::convertToKebabCase($this->class->getShortName());
        }

        if ($this->class->implementsInterface('\Sober\Controller\Module\Tree')) {
            $this->tree = true;
        }
    }

    final public function __setData(array $incomingData): void
    {
        $this->incomingData = $incomingData;

        $this->__setDataFromPost();

        $this->__setDataFromModuleAcf();

        $this->__setDataFromFilter();

        $this->__setDataFromMethods();

        $this->__setBladeData();

        $this->__setAppData();

        $this->__setTreeData();
    }

    private function __setDataFromPost(): void
    {
        if ($this->template !== 'app') {
            return;
        }

        if (!is_singular()) {
            return;
        }

        $this->post = get_post();
        $this->data['post'] = $this->post;
    }

    private function __setDataFromModuleAcf(): void
    {
        if (!$this->acf) {
            return;
        }

        $this->classAcf->setData($this->acf);

        if ($this->template === 'app' && is_bool($this->acf)) {
            $this->classAcf->setDataOptionsPage();
        }

        $this->classAcf->setDataReturnFormat();

        if (!$this->classAcf->getData()) {
            return;
        }

        $this->data = array_merge($this->data, $this->classAcf->getData());
    }

    private function __setDataFromFilter(): void
    {
        if ($this->template === 'app') {
            $this->data = array_merge($this->data, $this->incomingData);
        }
    }

    private function __setDataFromMethods(): void
    {
        $this->methods = $this->class->getMethods(\ReflectionMethod::IS_PUBLIC);

        $this->methods = array_filter($this->methods, function (\ReflectionMethod $method): bool {
            return $method->class !== 'Sober\Controller\Controller'
                && $method->name !== '__construct'
                && $method->name !== '__before'
                && $method->name !== '__after';
        });

        $this->staticMethods = $this->class->getMethods(\ReflectionMethod::IS_STATIC);

        $this->dataMethods = array_diff($this->methods, $this->staticMethods);

        $this->dataMethods = array_filter($this->dataMethods, function ($method): bool {
            return (bool) $method->name;
        });

        foreach ($this->dataMethods as $method) {
            $var = Utils::convertToSnakeCase($method->name);
            $this->data[$var] = $this->{$method->name}();
        }
    }

    private function __setBladeData(): void
    {
        $debuggerData = $this->data;

        foreach ($this->dataMethods as $dataMethod) {
            $key = Utils::convertToSnakeCase($dataMethod->name);
            $returned = $debuggerData[$key];

            $debuggerData[$key] = (object) [
                'method' => $dataMethod,
                'returned' => $returned,
            ];
        }

        $debugger = (object) [
            'class' => $this->class->getShortName(),
            'tree' => $this->tree,
            'methods' => $this->staticMethods,
            'data' => $debuggerData,
        ];

        $this->incomingData['__blade'][] = $debugger;
        $this->data['__blade'] = $this->incomingData['__blade'];
    }

    private function __setAppData(): void
    {
        if ($this->template === 'app') {
            $this->data['__app'] = $this->data;
            return;
        }

        $this->data['__app'] = $this->incomingData['__app'] ?? [];
        $this->data = array_merge($this->data['__app'], $this->data);
    }

    private function __setTreeData(): void
    {
        if ($this->tree) {
            $this->data = array_merge($this->incomingData['__store'] ?? [], $this->data);
        }

        $this->data['__store'] = array_merge($this->incomingData, $this->data);
    }

    final public function __getTemplateParam(): string|false
    {
        return $this->active ? $this->template : false;
    }

    final public function __getData(): array
    {
        return $this->active ? $this->data : [];
    }
}
