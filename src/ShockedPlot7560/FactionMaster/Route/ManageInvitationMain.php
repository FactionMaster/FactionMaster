<?php

namespace ShockedPlot7560\FactionMaster\Route;

use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\FormAPI;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Invitations\DemandList;
use ShockedPlot7560\FactionMaster\Route\Invitations\InvitationList;
use ShockedPlot7560\FactionMaster\Route\Invitations\ManageDemand;
use ShockedPlot7560\FactionMaster\Route\Invitations\ManageInvitation;
use ShockedPlot7560\FactionMaster\Route\Invitations\NewInvitation;
use ShockedPlot7560\FactionMaster\Route\MainPanel;
use ShockedPlot7560\FactionMaster\Route\Members\Invitations\MemberDemandList;
use ShockedPlot7560\FactionMaster\Route\Members\Invitations\MemberInvitationList;
use ShockedPlot7560\FactionMaster\Route\Members\Invitations\NewMemberInvitation;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageInvitationMain implements Route {

    const SLUG = "manageInvitationMain";

    public $PermissionNeed = [];
    public $backMenu;

    /** @var FormAPI */
    private $FormUI;
    /** @var array */
    private $buttons;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->FormUI = Main::getInstance()->FormUI;
        $this->backMenu = RouterFactory::get(MainPanel::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {        
        $this->buttons = [];
        $this->buttons[] = "Send an invitation";
        $this->buttons[] = "Invitation pending";
        $this->buttons[] = "Request pending";
        $this->buttons[] = "§4Back";

        $message = "";
        if (isset($params[0])) $message = $params[0];
        $menu = $this->manageMainMenu($message);
        $player->sendForm($menu);
    }

    public function call(): callable
    {
        $backMenu = $this->backMenu;
        return function (Player $player, $data) use ($backMenu) {
            if ($data === null) return;
            switch ($data) {
                case count($this->buttons) - 1:
                    Utils::processMenu($backMenu, $player);
                    break;
                case count($this->buttons) - 2:
                    Utils::processMenu(RouterFactory::get(DemandList::SLUG), $player);
                    break;
                case count($this->buttons) - 3:
                    Utils::processMenu(RouterFactory::get(InvitationList::SLUG), $player);
                    break;
                case count($this->buttons) - 4:
                    Utils::processMenu(RouterFactory::get(NewInvitation::SLUG), $player);
                    break;
                default:
                    return;
                    break;
            }
        };
    }

    private function manageMainMenu(string $message = "") : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle("Manage Invitations - Main");
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }

}