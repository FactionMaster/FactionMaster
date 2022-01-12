<?php

/*
 *
 *      ______           __  _                __  ___           __
 *     / ____/___ ______/ /_(_)___  ____     /  |/  /___ ______/ /____  _____
 *    / /_  / __ `/ ___/ __/ / __ \/ __ \   / /|_/ / __ `/ ___/ __/ _ \/ ___/
 *   / __/ / /_/ / /__/ /_/ / /_/ / / / /  / /  / / /_/ (__  ) /_/  __/ /
 *  /_/    \__,_/\___/\__/_/\____/_/ /_/  /_/  /_/\__,_/____/\__/\___/_/
 *
 * FactionMaster - A Faction plugin for PocketMine-MP
 * This file is part of FactionMaster
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author ShockedPlot7560
 * @link https://github.com/ShockedPlot7560
 *
 *
 */

namespace ShockedPlot7560\FactionMaster\Button;

use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Database\Table\FactionTable;
use ShockedPlot7560\FactionMaster\Database\Table\UserTable;
use ShockedPlot7560\FactionMaster\Event\FactionPropertyTransferEvent;
use ShockedPlot7560\FactionMaster\Event\MemberChangeRankEvent;
use ShockedPlot7560\FactionMaster\FactionMaster as Main;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Route\RouteSlug;
use ShockedPlot7560\FactionMaster\Task\DatabaseTask;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function json_encode;

class TransferProperty extends Button {
	public function __construct(UserEntity $member) {
		$this->setSlug(self::TRANSFER_PROPERTY)
			->setContent(function (string $player) {
				return Utils::getText($player, "BUTTON_TRANSFER_PROPERTY");
			})
			->setCallable(function (Player $player) use ($member) {
				Utils::processMenu(RouterFactory::get(RouteSlug::CONFIRMATION_ROUTE), $player, [
					function (Player $player, $data) use ($member) {
						if ($data === null) {
							return;
						}

						if ($data) {
							$message = Utils::getText($player->getName(), "SUCCESS_TRANSFER_PROPERTY", ['playerName' => $member->getName()]);
							$faction = MainAPI::getFactionOfPlayer($player->getName());
							$faction->setMemberRank($player->getName(), Ids::COOWNER_ID);
							$faction->setMemberRank($member->getName(), Ids::OWNER_ID);
							Main::getInstance()->getServer()->getAsyncPool()->submitTask(
								new DatabaseTask(
									"UPDATE " . FactionTable::TABLE_NAME . " SET members = :members WHERE name = :name",
									[
										'members' => json_encode($faction->getMembers()),
										'name' => $faction->getName(),
									],
									function () use ($faction) {
										MainAPI::$factions[$faction->getName()] = $faction;
									}
								)
							);
							$user = MainAPI::getUser($player->getName());
							$user->setRank(Ids::COOWNER_ID);
							Main::getInstance()->getServer()->getAsyncPool()->submitTask(
								new DatabaseTask(
									"UPDATE " . UserTable::TABLE_NAME . " SET rank = :rank WHERE name = :name",
									[
										'rank' => Ids::COOWNER_ID,
										'name' => $user->getName(),
									],
									function () use ($user, $faction) {
										MainAPI::$users[$user->getName()] = $user;
										(new MemberChangeRankEvent($faction, $user, Ids::OWNER_ID))->call();
									}
								)
							);
							$userj = MainAPI::getUser($member->getName());
							$oldRank = $userj->getRank();
							$userj->setRank(Ids::OWNER_ID);
							Main::getInstance()->getServer()->getAsyncPool()->submitTask(
								new DatabaseTask(
									"UPDATE " . UserTable::TABLE_NAME . " SET rank = :rank WHERE name = :name",
									[
										'rank' => Ids::OWNER_ID,
										'name' => $userj->getName(),
									],
									function () use ($userj, $user, $player, $member, $message, $faction, $oldRank) {
										MainAPI::$users[$userj->getName()] = $userj;
										MainAPI::$users[$user->getName()] = $user;
										(new MemberChangeRankEvent($faction, $userj, $oldRank))->call();
										(new FactionPropertyTransferEvent($player, $faction, $member))->call();
										Utils::processMenu(RouterFactory::get(RouteSlug::MAIN_ROUTE), $player, [$message]);
									}
								)
							);
						} else {
							Utils::processMenu(RouterFactory::get(RouteSlug::MANAGE_MEMBER_ROUTE), $player, [$member]);
						}
					},
					Utils::getText($player->getName(), "CONFIRMATION_TITLE_TRANSFER_PROPERTY"),
					Utils::getText($player->getName(), "CONFIRMATION_CONTENT_TRANSFER_PROPERTY"),
				]);
			})
			->setImgPack("textures/img/transfer");
	}
}