<?php
namespace FileConverter\Console;

class Application extends \Symfony\Component\Console\Application {
  public function __construct() {
    parent::__construct();

    // Identify all of the available console commands.
    $cmds = array(
      new InfoCommand,
      new ConvertCommand,
      new TestCommand,
    );

    // Add the commands to the application.
    foreach ($cmds as &$cmd) {
      $this->add($cmd);
    }

    // Not supported until a later version of Symfony.
    // $this->setDefaultCommand('convert');
  }

}