<?php
/**
 * Cron Run creditmemo export.
 *
 * @author    Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Cron\Export;

use Exception;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mmd\Atol\Cron\AbstractCron;
use Mmd\Atol\Helper\Data as AtolHelper;
use Mmd\Atol\Helper\Export as ExportHelper;
use Mmd\Atol\Task\Export\CreditmemoExport as CreditmemoExportTask;
use Psr\Log\LoggerInterface;
use Smile\Connector\Api\Task\ManagerInterface;
use Smile\Connector\Helper\Task;

/**
 * Class CreditmemoExport
 *
 * @package Mmd\Atol\Cron\Export
 */
class CreditmemoExport extends AbstractCron
{
    /**
     * @var CreditmemoInterface
     */
    protected $creditmemo;

    /**
     * @var ExportHelper
     */
    protected $exportHelper;

    /**
     * @var AtolHelper
     */
    protected $atolHelper;

    /**
     * CreditmemoExport constructor.
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
     * Export creditmemo.
     *
     * @throws Exception
     *
     * @return bool
     */
    public function execute(): bool
    {
        $creditmemos = $this->exportHelper->getCreditmemosByAtolStatus($this->atolHelper::STATUS_NEW);
        if ($this->atolHelper->getConfig(AtolHelper::ATOL_ENABLED) && $creditmemos) {
            foreach ($creditmemos as $creditmemo) {
                $this->setCurrentCreditmemo($creditmemo);
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
        $taskManager->setMainObject($this->getCurrentCreditmemo());

        return $taskManager;
    }

    /**
     * Get task code.
     *
     * @return string
     */
    protected function getTaskCode(): string
    {
        return 'atol/' . CreditmemoExportTask::TASK_CODE;
    }

    /**
     * Get website id for current сreditmemo.
     *
     * @return int
     */
    protected function getDefaultWebsiteId(): int
    {
        return (int) $this->creditmemo->getStore()->getWebsiteId();
    }

    /**
     * Set current сreditmemo.
     *
     * @param $creditmemo
     *
     * @return $this
     */
    protected function setCurrentCreditmemo(CreditmemoInterface $creditmemo): CreditmemoExport
    {
        $this->creditmemo = $creditmemo;

        return $this;
    }

    /**
     * Get current сreditmemo.
     *
     * @return CreditmemoInterface
     */
    protected function getCurrentCreditmemo(): CreditmemoInterface
    {
        return $this->creditmemo;
    }
}
