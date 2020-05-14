<?php
/**
 * Mmd Atol Discount interface.
 *
 * @author Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Api;

use Magento\Sales\Model\AbstractModel;
use Exception;

/**
 * Interface DiscountInterface
 *
 * Calculates prices of 1 unit for each item.
 * Recalculates invoice/creditmemo.
 * e.g. can spreads one item discount to all items
 *
 * @package Mmd\Atol\Api
 */
interface DiscountInterface
{
    /**
     * Set is split items allowed.
     *
     * @param bool $isSplitItemsAllowed
     *
     * @return $this
     */
    public function setIsSplitItemsAllowed(bool $isSplitItemsAllowed): DiscountInterface;

    /**
     * Set do calculation.
     *
     * @param bool $doCalculation
     *
     * @return $this
     */
    public function setDoCalculation(bool $doCalculation): DiscountInterface;

    /**
     * Set spread disc on all units.
     *
     * @param bool $spreadDiscOnAllUnits
     *
     * @return $this
     */
    public function setSpreadDiscOnAllUnits(bool $spreadDiscOnAllUnits): DiscountInterface;

    /**
     * Custom floor() function.
     *
     * @param float $val
     * @param int $precision
     *
     * @return float
     */
    public function slyFloor(float $val, int $precision = 2): float;

    /**
     * Custom ceil() function.
     *
     * @param float $val
     * @param int $precision
     *
     * @return float
     */
    public function slyCeil(float $val, int $precision = 2): float;
}
