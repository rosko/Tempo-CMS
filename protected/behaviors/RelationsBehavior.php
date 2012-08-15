<?php

// Класс, который позволяет удобно сохранять связи между моделями
// Based on CAdvancedArBehavior by Herbert Maschke <thyseus@gmail.com>


class RelationsBehavior extends CActiveRecordBehavior
{

    public function afterSave($event)
    {
        $relations = $this->getRelations();
        foreach ($relations as $relation) {

            if ($relation['type'] == CActiveRecord::MANY_MANY) {

                $forAdd = array_diff($relation['value'], $relation['oldValue']);
                foreach ($forAdd as $id) {

                    if ($id) {

                        $sql = 'INSERT INTO `' . $relation['m2mTable'] . '`
                                    (`'.$relation['m2mThisField'].'`, `'.$relation['m2mForeignField'].'`)
                            VALUES (:this_field, :foreign_field)';

                        Yii::app()->getDb()->createCommand($sql)->bindValues(array(
                              'this_field' => $this->getOwner()->id,
                              ':foreign_field' => $id
                         ))->execute();
                    }
                }

                $forRemove = array_diff($relation['oldValue'], $relation['value']);
                foreach ($forRemove as $id) {

                    if ($id) {

                        $sql = 'DELETE IGNORE FROM `' . $relation['m2mTable'] . '`
                                WHERE `'.$relation['m2mThisField'].'` = :this_field
                                   AND `'.$relation['m2mForeignField'].'` = :foreign_field';

                        Yii::app()->getDb()->createCommand($sql)->bindValues(array(
                              'this_field' => $this->getOwner()->id,
                              ':foreign_field' => $id
                         ))->execute();
                    }

                }

            }

        }
        //Yii::app()->end();

        parent::afterSave($event);
        return true;
    }

    protected function getRelations()
    {
        $owner = $this->getOwner();
        $relations = array();

        if (method_exists($owner, 'relations')) {
            foreach ($owner->relations() as $key => $relation) {

                $info = array();
                $info['key'] = $key;
                $value = $owner->$key;
                if (!is_array($value)) {
                    $value = explode(',', $value);
                }
                $info['value'] = $value;
                $relatedObjects = $owner->getRelated($key, true);
                $info['oldValue'] = array();
                foreach ($relatedObjects as $relatedObject) {
                    $info['oldValue'][] = $relatedObject->id;
                }

                $info['foreignTable'] = $relation[1];

                if ($relation[0] == CActiveRecord::MANY_MANY) {
                    $info['type'] = CActiveRecord::MANY_MANY;
                    if (preg_match('/^(.+)\((.+)\s*,\s*(.+)\)$/s', $relation[2], $pocks)) {
                        $info['m2mTable'] = $pocks[1];
                        $info['m2mThisField'] = $pocks[2];
                        $info['m2mForeignField'] = $pocks[3];
                    } else {
                        $info['m2mTable'] = $relation[2];
                        $info['m2mThisField'] = $this->owner->tableSchema->primaryKey;
                        $info['m2mForeignField'] = CActiveRecord::model($relation[1])->tableSchema->primaryKey;
                    }
                }
                $relations[$key] = $info;

            }
        }

        return $relations;

    }


}
