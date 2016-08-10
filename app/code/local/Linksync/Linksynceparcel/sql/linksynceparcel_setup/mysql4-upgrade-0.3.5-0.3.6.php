<?php
$installer = $this;
$installer->startSetup();

if (!$installer->getConnection()->fetchOne("select * from {$this->getTable('directory_country_region')} where `country_id`='AU' and `code`='ACT'")) {
    $installer->run("
        INSERT INTO `".$this->getTable('directory_country_region')."` (`country_id`, `code`, `default_name`) VALUES ('AU', 'ACT', 'Australian Capital Territory');
		INSERT INTO `".$this->getTable('directory_country_region_name')."` (`locale`, `region_id`, `name`) VALUES ('en_US', LAST_INSERT_ID(), 'Australian Capital Territory');
    ");
}

if (!$installer->getConnection()->fetchOne("select * from {$this->getTable('directory_country_region')} where `country_id`='AU' and `code`='NSW'")) {
    $installer->run("
        INSERT INTO `".$this->getTable('directory_country_region')."` (`country_id`, `code`, `default_name`) VALUES ('AU', 'NSW', 'New South Wales');
		INSERT INTO `".$this->getTable('directory_country_region_name')."` (`locale`, `region_id`, `name`) VALUES ('en_US', LAST_INSERT_ID(), 'New South Wales');
    ");
}

if (!$installer->getConnection()->fetchOne("select * from {$this->getTable('directory_country_region')} where `country_id`='AU' and `code`='NT'")) {
    $installer->run("
        INSERT INTO `".$this->getTable('directory_country_region')."` (`country_id`, `code`, `default_name`) VALUES ('AU', 'NT', 'Northern Territory');
		INSERT INTO `".$this->getTable('directory_country_region_name')."` (`locale`, `region_id`, `name`) VALUES ('en_US', LAST_INSERT_ID(), 'Northern Territory');
    ");
}

if (!$installer->getConnection()->fetchOne("select * from {$this->getTable('directory_country_region')} where `country_id`='AU' and `code`='QLD'")) {
    $installer->run("
        INSERT INTO `".$this->getTable('directory_country_region')."` (`country_id`, `code`, `default_name`) VALUES ('AU', 'QLD', 'Queensland');
		INSERT INTO `".$this->getTable('directory_country_region_name')."` (`locale`, `region_id`, `name`) VALUES ('en_US', LAST_INSERT_ID(), 'Queensland');
    ");
}

if (!$installer->getConnection()->fetchOne("select * from {$this->getTable('directory_country_region')} where `country_id`='AU' and `code`='SA'")) {
    $installer->run("
        INSERT INTO `".$this->getTable('directory_country_region')."` (`country_id`, `code`, `default_name`) VALUES ('AU', 'SA', 'South Australia');
		INSERT INTO `".$this->getTable('directory_country_region_name')."` (`locale`, `region_id`, `name`) VALUES ('en_US', LAST_INSERT_ID(), 'South Australia');
    ");
}

if (!$installer->getConnection()->fetchOne("select * from {$this->getTable('directory_country_region')} where `country_id`='AU' and `code`='TAS'")) {
    $installer->run("
        INSERT INTO `".$this->getTable('directory_country_region')."` (`country_id`, `code`, `default_name`) VALUES ('AU', 'TAS', 'Tasmania');
		INSERT INTO `".$this->getTable('directory_country_region_name')."` (`locale`, `region_id`, `name`) VALUES ('en_US', LAST_INSERT_ID(), 'Tasmania');
    ");
}

if (!$installer->getConnection()->fetchOne("select * from {$this->getTable('directory_country_region')} where `country_id`='AU' and `code`='VIC'")) {
    $installer->run("
        INSERT INTO `".$this->getTable('directory_country_region')."` (`country_id`, `code`, `default_name`) VALUES ('AU', 'VIC', 'Victoria');
		INSERT INTO `".$this->getTable('directory_country_region_name')."` (`locale`, `region_id`, `name`) VALUES ('en_US', LAST_INSERT_ID(), 'Victoria');
    ");
}

if (!$installer->getConnection()->fetchOne("select * from {$this->getTable('directory_country_region')} where `country_id`='AU' and `code`='WA'")) {
    $installer->run("
        INSERT INTO `".$this->getTable('directory_country_region')."` (`country_id`, `code`, `default_name`) VALUES ('AU', 'WA', 'Western Australia');
		INSERT INTO `".$this->getTable('directory_country_region_name')."` (`locale`, `region_id`, `name`) VALUES ('en_US', LAST_INSERT_ID(), 'Western Australia');
    ");
}

$installer->endSetup(); 
?>