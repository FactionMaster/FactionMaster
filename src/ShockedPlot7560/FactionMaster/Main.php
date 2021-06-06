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
use ShockedPlot7560\FactionMaster\API\PermissionManager;
use ShockedPlot7560\FactionMaster\Button\ButtonFactory;
use ShockedPlot7560\FactionMaster\Command\FactionCommand;
use ShockedPlot7560\FactionMaster\Database\Database;
use ShockedPlot7560\FactionMaster\Reward\RewardFactory;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
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

    public function onEnable()
    {
        self::$logger = $this->getLogger();
        self::$instance = $this;
        Utils::printLogo(self::$logger);

        self::$logger->info("Loading configurations");
        $this->loadConfig();
        self::$logger->info("Initialization and saving of the database");
        $this->Database = new Database($this);
        
        if(!PacketHooker::isRegistered()) PacketHooker::register($this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->FormUI = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        if ($this->FormUI === null) {
            self::$logger->alert("FactionMaster need FormAPI to work, please install them and reload server");
            $this->getServer()->getPluginManager()->disablePlugin($this);
        } 
        
        $this->getServer()->getCommandMap()->register($this->getDescription()->getName(), new FactionCommand($this, "faction", "FactionMaster command", ["f", "fac"]));

        self::$logger->info("Loading the global structure");
        RouterFactory::init();
        RewardFactory::init();
        ButtonFactory::init();
        PermissionManager::init();
        if (\is_array($this->config->get("extensions"))) {
            self::$logger->info("Loading extensions");
            foreach ($this->config->get("extensions") as $ExtensionName) {
                self::$logger->info("Loading " . $ExtensionName);
                /** @var \ShockedPlot7560\FactionMaster\API\Extension */
                $Plugin = $this->getServer()->getPluginManager()->getPlugin($ExtensionName);
                if ($Plugin === null) {
                    self::$logger->warning("Loading the extension: $ExtensionName, failed, check the name and presence of this extension on the server");
                }else{
                    $Plugin->execute();
                    foreach ($Plugin->getLangConfig() as $LangSLug => $LangConfig) {
                        if (!$LangConfig instanceof Config) {
                            self::$logger->warning("Loading the translate files of : $ExtensionName, check the return value of the function getLangConfig() and verify its key and value");
                        }else{
                            $LangMainFile = new Config($this->getDataFolder() . "Translation/$LangSLug.yml", Config::YAML);
                            foreach ($LangConfig->getAll() as $key => $value) {
                                $LangMainFile->__set($key, $value);
                            }
                            $LangMainFile->save();
                        }
                    }
                }
            }
            self::$logger->info("Loading extensions finish");
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
}