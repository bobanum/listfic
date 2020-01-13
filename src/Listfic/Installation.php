<?php
namespace Listfic;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class Installation {
	static public function postInstall(Event $event) {
		$root = dirname($event->getComposer()->getConfig()->get('vendor-dir'));
		copy(__DIR__.'/../../config-example.php', "$root/config/listfic.php");
	}
	static public function postUninstall(Event $event) {
		inlink(__DIR__.'/../../config-example.php');
	}
}