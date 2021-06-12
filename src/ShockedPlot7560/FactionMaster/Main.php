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
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use ShockedPlot7560\FactionMaster\Button\Collection\CollectionFactory;
use ShockedPlot7560\FactionMaster\Command\FactionCommand;
use ShockedPlot7560\FactionMaster\Database\Database;
use ShockedPlot7560\FactionMaster\Extension\ExtensionManager;
use ShockedPlot7560\FactionMaster\Permission\PermissionManager;
use ShockedPlot7560\FactionMaster\Reward\RewardFactory;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class Main extends PluginBase implements Listener{

    /** @var \pocketmine\plugin\PluginLogger */
    public static $logger;
    /** @var \ShockedPlot7560\FactionMaster\Main */
    private static $instance;
    /** @var \pocketmine\utils\Config */
    public $config;
    /** @var \ShockedPlot7560\FactionMaster\Database\Database */
    public $Database;
    /** @var \pocketmine\plugin\Plugin */
    public $FormUI;
    /** @var null|\onebone\economyapi\EconomyAPI */
    public $EconomyAPI;
    /** @var Config */
    public $levelConfig;
    /** @var ExtensionManager */
    private $ExtensionManager;
    /** @var PermissionManager */
    private $PermissionManager;

    public function onLoad()
    {
        self::$instance = $this;
        self::$logger = $this->getLogger();
        Utils::printLogo(self::$logger);

        self::$logger->info("Loading configurations");
        $this->loadConfig();

        self::$logger->info("Initialization and saving of the database");
        $this->Database = new Database($this);

        self::$logger->info("Loading the global structure");
        RouterFactory::init();
        RewardFactory::init();
        CollectionFactory::init();
    }

    public function onEnable()
    {
        $this->init();
        $this->getPermissionManager();
        $this->getExtensionManager()->load();
    }

    private function init() {

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        if(!PacketHooker::isRegistered()) PacketHooker::register($this);
        $this->getServer()->getCommandMap()->register($this->getDescription()->getName(), new FactionCommand($this, "faction", "FactionMaster command", ["f", "fac"]));

        $this->FormUI = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        if ($this->FormUI === null) {
            self::$logger->critical("FactionMaster need FormAPI to work, please install them and reload server");
            $this->getServer()->getPluginManager()->disablePlugin($this);
        } 

    }

    private function loadConfig() : void {
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder() . "Translation/");

        $this->saveDefaultConfig();
        $this->saveResource('config.yml');
        $this->saveResource('translation.yml');
        $this->saveResource('level.yml');

        $this->config = new Config($this->getDataFolder() . "config.yml");
        $this->levelConfig = new Config($this->getDataFolder() . "level.yml");
        $this->translation = new Config($this->getDataFolder() . "translation.yml");

        foreach ($this->translation->get("languages") as $key => $language) {
            $this->saveResource("Translation/$language.yml");
        }

    }

    public static function getInstance() : self {
        return self::$instance;
    }

    public function getExtensionManager() : ?ExtensionManager {
        if($this->ExtensionManager === null) $this->ExtensionManager = new ExtensionManager();
        return $this->ExtensionManager;
    }

    public function getPermissionManager() : ?PermissionManager {
        if($this->PermissionManager === null) $this->PermissionManager = new PermissionManager();
        return $this->PermissionManager;
    }
}