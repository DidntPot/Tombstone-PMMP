<?php

declare(strict_types = 1);


namespace tomb\DidntPot;

use pocketmine\plugin\PluginBase;
use pocketmine\nbt\tag\{
    CompoundTag, ListTag, DoubleTag, FloatTag, StringTag
};
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\entity\Skin;
use pocketmine\level\Level;
use pocketmine\level\particle\FloatingTextParticle;
use tomb\DidntPot\entity\Tomb;

class Main extends PluginBase implements Listener{
    
  public $time = 1000;
	public $skins = [];
	
	public function onEnable() : void{

        Entity::registerEntity(Tomb::class, true);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        foreach(["tomb.png", "geometry.json"] as $file){

			  $this->saveResource($file);

		}
  }
    
    public function tombSpawn(Player $player){ 

		$file = "tomb.png";
		$path = $this->getDataFolder() . $file;
		$img = @imagecreatefrompng($path);
		$skinbytes = "";

		$m = (int)@getimagesize($path)[0];
		$n = (int)@getimagesize($path)[1];

		for($y = 0; $y < $n; $y++){

			for($x = 0; $x < $m; $x++){

				$colorat = @imagecolorat($img, $x, $y);
				$a = ((~((int)($colorat >> 24))) << 1) & 0xff;
				$r = ($colorat >> 16) & 0xff;
				$g = ($colorat >> 8) & 0xff;
				$b = $colorat & 0xff;
				$skinbytes .= chr($r) . chr($g) . chr($b) . chr($a);

			}

		}

		@imagedestroy($img);
    $nbt = new CompoundTag("", [
          new ListTag("Pos", [
          new DoubleTag("", $player->getX()),
          new DoubleTag("", $player->getY() - 0,25),
          new DoubleTag("", $player->getZ())
          ]),

          new ListTag("Motion", [
          new DoubleTag("", 0),
          new DoubleTag("", 0),
          new DoubleTag("", 0)
          ]),

          new ListTag("Rotation", [
          new FloatTag("", 2),
          new FloatTag("", 2)
          ]),

          new CompoundTag("Skin", [
          new StringTag("Data", $player->getSkin()->getSkinData()),
          new StringTag("Name", "Tomb")
          ])

        ]);

    $npc = new Tomb($player->getLevel(), $nbt);
		$skin = new Skin('tomb', $skinbytes, "", "geometry.tomb", file_get_contents($this->getDataFolder() . "geometry.json"));
    $npc->setSkin($skin);
    $npc->spawnToAll();

    }

    /**
     * @param PlayerDeathEvent $event
     * @return void
     */
    public function onDeath(PlayerDeathEvent $event){

      $victim = $event->getEntity();
      $player = $event->getPlayer();
      $this->tombSpawn($player);
        
      if($victim->getLastDamageCause() instanceof EntityDamageByEntityEvent){

      if($victim->getLastDamageCause()->getDamager() instanceof Player){

        $killer = $victim->getLastDamageCause()->getDamager();
        $this->tombSpawn($victim);
        $text = "§f§l›§r §a$victim §egot clapped by §c$killer §f§l‹§r";
        $p->getLevel()->addParticle(new FloatingTextParticle(new Vector3($player->getX(), $player->getY(), $player->getZ()), '', $text), array($p));
        $text->setVisible(true);

	 	 	  if($this->time>0){

	 	 	    $this->time--;

	 	 	  }

	 	 	  if($this->time == 1){

	 	 	      $text->setVisible(false);

          }

        }
      }
    }

}