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
        $this->UserEntity = $User;

        $this->buttons = [];
        $this->Homes = MainAPI::getFactionHomes($Faction->name);
        var_dump($this->Homes);
        $i = 0;
        foreach ($this->Homes as $Name => $Home) {
            $Home['name'] = $Name;
            $this->Homes[$i] = $Home;
            $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_LISTING_HOME", [
                'name' => $Name,
                'x' => $Home['x'],
                'y' => $Home['y'],
                'z' => $Home['z']
            ]);
            $i++;
        }
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_BACK");

        if (isset($params[0])) $message = $params[0];
        if (count($Faction->members) == 0) $message .= Utils::getText($this->UserEntity->name, "NO_HOME_SET");
        
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
                $player->sendMessage(Utils::getText($this->UserEntity->name, "SUCCESS_TELEPORT_HOME"));
            }
            return;
        };
    }

    private function manageMembersListMenu(string $message = "") : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle(Utils::getText($this->UserEntity->name, "HOME_FACTION_PANEL_TITLE"));
        $content = Utils::getText($this->UserEntity->name, "HOME_FACTION_PANEL_CONTENT");
        if ($message !== "") $content .= ("\n§r" . $message);
        $menu->setContent($content);
        return $menu;
    }

}