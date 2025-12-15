<?php

use humhub\components\Migration;

/**
 * Class m251130_085500_childrens_listing_mode
 */
class m251130_085500_childrens_listing_mode extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('wiki_page', 'childrens_listing_mode', $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('wiki_page', 'childrens_listing_mode');
    }
}
