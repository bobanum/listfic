<?php
namespace Listfic;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class Installation {
	static public function postInstall(Event $event) {
		$root = dirname($event->getComposer()->getConfig()->get('vendor-dir'));
		file_put_contents("installe", __DIR__."/test1.txt");
		file_put_contents("installe2",$root);
		echo realpath(".");
		copy(__DIR__.'/../../config-example.php', "$root/config/listfic.php");
	}
	static public function postUninstall(Event $event) {
		$root = dirname($event->getComposer()->getConfig()->get('vendor-dir'));
		file_put_contents("desinstalle", __DIR__."/test3.txt");
		file_put_contents("desinstalle2",$root);
		echo realpath(".");
		inlink(__DIR__.'/../../config-example.php');
	}
}
