<?php
class Magestance_Demo_Model_Entity_Setup extends Mage_Eav_Model_Entity_Setup
{
	public function getDefaultEntities()
	{
		return array (
				'demo_translation' => array(
						'entity_model'      => 'demo/translation',
						'attribute_model'   => '',
						'table'             => 'demo/translation',
						'attributes'        => array(
								'csv' => array(
										//the EAV attribute type, NOT a mysql varchar
										'type'              => 'varchar',
										'backend'           => '',
										'frontend'          => '',
										'label'             => 'CSV',
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
								'string_id' => array(
										'type'				=> 'int',
										'backend'           => '',
										'frontend'          => '',
										'label'             => 'String ID',
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
								'store_group' => array(
										'type'				=> 'int',
										'backend'           => '',
										'frontend'          => '',
										'label'             => 'Store',
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
								'store' => array(
										'type'				=> 'int',
										'backend'           => '',
										'frontend'          => '',
										'label'             => 'Store View',
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
								'translation' => array(
										//the EAV attribute type, NOT a mysql varchar
										'type'              => 'varchar',
										'backend'           => '',
										'frontend'          => '',
										'label'             => 'Translation',
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
				),
				'demo_string' => array(
						'entity_model'      => 'demo/string',
						'attribute_model'   => '',
						'table'             => 'demo/string',
						'attributes'        => array(
								'string' => array(
										//the EAV attribute type, NOT a mysql varchar
										'type'              => 'text',
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
								'module' => array(
										//the EAV attribute type, NOT a mysql varchar
										'type'              => 'text',
										'backend'           => '',
										'frontend'          => '',
										'label'             => 'Module',
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
								'status' => array(
										//the EAV attribute type, NOT a mysql varchar
										'type'              => 'int',
										'backend'           => '',
										'frontend'          => '',
										'label'             => 'status',
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
				),
				'demo_path' => array(
						'entity_model'      => 'demo/path',
						'attribute_model'   => '',
						'table'             => 'demo/path',
						'attributes'        => array(
								'path' => array(
										//the EAV attribute type, NOT a mysql varchar
										'type'              => 'text',
										'backend'           => '',
										'frontend'          => '',
										'label'             => 'Path',
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
								'offset' => array(
										//the EAV attribute type, NOT a mysql varchar
										'type'              => 'int',
										'backend'           => '',
										'frontend'          => '',
										'label'             => 'Offset',
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
								)
						)
				)
		);
	}
}