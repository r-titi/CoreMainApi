<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%mapping}}`.
 */
class m221213_130940_create_mapping_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%mapping}}', [
            'id' => $this->primaryKey(),
            'event' => $this->string()->notNull(),
            'micro' => $this->string()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%mapping}}');
    }
}
