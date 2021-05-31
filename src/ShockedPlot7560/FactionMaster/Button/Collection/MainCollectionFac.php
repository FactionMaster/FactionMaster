<?php

namespace ShockedPlot7560\FactionMaster\Button\Collection;

use onebone\economyapi\EconomyAPI;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Button\Button;
use ShockedPlot7560\FactionMaster\Button\ButtonCollection;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\ConfirmationMenu;
use ShockedPlot7560\FactionMaster\Route\CreateFactionPanel;
use ShockedPlot7560\FactionMaster\Route\Faction\BankMain;
use ShockedPlot7560\FactionMaster\Route\Faction\Manage\ManageFactionMain;
use ShockedPlot7560\FactionMaster\Route\Faction\Members\ManageMainMembers;
use ShockedPlot7560\FactionMaster\Route\Faction\ViewFactionMembers;
use ShockedPlot7560\FactionMaster\Route\HomeListPanel;
use ShockedPlot7560\FactionMaster\Route\LanguagePanel;
use ShockedPlot7560\FactionMaster\Route\MainPanel;
use ShockedPlot7560\FactionMaster\Route\ManageInvitationMain;
use ShockedPlot7560\FactionMaster\Route\TopFactionPanel;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class MainCollectionFac extends ButtonCollection {

    const SLUG = "mainFac";

    public function __construct()
    {
        parent::__construct(self::SLUG);
        $this->register(new Button("factionMembers", "BUTTON_VIEW_FACTION_MEMBERS", function($Player) {
            Utils::processMenu(RouterFactory::get(ViewFactionMembers::SLUG), $Player);
        }));

        $this->register(new Button("factionHome", "BUTTON_VIEW_FACTION_HOME", function($Player) {
            Utils::processMenu(RouterFactory::get(HomeListPanel::SLUG), $Player);
        }));

        if (Main::getInstance()->EconomyAPI instanceof EconomyAPI) {
            $this->register(new Button("bank", "BUTTON_VIEW_BANK", function($Player) {
                Utils::processMenu(RouterFactory::get(BankMain::SLUG), $Player);
            }, [
                Ids::PERMISSION_BANK_DEPOSIT,
                Ids::PERMISSION_SEE_BANK_HISTORY
            ]));
        }
        
        $this->register(new Button("manageMembers", "BUTTON_MANAGE_MEMBERS", function($Player) {
            Utils::processMenu(RouterFactory::get(ManageMainMembers::SLUG), $Player);
        }, [
            Ids::PERMISSION_ACCEPT_MEMBER_DEMAND,
            Ids::PERMISSION_REFUSE_MEMBER_DEMAND,
            Ids::PERMISSION_DELETE_PENDING_MEMBER_INVITATION,
            Ids::PERMISSION_KICK_MEMBER,
            Ids::PERMISSION_CHANGE_MEMBER_RANK,
            Ids::PERMISSION_SEND_MEMBER_INVITATION
        ]));

        $this->register(new Button("manageFaction", "BUTTON_MANAGE_FACTION", function($Player) {
            Utils::processMenu(RouterFactory::get(ManageFactionMain::SLUG), $Player);
        }, [
            Ids::PERMISSION_BREAK_ALLIANCE,
            Ids::PERMISSION_SEND_ALLIANCE_INVITATION,
            Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND,
            Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND,
            Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION,
            Ids::PERMISSION_CHANGE_FACTION_DESCRIPTION,
            Ids::PERMISSION_CHANGE_FACTION_MESSAGE,
            Ids::PERMISSION_CHANGE_FACTION_VISIBILITY,
            Ids::PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS
        ]));

        $this->register(new Button("factionsTop", "BUTTON_TOP_FACTION", function($Player) {
            Utils::processMenu(RouterFactory::get(TopFactionPanel::SLUG), $Player);
        }));

        $this->register(new Button("changeLanguage", "BUTTON_CHANGE_LANGUAGE", function($Player) {
            Utils::processMenu(RouterFactory::get(LanguagePanel::SLUG), $Player);
        }), -3);

        $this->register(new Button("leavingButton", "BUTTON_LEAVE_DELETE_FACTION", $this->leaveDeleteButtonFunction()));

        $this->register(new Button("quit", "BUTTON_QUIT", function($Player) {
            return;
        }));
    }

    private function leaveDeleteButtonFunction() : callable {
        return function(Player $Player) {
            $UserEntity = MainAPI::getUser($Player->getName());
            $Faction = MainAPI::getFactionOfPlayer($Player->getName());
            if ($UserEntity->rank === Ids::OWNER_ID) {
                Utils::processMenu(
                    RouterFactory::get(ConfirmationMenu::SLUG), 
                    $Player, 
                    [
                        $this->callConfirmDelete($Faction),
                        Utils::getText($Player->getName(), "CONFIRMATION_TITLE_DELETE_FACTION", ['factionName' => $Faction->name]),
                        Utils::getText($Player->getName(), "CONFIRMATION_CONTENT_DELETE_FACTION")
                    ]
                );
            }else{
                $Faction = MainAPI::getFactionOfPlayer($Player->getName());
                Utils::processMenu(
                    RouterFactory::get(ConfirmationMenu::SLUG), 
                    $Player, 
                    [
                        $this->callConfirmLeave($Faction),
                        Utils::getText($Player->getName(), "CONFIRMATION_TITLE_LEAVE_FACTION", ['factionName' => $Faction->name]),
                        Utils::getText($Player->getName(), "CONFIRMATION_CONTENT_LEAVE_FACTION")
                    ]
                );
            }
        };
    }

    private function callConfirmLeave(FactionEntity $Faction) : callable {
        return function (Player $Player, $data) use ($Faction) {
            if ($data === null) return;
            if ($data) {
                $message = Utils::getText($Player->getName(), "SUCCESS_LEAVE_FACTION");
                if (!MainAPI::removeMember($Faction->name, $Player->getName())) $message = Utils::getText($Player->getName(), "ERROR");
                Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $Player, [$message]);
            }else{
                Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $Player);
            }
        };
    }

    private function callConfirmDelete(FactionEntity $Faction) : callable {
        return function (Player $Player, $data) use ($Faction) {
            if ($data === null) return;
            if ($data) {
                $message = Utils::getText($Player->getName(), "SUCCESS_DELETE_FACTION");
                if (!MainAPI::removeFaction($Faction->name)) $message = Utils::getText($Player->getName(), "ERROR");
                Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $Player, [$message]);
            }else{
                Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $Player);
            }
        };
    }
}