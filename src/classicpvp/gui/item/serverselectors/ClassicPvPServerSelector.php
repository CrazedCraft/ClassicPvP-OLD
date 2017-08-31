<?php

/**
 * Created by PhpStorm.
 * User: Jack
 * Date: 20/4/17
 * Time: 6:45 PM
 */

namespace  classicpvp\gui\item\serverselectors;

use core\CorePlayer;
use core\gui\item\GUIItem;
use core\language\LanguageUtils;
use core\Main;
use core\network\NetworkServer;
use core\network\NodeConstants;
use classicpvp\gui\containers\ServerSelectionContainer;
use pocketmine\item\Item;
use pocketmine\network\protocol\TransferPacket;

class ClassicPvPServerSelector extends GUIItem {

	const GUI_ITEM_ID = "classic_pvp_gui_selector";

	public function __construct(ServerSelectionContainer $parent = null) {
		parent::__construct(Item::get(Item::GOLD_CHESTPLATE, 0, 1), $parent);
		$this->setCustomName(LanguageUtils::translateColors("&l&6Classic PvP"));
		$this->setPreviewName($this->getName());
		$this->giveEnchantmentEffect();
	}

	public function onClick(CorePlayer $player) {
		$player->sendMessage(LanguageUtils::translateColors("&6- &cTYou're already connected to classoc pvp!"));
		//$server = Main::getInstance()->getNetworkManager()->getNodes()[NodeConstants::NODE_CLASSIC_PVP]->getSuitableServer();
		//if($server instanceof NetworkServer) {
		//	$pk = new TransferPacket();
		//	$pk->ip = $server->getHost();
		//	$player->dataPacket($pk);
		//} else {
		//	$player->sendMessage(LanguageUtils::translateColors("&c- &6There are currently no classic pvp servers available!"));
		//}
	}

}