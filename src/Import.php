<?php
/**
 * DRUPAL 8 NER importer.
 * Copyright (C) 2017. Tarik Curto <centro.tarik@live.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 */

namespace Drupal\ner_import;

use Drupal\content_type_tool\CreateContentType;
use Drupal\ner\DefinitionEntity;
use Drupal\ner\ObjectEntity;
use Drupal\ner\PropertyDefinitionEntity;

/**
 * Import NER processed data.
 *
 * @package Drupal\ner_import
 */
class Import {
    /**
     * @var ObjectEntity[]
     */
    protected $objectEntityList;

    /**
     * @var ObjectEntity
     */
    protected $objectEntity;

    /**
     * @var DefinitionEntity
     */
    protected $definitionEntity;

    /**
     * @var PropertyDefinitionEntity
     */
    protected $propertyDefinitionEntity;

    /**
     * All config nodes of current object
     * instance.
     *
     * [nodeId => node]
     *
     * @var CreateContentType[]
     */
    protected $contentTypeList;

    /**
     * Current ContentType instance
     *
     * @var CreateContentType
     */
    protected $contentType;

    /**
     * All processed field list.
     *
     * @var string[]
     */
    protected $fieldNameList;

    public function __construct() {
        $this->contentType = new CreateContentType();
    }

    /**
     * Create custom content type using
     * a multiples instances of ner\ObjectEntity.
     *
     * @param ObjectEntity[] $objectEntityList
     */
    public function contentTypeByObjectEntityList($objectEntityList) {

        $objectEntityKeyList = array_keys($objectEntityList);
        for ($i = 0; $i < count($objectEntityList); $i++) {

            $this->objectEntity = $objectEntityList[$objectEntityKeyList[$i]];

            if ($i == 0) {
                $this->nodeTypeConfig();
                $this->nodeFieldBodyConfig();
            }

            if (is_array($this->objectEntity->getDefinitionMap()))
                $this->nodeFieldListConfigByDefinitionEntityList($this->objectEntity->getDefinitionMap());
        }

        $this->contentType->setEntityDisplay();
        $this->contentType->save();
    }

    /**
     * Build node type config using current
     * instance of ner\ObjectEntity
     *
     * @return void
     */
    protected function nodeTypeConfig() {

        $nodeType = [];
        $nodeId = TransformImport::idByString($this->objectEntity->getType());

        // TODO: Update this
        /*if(isset($this->contentTypeList[$nodeId]))
            return;*/

        $nodeType['type'] = $nodeId;
        $nodeType['name'] = TransformImport::nameByString($this->objectEntity->getType());

        $this->contentType->setNodeType($nodeType);
    }

    /**
     * Build node type config using current
     * node type config
     *
     * @return void
     */
    protected function nodeFieldBodyConfig() {

        $this->contentType->addFieldBody();
    }

    /**
     *
     * @param DefinitionEntity[] $definitionEntityList
     * @return void
     */
    protected function nodeFieldListConfigByDefinitionEntityList(array $definitionEntityList) {

        foreach ($definitionEntityList as $definitionEntity)
            $this->nodeFieldListConfigByDefinitionEntity($definitionEntity);
    }

    /**
     *
     * @param DefinitionEntity $definitionEntity
     * @return void
     */
    protected function nodeFieldListConfigByDefinitionEntity(DefinitionEntity $definitionEntity) {

        $this->definitionEntity = $definitionEntity;
        $this->nodeFieldListConfigByPropertyDefinitionEntityList($definitionEntity->getPropertyDefinitionMap());
    }

    /**
     *
     * @param PropertyDefinitionEntity[] $propertyDefinitionEntityList
     * @return void
     */
    protected function nodeFieldListConfigByPropertyDefinitionEntityList(array $propertyDefinitionEntityList) {

        foreach ($propertyDefinitionEntityList as $propertyDefinitionEntity)
            $this->nodeFieldConfigByPropertyDefinitionEntity($propertyDefinitionEntity);
    }

    /**
     *
     * @param PropertyDefinitionEntity $propertyDefinitionEntity
     * @return void
     */
    protected function nodeFieldConfigByPropertyDefinitionEntity(PropertyDefinitionEntity $propertyDefinitionEntity) {

        $this->propertyDefinitionEntity = $propertyDefinitionEntity;

        $nodeField['field_name'] = 'field_' . TransformImport::idByString($this->propertyDefinitionEntity->getProperty());
        $nodeField['label'] = TransformImport::nameByString($nodeField['field_name']);

        if (in_array($nodeField['field_name'], $this->fieldNameList))
            return;

        $this->fieldNameList[] = $nodeField['field_name'];

        $this->contentType->addField($nodeField, 'string_textfield');
    }

    /**
     * Create custom content type using
     * a instance of ner\ObjectEntity.
     *
     * @param ObjectEntity $objectEntity
     */
    public function contentTypeByObjectEntity(ObjectEntity $objectEntity) {
        $this->objectEntity = $objectEntity;

        $this->nodeTypeConfig();
        $this->nodeFieldBodyConfig();

        if (is_array($this->objectEntity->getDefinitionMap()))
            $this->nodeFieldListConfigByDefinitionEntityList($this->objectEntity->getDefinitionMap());

        $this->contentType->setEntityDisplay();
        $this->contentType->save();
    }
}