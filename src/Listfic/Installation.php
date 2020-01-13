<?php
namespace Listfic;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class Installation {
	static public function postInstall(Event $event) {
		$root = dirname($event->getComposer()->getConfig()->get('vendor-dir'));
		file_put_contents(__DIR__."/test1.txt", "installe");
		file_put_contents("ici.txt", $root);
		file_put_contents("/tessst.txt", $root);
		echo realpath(".");
		return realpath("..");
//		copy(__DIR__.'/../../config-example.php', "$root/config/listfic.php");
	}
	static public function postUninstall(Event $event) {
		$root = dirname($event->getComposer()->getConfig()->get('vendor-dir'));
		file_put_contents(__DIR__."/test3.txt", "desinstalle encore");
		file_put_contents("desinstalle.txt", $root);
		echo realpath(".");
		return realpath("..");
//		inlink(__DIR__.'/../../config-example.php');
	}
}
