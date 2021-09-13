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

use ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\PacketHooker;
use ShockedPlot7560\FactionMaster\libs\JackMD\UpdateNotifier\UpdateNotifier;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Button\Collection\CollectionFactory;
use ShockedPlot7560\FactionMaster\Command\FactionCommand;
use ShockedPlot7560\FactionMaster\Database\Table\FactionTable;
use ShockedPlot7560\FactionMaster\Listener\BroadcastMessageListener;
use ShockedPlot7560\FactionMaster\Listener\EventListener;
use ShockedPlot7560\FactionMaster\Listener\ScoreHudListener;
use ShockedPlot7560\FactionMaster\Manager\ConfigManager;
use ShockedPlot7560\FactionMaster\Manager\DatabaseManager;
use ShockedPlot7560\FactionMaster\Manager\ExtensionManager;
use ShockedPlot7560\FactionMaster\Manager\ImageManager;
use ShockedPlot7560\FactionMaster\Manager\LeaderboardManager;
use ShockedPlot7560\FactionMaster\Manager\MigrationManager;
use ShockedPlot7560\FactionMaster\Manager\PermissionManager;
use ShockedPlot7560\FactionMaster\Manager\SyncServerManager;
use ShockedPlot7560\FactionMaster\Reward\RewardFactory;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Task\SyncServerTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class Main extends PluginBase implements Listener {

    /** @var Plugin */
    public $formUI;
    /** @var int[] */
    public static $activeTitle;
    /** @var boolean */
    public static $activeImage;

    /** @var Main */
    private static $instance;
    /** @var array */
    private static $tableQuery;
    /** @var string */
    private static $topFactionQuery;

    public function onLoad(): void {
        $factionTable = FactionTable::TABLE_NAME;

        self::$topFactionQuery = "SELECT * FROM $factionTable ORDER BY level DESC, xp DESC, power DESC LIMIT 10";
        self::$instance = $this;

        ConfigManager::init($this);
        SyncServerManager::init($this);
        DatabaseManager::init($this);
        MainAPI::init(DatabaseManager::getPDO());
        PermissionManager::init();
        ImageManager::init($this);
        LeaderboardManager::init($this);
        
        MigrationManager::init($this);
        if (version_compare($this->getDescription()->getVersion(), Utils::getConfigFile("version")->get("migrate-version")) == 1) {
            MigrationManager::migrate(Utils::getConfigFile("version")->get("migrate-version"));
        }

        RouterFactory::init();
        RewardFactory::init();
        CollectionFactory::init();
    }

    public function onEnable(): void {
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

        LeaderboardManager::checkLeaderBoard();
        if (Utils::getConfig("faction-scoreboard") === true 
                && Utils::getConfig("faction-scoreboard-position") !== false 
                && Utils::getConfig("faction-scoreboard-position") !== "") {
            LeaderboardManager::placeScoreboard();
        }
        
        ExtensionManager::load();

        $langConfigExtension = [];
        foreach (ExtensionManager::getExtensions() as $extension) {
            $langConfigExtension[$extension->getExtensionName()] = $extension->getLangConfig();
        }

        ExtensionManager::initTranslationExtension();
        $this->getScheduler()->scheduleRepeatingTask(new SyncServerTask($this), (int) Utils::getConfig("sync-time"));
    }

    public function onDisable() {
        LeaderboardManager::despawnLeaderboard();
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

    public static function getTopQuery(): string {
        return self::$topFactionQuery;
    }

    public static function setTopQuery(string $query): void {
        self::$topFactionQuery = $query;
    }
}