<?php

use yii\db\Migration;

/**
 * Class m200422_134447_alter_table_add_price
 */
class m200422_134447_alter_table_add_price extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }

    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->addColumn('offer', 'price', $this->money(5,2)->notNull()->defaultValue(0));
        $this->addColumn('user', 'paypalme_link', $this->string());
    }

    public function down()
    {
        $this->dropColumn('user', 'paypalme_link');
        $this->dropColumn('offer', 'price');
    }
}
