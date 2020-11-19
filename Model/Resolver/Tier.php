<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_BetterTierPriceGraphQl
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

declare(strict_types=1);

namespace Mageplaza\BetterTierPriceGraphQl\Model\Resolver;

use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Mageplaza\BetterTierPrice\Helper\Data;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Mageplaza\BetterTierPrice\Plugin\Api\Model\GetTierPriceList;

/**
 * Class Tier
 * @package Mageplaza\BetterTierPriceGraphQl\Model\Resolver
 */
class Tier implements ResolverInterface
{

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var GetTierPriceList
     */
    protected $tierListRepository;

    /**
     * @var SessionFactory
     */
    protected $customerSession;

    /**
     * Categories constructor.
     *
     * @param Data $helperData
     * @param ProductRepository $productRepository
     * @param GetTierPriceList $tierListRepository
     * @param SessionFactory $customerSession
     */
    public function __construct(
        Data $helperData,
        ProductRepository $productRepository,
        GetTierPriceList $tierListRepository,
        SessionFactory $customerSession
    ) {
        $this->_helperData        = $helperData;
        $this->productRepository  = $productRepository;
        $this->tierListRepository = $tierListRepository;
        $this->customerSession    = $customerSession;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->validateArgsPage($args);

        $product      = $this->productRepository->getById($args['productId']);
        $tierList     = [];
        $price        = $product->getFinalPrice();
        $specificList = $product->getMpSpecificCustomer();
        $customerId   = $context->getUserId();

        if ($customerId) {
            $groupId = $this->customerSession->create()->getCustomer()->getGroupId();
        } else {
            $groupId = 0;
        }

        if (!is_array($specificList)) {
            $specificList = Data::jsonDecode($specificList);
        }

        if (is_array($specificList)
            && count($specificList) > 0
            && $this->_helperData->isSpecificCustomerEnabled()
        ) {
            foreach ($specificList as $item) {
                $tier = $this->tierListRepository->getTierSpecificList($item, $price);

                if ((int)$tier['customer_id'] === $customerId) {
                    $tierList[] = $tier;
                }
            }
        }

        $normalList = $product->getTierPrices();
        if (is_array($normalList) && count($normalList) > 0) {
            foreach ($normalList as $item) {
                $tier = $this->tierListRepository->getTierNormalList($item, $price);

                foreach (explode(',',$tier['customer_group_id']) as $subGroup) {
                    if ((int)$subGroup === $groupId) {
                        $tierList[] = $tier;
                    }
                }
            }
        }

        $pageInfo = $this->getPageInfo($tierList, $args);

        return [
            'total_count' => count($tierList),
            'items'       => $tierList,
            'pageInfo'    => $pageInfo
        ];
    }

    /**
     * @param array $searchResult
     * @param $args
     *
     * @return array
     * @throws GraphQlInputException
     */
    public function getPageInfo($searchResult, $args)
    {
        //possible division by 0
        if (count($searchResult)) {
            $maxPages = ceil(count($searchResult) / $args['pageSize']);
        } else {
            $maxPages = 0;
        }
        $currentPage = $args['currentPage'];

        if ($currentPage > $maxPages && count($searchResult) > 0) {
            throw new GraphQlInputException(
                __(
                    'currentPage value %1 specified is greater than the %2 page(s) available.',
                    [$currentPage, $maxPages]
                )
            );
        }

        return [
            'pageSize'        => $args['pageSize'],
            'currentPage'     => $currentPage,
            'hasNextPage'     => $currentPage < $maxPages,
            'hasPreviousPage' => $currentPage > 1,
            'startPage'       => 1,
            'endPage'         => $maxPages,
        ];
    }

    /**
     * @param array $args
     *
     * @throws GraphQlInputException
     */
    public function validateArgsPage(array $args)
    {
        if (isset($args['currentPage']) && $args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }

        if (isset($args['pageSize']) && $args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
    }
}