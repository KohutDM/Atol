<?php
/**
 * Mmd request model for ATOL service.
 *
 * @author    Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Model;

use Exception;
use JsonSerializable;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Mmd\Atol\Api\ItemInterface;
use Mmd\Atol\Api\PaymentInterface;
use Mmd\Atol\Api\RequestInterface;
use Mmd\Atol\Exception\NonFatalErrorException;

/**
 * Class Request
 *
 * @package Mmd\Atol\Model
 */
class Request implements JsonSerializable, RequestInterface
{
    /**
     * Current Date format.
     */
    const DATE_FORMAT = 'd-m-Y H:i:s';

    /**
     * @var string
     */
    protected $sno = '';

    /**
     * @var string
     */
    protected $externalId = '';

    /**
     * @var string
     */
    protected $email = '';

    /**
     * @var string
     */
    protected $companyEmail = '';

    /**
     * @var string
     */
    protected $phone = '';

    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var array
     */
    protected $payments = [];

    /**
     * @var float
     */
    protected $total = 0.0;

    /**
     * @var string
     */
    protected $inn = '';

    /**
     * @var string
     */
    protected $paymentAddress = '';

    /**
     * @var int
     */
    protected $operationType = 0;

    /**
     * @var int|null
     */
    protected $salesEntityId = null;

    /**
     * @var Timezone|string
     */
    protected $date = '';

    /**
     * Request constructor.
     *
     * @param Timezone $date
     */
    public function __construct(
        Timezone $date
    ) {
        $this->date = $date;
    }

    /**
     * @inheritDoc
     */
    public function __toArray(): array
    {
        return $this->jsonSerialize();
    }

    /**
     * @inheritdoc
     */
    public function getSno(): string
    {
        return $this->sno;
    }

    /**
     * @inheritdoc
     */
    public function setSno(string $sno): RequestInterface
    {
        $this->sno = $sno;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @inheritdoc
     */
    public function setEmail(?string $email): RequestInterface
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @inheritdoc
     */
    public function setPhone(?string $phone): RequestInterface
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function setItems(array $items): RequestInterface
    {
        foreach ($items as $element) {
            $this->addItem($element);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPayments(): array
    {
        return $this->payments;
    }

    /**
     * @inheritdoc
     */
    public function setPayments(array $payments): RequestInterface
    {
        $this->payments = $payments;

        return $this;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function getTotal(): float
    {
        if (empty($this->getItems())) {
            throw new NonFatalErrorException(
                'Can not calculate totals. No items in the request'
            );
        }

        return $this->total;
    }

    /**
     * @inheritdoc
     */
    public function getExternalId(): string
    {
        return $this->externalId;
    }

    /**
     * @inheritdoc
     */
    public function setExternalId(string $externalId): RequestInterface
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getInn(): string
    {
        return $this->inn;
    }

    /**
     * @inheritdoc
     */
    public function setInn(string $inn): RequestInterface
    {
        $this->inn = $inn;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentAddress(): string
    {
        return $this->paymentAddress;
    }

    /**
     * @inheritdoc
     */
    public function setPaymentAddress(string $paymentAddress): RequestInterface
    {
        $this->paymentAddress = $paymentAddress;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCompanyEmail(): string
    {
        return $this->companyEmail;
    }

    /**
     * @inheritdoc
     */
    public function setCompanyEmail(string $companyEmail): RequestInterface
    {
        $this->companyEmail = $companyEmail;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addItem(ItemInterface $item): RequestInterface
    {
        $this->items[] = $item;
        $this->addTotal($item->getSum());

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addTotal(float $sum): void
    {
        $this->total += $sum;
    }

    /**
     * @inheritdoc
     */
    public function addPayment(PaymentInterface $payment): RequestInterface
    {
        $this->payments[] = $payment;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTimestamp(): string
    {
        return $this->date->date()->format(self::DATE_FORMAT);
    }

    /**
     * @inheritdoc
     */
    public function setTotal(float $total): RequestInterface
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTimestamp(string $timestamp): RequestInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOperationType(int $type): RequestInterface
    {
        $this->operationType = $type;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOperationType(): int
    {
        return $this->operationType;
    }

    /**
     * @inheritDoc
     */
    public function setSalesEntityId(int $entityId): RequestInterface
    {
        $this->salesEntityId = (int) $entityId;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSalesEntityId(): int
    {
        return $this->salesEntityId;
    }

    /**
     * Json serialize.
     *
     * @throws Exception
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $company = [
            'client_email' => ['email' => $this->getEmail()],
            'email' => $this->getCompanyEmail(),
            'sno' => $this->getSno(),
            'inn' => $this->getInn(),
            'payment_address' => $this->getPaymentAddress(),
        ];

        return [
            'external_id' => $this->getExternalId(),
            'receipt' => [
                'client' => $company['client_email'],
                'company' => array_slice($company, 1, null, true),
                'items' => $this->getItems(),
                'payments' => $this->getPayments(),
                'total' => $this->getTotal(),
            ],
            'timestamp' => $this->getTimestamp(),
        ];
    }
}
