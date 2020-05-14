<?php
/**
 * Mmd payment interface.
 *
 * @author Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Api;

/**
 * Interface PaymentInterface
 *
 * @package Mmd\Atol\Api
 */
interface PaymentInterface
{
    /**
     * #@+
     * Payment const.
     */
    const PAYMENT_TYPE_BASIC = 1;
    const PAYMENT_TYPE_AVANS = 2;
    /**#@-*/

    /**
     * Get type.
     *
     * @return int
     */
    public function getType(): int;

    /**
     * Set type.
     *
     * @param int $type
     *
     * @return $this
     */
    public function setType(int $type): PaymentInterface;

    /**
     * Get sum.
     *
     * @return float
     */
    public function getSum(): float;

    /**
     * Set sum.
     *
     * @param float $sum
     *
     * @return $this
     */
    public function setSum(float $sum): PaymentInterface;
}
