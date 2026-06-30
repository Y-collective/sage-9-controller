<?php

declare(strict_types=1);

namespace Sober\Controller;

class Blade
{
    protected array $data;
    protected ?object $first;
    protected ?object $last;

    protected function setBladeData(array $data): static
    {
        $this->data = $data['__data']['__blade'] ?? [];

        if (count($this->data) > 1) {
            $this->first = reset($this->data);
            $this->last = end($this->data);

            if (!$this->last->tree && $this->first->class === 'App') {
                $this->data = [$this->first, $this->last];
            } elseif (!$this->last->tree) {
                $this->data = $this->last;
            }
        }

        return $this;
    }
}
