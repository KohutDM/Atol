<?php
/**
 * Abstract function class.
 *
 * @author    Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\WebserviceFunction;

use Smile\Connector\WebserviceFunction\AbstractRest;

/**
 * Class AbstractFunction
 *
 * @package Mmd\Atol\WebserviceFunction
 */
abstract class AbstractFunction extends AbstractRest
{
    /**
     * Get data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->object->getData();
    }

    /**
     * Validate response.
     *
     * @param array $response
     *
     * @return bool
     */
    public function validateResponse(&$response): bool
    {
        if ($response) {
            return true;
        }

        return false;
    }

    /**
     * Get method.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return self::METHOD_POST;
    }

    /**
     * Get data encode type.
     *
     * @return string
     */
    public function getDataEncodeType(): string
    {
        return self::TYPE_JSON;
    }

    /**
     * Get response encode type.
     *
     * @return string
     */
    public function getResponseEncodeType(): string
    {
        return self::TYPE_JSON;
    }
}
