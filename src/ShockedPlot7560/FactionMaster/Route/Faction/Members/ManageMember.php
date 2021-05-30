<?php 

namespace ShockedPlot7560\FactionMaster\Route\Faction\Members;

use InvalidArgumentException;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Route\ConfirmationMenu;
use ShockedPlot7560\FactionMaster\Route\MainPanel;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageMember implements Route {
    
    const SLUG = "manageMember";

    public $PermissionNeed = [Ids::PERMISSION_CHANGE_MEMBER_RANK, Ids::PERMISSION_KICK_MEMBER];
    public $backMenu;

    /** @var array */
    private $buttons;
    /** @var UserEntity */
    private $victim;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->backMenu = RouterFactory::get(ManageMembersList::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->UserEntity = $User;
        if (!isset($params[0]) || !$params[0] instanceof UserEntity) throw new InvalidArgumentException("Need the target player instance");
        $this->victim = $params[0];

        $this->buttons = [];
        if ((isset($UserPermissions[Ids::PERMISSION_CHANGE_MEMBER_RANK]) && $UserPermissions[Ids::PERMISSION_CHANGE_MEMBER_RANK]) || $User->rank == Ids::OWNER_ID) $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_CHANGE_RANK");
        if ((isset($UserPermissions[Ids::PERMISSION_KICK_MEMBER]) && $UserPermissions[Ids::PERMISSION_KICK_MEMBER]) || $User->rank == Ids::OWNER_ID) $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_KICK_OUT");
        if ($User->rank == Ids::OWNER_ID) $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_TRANSFER_PROPERTY");
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_BACK");

        $menu = $this->manageMember();
        $player->sendForm($menu);
    }

    public function call(): callable
    {
        $victim = $this->victim;
        $backMenu = $this->backMenu;
        return function (Player $player, $data) use ($victim, $backMenu) {
            if ($data === null) return;
            switch ($this->buttons[$data]) {
                case Utils::getText($this->UserEntity->name, "BUTTON_BACK"):
                    Utils::processMenu($backMenu, $player);
                    break;
                case Utils::getText($this->UserEntity->name, "BUTTON_TRANSFER_PROPERTY"):
                    Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $player, [
                        $this->callTransferProperty($player->getName(), $victim->name),
                        Utils::getText($this->UserEntity->name, "CONFIRMATION_TITLE_TRANSFER_PROPERTY"),
                        Utils::getText($this->UserEntity->name, "CONFIRMATION_CONTENT_TRANSFER_PROPERTY")
                    ]);
                    break;
                case Utils::getText($this->UserEntity->name, "BUTTON_KICK_OUT"):
                    Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $player, [
                        $this->callKick($victim->name),
                        Utils::getText($this->UserEntity->name, "CONFIRMATION_TITLE_KICK_OUT"),
                        Utils::getText($this->UserEntity->name, "CONFIRMATION_CONTENT_KICK_OUT")
                    ]);
                    break;
                case Utils::getText($this->UserEntity->name, "BUTTON_CHANGE_RANK"):
                    Utils::processMenu(RouterFactory::get(MemberChangeRank::SLUG), $player, [ $victim ]);
                    break;
                default:
                    Utils::processMenu($this->backMenu, $player);
                    break;
            }
        };
    }

    private function manageMember() : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle(Utils::getText($this->UserEntity->name, "MANAGE_MEMBER_PANEL_TITLE", ['playerName' => $this->victim->name]));
        return $menu;
    }

    private function callTransferProperty(string $oldOwner, string $newOwner) : callable {
        return function (Player $Player, $data) use ($oldOwner, $newOwner) {
            if ($data === null) return;
            if ($data) {
                $message = Utils::getText($this->UserEntity->name, "SUCCESS_TRANSFER_PROPERTY", ['playerName' => $newOwner]);
                if (!MainAPI::changeRank($oldOwner, Ids::COOWNER_ID)) $message = Utils::getText($this->UserEntity->name, "ERROR"); 
                if (!MainAPI::changeRank($newOwner, Ids::OWNER_ID)) $message = Utils::getText($this->UserEntity->name, "ERROR");
                Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $Player, [$message]);
            }else{
                Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $Player);
            }
        };
    }

    private function callKick(string $targetName) : callable {
        return function (Player $Player, $data) use ($targetName) {
            $Faction = MainAPI::getFactionOfPlayer($Player->getName());
            if ($data === null) return;
            if ($data) {
                $message = Utils::getText($this->UserEntity->name, "SUCCESS_KICK_OUT", ['playerName' => $targetName]);
                if (!MainAPI::removeMember($Faction->name, $targetName)) $message = Utils::getText($this->UserEntity->name, "ERROR"); 
                Utils::processMenu($this->backMenu, $Player, [$message]);
            }else{
                Utils::processMenu(RouterFactory::get(self::SLUG), $Player);
            }
        };
    }
}