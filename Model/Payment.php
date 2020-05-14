<?php
/**
 * Mmd payment model.
 *
 * @author Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Model;

use JsonSerializable;
use Mmd\Atol\Api\PaymentInterface;

/**
 * Class Payment
 *
 * @package Mmd\Atol\Model
 */
class Payment implements JsonSerializable, PaymentInterface
{
    /**
     * @var int
     */
    protected $type = 0;

    /**
     * @var float
     */
    protected $sum = 0.00;

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->getType(),
            'sum' => $this->getSum(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function setType(int $type): PaymentInterface
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSum(): float
    {
        return $this->sum;
    }

    /**
     * @inheritDoc
     */
    public function setSum(float $sum): PaymentInterface
    {
        $this->sum = $sum;

        return $this;
    }
}
