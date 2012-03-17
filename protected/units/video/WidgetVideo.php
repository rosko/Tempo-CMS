<?php


class WidgetVideo extends ContentWidget
{
    public function name($language=null)
    {
        return Yii::t('UnitVideo.main', 'Video', array(), null, $language);
    }
    
    public function icon()
    {
        return '/images/icons/fatcow/16x16/movies.png';
    }
    
    public function modelClassName()
    {
        return 'ModelVideo';
    }

    public function unitClassName()
    {
        return 'UnitVideo';
    }

    public function init()
    {
        parent::init();
        $this->params['video'] = WidgetVideo::getHtmlByUrl(
            $this->params['content']->video,
            $this->params['content']->width,
            $this->params['content']->height,
            $this->params['unit']->title);
    }
    
    public static function getHtmlByUrl($url, $width=0, $height=0, $title='')
    {
/*
 * TODO:
 * - vkontakte.ru
 * - video.yandex.ru
 * - bbc ?
 * - cnn ?
 * -
 */

        $vs = array(
            'youtube.com' => array(
                'pattern' => "|youtube\.com([^0-9\?\#\=]*)(\?v[=/])?(#p/u/)?([0-9]*/)?([a-zA-Z0-9\-]*)|msi",
                'match' => 5,
                'width' => 480,
                'height' => 270,
                'view' => 'youtube',
            ),
            'youtu.be' => array(
                'pattern' => "|youtu\.be\/([a-zA-Z0-9\-]*)|msi",
                'match' => 1,
                'width' => 480,
                'height' => 270,
                'view' => 'youtube',                
            ),
            'vimeo.com' => array(
                'pattern' => "|vimeo\.com(/video)?/([0-9]*)|msi",
                'match' => 2,
                'width' => 480,
                'height' => 270,
                'view' => 'vimeo',
            ),
            'rutube.ru' => array(
                'pattern' => "|rutube\.ru.*v=([0-9a-f]*)|msi",
                'match' => 1,
                'width' => 480,
                'height' => 360,
                'view' => 'rutube',
            ),
            'godtube.com' => array(
                'pattern' => "|godtube\.com.*v[=/]([a-zA-Z0-9]*)|msi",
                'match' => 1,
                'width' => 480,
                'height' => 385,
                'view' => 'godtube',
            ),
            'tangle.com' => array(
                'pattern' => "|tangle\.com.*viewkey=([0-9a-f]*)|msi",
                'match' => 1,
                'width' => 480,
                'height' => 385,
                'view' => 'tangle',
            ),
            'video.google.com' => array(
                'pattern' => "|google\.com.*docid=([\-0-9]*)|msi",
                'match' => 1,
                'width' => 480,
                'height' => 385,
                'view' => 'google',
            ),
            'myspace.com' => array(
                'pattern' => "|myspace\.com.*videoid=([0-9]*)|msi",
                'match' => 1,
                'width' => 425,
                'height' => 360,
                'view' => 'myspace',
            ),
            'dailymotion.com' => array(
                'pattern' => "|dailymotion\.com/video/([^?]*)|msi",
                'match' => 1,
                'width' => 480,
                'height' => 385,
                'view' => 'dailymotion',
            ),
            'truegod.tv' => array(
                'pattern' => "|truegod\.tv.*viewkey=([0-9a-f]*)|msi",
                'match' => 1,
                'width' => 480,
                'height' => 385,
                'view' => 'truegod',
            ),
            'smotri.com' => array(
                'pattern' => "|smotri\.com.*id=([0-9a-z]*)|msi",
                'match' => 1,
                'width' => 640,
                'height' => 360,
                'view' => 'smotri',
            ),
            '1tv.ru' => array(
                'pattern' => "|1tv\.ru[^0-9]*([0-9]*)|msi",
                'match' => 1,
                'width' => 460,
                'height' => 353,
                'view' => '1tv',
            ),
            'video.bigmir.net' => array(
                'pattern' => "|bigmir\.net[^0-9]*([0-9]*)|msi",
                'match' => 1,
                'width' => 608,
                'height' => 342,
                'view' => 'bigmir',
            ),
            'video.online.ua' => array(
                'pattern' => "|online\.ua[^0-9]*([0-9]*)|msi",
                'match' => 1,
                'width' => 640,
                'height' => 400,
                'view' => 'online_ua',
            ),
            'vision.rambler.ru' => array(
                'pattern' => "|rambler\.ru/users/(.*)|msi",
                'match' => 1,
                'width' => 480,
                'height' => 291,
                'view' => 'rambler',
            ),
            'video.utro.ua' => array(
                'pattern' => "|utro\.ua.*id=([0-9a-z]*)|msi",
                'match' => 1,
                'width' => 640,
                'height' => 360,
                'view' => 'utro',
            ),
            'nbc.com' => array(
                'pattern' => "|nbc\.com/[^/]*/video/[^/]*/([0-9]*)|msi",
                'match' => 1,
                'width' => 512,                
                'height' => 347,
                'view' => 'nbc',
            ),
            $_SERVER['HTTP_HOST'] => array(
                'pattern' => "|(.*)|msi",
                'match' => 1,
                'width' => 492,
                'height' => 300,
                'view' => 'jwplayer',
            ),
            '.flv' => array(
                'pattern' => "|(.*)|msi",
                'match' => 1,
                'width' => 492,
                'height' => 300,
                'view' => 'jwplayer',
            ),
            '.mp4' => array(
                'pattern' => "|(.*)|msi",
                'match' => 1,
                'width' => 492,
                'height' => 300,
                'view' => 'jwplayer',
            ),
        );

        foreach ($vs as $site=>$arr) {
            if (strpos($url, $site) !== false) {
                preg_match($arr['pattern'], $url, $matches);
                $id = $matches[$arr['match']];
                $w = $width ? $width : $arr['width'];
                $h = $height ? $height : $arr['height'];
                $cfg = ContentUnit::loadConfig();
                return Yii::app()->controller->renderPartial($cfg['UnitVideo'].'.video.codes.'.$arr['view'], array(
                    'id' => $id,
                    'width' => $w,
                    'height' => $h,
                    'title' => $title,
                ), true);
            }
        }
        return false;
    }

    
}