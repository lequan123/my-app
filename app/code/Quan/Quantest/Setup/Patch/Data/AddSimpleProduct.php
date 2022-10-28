<?php

/**
 * @category Quan
 * @package  Quan\Quantest
 * @author   Le Quan <le.quan@scandiweb.com>
 */

namespace Quan\Quantest\Setup\Patch\Data;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\State;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;

class AddSimpleProduct implements DataPatchInterface
{
    /**
     * @var ProductInterfaceFactory
     */
    protected ProductInterfaceFactory $productFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var CategoryLinkManagementInterface
     */
    protected CategoryLinkManagementInterface $categoryLinkManagement;

    /**
     * @var State
     */
    protected State $appState;

    /**
     * @var SourceItemInterfaceFactory
     */
    protected SourceItemInterfaceFactory $sourceItemFactory;

    /**
     * @var SourceItemsSaveInterface
     */
    protected SourceItemsSaveInterface $sourceItemsSaveInterface;

    /**
     * CreateSimpleProduct constructor.
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryLinkManagementInterface $categoryLinkManagement
     * @param State $appState
     * @param SourceItemInterfaceFactory $sourceItemFactory
     * @param SourceItemsSaveInterface $sourceItemsSaveInterface
     */
    public function __construct(
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        State $appState,
        CategoryLinkManagementInterface $categoryLinkManagement,
        SourceItemInterfaceFactory $sourceItemFactory,
        SourceItemsSaveInterface $sourceItemsSaveInterface
    ) {
        $this->productFactory     = $productFactory;
        $this->productRepository  = $productRepository;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->appState = $appState;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
    }

    /**
     * @return void
     */
    public function apply(): void
    {
        // run setup in back-end area
        $this->appState->emulateAreaCode('adminhtml', [$this, 'execute']);
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $product = $this->productFactory->create();

        if ($product->getIdBySku('QUANTEST')) {
            return;
        }

        $simpleProductArray = [
            [
                'sku'               => 'QUANTEST',
                'name'              => 'Quan',
                'attribute_id'      => '4',
                'status'            => 1,
                'weight'            => 2,
                'price'             => 9999,
                'visibility'        => 1,
                'type_id'           => 'simple',
            ]
        ];

        foreach ($simpleProductArray as $data) {
            // Create Product
            $product = $this->productFactory->create();
            $product->setSku($data['sku'])
                ->setName($data['name'])
                ->setAttributeSetId($data['attribute_id'])
                ->setStatus($data['status'])
                ->setWeight($data['weight'])
                ->setPrice($data['price'])
                ->setVisibility($data['visibility'])
                ->setTypeId($data['type_id'])
                ->setStockData(
                    array(
                        'manage_stock' => 1,
                        'is_in_stock' => 1,
                    )
                );
            $product = $this->productRepository->save($product);
        }

        $sourceItem = $this->sourceItemFactory->create();
        $sourceItem ->setSourceCode('default');
        $sourceItem ->setQuantity(10);
        $sourceItem ->setSku($product->getSku());
        $sourceItem ->setStatus(SourceItemInterface::STATUS_IN_STOCK);
        $this->sourceItems[] = $sourceItem;

		if ($this->sourceItems) {
		    $this->sourceItemsSaveInterface->execute($this->sourceItems);
        }

        $this->categoryLinkManagement->assignProductToCategories($data['sku'], [2]);
    }

    /**
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }
}
