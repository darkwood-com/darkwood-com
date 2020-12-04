<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentPageRepository")
 */
class CommentPage extends \App\Entity\Comment
{
    /**
     * @var Page
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Page", inversedBy="comments", cascade={"persist"})
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id")
     **/
    protected $page;
    /**
     * Set page.
     *
     * @param \App\Entity\Page $page
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
