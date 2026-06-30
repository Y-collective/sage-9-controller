<?php

declare(strict_types=1);

namespace Sober\Controller;

use Brain\Hierarchy\Hierarchy;

function sage(): string|false
{
    $sage = apply_filters('sober/controller/sage/namespace', 'App') . '\sage';

    return function_exists($sage) ? $sage : false;
}

function loader(): void
{
    $sage = sage();

    if (!$sage) {
        return;
    }

    $hierarchy = new Hierarchy();
    $loader = new Loader($hierarchy);
    $container = $sage();

    foreach ($loader->getClassesToRun() as $class) {
        $controller = $container->make($class);
        $controller->__setParams();

        $location = 'sage/template/' . $controller->__getTemplateParam() . '-data/data';

        add_filter($location, function ($data) use ($container, $class) {
            $controller = $container->make($class);
            $controller->__setParams();
            $controller->__before();
            $controller->__setData($data);
            $controller->__after();

            return $controller->__getData();
        }, 10, 2);
    }
}

function blade(): void
{
    $sage = sage();

    if (!$sage) {
        return;
    }

    $sage('blade')->compiler()->directive('debug', function () {
        return '<?php (new \Sober\Controller\Blade\Debugger(get_defined_vars())); ?>';
    });

    $sage('blade')->compiler()->directive('dump', function ($param) {
        return "<?php (new Illuminate\Support\Debug\Dumper)->dump({$param}); ?>";
    });

    $sage('blade')->compiler()->directive('code', function ($param) {
        $param = $param ?: 'false';
        return "<?php (new \Sober\Controller\Blade\Coder(get_defined_vars(), {$param})); ?>";
    });

    $sage('blade')->compiler()->directive('codeif', function ($param) {
        $param = $param ?: 'false';
        return "<?php (new \Sober\Controller\Blade\Coder(get_defined_vars(), {$param}, true)); ?>";
    });
}

if (function_exists('add_action')) {
    add_action('init', __NAMESPACE__ . '\loader');
    add_action('init', __NAMESPACE__ . '\blade');
}
