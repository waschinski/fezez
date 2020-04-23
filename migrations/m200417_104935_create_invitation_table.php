<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%invitation}}`.
 */
class m200417_104935_create_invitation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('invitation', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'email' => $this->string()->notNull(),
            'code' => $this->string()->notNull(),
            'valid_until' => $this->integer()->notNull(),
            'new_user_id' => $this->integer()->defaultValue(null),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);
        $this->addForeignKey(
            'fk_invite_user',
            'invitation',
            'user_id',
            'user',
            'id',
            'NO ACTION',
        );
        $this->addForeignKey(
            'fk_register_user',
            'invitation',
            'new_user_id',
            'user',
            'id',
            'NO ACTION',
        );
        $this->addColumn('user', 'invited_by', $this->integer()->defaultValue(null));
        $this->addForeignKey(
          'fk_invited_by',
            'user',
            'invited_by',
            'user',
            'id',
            'NO ACTION',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_invited_by', 'user');
        $this->dropColumn('user', 'invited_by');
        $this->dropForeignKey('fk_register_user', 'invitation');
        $this->dropForeignKey('fk_invite_user', 'invitation');
        $this->dropTable('invitation');
    }
}
