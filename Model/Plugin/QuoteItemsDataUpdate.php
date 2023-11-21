<?php

namespace Chargeafter\Payment\Model\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface as ProductRepository;
use Magento\Checkout\Model\DefaultConfigProvider;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartItemRepositoryInterface as QuoteItemRepository;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class QuoteItemsDataUpdate extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var QuoteItemRepository
     */
    private $quoteItemRepository;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * QuoteItemsDataUpdate Construct
     *
     * @param CheckoutSession $checkoutSession
     * @param QuoteItemRepository $quoteItemRepository
     * @param ProductRepository $productRepository
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        QuoteItemRepository $quoteItemRepository,
        ProductRepository $productRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteItemRepository = $quoteItemRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * Execute method after config getting
     *
     * @param DefaultConfigProvider $subject
     * @param array $result
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, array $result)
    {
        if (isset($result['quoteItemData'])) {
            $result['quoteItemData'] = $this->updateQuoteItemData($result['quoteItemData']);
        }

        return $result;
    }

    /**
     * Update Quote items
     *
     * @param $quoteItemData
     * @return array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function updateQuoteItemData($quoteItemData)
    {
        $quoteId = $this->checkoutSession->getQuote()->getId();
        if ($quoteId) {
            $nonLeasableItems = [];

            $quoteItems = $this->quoteItemRepository->getList($quoteId);
            foreach ($quoteItems as $quoteItem) {
                $product = $this->productRepository->getById($quoteItem->getProduct()->getId());
                if ($product) {

                    $attribute = $product->getDataByKey('chargeafter_non_leasable');
                    if ($attribute) {
                        $nonLeasableItems[$quoteItem->getItemId()] = $attribute;
                    }
                }
            }

            $nonLeasableCallable = function ($item) use ($nonLeasableItems) {
                $item['ca_is_leasable'] = !key_exists($item['item_id'], $nonLeasableItems);
                return $item;
            };

            $quoteItemData = array_map($nonLeasableCallable, $quoteItemData);
        }

        return $quoteItemData;
    }
}
