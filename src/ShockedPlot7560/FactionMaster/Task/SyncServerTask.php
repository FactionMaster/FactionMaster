<?php

namespace ShockedPlot7560\FactionMaster\Task;

use PDO;
use pocketmine\scheduler\Task;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Database;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\HomeEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Database\Table\FactionTable;
use ShockedPlot7560\FactionMaster\Database\Table\HomeTable;
use ShockedPlot7560\FactionMaster\Database\Table\InvitationTable;
use ShockedPlot7560\FactionMaster\Database\Table\UserTable;

class SyncServerTask extends Task {

    public function onRun(int $currentTick): void {
        $db = MainAPI::$PDO;
        $data = [];
        
        $query = $db->prepare("SELECT * FROM " . FactionTable::TABLE_NAME);
        $query->execute();
        $query->setFetchMode(PDO::FETCH_CLASS, FactionEntity::class);
        $data["faction"] = $query->fetchAll();
        
        $query = $db->prepare("SELECT * FROM " . InvitationTable::TABLE_NAME);
        $query->execute();
        $query->setFetchMode(PDO::FETCH_CLASS, InvitationEntity::class);
        $data["invitation"] = $query->fetchAll();
        
        $query = $db->prepare("SELECT * FROM " . UserTable::TABLE_NAME);
        $query->execute();
        $query->setFetchMode(PDO::FETCH_CLASS, UserEntity::class);
        $data["user"] = $query->fetchAll();
        
        $query = $db->prepare("SELECT * FROM " . HomeTable::TABLE_NAME);
        $query->execute();
        $query->setFetchMode(PDO::FETCH_CLASS, HomeEntity::class);
        $data["home"] = $query->fetchAll();

        foreach ($data as $type => $data2) {
            foreach ($data2 as $dat) {
                switch ($type) {
                    case 'faction':
                        if ($dat instanceof FactionEntity) MainAPI::$factions[$dat->name] = $dat;
                        break;
                    case "invitation":
                        if ($dat instanceof InvitationEntity) MainAPI::$invitation[$dat->sender . "|" . $dat->receiver] = $dat;
                        break;
                    case "user":
                        if ($dat instanceof UserEntity) MainAPI::$users[$dat->name] = $dat;
                        break;
                    case "home":
                        if ($dat instanceof HomeEntity) MainAPI::$home[$dat->faction][$dat->name] = $dat;
                        break;
                }            
            }
        }
    }
}