<?php

/**
 * ClassicPvP – SpawnWarpItem.php.php
 *
 * Copyright (C) 2017 Jack Noordhuis
 *
 * This is private software, you cannot redistribute and/or modify it in any way
 * unless given explicit permission to do so. If you have not been given explicit
 * permission to view or modify this software you should take the appropriate actions
 * to remove this software from your device immediately.
 *
 * @author Jack Noordhuis
 *
 * Created on 31/8/17 at 11:47 PM
 *
 */

namespace classicpvp\gui\item;

use core\CorePlayer;
use core\gui\item\GUIItem;
use core\language\LanguageUtils;
use pocketmine\item\Item;

class SpawnWarpItem extends GUIItem {

	const GUI_ITEM_ID = "spawn_warp_item";

	public function __construct($parent = null) {
		parent::__construct(Item::get(Item::BED, mt_rand(1, 15), 1), $parent);
		$this->setCustomName(LanguageUtils::translateColors("&l&aWarp to spawn"));
		$this->setPreviewName($this->getName());
	}

	public function onClick(CorePlayer $player) {
		$player->kill();
		$player->sendTranslatedMessage("HUB_COMMAND", [], true);
	}

	public function getCooldown() : int {
		return 5; // in seconds
	}

}