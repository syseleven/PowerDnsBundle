<?php


use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application;

if (!file_exists($file = __DIR__.'/../vendor/autoload.php')) {
    throw new \RuntimeException('Install the dependencies to run the test suite.');
}

$loader = require $file;

AnnotationRegistry::registerLoader('class_exists');

require_once __DIR__.'/Functional/app/AppKernel.php';

// Setting up the fixtures
$kernel = new \AppKernel("test", true);
$kernel->boot();

$application = new Application($kernel);
$application->setAutoExit(false);
runConsole("doctrine:schema:drop", array("--force" => true), $application);
runConsole("doctrine:schema:create",array(), $application);
runConsole("doctrine:fixtures:load", array("--fixtures" => __DIR__ . "/../DataFixtures",'-n' => true), $application);

function runConsole($command, Array $options = array(), Application $application)
{
    $options["-e"] = "test";
    // $options["-q"] = null;
    $options = array_merge($options, array('command' => $command));
    return $application->run(new \Symfony\Component\Console\Input\ArrayInput($options));
}