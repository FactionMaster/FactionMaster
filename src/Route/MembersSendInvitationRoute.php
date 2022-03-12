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
use pocketmine\Server;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\FactionJoinEvent;
use ShockedPlot7560\FactionMaster\Event\InvitationAcceptEvent;
use ShockedPlot7560\FactionMaster\Event\InvitationSendEvent;
use ShockedPlot7560\FactionMaster\libs\Vecnavium\FormsUI\CustomForm;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function count;
use function is_string;

class MembersSendInvitationRoute extends RouteBase {
	/** @deprecated */
	const SLUG = "membersSendInvitationRoute";

	public function getSlug(): string {
		return self::MEMBERS_SEND_INVITATION_ROUTE;
	}

	public function getPermissions(): array {
		return [
			PermissionIds::PERMISSION_SEND_MEMBER_INVITATION
		];
	}

	public function getBackRoute(): ?Route {
		return RouterFactory::get(self::MEMBERS_OPTION_ROUTE);
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
			if ($data === null || count($data) == 0) {
				return;
			}

			if ($data[1] !== "") {
				$targetName = Server::getInstance()->getPlayerExact($data[1]);
				$targetName = $targetName === null ? $data[1] : $targetName->getName();
				$userRequested = MainAPI::getUser($targetName);
				$faction = $this->getFaction();
				if (count($faction->getMembers()) >= $faction->getMaxPlayer()) {
					Utils::processMenu($this, $player, [Utils::getText($this->getUserEntity()->getName(), "MAX_PLAYER_REACH")]);
					return;
				}
				if ($userRequested instanceof UserEntity) {
					$factionName = $faction->getName();
					if (!MainAPI::getFactionOfPlayer($targetName) instanceof FactionEntity) {
						if (!MainAPI::areInInvitation($factionName, $targetName, InvitationEntity::MEMBER_INVITATION)) {
							if (MainAPI::areInInvitation($targetName, $factionName, InvitationEntity::MEMBER_INVITATION)) {
								MainAPI::addMember($factionName, $userRequested->getName());
								Utils::newMenuSendTask(new MenuSendTask(
									function () use ($userRequested) {
										return MainAPI::getUser($userRequested->getName())->getFactionName() !== null;
									},
									function () use ($userRequested, $player, $faction, $factionName) {
										(new FactionJoinEvent($userRequested, $faction))->call();
										$request = MainAPI::$invitation[$userRequested->getName() . "|" . $factionName . "|" . InvitationEntity::MEMBER_INVITATION];
										MainAPI::removeInvitation($userRequested->getName(), $factionName, InvitationEntity::MEMBER_INVITATION);
										Utils::newMenuSendTask(new MenuSendTask(
											function () use ($userRequested, $factionName) {
												return !MainAPI::areInInvitation($userRequested->getName(), $factionName, InvitationEntity::MEMBER_INVITATION);
											},
											function () use ($request, $player, $userRequested) {
												(new InvitationAcceptEvent($player, $request))->call();
												Utils::processMenu($this->getBackRoute(), $player, [Utils::getText($player->getName(), "SUCCESS_ACCEPT_REQUEST", ['name' => $userRequested->getName()])]);
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
							} else {
								MainAPI::makeInvitation($factionName, $targetName, InvitationEntity::MEMBER_INVITATION);
								Utils::newMenuSendTask(new MenuSendTask(
									function () use ($faction, $targetName) {
										return MainAPI::areInInvitation($faction->getName(), $targetName, InvitationEntity::MEMBER_INVITATION);
									},
									function () use ($player, $targetName, $faction) {
										$invitation = null;
										foreach (MainAPI::getInvitationsBySender($faction->getName(), InvitationEntity::MEMBER_INVITATION) as $invitations) {
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
							}
						} else {
							Utils::processMenu($this, $player, [Utils::getText($player->getName(), "ALREADY_PENDING_INVITATION")]);
						}
					} else {
						Utils::processMenu($this, $player, [Utils::getText($player->getName(), "PLAYER_HAVE_ALREADY_FACTION")]);
					}
				} else {
					Utils::processMenu($this, $player, [Utils::getText($player->getName(), "USER_DONT_EXIST")]);
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
		$menu->addInput(Utils::getText($this->getUserEntity()->getName(), "SEND_INVITATION_PANEL_INPUT_CONTENT"));
		return $menu;
	}
}