MongoRevision behavior for Yii 2
================================
This behavior provides automatic revision creation of any ActiveRecord object(s) into MongoDB collection.

It means, that the extension will automatically save "previous version" of your ActiveRecord object 
after it's been updated. 

So you can store and track the history of all your data changes.

To describe in detail, MongoRevision behavior will collect "old" AR object attributes, fill the `revisionOwnerId`, 
`revisionOwnerModel`, `revisionDate` and `revisionUser` attributes with the corresponding values; 
and then store the resulting AR object revision in particular MongoDB `revision` collection.

Behavior is called after the associated AR object is being updated (EVENT_AFTER_UPDATE).

Installation
------------

This extension requires [MongoDb Extension for Yii 2](https://github.com/yiisoft/yii2-mongodb).

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist olegf13/yii2-mongorevision-behavior "*"
```

or add

```
"olegf13/yii2-mongorevision-behavior": "*"
```

to the require section of your `composer.json` file.


Usage
-----

To use MongoRevisionBehavior, insert the following code to your ActiveRecord class:

```php
use olegf13\mongorevision\MongoRevisionBehavior;
// ...
public function behaviors()
{
    return [
        MongoRevisionBehavior::className(),
    ];
}
```

If your MongoDB connection name is different or you want to use a different collection or attribute names,
you may configure behavior properties like the following:

```php
use olegf13\mongorevision\MongoRevisionBehavior;
// ...
public function behaviors()
{
    return [
        [
            'class' => MongoRevisionBehavior::className(),
            'mongoConnectionName' => 'mongodb',
            'mongoCollection' => 'revision',
            'revisionOwnerIdAttribute' => 'ownerId',
            'revisionOwnerModelAttribute' => 'ownerModel',
            'revisionDateAttribute' => 'revisionDate',
            'revisionUserAttribute' => 'revisionUser',
        ],
    ];
}
```