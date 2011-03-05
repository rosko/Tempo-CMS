<?php

function smarty_function_form($params, &$smarty)
{
    if(!empty($params['className']) && method_exists($params['className'], 'form')) {
        $form_array = call_user_func(array($params['className'], 'form'));
        $id = $params['className'].$params['id'];
        $form_array['id'] = $id;

        if (!empty($params['enableAjax'])) {
            $cs=Yii::app()->getClientScript();
            $am=Yii::app()->getAssetManager();
            $cs->registerScriptFile($am->publish(Yii::getPathOfAlias('application.assets.js')).'/cms.js');

            $form_array['activeForm'] = Form::ajaxify($id);
            $unit = $smarty->getTemplateVars('unit');
            $pageunit = $smarty->getTemplateVars('pageunit');
            unset($form_array['activeForm']['focus']);
            $form_array['activeForm']['clientOptions']['validationUrl'] = '/?r=page/unitAjax&unit_id='.$unit['id'];
            $form_array['activeForm']['clientOptions']['afterValidate'] = <<<EOD
js:function(f,d,h){
    if (!h) {
        var params = f.serialize();
        ajaxSave('/?r=page/unitAjax&unit_id={$unit['id']}', params, f.attr('method'), function(html) {
            updatePageunit({$pageunit['id']}, '.pageunit[rev={$unit['id']}]');
        });
    }
}
EOD
;
        }

        $form_array['type'] = 'form';
        if (!empty($params['buttons'])) {
            $form_array['buttons'] = $params['buttons'];
        } else {
            $form_array['buttons'] = array(
				'submit'=>array(
					'type'=>'submit',
					'label'=>Yii::t('cms', 'Submit'),
					'title'=>Yii::t('cms', 'Submit'),
				),
            );
        }
        $form = new Form($form_array);
        $form->model = new $params['className'];

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