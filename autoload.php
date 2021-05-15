<?php

use Composer\Autoload\ClassLoader;

$loader = new ClassLoader();
$loader->addPsr4('HuHwt\\WebtreesMods\\InteractiveTreeXT\\', __DIR__ );
$loader->addPsr4('HuHwt\\WebtreesMods\\InteractiveTreeXT\\', __DIR__ . '/resources');
$loader->addPsr4('HuHwt\\WebtreesMods\\InteractiveTreeXT\\modules\\', __DIR__ . "/views/modules");
$loader->addPsr4('HuHwt\\WebtreesMods\\InteractiveTreeXT\\Exceptions\\', __DIR__ . "/Exceptions");
$loader->addPsr4('HuHwt\\WebtreesMods\\InteractiveTreeXT\\Traits\\', __DIR__ . "/Traits");
$loader->addPsr4('HuHwt\\WebtreesMods\\InteractiveTreeXT\\Module\\', __DIR__ . "/Module/InteractiveTree");

$loader->register();
