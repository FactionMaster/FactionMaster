<?php

namespace ShockedPlot7560\FactionMaster\Route;

use InvalidArgumentException;
use jojoe77777\FormAPI\FormAPI;
use jojoe77777\FormAPI\ModalForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Main;

class ConfirmationMenu implements Route {

    const SLUG = "confirmationMenu";
    
    /** @var \jojoe77777\FormAPI\FormAPI */
    private $FormUI;

    private $params;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $Main = Main::getInstance();
        $this->FormUI = $Main->FormUI;
    }

    /**
     * @param Player $player 
     * @param array|null $params 
     *      Give to first item of the list, a callable to call when the confirmation are send
     *      At the second, the title
     *      At the third, the content
     */
    public function __invoke(Player $player, ?array $params = null)
    {
        $this->params = $params;
        $menu = $this->confirmationMenu($params[1], $params[2]);
        $menu->sendToPlayer($player);
    }

    public function call(): callable
    {
        return $this->params[0];
    }

    private function confirmationMenu(string $title, string $content) : ModalForm {
        $menu = $this->FormUI->createModalForm($this->call());
        $menu->setTitle($title);
        $menu->setContent($content);
        $menu->setButton1("§2Yes");
        $menu->setButton2("§4No");
        return $menu;
    }

}