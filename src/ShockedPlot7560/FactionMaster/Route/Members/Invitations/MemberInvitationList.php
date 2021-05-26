<?php

namespace ShockedPlot7560\FactionMaster\Route\Members\Invitations;

use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\FormAPI;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Members\ManageMainMembers;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class MemberInvitationList implements Route {

    const SLUG = "memberInvitationList";

    public $PermissionNeed = [
        Ids::PERMISSION_DELETE_PENDING_MEMBER_INVITATION
    ];
    public $backMenu;

    /** @var FormAPI */
    private $FormUI;
    /** @var array */
    private $buttons;
    /** @var InvitationEntity[] */
    private $Invitations;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->FormUI = Main::getInstance()->FormUI;
        $this->backMenu = RouterFactory::get(ManageMainMembers::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $message = "";
        $Faction = MainAPI::getFactionOfPlayer($player->getName());
        $this->Invitations = MainAPI::getInvitationsBySender($Faction->name, "member");
        $this->buttons = [];
        foreach ($this->Invitations as $Invitation) {
            $this->buttons[] = $Invitation->receiver;
        }
        $this->buttons[] = "§4Back";
        if (isset($params[0])) $message = $params[0];
        if (count($this->Invitations) == 0) $message .= "\n \n§4No pending invitations";
        $menu = $this->memberInvitationList($message);
        $player->sendForm($menu);
    }

    public function call(): callable
    {
        $backMenu = $this->backMenu;
        return function (Player $player, $data) use ($backMenu) {
            if ($data === null) return;
            if ($data == count($this->buttons) - 1) {
                Utils::processMenu($backMenu, $player);
                return;
            }
            if (isset($this->buttons[$data])) {
                Utils::processMenu(RouterFactory::get(ManageMemberInvitation::SLUG), $player, [$this->Invitations[$data]]);
            }
            return;
        };
    }

    private function memberInvitationList(string $message = "") : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle("Invitations list");
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }


}