<?php


namespace SK\SortableBundle\Model;

final class SortParameter
{
    const ORDER_ASC = 'asc';
    const ORDER_DESC = 'desc';
    const ORDER_NONE = 'none';

    private $translation;
    private $queryParameter;
    private $order;

    public function __construct(string $queryParameter, $translation, $order = self::ORDER_NONE)
    {
        $this->queryParameter = $queryParameter;
        $this->order = $order;
        $this->translation = $translation;
    }

    public function getTranslation()
    {
        return $this->translation;
    }

    public function getQueryParameter(): string
    {
        return $this->queryParameter;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function setOrder(string $order)
    {
        if (!in_array($order, [self::ORDER_ASC, self::ORDER_DESC, self::ORDER_NONE])) {
            throw new \InvalidArgumentException("Order must be either 'asc' or 'desc' or 'none'");
        }
        $this->order = $order;
    }
}
