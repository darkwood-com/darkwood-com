<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\CommentPageRepository::class)]
class CommentPage extends \App\Entity\Comment
{
    #[ORM\ManyToOne(targetEntity: \App\Entity\Page::class, inversedBy: 'comments', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'page_id', referencedColumnName: 'id')]
    protected ?\App\Entity\Page $page = null;

    /**
     * Set page.
     */
    public function setPage(Page $page = null): void
    {
        $this->page = $page;
    }

    /**
     * Get page.
     *
     * @return \App\Entity\Page
     */
    public function getPage()
    {
        return $this->page;
    }
}
