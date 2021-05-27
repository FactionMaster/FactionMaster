<?php

namespace ShockedPlot7560\FactionMaster\Route;

use InvalidArgumentException;
use jojoe77777\FormAPI\FormAPI;
use jojoe77777\FormAPI\ModalForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;

class ConfirmationMenu implements Route {

    const SLUG = "confirmationMenu";

    public $PermissionNeed = [];
    public $backMenu;
    
    /** @var FormAPI */
    private $FormUI;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->FormUI = Main::getInstance()->FormUI;
    }

    /**
     * @param Player $player 
     * @param array|null $params 
     *      Give to first item of the list, a callable to call when the confirmation are send
     *      At the second, the title
     *      At the third, the content
     *      (Optionnal) at the fourth, array with for the first item -> message for positive answer and second the negative
     */
    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        if (!isset($params[0])) throw new InvalidArgumentException("First item must be set");

        $this->backMenu = $params[0];

        $menu = $this->confirmationMenu($params);
        $player->sendForm($menu);
    }

    public function call(): callable
    {
        return $this->backMenu;
    }

    private function confirmationMenu(array $params) : ModalForm {
        $menu = new ModalForm($this->call());
        $menu->setTitle($params[1]);
        $menu->setContent($params[2]);
        $menu->setButton1(isset($params[3]) ? $params[3] : "§2Yes");
        $menu->setButton2(isset($params[4]) ? $params[4] : "§4No");
        return $menu;
    }

}