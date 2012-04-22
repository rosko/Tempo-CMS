<?php

class FeedHelper {

    public static function renderFeed($type='rss', $content=null)
    {
        $limit = 10;

        if (is_string($content) && isset(Yii::$classMap[$content])) {
            $params = array(
                'items' => array(
                    $content,
                    'order'=>'`create` DESC',
                ),
            );
        }

        if ($content===null) {


            
        } elseif (isset($params) || (is_object($content) && is_subclass_of($content, 'ContentModel'))) {

            if (!isset($params))
                $params = $content->feed();
            $itemsClassName = $params['items'][0];
            unset($params['items'][0]);
            if (isset(Yii::$classMap[$itemsClassName])) {
                $criteria = $params['items'];
                if (isset($criteria['params']) && is_array($criteria['params'])) {
                    $p = array();
                    foreach ($criteria['params'] as $attribute) {
                        if ($attribute)
                            $p[$attribute] = $content->{$attribute};
                    }
                }
                $criteria['params'] = $p;
                $criteria['limit'] = $limit;
                $criteria = Yii::app()->getDb()->getCommandBuilder()->createCriteria($criteria);
                $contents = call_user_func(array($itemsClassName, 'model'))->findAll($criteria);
                $items = array();
                foreach ($contents as $cont) {
                    $feed = $cont->feedItem();
                    $item = $cont->getAttributes();
                    foreach ($feed as $element => $attribute)
                    {
                        if ($attribute)
                            $item[$element] = $cont->{$attribute};
                    }
                    if (!isset($item['title'])) $item['title'] = $cont->widget->title;
                    if ($cont->hasAttribute('modify'))
                        if (!isset($item['updated'])) $item['updated'] = $cont->modify;
                    $items[] = $item;
                }
            }
            unset($params['items']);
            $channel = array();
            $channel['language'] = Yii::app()->language;
            if (is_object($content)) {
                foreach ($params as $element => $attribute) {
                    if ($content->hasAttribute($attribute))
                        $channel[$element] = $content->{$attribute};
                }
                $channel['link'] = $content->getWidgetUrl(true);
                if (!isset($channel['title']) || !$channel['title'])
                    $channel['title'] = $content->widget->title;
            } else {
                $channel['title'] = call_user_func(array($content, 'modelName'));
                $channel['link'] = Yii::app()->createAbsoluteUrl('view/index');
            }
            $channel['updated'] = date('r', strtotime($items[0]['updated']));
            
        }

        Yii::app()->getController()->renderPartial('feed/rss', array(
            'channel'=>$channel,
            'items'=>$items,
            'rssLink'=>Yii::app()->request->hostInfo.Yii::app()->request->url,
            'settings'=>array(
                'global'=>Yii::app()->settings->model->getAttributes(),
            ),
        ));
    }

    public static function isFeedPresent($className, $general=true)
    {
        if (!isset(Yii::$classMap[$className]))
            return false;
        if ($general) {
            return method_exists($className, 'feedItem');
        } else {
            return method_exists($className, 'feed');
        }
    }


}
