<?php


namespace SK\SortableBundle\Model;

use Doctrine\ORM\QueryBuilder;

interface Sortable
{
    public function addSortBy(QueryBuilder $query, SortParameter $sortParameter);
}
