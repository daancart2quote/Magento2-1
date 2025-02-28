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
namespace Buckaroo\Magento2\Cron;

class SecondChance
{
    /**
     * @var \Buckaroo\Magento2\Model\SecondChanceFactory
     */
    protected $secondChanceFactory;

    /**
     * @var \Buckaroo\Magento2\Model\ConfigProvider\Account
     */
    protected $accountConfig;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var Log $logging
     */
    public $logging;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var \Buckaroo\Magento2\Model\SecondChanceRepository
     */
    protected $secondChanceRepository;

    /**
     * @param \Magento\Checkout\Model\Session\Proxy                $checkoutSession
     * @param \Buckaroo\Magento2\Model\ConfigProvider\Account      $accountConfig
     * @param \Buckaroo\Magento2\Model\SecondChanceFactory         $secondChanceFactory
     */
    public function __construct(
        \Buckaroo\Magento2\Model\ConfigProvider\Account $accountConfig,
        \Buckaroo\Magento2\Model\SecondChanceFactory $secondChanceFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Buckaroo\Magento2\Logging\Log $logging,
        \Buckaroo\Magento2\Model\SecondChanceRepository $secondChanceRepository
    ) {
        $this->accountConfig       = $accountConfig;
        $this->secondChanceFactory = $secondChanceFactory;
        $this->orderFactory        = $orderFactory;
        $this->storeManager        = $storeManager;
        $this->storeRepository     = $storeRepository;

        $this->logging                = $logging;
        $this->secondChanceRepository = $secondChanceRepository;
    }

    public function execute()
    {
        $stores = $this->storeRepository->getList();
        foreach ($stores as $store) {
            if ($this->accountConfig->getSecondChance($store)) {
                foreach ([2, 1] as $step) {
                    $this->secondChanceRepository->getSecondChanceCollection($step, $store);
                }
            }
        }
        return $this;
    }
}
