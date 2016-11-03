<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 24.03.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150520_183310_alter_table__cms_tree extends Migration
{
    public function safeUp()
    {

        $this->execute("ALTER TABLE {{%cms_tree%}} ADD `site_code` CHAR(15) NOT NULL;");
        $this->execute("ALTER TABLE {{%cms_tree}} ADD INDEX(site_code);");

        $this->addForeignKey(
            'cms_tree_site_code', "{{%cms_tree}}",
            'site_code', '{{%cms_site}}', 'code', 'CASCADE', 'CASCADE'
        );

        $this->execute("UPDATE {{%cms_tree}}  SET `site_code` = 's1'");
    }

    public function down()
    {
        echo "m150520_183310_alter_table__cms_tree cannot be reverted.\n";
        return false;
    }
}
