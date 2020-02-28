<?php
namespace FileConverter\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use FileConverter\FileConverter;

class InfoCommand extends \Symfony\Component\Console\Command\Command {
  protected function configure() {
    $this->setName('info');
    $this->setDescription('List supported converters and extensions');
    $this->setDefinition(array(
      new InputArgument('info_type', InputArgument::OPTIONAL, 'Type of info request (converters|convert-paths|defaults|extension-table)'),
    ));
  }

  protected function execute(InputInterface $input, OutputInterface $output, $mode = NULL) {
    $items = array();

    switch ($input->getArgument('info_type', 'converters')) {
      case 'converters':
        $fc = FileConverter::factory();
        foreach ($fc->getEngineConfigurations() as $configurations) {
          foreach ($configurations->getAllConverters() as $convert_path => $converters) {
            foreach ($converters as $id => $converter) {
              $item = array(
                '#title' => $id,
              );
              $item['Engine'] = preg_replace('@^.*\\\\@', '', $converter['#engine']);
              if (isset($converter['chain']) && $converter['chain']) {
                $item['Engine'] .= ' ' . $converter['chain'];
              }

              $items[$id] = $item;
            }
          }
        }
        break;

      case 'convert-paths':
        $fc = FileConverter::factory();
        foreach ($fc->getEngineConfigurations() as $configurations) {
          foreach ($configurations->getAllConverters() as $convert_path => $converters) {
            $items[$convert_path]['#title'] = $convert_path;
            foreach ($converters as $id => $converter) {
              $engine = preg_replace('@^.*\\\\@', '', $converter['#engine']);
              if ($converter['chain']) {
                $engine .= ' ' . $converter['chain'];
              }
              $item = sprintf('%- 28s %s', $id, $engine);
              $items[$convert_path][] = $item;
            }
          }
        }
        break;

      case 'defaults':
        $output->writeln("File Converter Default Settings:");
        $fc = FileConverter::factory();
        foreach ($fc->getSettings() as $k => $v) {
          $items[] = sprintf('% -25s: %s', $k, $v);
        }
        break;

      case 'extension-table':
        $rows = array();
        $fc = FileConverter::factory();
        $all_tos = array();
        foreach ($fc->getEngineConfigurations() as $configurations) {
          foreach ($configurations->getAllConverters() as $convert_path => $converters) {
            if (preg_match('@^(.*)->(.*)$@', $convert_path, $arr)) {
              $froms = explode('|', trim($arr[1], '()'));
              $tos = explode('|', trim($arr[2], '()'));
            }
            elseif (preg_match('@^(.*)~optimize$@', $convert_path, $arr)) {
              $froms = $tos = array(
                $arr[1],
              );
            }
            else {
              continue;
            }
            foreach ($froms as $from) {
              if (!isset($rows[$from])) {
                $rows[$from] = array();
              }
              foreach ($tos as $to) {
                $all_tos[] = $to;
                if (!isset($rows[$to])) {
                  $rows[$to] = array();
                }
                $rows[$from][$to] += sizeof($converters);
              }
            }
          }
        }

        // Build the table.
        $all_tos = array_unique($all_tos);
        sort($all_tos);
        ksort($rows);
        $exts = array_keys($rows);
        echo "source | " . join(" | ", $all_tos) . "\n";
        echo str_repeat("--- | ", sizeof($all_tos)) . "---\n";
        foreach ($rows as $from => $row) {
          if (empty($row)) {
            continue;
          }
          echo $from;
          foreach ($all_tos as $to) {
            if (isset($row[$to]) && $row[$to]) {
              echo " | " . $row[$to];
            }
            else {
              echo " | ";
            }
          }
          echo "\n";
        }
        break;

      default:
        $output->writeln("Usage: fileconverter info [converters|convert-paths|extension-table]");
        return;
    }

    // Sort and display the items.
    ksort($items);
    foreach ($items as $id => $item) {
      $output->writeln($item['#title']);
      foreach ($item as $k => $v) {
        if ($k{0} !== '#') {
          if (is_array($v)) {
            $output->writeln('   ' . $v['#title']);
            foreach ($v as $k1 => $v1) {
              if ($k1{0} !== '#') {
                $output->writeln('      ' . $v1);
              }
            }
          }
          else {
            $output->writeln('   ' . $v);
          }
        }
      }
    }
  }

}