<?php
/***
 *      __  __                       _      
 *     |  \/  |                     (_)     
 *     | \  / | __ ___   _____  _ __ _  ___ 
 *     | |\/| |/ _` \ \ / / _ \| '__| |/ __|
 *     | |  | | (_| |\ V / (_) | |  | | (__ 
 *     |_|  |_|\__,_| \_/ \___/|_|  |_|\___|
 *                                          
 *   THIS CODE IS TO NOT BE REDISTRUBUTED
 *   @author MavoricAC
 *   @copyright Everything is copyrighted to their respective owners.
 *   @link https://github.com/Olybear9/Mavoric                                  
 */

namespace Bavfalcon9\Mavoric\events\player;

use pocketmine\Player;
use pocketmine\item\Item;
use Bavfalcon9\Mavoric\Mavoric;
use Bavfalcon9\Mavoric\events\MavoricEvent;

/**
 * Called when a player consumes an item.
 */
class PlayerConsume extends MavoricEvent {
    /** @var Player */
    private $player;
    /** @var Item */
    private $item;

    public function __construct($e, Mavoric $mavoric, Player $player, Item $item) {
        parent::__construct($e, $mavoric, $player);
        $this->player = $player;
        $this->item = $item;
    }

    public function getItem(): ?Item {
        return $this->item;
    }

    public function getTime(): int {
        // TO DO
        return -1;
    }
}