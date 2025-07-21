<?php
namespace Networld\Debug\Console\Command;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugHintsCommand extends Command
{
    const ARG_STATE = 'state';

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * Constructor
     *
     * @param WriterInterface $configWriter
     * @param TypeListInterface $cacheTypeList
     * @param string|null $name
     */
    public function __construct(
        WriterInterface $configWriter,
        TypeListInterface $cacheTypeList,
        string $name = null
    ) {
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
        parent::__construct($name);
    }

    /**
     * Configure the CLI command.
     */
    protected function configure()
    {
        $this->setName('networld:debug:hints')
            ->setDescription('Enable or disable Networld Debug Hints')
            ->addArgument(
                self::ARG_STATE,
                InputArgument::REQUIRED,
                'State to set: enable or disable'
            );
        parent::configure();
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $state = strtolower($input->getArgument(self::ARG_STATE));
        if (!in_array($state, ['enable', 'disable'])) {
            $output->writeln('<error>Invalid state. Use "enable" or "disable".</error>');
            return Cli::RETURN_FAILURE;
        }

        $value = ($state === 'enable') ? 1 : 0;

        try {
            $this->configWriter->save('networld_debug/general/enabled', $value);
            $this->cacheTypeList->cleanType('config');
            $output->writeln("<info>Networld Debug Hints have been " . ($value ? "enabled" : "disabled") . ".</info>");
            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error updating configuration: ' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }
    }
}