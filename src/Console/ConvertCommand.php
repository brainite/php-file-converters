<?php
namespace FileConverter\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use FileConverter\FileConverter;

class ConvertCommand extends \Symfony\Component\Console\Command\Command {
  protected function configure() {
    $this->setName('convert');
    $this->setDescription('Convert a file.');
    $this->setDefinition(array(
      new InputArgument('source', InputArgument::OPTIONAL, 'The path of the file to convert'),
      new InputArgument('destination', InputArgument::OPTIONAL, 'The destination path for the converted file'),
      new InputOption('optimize', NULL, InputOption::VALUE_NONE, 'Optimize the destination file'),
      new InputOption('allow-remote', NULL, InputOption::VALUE_NONE, 'Allow remote paths (e.g., http://)'),
      new InputOption('engines', NULL, InputOption::VALUE_OPTIONAL, 'JSON Object: Force a specific conversion engine'),
      new InputOption('conversion', NULL, InputOption::VALUE_OPTIONAL, 'Force a specific file type conversion (ignore extensions)'),
      new InputOption('replace-string', NULL, InputOption::VALUE_OPTIONAL, 'ON Object: Configure text replacements'),
    ));

    // Build help.
    $examples = array(
      'Simple jpg->png conversion' => 'fileconverter source.jpg destination.png',
      'HTML to PDF using Xhtml2Pdf' => 'fileconverter --engines=\'{"html->pdf":"xhtml2pdf:default"}\' source.html destination.pdf',
      'Force specific conversion engines' => '--engines={"html->pdf":"xhtml2pdf:default"}',
      'Force specific conversion regardless of extensions' => '--conversion=html:pdf',
      'Replace text' => '{"find":"replace","find2":"replace2"}',
    );
    $help = '';
    foreach ($examples as $a => $b) {
      $help .= "$a\n   $b\n";
    }
    $this->setHelp($help);
  }

  protected function execute(InputInterface $input, OutputInterface $output, $mode = NULL) {
    // Require the arguments.
    $source = $input->getArgument('source', NULL);
    $destination = $input->getArgument('destination', '');
    $optimize = (bool) $input->getOption('optimize', FALSE);
    if (!isset($source) || !isset($destination) || $destination === '') {
      if ($optimize === TRUE && isset($source)) {
        $destination = $source;
      }
      else {
        $output->writeln("USAGE: fileconverter 'source.ext1' 'destination.ext2'");
        return 0;
      }
    }

    // Normalize the file paths.
    $fc = FileConverter::factory();
    $stdin = $stdout = FALSE;
    $force_conversion = trim($input->getOption('conversion', ''));
    $is_remote = FALSE;
    if (preg_match('@^(.*):(.*)$@', $force_conversion, $arr)) {
      $force_conversion = array(
        $arr[1],
        $arr[2],
      );
    }
    else {
      $force_conversion = NULL;
    }
    if ($source === '-') {
      if (!isset($force_conversion)) {
        $output->writeln("Error: When using stdin, you must explicitly define conversion.");
        $output->writeln("Usage: fileconverter - output.dat --conversion=misc:dat");
        return 1;
      }
      $stdin = TRUE;
      $source = $fc->getEngine(NULL, NULL)->getTempFile($force_conversion[0]);
      $source_fp = fopen($source, 'w');
      while (!feof(STDIN)) {
        fputs($source_fp, fgets(STDIN));
      }
      fclose($source_fp);
    }
    else {
      if (!is_file($source)) {
        if (is_file(getcwd() . '/' . $source)) {
          $source = getcwd() . '/' . $source;
        }
      }
      if (!is_file($source)) {
        if ($input->getOption('allow-remote', FALSE)
          && strpos($source, '://') !== FALSE) {
          $is_remote = TRUE;
        }
        else {
          $output->writeln("Error: Unable to locate source file: $source");
          return 1;
        }
      }
    }
    if ($destination{0} !== '/') {
      if ($destination === '-') {
        if (!isset($force_conversion)) {
          $output->writeln("Error: When using stdout, you must explicitly define conversion.");
          $output->writeln("Usage: fileconverter input.dat - --conversion=misc:dat");
          return 1;
        }
        $stdout = TRUE;
        $destination = $fc->getEngine(NULL, NULL)->getTempFile($force_conversion[1]);
      }
      else {
        $destination = getcwd() . '/' . $destination;
      }
    }

    // Create the file converter and apply any cli options.
    $replace = $input->getOption('replace-string', FALSE);
    if ($replace) {
      $dat = json_decode($replace, TRUE);
      if (is_array($dat)) {
        $fc->setReplacements($dat, 'string');
      }
    }

    // Convert the file.
    $fc = FileConverter::factory();
    if ($is_remote || realpath($source) !== realpath($destination)) {
      $convert_path = isset($force_conversion) ? join('->', $force_conversion)
        : NULL;
      try {
        $fc->convertFile($is_remote ? $source : realpath($source), $destination, $convert_path);
      } catch (\Exception $e) {
        $output->writeln($e->getMessage());
        return 1;
      }
    }

    // Further commands can work on a single file, so use a default destination.
    if (!isset($destination)) {
      $destination = $source;
    }

    // Optimize the file.
    if ($optimize === TRUE) {
      $fc->optimizeFile($destination, $destination);
    }

    // Provide some verbose debug.
    if ($output->isVerbose()) {
      foreach ($fc->getLogs() as $log) {
        $output->writeln("DEBUG: " . $log['title']);
        if (is_string($log['data'])) {
          $output->writeln('  ' . $log['data']);
        }
        else {
          $output->writeln('  ' . var_export($log['data'], 1));
        }
      }
    }

    if ($stdin) {
      unlink($source);
    }
    if ($stdout) {
      readfile($destination);
      unlink($destination);
    }

    return 0;
  }

}