<?php

class ElectronicItems
{
    /**
     * @var ElectronicItem[]
     */
    private array $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function getSortedByPrice(): array
    {
        $items = [];

        foreach ($this->items as $item) {
            $items[] = $item;
            foreach ($item->extras as $extra) {
                $items[] = $extra;
            }
        }

        usort(
            $items,
            static fn(ElectronicItem $item1, ElectronicItem $item2) => $item1->getPrice() <=> $item2->getPrice()
        );

        return $items;
    }

    /**
     * Returns the items depending on the sorting type requested
     *
     * @param $type
     *
     * @return bool
     */
    public function getSortedItems($type): bool
    {
        $sorted = [];
        foreach ($this->items as $item)
        {
            $sorted[($item->getPrice() * 100)] = $item;
        }

        return ksort($sorted, SORT_NUMERIC);
    }

    public function getItemsByType(string $type): array
    {
        if (!in_array($type, ElectronicItem::$types, true)) {
            return [];
        }

        return array_filter($this->items, static fn(ElectronicItem $item) => $item->getType() === $type);
    }

    public function getTotalPrice(): float
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->getTotalPrice();
        }

        return $total;
    }
}

class ElectronicItem
{
    use ExtrasTrait;

    public const ELECTRONIC_ITEM_CONSOLE = 'console';
    public const ELECTRONIC_ITEM_TELEVISION = 'television';
    public const ELECTRONIC_ITEM_MICROWAVE = 'microwave';
    public const ELECTRONIC_ITEM_CONTROLLER = 'controller';

    /**
     * @var string[]
     */
    public static array $types = [
        self::ELECTRONIC_ITEM_CONSOLE,
        self::ELECTRONIC_ITEM_TELEVISION,
        self::ELECTRONIC_ITEM_MICROWAVE,
        self::ELECTRONIC_ITEM_CONTROLLER,
    ];

    protected float $price;
    protected string $type;
    protected bool $isWired;

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function isWired(): bool
    {
        return $this->isWired;
    }

    public function setIsWired(bool $isWired): void
    {
        $this->isWired = $isWired;
    }

    /**
     * Returns item price and extras prices
     *
     * @return float
     */
    public function getTotalPrice(): float
    {
        return $this->getPrice() + $this->getExtrasPrice();
    }
}

trait ExtrasTrait {
    /**
     * @var ElectronicItem[]
     */
    public array $extras = [];
    protected int $extrasLimit;

    /**
     * @throws Exception
     */
    public function addExtra(ElectronicItem $item): void
    {
        $this->maxExtras();
        $this->extras[] = $item;
    }

    public function getExtrasPrice(): float
    {
        $total = 0;
        foreach ($this->extras as $item) {
            $total += $item->getPrice();
        }

        return $total;
    }

    /**
     * @throws Exception
     */
    protected function maxExtras(): void
    {
        if (!isset($this->extrasLimit)) {
            throw new Exception('This item cannot have any extras');
        }

        if ($this->extrasLimit > 0 && count($this->extras) === $this->extrasLimit) {
            throw new Exception('Extras limit reached');
        }
    }
}

class Console extends ElectronicItem
{
    private const EXTRAS_LIMIT = 4;

    public function __construct(float $price)
    {
        $this->setType(self::ELECTRONIC_ITEM_CONSOLE);
        $this->extrasLimit = self::EXTRAS_LIMIT;
        $this->setPrice($price);
    }
}

class Television extends ElectronicItem
{
    private const EXTRAS_LIMIT = 0;

    public function __construct(float $price)
    {
        $this->setType(self::ELECTRONIC_ITEM_TELEVISION);
        $this->extrasLimit = self::EXTRAS_LIMIT;
        $this->setPrice($price);
    }
}

class Microwave extends ElectronicItem
{
    public function __construct(float $price)
    {
        $this->setType(self::ELECTRONIC_ITEM_MICROWAVE);
        $this->setPrice($price);
    }
}

class Controller extends ElectronicItem
{
    public function __construct(float $price, bool $isWired)
    {
        $this->setType(self::ELECTRONIC_ITEM_CONTROLLER);
        $this->setPrice($price);
        $this->setIsWired($isWired);
    }
}

$console = new Console(499.99);
$remoteController1 = new Controller(53.19, false);
$remoteController2 = new Controller(46.65, false);
$wiredController1 = new Controller(34.97, true);
$wiredController2 = new Controller(21.12, true);
$console->addExtra($remoteController1);
$console->addExtra($remoteController2);
$console->addExtra($wiredController1);
$console->addExtra($wiredController2);

$tv1 = new Television(379.99);
$tv1->addExtra(new Controller(12.34, false));
$tv1->addExtra(new Controller(23.45, false));


$tv2 = new Television(456.78);
$tv2->addExtra(new Controller(9.13, false));

$microwave = new Microwave(191.89);

$items = new ElectronicItems([
    $console,
    $tv1,
    $tv2,
    $microwave,
]);

foreach ($items->getSortedByPrice() as $item) {
    echo $item->getType() . ': ' . number_format($item->getPrice(), 2) . PHP_EOL;
}

echo PHP_EOL . 'TOTAL: ' . number_format($items->getTotalPrice(), 2) . PHP_EOL;
echo 'Console with controllers costs: ' . number_format($console->getTotalPrice(), 2) . PHP_EOL;

