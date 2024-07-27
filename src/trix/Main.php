<?php

namespace trix;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\item\Item;

class Main extends PluginBase implements Listener {

    private $playerData;

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveResource("revive.yml");
        $this->playerData = new Config($this->getDataFolder() . "revive.yml", Config::YAML);
    }

    public function onPlayerDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        $inventory = [];
        foreach ($player->getInventory()->getContents() as $slot => $item) {
            $inventory[$slot] = $item->jsonSerialize();
        }
        $this->playerData->set($player->getName(), $inventory);
        $this->playerData->save();
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "lastinventory") {
            if (isset($args[0]) && $sender->hasPermission("core.staff.revive")) {
                $playerName = $args[0];
                if ($this->playerData->exists($playerName)) {
                    $inventoryData = $this->playerData->get($playerName);
                    $player = $this->getServer()->getPlayer($playerName);
                    if ($player !== null) {
                        $inventory = $player->getInventory();
                        $inventory->clearAll();
                        foreach ($inventoryData as $slot => $itemData) {
                            $inventory->setItem($slot, Item::jsonDeserialize($itemData));
                        }
                        $sender->sendMessage("Restored inventory for $playerName.");
                    } else {
                        $sender->sendMessage("Player $playerName is not online.");
                    }
                } else {
                    $sender->sendMessage("No inventory data found for $playerName.");
                }
            } else {
                $sender->sendMessage("Usage: /lastinventory <player>");
            }
            return true;
        }
        return false;
    }
}
