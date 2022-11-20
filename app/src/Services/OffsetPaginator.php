<?php

namespace App\Services;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class OffsetPaginator
{
    private Paginator $paginator;
    private int $perPage;
    private int $count;
    private int $countPages;

    public function __construct(Query|QueryBuilder $query, int $perPage = 25)
    {
        $this->perPage = $perPage;
        $this->paginator = new Paginator($query, true);
        $this->count = $this->paginator->count();
        $this->countPages = ceil($this->count / $this->perPage);
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getCountPages(): int
    {
        return $this->countPages;
    }

    public function getPage(int $page): Query|QueryBuilder
    {
        return $this->paginator
            ->getQuery()
            ->setFirstResult($this->perPage * ($page - 1))
            ->setMaxResults($this->perPage);
    }

}