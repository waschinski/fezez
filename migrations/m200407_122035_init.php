<?php

use yii\db\Migration;

/**
 * Class m200407_122035_init
 */
class m200407_122035_init extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('user', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull()->unique(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string()->unique(),
            'email' => $this->string()->notNull()->unique(),
			'verification_token' => $this->string()->defaultValue(null),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'karma' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createTable('offer', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'description' => $this->string()->notNull(),
            'key_hash' => $this->string()->notNull(),
            'karma_threshold' => $this->integer()->defaultValue(0),
            'autoacceptrequest' => $this->boolean()->defaultValue(true),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);
        $this->addForeignKey(
            'fk_offer_by',
            'offer',
            'user_id',
            'user',
            'id',
            'NO ACTION',
        );

        $this->createTable('request', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'offer_id' => $this->integer()->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);
        $this->addForeignKey(
            'fk_request_for',
            'request',
            'offer_id',
            'offer',
            'id',
            'NO ACTION',
        );
        $this->addForeignKey(
            'fk_request_by',
            'request',
            'user_id',
            'user',
            'id',
            'NO ACTION',
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk_request_by', 'request');
        $this->dropForeignKey('fk_request_for', 'request');
        $this->dropTable('request');
        $this->dropForeignKey('fk_offer_by', 'offer');
        $this->dropTable('offer');
        $this->dropForeignKey('fk_invited_by_user_id', 'user');
        $this->dropTable('user');
    }
}
