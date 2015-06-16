<?php

namespace Rootcanal\Console;

use Rootcanal\Config\Config;
use Rootcanal\Config\Finder;
use Rootcanal\Mapper;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Composer\Factory;

/**
 * Single command, responsible for running the application
 */
class Command extends BaseCommand
{
    /*
     * @var Composer
     */
    private $composer = null;

    protected function configure()
    {
        $this
            ->setName('drupal:canal')
            ->setDefinition(array(
                new InputOption(
                    'source',
                    's',
                    InputOption::VALUE_NONE,
                    'Path to source of the custom files and directories'
                ),
                new InputOption(
                    'destination',
                    'd',
                    InputOption::VALUE_REQUIRED,
                    'Path to destination of the project',
                    'www'
                ),
                new InputOption(
                    'production',
                    'p',
                    InputOption::VALUE_NONE,
                    'Generate production artifact from source'
                )
            ))
            ->setDescription('Build a canal between composer and a working Drupal Application')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command specifications:

  <info>php %command.full_name%</info>

Will run generate a drupal root directory inside 'www' using your composer installation and custom files and directories that are in your project's root.

You can override the default name of the destination path with:

  <info>php %command.full_name% --destination=docroot</info>

You can override the default source path of your custom directories and files with:

  <info>php %command.full_name% --source=my_custom_dir</info>

By default, modules, themes, and custom directories will be symlinked into a Drupal root.
You can instead copy all files and directories with:

  <info>php %command.full_name% --production</info>
EOF
        )
            ;
    }

    /**
     * @param  bool $required
     * @param  bool $disablePlugins
     * @throws JsonValidationException
     * @return \Composer\Composer
     */
    public function getComposer($required = true, $disablePlugins = false)
    {
        if (null === $this->composer) {
            $io = $this->getApplication()->getIO();
            try {
                $this->composer = Factory::create($io, null, $disablePlugins);
            } catch (\InvalidArgumentException $e) {
                if ($required) {
                    $io->write($e->getMessage());
                    exit(1);
                }
            } catch (JsonValidationException $e) {
                $errors = ' - ' . implode(PHP_EOL . ' - ', $e->getErrors());
                $message = $e->getMessage() . ':' . PHP_EOL . $errors;
                throw new JsonValidationException($message);
            }
        }

        return $this->composer;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = new Config(
            $input->getOption('production'),
            $input->getOption('destination'),
            '/sites/all/modules/%s',
            '/sites/all/themes/%s',
            '/sites/all/drush/%s',
            '/profiles/%s',
            '/sites/default/files',
            '/sites/default/files-private',
            '/sites/default/settings.php'
        );

        $finder = new Finder(
             $input->getOption('source'),
           # 'fixture',
            $input->getOption('destination'),
            'cnf/files',
            'cnf/private',
            'cnf/settings.php'
        );

        $im = $this->getComposer()->getInstallationManager();
        $rm = $this->getComposer()->getRepositoryManager();

        $mapper = new Mapper($config, $finder, $im, $rm);
       # print_r($mapper->mapCustomFiles());
        print_r($mapper->getMap());
        #print_r($mapper->mapCustomByType('module'));

    }
}