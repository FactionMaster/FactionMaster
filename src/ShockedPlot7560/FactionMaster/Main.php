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

use CortexPE\Commando\PacketHooker;
use JackMD\ConfigUpdater\ConfigUpdater;
use JackMD\UpdateNotifier\UpdateNotifier;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginLogger;
use pocketmine\resourcepacks\ResourcePack;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;
use ShockedPlot7560\FactionMaster\Button\Collection\CollectionFactory;
use ShockedPlot7560\FactionMaster\Command\FactionCommand;
use ShockedPlot7560\FactionMaster\Database\Database;
use ShockedPlot7560\FactionMaster\Database\Table\ClaimTable;
use ShockedPlot7560\FactionMaster\Database\Table\FactionTable;
use ShockedPlot7560\FactionMaster\Database\Table\HomeTable;
use ShockedPlot7560\FactionMaster\Database\Table\InvitationTable;
use ShockedPlot7560\FactionMaster\Database\Table\UserTable;
use ShockedPlot7560\FactionMaster\Entity\ScoreboardEntity;
use ShockedPlot7560\FactionMaster\Extension\ExtensionManager;
use ShockedPlot7560\FactionMaster\Listener\BroadcastMessageListener;
use ShockedPlot7560\FactionMaster\Listener\EventListener;
use ShockedPlot7560\FactionMaster\Listener\ScoreHudListener;
use ShockedPlot7560\FactionMaster\Migration\MigrationManager;
use ShockedPlot7560\FactionMaster\Migration\SyncServerManager;
use ShockedPlot7560\FactionMaster\Permission\PermissionManager;
use ShockedPlot7560\FactionMaster\Reward\RewardFactory;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Task\InitTranslationFile;
use ShockedPlot7560\FactionMaster\Task\SyncServerTask;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class Main extends PluginBase implements Listener {

    private const CONFIG_VERSION = 3;

    /** @var PluginLogger */
    public static $logger;
    /** @var Main */
    private static $instance;
    /** @var Config */
    public $config;
    /** @var Database */
    public $Database;
    /** @var Plugin */
    public $FormUI;
    /** @var Config */
    public $levelConfig;
    /** @var Config */
    public $version;
    /** @var Config */
    public $translation;
    public static $activeTitle;
    public static $scoreboardEntity;
    /** @var ExtensionManager */
    private $ExtensionManager;
    /** @var PermissionManager */
    private $PermissionManager;

    /** @var array */
    private static $tableQuery;
    private static $topFactionQuery;
    public static $activeImage;

    public function onLoad(): void {
        self::$topFactionQuery = "SELECT * FROM " . FactionTable::TABLE_NAME . " ORDER BY level DESC, xp DESC, power DESC LIMIT 10";
        self::$instance = $this;
        self::$logger = $this->getLogger();

        $this->loadConfig();
        MigrationManager::init();
        SyncServerManager::init();
        $this->loadTableInitQuery();
        $this->Database = new Database($this);
        
        if (Utils::getConfig("active-image") == true) {
            $pack = $this->getServer()->getResourcePackManager()->getPackById("6682bde3-ece8-4f22-8d6b-d521efc9325d");
            if (!$pack instanceof ResourcePack) {
                self::$logger->warning("To enable FactionMaster images and a better player experience, please download the dedicated FactionMaster pack. Then reactivate the images once this is done.");
                self::$activeImage = false;
            }else{
                self::$activeImage = true;
            }
        }else{
            self::$activeImage = false;
        }

        if (version_compare($this->getDescription()->getVersion(), $this->version->get("migrate-version")) == 1) {
            MigrationManager::migrate($this->version->get("migrate-version"));
        }

        RouterFactory::init();
        RewardFactory::init();
        CollectionFactory::init();
    }

    public function onEnable(): void {
        MigrationManager::updateConfigDb();
        $this->init();
        $this->getPermissionManager();
        $this->getExtensionManager()->load();
        $langConfig = [];
        foreach ($this->getExtensionManager()->getExtensions() as $extension) {
            $langConfig[$extension->getExtensionName()] = $extension->getLangConfig();
        }
        $this->getServer()->getAsyncPool()->submitTask(new InitTranslationFile($langConfig, $this->getDataFolder(), self::$logger));
        $this->getScheduler()->scheduleRepeatingTask(new SyncServerTask($this), (int) Utils::getConfig("sync-time"));
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

    public function getExtensionManager(): ?ExtensionManager {
        if ($this->ExtensionManager === null) {
            $this->ExtensionManager = new ExtensionManager();
        }

        return $this->ExtensionManager;
    }

    public function getPermissionManager(): ?PermissionManager {
        if ($this->PermissionManager === null) {
            $this->PermissionManager = new PermissionManager();
        }

        return $this->PermissionManager;
    }

    public static function getTopQuery(): string {
        return self::$topFactionQuery;
    }

    public static function setTopQuery(string $query): void {
        self::$topFactionQuery = $query;
    }

    private function init(): void {
        UpdateNotifier::checkUpdate($this->getDescription()->getName(), $this->getDescription()->getVersion());
        
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new BroadcastMessageListener($this), $this);
        if ($this->getServer()->getPluginManager()->getPlugin("ScoreHud") instanceof Plugin) {
            $this->getServer()->getPluginManager()->registerEvents(new ScoreHudListener($this), $this);
        }

        if (!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }

        $this->getServer()->getCommandMap()->register($this->getDescription()->getName(), new FactionCommand($this, "faction", "FactionMaster command", ["f", "fac"]));

        $coordinates = explode("|", Utils::getConfig("faction-scoreboard-position"));
        if (count($coordinates) == 4) {
            foreach ($this->getServer()->getLevels() as $level) {
                $entities = $level->getEntities();
                foreach ($entities as $entity) {
                    if ($entity instanceof ScoreboardEntity) {
                        $entity->flagForDespawn();
                        $entity->despawnFromAll();
                    }
                }
            }
        }

        if (Utils::getConfig("faction-scoreboard") === true 
                && Utils::getConfig("faction-scoreboard-position") !== false 
                && Utils::getConfig("faction-scoreboard-position") !== "") {
            self::placeScoreboard();
        }
    }

    private function loadConfig(): void {
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder() . "Translation/");

        $this->saveDefaultConfig();

        $this->saveResource('translation.yml');
        $this->saveResource('level.yml');
        $this->saveResource('version.yml');

        $this->config = new Config($this->getDataFolder() . "config.yml");
        $this->levelConfig = new Config($this->getDataFolder() . "level.yml");
        $this->translation = new Config($this->getDataFolder() . "translation.yml");
        $this->version = new Config($this->getDataFolder() . "version.yml");

        ConfigUpdater::checkUpdate($this, $this->getConfig(), "config-version", self::CONFIG_VERSION);

        foreach ($this->translation->get("languages") as $key => $language) {
            $this->saveResource("Translation/$language.yml");
        }
    }

    private function loadTableInitQuery(): void {
        $auto_increment = $this->config->get("PROVIDER") === Database::MYSQL_PROVIDER ? "AUTO_INCREMENT" : "AUTOINCREMENT";
        self::$tableQuery = [
            FactionTable::class => "CREATE TABLE IF NOT EXISTS `" . FactionTable::TABLE_NAME . "` (
                `id` INTEGER NOT NULL PRIMARY KEY $auto_increment,
                `name` VARCHAR(22) NOT NULL,
                `members` VARCHAR(255) NOT NULL DEFAULT '" . base64_encode(serialize([])) . "',
                `visibility` INT(11) DEFAULT " . Ids::PRIVATE_VISIBILITY . ",
                `xp` INT(11) NOT NULL DEFAULT '0',
                `level` INT(11) NOT NULL DEFAULT '1',
                `description` TEXT,
                `messageFaction` TEXT,
                `ally` VARCHAR(255) NOT NULL DEFAULT '" . base64_encode(serialize([])) . "',
                `max_player` INT(11) NOT NULL DEFAULT '" . $this->config->get("default-member-limit") . "',
                `max_ally` INT(11) NOT NULL DEFAULT '" . $this->config->get("default-ally-limit") . "',
                `max_claim` INT(11) NOT NULL DEFAULT '" . $this->config->get("default-claim-limit") . "',
                `max_home` INT(11) NOT NULL DEFAULT '" . $this->config->get("default-home-limit") . "',
                `power` INT(11) NOT NULL DEFAULT '" . $this->config->get("default-power") . "',
                `permissions` TEXT,
                `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
            ClaimTable::class => "CREATE TABLE IF NOT EXISTS `" . ClaimTable::TABLE_NAME . "` (
                `id` INTEGER NOT NULL PRIMARY KEY $auto_increment ,
                `faction` VARCHAR(255) NOT NULL ,
                `x` INT(11) NOT NULL ,
                `z` INT(11) NOT NULL,
                `world` VARCHAR(255) NOT NULL
            )",
            HomeTable::class => "CREATE TABLE IF NOT EXISTS `" . HomeTable::TABLE_NAME . "` (
                `id` INTEGER NOT NULL PRIMARY KEY $auto_increment ,
                `name` VARCHAR(255) NOT NULL ,
                `faction` VARCHAR(255) NOT NULL ,
                `x` INT(11) NOT NULL,
                `y` INT(11) NOT NULL,
                `z` INT(11) NOT NULL,
                `world` VARCHAR(255) NOT NULL
            )",
            InvitationTable::class => "CREATE TABLE IF NOT EXISTS `" . InvitationTable::TABLE_NAME . "` (
                `id` INTEGER NOT NULL PRIMARY KEY $auto_increment,
                `sender` VARCHAR(255) NOT NULL,
                `receiver` VARCHAR(255) NOT NULL,
                `type` VARCHAR(255) NOT NULL,
                `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
            UserTable::class => "CREATE TABLE IF NOT EXISTS `" . UserTable::TABLE_NAME . "` (
                `name` VARCHAR(22) NOT NULL,
                `faction` VARCHAR(255) DEFAULT NULL,
                `rank` INT(11) DEFAULT NULL,
                `language` VARCHAR(255) NOT NULL DEFAULT '" . Utils::getConfigLang("default-language") . "',
                PRIMARY KEY (`name`)
            )"
        ];
    }

    public static function placeScoreboard(): void {
        Entity::registerEntity(ScoreboardEntity::class, true);
        $coordinates = Utils::getConfig("faction-scoreboard-position");
        if ($coordinates !== false && $coordinates !== "") {
            $coordinates = explode("|", $coordinates);
            if (count($coordinates) == 4) {
                $levelName = $coordinates[3];
                $level = self::getInstance()->getServer()->getLevelByName($levelName);
                if ($level instanceof Level) {
                    $level->loadChunk((float)$coordinates[0] >> 4, (float)$coordinates[2] >> 4);
                    $nbt = Entity::createBaseNBT(new Position((float)$coordinates[0], (float)$coordinates[1], (float)$coordinates[2], $level));
                    $scoreboard = Entity::createEntity("ScoreboardEntity", $level, $nbt);
                    $scoreboard->spawnToAll();
                    self::$scoreboardEntity = [$scoreboard->getId(), $level->getName()];
                } else {
                    self::getInstance()->getLogger()->notice("An unknow world was set on config.yml, can't load faction scoreboard");
                }            
            }
        }
        
    }

    public function onDisable() {
        if (isset(self::$scoreboardEntity[1])) {
            $level = $this->getServer()->getLevelByName(self::$scoreboardEntity[1]);
            if ($level instanceof Level) {
                $entity = $level->getEntity(self::$scoreboardEntity[0]);
                if ($entity instanceof ScoreboardEntity) {
                    $entity->flagForDespawn();
                    $entity->despawnFromAll();
                }
            }
        }
    }
}