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

use core\language\LanguageManager;
use classicpvp\Main;
use pocketmine\scheduler\PluginTask;

/**
 * Task that updates the info text in the lobby every 30 seconds
 */
class UpdateInfoTextTask extends PluginTask {

	/** @var Main */
	private $plugin;

	public function __construct(Main $plugin) {
		parent::__construct($plugin);
		$this->plugin = $plugin;
		$this->setHandler($plugin->getServer()->getScheduler()->scheduleRepeatingTask($this, 20 * 30));
	}

	public function onRun($tick) {
		$this->plugin->floatingText["playing"]->update($this->plugin->getCore()->getLanguageManager()->translate("INFO_TEXT_PLAYING", "en", [count($this->plugin->getServer()->getOnlinePlayers())]));
		$this->plugin->floatingText["tip"]->update($this->plugin->getCore()->getLanguageManager()->translate("TIP_TEXT_MESSAGE_" . (string)mt_rand(1, 5), "en"));
	}

}