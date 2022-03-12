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
use ShockedPlot7560\FactionMaster\Event\FactionJoinEvent;
use ShockedPlot7560\FactionMaster\Event\InvitationAcceptEvent;
use ShockedPlot7560\FactionMaster\Event\InvitationSendEvent;
use ShockedPlot7560\FactionMaster\libs\Vecnavium\FormsUI\CustomForm;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function count;
use function is_string;

class JoinSendInvitationRoute extends RouteBase {
	/** @deprecated */
	const SLUG = "joinSendInvitationRoute";

	public function getSlug(): string {
		return self::JOIN_SEND_INVITATION_ROUTE;
	}

	public function getPermissions(): array {
		return [];
	}

	public function getBackRoute(): ?Route {
		return RouterFactory::get(self::JOIN_FACTION_ROUTE);
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
					if (count($factionRequested->getMembers()) < $factionRequested->getMaxPlayer()) {
						if (!MainAPI::getFactionOfPlayer($player->getName()) instanceof FactionEntity) {
							switch ($factionRequested->getVisibilityId()) {
							case Ids::PUBLIC_VISIBILITY:
								MainAPI::addMember($factionRequested->getName(), $player->getName());
								Utils::newMenuSendTask(new MenuSendTask(
									function () use ($player, $factionRequested) {
										return MainAPI::getUser($player->getName())->faction === $factionRequested->getName();
									},
									function () use ($player, $factionRequested) {
										(new FactionJoinEvent($player, $factionRequested))->call();
										Utils::processMenu(RouterFactory::get(self::MAIN_ROUTE), $player, [Utils::getText($player->getName(), "SUCCESS_JOIN_FACTION", ['factionName' => $factionRequested->getName()])]);
									},
									function () use ($player) {
										Utils::processMenu($this, $player, [Utils::getText($player->getName(), "ERROR")]);
									}
								));
								if (MainAPI::areInInvitation($factionRequested->getName(), $player->getName(), "member")) {
									MainAPI::removeInvitation($factionRequested->getName(), $player->getName(), "member");
								} elseif (MainAPI::areInInvitation($player->getName(), $factionRequested->getName(), "member")) {
									MainAPI::removeInvitation($player->getName(), $factionRequested->getName(), "member");
								}
								break;
							case Ids::PRIVATE_VISIBILITY:
								Utils::processMenu($this, $player, [Utils::getText($player->getName(), "FACTION_DONT_ACCEPT_INVITATION")]);
								break;
							case Ids::INVITATION_VISIBILITY:
								if (MainAPI::areInInvitation($targetName, $player->getName(), InvitationEntity::MEMBER_INVITATION)) {
									MainAPI::addMember($targetName, $player->getName());
									Utils::newMenuSendTask(new MenuSendTask(
										function () use ($targetName, $player) {
											return MainAPI::getUser($player->getName())->faction === $targetName;
										},
										function () use ($player, $factionRequested) {
											(new FactionJoinEvent($player, $factionRequested))->call();
											$request = MainAPI::$invitation[$factionRequested->getName() . "|" . $player->getName() . "|" . InvitationEntity::MEMBER_INVITATION];
											MainAPI::removeInvitation($factionRequested->getName(), $player->getName(), "member");
											Utils::newMenuSendTask(new MenuSendTask(
												function () use ($factionRequested, $player) {
													return !MainAPI::areInInvitation($factionRequested->getName(), $player->getName(), "member");
												},
												function () use ($request, $player) {
													(new InvitationAcceptEvent($player, $request))->call();
													Utils::processMenu(RouterFactory::get(self::MAIN_ROUTE), $player, [Utils::getText($player->getName(), "SUCCESS_JOIN_FACTION", ['factionName' => $request->getSenderString()])]);
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
								} elseif (!MainAPI::areInInvitation($player->getName(), $targetName, InvitationEntity::MEMBER_INVITATION)) {
									MainAPI::makeInvitation($player->getName(), $targetName, InvitationEntity::MEMBER_INVITATION);
									Utils::newMenuSendTask(new MenuSendTask(
										function () use ($player, $targetName) {
											return MainAPI::areInInvitation($player->getName(), $targetName, InvitationEntity::MEMBER_INVITATION);
										},
										function () use ($player, $targetName) {
											$invitation = null;
											foreach (MainAPI::getInvitationsBySender($player->getName(), InvitationEntity::MEMBER_INVITATION) as $invitations) {
												if ($invitations->getReceiverString() === $targetName) {
													$invitation = $invitations;
												}
											}
											(new InvitationSendEvent($player, $invitation))->call();
											Utils::processMenu($this->getBackRoute(), $player, [Utils::getText($player->getName(), "SUCCESS_SEND_INVITATION", ['name' => $targetName])]);
										},
										function () use ($player) {
											Utils::processMenu($this, $player, [Utils::getText($player->getName(), "ERROR")]);
										}
									));
								} else {
									Utils::processMenu($this, $player, [Utils::getText($player->getName(), "ALREADY_PENDING_INVITATION")]);
								}
								break;
							}
						} else {
							Utils::processMenu($this, $player, [Utils::getText($player->getName(), "ALREADY_IN_THIS_FACTION")]);
						}
					} else {
						Utils::processMenu($this, $player, [Utils::getText($player->getName(), "MAX_PLAYER_REACH")]);
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
		$menu->addLabel(Utils::getText($this->getUserEntity()->getName(), "SEND_INVITATION_PANEL_CONTENT") . "\n" . $message);
		$menu->addInput(Utils::getText($this->getUserEntity()->getName(), "SEND_INVITATION_PANEL_INPUT_CONTENT_FACTION"));
		return $menu;
	}
}