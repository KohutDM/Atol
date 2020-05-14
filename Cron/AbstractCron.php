<?php
/**
 * Abstract Cron task.
 *
 * @author    Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Cron;

use Magento\Store\Model\StoreManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Smile\Connector\Api\Task\ManagerInterface;
use Smile\Connector\Helper\Task;

/**
 * Class AbstractCron
 *
 * @package Mmd\Atol\Cron
 */
abstract class AbstractCron
{
    /**
     * @var Task init a new task.
     */
    protected $taskHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * RunImport constructor.
     *
     * @param Task $taskHelper
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Task $taskHelper,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager
    ) {
        $this->taskHelper = $taskHelper;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    /**
     * Execute.
     *
     * @throws Exception
     *
     * @return bool
     */
    public function execute(): bool
    {
        return $this->executeTask();
    }

    /**
     * Execute task.
     *
     * @throws Exception
     *
     * @return bool
     */
    public function executeTask(): bool
    {
        $taskManager = $this->initTask();
        try {
            $taskManager->execute();
            $this->logger->debug($this->getTaskCode() . ' is complete.');
        } catch (Exception $e) {
            $this->logger->debug($this->getTaskCode() . ': ' . $e->getMessage());
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
        $taskManager = $this->taskHelper->init($this->getTaskCode());
        $taskManager->getContext()->setWebsiteId($this->getDefaultWebsiteId());

        return $taskManager;
    }

    /**
     * Get task code.
     *
     * @return string
     */
    abstract protected function getTaskCode(): string;
}
