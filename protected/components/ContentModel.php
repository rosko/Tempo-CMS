<?php
class ContentModel extends I18nActiveRecord
{
    const EXCLUSIVE = false; // Разрешает создавать только один экземпляр юнита и только в одном месте

    public static function form()
	{
		return array();
    }

    public function relations()
    {
        return array(
            'widget' => array(self::BELONGS_TO, 'Widget', 'widget_id'),
        );
    }

    public function selectPage($number, $per_page=0)
    {
        if ($per_page<1)
            $per_page = Yii::app()->settings->getValue('defaultsPerPage');
        
        $offset = ($number-1)*$per_page;
        if ($offset < 0)
            $offset = 0;
        $this->getDbCriteria()->mergeWith(array(
            'limit'=>$per_page,
            'offset'=>$offset
        ));
        return $this;        
    }

    public function getWidgetPageArray()
    {
        $sql = 'SELECT p.* FROM `'.Page::tableName().'`  as p INNER JOIN `' . PageWidget::tableName() . '` as pu ON pu.page_id = p.id WHERE pu.widget_id = :widget_id ORDER BY pu.id LIMIT 1';
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':widget_id', $this->widget_id, PDO::PARAM_INT);
        $page = $command->queryRow();
        $page['alias'] = $page[Yii::app()->language.'_alias'];
        $page['url'] = $page[Yii::app()->language.'_url'];
        return $page;
    }

    public function getWidgetUrl($absolute=false, $params=array())
    {
        $page = $this->getWidgetPageArray();
        $params = array_merge(array('pageId'=>$page['id'], 'alias'=>$page['alias'], 'url'=>$page['url']), $params);
        if ($absolute)
            return Yii::app()->controller->createAbsoluteUrl('view/index', $params);
        else
            return Yii::app()->controller->createUrl('view/index', $params);
    }

    public function getTemplateDirAliases($className='')
    {
        if ($className == '')
            $className = get_class($this);
        $dir = strtolower(substr($className,4));
        $pathes = array(
            'application.units.'.$dir.'.templates',
            'local.units.'.$dir.'.templates',
            'local.templates.'.$dir,
        );
        return $pathes;
    }

    public function getAllValuesBy($attr)
    {
        $attr = $this->getI18nFieldName($attr);
        $sql = "SELECT DISTINCT `{$attr}` FROM `" . $this->tableName() . "` ORDER BY `{$attr}` ASC";
        return Yii::app()->db->createCommand($sql)->queryColumn();
    }

    // Обработка ajax-запроса
    public function ajax($vars)
    {
        $widget = Widget::model()->findByPk($vars['widgetId']);
        $content = $widget->content;
        if ($content && !Yii::app()->user->isGuest) {
            if (isset($vars['ContentModel'])) {
                $content->attributes=$vars['ContentModel'];
            }
            if (isset($vars['attribute']) && isset($vars['width']) && isset($vars['height'])
                    && isset($vars['tag']) && isset($vars['number'])) {
                $html = $content->{$vars['attribute']};
                preg_match_all("/<{$vars['tag']}[^>]*?\/?>/msiu", $html, $matches, PREG_OFFSET_CAPTURE);
                $t = $matches[0][intval($vars['number'])];
                $source = $t[0];
                $repl = preg_replace("/width=[\"\']?([\d]*)[\"\'?]/msi", 'width="'.intval($vars['width']).'"', $t[0]);
                if ($repl == $t[0]) {
                    $repl = str_ireplace('<'.$vars['tag'], '<'.$vars['tag'].' width="'.intval($vars['width']).'"', $repl);
                }
                $t[0] = $repl;
                $repl = preg_replace("/height=[\"\']?([\d]*)[\"\'?]/msi", 'height="'.intval($vars['height']).'"', $repl);
                if ($repl == $t[0]) {
                    $repl = str_ireplace('<'.$vars['tag'], '<'.$vars['tag'].' height="'.intval($vars['height']).'"', $repl);
                }
                $repl = preg_replace("/width:[\s]+?([\d]*)px/msi", 'width: '.intval($vars['width']).'px', $repl);
                $repl = preg_replace("/height:[\s]+?([\d]*)px/msi", 'height: '.intval($vars['height']).'px', $repl);
                $content->{$vars['attribute']} = substr($html, 0, $t[1]) . str_replace($source, $repl, substr($html, $t[1], strlen($source))) . substr($html, $t[1]+strlen($source));
            }
            echo $widget->save() && $content->save();
        }
    }
    
    public function widget($className, $params, $return=false)
    {
        $output = Yii::app()->getController()->widget($className, array('params'=>$params, 'content'=>$this), true);
        if ($return) {
            return $output;
        } else {
            echo $output;
        }
    }
    
}
