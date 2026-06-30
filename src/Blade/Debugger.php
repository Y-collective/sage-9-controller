<?php

declare(strict_types=1);

namespace Sober\Controller\Blade;

use Sober\Controller\Blade;

class Debugger extends Blade
{
    private object $controller;
    private array $controllerDataLog = [];

    public function __construct(array $data)
    {
        $this->setBladeData($data);
        $this->render();
    }

    private function render(): void
    {
        echo <<<HTML
<style>
.debugger small {
    border: 1px solid rgba(0,0,0,0.2);
    opacity: 0.5;
    padding: 2px 5px;
    margin-left: 5px;
    border-radius: 2px;
}
</style>
<pre class="debugger"><strong>@debug</strong><br>
HTML;

        foreach ($this->data as $controller) {
            $this->controller = $controller;

            echo $controller->class;

            if ($controller->tree) {
                echo '<small>extends tree</small>';
            }

            echo '<ul>';

            if ($controller->data) {
                $this->renderData();
            }

            if ($controller->methods) {
                $this->renderMethods();
            }

            echo '</ul>';
        }

        echo '</pre>';
    }

    private function renderData(): void
    {
        echo '<li>Data<ul>';

        foreach ($this->controller->data as $name => $data) {
            $override = false;

            $key = array_search($name, array_column($this->controllerDataLog, 'name'));

            if ($key !== false) {
                $override = $this->controllerDataLog[$key]['class'];
            }

            $this->controllerDataLog[] = [
                'name' => $name,
                'class' => $this->controller->class,
            ];

            $dataType = isset($data->method) ? gettype($data->returned) : gettype($data);

            echo "<li>{$name}";

            if ($override) {
                echo "<small>overrides {$override}</small>";
            }

            echo "<small>{$dataType}</small>";

            if (isset($data->method)) {
                echo '<small>line ' . $data->method->getStartLine() . '&mdash;' . $data->method->getEndLine() . '</small>';
            }

            echo '</li>';
        }

        echo '</ul></li>';
    }

    private function renderMethods(): void
    {
        echo '<li>Methods<ul>';

        foreach ($this->controller->methods as $method) {
            echo '<li>' . $method->name . '<small>line ' . $method->getStartLine() . '&mdash;' . $method->getEndLine() . '</small></li>';
        }

        echo '</ul></li>';
    }
}
