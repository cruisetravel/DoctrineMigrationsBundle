<?php

/*
 * This file is part of the Doctrine MigrationsBundle
 *
 * The code was originally distributed inside the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 * (c) Doctrine Project, Benjamin Eberlei <kontakt@beberlei.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * NOTE: Copied from Github. This file is in a PR and not yet part of the master branch
 *       We need this one, because the current master version has a bug in passing
 *       the --configuration option (gets overwritten)
 */

namespace Doctrine\Bundle\MigrationsBundle\Command;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand as BaseCommand;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Base class for Doctrine console commands to extend from.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class DoctrineCommand extends BaseCommand
{
    public static function configureMigrations(ContainerInterface $container, Configuration $configuration)
    {
        if (is_null($configuration->getMigrationsNamespace())) {
            $configuration->setMigrationsNamespace($container->getParameter('doctrine_migrations.namespace'));
        }

        $dir = $configuration->getMigrationsDirectory();

        if (is_null($dir)) {
            $dir = $container->getParameter('doctrine_migrations.dir_name');

            $configuration->setMigrationsDirectory($dir);
            $configuration->registerMigrationsFromDirectory($dir);
        }

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        if (is_null($configuration->getName())) {
            $configuration->setName($container->getParameter('doctrine_migrations.name'));
        }

        if ($configuration->getMigrationsTableName() == 'doctrine_migration_versions')
        {
            $configuration->setMigrationsTableName($container->getParameter('doctrine_migrations.table_name'));
        }

        self::injectContainerToMigrations($container, $configuration->getMigrations());
    }

    /**
     * Injects the container to migrations aware of it
     */
    private static function injectContainerToMigrations(ContainerInterface $container, array $versions)
    {
        foreach ($versions as $version) {
            $migration = $version->getMigration();
            if ($migration instanceof ContainerAwareInterface) {
                $migration->setContainer($container);
            }
        }
    }

    public static function fetchParameters(ContainerInterface $container, InputInterface $input)
    {
        $dir = $input->getOption('configuration');
        if (!empty($dir)) {
            $settings = $container->getParameter('doctrine_migrations');
            $configurations = array();
            if (array_key_exists('configs', $settings)) {
                $configurations = $settings['configs'];
                if (array_key_exists($dir, $configurations)) {
                    $configuration = $configurations[$dir];
                    if (array_key_exists('file', $configuration)) {
                        $input->setOption('configuration', $configuration['file']);
                    }
                    if (array_key_exists('em', $configuration)) {
                        $input->setOption('em', $configuration['em']);
                    }
                }
            }
        }
    }
}

