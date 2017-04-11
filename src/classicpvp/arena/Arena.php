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

use pocketmine\math\Vector3;

class Arena {

	/** @var ArenaManager */
	private $manager;

	/** @var string */
	private $name = "";

	/** @var string */
	private $author = "";

	/** @var Vector3 */
	private $a;

	/** @var Vector3 */
	private $b;
	
	/** @var int */
	protected $playerCount = 0;

	/**
	 * Arena constructor.
	 *
	 * @param ArenaManager $manager
	 * @param string $name
	 * @param string $author
	 * @param Vector3 $a
	 * @param Vector3 $b
	 */
	public function __construct(ArenaManager $manager, string $name, string $author, Vector3 $a, Vector3 $b) {
		$this->manager = $manager;
		$this->name = $name;
		$this->author = $author;
		$this->a = $a;
		$this->b = $b;
	}

	/**
	 * @return ArenaManager
	 */
	public function getManager() {
		return $this->manager;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getAuthor() {
		return $this->author;
	}

	/**
	 * @return Vector3
	 */
	public function getAPosition() {
		return $this->a;
	}

	/**
	 * @return Vector3
	 */
	public function getBPosition() {
		return $this->b;
	}

	/**
	 * Get's a random location inside of the arena
	 *
	 * @return Vector3
	 */
	public function getRandomLocation() {
		return $this->polishVector(new Vector3(mt_rand(min($this->a->x, $this->b->x), max($this->a->x, $this->b->x)), mt_rand(min($this->a->y, $this->b->y), max($this->a->y, $this->b->y)), mt_rand(min($this->a->z, $this->b->z), max($this->a->z, $this->b->z))));
	}

	/**
	 * @return int
	 */
	public function getPlayerCount() {
		return $this->playerCount;
	}

	/**
	 * @param int $count
	 */
	public function updatePlayerCount(int $count) {
		$this->playerCount = $count;
	}

	/**
	 * Get the highest possible y
	 *
	 * @return mixed
	 */
	public function getMaxY() {
		return max($this->a->y, $this->b->y);
	}

	/**
	 * Get the lowest possible y
	 *
	 * @return mixed
	 */
	public function getMinY() {
		return min($this->a->y, $this->b->y);
	}

	/**
	 * @param Vector3 $pos
	 *
	 * @return Vector3
	 */
	protected function polishVector(Vector3 $pos) {
		$pos->x = round($pos->x) + 0.5;
		$pos->z = round($pos->z) + 0.5;
		return $pos;
	}

}