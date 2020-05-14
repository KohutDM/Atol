<?php
/**
 * Request interface for ATOL service.
 *
 * @author Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Api;

/**
 * Interface RequestInterface
 *
 * @package Mmd\Atol\Api
 */
interface RequestInterface
{
    /**
     * #@+
     * Period const.
     */
    const SELL_OPERATION_TYPE = 1;
    const REFUND_OPERATION_TYPE = 2;
    /**#@-*/

    /**
     * To array.
     *
     * @return array
     */
    public function __toArray(): array;

    /**
     * Get sno.
     *
     * @return string
     */
    public function getSno(): string;

    /**
     * Set sno.
     *
     * @param string $sno
     *
     * @return $this
     */
    public function setSno(string $sno): RequestInterface;

    /**
     * Get email.
     *
     * @return string|null
     */
    public function getEmail(): ?string;

    /**
     * Set email.
     *
     * @param string|null $email
     *
     * @return $this
     */
    public function setEmail(?string $email): RequestInterface;

    /**
     * Get phone.
     *
     * @return string|null
     */
    public function getPhone(): ?string;

    /**
     * Set phone.
     *
     * @param string|null $phone
     *
     * @return $this
     */
    public function setPhone(?string $phone): RequestInterface;

    /**
     * Get items.
     *
     * @return ItemInterface[]
     */
    public function getItems(): array;

    /**
     * Set items.
     *
     * @param ItemInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items): self;

    /**
     * Get payments.
     *
     * @return PaymentInterface[]
     */
    public function getPayments(): array;

    /**
     * Set payments.
     *
     * @param PaymentInterface[] $payments
     *
     * @return $this
     */
    public function setPayments(array $payments): RequestInterface;

    /**
     * Invoke this method AFTER addItem() method.
     *
     * @return float
     */
    public function getTotal(): float;

    /**
     * Get external id.
     *
     * @return string
     */
    public function getExternalId(): string;

    /**
     * Set external id.
     *
     * @param string $externalId
     *
     * @return $this
     */
    public function setExternalId(string $externalId): RequestInterface;

    /**
     * Get inn.
     *
     * @return string|null
     */
    public function getInn(): ?string;

    /**
     * Set inn.
     *
     * @param string $inn
     *
     * @return $this
     */
    public function setInn(string $inn): RequestInterface;

    /**
     * Get payment address
     *
     * @return string|null
     */
    public function getPaymentAddress(): ?string;

    /**
     * Set payment address.
     *
     * @param string $paymentAddress
     *
     * @return $this
     */
    public function setPaymentAddress(string $paymentAddress): RequestInterface;

    /**
     * Get company email.
     *
     * @return string
     */
    public function getCompanyEmail(): string;

    /**
     * Set company email.
     *
     * @param string $companyEmail
     *
     * @return $this
     */
    public function setCompanyEmail(string $companyEmail): RequestInterface;

    /**
     * Add item.
     *
     * @param ItemInterface $item
     *
     * @return RequestInterface
     */
    public function addItem(ItemInterface $item): RequestInterface;

    /**
     * Add total.
     *
     * @param float $sum
     *
     * @return void
     */
    public function addTotal(float $sum): void;

    /**
     * Add payment.
     *
     * @param PaymentInterface $payment
     *
     * @return $this
     */
    public function addPayment(PaymentInterface $payment): RequestInterface;

    /**
     * Get timestamp.
     *
     * @return string
     */
    public function getTimestamp(): string;

    /**
     * Set total.
     *
     * @param float $total
     *
     * @return $this
     */
    public function setTotal(float $total): RequestInterface;

    /**
     * Set timestamp.
     *
     * @param string $timestamp
     *
     * @return $this
     */
    public function setTimestamp(string $timestamp): RequestInterface;

    /**
     * Specify the operation type (sell|refund).
     *
     * @param int $type
     *
     * @return $this
     */
    public function setOperationType(int $type): RequestInterface;

    /**
     * Get the operation type (sell|refund).
     *
     * @return int
     */
    public function getOperationType(): int;

    /**
     * Set id of basic entity (Invoice|Creditmemo).
     *
     * @param int $entityId
     *
     * @return $this
     */
    public function setSalesEntityId(int $entityId): RequestInterface;

    /**
     * Get id of basic entity (Invoice|Creditmemo).
     *
     * @return int
     */
    public function getSalesEntityId(): int;
}
