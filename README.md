[![](https://poggit.pmmp.io/shield.state/FactionMaster)](https://poggit.pmmp.io/p/FactionMaster) [![](https://poggit.pmmp.io/shield.dl.total/FactionMaster)](https://poggit.pmmp.io/p/FactionMaster)

# FactionMaster

### For using PureChat with FactionMaster, you must use my PureChat fork and download the last release : https://github.com/ShockedPlot7560/PureChat/releases/tag/v1.4.1

FactionMaster is a new faction plugin that aims at flexibility and customization of the plugin by the user and the developers. It includes all the basic functionality of a faction plugin and data storage in MySQL or SQLITE. This is done by adding an extension system and a translation system. FactionMaster has a will of accessibility to the players and especially not to have to remember a lot of commands to play, all is done via interface.

The PVP Faction mode is a game mode that consists of creating your faction and growing it. You can ally with other factions, fight and even request territories. All the available features will be listed in another section.

## Features
FactionMaster have multiple server support, see the Installation section for more information

| Feature | FactionMaster | FactionsPro | PiggyFactions | SimpleFaction |
| ------- | :-----------: | :---------: | :-----------: | :-----------: |
| ``SQLite3 Support`` | ✔ | ✔ | ✔ | ✔ |
| ``MySQL Support`` | ✔ | ❌ | ✔ | ✔ |
| ``Multiple claim`` | ✔ | ❌ | ✔ | ✔ |
| ``Multiple faction home`` | ✔ | ❌ | ❌ | ❌ |
| ``Image for UI (Texture pack)`` | ✔ | ❌ | ❌ | ❌ |
| ``Translation system`` | ✔ | ❌ | ✔ | ✔ |
| ``Extension system`` | ✔ | ❌ | ❌ | ❌ |
| ``Per Faction Permissions`` | ✔ | ❌ | ✔ | ❌ |
| ``SQL Injection Protection`` | ✔ | ❌ | ✔ | ❌ |
| ``Command Autocomplete`` | ✔ | ❌ | ✔ | ❌ |
| ``Form UI`` | ✔ | ❌ | ✔ | ❌ |
| ``Async Queries`` | ✔ | ❌ | ✔ | ✔ |
| ``Faction level`` | ✔ | ❌ | ❌ | ❌ |
| ``Custom level reward`` | ✔ | ❌ | ❌ | ❌ |
| ``Faction/ally chat`` | ✔ | ✔ | ✔ | ✔ |
| ``Awaiting invitation`` | ✔ | ❌ | ❌ | ❌ |
| ``Faction visibility`` | ✔ | ❌ | ❌ | ❌ |
| ``Editable message`` | ✔ | ❌ | ✔ | ✔ |
| ``Custom event`` | ✔ | ❌ | ✔ | ❌ |
| ``Claim title`` | ✔ | ❌ | ❌ | ✔ |
| ``Scoreboard faction top`` | ✔ | ❌ | ❌ | ✔ |
| ``Banned faction name`` | ✔ | ✔ | ✔ | ✔ |

## Additionnal plugin
* ``ScoreHUD v6.0.0``: FactionMaster support this plugin and implements all this tags :
  - *factionmaster.faction.name*
  - *factionmaster.faction.power*
  - *factionmaster.faction.level*
  - *factionmaster.faction.xp*
  - *factionmaster.faction.message*
  - *factionmaster.faction.description*
  - *factionmaster.faction.visibility*
  - *factionmaster.player.rank*
* ``PureChat``: To use PureChat tags, download our fork of it [here](https://github.com/ShockedPlot7560/PureChat/releases/tag/v1.4.1)

## Installation
* If you just want to use it on the same machine, no special installation is required, just download the .phar plugin and put it in the plugins folder.
* If you wish to use FactionMaster on more than one server at a time, please modify the ``config.yml`` after starting your server for the first time with FactionMaster on it and change the ``PROVIDER: "SQLITE"`` with MYSQL. Enter your database connection details and restart your server.

## Use image
* Download factionMaster texture pack available [here](https://github.com/ShockedPlot7560/FactionMaster/blob/v2.1.3-alpha/FactionMaster.zip)
* Install it on your server as a mandatory Texture pack
* Put the line : active-image to true in ``config.yml`` file
* Stop and start your server

## Commands
* ``/f``, ``/faction``, ``/fac``: Opens the main menu of FactionMaster
* ``/f top``: Open the faction ranking
* ``/f manage``: Opens the faction management interface
* ``/f sethome <:name>``: Place a home at the player's location
* ``/f delhome <:name>``: Remove the faction home
* ``/f tp <:name>``: TP at the faction home
* ``/f home``: Opens the home menu
* ``/f claim``: Claim the current chunk
* ``/f unclaim``: Remove the current claim
* ``/f create``: Opens the menu to create a faction
* ``/f map``: Displays the map listing all claims
* ``/f help``: Displays the orders
* ``/f info <:name>``: Displays information about a faction
* ``/f claiminfo``: Displays information about a chunk
* ``/f extension``: Display extensions enabled *For op only*
* ``/f scoreboard``: Set the location to spawn top faction scoreboard

## Extension
Extensions, a new way to customize your plugin to your liking. You just have to download the plugin corresponding to the desired extension and place it in the corresponding folder on your server. If you have an urge to stop using the functionality, delete the plugin from your server and the changes will be gone! You can use those approved by the FactionMaster team or do it yourself (*refer to the GitHub*) and submit it to us if you feel like it.

All extensions made by the FactionMaster team and those approved by the FactionMaster team, which are accessible via poggit will be listed here.

## Translators
To participate in the translation of FactionMaster and probably see yourself here, create a Pull Request on the FactionMaster [GitHub](https://github.com/ShockedPlot7560/FactionMaster/). Once the language has been translated on the main plugin and on all the extensions listed in the ``Extension`` section, it will be added and all its contributors thanked.

* **French** (fr_Fr): @ShockedPlot7560
* **English** (en_EN): @ShockedPlot7560
* **Spanish** (es_SPA): @MrBlasyMSK

## Developpers
Adding and modifying extensions is rather simple and explained on the README.md of the [GitHub](https://github.com/ShockedPlot7560/FactionMaster/) repository with a documentation for the handling of the API.
Many extensions will be made and approved by the FactionMaster team to allow users to modulate the plugin to their choice.
All approved extensions will have a line in the README and and listed in the ``Extension`` section .

## Config
```yaml
 #
 #      ______           __  _                __  ___           __
 #     / ____/___ ______/ /_(_)___  ____     /  |/  /___ ______/ /____  _____
 #    / /_  / __ `/ ___/ __/ / __ \/ __ \   / /|_/ / __ `/ ___/ __/ _ \/ ___/
 #   / __/ / /_/ / /__/ /_/ / /_/ / / / /  / /  / / /_/ (__  ) /_/  __/ /  
 #  /_/    \__,_/\___/\__/_/\____/_/ /_/  /_/  /_/\__,_/____/\__/\___/_/ 
 #
 # FactionMaster - A Faction plugin for PocketMine-MP
 # This file is part of FactionMaster
 # 
 # This program is free software: you can redistribute it and/or modify
 # it under the terms of the GNU Lesser General Public License as published by
 # the Free Software Foundation, either version 3 of the License, or
 # (at your option) any later version.
 #
 # This program is distributed in the hope that it will be useful,
 # but WITHOUT ANY WARRANTY; without even the implied warranty of
 # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 # GNU Lesser General Public License for more details.
 #
 # You should have received a copy of the GNU Lesser General Public License
 # along with this program.  If not, see <https://www.gnu.org/licenses/>.
 #
 # @author ShockedPlot7560 
 # @link https://github.com/ShockedPlot7560
 #

# --------------------- DATABASE --------------------------

# Use only SQLITE or MYSQL
#
# To enabled Multiple server support
# please use MYSQL for PROVIDER, give the good information and restart the server
PROVIDER: "SQLITE"
MYSQL_database:
  host: "localhost"
  name: "FactionMaster"
  user: "root"
  pass: ""
SQLITE_database: 
  name: "FactionMaster"

# --------------------- GLOBAL CONFIGURATION --------------------------

# If you want to disable this feature, empty this array and reload your server
banned-faction-name: ["op", "staff", "admin", "fuck", "shit"]

xp-win-per-kill: 1
power-win-per-kill: 2
power-loose-per-death: 2
#Use to multiply the power win and loose if players are in factions
faction-multiplicator: 2
#check if player have armor equip
allow-no-stuff: false

default-home-limit: 2
default-claim-limit: 2
default-member-limit: 2
default-ally-limit: 2
default-power: 0

min-faction-name-length: 3
max-faction-name-length: 20

# If it set to true, image will be display near button
# If it set to false, image will be disabled
active-image: true

faction-chat-active: false
faction-chat-symbol: "$"
faction-chat-message: "[{factionName}] {playerName}: {message}"
ally-chat-active: false
ally-chat-symbol: "%"
ally-chat-message: "[{factionName}] {playerName}: {message}"

# ------------------ BROADCAST MESSAGE CONFIGURATION ---------------------

broadcast-faction-create: false
broadcast-faction-create-message: "{playerName} has created the faction {factionName}"
broadcast-faction-delete: false
broadcast-faction-delete-message: "{playerName} has deleted the faction {factionName}"
broadcast-faction-transferProperty: false
broadcast-faction-transferProperty-message: "{playerName} transferred the property to {targetName} of the faction {factionName}"


# --------------------- CLAIM CONFIGURATION --------------------------

claim-cost:
  # the type is the same type as level reward, you can put :
  #   power / allyLimit / claimLimit / homeLimit / memberLimit
  #   to make your own reward, please read the documentation
  type: "power"
  # the start value for the first claim
  value: 200

# flat: cost all the time, the same price
# addition: first claim will be cost: Ex: 100
#      second claim will be cost: Ex: 200
#      second claim will be cost: Ex: 300
# multiplication: will be times by a factor
# decrease: descrease the start value by the factor
claim-provider: "flat"
# Equation for mutliplication :
#   cost-price = cost-start * (factor ** number-claim)
multiplication-factor: 2
#Equation for decrease
#   cost-price = cost-start - (number-claim * decrease-factor)
decrease-factor: 100

# If set to false, the player cant /f sethome in a ennemy claim
allow-home-ennemy-claim: true

# --------------------- TITLE CONFIGURATION --------------------------

# Set this to true if you want to display on player's screen the message when entering a claim
message-alert: true
# The following line define which message will be print
# on the screen of players when entering a claim chunk
# active parameter which can set : {factionName}
# to purpose a suggestion, open an issue on our github
message-alert-title: "{factionName}"
message-alert-subtitle: ""
# Defines the time that will be applied before the message is displayed again
message-alert-cooldown: 10

# -------------- TOP FACTION'S SCOREBOARD CONFIGURATION -----------------

# set this to true if you want to enable this scoreboard
faction-scoreboard: false
# This is the scoreboard header, display on the top
faction-scoreboard-header: "- Top 10 faction -"
# Lign patern for each faction
# you can use this parameter : 
# {factionName} / {level} / {power}
# to purpose a parameter suggestion, please open an issue on github
faction-scoreboard-lign: "{factionName}: Level {level}"
# This is the coordonnate to display the scoreboard
# for a better handling, use the /f scoreboard command in game
faction-scoreboard-position: "0|100|0|world"

# --------------------- PLUGIN CONFIGURATION --------------------------
#       DONT CHANGE IF YOU DONT KNOW WHAT YOU ARE DOING

# Change this value only if you are sure of what you are doing, 
# reducing it may break some functionality of the plugin, 
# increasing it may reduce the players experience.
# Default: 60
timeout-task: 60

# Change this value only if you are sure of what you are doing, 
# reducing it may break some functionality of the plugin, 
# increasing it may reduce the players experience.
# Default: 200
# It will determine ow much time Database synchronisation will be done
sync-time: 200
```
