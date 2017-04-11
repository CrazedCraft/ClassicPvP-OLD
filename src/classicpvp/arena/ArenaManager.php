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

namespace classicpvp\arena;

use core\Utils;
use classicpvp\ClassicPvPPlayer;
use classicpvp\Main;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;

class ArenaManager {

	/** @var Main */
	private $plugin;

	/** @var Arena[] */
	private $arenas = [];

	/* Path to where the Arena data file is stored */
	const DATA_FILE_PATH = "data" . DIRECTORY_SEPARATOR . "Arenas.json";

	/**
	 * ArenaManager constructor
	 *
	 * @param Main $plugin
	 */
	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
		$plugin->saveResource(self::DATA_FILE_PATH);
		$this->registerFromData();
	}

	/**
	 * Registers the arena data from Arenas.json
	 */
	private function registerFromData() {
		$data = (new Config($this->plugin->getDataFolder() . self::DATA_FILE_PATH))->getAll();
		foreach($data as $arena) {
			$this->addArena($arena["name"], $arena["author"], Utils::parseVector($arena["a"]), Utils::parseVector($arena["b"]));
		}
	}

	/**
	 * @param string $name
	 * @param string $author
	 * @param Vector3 $a
	 * @param Vector3 $b
	 */
	public function addArena(string $name, string $author, Vector3 $a, Vector3 $b) {
		$this->arenas[strtolower(Utils::cleanString(Utils::stripSpaces($name)))] = new Arena($this, $name, $author, $a, $b);
	}

	/**
	 * @return Arena[]
	 */
	public function getArenas() {
		return $this->arenas;
	}

	/**
	 * @param $name
	 * @return Arena
	 */
	public function getArena($name) {
		return $this->arenas[strtolower(Utils::cleanString(Utils::stripSpaces($name)))];
	}

	/**
	 * @param $name
	 * @return bool
	 */
	public function isArena($name) {
		return $this->getArena(strtolower(Utils::cleanString(Utils::stripSpaces($name)))) instanceof Arena;
	}

	/**
	 * @param Arena $arena
	 * @param ClassicPvPPlayer $player
	 */
	public function addPlayerToArena(Arena $arena, ClassicPvPPlayer $player) {
		Main::applyKit($player);
		$player->setArena($arena);
		$player->teleport($this->getSafeSpawnLocation($arena, $player->getLevel()));
		$arena->updatePlayerCount($arena->getPlayerCount() + 1);
		$player->sendTranslatedMessage("ARENA_JOIN", [$arena->getName(), $arena->getAuthor()], true);
	}

	/**
	 * @param Arena $arena
	 * @param Level $level
	 *
	 * @return \pocketmine\level\Position
	 */
	public function getSafeSpawnLocation(Arena $arena, Level $level) {
		$pos = $level->getSafeSpawn($arena->getRandomLocation());
		if($pos->y <= $arena->getMaxY() and $pos->y >= $arena->getMinY()) return $pos;
		return $this->getSafeSpawnLocation($arena, $level);
	}

	/**
	 * @param ClassicPvPPlayer $player
	 */
	public function removePlayerFromArena(ClassicPvPPlayer $player) {
		if($player->inArena()) {
			$player->getArena()->updatePlayerCount($player->getArena()->getPlayerCount() - 1);
			$player->removeArena();
		}
	}

}