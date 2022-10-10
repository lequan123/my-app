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
use Magento\Catalog\Api\Data\CategoryProductLinkInterface;

class AddSimpleProduct implements DataPatchInterface
{
    /**
     * @var ProductInterfaceFactory
     */
    protected $productFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * CreateSimpleProduct constructor.
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        State $state,
        CategoryLinkManagementInterface $categoryLinkManagement
    )
    {
        $this->productFactory     = $productFactory;
        $this->productRepository  = $productRepository;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $state->setAreaCode('adminhtml');
    }

    /**
     * @return string
     */
    public function apply()
    {
        $product = $this->productFactory->create();

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
                        'use_config_manage_stock' => 0,
                        'manage_stock' => 1,
                        'is_in_stock' => 1,
                        'qty' => 199
                    )
                );
            $product = $this->productRepository->save($product);
            $product->save();
        }

        $this->categoryLinkManagement->assignProductToCategories($data['sku'], [2]);
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
