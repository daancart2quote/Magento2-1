<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * It is available through the world-wide-web at this URL:
 * https://tldrlegal.com/license/mit-license
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to support@buckaroo.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact support@buckaroo.nl for more information.
 *
 * @copyright Copyright (c) Buckaroo B.V.
 * @license   https://tldrlegal.com/license/mit-license
 */

namespace Buckaroo\Magento2\Model\ConfigProvider;

/**
 * @method int getPriceDisplayCart()
 * @method int getPriceDisplaySales()
 * @method int getPaymentFeeTax()
 * @method string getTaxClass()
 */
class Idin extends AbstractConfigProvider
{
    const XPATH_ACCOUNT_IDIN = 'buckaroo_magento2/account/idin';

    protected $issuers = [
        [
            'name' => 'ABN AMRO',
            'code' => 'ABNANL2A',
        ],
        [
            'name' => 'ING',
            'code' => 'INGBNL2A',
        ],
        [
            'name' => 'Rabobank',
            'code' => 'RABONL2U',
        ],
        [
            'name' => 'SNS Bank',
            'code' => 'SNSBNL2A',
        ],
        [
            'name' => 'ASN Bank',
            'code' => 'ASNBNL21',
        ],
        [
            'name' => 'RegioBank',
            'code' => 'RBRBNL21',
        ],
        [
            'name' => 'Bunq Bank',
            'code' => 'BUNQNL2A',
        ],
        [
            'name' => 'Triodos Bank',
            'code' => 'TRIONL2U',
        ],
    ];

    private $storeManager;

    private $configProviderAccount;

    private $customerSession;

    private $session;

    private $productFactory;

    protected $checkoutSession;

    protected $scopeConfig;

    public function __construct(
        \Buckaroo\Magento2\Model\ConfigProvider\Account $configProviderAccount,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Checkout\Model\Session $session,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager                = $storeManager;
        $this->configProviderAccount       = $configProviderAccount;
        $this->customerSession             = $customerSession;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->session                     = $session;
        $this->productFactory              = $productFactory;
        $this->checkoutSession             = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
    }
    /**
     * Retrieve associated array of checkout configuration
     *
     * @param null $store
     *
     * @return array
     */
    public function getConfig($store = null)
    {
        $idin = $this->isIDINActive();
        return [
            'buckarooIdin' => [
                'issuers' => $this->formatIssuers(),
                'active'  => $idin['active'],
                'verified'  => $idin['verified'],
                'isOscEnabled' => intval($this->scopeConfig->getValue(
                    'onestepcheckout_iosc/general/enable',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ))
            ],
        ];
    }

    protected function isIDINActive()
    {
        $active = false;
        $verified = false;
        if ($this->configProviderAccount->getIdin($this->storeManager->getStore())) {
            foreach ($this->session->getQuote()->getAllVisibleItems() as $item) {
                $productId = $item->getProductId();
                $product   = $this->productFactory->create()->load($productId);

                switch ($this->configProviderAccount->getIdinMode($this->storeManager->getStore())) {
                    case 1:
                        if (null !== $product->getCustomAttribute('buckaroo_product_idin') && $product->getCustomAttribute('buckaroo_product_idin')->getValue() == 1) {
                            $active = true;
                        }
                        break;
                    case 2:
                        foreach ($product->getCategoryIds() as $key => $cat) {
                            if (in_array($cat, explode(',', $this->configProviderAccount->getIdinCategory()))) {
                                $active = true;
                            }
                        }
                        break;
                    default:
                        $active = true;
                        break;
                }

            }
            if ($active === true && $customerId = $this->customerSession->getCustomer()->getId()) {
                $customer              = $this->customerRepositoryInterface->getById($customerId);
                $customerAttributeData = $customer->__toArray();
                $active                = (isset($customerAttributeData['custom_attributes']) && isset($customerAttributeData['custom_attributes']['buckaroo_idin_iseighteenorolder']) && $customerAttributeData['custom_attributes']['buckaroo_idin_iseighteenorolder']['value']==1) ? false : 1;
                $verified = !$active;
            } elseif ($active === true) {
                if ($this->checkoutSession->getCustomerIDINIsEighteenOrOlder()) {
                    $active = false;
                    $verified = true;
                }
            }
        }

        return ['active' => $active, 'verified' => $verified];
    }

    protected function formatIssuers()
    {
        $all = $this->issuers;
        if ($this->configProviderAccount->getIdin($this->storeManager->getStore()) == 1) {
            array_push($all, ['name' => 'TEST BANK', 'code' => 'BANKNL2Y']);
        }

        $issuers = array_map(
            function ($issuer) {
                return $issuer;
            },
            $all
        );

        return $issuers;
    }
}
