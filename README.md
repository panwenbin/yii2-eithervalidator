# yii2-either-validator
Yii2 validator for either attributes is required

## Installation

```
composer require "panwenbin/yii2-eithervalidator"
```

## Usage

Add a rule similar to the following to rules of the model

```php
    [
        ['email'],
        EitherValidator::className(),
        'eitherAttributes' => ['phone'],
        'message' => Yii::t('app', 'Either attribute {attribute}, {either_attributes} is required')
    ]
```
