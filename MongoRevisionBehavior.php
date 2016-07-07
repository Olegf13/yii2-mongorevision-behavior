<?php

namespace olegf13\mongorevision;

use yii\base\Behavior;
use yii\db\BaseActiveRecord;
use MongoDB\BSON\UTCDateTime;

/**
 * MongoRevisionBehavior automatically makes revision of an ActiveRecord object when AR update event happens.
 *
 * To use MongoRevisionBehavior, insert the following code to your ActiveRecord class:
 *
 * ```php
 * use olegf13\mongorevision\MongoRevisionBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *         MongoRevisionBehavior::className(),
 *     ];
 * }
 * ```
 *
 * By default, MongoRevisionBehavior will collect "old" AR object attributes, fill the `revisionOwnerId`,
 * `revisionOwnerModel`, `revisionDate` and `revisionUser` attributes with the corresponding values;
 * and then store the resulting AR object revision in MongoDB `revision` collection.
 *
 * Behavior is called after the associated AR object is being updated (EVENT_AFTER_UPDATE).
 *
 *
 * If your MongoDB connection name is different or you want to use a different collection or attribute names,
 * you may configure behavior properties like the following:
 *
 * ```php
 * use olegf13\mongorevision\MongoRevisionBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *         [
 *             'class' => MongoRevisionBehavior::className(),
 *             'mongoConnectionName' => 'mongodb',
 *             'mongoCollection' => 'revision',
 *             'revisionOwnerIdAttribute' => 'ownerId',
 *             'revisionOwnerModelAttribute' => 'ownerModel',
 *             'revisionDateAttribute' => 'revisionDate',
 *             'revisionUserAttribute' => 'revisionUser',
 *         ],
 *     ];
 * }
 * ```
 *
 * @author Oleg Fedorov <olegf39@gmail.com>
 */
class MongoRevisionBehavior extends Behavior
{
    /**
     * @var string MongoDB connection name.
     */
    public $mongoConnectionName = 'mongodb';
    /**
     * @var string MongoDB collection name where to store revisions.
     */
    public $mongoCollection = 'revision';
    /**
     * @var string the attribute that will receive "owner" private key value (id).
     */
    public $revisionOwnerIdAttribute = 'ownerId';
    /**
     * @var string the attribute that will receive "owner" model name (className).
     */
    public $revisionOwnerModelAttribute = 'ownerModel';
    /**
     * @var string the attribute that will receive revision creation ISODate value.
     */
    public $revisionDateAttribute = 'revisionDate';
    /**
     * @var string the attribute that will receive revision creator (current user ID) value.
     */
    public $revisionUserAttribute = 'revisionUser';

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'makeRevision',
        ];
    }

    /**
     * Makes revision of owner model by collecting "old" model attributes,
     * adding extra "revision spec" attributes (revision owner id, revision date etc)
     * and saves it into MongoDB collection.
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\mongodb\Exception
     */
    public function makeRevision()
    {
        /* @var BaseActiveRecord $owner */
        $owner = $this->owner;
        /** @var array Old "owner model" attributes plus revision specific attributes. */
        $attributes = $owner->getOldAttributes();

        $attributes[$this->revisionOwnerIdAttribute] = $owner->primaryKey;
        $attributes[$this->revisionOwnerModelAttribute] = $owner->className();
        $attributes[$this->revisionDateAttribute] = new UTCDateTime(round(microtime(true) * 1000));

        /** @var \yii\web\User $user */
        $attributes[$this->revisionUserAttribute] = (($user = \Yii::$app->get('user', false)) && !$user->isGuest) ? $user->id : null;

        if ($owner->isPrimaryKey(['_id'])) {
            unset($attributes['_id']);
        }

        /** @var \yii\mongodb\Connection $connection */
        $connection = \Yii::$app->get($this->mongoConnectionName);
        /** @var \yii\mongodb\Collection $collection */
        $collection = $connection->getCollection($this->mongoCollection);

        $collection->insert($attributes);
    }
}