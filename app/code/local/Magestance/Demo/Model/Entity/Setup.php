<?php
class Magestance_Demo_Model_Entity_Setup extends Mage_Eav_Model_Entity_Setup
{
	public function getDefaultEntities()
	{
		return array (
				'demo_translator' => array(
						'entity_model'      => 'demo/translator',
						'attribute_model'   => '',
						'table'             => 'demo/translator',
						'attributes'        => array(
								'string' => array(
										//the EAV attribute type, NOT a mysql varchar
										'type'              => 'varchar',
										'backend'           => '',
										'frontend'          => '',
										'label'             => 'String',
										'input'             => 'text',
										'class'             => '',
										'source'            => '',
										// store scope == 0
										// global scope == 1
										// website scope == 2
										'global'            => 0,
										'visible'           => true,
										'required'          => true,
										'user_defined'      => true,
										'default'           => '',
										'searchable'        => false,
										'filterable'        => false,
										'comparable'        => false,
										'visible_on_front'  => false,
										'unique'            => false,
								),
								'key_id' => array(
										'type'				=> 'int',
										'backend'           => '',
										'frontend'          => '',
										'label'             => 'Key ID',
										'input'             => 'text',
										'class'             => '',
										'source'            => '',
										// store scope == 0
										// global scope == 1
										// website scope == 2
										'global'            => 0,
										'visible'           => false,
										'required'          => true,
										'user_defined'      => false,
										'default'           => '',
										'searchable'        => false,
										'filterable'        => false,
										'comparable'        => false,
										'visible_on_front'  => false,
										'unique'            => true,
								),
								'store_id' => array(
										'type'				=> 'int',
										'backend'           => '',
										'frontend'          => '',
										'label'             => 'Store ID',
										'input'             => 'text',
										'class'             => '',
										'source'            => '',
										// store scope == 0
										// global scope == 1
										// website scope == 2
										'global'            => 0,
										'visible'           => false,
										'required'          => true,
										'user_defined'      => false,
										'default'           => '',
										'searchable'        => false,
										'filterable'        => false,
										'comparable'        => false,
										'visible_on_front'  => false,
										'unique'            => true,
								),
								'translate' => array(
										//the EAV attribute type, NOT a mysql varchar
										'type'              => 'varchar',
										'backend'           => '',
										'frontend'          => '',
										'label'             => 'Translate',
										'input'             => 'text',
										'class'             => '',
										'source'            => '',
										// store scope == 0
										// global scope == 1
										// website scope == 2
										'global'            => 0,
										'visible'           => true,
										'required'          => true,
										'user_defined'      => true,
										'default'           => '',
										'searchable'        => false,
										'filterable'        => false,
										'comparable'        => false,
										'visible_on_front'  => false,
										'unique'            => false,
								),
								'locale' => array(
										//the EAV attribute type, NOT a mysql varchar
										'type'              => 'varchar',
										'backend'           => '',
										'frontend'          => '',
										'label'             => 'Locale',
										'input'             => 'text',
										'class'             => '',
										'source'            => '',
										// store scope == 0
										// global scope == 1
										// website scope == 2
										'global'            => 0,
										'visible'           => true,
										'required'          => true,
										'user_defined'      => true,
										'default'           => '',
										'searchable'        => false,
										'filterable'        => false,
										'comparable'        => false,
										'visible_on_front'  => false,
										'unique'            => false,
								),
						),
				)
		);
	}
}