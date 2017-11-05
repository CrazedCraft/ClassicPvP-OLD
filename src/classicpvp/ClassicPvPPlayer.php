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

namespace classicpvp;

use core\CorePlayer;
use classicpvp\arena\Arena;
use core\language\LanguageUtils;
use core\Utils;
use pocketmine\entity\Projectile;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\item\food\Potion;
use pocketmine\network\protocol\LevelEventPacket;
use pocketmine\utils\PluginException;
use pocketmine\utils\TextFormat;

class ClassicPvPPlayer extends CorePlayer {

	/** @var Arena|null */
	private $arena;

	/** @var int */
	private $killStreak = 0;

	/** @var ClassicPvPPlayer */
	private $lastPlayerDamager;

	/** @var float */
	private $lastPlayerDamageTime;

	/** @var Main */
	private $plugin = null;

	/**
	 * @return Arena|null
	 */
	public function getArena() {
		return $this->arena;
	}

	/**
	 * @return int
	 */
	public function getKillStreak() {
		return $this->killStreak;
	}

	/**
	 * @param Arena $arena
	 */
	public function setArena(Arena $arena) {
		$this->arena = $arena;
		$this->setPlayersVisible();
		$this->setFood(20);
		$this->setStatus(CorePlayer::STATE_PLAYING);
	}

	/**
	 * Add a kill to the kill streak and give rewards
	 */
	public function updateKillStreak() {
		$this->killStreak++;
		$this->sendTranslatedMessage("KILL_STREAK_UPDATE", [$this->getKillStreak()], true);
		$killMessage = $this->getCore()->getLanguageManager()->translateForPlayer($this, "KILL_MESSAGE_" . (string)mt_rand(1, 5));
		if($this->killStreak < 3) {
			$health = 4 + (1 * $this->getKillStreak());
			$this->sendTranslatedMessage("HEALTH_GAIN", [$killMessage, $health / 2], true);
			$this->heal($health, ($ev = new EntityRegainHealthEvent($this, $health, EntityRegainHealthEvent::CAUSE_CUSTOM)));
			$this->server->getPluginManager()->callEvent($ev);
		} elseif($this->killStreak >= 3 and $this->killStreak < 10) {
			if(rand(1, 6) >= 5) {
				$this->getInventory()->addItem(Item::get(Item::SPLASH_POTION, Potion::HEALING, 1));
				$this->sendTranslatedMessage("BONUS_ITEM", [$killMessage, "1", Utils::translateColors("&l&4Health Potion")], true);
			} else {
				$amount = mt_rand(1, 6);
				$this->getInventory()->addItem(Item::get(Item::ARROW, 0, $amount));
				$this->sendTranslatedMessage("BONUS_ITEM", [$killMessage, "{$amount}", Utils::translateColors("&l&7Arrow")], true);
			}
		} elseif($this->killStreak >= 10) {
			if(rand(1, 6) >= 5) {
				$this->getInventory()->addItem(Item::get(Item::GOLDEN_APPLE, 0, 1));
				$this->sendTranslatedMessage("BONUS_ITEM", [$killMessage, "1", Utils::translateColors("&l&6Golden Apple")], true);
			} else {
				$amount = mt_rand(1, 4);
				$this->getInventory()->addItem(Item::get(Item::STEAK, 0, $amount));
				$this->sendTranslatedMessage("BONUS_ITEM", [$killMessage, "{$amount}", Utils::translateColors("&l&5Steak")], true);
			}
		}
	}

	public function removeArena() {
		$this->arena = null;
		$this->setFood(20);
		$this->setStatus(CorePlayer::STATE_LOBBY);
	}

	/**
	 * @return bool
	 */
	public function inArena() {
		return $this->arena instanceof Arena;
	}

	/**
	 * Set the kill streak back to 0
	 */
	public function resetKillStreak() {
		$this->killStreak = 0;
	}

	public function attack($damage, EntityDamageEvent $source) {
		if($source instanceof EntityDamageByEntityEvent and ($attacker = $source->getDamager()) instanceof ClassicPvPPlayer) {
			if($attacker->getState() === CorePlayer::STATE_PLAYING) {
				if($this->lastPlayerDamager instanceof ClassicPvPPlayer and $this->lastPlayerDamager !== $attacker) {
					$attacker->despawnFrom($this);
					$attacker->spawnTo($this);
				}
				$this->lastPlayerDamager = $attacker;
				$this->lastPlayerDamageTime = microtime(true);
			}
		}
		if($source->getFinalDamage() >= $this->getHealth()) {
			switch($source->getCause()) {
				default:
					$attacker = $this->lastPlayerDamager;
					if($attacker instanceof ClassicPvPPlayer and floor(microtime(true) - $this->lastPlayerDamageTime) <= 12) {
						$this->sendTranslatedMessage("KILLED_BY_PLAYER", [$attacker->getName(), $attacker->getHealth() / 2], true);
						$attacker->updateKillStreak();
					}
					break;
				case EntityDamageEvent::CAUSE_FALL:
//					$this->kill(true);
					$source->setCancelled();
					return;
				case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
					if($source instanceof EntityDamageByEntityEvent) {
						$attacker = $source->getDamager();
						if($attacker instanceof ClassicPvPPlayer) {
							$this->sendTranslatedMessage("KILLED_BY_PLAYER", [$attacker->getName(), $attacker->getHealth() / 2], true);
							$attacker->updateKillStreak();
							$attacker->sendTranslatedMessage("PLAYER_KILL_ATTACK", [$this->getName(), $this->lastPlayerDamager->getHealth() / 2], true);
						} elseif($attacker instanceof Projectile) {
							if($attacker->shootingEntity instanceof ClassicPvPPlayer) {
								$attacker = $attacker->shootingEntity;
								$distance = round($attacker->distance($this));
								$this->sendTranslatedMessage("SHOT_BY_PLAYER", [$attacker->getName(), $distance], true);
								$attacker->updateKillStreak();
								$attacker->sendTranslatedMessage("PLAYER_KILL_SHOT", [$this->getName(), $distance], true);
							}
						}
					}
					break;
				case EntityDamageEvent::CAUSE_PROJECTILE:
					if($source instanceof EntityDamageByEntityEvent) {
						$attacker = $source->getDamager();
						if($attacker instanceof ClassicPvPPlayer) {
							$distance = round($attacker->distance($this));
							$this->sendTranslatedMessage("SHOT_BY_PLAYER", [$attacker->getName(), $distance], true);
							$attacker->updateKillStreak();
							$attacker->sendTranslatedMessage("PLAYER_KILL_SHOT", [$this->getName(), $distance], true);
						}
					}
					break;
			}
			$source->setCancelled();
			$this->kill();
		} else {
			switch($source->getCause()) {
				case EntityDamageEvent::CAUSE_PROJECTILE:
					if($source instanceof EntityDamageByEntityEvent) {
						$attacker = $source->getDamager();
						if($attacker instanceof ClassicPvPPlayer) {
							$distance = round($attacker->distance($this));
							$attacker->sendTip($this->getCore()->getLanguageManager()->translateForPlayer($attacker, "ARROW_HIT", [$this->getName(), $distance, $source->getFinalDamage() / 2]));
						}
					}
					break;
				case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
					if($source instanceof EntityDamageByEntityEvent) {
						$attacker = $source->getDamager();
						if($attacker instanceof Projectile) {
							if($attacker->shootingEntity instanceof ClassicPvPPlayer) {
								$attacker = $attacker->shootingEntity;
								$distance = round($attacker->distance($this));
								$attacker->sendTip($this->getCore()->getLanguageManager()->translateForPlayer($attacker, "ARROW_HIT", [$this->getName(), $distance, $source->getFinalDamage() / 2]));
							}
						}
					}
					break;
				case EntityDamageEvent::CAUSE_FALL:
					$source->setCancelled();
					return;
			}
		}
		parent::attack($damage, $source);
	}

	public function kill($forReal = false) {
		Main::getInstance()->getArenaManager()->removePlayerFromArena($this);
		$this->lastPlayerDamager = null;
		$this->removeAllEffects();
		$this->extinguish();
		$this->resetKillStreak();
		$this->getInventory()->clearAll();
		parent::kill($forReal);
		if($this->isConnected()) $this->plugin->giveLobbyItems($this);
	}

	public function initEntity() {
		parent::initEntity();
		$plugin = $this->server->getPluginManager()->getPlugin("ClassicPvP");
		if($plugin instanceof Main) {
			$this->plugin = $plugin;
		} else {
			throw new PluginException("ClassicPvP plugin isn't loaded!");
		}
	}

	public function afterAuthCheck() {
		$this->addTitle(LanguageUtils::translateColors("&eWelcome to &1C&ar&ea&6z&9e&5d&fC&7r&6a&cf&dt &l&6ClassicPvP&r&e!"), TextFormat::GRAY . ($this->isAuthenticated() ? "Use the sword to start playing!" : ($this->isRegistered() ? "Login to start playing!" : "Follow the prompts to register!")), 10, 100, 10);

		$pk = new LevelEventPacket();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->evid = LevelEventPacket::EVENT_SOUND_CLICK_FAIL;
		$pk->data = 0;
		$this->dataPacket($pk);
	}

	public function onInteract(PlayerInteractEvent $event) {
		if($this->getState() === self::STATE_PLAYING and $event->getBlock()->getId() === Item::TRAPDOOR) {
			$event->setCancelled(true);
		}

		parent::onInteract($event);
	}

}