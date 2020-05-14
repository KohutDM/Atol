<?php
/**
 * Mmd Atol AbstractDiscount.
 *
 * @author Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Model;

use Magento\Sales\Model\AbstractModel;
use Mmd\Atol\Api\DiscountInterface;

/**
 * Class AbstractDiscount
 *
 * @package Mmd\Atol\Model
 */
abstract class AbstractDiscount implements DiscountInterface
{
    /**
     * #@+
     * AbstractDiscount const.
     */
    const NAME_UNIT_PRICE = 'disc_hlpr_price';
    const NAME_ROW_DIFF = 'recalc_row_diff';
    const ORIG_GRAND_TOTAL = 'origGrandTotal';
    const ITEMS = 'items';
    const SHIPPING = 'shipping';
    const NAME = 'name';
    const PRICE = 'price';
    const SUM = 'sum';
    const QUANTITY = 'quantity';
    const TAX = 'tax';
    /**#@-*/

    /**
     * @var bool Does item exist with price not divisible evenly?
     */
    protected $wryItemUnitPriceExists = false;

    /**
     * @var bool The ability to divide one product item by 2 if the price is not completely divided
     */
    protected $isSplitItemsAllowed = false;

    /**
     * @var bool Enable recalculation
     */
    protected $doCalculation = true;

    /**
     * @var bool Spread a discount on all positions
     */
    protected $spreadDiscOnAllUnits = false;

    /**
     * @var string
     */
    protected $taxValue;

    /**
     * @var string
     */
    protected $shippingTaxValue;

    /**
     * @var float
     */
    protected $factor = 1.00;

    /**
     * @var float
     */
    protected $discountlessSum = 0.0;

    /**
     * Get all items.
     *
     * @param AbstractModel $entity
     *
     * @return array
     */
    protected function getAllItems(AbstractModel $entity): array
    {
        return $entity->getAllVisibleItems()
            ? $entity->getAllVisibleItems()
            : $entity->getAllItems();
    }

    /**
     * Calculates grandTotal manually
     * due to Gift Card and Customer Balance should be visible in tax receipt.
     *
     * @param AbstractModel $entity
     *
     * @return float
     */
    protected function getGrandTotal(AbstractModel $entity): float
    {
        return round(
            $entity->getGrandTotal()
            + $entity->getData('gift_cards_amount')
            + $entity->getData('customer_balance_amount'),
            2
        );
    }

    /**
     * @inheritdoc
     */
    public function slyFloor(float $val, int $precision = 2): float
    {
        $factor = $this->factor;
        $divider = pow(10, $precision);

        if ($val < 0) {
            $factor = -1.00;
        }

        return (floor(abs($val) * $divider) / $divider) * $factor;
    }

    /**
     * @inheritdoc
     */
    public function slyCeil(float $val, int $precision = 2): float
    {
        $factor = $this->factor;
        $divider = pow(10, $precision);

        if ($val < 0) {
            $factor = -1.00;
        }

        return (ceil(abs($val) * $divider) / $divider) * $factor;
    }

    /**
     * @inheritdoc
     */
    public function setIsSplitItemsAllowed(bool $isSplitItemsAllowed): DiscountInterface
    {
        $this->isSplitItemsAllowed = $isSplitItemsAllowed;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setDoCalculation(bool $doCalculation): DiscountInterface
    {
        $this->doCalculation = $doCalculation;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setSpreadDiscOnAllUnits(bool $spreadDiscOnAllUnits): DiscountInterface
    {
        $this->spreadDiscOnAllUnits = $spreadDiscOnAllUnits;

        return $this;
    }
}
