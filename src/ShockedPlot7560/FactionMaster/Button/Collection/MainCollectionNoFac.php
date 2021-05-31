<?php

namespace ShockedPlot7560\FactionMaster\Button\Collection;

use ShockedPlot7560\FactionMaster\Button\Button;
use ShockedPlot7560\FactionMaster\Button\ButtonCollection;
use ShockedPlot7560\FactionMaster\Route\CreateFactionPanel;
use ShockedPlot7560\FactionMaster\Route\LanguagePanel;
use ShockedPlot7560\FactionMaster\Route\ManageInvitationMain;
use ShockedPlot7560\FactionMaster\Route\TopFactionPanel;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class MainCollectionNoFac extends ButtonCollection {

    const SLUG = "mainNoFac";

    public function __construct()
    {
        parent::__construct(self::SLUG);
        $this->register(new Button("createFaction", "BUTTON_CREATE_FACTION", function($Player) {
            Utils::processMenu(RouterFactory::get(CreateFactionPanel::SLUG), $Player);
        }));
        $this->register(new Button("joinFaction", "BUTTON_JOIN_FACTION", function($Player) {
            Utils::processMenu(RouterFactory::get(ManageInvitationMain::SLUG), $Player);
        }));
        $this->register(new Button("topFaction", "BUTTON_TOP_FACTION", function($Player) {
            Utils::processMenu(RouterFactory::get(TopFactionPanel::SLUG), $Player);
        }));
        $this->register(new Button("changeLanguage", "BUTTON_CHANGE_LANGUAGE", function($Player) {
            Utils::processMenu(RouterFactory::get(LanguagePanel::SLUG), $Player);
        }));
        $this->register(new Button("quit", "BUTTON_QUIT", function($Player) {
            return;
        }));
    }

}