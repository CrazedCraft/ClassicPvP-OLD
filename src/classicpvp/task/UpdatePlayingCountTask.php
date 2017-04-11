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

namespace classicpvp\task;

use classicpvp\entity\npc\JoinArenaNPC;
use classicpvp\Main;
use pocketmine\scheduler\PluginTask;

/**
 * Task that updates the joining npc's in the lobby every 10 seconds
 */
class UpdatePlayingCountTask extends PluginTask {

	/** @var Main */
	private $plugin;

	public function __construct(Main $plugin) {
		parent::__construct($plugin);
		$this->plugin = $plugin;
		$this->setHandler($plugin->getServer()->getScheduler()->scheduleRepeatingTask($this, 20 * 10));
	}

	public function onRun($tick) {
		foreach($this->plugin->getNpcManager()->getSpawned() as $e) {
			if($e instanceof JoinArenaNPC) $e->updatePlayingText("&l&e{$e->getArena()->getPlayerCount()} players playing&r");
		}
	}

}