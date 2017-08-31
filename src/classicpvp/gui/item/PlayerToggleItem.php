<?php

/**
 * ClassicPvP – PlayerToggleItem.php
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
 * Created on 31/8/17 at 11:44 PM
 *
 */

namespace classicpvp\gui\item;

use core\CorePlayer;
use core\gui\item\GUIItem;
use core\language\LanguageUtils;
use pocketmine\item\Item;

class PlayerToggleItem extends GUIItem {

	const GUI_ITEM_ID = "player_toggle_item";

	public function __construct($parent = null) {
		parent::__construct(Item::get(Item::CLOCK, 0, 1), $parent);
		$this->setCustomName(LanguageUtils::translateColors("&l&6Toggle players"));
		$this->setPreviewName($this->getName());
	}

	public function onClick(CorePlayer $player) {
		$player->setPlayersVisible(!$player->hasPlayersVisible());
		$player->sendTranslatedMessage("TOGGLE_PLAYERS", [], true);
	}

	public function getCooldown() : int {
		return 5; // in seconds
	}

}