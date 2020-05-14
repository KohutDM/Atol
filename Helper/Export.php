<?php
/**
 * Mmd Export helper.
 *
 * @author Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class Data
 *
 * @package Mmd\Atol\Helper
 */
class Export extends AbstractHelper
{
    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Export constructor.
     *
     * @param Context $context
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        InvoiceRepositoryInterface $invoiceRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
        $this->invoiceRepository = $invoiceRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get not exported invoices.
     *
     * @param int $status
     *
     * @return InvoiceInterface[]
     */
    public function getInvoicesByAtolStatus(int $status): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('Atol_Status', $status)
            ->create();
        $searchResult = $this->invoiceRepository->getList($searchCriteria);

        return $searchResult->getItems();
    }

    /**
     * Get not exported creditmemo.
     *
     * @param int $status
     *
     * @return CreditmemoInterface[]
     */
    public function getCreditmemosByAtolStatus(int $status): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('Atol_Status', $status)
            ->create();
        $searchResult = $this->creditmemoRepository->getList($searchCriteria);

        return $searchResult->getItems();
    }
}
