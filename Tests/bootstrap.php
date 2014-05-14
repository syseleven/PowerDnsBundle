<?php
/**
 * This file is part of the SysEleven PowerDnsBundle.
 *
 * (c) SysEleven GmbH <http://www.syseleven.de/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author   M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Tests
 */


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
runConsole("doctrine:database:drop", array("--force" => true), $application);
runConsole("doctrine:database:create", array(), $application);
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