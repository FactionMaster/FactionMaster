<?php 

namespace ShockedPlot7560\FactionMaster\Route\Members\Invitations;

use InvalidArgumentException;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\FormAPI;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\ConfirmationMenu;
use ShockedPlot7560\FactionMaster\Route\Members\Invitations\MemberInvitationList;
use ShockedPlot7560\FactionMaster\Route\Members\ManageMainMembers;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageMemberInvitation implements Route {
    
    const SLUG = "manageMemberInvitation";

    public $PermissionNeed = [
        Ids::PERMISSION_DELETE_PENDING_MEMBER_INVITATION
    ];
    public $backMenu;

    /** @var FormAPI */
    private $FormUI;
    /** @var array */
    private $buttons;
    /** @var InvitationEntity */
    private $invitation;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->FormUI = Main::getInstance()->FormUI;
        $this->backMenu = RouterFactory::get(MemberInvitationList::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->UserEntity = $User;
        if (!isset($params[0]) || !$params[0] instanceof InvitationEntity) throw new InvalidArgumentException("Need the invitation instance");
        $this->invitation = $params[0];

        $this->buttons = [];
        if ((array_key_exists(Ids::PERMISSION_DELETE_PENDING_MEMBER_INVITATION, $UserPermissions) && $UserPermissions[Ids::PERMISSION_DELETE_PENDING_MEMBER_INVITATION]) || $User->rank == Ids::OWNER_ID) $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_DELETE_INVITATION");
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_BACK");

        $menu = $this->manageMember();
        $player->sendForm($menu);
    }

    public function call(): callable
    {
        $invitation = $this->invitation;
        $backMenu = $this->backMenu;
        return function (Player $player, $data) use ($invitation, $backMenu) {
            if ($data === null) return;
            switch ($this->buttons[$data]) {
                case Utils::getText($this->UserEntity->name, "BUTTON_BACK"):
                    Utils::processMenu($backMenu, $player);
                    break;
                case Utils::getText($this->UserEntity->name, "BUTTON_DELETE_INVITATION"):
                    Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $player, [
                        $this->callDelete($invitation->sender, $invitation->receiver),
                        Utils::getText($this->UserEntity->name, "CONFIRMATION_TITLE_DELETE_INVITATION"),
                        Utils::getText($this->UserEntity->name, "CONFIRMATION_CONTENT_DELETE_INVITATION")
                    ]);
                    break;
                default:
                    return;
                    break;
            }
        };
    }

    private function manageMember() : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        if (count($this->buttons) == 1) {
            $menu->setContent(Utils::getText($this->UserEntity->name, "NO_ACTION_POSSIBLE"));
        }
        $menu->setTitle(Utils::getText($this->UserEntity->name, "MANAGE_MEMBER_INVITATION_TITLE", ['name' => $this->invitation->receiver]));
        return $menu;
    }

    private function callDelete(string $factionName, string $playerName) : callable {
        $invitation = $this->invitation;
        return function (Player $Player, $data) use ($factionName, $playerName, $invitation) {
            if ($data === null) return;
            if ($data) {
                $message = Utils::getText($this->UserEntity->name, "SUCCESS_DELETE_INVITATION", ['name' => $playerName]);
                if (!MainAPI::removeInvitation($factionName, $playerName, "member")) $message = Utils::getText($this->UserEntity->name, "ERROR"); 
                Utils::processMenu(RouterFactory::get(ManageMainMembers::SLUG), $Player, [$message]);
            }else{
                Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [$invitation]);
            }
        };
    }

}