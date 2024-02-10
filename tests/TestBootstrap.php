<?php declare(strict_types=1);

use Shopware\Core\TestBootstrapper;

$loader = (new TestBootstrapper())
    ->addCallingPlugin()
    ->addActivePlugins('MuckiSearchPlugin')
    ->setDatabaseUrl('mysql://root:root@db:3306/db_test')
    ->setForceInstallPlugins(true)
    ->bootstrap()
    ->getClassLoader()
;

$loader->addPsr4('MuckiSearchPlugin\\tests\\', __DIR__);
