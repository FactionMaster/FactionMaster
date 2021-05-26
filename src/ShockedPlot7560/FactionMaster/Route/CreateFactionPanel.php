<?php

namespace ShockedPlot7560\FactionMaster\Route;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\FormAPI;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class CreateFactionPanel implements Route {

    const SLUG = "createFaction";

    public $PermissionNeed = [];
    public $backMenu;

    /** @var FormAPI */
    private $FormUI;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->FormUI = Main::getInstance()->FormUI;
        $this->backMenu = RouterFactory::get(MainPanel::SLUG);
    }

    /**
     * @param array|null $params Give to first item the message to print if wanted
     */
    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $message = "";
        if (isset($params[0]) && \is_string($params[0])) $message = $params[0];
        $menu = $this->createFactionMenu($message);
        $menu->sendToPlayer($player);
    }

    public function call() : callable{
        $backRoute = $this->backMenu;
        return function (Player $Player, $data) use ($backRoute) {
            if ($data === null) return;
            $FactionRequest = MainAPI::getFaction($data[1]);

            if (!$FactionRequest instanceof FactionEntity) {
                if (MainAPI::addFaction($data[1], $Player->getName())) {
                    Utils::processMenu($backRoute, $Player, ['§2Successfully created faction !']);
                }else{
                    $menu = $this->createFactionMenu(" §c>> §4An error has occured");
                    $menu->sendToPlayer($Player);
                }
            }else{
                $menu = $this->createFactionMenu(" §c>> §4This name is already used");
                $menu->sendToPlayer($Player);
            } 
        };
    }

    private function createFactionMenu(string $message = "") : CustomForm {
        $menu = $this->FormUI->createCustomForm($this->call());
        $menu->setTitle("Create a faction");
        $menu->addLabel($message);
        $menu->addInput("Name of your faction : ");
        return $menu;
    }
}