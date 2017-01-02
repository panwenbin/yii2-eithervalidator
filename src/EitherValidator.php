<?php
/**
 * EitherValidator class file.
 *
 * @author Petra Barus <petra.barus@gmail.com>
 * @since 2015.02.12
 */

namespace panwenbin\yii2\validators;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;

/**
 * EitherValidator forces either the attribute or any attribute in eitherAttributes is required
 *
 * To use like this
 *
 * ```
 *    [
 *        ['email'],
 *        EitherValidator::className(),
 *        'eitherAttributes' => ['phone'],
 *        'message' => Yii::t('app', 'Either attribute {attribute}, {either_attributes} is required')
 *    ]
 * ```
 */
class EitherValidator extends \yii\validators\Validator
{
    /**
     * The error message.
     * @var string
     */
    public $message;

    /**
     * List of either attributes that either have to be filled.
     * @var array
     */
    public $eitherAttributes = [];

    /**
     * This has to set as false or it defeats the purpose.
     * @var false
     */
    public $skipOnEmpty = false;

    public function init()
    {
        if (empty($this->eitherAttributes)) {
            throw new InvalidConfigException(Yii::t('yii', 'EitherAttributes are not set'));
        }
        if (!isset($this->message)) {
            $this->message = Yii::t('yii', 'Either \'{attribute}\', \'{either_attributes}\' has to be filled');
        }
    }

    /**
     * @param \yii\base\Model $model
     * @param string $attribute the name of the attribute.
     */
    public function validateAttribute($model, $attribute)
    {
        $values = [];
        $values[$attribute] = $model->{$attribute};
        foreach ($this->eitherAttributes as $eitherAttribute) {
            $values[$eitherAttribute] = $model->{$eitherAttribute};
        }
        $filledValues = array_filter($values, function ($value) {
            return !$this->isEmpty($value);
        });
        if (count($filledValues) == 0) {
            $this->addError($model, $attribute, $this->message, $this->getErrorParams($model, $attribute));
        }
    }

    /**
     * @param \yii\base\Model $model the model to be validated.
     * @param string $attribute the name of the attribute to be validated.
     * @return array
     */
    private function getErrorParams($model, $attribute)
    {
        $eitherAttributeLabels = array_map(function ($eitherAttribute) use ($model) {
            return $model->getAttributeLabel($eitherAttribute);
        }, $this->eitherAttributes);
        return [
            'attribute' => $model->getAttributeLabel($attribute),
            'either_attributes' => implode(', ', $eitherAttributeLabels),
        ];
    }

    /**
     * @param \yii\base\Model $model the model to be validated.
     * @param string $attribute the name of the attribute to be validated.
     * @param \yii\web\View $view the view.
     * @return string
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        $options = [
            'attribute' => Html::getInputId($model, $attribute),
        ];
        foreach ($this->eitherAttributes as $eitherAttribute) {
            $options['eitherAttributes'][] = Html::getInputId($model, $eitherAttribute);
        }
        $options['message'] = Yii::$app->getI18n()->format($this->message,
            $this->getErrorParams($model, $attribute), Yii::$app->language);
        $optionJson = json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return <<<JS
(function(){
    var options = {$optionJson};
    var values = [];
    values.push($('#' + options.attribute).val());
    for (var i in options.eitherAttributes) {
        values.push($('#' + options.eitherAttributes[i]).val());
    }
    if (values.filter(function(e){
            return e.length > 0;
        }).length == 0) {
        messages.push(options.message);
    }
})();
JS;
    }

}

