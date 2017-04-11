<?php

/**
 * CrazedCraft Network ClassicPvP
 *
 * Copyright (C) 2016 CrazedCraft Network
 *
 * This is private software, you cannot redistribute it and/or modify any way
 * unless otherwise given permission to do so. If you have not been given explicit
 * permission to view or modify this software you should take the appropriate actions
 * to remove this software from your device immediately.
 *
 * @author JackNoordhuis
 *
 * Created on 13/07/2016 at 12:57 AM
 *
 */

namespace classicpvp\command;

use core\Main as Core;
use core\command\CoreUserCommand;
use core\CorePlayer;
use classicpvp\ClassicPvPPlayer;
use pocketmine\Server;

class HubCommand extends CoreUserCommand {

	public function __construct(Core $plugin) {
		parent::__construct($plugin, "hub", "Returns you to the hub", "/hub", ["spawn", "lobby"]);
	}

	public function onRun(CorePlayer $player, array $args) {
		/** @var ClassicPvPPlayer $player */
		$player->kill();
		$player->teleport($this->getPlugin()->getServer()->getDefaultLevel()->getSafeSpawn());
		$player->sendTranslatedMessage("HUB_COMMAND", [], true);
		return true;
	}

}