<?php
/**
 * Created by PhpStorm.
 * User: Jack
 * Date: 20/4/17
 * Time: 9:58 PM
 */

namespace  classicpvp\gui\item\serverselectors;

use core\CorePlayer;
use core\gui\item\GUIItem;
use core\language\LanguageUtils;
use classicpvp\Main;
use pocketmine\item\Item;

class ServerSelector extends GUIItem {

	public function __construct($parent = null) {
		parent::__construct(Item::get(Item::COMPASS, 0, 1), $parent);
		$this->setCustomName(LanguageUtils::translateColors("&l&dServer Selector"));
		$this->setPreviewName($this->getName());
	}

	public function onClick(CorePlayer $player) {
		$player->addWindow($player->getGuiContainer(Main::GUI_SERVER_SELECTION_CONTAINER));
	}

	public function getCooldown() : int {
		return 5; // in seconds
	}

}