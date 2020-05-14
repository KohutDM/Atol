<?php
/**
 * Cron Run invoice export.
 *
 * @author    Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Cron\Export;

use Exception;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mmd\Atol\Cron\AbstractCron;
use Mmd\Atol\Helper\Data as AtolHelper;
use Mmd\Atol\Helper\Export as ExportHelper;
use Mmd\Atol\Task\Export\InvoiceExport as InvoiceExportTask;
use Psr\Log\LoggerInterface;
use Smile\Connector\Api\Task\ManagerInterface;
use Smile\Connector\Helper\Task;

/**
 * Class InvoiceExport
 *
 * @package Mmd\Atol\Cron\Export
 */
class InvoiceExport extends AbstractCron
{
    /**
     * @var InvoiceInterface
     */
    protected $invoice;

    /**
     * @var ExportHelper
     */
    protected $exportHelper;

    /**
     * @var AtolHelper
     */
    protected $atolHelper;

    /**
     * InvoiceExport constructor.
     *
     * @param Task $taskHelper
     * @param AtolHelper $atolHelper
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param ExportHelper $exportHelper
     */
    public function __construct(
        Task $taskHelper,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        ExportHelper $exportHelper,
        AtolHelper $atolHelper
    ) {
        parent::__construct($taskHelper, $logger, $storeManager);
        $this->exportHelper = $exportHelper;
        $this->atolHelper = $atolHelper;
    }

    /**
     * Export invoice.
     *
     * @throws Exception
     *
     * @return bool
     */
    public function execute(): bool
    {
        $invoices = $this->exportHelper->getInvoicesByAtolStatus($this->atolHelper::STATUS_NEW);
        if ($this->atolHelper->getConfig(AtolHelper::ATOL_ENABLED) && $invoices) {
            foreach ($invoices as $invoice) {
                $this->setCurrentInvoice($invoice);
                $this->executeTask();
            }
        }

        return true;
    }

    /**
     * Init task.
     *
     * @throws Exception
     *
     * @return ManagerInterface
     */
    protected function initTask(): ManagerInterface
    {
        $taskManager = parent::initTask();
        $taskManager->setMainObject($this->getCurrentInvoice());

        return $taskManager;
    }

    /**
     * Get task code.
     *
     * @return string
     */
    protected function getTaskCode(): string
    {
        return 'atol/' . InvoiceExportTask::TASK_CODE;
    }

    /**
     * Get website id for current invoice.
     *
     * @return int
     */
    protected function getDefaultWebsiteId(): int
    {
        return (int) $this->invoice->getStore()->getWebsiteId();
    }

    /**
     * Set current invoice.
     *
     * @param $invoice
     *
     * @return $this
     */
    protected function setCurrentInvoice(InvoiceInterface $invoice): InvoiceExport
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * Get current invoice.
     *
     * @return InvoiceInterface
     */
    protected function getCurrentInvoice(): InvoiceInterface
    {
        return $this->invoice;
    }
}
