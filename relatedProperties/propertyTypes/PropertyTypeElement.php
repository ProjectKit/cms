<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 30.04.2015
 */
namespace skeeks\cms\relatedProperties\propertyTypes;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\relatedProperties\models\RelatedPropertiesModel;
use skeeks\cms\relatedProperties\PropertyType;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * Class PropertyTypeElement
 * @package skeeks\cms\relatedProperties\propertyTypes
 */
class PropertyTypeElement extends PropertyType
{
    public $code = self::CODE_ELEMENT;
    public $name = "";

    const FIELD_ELEMENT_SELECT              = "select";
    const FIELD_ELEMENT_SELECT_MULTI        = "selectMulti";
    const FIELD_ELEMENT_RADIO_LIST          = "radioList";
    const FIELD_ELEMENT_CHECKBOX_LIST       = "checkbox";

    public $fieldElement            = self::FIELD_ELEMENT_SELECT;
    public $content_id;

    static public function fieldElements()
    {
        return [
            self::FIELD_ELEMENT_SELECT          => \Yii::t('skeeks/cms','Combobox').' (select)',
            self::FIELD_ELEMENT_SELECT_MULTI    => \Yii::t('skeeks/cms','Combobox').' (select multiple)',
            self::FIELD_ELEMENT_RADIO_LIST      => \Yii::t('skeeks/cms','Radio Buttons (selecting one value)'),
            self::FIELD_ELEMENT_CHECKBOX_LIST   => \Yii::t('skeeks/cms','Checkbox List'),
        ];
    }

    public function init()
    {
        parent::init();

        if(!$this->name)
        {
            $this->name = \Yii::t('skeeks/cms','Binding to an element');
        }
    }

    /**
     * @return bool
     */
    public function getIsMultiple()
    {
        if (in_array($this->fieldElement, [self::FIELD_ELEMENT_SELECT_MULTI, self::FIELD_ELEMENT_CHECKBOX_LIST]))
        {
            return true;
        }

        return false;
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),
        [
            'content_id'  => \Yii::t('skeeks/cms','Content'),
        ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(),
        [
            ['content_id', 'integer'],
            ['fieldElement', 'in', 'range' => array_keys(static::fieldElements())],
            ['fieldElement', 'string'],
        ]);
    }

    /**
     * @return \yii\widgets\ActiveField
     */
    public function renderForActiveForm()
    {
        $field = parent::renderForActiveForm();

        $find = CmsContentElement::find()->active();

        if ($this->content_id)
        {
            $find->andWhere(['content_id' => $this->content_id]);
        }

        if ($this->fieldElement == self::FIELD_ELEMENT_SELECT)
        {
            $field = $this->activeForm->fieldSelect(
                $this->property->relatedPropertiesModel,
                $this->property->code,
                ArrayHelper::map($find->all(), 'id', 'name'),
                []
            );
        } else if ($this->fieldElement == self::FIELD_ELEMENT_SELECT_MULTI)
        {
            $field = $this->activeForm->fieldSelectMulti(
                $this->property->relatedPropertiesModel,
                $this->property->code,
                ArrayHelper::map($find->all(), 'id', 'name'),
                []
            );
        } else if ($this->fieldElement == self::FIELD_ELEMENT_RADIO_LIST)
        {
            $field = parent::renderForActiveForm();
            $field->radioList(ArrayHelper::map($find->all(), 'id', 'name'));

        } else if ($this->fieldElement == self::FIELD_ELEMENT_CHECKBOX_LIST)
        {
            $field = parent::renderForActiveForm();
            $field->checkboxList(ArrayHelper::map($find->all(), 'id', 'name'));
        }


        if (!$field)
        {
            return '';
        }


        return $field;
    }


    /**
     * @return string
     */
    public function renderConfigForm(ActiveForm $activeForm)
    {
        echo $activeForm->fieldSelect($this, 'fieldElement', \skeeks\cms\relatedProperties\propertyTypes\PropertyTypeElement::fieldElements());
        echo $activeForm->fieldSelect($this, 'content_id', \skeeks\cms\models\CmsContent::getDataForSelect());
    }

    /**
     * @varsion > 3.0.2
     *
     * @return $this
     */
    public function addRules()
    {
        if ($this->isMultiple)
        {
            $this->property->relatedPropertiesModel->addRule($this->property->code, 'safe');
        } else
        {
            $this->property->relatedPropertiesModel->addRule($this->property->code, 'integer');
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getStringValue()
    {
        $value = $this->property->relatedPropertiesModel->getAttribute($this->property->code);

        if ($this->isMultiple)
        {
            $data = ArrayHelper::map(CmsContentElement::find()->where(['id' => $value])->all(), 'id', 'name');
            return implode(', ', $data);
        } else
        {
            if ($element = CmsContentElement::find()->where(['id' => $value])->one())
            {
                return $element->name;
            }

            return "";
        }
    }
}