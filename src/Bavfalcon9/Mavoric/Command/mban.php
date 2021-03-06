<?php
/***
 *      __  __                       _      
 *     |  \/  |                     (_)     
 *     | \  / | __ ___   _____  _ __ _  ___ 
 *     | |\/| |/ _` \ \ / / _ \| '__| |/ __|
 *     | |  | | (_| |\ V / (_) | |  | | (__ 
 *     |_|  |_|\__,_| \_/ \___/|_|  |_|\___|
 *                                          
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 * 
 *  @author Bavfalcon9
 *  @link https://github.com/Olybear9/Mavoric                                  
 */

namespace Bavfalcon9\Mavoric\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use pocketmine\Player;
use pocketmine\Server;

class mban extends Command {
    private $pl;

    public function __construct($pl) {
        parent::__construct("mban");
        $this->pl = $pl;
        $this->description = "Check a mavoric ban.";
        $this->usageMessage = "/mban <player>";
        $this->setAliases(['mavoricban', 'mban', 'mavban']);
        $this->setPermission("mavoric.alerts");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if (!$sender->hasPermission('mavoric.alerts') && !$sender->isOp()) {
            $sender->sendMessage(TF::RED . "Unknown command. Try /help for a list of commands");
            return true;
        }

        $sender->sendMessage('§c§lWarning: §r§cDeprecated command.');
        if (!isset($args[0])) {
            $sender->sendMessage('§cInclude a player.');
            return true;
        }

        $player = strtolower(implode(' ', array_slice($args, 0)));
        $bans = $this->pl->mavoric->banManager->getBansFor($player);

        if (empty($bans)) {
            $sender->sendMessage('§aNo Mavoric ban found for this user.');
            return true;
        }

        $data = [];
        $i = 1;
        foreach ($bans as $ban) {
            array_push($data, "§c{$i}§7: {$this->stringify($ban['time'])}\n §c-Reason: §7{$ban['reason']}\n §c-All: §7".implode(', ', array_keys($ban['violations'])) . "\n §c-MOD: §7{$ban['moderator']} \n §c-Percentile: §7{$ban['percentile']}");
            $i++;
        }
        if (empty($data)) {
            $sender->sendMessage('§cSomething went wrong...');
            return true;
        } else {
            $sender->sendMessage("§c{$player} has §7".sizeOf($bans)."§c Mavoric bans.");
            $sender->sendMessage(implode("\n", $data));
            return true;
        }
    }

    private function stringify($arr) {
        if (!$arr) return 'Time Invalid';
        if (!isset($arr['date']) || !isset($arr['timezone'])) return 'Time Invalid';
        
        $dnt = explode(' ', $arr['date']);
        $date = $dnt[0];
        $time = explode('.', $dnt[1])[0];
        $timezone = $arr['timezone'];
        return "$date at $time [$timezone]";
    }
}
