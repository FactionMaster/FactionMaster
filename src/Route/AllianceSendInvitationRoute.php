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

namespace ShockedPlot7560\FactionMaster\Route;

use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\AllianceCreateEvent;
use ShockedPlot7560\FactionMaster\Event\InvitationAcceptEvent;
use ShockedPlot7560\FactionMaster\Event\InvitationSendEvent;
use ShockedPlot7560\FactionMaster\libs\Vecnavium\FormsUI\CustomForm;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function count;
use function is_string;

class AllianceSendInvitationRoute extends RouteBase {
	/** @deprecated */
	const SLUG = "allianceSendInvitationRoute";

	public function getSlug(): string {
		return self::ALLIANCE_SEND_INVITATION_ROUTE;
	}

	public function getPermissions(): array {
		return [
			PermissionIds::PERMISSION_SEND_ALLIANCE_INVITATION
		];
	}

	public function getBackRoute(): ?Route {
		return RouterFactory::get(self::ALLIANCE_OPTION_ROUTE);
	}

	public function __invoke(Player $player, UserEntity $userEntity, array $userPermissions, ?array $params = null) {
		$this->init($player, $userEntity, $userPermissions, $params);

		$message = "";
		if (isset($params[0]) && is_string($params[0])) {
			$message = $params[0];
		}
		$player->sendForm($this->getForm($message));
	}

	public function call(): callable {
		return function (Player $player, $data) {
			if ($data === null) {
				return;
			}

			if ($data[1] !== "") {
				$targetName = $data[1];
				$factionRequested = MainAPI::getFaction($targetName);
				if ($factionRequested instanceof FactionEntity) {
					if (count($this->getFaction()->getAlly()) < $this->getFaction()->getMaxAlly()) {
						if (count($factionRequested->getAlly()) < $factionRequested->getMaxAlly()) {
							$faction = $this->getFaction();
							if (!$faction instanceof FactionEntity) {
								return;
							}
							if (MainAPI::areInInvitation($targetName, $faction->getName(), InvitationEntity::ALLIANCE_INVITATION)) {
								MainAPI::setAlly($targetName, $faction->getName());
								Utils::newMenuSendTask(new MenuSendTask(
									function () use ($targetName, $faction) {
										return MainAPI::isAlly($targetName, $faction->getName());
									},
									function () use ($faction, $targetName, $player, $factionRequested) {
										$event = new AllianceCreateEvent($player, $faction, $factionRequested);
										$event->call();
										$invit = MainAPI::getInvitationsBySender($targetName, "alliance")[0];
										MainAPI::removeInvitation($targetName, $faction->getName(), "alliance");
										Utils::newMenuSendTask(new MenuSendTask(
											function () use ($targetName, $faction) {
												return !MainAPI::areInInvitation($targetName, $faction->getName(), "alliance");
											},
											function () use ($invit, $player) {
												(new InvitationAcceptEvent($player, $invit))->call();
												Utils::processMenu($this->getBackRoute(), $player, [Utils::getText($player->getName(), "SUCCESS_ACCEPT_REQUEST", ['name' => $invit->sender])]);
											},
											function () use ($player) {
												Utils::processMenu($this, $player, [Utils::getText($player->getName(), "ERROR")]);
											}
										));
									},
									function () use ($player) {
										Utils::processMenu($this, $player, [Utils::getText($player->getName(), "ERROR")]);
									}
								));
							} elseif (!MainAPI::areInInvitation($faction->getName(), $targetName, InvitationEntity::ALLIANCE_INVITATION)) {
								MainAPI::makeInvitation($faction->getName(), $targetName, InvitationEntity::ALLIANCE_INVITATION);
								Utils::newMenuSendTask(new MenuSendTask(
									function () use ($faction, $targetName) {
										return MainAPI::areInInvitation($faction->getName(), $targetName, InvitationEntity::ALLIANCE_INVITATION);
									},
									function () use ($faction, $player, $targetName, $data) {
										$invitation = null;
										foreach (MainAPI::getInvitationsBySender($faction->getName(), InvitationEntity::ALLIANCE_INVITATION) as $invitations) {
											if ($invitations->getReceiverString() === $targetName) {
												$invitation = $invitations;
											}
										}
										(new InvitationSendEvent($player, $invitation))->call();
										Utils::processMenu($this->getBackRoute(), $player, [Utils::getText($player->getName(), "SUCCESS_SEND_INVITATION", ['name' => $data[1]])]);
									},
									function () use ($player) {
										Utils::processMenu($this, $player, [Utils::getText($player->getName(), "ERROR")]);
									}
								));
							} else {
								Utils::processMenu($this, $player, [Utils::getText($player->getName(), "ALREADY_PENDING_INVITATION")]);
							}
						} else {
							Utils::processMenu($this, $player, [Utils::getText($player->getName(), "MAX_ALLY_REACH_OTHER")]);
						}
					} else {
						Utils::processMenu($this, $player, [Utils::getText($player->getName(), "MAX_ALLY_REACH")]);
					}
				} else {
					Utils::processMenu($this, $player, [Utils::getText($player->getName(), "FACTION_DONT_EXIST")]);
				}
			} else {
				Utils::processMenu($this->getBackRoute(), $player);
			}
		};
	}

	protected function getForm(string $message = ""): CustomForm {
		$menu = new CustomForm($this->call());
		$menu->setTitle(Utils::getText($this->getUserEntity()->getName(), "SEND_INVITATION_PANEL_TITLE"));
		$menu->addLabel(Utils::getText($this->getUserEntity()->getName(), "SEND_INVITATION_PANEL_CONTENT") . "\n§r" . $message);
		$menu->addInput(Utils::getText($this->getUserEntity()->getName(), "SEND_INVITATION_PANEL_INPUT_CONTENT_FACTION"));
		return $menu;
	}
}