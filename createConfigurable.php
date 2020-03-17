<?php


namespace Aventi\Product\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Create extends Command
{


    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurable;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    private $type;
    /**
     * @var \Magento\Catalog\Setup\CategorySetup
     */
    private $setup;
    /**
     * @var \Magento\CatalogInventory\Model\Stock\Item
     */
    private $item;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;
    /**
     * @var \Magento\CatalogInventory\Api\StockRepositoryInterface
     */
    private $stockRepository;
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;
    /**
     * @var \Magento\ConfigurableProduct\Helper\Product\Options\Factory
     */
    private $optionFactory;

    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionInterface
     */


    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Catalog\Setup\CategorySetup $setup,
        \Magento\CatalogInventory\Model\Stock\Item $item,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockRepositoryInterface $stockRepository,
        \Magento\Framework\App\State $state,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\ConfigurableProduct\Helper\Product\Options\Factory $optionFactory
    )
    {
        parent::__construct();
        $this->configurable = $configurable;
        $this->productRepository = $productRepository;
        $this->type = $type;
        $this->setup = $setup;
        $this->item = $item;
        $this->productFactory = $productFactory;
        $this->stockRegistry = $stockRegistry;
        $this->stockRepository = $stockRepository;
        $this->state = $state;
        $this->eavConfig = $eavConfig;
        $this->optionFactory = $optionFactory;
    }


    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {

        $this->state->setAreaCode( \Magento\Framework\App\Area::AREA_FRONTEND);

        $attributeSetId =   $this->setup->getAttributeSetId('catalog_product', 'Default');

        $output->writeln("Category id ".$attributeSetId);

        $products = ['Yellow','Black','Blue'];
        $stock = '10000';

        /*foreach ($products as $p){

            $product = $this->productFactory->create();
            $product->setName('Product '.$p)
                    ->setSku('Product '.$p)
                    ->setPrice(100000)
                    ->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
                    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE)
                    ->setCustomAttribute('color', $p)
                    ->setAttributeSetId(4);
            $this->productRepository->save($product);
            $stockItemFull = $this->stockRegistry->getStockItem($product->getId());
            $stockItemFull->setQty($stock);
            $stockItemFull->setIsInStock(($stock > 0) ? 1 : 0);
            $stockItemFull->save();
            //$this->stockRepository->save($stockItemFull);

        }*/


        $attribute = $this->eavConfig->getAttribute('catalog_product', 'color');
        $options = $attribute->getSource()->getAllOptions();
        array_shift($options);
        foreach ($options  as $option){
            $attributeValues[] = [
                'label' => $option['label'],
                'attribute_id' => $attribute->getId(),
                'value_index' => $option['value']
            ];
        }

        $associatedProductIds = [2,3,4];

        $configurableAttributesData = [
            [
                'attribute_id' => $attribute->getId(),
                'code' => $attribute->getAttributeCode(),
                'label' => $attribute->getStoreLabel(),
                'position' => '0',
                'values' => $attributeValues,
            ],
        ];

        $product = $this->productFactory->create();
        $configurableOptions = $this->optionFactory->create($configurableAttributesData);

        $extensionConfigurableAttributes = $product->getExtensionAttributes();
        $extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
        $extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);
        $product->setExtensionAttributes($extensionConfigurableAttributes);

        $product->setTypeId('configurable')
            ->setAttributeSetId($attributeSetId)
            ->setWebsiteIds([0])
            ->setName('Configurable Product')
            ->setSku('configurable')
            ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);

        $this->productRepository->save($product);




    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("aventi_product:create");
        $this->setDescription("test create product");
        parent::configure();
    }
}
