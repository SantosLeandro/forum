<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use DateTime;

#[ORM\HasLifecycleCallbacks]
trait TimeStamp
{
    #[ORM\Column(name:'created_at', type:'datetime')]
    private $createdAt;

    #[ORM\Column(name:'updated_at', type:'datetime')]
    private $updatedAt;

    #[ORM\PrePersist]
    public function onCreate()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    #[ORM\PreUpdate]
    public function onUpdate()
    {
        $this->updatedAt = new DateTime();
    }

    public function getCreateAt(): DateTime
    {
        return $this->createdAt();
    }

    public function getUpdateAt(): DateTime
    {
        return $this->updatedAt();
    }
}
