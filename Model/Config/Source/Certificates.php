<?php
/**
 *                  ___________       __            __
 *                  \__    ___/____ _/  |_ _____   |  |
 *                    |    |  /  _ \\   __\\__  \  |  |
 *                    |    | |  |_| ||  |   / __ \_|  |__
 *                    |____|  \____/ |__|  (____  /|____/
 *                                              \/
 *          ___          __                                   __
 *         |   |  ____ _/  |_   ____ _______   ____    ____ _/  |_
 *         |   | /    \\   __\_/ __ \\_  __ \ /    \ _/ __ \\   __\
 *         |   ||   |  \|  |  \  ___/ |  | \/|   |  \\  ___/ |  |
 *         |___||___|  /|__|   \_____>|__|   |___|  / \_____>|__|
 *                  \/                           \/
 *                  ________
 *                 /  _____/_______   ____   __ __ ______
 *                /   \  ___\_  __ \ /  _ \ |  |  \\____ \
 *                \    \_\  \|  | \/|  |_| ||  |  /|  |_| |
 *                 \______  /|__|    \____/ |____/ |   __/
 *                        \/                       |__|
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\Buckaroo\Model\Config\Source;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Option\ArrayInterface;
use TIG\Buckaroo\Api\CertificateRepositoryInterface;

class Certificates implements ArrayInterface
{
    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var CertificateRepositoryInterface */
    private $certificateRepository;

    /**
     * @param SearchCriteriaBuilder          $searchCriteriaBuilder
     * @param CertificateRepositoryInterface $certificateRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CertificateRepositoryInterface $certificateRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->certificateRepository = $certificateRepository;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $certificateData = $this->getCertificateData();

        $options = [];

        if (count($certificateData) <= 0) {
            $options[] = [
                'value' => '',
                'label' => __('You have not yet uploaded any certificate files')
            ];

            return $options;
        }

        $options[] = ['value' => '', 'label' => __('No certificate selected')];

        /** @var \TIG\Buckaroo\Model\Certificate $model */
        foreach ($certificateData as $model) {
            $options[] = [
                'value' => $model->getEntityId(),
                'label' => $model->getName() . ' (' . $model->getCreatedAt() . ')'
            ];
        }

        return $options;
    }

    /**
     * Get a list of all stored certificates
     *
     * @return array
     */
    protected function getCertificateData()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $list = $this->certificateRepository->getList($searchCriteria);

        if (!$list->getTotalCount()) {
            return [];
        }

        return $list->getItems();
    }
}
