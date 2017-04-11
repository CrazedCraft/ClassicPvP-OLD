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

namespace classicpvp\entity\npc;

use core\entity\npc\HumanNPC;
use core\entity\text\UpdatableFloatingText;
use core\Utils;
use classicpvp\arena\Arena;
use classicpvp\ClassicPvPPlayer;
use classicpvp\Main;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Location;
use pocketmine\nbt\tag\Compound;
use pocketmine\utils\PluginException;

class JoinArenaNPC extends HumanNPC {

	/** @var Main */
	private $plugin;

	/** @var Arena */
	private $arena;

	/** @var UpdatableFloatingText */
	public $playingText;

	public function initEntity() {
		parent::initEntity();
		$plugin = $this->server->getPluginManager()->getPlugin("ClassicPvP");
		if($plugin instanceof Main and $plugin->isEnabled()){
			$this->plugin = $plugin;
		} else {
			throw new PluginException("ClassicPvP plugin isn't loaded!");
		}
	}

	/**
	 * @return Main
	 */
	public function getPlugin() {
		return $this->plugin;
	}

	/**
	 * @return Arena
	 */
	public function getArena() {
		return $this->arena;
	}

	/**
	 * @param string $text
	 */
	public function updatePlayingText($text) {
		if(!$this->playingText instanceof UpdatableFloatingText) {
			$pos = $this->getPosition();
			$pos->y -= 1;
			$this->playingText = new UpdatableFloatingText($pos, Utils::translateColors($text));
			return;
		}
		$this->playingText->update(Utils::translateColors($text));
	}

	/**
	 * @param $string
	 */
	public function setArena($string) {
		$this->arena = $this->plugin->getArenaManager()->getArena($string);
	}

	public function attack($damage, EntityDamageEvent $source) {
		$source->setCancelled(true);
		if($source instanceof EntityDamageByEntityEvent) {
			$attacker = $source->getDamager();
			if($attacker instanceof ClassicPvPPlayer) {
				if($attacker->isAuthenticated()) {
					if(!$attacker->inArena()) {
						if($this->arena instanceof Arena) {
							$this->plugin->getArenaManager()->addPlayerToArena($this->arena, $attacker);
						} else {
							$attacker->sendTranslatedMessage("ARENA_JOIN_ERROR", [], true);
						}
					} else {
						$attacker->sendTranslatedMessage("ALREADY_IN_ARENA", [], true);
					}
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
	 * @param string $arena
	 *
	 * @return JoinArenaNPC|HumanNPC|null
	 */
	public static function spawn($shortName, Location $pos, $name, $skin, $skinName, Compound $nbt, $arena = "") {
		$entity = parent::spawn($shortName, $pos, $name, $skin, $skinName, $nbt);
		if($entity instanceof JoinArenaNPC) {
			$entity->updatePlayingText("&l&e0 players playing&r");
			$entity->setArena($arena);
		}
		return $entity;
	}

}