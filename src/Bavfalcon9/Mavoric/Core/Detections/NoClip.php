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

namespace Bavfalcon9\Mavoric\Core\Detections;

use Bavfalcon9\Mavoric\Main;
use Bavfalcon9\Mavoric\Mavoric;
use Bavfalcon9\Mavoric\events\MavoricEvent;
use Bavfalcon9\Mavoric\events\player\PlayerClick;
use Bavfalcon9\Mavoric\events\player\PlayerMove;
use Bavfalcon9\Mavoric\events\player\PlayerTeleport;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as TF;
use pocketmine\Level\Position;
use pocketmine\math\Vector3;
use pocketmine\block\BlockIds;
use pocketmine\block\Stair;

use pocketmine\{
    Player,
    Server
};

/* API CHANGE (Player) */

class NoClip implements Detection {
    private $mavoric;
    private $plugin;
    private $teleported = [];
    private $teleportQueue = [];
    private $ender_pearls = [];
    private $slabs = [182,181,126,157,44,43,139,109,67,114,108,180,128,106,209,208,175,176,177,167,144,127,105,96,94,78,101,90,85];

    public function __construct(Mavoric $mavoric) {
        $this->plugin = $mavoric->getPlugin();
        $this->mavoric = $mavoric;
    }

    public function onEvent(MavoricEvent $event): void {
        /**
         * @var PlayerMove event
         */
        if ($event instanceof PlayerMove) {
            //if (!$event->isMoved()) return;
            $blockA = $event->getBlocks()[1];
            $blockB = $event->getBlocks()[0];
            $player = $event->getPlayer();

            if ($player->isSpectator()) return;
            if ($player->isCreative()) return;

            $pearl_data = $this->pearledAway($event->getPlayer()); 
            /** 
             * TO DO: Fix and check both rather than current.
             * aka check block a and then check block b
             */
            if ($blockA->isSolid() && $blockB->isSolid()) {
                if ($event->isTeleport()) {
                    return;
                }
                if ($pearl_data !== false) {
                    if (!$pearl_data['pos'] instanceof Vector3) {
                        $player->sendMessage(Mavoric::EPEARL_LOCATION_BAD);
                        return;
                    } else {
                        $player->teleport($pearl_data['pos']);
                        $player->sendMessage(Mavoric::EPEARL_LOCATION_BAD);
                        return;
                    }
                }

                if (in_array($blockA->getId(), $this->slabs) || in_array($blockB->getId(), $this->slabs)) {
                    return;
                }

                if ($blockA instanceof Stair) {
                    // Prevent stair bugging (usually when you run up stairs)
                    return;
                }

                if ($blockA->getId() === BlockIds::SAND || $blockB->getId() === BlockIds::SAND) {
                    $y = $event->getNextAirBlock()->y;
                    $player->teleport(new Position($player->x, $y, $player->z, $player->getLevel()));
                    return;
                }
                if ($blockA->getId() === BlockIds::GRAVEL || $blockB->getId() === BlockIds::GRAVEL) {
                    $y = $event->getNextAirBlock()->y;
                    $player->teleport(new Position($player->x, $y, $player->z, $player->getLevel()));
                    return;
                }
                if ($blockA->getId() === BlockIds::ANVIL || $blockB->getId() === BlockIds::ANVIL) {
                    $y = $event->getNextAirBlock()->y;
                    $player->teleport(new Position($player->x, $y, $player->z, $player->getLevel()));
                    return;
                }
                
                $event->issueViolation(Mavoric::CHEATS['NoClip']);
                $event->sendAlert('NoClip', 'Illegal movement, player moved while in a block');
            }
        }

        /**
         * @var PlayerTeleport event
         */
        if ($event instanceof PlayerTeleport) {
            $player = $event->getPlayer();

            foreach ($this->teleportQueue as $p=>$t) {
                if ($t['time'] + 3 >= time()) unset($this->teleportQueue[$p]);
            }

            if (!isset($this->teleportQueue[$player->getName()])) $this->teleportQueue[$player->getName()] = [
                    'time' => microtime(true),
                    'pos' => $event->getFrom()
                ];
            if (!isset($this->ender_pearls[$player->getName()])) {
                return;
            } else {
                $this->teleported[$player->getName()] = [
                    'thrownAt' => $this->ender_pearls[$player->getName()],
                    'elapsed' => microtime(true) - $this->ender_pearls[$player->getName()],
                    'pos' => $event->getFrom()
                ];

                unset($this->ender_pearls[$player->getName()]);
                return;
            }
        }

        /**
         * @var PlayerClick event
         */
        if ($event instanceof PlayerClick) {
            if (!$event->isRightClick()) return;
            $player = $event->getPlayer();

            if ($event->getItem()->getId() === 368) {
                $this->ender_pearls[$player->getName()] = microtime(true);   
            }
            return;
        }
    }

    /** 
     * @return Bool
     */
    public function isEnabled(): Bool {
        return false;
    }


    /**
     * Private functions.
     */


    private function pearledAway($p) {
        $p = $p->getName();
        if (empty($this->teleported)) return false;
        if (!isset($this->teleported[$p])) return false; // wtf lol
        if (microtime(true) - $this->teleported[$p]['thrownAt'] >= 3) {
            // Three seconds passed since teleport, ignore, but still return teleport if within 5 seconds?
            $cache = $this->teleported[$p];
            unset($this->teleported[$p]);

            if (microtime(true) - $cache['thrownAt'] >= 5) return false;
            else {
                return $cache;
            }
        } else {
            return $this->teleported[$p];
        }
    }
    private function hasTeleported($p) {
        $p = $p->getName();
        // Purge cache
        foreach ($this->teleportQueue as $p=>$t) {
            if ($t['time'] + 2 >= time()) unset($this->teleportQueue[$p]);
        }
        if (empty($this->teleportQueue)) return false;
        if (!isset($this->teleportQueue[$p])) return false; // wtf lol
        if (microtime(true) - $this->teleportQueue[$p]['time'] >= 2) {
            $cache = $this->teleportQueue[$p];
            unset($this->teleportQueue[$p]);
            return false;
        } else {
            return $this->teleportQueue[$p];
        }
    }
}