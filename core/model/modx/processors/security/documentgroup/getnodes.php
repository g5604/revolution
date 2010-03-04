<?php
/**
 * Get the resource groups as nodes
 *
 * @param string $id The ID of the parent node
 *
 * @package modx
 * @subpackage processors.security.documentgroup
 */
if (!$modx->hasPermission('access_permissions')) return $modx->error->failure($modx->lexicon('permission_denied'));
$modx->lexicon->load('access');

$scriptProperties['id'] = !isset($scriptProperties['id']) ? 0 : str_replace('n_dg_','',$scriptProperties['id']);

$resourceGroup = $modx->getObject('modResourceGroup',$scriptProperties['id']);
$groups = $modx->getCollection('modResourceGroup');

$list = array();
if ($resourceGroup == null) {
	foreach ($groups as $group) {
        $menu = array();
        $menu[] = array(
            'text' => $modx->lexicon('resource_group_create'),
            'handler' => 'function(itm,e) {
                this.create(itm,e);
            }',
        );
        $menu[] = '-';
        $menu[] = array(
            'text' => $modx->lexicon('resource_group_remove'),
            'handler' => 'function(itm,e) {
                this.remove(itm,e);
            }',
        );

		$list[] = array(
			'text' => $group->get('name'),
			'id' => 'n_dg_'.$group->get('id'),
			'leaf' => 0,
			'type' => 'modResourceGroup',
			'cls' => 'icon-resourcegroup',
            'menu' => array('items' => $menu),
		);
	}
} else {
	$resources = $resourceGroup->getResources();
	foreach ($resources as $resource) {
        $menu = array();
        $menu[] = array(
            'text' => $modx->lexicon('resource_group_access_remove'),
            'handler' => 'function(itm,e) {
                this.removeResource(itm,e);
            }',
        );

		$list[] = array(
			'text' => $resource->get('pagetitle'),
			'id' => 'n_'.$resource->get('id'),
			'leaf' => 1,
			'type' => 'modResource',
			'cls' => 'icon-'.$resource->get('class_key'),
            'menu' => array('items' => $menu),
		);
	}
}

return $this->toJSON($list);