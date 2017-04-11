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
use core\Utils;
use classicpvp\Main;
use classicpvp\task\UpdatePlayingCountTask;
use pocketmine\entity\Entity;
use pocketmine\level\Location;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\utils\Config;

class NPCManager {

	/** @var Main */
	private $plugin;

	/** @var UpdatePlayingCountTask */
	private $updateTask;

	/** @var HumanNPC[] */
	public $spawned = [];

	/* Path to where the NPC data file is stored */
	const DATA_FILE_PATH = "data" . DIRECTORY_SEPARATOR . "NPCs.json";

	/* NPC types */
	const TYPE_JOIN_ARENA = "join";
	const TYPE_DISPLAY = "display";

	/**
	 * NPCManager constructor
	 *
	 * @param Main $plugin
	 */
	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
		$plugin->saveResource(self::DATA_FILE_PATH);
		Entity::registerEntity(DisplayNPC::class, true);
		Entity::registerEntity(JoinArenaNPC::class, true);
		$this->spawnFromData();
		$this->updateTask = new UpdatePlayingCountTask($plugin);
	}

	/**
	 * @return Main
	 */
	public function getPlugin() {
		return $this->plugin;
	}

	private function spawnFromData() {
		$data = (new Config($this->plugin->getDataFolder() . self::DATA_FILE_PATH))->getAll();
		foreach($data as $npc) {
			try {
				$path = "data" . DIRECTORY_SEPARATOR . "skins" . DIRECTORY_SEPARATOR . $npc["skin-file"];
				$this->plugin->saveResource($path);
				$npc["skin"] = file_get_contents($this->plugin->getDataFolder() . $path);
				$npc["skinName"] = "";
			} catch(\ArrayOutOfBoundsException $e) {
				$npc["skin"] = "";
				$npc["skinName"] = "";
			}
			$this->spawn(Utils::parseVector($npc["pos"]), Utils::translateColors($npc["name"]), $npc["skin"], $npc["skinName"], $npc["yaw"], $npc["pitch"], $npc["type"], $npc["data"]);
		}
	}

	public function spawn(Vector3 $pos, $name, $skin, $skinName = "custom", $yaw = 180, $pitch = 0, $type = "display", $extraData = []) {
		$location = new Location($pos->x, $pos->y, $pos->z, $yaw, $pitch, $this->plugin->getServer()->getDefaultLevel());
		switch($type) {
			case self::TYPE_DISPLAY:
				$npc = DisplayNPC::spawn("DisplayNPC", $location, $name, $skin, $skinName, $this->makeNBT($pos), $extraData["text"]);
				return;
			case self::TYPE_JOIN_ARENA:
				$this->spawned[] = JoinArenaNPC::spawn("JoinArenaNPC", $location, $name, $skin, $skinName, $this->makeNBT($pos), $extraData["arena"]);
				return;
		}
	}

	public function makeNBT(Vector3 $pos) {
		return new Compound("", [
			"Pos" => new Enum("Pos", [
				new DoubleTag("", $pos->x),
				new DoubleTag("", $pos->y),
				new DoubleTag("", $pos->z)
			]),
			"Motion" => new Enum("Motion", [
				new DoubleTag("", 0),
				new DoubleTag("", 0),
				new DoubleTag("", 0)
			]),
			"Rotation" => new Enum("Rotation", [
				new FloatTag("", 180),
				new FloatTag("", 0)
			]),
		]);
	}

	/**
	 * @return \core\entity\npc\HumanNPC[]
	 */
	public function getSpawned() {
		return $this->spawned;
	}

}