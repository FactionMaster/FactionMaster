<?php

namespace ShockedPlot7560\FactionMaster\Route\Invitations;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Route\ManageInvitationMain;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class DemandList implements Route {

    const SLUG = "demandList";

    public $PermissionNeed = [];
    public $backMenu;

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
        $this->backMenu = RouterFactory::get(ManageInvitationMain::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->UserEntity = $User;
        $message = "";
        $this->Invitations = MainAPI::getInvitationsByReceiver($player->getName(), "member");
        $this->buttons = [];
        foreach ($this->Invitations as $Invitation) {
            $this->buttons[] = $Invitation->sender;
        }
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_BACK");
        if (isset($params[0])) $message = $params[0];
        if (count($this->Invitations) == 0) $message .= Utils::getText($this->UserEntity->name, "NO_PENDING_REQUEST");
        $menu = $this->demandList($message);
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
                Utils::processMenu(RouterFactory::get(ManageDemand::SLUG), $player, [$this->Invitations[$data]]);
            }
            return;
        };
    }

    private function demandList(string $message = "") : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle(Utils::getText($this->UserEntity->name, "REQUEST_LIST_TITLE"));
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }


}