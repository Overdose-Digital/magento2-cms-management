# Mage2 Module Overdose CMSContent

    ``overdose/module-cmscontent``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Requirements](#markdown-header-requirements)
 - [Installation](#markdown-header-installation)
 - [Specifications](#markdown-header-specifications)
 - [Description](#markdown-header-description)
 - [Managing by using xml files](#markdown-header-managing-by-using-xml-files)
 - [Console commands usage](#markdown-header-console-commands-usage)


## Main Functionalities

   - Create content backups of cms-blocks and cms-pages
   - Review backup history of CMS-Block/Page
   - Create and Update CMS-Blocks/Pages by using of xml-files

## Requirements

   - Magento 2.3 (CE or EE) and higher
   
## Installation

### Type 1: Composer (from github)

   - Add repository to composer.json `composer config repositories.od-cmscontent-github vcs https://github.com/Overdose-Digital/magento2-cms-management.git`
   - Download package `composer require overdose/module-cmscontent:dev-master`
   - Apply database updates and generate code by running `php bin/magento setup:upgrade && php bin/magento setup:di:compile`
   - If production mode generate static content by running `php bin/magento s:s:d`
   - Flush the cache by running `php bin/magento cache:flush`
   
### Type 2: Zip file

   - Download archive from repo
   - Unzip the zip file in `app/code/Overdose`
   - Apply database updates and generate code by running `php bin/magento setup:upgrade && php bin/magento setup:di:compile`
   - If production mode generate static content by running `php bin/magento s:s:d`
   - Flush the cache by running `php bin/magento cache:flush`

## Specifications

 - Observers
	- cms_block_save_before > Overdose\CMSContent\Observer\Cms\CmsSaveBefore
	- cms_page_save_before  > Overdose\CMSContent\Observer\Cms\CmsSaveBefore

 - Model
	- content_version
	
 - Tables
    - od_cmscontent_version > Overdose\CMSContent\Model\ContentVersion
    
 - Console commands
    - od:cms:upgrade >  Upgrade configuration of CMS page/blocks
        - Options:
           - -t, --type=TYPE              > CMS-type to upgrade: [block|blocks|page|pages]
           - -i, --identifier=IDENTIFIER  > Comma-separated Identifiers of Block/Page to upgrade
    
 - Custom configuration files
     - cms_block_data.xml > Create or update cms-blocks
     - cms_page_data.xml > Create or update cms-pages
    
	
## Description
   - Before saving of cms-block or page if the one was changed it's content will be saved to backup file (currently used 
    html-format). Following directory tree will be created under 'var' folder:
    
        - for block: cms/blocks/history/{identifier}/{backup_name}
        
        - for page: cms/pages/history/{identifier}/{backup_name}
    
  
   - Added new section "History" to CMS-Block and CMS-Page editing page. Here you will be able to see backup records
    list. Each record is represented as link with name in format Y_m_d_h_i_s_{store_ids}. By clicking the link a popup
    window with backup's content will appear.
    
   - Added two configuration xml-files cms_block_data.xml and cms_page_data.xml for creating and updating cms-blocks or pages.
    
## Managing by using xml files
   - Main purpose create and update cms-blocks/pages without editing via admin panel. 
        There are to types: cms_block_data.xml and cms_page_data.xml. Mainly they have same scructure, examples you can 
        check under Overdose_CMSContent::etc folder.
        
        You can put you configuration files into:
        
            - MAGENTO_ROOT/app/etc/ (Recomended)
            
            - Overdose_CMSContent::etc folder
            
   - CASE #1 Create block or page:
   
        1. Add cms_block_data.xml(cms_page_data.xml) with desired configuration and save in app/etc folder (not forget 
            to define 'version' attribute)
            
        2. Run console command (php bin/magento od:cms:upgrade) or php bin/magento setup:upgrade
        
        3. Refresh cache
        
        Result: Block or page will be created and new record will be added to `od_cmscontent_version` table.
        
        !!!Note!!! If block or page already exists in magento - it will be updated according to data in your xml-file.
    
   - CASE #2 Update block or page
   
        1. Update config for desired entities in cms_block_data.xml(cms_page_data.xml)
        
        2. Set new version (should be greater then old)
        
        3. Run console command (php bin/magento od:cms:upgrade) or php bin/magento setup:upgrade
        
        4. Refresh cache
        
        Result: Will be updated Block or page and corresponding record in `od_cmscontent_version` table

## Console commands usage
        
   od:cms:upgrade [options]
   
   - Used to create/update cms-blocks/pages bases on data in `cms_block_data.xml` and `cms_page_data.xml`
   Alternative way: run `php bin/magento setup:upgrade`
   
   - Some main examples:
   
         `php bin/magento od:cms:upgrade -t block`          -- will update only Blocks (`cms_block_data.xml`)
         
         `php bin/magento od:cms:upgrade -t page`           -- will update only Pages (`cms_page_data.xml`)
         
         `php bin/magento od:cms:upgrade -t page -i home`   -- will update only page with identifier `home`      
    
    
            



