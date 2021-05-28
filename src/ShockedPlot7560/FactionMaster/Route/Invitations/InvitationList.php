<?php

namespace ShockedPlot7560\FactionMaster\Route\Invitations;

use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\FormAPI;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\ManageInvitationMain;
use ShockedPlot7560\FactionMaster\Route\Members\ManageMainMembers;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class InvitationList implements Route {

    const SLUG = "invitationList";

    public $PermissionNeed = [];
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
        $this->backMenu = RouterFactory::get(ManageInvitationMain::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->UserEntity = $User;
        $message = "";
        $this->Invitations = MainAPI::getInvitationsBySender($player->getName(), "member");
        $this->buttons = [];
        foreach ($this->Invitations as $Invitation) {
            $this->buttons[] = $Invitation->receiver;
        }
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_BACK");
        if (isset($params[0])) $message = $params[0];
        if (count($this->Invitations) == 0) $message .= Utils::getText($this->UserEntity->name, "NO_PENDING_INVITATION");
        $menu = $this->invitationList($message);
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
                Utils::processMenu(RouterFactory::get(ManageInvitation::SLUG), $player, [$this->Invitations[$data]]);
            }
            return;
        };
    }

    private function invitationList(string $message = "") : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle(Utils::getText($this->UserEntity->name, "INVITATION_LIST_TITLE"));
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }


}