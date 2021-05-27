<?php

namespace ShockedPlot7560\FactionMaster\Route\Faction;

use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\FormAPI;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\MainPanel;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ViewFactionMembers implements Route {

    const SLUG = "viewFactionMembers";

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
        $message = "";
        $Faction = MainAPI::getFactionOfPlayer($player->getName());
        $UserEntity = $User;

        $this->buttons = [];
        foreach ($Faction->members as $key => $Member) {
            $text = $key . "\n";
            switch ($Member) {
                case Ids::RECRUIT_ID:
                    $text .= "§7Recruit";
                    break;
                case Ids::MEMBER_ID:
                    $text .= "§7Member";
                    break;
                case Ids::COOWNER_ID:
                    $text .= "§7Co-owner";
                    break;
                case Ids::OWNER_ID:
                    $text .= "§7Owner";
                    break;
            }
            $this->buttons[] = $text;
        }
        $this->buttons[] = "§4Back";

        if (isset($params[0])) $message = $params[0];
        if (count($Faction->members) == 0) $message .= "\n \n§4No members to display";
        
        $menu = $this->membersListMenu($message);
        $player->sendForm($menu);
    }

    public function call(): callable
    {
        $backMenu = $this->backMenu;
        return function (Player $player, $data) use ($backMenu) {
            if ($data === null) return;
            if ($data == count($this->buttons) - 1) {
                Utils::processMenu($backMenu, $player);
            }else{
                Utils::processMenu(RouterFactory::get(self::SLUG), $player);
            }
            return;
        };
    }

    private function membersListMenu(string $message = "") : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle("Members list");
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }

}