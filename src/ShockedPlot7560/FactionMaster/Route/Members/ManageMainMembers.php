<?php

namespace ShockedPlot7560\FactionMaster\Route\Members;

use InvalidArgumentException;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\MainPanel;
use ShockedPlot7560\FactionMaster\Route\Members\Invitations\MemberDemandList;
use ShockedPlot7560\FactionMaster\Route\Members\Invitations\MemberInvitationList;
use ShockedPlot7560\FactionMaster\Route\Members\Invitations\NewMemberInvitation;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageMainMembers implements Route {

    const SLUG = "manageMainMembers";

    /** @var \jojoe77777\FormAPI\FormAPI */
    private $FormUI;
    /** @var array */
    private $buttons;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $Main = Main::getInstance();
        $this->FormUI = $Main->FormUI;
    }

    public function __invoke(Player $player, ?array $params = null)
    {
        $permissions = MainAPI::getMemberPermission($player->getName());
        $UserEntity = MainAPI::getUser($player->getName());
        
        $this->buttons = [];
        if ((array_key_exists(Ids::PERMISSION_SEND_MEMBER_INVITATION, $permissions) && $permissions[Ids::PERMISSION_SEND_MEMBER_INVITATION]) || $UserEntity->rank == Ids::OWNER_ID) $this->buttons[] = "Send an invitation";
        $this->buttons[] = "Invitation pending";
        $this->buttons[] = "Request pending";
        $this->buttons[] = "Manage members";
        $this->buttons[] = "§4Back";

        $message = "";
        if (isset($params[0])) $message = $params[0];
        $menu = $this->manageMainMembersMenu($message);
        $menu->sendToPlayer($player);
    }

    public function call(): callable
    {
        return function (Player $player, $data) {
            if ($data === null) return;
            switch ($data) {
                case count($this->buttons) - 1:
                    Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $player);
                    break;
                case count($this->buttons) - 2:
                    Utils::processMenu(RouterFactory::get(ManageMembersList::SLUG), $player);
                    break;
                case count($this->buttons) - 3:
                    Utils::processMenu(RouterFactory::get(MemberDemandList::SLUG), $player);
                    break;
                case count($this->buttons) - 4:
                    Utils::processMenu(RouterFactory::get(MemberInvitationList::SLUG), $player);
                    break;
                case count($this->buttons) - 5:
                    Utils::processMenu(RouterFactory::get(NewMemberInvitation::SLUG), $player);
                    break;
                default:
                    return;
                    break;
            }
        };
    }

    private function manageMainMembersMenu(string $message = "") : SimpleForm {
        $menu = $this->FormUI->createSimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle("Manage members - Main");
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }

}