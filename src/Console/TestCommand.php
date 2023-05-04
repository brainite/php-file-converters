<?php
namespace FileConverter\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use FileConverter\FileConverter;

class TestCommand extends \Symfony\Component\Console\Command\Command {
  protected function configure() {
    $this->setName('test');
    $this->setDescription('Run all FileConverter tests');
    $this->setDefinition(array(
      new InputArgument('path_to_tests', InputArgument::OPTIONAL, 'The path of the test branch'),
      new InputOption('test', NULL, InputOption::VALUE_OPTIONAL, 'Specify a certain test group or specific test (rdf2pdf or rdf2pdf/001)'),
      new InputOption('converter', NULL, InputOption::VALUE_OPTIONAL, 'Specify converters (csv) to test using the key from _test.json (pandoc or pandoc,htmldoc)'),
    ));
  }

  protected function execute(InputInterface $input, OutputInterface $output, $mode = NULL) {
    $path_to_tests = $input->getArgument('path_to_tests');
    if (!isset($path_to_tests)) {
      $output->writeln("USAGE: fileconverter tests <path_to_tests>");
      return 0;
    }
    $root = realpath(getcwd() . '/' . $path_to_tests);
    if (!$root || !$path_to_tests || !is_dir($root)) {
      $output->writeln("Unable to locate tests.");
      return 1;
    }

    try {
      $tester = \FileConverter\FileConverterTests::factory($root);
      $tester->setTestFilter(trim($input->getOption('test', '')));
      $tester->setTestConverters(trim($input->getOption('converter', '')));
      $tester->doAllTests();
    } catch (\Exception $e) {
      $output->writeln($e->getMessage());
    }

    return 0;
  }

}