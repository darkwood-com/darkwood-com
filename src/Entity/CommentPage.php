<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CommentPageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentPageRepository::class)]
class CommentPage extends Comment {}
