<?php
/**
 * Report invoice.
 *
 * @author    Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\WebserviceFunction;

/**
 * Class Report
 *
 * @package Mmd\Atol\WebserviceFunction
 */
class Report extends AbstractFunction
{
    /**
     * Additional url part for report invoice.
     *
     * @return string
     */
    public function getAdditionnalUrlPart(): string
    {
        return $this->object->getData('groupCode') .
            '/report/' . $this->object->getData('uuid') .
            '?token=' . $this->object->getData('token');
    }

    /**
     * Get method.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return self::METHOD_GET;
    }

    /**
     * Get data encode type.
     *
     * @return string
     */
    public function getDataEncodeType(): string
    {
        return self::TYPE_NONE;
    }

    /**
     * Get data.
     */
    public function getData()
    {
        return null;
    }
}
