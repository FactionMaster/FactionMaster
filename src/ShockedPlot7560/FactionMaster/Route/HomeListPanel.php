<?php

namespace ShockedPlot7560\FactionMaster\Route;

use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\FormAPI;
use pocketmine\math\Vector3;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class HomeListPanel implements Route {

    const SLUG = "homeListPanel";

    public $PermissionNeed = [Ids::PERMISSION_TP_FACTION_HOME];
    public $backMenu;

    /** @var FormAPI */
    private $FormUI;
    /** @var array */
    private $buttons;
    /** @var array[] */
    private $Homes;

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
        $this->Homes = MainAPI::getFactionHomes($Faction->name);
        var_dump($this->Homes);
        $i = 0;
        foreach ($this->Homes as $Name => $Home) {
            $Home['name'] = $Name;
            $this->Homes[$i] = $Home;
            $this->buttons[] = $Name . "\nx:" . $Home['x'] . " | y:" . $Home['y'] ." | z:" . $Home['z'];
            $i++;
        }
        $this->buttons[] = "§4Back";

        if (isset($params[0])) $message = $params[0];
        if (count($Faction->members) == 0) $message .= "\n \n§4No home was set";
        
        $menu = $this->manageMembersListMenu($message);
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
            if (isset($this->Homes[$data])) {
                $Home = $this->Homes[$data];
                $player->teleport(new Vector3($Home["x"], $Home["y"], $Home['z']));
                $player->sendMessage(" §a>> §2You have been teleport to the home");
            }
            return;
        };
    }

    private function manageMembersListMenu(string $message = "") : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle("Home list");
        if ($message !== "") $menu->setContent("§7Click on the home that you want to teleport\n §r" . $message);
        return $menu;
    }

}