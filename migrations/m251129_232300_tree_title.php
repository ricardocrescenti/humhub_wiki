<?php

use humhub\components\Migration;

/**
 * Class m251129_232300_tree_title
 */
class m251129_232300_tree_title extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('wiki_page', 'tree_title', $this->string(255));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('wiki_page', 'tree_title');
    }
}
