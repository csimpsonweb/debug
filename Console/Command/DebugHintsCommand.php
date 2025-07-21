<?php
namespace Networld\Debug\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI command placeholder for debugging or listing developer hints.
 * You can register this with bin/magento and extend it to output block names, template hints, etc.
 *
 * Example usage after implementation:
 * bin/magento networld:debug:hints
 */
class DebugHintsCommand extends Command
{
    protected function configure()
    {
        $this->setName('networld:debug:hints')
            ->setDescription('Outputs debug hints or layout references');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Debug hints placeholder command executed.</info>');
        return Command::SUCCESS;
    }
}
