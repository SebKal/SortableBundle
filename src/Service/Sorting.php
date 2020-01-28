<?php


namespace SK\SortableBundle\Service;


use SK\SortableBundle\Model\SortParameter;

final class Sorting
{
    const DEFAULT_SORT_KEY = 'sort';
    const KEY_DELIMITER = ',';

    private $mappings;
    private $order;

    public function __construct()
    {
        $this->mappings = [];
    }

    public function addMapping(int $column, string $queryParameter, string $translationLabel)
    {
        $this->mappings[$column] = new SortParameter($queryParameter, $translationLabel);
    }

    public function handleQuery(string $sortQueryParameters)
    {
        $this->order = [];

        foreach ($this->getMappings() as $column => &$sort) {
            $sort->setOrder(SortParameter::ORDER_NONE);
        }

        foreach (explode(self::KEY_DELIMITER, $sortQueryParameters) as $columnQueryValue) {
            $order = ($columnQueryValue > 0) ? SortParameter::ORDER_ASC : SortParameter::ORDER_DESC;
            $column = abs($columnQueryValue);
            if ($this->has($column)) {
                $this->order[] = $column;
                $this->mappings[$column]->setOrder($order);
            }
        }
    }

    public function getMappings(): array
    {
        return $this->mappings;
    }

    public function getSortedColumnsByOrder(): array
    {
        $filterSorted = function (SortParameter $entry) {
            return $entry->getOrder() !== SortParameter::ORDER_NONE;
        };

        $querySortedParams = array_filter($this->mappings, $filterSorted);

        $columnOrder = $this->order;
        $sortByQueryOrder = function (int $a, int $b) use ($columnOrder) {
            $posA = array_search($a, $columnOrder);
            $posB = array_search($b, $columnOrder);

            return $posA - $posB;
        };

        uksort($querySortedParams, $sortByQueryOrder);

        return $querySortedParams;
    }

    public function get(int $column)
    {
        if ($this->has($column)) {
            return $this->mappings[$column];
        }

        return null;
    }

    public function has(int $column, bool $sorted = false): bool
    {
        if ($sorted) {
            return array_key_exists($column, $this->getSortedColumnsByOrder());
        }

        return array_key_exists($column, $this->mappings);
    }

    public function singleUrlQuery(int $column, string $defaultOrder = SortParameter::ORDER_DESC): int
    {
        if ($this->has($column, true)) {
            return $this->get($column)->getOrder() === SortParameter::ORDER_ASC ? -$column : $column;
        }

        return $defaultOrder === SortParameter::ORDER_DESC ? -$column : $column;
    }

    public function getUrlQuery(int $column, string $order)
    {
        $sortQueryArray = [];
        /** @var $sortParam SortParameter */
        foreach ($this->getSortedColumnsByOrder() as $sortColumn => $sortParam) {
            if ($column === $sortColumn) {
                if ($sortParam->getOrder() === $order) {
                    continue;
                } else {
                    $sortQueryArray[] = ($order === SortParameter::ORDER_ASC) ? $sortColumn : -$sortColumn;
                }
            } else {
                $sortQueryArray[] = ($sortParam->getOrder() === SortParameter::ORDER_ASC) ? $sortColumn : -$sortColumn;
            }
        }

        if (!$this->has($column, true)) {
            $sortQueryArray[] = ($order === SortParameter::ORDER_ASC) ? $column : -$column;
        }

        if (empty($sortQueryArray)) {
            return null;
        }

        return implode(self::KEY_DELIMITER, $sortQueryArray);
    }
}
