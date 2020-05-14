<?php
/**
 * Mmd Atol Discount Calculator.
 *
 * @author Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Model;

use Magento\Sales\Model\AbstractModel;

/**
 * Class DiscountCalculator
 *
 * @package Mmd\Atol\Model
 */
class DiscountCalculator extends AbstractDiscount
{
    /**
     * Calculate item.
     *
     * @param AbstractModel $item
     * @param float $subTotal
     * @param float $superGrandDiscount
     * @param float $grandDiscount
     */
    public function calculateItem(
        AbstractModel $item,
        float $subTotal,
        float $superGrandDiscount,
        float $grandDiscount
    ) :void {
        $price = $item->getData('price_incl_tax');
        $qty = $item->getQty() ?: $item->getQtyOrdered();
        $rowTotal = $item->getData('row_total_incl_tax');
        $rowDiscount = round((-1.00) * $item->getDiscountAmount(), 2);

        /** ==== Start Calculate Percentage. The heart of logic. ==== */

        /** @var float
         * This is the fraction denominator (rowTotal / sum). If the discount should apply to all positions,
         * then this is subTotal. If the positions without discounts should remain unchanged - then this is
         * subTotal minus all positions without discounts.
         */
        $denominator = $subTotal - $this->discountlessSum;

        if ($this->spreadDiscOnAllUnits
            || ($subTotal == $this->discountlessSum)
            || ($superGrandDiscount !== 0.00)) {
            $denominator = $subTotal;
        }

        $rowPercentage = $rowTotal / $denominator;

        /** ==== End Calculate Percentage. ==== */

        if (!$this->spreadDiscOnAllUnits
            && ($rowDiscount === 0.00)
            && ($superGrandDiscount === 0.00)) {
            $rowPercentage = 0;
        }

        if ($this->spreadDiscOnAllUnits) {
            $rowDiscount = 0;
        }

        $discountPerUnit = $this->slyCeil(
            ($rowDiscount + $rowPercentage * $grandDiscount) / $qty
        );

        $priceWithDiscount = bcadd($price, (string) $discountPerUnit, 2);

        /** Set Recalculated unit price for the item */
        $item->setData(self::NAME_UNIT_PRICE, $priceWithDiscount);

        $rowTotalNew = round($priceWithDiscount * $qty, 2);

        $rowDiscountNew = $rowDiscount + round($rowPercentage * $grandDiscount, 2);

        $rowDiff = round($rowTotal + $rowDiscountNew - $rowTotalNew, 2) * 100;

        $item->setData(self::NAME_ROW_DIFF, $rowDiff);
    }

    /**
     * Returns a discount on the entire order (if any). For example, rewardPoints or storeCredit.
     * If there is no discount - returns 0.00.
     *
     * @param AbstractModel $entity
     *
     * @return float
     */
    public function getGlobalDiscount(AbstractModel $entity): float
    {
        $items = $this->getAllItems($entity);
        $totalItemsSum = 0;
        foreach ($items as $item) {
            $totalItemsSum += $item->getData('row_total_incl_tax');
        }

        $entityDiscount = $entity->getDiscountAmount() ?? 0.00;
        $shippingAmount = $entity->getShippingInclTax() ?? 0.00;
        $grandTotal = $this->getGrandTotal($entity);
        $discount = round($entityDiscount, 2);

        return round($grandTotal - $shippingAmount - $totalItemsSum - $discount, 2);
    }

    /**
     * Calculates extra discounts and adds them to items $item->setData('discount_amount', ...).
     *
     * @param AbstractModel $entity
     *
     * @return int count of iterations
     */
    public function preFixLowDiscount($entity): int
    {
        $items = $this->getAllItems($entity);
        $globalDiscount = $this->getGlobalDiscount($entity);

        $sign = (int) ($globalDiscount / abs($globalDiscount));
        $iterator = (int) round(abs($globalDiscount) * 100);
        $count = count($items);
        $iter = 0;

        while ($iterator > 0) {
            $item = current($items);

            $itDisc = $item->getData('discount_amount');
            $itTotal = $item->getData('row_total_incl_tax');

            $inc = $this->getDiscountIncrement($sign * $iterator, $count, $itTotal, $itDisc);
            $item->setData('discount_amount', $itDisc - $inc / 100);
            $iterator = (int) ($iterator - abs($inc));

            $next = next($items);
            if (!$next) {
                reset($items);
            }
            $iter++;
        }

        return $iter;
    }

    /**
     * Calculates extra discounts and adds them to items rowDiscount value.
     *
     * @param AbstractModel $entity
     *
     * @return int count of iterations
     */
    public function postFixLowDiscount(AbstractModel $entity): int
    {
        $items = $this->getAllItems($entity);
        $grandTotal = $this->getGrandTotal($entity);
        $shippingAmount = $entity->getShippingInclTax() ?? 0.00;

        $newItemsSum = 0;
        $rowDiffSum = 0;
        foreach ($items as $item) {
            $qty = $item->getQty() ?: $item->getQtyOrdered();
            $rowTotalNew = $item->getData(self::NAME_UNIT_PRICE) * $qty
                + ($item->getData(self::NAME_ROW_DIFF) / 100);
            $rowDiffSum += $item->getData(self::NAME_ROW_DIFF);
            $newItemsSum += $rowTotalNew;
        }

        $lostDiscount = round($grandTotal - $shippingAmount - $newItemsSum, 2);

        if ($lostDiscount === 0.00) {
            return 0;
        }

        $sign = (int) ($lostDiscount / abs($lostDiscount));
        $iterator = (int) round(abs($lostDiscount) * 100);
        $count = count($items);
        $iter = 0;
        reset($items);
        while ($iterator > 0) {
            $item = current($items);

            $qty = $item->getQty() ?: $item->getQtyOrdered();
            $rowDiff = $item->getData(self::NAME_ROW_DIFF);
            $itTotalNew = $item->getData(self::NAME_UNIT_PRICE) * $qty + $rowDiff / 100;

            $inc = $this->getDiscountIncrement($sign * $iterator, $count, $itTotalNew, 0);

            $item->setData(self::NAME_ROW_DIFF, $item->getData(self::NAME_ROW_DIFF) + $inc);
            $iterator = (int) ($iterator - abs($inc));

            $next = next($items);
            if (!$next) {
                reset($items);
            }
            $iter++;
        }

        return $iter;
    }

    /**
     * Calculates how many cents can be added to item
     * considering number of items, rowTotal and rowDiscount.
     *
     * @param int $amountToSpread (in cents)
     * @param int $itemsCount
     * @param float $itemTotal
     * @param float $itemDiscount
     *
     * @return int
     */
    protected function getDiscountIncrement(
        int $amountToSpread,
        int $itemsCount,
        float $itemTotal,
        float $itemDiscount
    ): int {
        $sign = (int) ($amountToSpread / abs($amountToSpread));

        /** Trying to spread evenly */
        $discPerItem = (int) (abs($amountToSpread) / $itemsCount);
        $inc = ($discPerItem > 1) && ($itemTotal - $itemDiscount) > $discPerItem
            ? $sign * $discPerItem
            : $sign;

        /** Change position discount */
        if (($itemTotal - $itemDiscount) > abs($inc)) {
            return $inc;
        }

        return 0;
    }

    /**
     * It checks do we need to spread discount on all units and sets flag.
     * $this->spreadDiscOnAllUnits.
     *
     * @param AbstractModel $entity
     *
     * @return bool
     */
    public function checkSpread(AbstractModel $entity): bool
    {
        $items = $this->getAllItems($entity);

        $this->discountlessSum = 0.00;
        foreach ($items as $item) {
            $qty = $item->getQty() ?: $item->getQtyOrdered();
            $rowPrice = $item->getData('row_total_incl_tax') - $item->getData('discount_amount');

            if ((float) $item->getData('discount_amount') === 0.00) {
                $this->discountlessSum += $item->getData('row_total_incl_tax');
            }

            /** Means that there is an item whose price is not completely divided */
            if (!$this->wryItemUnitPriceExists) {
                $decimals = $this->getDecimalsCountAfterDiv($rowPrice, (float) $qty);

                $this->wryItemUnitPriceExists = $decimals > 2 ? true : false;
            }
        }

        /** Is there a general discount on a check. bccomp returns 0 if operands are equal */
        if (bccomp((string) $this->getGlobalDiscount($entity), '0.00', 2) !== 0) {
            return true;
        }

        /** ok, there is a product that is not completely divided */
        if ($this->wryItemUnitPriceExists) {
            return true;
        }

        if ($this->spreadDiscOnAllUnits) {
            return true;
        }

        return false;
    }

    /**
     * Get Decimals Count After Div.
     *
     * @param float $rowPrice
     * @param float $qty
     *
     * @return float
     */
    protected function getDecimalsCountAfterDiv(float $rowPrice, float $qty): float
    {
        $divRes = (string) round($rowPrice / $qty, 20);
        $pos = strrchr($divRes, '.');

        return $pos !== false ? strlen($pos) - 1 : 0;
    }
}
