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

class LanguagePanel implements Route {

    const SLUG = "languagePanel";

    public $PermissionNeed = [];
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
        $this->UserEntity = $User;
        $this->UserLang = MainAPI::getPlayerLang($player->getName());
        $this->Languages = Utils::getConfigLang("languages-name");

        $this->buttons = [];
        $i = 0;
        foreach ($this->Languages as $Name => $Langue) {
            if ($Name === $this->UserLang) {
                $this->buttons[] = $Langue . "\n" . Utils::getText($User->name, "CURRENT_LANG");
            }else{
                $this->buttons[] = $Langue;
            }
        }
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_BACK");

        $menu = $this->languagesMenu();
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
            $i = 0;
            foreach ($this->Languages as $key => $value) {
                if ($value === $this->buttons[$data]) {
                    $Lang = $key;
                }
                $i++;
            }
            if (isset($Lang)) {
                MainAPI::changeLanguage($player->getName(), $Lang);
                Utils::processMenu($backMenu, $player);
            }
            return;
        };
    }

    private function languagesMenu() : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle(Utils::getText($this->UserEntity->name, "CHANGE_LANGUAGE_TITLE"));
        return $menu;
    }

}