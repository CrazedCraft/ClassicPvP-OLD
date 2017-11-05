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

use classicpvp\command\RestoreKitCommand;
use classicpvp\gui\item\PlayerToggleItem;
use core\entity\text\UpdatableFloatingText;
use core\gui\item\defaults\serverselector\ServerSelector;
use core\gui\item\defaults\SpawnWarpItem;
use core\Utils;
use classicpvp\arena\ArenaManager;
use classicpvp\command\HubCommand;
use classicpvp\entity\npc\NPCManager;
use classicpvp\task\UpdateInfoTextTask;
use pocketmine\item\Item;
use pocketmine\item\food\Potion;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\PluginException;

class Main extends PluginBase {

	const GUI_SERVER_SELECTION_CONTAINER = "server_selection_container";

	/** @var \core\Main */
	private $components;

	/** @var ArenaManager */
	private $arenaManager;

	/** @var ClassicPvPListener */
	private $listener;

	/** @var Config */
	private $settings;

	/** @var NPCManager */
	private $npcManager;

	/** @var UpdatableFloatingText[] */
	public $floatingText = [];

	/** @var Main */
	public static $instance = null;

	/** @var Item[] */
	protected $lobbyItems = [];

	/** @var array */
	public static $languages = [
		"en" => "english.json"
	];

	const MESSAGES_FILE_PATH = "messages" . DIRECTORY_SEPARATOR;

	public function onEnable() {
		Main::$instance = $this;
		$components = $this->getServer()->getPluginManager()->getPlugin("Components");
		if(!$components instanceof \core\Main) throw new PluginException("Components plugin isn't loaded!");
		$this->components = $components;
		$this->loadConfigs();
		$this->setArenaManager();
		$this->setListener();
		$this->setNpcManager();
		$this->spawnFloatingText();
		$this->registerCommands();
		$this->setLobbyItems();
		$this->getServer()->getNetwork()->setName($components->getLanguageManager()->translate("SERVER_NAME", "en"));
	}

	public function loadConfigs() {
		if(!is_dir($this->getDataFolder())) @mkdir($this->getDataFolder());
		if(!is_dir($this->getDataFolder() . "data")) @mkdir($this->getDataFolder() . "data");
		if(!is_dir($this->getDataFolder() . "data" . DIRECTORY_SEPARATOR . "skins")) @mkdir($this->getDataFolder() . "data" . DIRECTORY_SEPARATOR . "skins");
		$msgPath = $this->getDataFolder() . self::MESSAGES_FILE_PATH;
		if(!is_dir($msgPath)) @mkdir($msgPath);
		$this->saveResource("Settings.yml");
		$this->settings = new Config($this->getDataFolder() . "Settings.yml",  Config::YAML);
		foreach(self::$languages as $lang => $filename) {
			$file = $msgPath . $filename;
			$this->saveResource(self::MESSAGES_FILE_PATH . $filename);
			if(!is_file($file)) {
				$this->getLogger()->warning("Couldn't find language file for '{$lang}'! Path: {$file}");
			} else {
				$this->components->getLanguageManager()->registerLanguage($lang, (new Config($file, Config::JSON))->getAll());
			}
		}
	}

	protected function spawnFloatingText() {
		$infoPos = Utils::parseVector($this->settings->get("info-pos"));
		$level = $this->getServer()->getDefaultLevel();
		$this->floatingText[] = new UpdatableFloatingText(new Position($infoPos->x, $infoPos->y + 1.2, $infoPos->z, $level), $this->components->getLanguageManager()->translate("INFO_TEXT_WELCOME"));
		$this->floatingText["playing"] = new UpdatableFloatingText(new Position($infoPos->x, $infoPos->y + 0.9, $infoPos->z, $level), $this->components->getLanguageManager()->translate("INFO_TEXT_PLAYING", "en", [count($this->getServer()->getOnlinePlayers())]));
		$this->floatingText[] = new UpdatableFloatingText(new Position($infoPos->x, $infoPos->y + 0.2, $infoPos->z, $level), $this->components->getLanguageManager()->translate("INFO_TEXT_TWITTER"));
		$this->floatingText["task"] = new UpdateInfoTextTask($this);
		$pos = Utils::parseVector($this->settings->get("npc-tip"));
		$this->floatingText[] = new UpdatableFloatingText(new Position($pos->x, $pos->y + 0.5, $pos->z, $level), $this->components->getLanguageManager()->translate("TIP_TEXT_TITLE"));
		$this->floatingText["tip"] = new UpdatableFloatingText(new Position($pos->x, $pos->y + 0.2, $pos->z, $level), $this->components->getLanguageManager()->translate("TIP_TEXT_MESSAGE_1"));
	}

	protected function registerCommands() {
		$this->components->getCommandMap()->registerAll([
			new HubCommand($this->getCore()),
			new RestoreKitCommand($this->getCore())
		]);
	}

	/**
	 * @return Main
	 */
	public static function getInstance() {
		return self::$instance;
	}

	/**
	 * @return \core\Main
	 */
	public function getCore() {
		return $this->components;
	}

	/**
	 * @return Config
	 */
	public function getSettings() {
		return $this->settings;
	}

	/**
	 * @return ArenaManager
	 */
	public function getArenaManager() {
		return $this->arenaManager;
	}

	/**
	 * @return ClassicPvPListener
	 */
	public function getListener() {
		return $this->listener;
	}

	/**
	 * @return NPCManager
	 */
	public function getNpcManager() {
		return $this->npcManager;
	}

	/**
	 * Initiate the arena manager
	 */
	public function setArenaManager() {
		$this->arenaManager = new ArenaManager($this);
	}

	/**
	 * Set the listener
	 */
	public function setListener() {
		$this->listener = new ClassicPvPListener($this);
	}

	/**
	 * Set the npc manager
	 */
	public function setNpcManager() {
		$this->npcManager = new NPCManager($this);
	}

	/**
	 * Set the lobby items
	 */
	public function setLobbyItems() {
		$this->lobbyItems = [
			Item::get(Item::AIR),
			new PlayerToggleItem(),
			Item::get(Item::AIR),
			Item::get(Item::AIR),
			Item::get(Item::AIR),
			new SpawnWarpItem(),
			Item::get(Item::AIR),
			Item::get(Item::AIR),
			new ServerSelector(),
		];
	}

	/**
	 * Give a player the lobby items
	 *
	 * @param Player $player
	 */
	public function giveLobbyItems(Player $player) {
		self::giveItems($player, $this->lobbyItems, true);
	}

	/**
	 * Apply's the basic kit to a player
	 *
	 * @param Player $player
	 */
	public static function applyKit(Player $player) {
		$inv = $player->getInventory();
		$inv->clearAll();
		$inv->setHelmet(Item::get(Item::IRON_HELMET));
		$inv->setChestplate(Item::get(Item::CHAIN_CHESTPLATE));
		$inv->setLeggings(Item::get(Item::CHAIN_LEGGINGS));
		$inv->setBoots(Item::get(Item::IRON_BOOTS));
		$inv->sendArmorContents($player);
		$items = [
			Item::get(Item::STONE_SWORD),
			Item::get(Item::BOW),
			Item::get(Item::STEAK, 0, 8),
			Item::get(Item::GOLDEN_APPLE, 0, 3),
			Item::get(Item::SPLASH_POTION, Potion::HEALING, 1),
			Item::get(Item::SPLASH_POTION, Potion::HEALING, 1),
			Item::get(Item::SPLASH_POTION, Potion::HEALING, 1),
			Item::get(Item::POTION, Potion::NIGHT_VISION_T, 1),
			Item::get(Item::POTION, Potion::WATER_BREATHING_T, 1),
			Item::get(Item::ARROW, 0, 16)
		];
		self::giveItems($player, $items);
	}

	/**
	 * Give a player an array of items and order them correctly in their hot bar
	 *
	 * @param Player $player
	 * @param Item[] $items
	 * @param bool $shouldCloneItems
	 */
	public static function giveItems(Player $player, array $items, $shouldCloneItems = false) {
		for($i = 0, $invIndex = 0, $inv = $player->getInventory(), $itemCount = count($items); $i < $itemCount; $i++, $invIndex++) {
			$inv->setItem($invIndex, ($shouldCloneItems ? clone $items[$i] : $items[$i]));
			continue;
		}
		$inv->sendContents($player);
	}

}