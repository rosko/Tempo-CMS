<?php

class AccessRights extends CInputWidget
{
    var $objects;
    var $operation;
    var $instantSave = true;

    public function run()
	{
        list($name,$id)=$this->resolveNameID();

		if(isset($this->htmlOptions['id']))
			$id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$id;

		if(isset($this->htmlOptions['name']))
			$name=$this->htmlOptions['name'];
        else
            $this->htmlOptions['name']=$name;



        // Сохранение информации осуществляется через behavior

        if ($this->hasModel()) {

            $this->objects = array(get_class($this->model), 'id', $this->model->id);

        }

        $controls = array();

        if (is_array($this->objects) && !empty($this->objects)) {

            $operations = ClassHelper::getBehaviorPropertyByClassName($this->objects[0], 'AccessCBehavior', 'operations');

            if (!empty($operations)) {

                if ($this->operation) {

                    $controls[] = array(
                        'label' => $operations[$this->operation],
                        'content' => $this->rightsForOperation($this->operation),
                    );
                    ;

                } else {

                    foreach ($operations as $operationName => $operationTitle) {

                        $controls[] = array(
                            'label' => $operations[$operationName],
                            'content' => $this->rightsForOperation($operationName)
                        );

                    }

                }

            }

        }

        if (!empty($controls)) {

            $this->render('AccessRights',
                array(
                     'controls' => $controls,
                     'instantSave' => $this->instantSave,
                )
            );

        }

    }

    protected function rightsForOperation($operation, $is_deny=0)
    {
        $params = array(
            'aco_class' => $this->objects[0],
            'aco_key' => !empty($this->objects[1]) ? $this->objects[1] : '',
            'aco_value' => !empty($this->objects[2]) ? $this->objects[2] : '',
            'action' => $operation,
        );

        $items = AccessItem::model()->findAllByAttributes($params);
        $data = array();

        foreach ($items as $item) {
            if ($item['aro_class']) {
                $data[] = array(
                    'id' => $item->getAroId(),
                    'text' => $item->getAroText(),
                );
            }
        }

        $extraResults = array();
        $buildInRoles = Role::builtInRoles();
        foreach ($buildInRoles as $roleName => $roleTitle) {
            $extraResults['User:roles:'.$roleName] = $roleTitle;
        }

        return $this->widget('Select2',
            array(
                 'name' => $this->htmlOptions['name'].'['.$operation.']',
                 'id' => $this->htmlOptions['id'].'_'.$operation,
                 'data' => $data,
                 'htmlOptions' => array(
                     'class' => 'rightsselect',
                     'data-aco_class' => $params['aco_class'],
                     'data-aco_key' => $params['aco_key'],
                     'data-aco_value' => $params['aco_value'],
                     'data-operation' => $params['action'],
                     'data-is_deny' => $is_deny,
                 ),
                 'classNames' => array(
                     // Class name; primary key prefix; primary key; search fields
                     // Primary key can use . for using relations
                     array('User', 'User:id:', 'id', 'email,login,displayname'),
                     array('User', 'User:roles:', '_roles.name', 'title'),
                     $extraResults,
                 ),
            ), true
        );

    }

}