<?php
/**
 * Grabs a list of context settings for a context
 *
 * @param string $context_key The context from which to grab
 * @param string $key (optional) A filter key by which to search settings
 * @param integer $start (optional) The record to start at. Defaults to 0.
 * @param integer $limit (optional) The number of records to limit to. Defaults
 * to 20.
 * @param string $sort (optional) The column to sort by. Defaults to name.
 * @param string $dir (optional) The direction of the sort. Defaults to ASC.
 *
 * @package modx
 * @subpackage processors.context.setting
 */
if (!$modx->hasPermission('settings')) return $modx->error->failure($modx->lexicon('permission_denied'));
$modx->lexicon->load('setting');

/* setup default properties */
$isLimit = !empty($scriptProperties['limit']);
$start = $modx->getOption('start',$scriptProperties,0);
$limit = $modx->getOption('limit',$scriptProperties,20);
$sort = $modx->getOption('sort',$scriptProperties,'key');
$dir = $modx->getOption('dir',$scriptProperties,'ASC');
$key = $modx->getOption('key',$scriptProperties,'');
$namespace = $modx->getOption('namespace',$scriptProperties,'');
$area = $modx->getOption('area',$scriptProperties,'');

if (!$context = $modx->getObject('modContext', $scriptProperties['context_key'])) return $modx->error->failure($modx->lexicon('setting_err_nf'));
if (!$context->checkPolicy('view')) return $modx->error->failure($modx->lexicon('permission_denied'));

/* build query */
$c = $modx->newQuery('modContextSetting');
$c->leftJoin('modLexiconEntry','Entry',"CONCAT('setting_',modContextSetting.{$modx->escape('key')}) = Entry.name");
$c->leftJoin('modLexiconEntry','Description',"CONCAT('setting_',modContextSetting.{$modx->escape('key')},'_desc') = Description.name");
$c->where(array(
    'context_key' => $scriptProperties['context_key'],
));

if (!empty($key)) {
    $c->where(array(
        'modContextSetting.key:LIKE' => '%'.$key.'%',
    ),null,xPDOQuery::SQL_AND,2);
    $c->orCondition(array(
        'Entry.value:LIKE' => '%'.$key.'%',
    ),null,2);
    $c->orCondition(array(
        'modContextSetting.value:LIKE' => '%'.$key.'%',
    ),null,2);
    $c->orCondition(array(
        'Description.value:LIKE' => '%'.$key.'%',
    ),null,2);
}
if (!empty($namespace)) {
    $c->where(array('namespace' => $namespace));
}
if (!empty($area)) {
    $c->where(array('area' => $area));
}
$count = $modx->getCount('modContextSetting',$c);

$c->select(array(
    $modx->getSelectColumns('modContextSetting','modContextSetting'),
));
$c->select(array(
    'Entry.value AS name_trans',
    'Description.value AS description_trans',
));
$c->sortby($modx->getSelectColumns('modContextSetting','modContextSetting','',array($sort)),$dir);
if ($isLimit) $c->limit($limit,$start);
$settings = $modx->getCollection('modContextSetting',$c);

$list = array();
foreach ($settings as $setting) {
    $settingArray = $setting->toArray();

    $k = 'setting_'.$settingArray['key'];

    /* if 3rd party setting, load proper text */
    $modx->lexicon->load($setting->get('namespace').':default');

    if ($modx->lexicon->exists('area_'.$setting->get('area'))) {
        $settingArray['area_text'] = $modx->lexicon('area_'.$setting->get('area'));
    } else $settingArray['area_text'] = $settingArray['area'];


    /* get translated name and description text */
    if (empty($settingArray['description_trans'])) {
        if ($modx->lexicon->exists($k.'_desc')) {
            $settingArray['description_trans'] = $modx->lexicon($k.'_desc');
            $settingArray['description'] = $k.'_desc';
        } else {
            $settingArray['description_trans'] = $settingArray['description'];
        }
    } else {
        $settingArray['description'] = $settingArray['description_trans'];
    }
    if (empty($settingArray['name_trans'])) {
        if ($modx->lexicon->exists($k)) {
            $settingArray['name_trans'] = $modx->lexicon($k);
            $settingArray['name'] = $k;
        } else {
            $settingArray['name_trans'] = $settingArray['key'];
        }
    } else {
        $settingArray['name'] = $settingArray['name_trans'];
    }

    $settingArray['editedon'] = $setting->get('editedon') == '-001-11-30 00:00:00' || $settingArray['editedon'] == '0000-00-00 00:00:00' || $settingArray['editedon'] == null
        ? ''
        : strftime('%b %d, %Y %I:%M %p',strtotime($setting->get('editedon')));


    $settingArray['menu'] = array(
        array(
            'text' => $modx->lexicon('setting_update'),
            'handler' => array(
                'xtype' => 'modx-window-context-setting-update',
                'record' => $settingArray,
            ),
        ),
        '-',
        array(
            'text' => $modx->lexicon('setting_remove'),
            'handler' => 'this.remove.createDelegate(this,["setting_remove_confirm"])',
        ),
    );
    $list[] = $settingArray;
}
return $this->outputArray($list,$count);