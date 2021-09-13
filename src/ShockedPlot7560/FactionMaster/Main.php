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
use ShockedPlot7560\FactionMaster\libs\JackMD\ConfigUpdater\ConfigUpdater;
use ShockedPlot7560\FactionMaster\libs\JackMD\UpdateNotifier\UpdateNotifier;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\resourcepacks\ResourcePack;
use pocketmine\utils\Config;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Button\Collection\CollectionFactory;
use ShockedPlot7560\FactionMaster\Command\FactionCommand;
use ShockedPlot7560\FactionMaster\Database\Table\FactionTable;
use ShockedPlot7560\FactionMaster\Entity\ScoreboardEntity;
use ShockedPlot7560\FactionMaster\Listener\BroadcastMessageListener;
use ShockedPlot7560\FactionMaster\Listener\EventListener;
use ShockedPlot7560\FactionMaster\Listener\ScoreHudListener;
use ShockedPlot7560\FactionMaster\Manager\ConfigManager;
use ShockedPlot7560\FactionMaster\Manager\DatabaseManager;
use ShockedPlot7560\FactionMaster\Manager\ExtensionManager;
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
    /** @var array */
    public static $scoreboardEntity;
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

        $this->initImage();
        
        MigrationManager::init($this);
        if (version_compare($this->getDescription()->getVersion(), $this->version->get("migrate-version")) == 1) {
            MigrationManager::migrate($this->version->get("migrate-version"));
        }

        RouterFactory::init();
        RewardFactory::init();
        CollectionFactory::init();
    }

    public function onEnable(): void {
        $this->init();
        ExtensionManager::load();

        $langConfigExtension = [];
        foreach (ExtensionManager::getExtensions() as $extension) {
            $langConfigExtension[$extension->getExtensionName()] = $extension->getLangConfig();
        }

        $this->initTranslationExtension();
        $this->getScheduler()->scheduleRepeatingTask(new SyncServerTask($this), (int) Utils::getConfig("sync-time"));
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

        $this->getServer()->getCommandMap()->register($this->getDescription()->getName(), new FactionCommand($this, "faction", Utils::getText("", "COMMAND_FACTION_DESCRIPTION"), ["f", "fac"]));

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
        @mkdir(Utils::getLangFile());

        $this->saveDefaultConfig();
        $this->saveResource('translation.yml');
        $this->saveResource('level.yml');

        $this->config = Utils::getConfigFile("config");
        $this->levelConfig = Utils::getConfigFile("level");
        $this->translation = Utils::getConfigFile("translation");

        ConfigUpdater::checkUpdate($this, $this->getConfig(), "file-version", ConfigManager::CONFIG_VERSION);
        ConfigUpdater::checkUpdate($this, $this->getConfig("level"), "file-version", ConfigManager::LEVEL_VERSION);
        ConfigUpdater::checkUpdate($this, $this->getConfig("translation"), "file-version", ConfigManager::TRANSLATION_VERSION);

        foreach (ConfigManager::getTranslationConfig()->get("languages") as $key => $language) {
            ConfigUpdater::checkUpdate($this, Utils::getConfigLangFile($language), "file-version", ConfigManager::LANG_FILE_VERSION[$language]);
            $this->saveResource("Translation/$language.yml");
        }
    }

    private function initImage(): void {
        if (Utils::getConfig("active-image") == true) {
            $pack = $this->getServer()->getResourcePackManager()->getPackById("6682bde3-ece8-4f22-8d6b-d521efc9325d");
            if (!$pack instanceof ResourcePack) {
                $this->getLogger()->warning("To enable FactionMaster images and a better player experience, please download the dedicated FactionMaster pack. Then reactivate the images once this is done.");
                self::$activeImage = false;
            }else{
                self::$activeImage = true;
            }
        }else{
            self::$activeImage = false;
        }
    }

    private function initTranslationExtension(): void {
        $langConfigExtension = [];
        foreach (ExtensionManager::getExtensions() as $extension) {
            $langConfigExtension[$extension->getExtensionName()] = $extension->getLangConfig();
        }
        foreach ($langConfigExtension as $extensionName => $langConfig) {
            foreach ($langConfig as $langSlug => $langConfigFile) {
                if (!$langConfigFile instanceof Config) {
                    $this->getLogger()->warning("Loading the translate files of : $extensionName, check the return value of the function getLangConfig() and verify its key and value. If you are not the author of this extension, please inform him");
                } else {
                    $langMain = new Config($this->mainFolder . "Translation/$langSlug.yml", Config::YAML);
                    foreach ($langConfigFile->getAll() as $key => $value) {
                        $langMain->__set($key, $value);
                    }
                    $langMain->save();
                }
            }
        }
    }
}