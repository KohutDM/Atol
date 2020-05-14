<?php
/**
 * Mmd Atol Discount.
 *
 * @author Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Model;

use Exception;
use Magento\Sales\Model\AbstractModel;
use Mmd\Atol\Exception\NonFatalErrorException;

/**
 * Class Discount
 *
 * @package Mmd\Atol\Model
 */
class Discount extends AbstractDiscount
{
    /**
     * @var AbstractModel
     */
    protected $entity;

    /**
     * @var DiscountCalculator
     */
    protected $discountCalculator;

    /**
     * Discount constructor.
     *
     * @param DiscountCalculator $discountCalculator
     */
    public function __construct(
        DiscountCalculator $discountCalculator
    ) {
        $this->discountCalculator = $discountCalculator;
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function getRecalculated(
        AbstractModel $entity,
        string $taxValue = '',
        string $shippingTaxValue = ''
    ) : array {
        if (!extension_loaded('bcmath')) {
            throw new NonFatalErrorException('BCMath extension is not available in this PHP version.');
        }
        $this->entity = $entity;
        $this->taxValue = $taxValue;
        $this->shippingTaxValue = $shippingTaxValue;

        $globalDiscount = $this->discountCalculator->getGlobalDiscount($this->entity);

        /**  If there are RewardPoints, then costing must be enforced */
        if ($globalDiscount !== 0.00) {
            $this->doCalculation = true;
        }
        switch (true) {
            case (!$this->doCalculation):
                break;
            case ($this->discountCalculator->checkSpread($this->entity)):
                $this->applyDiscount();
                break;
            default:
                /**
                 * This is the case when you do not need to smear a penny in positions and at the same time,
                 * positions can have discounts that are evenly divisible.
                 */
                $this->setSimplePrices();
                break;
        }

        return $this->buildFinalArray();
    }

    /**
     * Apply discount.
     *
     * @return void
     */
    protected function applyDiscount(): void
    {
        $subTotal = $this->entity->getSubtotalInclTax() ?? 0;
        $discount = $this->entity->getDiscountAmount() ?? 0;

        /** @var float Discount on all order.
         * For example, rewardPoints or storeCredit
         */
        $superGrandDiscount = $this->discountCalculator->getGlobalDiscount($this->entity);

        if ($superGrandDiscount && abs($superGrandDiscount) < 10.00) {
            $this->discountCalculator->preFixLowDiscount($this->entity);
            $superGrandDiscount = 0.00;
        }
        $grandDiscount = $superGrandDiscount;

        /** If we spread the discount, then we spread everything: (product discounts + $ superGrandDiscount) */
        if ($this->spreadDiscOnAllUnits) {
            $grandDiscount = $discount + $this->discountCalculator->getGlobalDiscount($this->entity);
        }
        $items = $this->getAllItems($this->entity);
        foreach ($items as $item) {
            if (!$this->isValidItem($item)) {
                continue;
            }
            $this->discountCalculator->calculateItem(
                $item,
                (float) $subTotal,
                (float) $superGrandDiscount,
                (float) $grandDiscount
            );
        }

        if ($this->spreadDiscOnAllUnits && $this->isSplitItemsAllowed) {
            $this->discountCalculator->postFixLowDiscount($this->entity);
        }
    }

    /**
     * If everything is evenly divisible - set up prices without extra recalculations
     * like applyDiscount() method does.
     *
     * @return void
     */
    protected function setSimplePrices(): void
    {
        $items = $this->getAllItems($this->entity);
        foreach ($items as $item) {
            if (!$this->isValidItem($item)) {
                continue;
            }

            $qty = $item->getQty() ?: $item->getQtyOrdered();
            $rowTotal = $item->getData('row_total_incl_tax');

            $priceWithDiscount = ($rowTotal - $item->getData('discount_amount')) / $qty;
            $item->setData(self::NAME_UNIT_PRICE, $priceWithDiscount);
        }
    }

    /**
     * Build final array.
     *
     * @throws Exception
     *
     * @return array
     */
    protected function buildFinalArray(): array
    {
        $grandTotal = $this->getGrandTotal($this->entity);

        $items = $this->getAllItems($this->entity);
        $itemsFinal = [];
        $itemsSum = 0.00;
        foreach ($items as $item) {
            if (!$this->isValidItem($item)) {
                continue;
            }

            $splitedItems = $this->getProcessedItem($item);
            $itemsFinal = $itemsFinal + $splitedItems;
        }

        /** Calculate sum */
        foreach ($itemsFinal as $item) {
            $itemsSum += $item[self::SUM];
        }

        $receipt = [
            self::SUM => $itemsSum,
            self::ORIG_GRAND_TOTAL => $grandTotal,
        ];

        $shippingAmount = $this->entity->getShippingInclTax() ?? 0.00;
        $itemsSumDiff = round($this->slyFloor($grandTotal - $itemsSum - $shippingAmount, 3), 2);

        $shippingItem = [
            self::NAME => $this->getShippingName($this->entity),
            self::PRICE => $shippingAmount + $itemsSumDiff,
            self::QUANTITY => 1.0,
            self::SUM => $shippingAmount + $itemsSumDiff,
            self::TAX => $this->shippingTaxValue,
        ];

        $itemsFinal[self::SHIPPING] = $shippingItem;
        $receipt[self::ITEMS] = $itemsFinal;

        return $receipt;
    }

    /**
     * Build item.
     *
     * @param AbstractModel $item
     * @param float $price
     * @param string $taxValue
     *
     * @throws Exception
     *
     * @return array
     */
    protected function buildItem(AbstractModel $item, float $price, string  $taxValue = ''): array
    {
        $qty = $item->getQty() ?: $item->getQtyOrdered();
        if (!$qty) {
            throw new NonFatalErrorException(
                'Divide by zero. Qty of the item is equal to zero! Item: ' . $item->getId()
            );
        }

        $entityItem = [
            self::PRICE => round($price, 2),
            self::NAME => $item->getName(),
            self::QUANTITY => round($qty, 2),
            self::SUM => round($price * $qty, 2),
            self::TAX => $taxValue,
        ];

        if (!$this->doCalculation) {
            $entityItem[self::SUM] = round(
                $item->getData('row_total_incl_tax') - $item->getData('discount_amount'),
                2
            );
            $entityItem[self::PRICE] = 1;
        }

        return $entityItem;
    }

    /**
     * Make item array and split (if needed) it into 2 items with different prices.
     *
     * @param AbstractModel $item
     *
     * @throws Exception
     *
     * @return array
     */
    protected function getProcessedItem(AbstractModel $item): array
    {
        $final = [];

        $taxValue = $this->taxValue;
        $price = !($item->getData(self::NAME_UNIT_PRICE) === null)
            ? $item->getData(self::NAME_UNIT_PRICE)
            : $item->getData('price_incl_tax');

        $entityItem = $this->buildItem($item, $price, $taxValue);

        $rowDiff = $item->getData(self::NAME_ROW_DIFF);

        if (!$rowDiff || !$this->isSplitItemsAllowed || !$this->doCalculation) {
            $final[$item->getId()] = $entityItem;

            return $final;
        }

        $qty = $item->getQty() ?: $item->getQtyOrdered();

        /** @var int How many products in a row need a price change
         *  If $qtyUpdate =0 - then the price of all goods should be increased
         */
        $qtyUpdate = abs(bcmod($rowDiff, $qty));
        $sign = abs($rowDiff) / $rowDiff;

        /**
         * 2 cases:
         * $qtyUpdate == 0 - then all products increase the price without sharing.
         * $qtyUpdate > 0  - we consider how many goods will be increased.
         */

        /** @var int "$inc + 1 cent" How much should be increased prices */
        $inc = (int) ($rowDiff / $qty);

        $item1 = $entityItem;
        $item2 = $entityItem;

        $item1[self::PRICE] = $item1[self::PRICE] + $inc / 100;
        $item1[self::QUANTITY] = $qty - $qtyUpdate;
        $item1[self::SUM] = round($item1[self::QUANTITY] * $item1[self::PRICE], 2);

        if ($qtyUpdate == 0) {
            $final[$item->getId()] = $item1;

            return $final;
        }

        $item2[self::PRICE] = $item2[self::PRICE] + $sign * 0.01 + $inc / 100;
        $item2[self::QUANTITY] = $qtyUpdate;
        $item2[self::SUM] = round($item2[self::QUANTITY] * $item2[self::PRICE], 2);

        $final[$item->getId() . '_1'] = $item1;
        $final[$item->getId() . '_2'] = $item2;

        return $final;
    }

    /**
     * Get shipping name.
     *
     * @param AbstractModel $entity
     *
     * @return string
     */
    protected function getShippingName(AbstractModel $entity): string
    {
        return $entity->getShippingDescription()
            ?: ($entity->getOrder() ? $entity->getOrder()->getShippingDescription() : '');
    }

    /**
     * Is valid item.
     *
     * @param AbstractModel $item
     *
     * @return bool
     */
    protected function isValidItem(AbstractModel $item): bool
    {
        return $item->getRowTotalInclTax() !== null;
    }
}
