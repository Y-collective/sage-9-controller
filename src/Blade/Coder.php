<?php

declare(strict_types=1);

namespace Sober\Controller\Blade;

use Sober\Controller\Blade;
use Sober\Controller\Utils;

class Coder extends Blade
{
    private string $code = '';
    private string $indentation = '';
    private array $includes = [];
    private bool $codeif;

    public function __construct(array $data, string|array $includes = '', bool $codeif = false)
    {
        $this->codeif = $codeif;

        $this->setIncludeData($includes);
        $this->setBladeData($data);
        $this->render();
    }

    private function setIncludeData(string|array $includes): void
    {
        $this->includes = is_string($includes) ? [$includes] : $includes;
    }

    private function increaseIndentation(): void
    {
        $this->indentation .= '  ';
    }

    private function decreaseIndentation(): void
    {
        $this->indentation = substr($this->indentation, 0, -2);
    }

    private function render(): void
    {
        $this->data = array_map(function ($item) {
            return $item->data;
        }, $this->data);

        if (empty($this->data)) {
            return;
        }

        $this->data = array_merge(...$this->data);

        unset($this->data['post']);

        $type = $this->codeif ? '@codeif' : '@code';
        echo '<pre class="coder"><strong>' . $type . '</strong><br>';

        foreach ($this->data as $name => $value) {
            $value = (isset($value->method) ? $value->returned : $value);

            if (!empty($this->includes) && in_array($name, $this->includes, true)) {
                $this->router($name, $value);
            }

            if (empty($this->includes)) {
                $this->router($name, $value);
            }
        }

        echo '</pre>';
    }

    private function router(string $name, mixed $val): void
    {
        if (is_object($val)) {
            $this->renderObj($name, $val);
        }

        if (is_array($val) && Utils::isArrayIndexed($val)) {
            $this->renderArrIndexed($name, $val);
        }

        if (is_array($val) && !Utils::isArrayIndexed($val)) {
            $this->renderArrKeys($name, $val);
        }

        if (!is_array($val) && !is_object($val)) {
            $this->renderResult($name, $val);
            $this->code = '';
        }
    }

    private function renderObj(string $name, object $val): void
    {
        $props = get_object_vars($val);

        foreach ($props as $prop_name => $prop_val) {
            $trail = $this->code;
            $this->code = $trail . $name . '->';
            $this->router($prop_name, $prop_val);
            $this->code = $trail;
        }
    }

    private function renderArrIndexed(string $name, array $val): void
    {
        if ($this->codeif) {
            echo $this->indentation . '@if ($' . $this->code . $name . ')<br>';
            $this->increaseIndentation();
        }

        echo $this->indentation . '@foreach ($' . $this->code . $name . ' as $item)<br>';

        $this->code = '';
        $this->increaseIndentation();

        foreach ($val as $key_index => $key_val) {
            if (count($val) > 1) {
                echo $this->indentation . '<strong>[' . $key_index . ']</strong><br>';
            }
            $this->router('item', $key_val);
        }

        $this->decreaseIndentation();
        echo $this->indentation . '@endforeach<br>';

        if ($this->codeif) {
            $this->decreaseIndentation();
            echo $this->indentation . '@endif<br>';
        }
    }

    private function renderArrKeys(string $name, array $val): void
    {
        foreach ($val as $key_name => $key_val) {
            $this->router($this->code . $name . "['{$key_name}']", $key_val);
        }
    }

    private function renderResult(string $name, mixed $val): void
    {
        $this->code = '$' . $this->code . $name;

        if ($this->codeif) {
            echo $this->indentation . '@if (' . $this->code . ')<br>';
            $this->increaseIndentation();
        }

        if (Utils::doesStringContainMarkup((string) $val)) {
            $this->code = '{!! ' . $this->code . ' !!}';
        } else {
            $this->code = '{{ ' . $this->code . ' }}';
        }

        echo $this->indentation . $this->code . '<br>';

        if ($this->codeif) {
            $this->decreaseIndentation();
            echo $this->indentation . '@endif<br>';
        }
    }
}
