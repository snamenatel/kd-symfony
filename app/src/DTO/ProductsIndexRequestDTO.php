<?php

namespace App\DTO;

use Symfony\Component\HttpFoundation\Request;

class ProductsIndexRequestDTO
{
    private const ORDER_FIELDS = [
        'title',
        'weight',
        'id',
    ];
    private const ORDER_DIRECTION = [
        'desc',
        'asc'
    ];

    private ?int $categoryId;
    private ?string $title;
    private ?string $orderBy;
    private ?string $orderDirection;
    private int $page = 1;

    public function __construct(Request $request)
    {
        $this->setCategoryId($request->get('category'));
        $this->setTitle($request->get('title'));
        $this->setOrderBy($request->get('orderBy'));
        $this->setOrderDirection($request->get('orderDirection'));
        $this->setPage($request->get('page'));
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage($page): void
    {
        if ($page && is_numeric($page)) {
            $this->page = (int) $page;
        } else {
            $this->page = 1;
        }
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    public function getOrderDirection(): ?string
    {
        return $this->orderDirection;
    }

    public function setCategoryId($categoryId): void
    {
        if (is_numeric($categoryId)) {
            $this->categoryId = (int) $categoryId;
        } else {
            $this->categoryId = null;
        }
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function setOrderBy(?string $orderBy): void
    {
        if (is_string($orderBy) && in_array(strtolower($orderBy), self::ORDER_FIELDS)) {
            $this->orderBy = strtolower($orderBy);
        } else {
            $this->orderBy = 'id';
        }

    }

    public function setOrderDirection(?string $orderDirection): void
    {
        if (is_string($orderDirection) && in_array(strtolower($orderDirection), self::ORDER_DIRECTION)) {
            $this->orderDirection = strtolower($orderDirection);
        } else {
            $this->orderDirection = 'asc';
        }
    }
}