<?php

function smarty_function_form($params, &$smarty)
{
    if(!empty($params['className']) && method_exists($params['className'], 'form')) {
        if (empty($params['ajaxUrlParams'])) {
            $params['ajaxUrlParams'] = '';
        }
        if (!empty($params['elements'])) {
            $form_array['elements'] = $params['elements'];
        } else {
            $f = call_user_func(array($params['className'], 'form'));
            $form_array['elements'] = $f['elements'];
        }
        $id = $params['className'].$params['id'];
        $form_array['id'] = $id;

        if (!empty($params['enableAjax'])) {
            $cs=Yii::app()->getClientScript();
            $am=Yii::app()->getAssetManager();
            $cs->registerScriptFile($am->publish(Yii::getPathOfAlias('application.assets.js')).'/cms.js');

            $form_array['activeForm'] = Form::ajaxify($id);
            $widget = $smarty->getTemplateVars('widget');
            $pageWidget = $smarty->getTemplateVars('pageWidget');
            unset($form_array['activeForm']['focus']);
            $form_array['activeForm']['clientOptions']['validationUrl'] = '/?r=view/widget&pageWidgetId='.$pageWidget['id'].$params['ajaxUrlParams'];
            if ($params['enableAjax'] === 'validate') {
                $form_array['activeForm']['clientOptions']['afterValidate'] = "js:function(f,d,h){if (!h) {return true;}}";
            } else {
                $form_array['activeForm']['clientOptions']['afterValidate'] = 'js:function(f,d,h)'.<<<JS
{
    if (!h) {
        var params = f.serialize();
        cmsAjaxSave('/?r=view/widget&pageWidgetId={$pageWidget['id']}{$params['ajaxUrlParams']}', params, f.attr('method'), function(html) {
        //    cmsReloadPageWidget({$pageWidget['id']}, '.pagewidget[rev={$widget['id']}]');
        });
    }
}
JS;
            }
        }

        $form_array['type'] = 'form';
        if (!empty($params['buttons'])) {
            $form_array['buttons'] = $params['buttons'];
        } else {
            if (empty($params['submitLabel']))
                $params['submitLabel'] = Yii::t('cms', 'Submit');
            $form_array['buttons'] = array(
				'submit'=>array(
					'type'=>'submit',
					'label'=>$params['submitLabel'],
					'title'=>$params['submitLabel'],
				),
            );
        }
        $form = new Form($form_array);
        if (!empty($params['id']))
            $form->model = call_user_func(array($params['className'],'model'))->findByPk(intval($params['id']));
        else
            $form->model = new $params['className'];
        if (!empty($params['scenario']))
            $form->model->scenario = $params['scenario'];
        if (!empty($params['rules']))
            $form->model->rules = $params['rules'];

        $buttons = array_keys($form_array['buttons']);
        $submitted = false;
        foreach ($buttons as $btn) {
            $submitted = $submitted || $form->submitted($btn);
        }
        if ($submitted) {
            $form->validate();
        }

        return '<div class="form">'.$form->render().'</div>';
    }

    $htmlOptions = $params;
    unset($htmlOptions['action']);
    unset($htmlOptions['method']);
    return CHtml::form($params['action'], $params['method'], $htmlOptions);
}