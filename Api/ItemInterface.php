<?php
/**
 * Mmd product item interface.
 *
 * @author Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Api;

/**
 * Interface ItemInterface
 *
 * @package Mmd\Atol\Api
 */
interface ItemInterface
{
    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): ItemInterface;

    /**
     * Get price.
     *
     * @return float
     */
    public function getPrice(): float;

    /**
     * Set price.
     *
     * @param float $price
     *
     * @return $this
     */
    public function setPrice(float $price): ItemInterface;

    /**
     * Get quantity.
     *
     * @return float
     */
    public function getQuantity(): float;

    /**
     * Set quantity.
     *
     * @param float $quantity
     *
     * @return $this
     */
    public function setQuantity(float $quantity): ItemInterface;

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
    public function setSum(float $sum): ItemInterface;

    /**
     * Get tax.
     *
     * @return string
     */
    public function getTax(): string;

    /**
     * Set tax.
     *
     * @param string $tax
     *
     * @return $this
     */
    public function setTax(string $tax): ItemInterface;

    /**
     * Get tax sum.
     *
     * @return float
     */
    public function getTaxSum(): float;

    /**
     * Set tax sum.
     *
     * @param float $taxSum
     *
     * @return $this
     */
    public function setTaxSum(float $taxSum): ItemInterface;

    /**
     * Get payment method.
     *
     * @return string
     */
    public function getPaymentMethod(): string;

    /**
     * Set payment method.
     *
     * @param string $paymentMethod
     *
     * @return $this
     */
    public function setPaymentMethod(string $paymentMethod): ItemInterface;

    /**
     * Get payment object.
     *
     * @return string
     */
    public function getPaymentObject(): string;

    /**
     * Set payment object.
     *
     * @param string $paymentObject
     *
     * @return $this
     */
    public function setPaymentObject(string $paymentObject): ItemInterface;

    /**
     * Get country code.
     *
     * @return string
     */
    public function getCountryCode(): string;

    /**
     * Set country code.
     *
     * @param string $countryCode
     *
     * @return $this
     */
    public function setCountryCode(string $countryCode): ItemInterface;

    /**
     * Get custom declaration.
     *
     * @return string
     */
    public function getCustomsDeclaration(): string;

    /**
     * Set custom declaration.
     *
     * @param string $customsDeclaration
     *
     * @return $this
     */
    public function setCustomsDeclaration(string $customsDeclaration): ItemInterface;
}
