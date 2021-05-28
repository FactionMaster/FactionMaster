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
    private $sliderData;
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
        $this->UserEntity = $User;
        $this->sliderData = [
            Ids::PUBLIC_VISIBILITY => Utils::getText($this->UserEntity->name, "PUBLIC_VISIBILITY_NAME"),
            Ids::PRIVATE_VISIBILITY => Utils::getText($this->UserEntity->name, "PRIVATE_VISIBILITY_NAME"),
            Ids::INVITATION_VISIBILITY => Utils::getText($this->UserEntity->name, "INVITATION_VISIBILITY_NAME")
        ];
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
                Utils::processMenu($backMenu,  $player, [Utils::getText($this->UserEntity->name, "SUCCESS_VISIBILITY_UPDATE")]);
            }else{
                Utils::processMenu($backMenu, $player, [Utils::getText($this->UserEntity->name, "ERROR")]);
            }
        };
    }

    private function changeVisibility() : CustomForm {
        $menu = new CustomForm($this->call());
        $menu->addStepSlider(Utils::getText($this->UserEntity->name, "CHANGE_VISIBILITY_STEP"), $this->sliderData, $this->Faction->visibility);
        $menu->addLabel(Utils::getText($this->UserEntity->name, "CHANGE_VISIBILITY_EXPLICATION"));
        $menu->setTitle(Utils::getText($this->UserEntity->name, "CHANGE_VISIBILITY_TITLE"));
        return $menu;
    }
}