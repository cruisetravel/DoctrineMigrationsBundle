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
 */

namespace Doctrine\Bundle\MigrationsBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\DoctrineCommandHelper;
use Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand;

/**
 * Command to view the status of a set of migrations.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class MigrationsStatusDoctrineCommand extends StatusCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('doctrine:migrations:status')
            ->addOption('em', null, InputOption::VALUE_OPTIONAL, 'The entity manager to use for this command.')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        DoctrineCommand::fetchParameters($this->getApplication()->getKernel()->getContainer(), $input);
        DoctrineCommandHelper::setApplicationEntityManager($this->getApplication(), $input->getOption('em'));

        $configuration = $this->getMigrationConfiguration($input, $output);
        DoctrineCommand::configureMigrations($this->getApplication()->getKernel()->getContainer(), $configuration);

        parent::execute($input, $output);
    }
}
