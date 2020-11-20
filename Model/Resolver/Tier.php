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
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
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
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * Categories constructor.
     *
     * @param Data $helperData
     * @param ProductRepository $productRepository
     * @param GetTierPriceList $tierListRepository
     * @param GetCustomer $getCustomer
     */
    public function __construct(
        Data $helperData,
        ProductRepository $productRepository,
        GetTierPriceList $tierListRepository,
        GetCustomer $getCustomer
    ) {
        $this->_helperData        = $helperData;
        $this->productRepository  = $productRepository;
        $this->tierListRepository = $tierListRepository;
        $this->getCustomer = $getCustomer;
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
        /** @var ContextInterface $context */
        if ($context->getExtensionAttributes()->getIsCustomer() === false) {
            $customerId= '0';
            $groupId= '0';
        } else {
            $customer = $this->getCustomer->execute($context);
            $customerId   = $customer->getId();
            $groupId = $customer->getGroupId();
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

                if ($tier['customer_id'] === $customerId) {
                    $tierList[(int)$tier['qty']] = $tier;
                }
            }
        }

        $normalList = $product->getTierPrices();
        if (is_array($normalList) && count($normalList) > 0) {
            foreach ($normalList as $item) {
                $tier = $this->tierListRepository->getTierNormalList($item, $price);

                foreach (explode(',',$tier['customer_group_id']) as $subGroup) {

                    if ($subGroup === $groupId) {
                        $tierList[(int)$tier['qty']] = $tier;
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
