<?php

/*
 *
 *      ______           __  _                __  ___           __
 *     / ____/___ ______/ /_(_)___  ____     /  |/  /___ ______/ /____  _____
 *    / /_  / __ `/ ___/ __/ / __ \/ __ \   / /|_/ / __ `/ ___/ __/ _ \/ ___/
 *   / __/ / /_/ / /__/ /_/ / /_/ / / / /  / /  / / /_/ (__  ) /_/  __/ /
 *  /_/    \__,_/\___/\__/_/\____/_/ /_/  /_/  /_/\__,_/____/\__/\___/_/
 *
 * FactionMaster - A Faction plugin for PocketMine-MP
 * This file is part of FactionMaster
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author ShockedPlot7560
 * @link https://github.com/ShockedPlot7560
 *
 *
 */

namespace ShockedPlot7560\FactionMaster;

use pocketmine\event\Listener;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Button\Collection\CollectionFactory;
use ShockedPlot7560\FactionMaster\Command\FactionCommand;
use ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\PacketHooker;
use ShockedPlot7560\FactionMaster\libs\JackMD\UpdateNotifier\UpdateNotifier;
use ShockedPlot7560\FactionMaster\Listener\BroadcastMessageListener;
use ShockedPlot7560\FactionMaster\Listener\EventListener;
use ShockedPlot7560\FactionMaster\Listener\ScoreHudListener;
use ShockedPlot7560\FactionMaster\Manager\CommandManager;
use ShockedPlot7560\FactionMaster\Manager\ConfigManager;
use ShockedPlot7560\FactionMaster\Manager\DatabaseManager;
use ShockedPlot7560\FactionMaster\Manager\ExtensionManager;
use ShockedPlot7560\FactionMaster\Manager\ImageManager;
use ShockedPlot7560\FactionMaster\Manager\LeaderboardManager;
use ShockedPlot7560\FactionMaster\Manager\MigrationManager;
use ShockedPlot7560\FactionMaster\Manager\PermissionManager;
use ShockedPlot7560\FactionMaster\Manager\SyncServerManager;
use ShockedPlot7560\FactionMaster\Manager\TranslationManager;
use ShockedPlot7560\FactionMaster\Reward\RewardFactory;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Task\LeaderboardTask;
use ShockedPlot7560\FactionMaster\Task\MapTask;
use ShockedPlot7560\FactionMaster\Task\PowerTask;
use ShockedPlot7560\FactionMaster\Task\SyncServerTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function version_compare;

class FactionMaster extends PluginBase implements Listener {

	/** @var Plugin */
	public $formUI;
	/** @var int[] */
	public static $activeTitle;
	/** @var boolean */
	public static $activeImage;

	/** @var FactionMaster */
	private static $instance;
	/** @var array */
	private static $tableQuery;

	public static $light = false;

	public function onLoad(): void {
		self::$instance = $this;

		ConfigManager::init($this);
		self::$light = ConfigManager::getConfig()->get("low-mode") ?? false;
		TranslationManager::init($this);
		SyncServerManager::init($this);
		DatabaseManager::init();
		MainAPI::init(DatabaseManager::getPDO(), $this);
		if (!self::$light) {
			PermissionManager::init();
			ImageManager::init($this);
			LeaderboardManager::init($this);
			RouterFactory::init();
			RewardFactory::init();
			CollectionFactory::init();
		}
		CommandManager::init(self::$light);

		MigrationManager::init($this);
		if (version_compare($this->getDescription()->getVersion(), Utils::getConfigFile("version")->get("migrate-version")) == 1) {
			MigrationManager::migrate(Utils::getConfigFile("version")->get("migrate-version"));
		}
	}

	public function onEnable(): void {
		if ($this->isEnabled()) {
			UpdateNotifier::checkUpdate($this->getDescription()->getName(), $this->getDescription()->getVersion());
			$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
			$this->getServer()->getPluginManager()->registerEvents(new BroadcastMessageListener($this), $this);
			if ($this->getServer()->getPluginManager()->getPlugin("ScoreHud") instanceof Plugin) {
				$this->getServer()->getPluginManager()->registerEvents(new ScoreHudListener($this), $this);
			}

			if (!PacketHooker::isRegistered()) {
				PacketHooker::register($this);
			}

			$this->getServer()->getCommandMap()->register($this->getDescription()->getName(), new FactionCommand($this, "faction", Utils::getText("", "COMMAND_FACTION_DESCRIPTION"), ["f", "fac"]));

			LeaderboardManager::updateLeaderboards();

			ExtensionManager::load();

			$this->getScheduler()->scheduleRepeatingTask(new SyncServerTask($this), (int) Utils::getConfig("sync-time"));
			$this->getScheduler()->scheduleRepeatingTask(new LeaderboardTask(), 80);
			$time = ConfigManager::getConfig()->get("power-staying-online-time", null) ?? 0;
			if ($time > 0) {
				$this->getScheduler()->scheduleRepeatingTask(new PowerTask(), $time * 20);
			}

			if (Utils::getConfig("f-map-task") !== false) {
				$time = (int) Utils::getConfig("f-map-task");
				if ($time > 0) {
					$this->getScheduler()->scheduleRepeatingTask(new MapTask(), $time);
				}
			}
			if (Utils::getConfig("message-alert") === true) {
				$this->getLogger()->warning("Claim alert are enabled, with a lot of player this can probably a source of lag.");
				$this->getLogger()->warning("So, if you have a lot of player, please disable this feature.");
			}
			MigrationManager::updateConfigDb();
		}
	}

	public static function getTableInitQuery(string $class): ?string {
		return self::$tableQuery[$class] ?? null;
	}

	public static function setTableInitQuery(string $class, string $query): void {
		self::$tableQuery[$class] = $query;
	}

	public static function getInstance(): self {
		return self::$instance;
	}
}