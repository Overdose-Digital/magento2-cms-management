# Mage2 Module Overdose CMSContent

    ``overdose/module-cmscontent``

 - [Main Functionalities](#user-content-main-functionalities)
 - [Requirements](#user-content-requirements)
 - [Installation](#user-content-installation)
 - [Specifications](#user-content-specifications)
 - [Description](#user-content-description)
 - [Managing by using xml files](#user-content-managing-by-using-xml-files)
 - [Console commands usage](#user-content-console-commands-usage)


## Main Functionalities

   - Create content backups of cms-blocks and cms-pages
   - Review backup history of CMS-Block/Page
   - Create and Update CMS-Blocks/Pages by using of xml-files
   - Allowing users to import/export CMS pages or blocks
   - Supports multistore and wysiwyg images

## Requirements

   - Magento 2.3 (CE or EE) and higher
   

## Example

   Approach to use this extension:
   - Create new custom module e.g. Project_CMS.
   - In etc directory you can create cms_page_data.xml and cms_page_data.xml files (examples of these files https://github.com/Overdose-Digital/magento2-cms-management/blob/master/etc/cms_block_data.xml.sample and https://github.com/Overdose-Digital/magento2-cms-management/blob/master/etc/cms_page_data.xml.sample). In these files developer can create/update CMS block or page and push it to git.
   - Such files can be created for any extension.
   
   The advantage of this approach is that no need to update UpdateData.php with a lot of conditions. You can easily update xml file and specify new version of CMS data. Also, extension creates a backup of previous CMS data.
   
## Installation

### Type 1: Composer (from github)

   - Add repository to composer.json `composer config repositories.od-cmscontent-github vcs https://github.com/Overdose-Digital/magento2-cms-management.git`
   - Download package `composer require overdose/module-cmscontent`
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

## How to export contents

   Select one or more pages you wish to export from **CMS > Pages** in your Magento Admin and select **Export** from the mass action menù.

   You will download a **ZIP file** containing pages or blocks information. If your pages or blocks contain one or more images,
   they will be automatically added in the ZIP file.

## How to import contents

   A new option **Import** will appear in your Magento Admin **Content** menu. Click on it to import a previously exported ZIP file.

### CMS import mode

   **Overwrite existing**: If a page/block with same id and store assignment is found, it will be overwritten by the ZIP file content.

   **Skip existing**: If a page/block with same id and store assignment is found, it will **NOT** be overwritten by the ZIP file content.

### Media import mode

   **Do not import**: Do not import media files in the ZIP file.

   **Overwrite existing**: If an image with same name exists it will be overwritten with version in the ZIP file.

   **Skip existing**: If an image with same name exists it will **NOT** be overwritten with version in the ZIP file.
 
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
    
    
            



