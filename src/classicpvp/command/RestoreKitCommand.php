<?php
/**
 * Created by PhpStorm.
 * User: Jack
 * Date: 11/10/2016
 * Time: 11:12 PM
 */

namespace classicpvp\command;

use classicpvp\ClassicPvPPlayer;
use classicpvp\Main;
use core\Main as Core;
use core\command\CoreUserCommand;
use core\CorePlayer;

class RestoreKitCommand extends CoreUserCommand {

	public function __construct(Core $plugin) {
		parent::__construct($plugin, "restorekit", "Restores your kit", "/restorekit", ["rkit", "kit", "refreshkit", "givekit"]);
	}

	/**
	 * @param CorePlayer|ClassicPvPPlayer $player
	 * @param array $args
	 *
	 * @return bool
	 */
	public function onRun(CorePlayer $player, array $args) {
		if($player->inArena()) {
			Main::applyKit($player);
			$player->sendTranslatedMessage("RESTORE_KIT_COMMAND");
			return true;
		} else {
			$player->sendTranslatedMessage("MUST_BE_IN_ARENA_FOR_COMMAND");
			return true;
		}
	}

}