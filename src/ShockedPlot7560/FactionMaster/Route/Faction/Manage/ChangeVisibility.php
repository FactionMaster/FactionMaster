<?php

namespace ShockedPlot7560\FactionMaster\Route\Faction\Manage;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\FormAPI;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ChangeVisibility implements Route {

    const SLUG = "changeVisibility";

    public $PermissionNeed = [
        Ids::PERMISSION_CHANGE_FACTION_VISIBILITY
    ];
    public $backMenu;

    /** @var FormAPI */
    private $FormUI;
    /** @var array */
    private $sliderData = [
        Ids::PUBLIC_VISIBILITY => "Public",
        Ids::PRIVATE_VISIBILITY => "Private",
        Ids::INVITATION_VISIBILITY => "Invitation"
    ];
    /** @var FactionEntity */
    private $Faction;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->FormUI = Main::getInstance()->FormUI;
        $this->backMenu = RouterFactory::get(ManageFactionMain::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->Faction = MainAPI::getFactionOfPlayer($player->getName());
        
        $menu = $this->changeVisibility();
        $player->sendForm($menu);
    }

    public function call(): callable
    {
        $backMenu = $this->backMenu;
        return function (Player $player, $data) use ($backMenu) {
            if ($data === null) return;
            if (MainAPI::changeVisibility($this->Faction->name, $data[0])) {
                Utils::processMenu($backMenu,  $player, ['§2Successfully modified visibility ! ']);
            }else{
                Utils::processMenu($backMenu, $player, [' §c>> §4An error has occured']);
            }
        };
    }

    private function changeVisibility() : CustomForm {
        $menu = new CustomForm($this->call());
        $menu->addStepSlider("Choose the visibility", $this->sliderData, $this->Faction->visibility);
        $menu->setTitle("Change the visibility ");
        return $menu;
    }
}