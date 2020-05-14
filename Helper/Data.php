<?php
/**
 * Mmd Atol helper
 *
 * @author Dmytro Kohut <dmkoh@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Mmd\Atol\Helper;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\Encryptor;
use Magento\GiftCard\Model\Catalog\Product\Type\Giftcard as ProductType;
use Magento\Sales\Model\AbstractModel;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 *
 * @package Mmd\Atol\Helper
 */
class Data extends AbstractHelper
{
    /**
     * #@+
     * Atol helper const.
     */
    const STATUS_NEW = 1;
    const STATUS_NEW_LABEL = 'New';
    const STATUS_WAIT = 2;
    const STATUS_WAIT_LABEL = 'Wait';
    const STATUS_DONE = 3;
    const STATUS_DONE_LABEL = 'Done';
    const CONFIG_CODE = 'mmd_atol';
    const STORE_EMAIL = 'trans_email/ident_general/email';
    const ATOL_LOGIN = 'atol/login';
    const ATOL_PASSWORD = 'atol/password';
    const ATOL_WEBSERVICE_URL = 'atol/webservice_url';
    const ATOL_WEBSERVICE_TIMEOUT = 'atol/webservice_timeout';
    const ATOL_GROUP_CODE = 'atol/group_code';
    const SHIPPING_TAX = 'general/shipping_tax';
    const TAX_OPTIONS = 'general/tax_options';
    const DEFAULT_SHIPPING_NAME = 'general/default_shipping_name';
    const CUSTOM_SHIPPING_NAME = 'general/custom_shipping_name';
    const ATOL_ENABLED = 'general/enabled';
    const PAYMENT_ADDRESS = 'atol/payment_address';
    const ATOL_SNO = 'atol/sno';
    const ATOL_INN = 'atol/inn';
    const WITHOUT_VAT = 'vat/none';
    const VAT0 = 'vat/vat0';
    const VAT10 = 'vat/vat10';
    const VAT20 = 'vat/vat20';
    const VAT110 = 'vat/vat110';
    const VAT120 = 'vat/vat120';
    /**#@-*/

    /**
     * @var string
     */
    protected $code = self::CONFIG_CODE;

    /**
     * @var Encryptor
     */
    protected $encryptor;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * All entity products.
     *
     * @var array
     */
    protected $allEntityProducts;

    /**
     * Constructor.
     *
     * @param Encryptor $encryptor
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Context $context
     */
    public function __construct(
        Encryptor $encryptor,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Context $context
    ) {
        parent::__construct($context);

        $this->encryptor = $encryptor;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get code.
     *
     * @return string
     */
    protected function getCode(): string
    {
        return $this->code;
    }

    /**
     * Get config.
     *
     * @param string $param
     * @param string|null $scopeCode
     *
     * @return string|null
     */
    public function getConfig(string $param, ?string $scopeCode = null): ?string
    {
        return $this->scopeConfig->getValue(
            $this->getCode() . '/' . $param,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Get global config.
     *
     * @param string $configPath
     * @param string|null $scopeCode
     *
     * @return string|null
     */
    public function getGlobalConfig(string $configPath, ?string $scopeCode = null): ?string
    {
        return $this->scopeConfig->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Decrypt.
     *
     * @param string $path
     *
     * @throws Exception
     *
     * @return string
     */
    public function decrypt(string $path): string
    {
        return $this->encryptor->decrypt($path);
    }

    /**
     * Check does the item works as gift card. For Magento Commerce only.
     *
     * @param AbstractModel $salesEntity
     * @param string $itemName
     *
     * @return bool
     */
    public function isGiftCard(AbstractModel $salesEntity, string $itemName): bool
    {
        $items = $salesEntity->getAllVisibleItems() ?? $salesEntity->getAllItems();

        if (!defined('ProductType::TYPE_GIFTCARD')) {
            return false;
        }

        $allItemsIds =[];
        foreach ($salesEntity->getItemsCollection() as $item) {
            $allItemsIds[] = $item->getProductId();
        }

        foreach ($items as $item) {
            $productType = $item->getProductType()
                ?? $this->getItemTypeId($allItemsIds, (int) $item->getProductId());

            $giftCardType = ProductType::TYPE_GIFTCARD;
            if (strpos($item->getName(), $itemName) !== false && $productType == $giftCardType) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get item type id.
     *
     * @param array $allItemsIds
     * @param int $itemProductId
     *
     * @return string|null
     */
    protected function getItemTypeId(array $allItemsIds, int $itemProductId): ?string
    {
        if (!$this->allEntityProducts) {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('entity_id', $allItemsIds, 'in')
                ->create();

            $this->allEntityProducts = $this->productRepository->getList($searchCriteria);
        }

        return $this->allEntityProducts->getItems()[$itemProductId]->getTypeId();
    }

    /**
     * Is gift card applied.
     *
     * @param AbstractModel $entity
     *
     * @return bool
     */
    public function isGiftCardApplied(AbstractModel $entity): bool
    {
        return $entity->getGiftCardsAmount() + $entity->getCustomerBalanceAmount() > 0.00;
    }

    /**
     * Generate external id.
     *
     * @param AbstractModel $entity
     * @param string $postfix
     *
     * @return string
     */
    public function generateExternalId(AbstractModel $entity, string $postfix = ''): string
    {
        $postfix = $postfix ? "_{$postfix}" : '';

        return $entity->getEntityType() . '_mmd_' . $entity->getStoreId() . '_' . $entity->getIncrementId() . $postfix;
    }
}
