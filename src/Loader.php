<?php

declare(strict_types=1);

namespace Sober\Controller;

use Sober\Controller\Utils;
use Brain\Hierarchy\Hierarchy;

class Loader
{
    private Hierarchy $hierarchy;

    private string $namespace;
    private string $path;

    private \RecursiveIteratorIterator $listOfFiles;
    private array $classesToRun = [];

    public function __construct(Hierarchy $hierarchy)
    {
        $this->hierarchy = $hierarchy;

        $this->setNamespace();
        $this->setPath();

        if (!file_exists($this->path)) {
            return;
        }

        $this->setListOfFiles();
        $this->setClassesToRun();
        $this->setClassesAlias();
        $this->addBodyDataClasses();
    }

    protected function setNamespace(): void
    {
        $this->namespace = has_filter('sober/controller/namespace')
            ? apply_filters('sober/controller/namespace', rtrim($this->namespace))
            : 'App\Controllers';
    }

    protected function setPath(): void
    {
        $className = $this->namespace . '\\App';

        if (!class_exists($className)) {
            $this->path = '';
            return;
        }

        $reflection = new \ReflectionClass($className);
        $this->path = dirname($reflection->getFileName());
    }

    protected function setListOfFiles(): void
    {
        $this->listOfFiles = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->path)
        );
    }

    protected function setClassesToRun(): void
    {
        foreach ($this->listOfFiles as $filename => $file) {
            if (!Utils::isFilePhp($filename)) {
                continue;
            }

            if (!Utils::doesFileContain($filename, 'extends Controller')) {
                continue;
            }

            $this->classesToRun[] = $this->namespace . '\\' . pathinfo($filename, PATHINFO_FILENAME);
        }
    }

    public function setClassesAlias(): void
    {
        foreach ($this->classesToRun as $class) {
            if (!class_exists($class)) {
                continue;
            }

            $shortName = (new \ReflectionClass($class))->getShortName();

            if (class_exists($shortName)) {
                continue;
            }

            class_alias($class, $shortName);
        }
    }

    protected function addBodyDataClasses(): void
    {
        add_filter('body_class', function (array $body): array {
            global $wp_query;

            $templates = $this->hierarchy->getTemplates($wp_query);
            $templates = array_reverse($templates);

            $classes = ['app-data'];

            foreach ($templates as $template) {
                if (str_contains($template, '.blade.php') || $template === 'index.php') {
                    continue;
                }

                if ($template === 'index') {
                    $template = 'index.php';
                }

                $classes[] = basename(str_replace('.php', '-data', $template));
            }

            return array_merge($body, $classes);
        });
    }

    public function getClassesToRun(): array
    {
        return $this->classesToRun;
    }
}
