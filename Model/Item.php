<?php
/**
 * Mmd product item model.
 *
 * @author Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Model;

use Exception;
use JsonSerializable;
use Mmd\Atol\Api\ItemInterface;
use Mmd\Atol\Model\Source\Tax;
use Mmd\Atol\Exception\NonFatalErrorException;

/**
 * Class Item
 *
 * @package Mmd\Atol\Model
 */
class Item implements JsonSerializable, ItemInterface
{
    /**
     * #@+
     * Item const.
     */
    const PAYMENT_METHOD_FULL_PAYMENT = 'full_payment';
    const PAYMENT_METHOD_FULL_PREPAYMENT = 'full_prepayment';
    const PAYMENT_METHOD_ADVANCE = 'advance';
    const PAYMENT_OBJECT_BASIC = 'commodity';
    const PAYMENT_OBJECT_SERVICE = 'service';
    const PAYMENT_OBJECT_PAYMENT = 'payment'; //Advance, Bonus, Gift Card
    const PAYMENT_OBJECT_ANOTHER = 'another';
    /**#@-*/

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var float
     */
    protected $price = 1.0;

    /**
     * @var int
     */
    protected $quantity = 1;

    /**
     * @var float
     */
    protected $sum = 0.0;

    /**
     * @var string
     */
    protected $tax = '';

    /**
     * @var float
     */
    protected $taxSum = 0.0;

    /**
     * @var string
     */
    protected $paymentMethod = '';

    /**
     * @var string
     */
    protected $paymentObject = '';

    /**
     * @var string
     */
    protected $countryCode = '';

    /**
     * @var string
     */
    protected $customsDeclaration = '';

    /**
     * @var Tax
     */
    protected $taxSource;

    /**
     * Item constructor.
     *
     * @param Tax $taxSource
     */
    public function __construct(Tax $taxSource)
    {
        $this->taxSource = $taxSource;
    }

    /**
     * Json serialize.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $item = [
            'name' => $this->getName(),
            'price' => $this->getPrice(),
            'quantity' => $this->getQuantity(),
            'sum' => $this->getSum(),
            'payment_method' => $this->getPaymentMethod(),
            'payment_object' => $this->getPaymentObject(),
            'vat' => [
                'type' => $this->getTax(),
            ],
        ];

        if ($this->getTaxSum()) {
            $item['vat']['sum'] = $this->getTaxSum();
        }

        if ($this->getCountryCode()) {
            $item['country_code'] = $this->getCountryCode();
        }

        if ($this->getCustomsDeclaration()) {
            $item['declaration_number'] = $this->getCustomsDeclaration();
        }

        return $item;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName(string $name): ItemInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @inheritdoc
     */
    public function setPrice(float $price): ItemInterface
    {
        $this->price = (float) $price;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQuantity(): float
    {
        return $this->quantity;
    }

    /**
     * @inheritdoc
     */
    public function setQuantity(float $quantity): ItemInterface
    {
        $this->quantity = (float) $quantity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSum(): float
    {
        return $this->sum;
    }

    /**
     * @inheritdoc
     */
    public function setSum(float $sum): ItemInterface
    {
        $this->sum = (float) $sum;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTax(): string
    {
        return $this->tax;
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function setTax(string $tax): ItemInterface
    {
        if (!in_array($tax, $this->taxSource->getAllTaxes(), true)) {
            throw new NonFatalErrorException("Incorrect tax {$tax} for Item {$this->getName()}");
        }

        $this->tax = $tax;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTaxSum(): float
    {
        return $this->taxSum;
    }

    /**
     * @inheritdoc
     */
    public function setTaxSum(float $taxSum): ItemInterface
    {
        $this->taxSum = round($taxSum, 2);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    /**
     * @inheritdoc
     */
    public function setPaymentMethod(string $paymentMethod): ItemInterface
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentObject(): string
    {
        return $this->paymentObject;
    }

    /**
     * @inheritdoc
     */
    public function setPaymentObject(string $paymentObject): ItemInterface
    {
        $this->paymentObject = $paymentObject;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * @inheritdoc
     */
    public function setCountryCode(string $countryCode): ItemInterface
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomsDeclaration(): string
    {
        return $this->customsDeclaration;
    }

    /**
     * @inheritdoc
     */
    public function setCustomsDeclaration(string $customsDeclaration): ItemInterface
    {
        $this->customsDeclaration = $customsDeclaration;

        return $this;
    }
}
