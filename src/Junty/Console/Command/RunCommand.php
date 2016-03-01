<?php
/**
 * Junty
 *
 * @author Gabriel Jacinto aka. GabrielJMJ <gamjj74@hotmail.com>
 * @license MIT License
 */
 
namespace Junty\Console\Command;

use Junty\Runner\RunnerInterface;
use Junty\{Task};
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command
{
    private $runner;

    public function __construct(RunnerInterface $runner, $name = null)
    {
        parent::__construct($name);

        $this->runner = $runner;
    }

    protected function configure()
    {
        $this->setName('run')
            ->setDescription('Run tasks')
            ->addArgument(
                'task',
                InputArgument::OPTIONAL
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasArgument('task') && $task = $input->getArgument('task') !== null) {
            $output->writeln('Executing task: ' . $task = $input->getArgument('task'));

            $this->runner->runTask($task);
        } else {
            $tasks = $this->runner->getTasks();
            $output->writeln('Executing tasks');
            
            foreach ($tasks as $task) {
                try {
                    $output->writeln('Executing task \'' . $task->getName() . '\'');
                    $this->runner->runTask($task);
                } catch (\Exception $e) {
                    $output->writeln('Error on task \'' . $task->getName() . '\': ' . $e->getMessage());
                }
            }
        }

        $time = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 100) / 100;
        $output->writeln('Finished! Time: ' . $time . 'ms');
    }
}