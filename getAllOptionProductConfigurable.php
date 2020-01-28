

	/**
	   Retorna todas las opciones de un product configurable agrupandolas por el sku del producto hijo

	   [ 
	    ["PRD_BSL_03-Pink"]=>"Color:Pink" 
	    ["PRD_BSL_03-Viridian"]=>  "Color:Viridian" 
	    ["PRD_BSL_03-Gray"]=>  "Color:Gray" 
	  ]

	**/
    public function getOption($productId){

        $sql = <<<SQL
              SELECT `entity`.`sku`, 
                        `product_entity`.`sku` AS `parent`,
                        label.value as opcion,
                        `attribute_label`.`value` AS `super_attribute_label` 
              FROM `catalog_product_super_attribute` AS `super_attribute`
              INNER JOIN `catalog_product_entity` AS `product_entity` ON product_entity.entity_id = super_attribute.product_id
              INNER JOIN `catalog_product_super_link` AS `product_link` ON product_link.parent_id = super_attribute.product_id
              INNER JOIN `eav_attribute` AS `attribute` ON attribute.attribute_id = super_attribute.attribute_id
              INNER JOIN `catalog_product_entity` AS `entity` ON entity.entity_id = product_link.product_id
              INNER JOIN `catalog_product_entity_int` AS `entity_value` ON entity_value.attribute_id = super_attribute.attribute_id AND entity_value.store_id = 0 AND entity_value.entity_id = entity.entity_id  LEFT JOIN eav_attribute_option_value AS label on (  label.option_id =  entity_value.value  and  label.store_id = 0)
              LEFT JOIN `catalog_product_super_attribute_label` AS `attribute_label` ON super_attribute.product_super_attribute_id = attribute_label.product_super_attribute_id AND attribute_label.store_id = 0
              LEFT JOIN `eav_attribute_option` AS `attribute_option` ON attribute_option.option_id = entity_value.value WHERE (super_attribute.product_id = __PRODUCTO__) 
              ORDER BY `attribute_option`.`sort_order` ASC   
SQL;

        $sql = str_replace('__PRODUCTO__',$productId, $sql);
        $conection = $this->resourceConnection->getConnection();
        $option = $conection->fetchAll($sql);
        $options  =  [];
        foreach ($option as $o){
            $options[$o['sku']] = (isset($options[$o['sku']])) ? $options[$o['sku']].' '.__($o['super_attribute_label']).':'.$o['opcion'] : __($o['super_attribute_label']).':'.$o['opcion'];
        }

        return $options;
    }