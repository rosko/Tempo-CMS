<?php

class FeedHelper {

    public static function renderFeed($type='rss', $content=null)
    {
        $limit = 10;

        if ($content===null) {
            
        } elseif (is_object($content) && is_subclass_of($content, 'Content')) {

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
                $contents = call_user_func(array($itemsClassName, 'model'))->with('unit')->findAll($criteria);
                $items = array();
                foreach ($contents as $cont) {
                    $feed = $cont->feedItem();
                    $item = $cont->getAttributes();
                    foreach ($feed as $element => $attribute)
                    {
                        if ($attribute)
                            $item[$element] = $cont->{$attribute};
                    }
                    if (!isset($item['title'])) $item['title'] = $cont->unit->title;
                    if ($cont->hasAttribute('modify'))
                        if (!isset($item['updated'])) $item['updated'] = $cont->modify;
                    $item['link'] = $cont->getUnitUrl(true);
                    $items[] = $item;
                }
            }
            unset($params['items']);
            $channel = array();
            foreach ($params as $element => $attribute) {
                if ($content->hasAttribute($attribute))
                    $channel[$element] = $content->{$attribute};
            }
            if (!isset($channel['title']) || !$channel['title'])
                $channel['title'] = $content->unit->title;
            $channel['language'] = Yii::app()->language;
            $channel['link'] = $content->getUnitUrl(true);
            $channel['updated'] = date('r', strtotime($items[0]['updated']));
            
        } elseif (isset(Yii::$classMap[$content])) {
            
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
