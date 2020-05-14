<?php
/**
 * Mmd Observer model sets ATOL statuses to invoice and creditmemo.
 *
 * @author    Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Mmd\Atol\Helper\Data;

/**
 * Class SetAtolStatus
 *
 * @package Mmd\Atol\Observer
 */
class SetAtolStatus implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $atolHelper;

    /**
     * SetAtolStatus constructor.
     *
     * @param Data $atolHelper
     */
    public function __construct(
        Data $atolHelper
    ) {
        $this->atolHelper = $atolHelper;
    }

    /**
     * Execute.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $entity = $observer->getEvent()->getInvoice() ?? $observer->getEvent()->getCreditmemo();
        if ($entity->getAtolStatus() === null) {
            $entity->setAtolStatus($this->atolHelper::STATUS_NEW)->save();
        }
    }
}
