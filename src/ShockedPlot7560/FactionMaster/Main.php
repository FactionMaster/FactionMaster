<?php

namespace ShockedPlot7560\FactionMaster;

use Exception;
use PDO;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\UUID;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Database;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Database\Table\FactionTable;
use ShockedPlot7560\FactionMaster\Route\Faction\Manage\ManageFactionMain;
use ShockedPlot7560\FactionMaster\Route\MainPanel;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Task\DatabaseSynchronisation;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class Main extends PluginBase implements Listener{

    /** @var \pocketmine\plugin\PluginLogger */
    public static $logger;
    /** @var \ShockedPlot7560\FactionMaster\Main */
    public static $instance;
    /** @var \pocketmine\utils\Config */
    public $config;
    /** @var \ShockedPlot7560\FactionMaster\Database\Database */
    public $Database;
    /** @var \pocketmine\plugin\Plugin */
    public $FormUI;

    public function onEnable()
    {
        self::$logger = $this->getLogger();
        
        $this->loadConfig();
        $this->Database = new Database($this);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->FormUI = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        if ($this->FormUI === null) {
            self::$logger->alert("FactionMaster need FormAPI to work, please install them and reload server");
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }

        self::$instance = $this;

        RouterFactory::init();
    }

    private function loadConfig() : void {
        @mkdir($this->getDataFolder());
        $this->saveResource('config.yml');
        $this->saveDefaultConfig();
        $this->config = new Config($this->getDataFolder() . "config.yml");
    }

    public static function getInstance() : self {
        return self::$instance;
    }

    public function JoinEvent(PlayerJoinEvent $event) {
        Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $event->getPlayer());
    }

}