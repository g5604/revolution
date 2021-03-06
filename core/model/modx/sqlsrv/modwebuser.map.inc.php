<?php
/**
 * @package modx
 * @subpackage sqlsrv
 */
$xpdo_meta_map['modWebUser']= array (
  'package' => 'modx',
  'version' => '1.1',
  'table' => 'web_users',
  'composites' => 
  array (
    'modWebUserProfile' => 
    array (
      'class' => 'modWebUserProfile',
      'key' => 'id',
      'local' => 'id',
      'foreign' => 'internalKey',
      'cardinality' => 'one',
      'owner' => 'local',
    ),
    'modWebUserSetting' => 
    array (
      'class' => 'modWebUserSetting',
      'key' => 'id',
      'local' => 'id',
      'foreign' => 'webuser',
      'cardinality' => 'one',
      'owner' => 'local',
    ),
    'modWebGroupMember' => 
    array (
      'class' => 'modWebGroupMember',
      'key' => 'id',
      'local' => 'id',
      'foreign' => 'webuser',
      'cardinality' => 'one',
      'owner' => 'local',
    ),
  ),
);
