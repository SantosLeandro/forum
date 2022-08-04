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

    #[ORM\Column(name:'deleted_at', type:'datetime',nullable:'true')]
    private $deletedAt = null;

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

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(DateTime $deletedAt): self
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }
}
