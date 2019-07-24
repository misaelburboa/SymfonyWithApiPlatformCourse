<?php
namespace App\Entity;

use DateTimeInterface;

interface PublishedDateEntityInterface
{
    public function setPublished(DateTimeInterface $published): PublishedDateEntityInterface;
}