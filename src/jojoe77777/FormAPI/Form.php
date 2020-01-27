<?php

declare(strict_types = 1);

namespace jojoe77777\FormAPI;

use pocketmine\form\Form as IForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

abstract class Form implements IForm{

    /** @var array */
    protected $data = [];
    /** @var callable|null */
    private $callable;
    /** @var string|null */
    private $permission;
    /** @var string|null */
    private $permissionMessage;

    /**
     * @param callable|null $callable
     */
    public function __construct(?callable $callable) {
        $this->callable = $callable;
    }

    /**
     * @deprecated
     * @see Player::sendForm()
     *
     * @param Player $player
     */
    public function sendToPlayer(Player $player) : void {
        $player->sendForm($this);
    }

    public function getCallable() : ?callable {
        return $this->callable;
    }

    public function setCallable(?callable $callable) {
        $this->callable = $callable;
    }

    public function getPermission() : ?string {
        return $this->permission;
    }

    public function setPermission(?string $permission) : void {
        $this->permission = $permission;
    }

    public function getPermissionMessage() : ?string {
        return $this->permissionMessage;
    }

    public function setPermissionMessage(?string $permissionMessage) : void {
        $this->permissionMessage = $permissionMessage;
    }

    public function testPermission(Player $target): bool {
        if ($this->testPermissionSilent($target)) {
            return true;
        }
        
        if ($this->permissionMessage === null) {
            $target->sendMessage(TextFormat::RED . "You do not have permission to use this form");
        } else if ($this->permissionMessage !== "") {
            $target->sendMessage(str_replace("<permission>", $this->permission, $this->permissionMessage));
        }
        
        return false;
    }

    public function testPermissionSilent(Player $target): bool
    {
        if ($this->permission === null || $this->permission === "") {
            return true;
        }
        
        foreach (explode(";", $this->permission) as $permission) {
            if ($target->hasPermission($permission)) {
                return true;
            }
        }
        
        return false;
    }

    public function handleResponse(Player $player, $data) : void {
        if (!$this->testPermission($player)) {
            return;
        }
        $this->processData($data);
        $callable = $this->getCallable();
        if($callable !== null) {
            $callable($player, $data);
        }
    }

    public function processData(&$data) : void {
    }

    public function jsonSerialize(){
        return $this->data;
    }
}
