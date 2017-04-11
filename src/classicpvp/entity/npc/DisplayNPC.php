<?php
/**
 * Created by PhpStorm.
 * User: Jack
 * Date: 23/09/2016
 * Time: 5:55 PM
 */

namespace classicpvp\entity\npc;

use classicpvp\ClassicPvPPlayer;
use core\entity\npc\HumanNPC;
use core\Utils;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Location;
use pocketmine\nbt\tag\Compound;

class DisplayNPC extends HumanNPC {

	/** @var string */
	private $text = "";

	/**
	 * Set the text to display to players when they tap the npc
	 *
	 * @param string $text
	 */
	public function setText($text = "") {
		$this->text = Utils::translateColors($text);
	}

	/**
	 * Get the text to display to players when they tap the npc
	 *
	 * @return string
	 */
	public function getText() {
		return $this->text;
	}

	public function attack($damage, EntityDamageEvent $source) {
		parent::attack($damage, $source);
		if($source instanceof EntityDamageByEntityEvent) {
			$attacker = $source->getDamager();
			if($attacker instanceof ClassicPvPPlayer) {
				if($attacker->isAuthenticated()) {
					$attacker->sendMessage($this->text);
				} else {
					$attacker->sendTranslatedMessage("MUST_AUTHENTICATE_FIRST", [], true);
				}
			}
		}
	}

	/**
	 * @param string $shortName
	 * @param Location $pos
	 * @param string $name
	 * @param string $skin
	 * @param string $skinName
	 * @param Compound $nbt
	 * @param string $text
	 *
	 * @return HumanNPC|null
	 */
	public static function spawn($shortName, Location $pos, $name, $skin, $skinName, Compound $nbt, $text = "") {
		$entity = parent::spawn($shortName, $pos, $name, $skin, $skinName, $nbt);
		if($entity instanceof DisplayNPC) $entity->setText($text);
		return $entity;
	}

}