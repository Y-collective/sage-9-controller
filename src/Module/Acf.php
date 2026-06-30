<?php

declare(strict_types=1);

namespace Sober\Controller\Module;

use Sober\Controller\Utils;

class Acf
{
    protected array $data = [];

    private bool $returnArrayFormat = false;

    public function __construct()
    {
        $this->setReturnFilter();
    }

    private function setReturnFilter(): void
    {
        $this->returnArrayFormat = has_filter('sober/controller/acf/array')
            ? (bool) apply_filters('sober/controller/acf/array', $this->returnArrayFormat)
            : false;
    }

    private function recursiveSnakeCase(array &$data): void
    {
        foreach ($data as $key => $val) {
            $snakeKey = Utils::convertKebabCaseToSnakeCase($key);

            if ($snakeKey !== $key) {
                $data[$snakeKey] = $val;
            }

            if (is_array($val)) {
                $this->recursiveSnakeCase($val);
            }
        }
    }

    public function setDataReturnFormat(): void
    {
        if ($this->returnArrayFormat) {
            return;
        }

        foreach ($this->data as $key => $item) {
            if (is_array($item) || is_object($item)) {
                $this->data[$key] = json_decode(json_encode($item), false);
            }
        }
    }

    public function setDataOptionsPage(): void
    {
        if (!function_exists('acf_add_options_page')) {
            return;
        }

        $fields = get_fields('options');

        if (!empty($fields)) {
            $this->data['acf_options'] = $fields;
        }
    }

    public function setData(bool|string|array $acf): void
    {
        $query = get_queried_object();

        if (!acf_get_valid_post_id($query)) {
            return;
        }

        if (is_bool($acf) && $acf) {
            $fields = get_fields($query);
            if (is_array($fields)) {
                $this->data = $fields;
            }
        }

        if (is_string($acf)) {
            $value = get_field($acf, $query);
            if ($value !== null) {
                $this->data[$acf] = $value;
            }
        }

        if (is_array($acf)) {
            foreach ($acf as $item) {
                $value = get_field($item, $query);
                if ($value !== null) {
                    $this->data[$item] = $value;
                }
            }
        }

        $this->recursiveSnakeCase($this->data);
    }

    public function getData(): array
    {
        return $this->data;
    }
}
