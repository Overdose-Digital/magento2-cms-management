# Mage2 Module Overdose CMSContent

 - [Main Functionalities](#user-content-main-functionalities)
 - [Requirements](#user-content-requirements)
 - [Installation](#user-content-installation)
 - [Specifications](#user-content-specifications)
 - [Description](#user-content-description)
 - [Managing by using xml files](#user-content-managing-by-using-xml-files)
 - [Console commands usage](#user-content-console-commands-usage)


## Installation

### Type 1: Composer (from github)

- Add repository to composer.json: `composer config repositories.od-cmscontent-github vcs https://github.com/Overdose-Digital/magento2-cms-management.git`
- Download package: `composer require overdose/module-cmscontent`
- Apply database updates and generate code by running `php bin/magento setup:upgrade && php bin/magento setup:di:compile`
- If production mode generate static content by running `php bin/magento s:s:d`
- Flush the cache by running `php bin/magento cache:flush`
   
### Type 2: Zip file

- Download archive from repo
- Unzip the zip file in `app/code/Overdose`
- Apply database updates and generate code by running `php bin/magento setup:upgrade && php bin/magento setup:di:compile`
- If production mode generate static content by running `php bin/magento s:s:d`
- Flush the cache by running `php bin/magento cache:flush`

## Functionalities

### Main features
- Create content backups of cms-blocks and cms-pages
- Review backup history of CMS-Block/Page
- Create and Update CMS-Blocks/Pages by using of xml-files
- Allowing users to import/export CMS pages or blocks:  
  -- (by json and xml files)  
  -- Supports multistore and wysiwyg images
  
### Fetaures details
   - Before saving of cms-block or page if the one was changed it's content will be saved to backup file (currently used 
    html-format). Following directory tree will be created under 'var' folder:
    
        - for block: cms/blocks/history/{identifier}/{backup_name}
        
        - for page: cms/pages/history/{identifier}/{backup_name}
    
   - Added new section "History" to CMS-Block and CMS-Page editing page. Here you will be able to see backup records
    list. Each record is represented as link with name in format Y_m_d_h_i_s_{store_ids}. By clicking the link a popup
    window with backup's content will appear.
    
   - Added two configuration xml-files cms_block_data.xml and cms_page_data.xml for creating and updating cms-blocks or pages.


## Example

   Approach to use this extension:
   - Create new custom module e.g. Project_CMS.
   - In etc directory you can create cms_page_data.xml and cms_page_data.xml files (examples of these files https://github.com/Overdose-Digital/magento2-cms-management/blob/master/etc/cms_block_data.xml.sample and https://github.com/Overdose-Digital/magento2-cms-management/blob/master/etc/cms_page_data.xml.sample). In these files developer can create/update CMS block or page and push it to git.
   - Such files can be created for any extension.
   
   The advantage of this approach is that no need to update UpdateData.php with a lot of conditions. You can easily update xml file and specify new version of CMS data. Also, extension creates a backup of previous CMS data.

## How to

### How to export contents

   Select one or more pages you wish to export from **CMS > Pages** in your Magento Admin and select one of the export menu. 
   "Export JSON", "Export XML" - downloads to a **ZIP file** as single file in JSON/XML format
   "Export as split JSON files", "Export as split XML files" - downloads to a **ZIP file** 
   that put each block/page to a separate file in JSON/XML format

   If your pages or blocks contain one or more images, they will be automatically added in the ZIP file.

### How to import contents

   A new option **Import** will appear in your Magento Admin **Content** menu. Click on it to import a previously exported ZIP file.
   Import supports XML and JSON format for block/page files (single and split as well).

#### CMS import mode

   **Overwrite existing**: If a page/block with same id and store assignment is found, it will be overwritten by the ZIP file content.

   **Skip existing**: If a page/block with same id and store assignment is found, it will **NOT** be overwritten by the ZIP file content.

#### Media import mode

   **Do not import**: Do not import media files in the ZIP file.

   **Overwrite existing**: If an image with same name exists it will be overwritten with version in the ZIP file.

   **Skip existing**: If an image with same name exists it will **NOT** be overwritten with version in the ZIP file.
 
### Managing by using xml files
   - Main purpose create and update cms-blocks/pages without editing via admin panel. 
        There are two options:
     1. Single file.
        Use a single file for all pages/blocks. Block`s content should be placed in cms_block_data.xml, pages in cms_page_data.xml. 
     You can put your configuration files into:
        - MAGENTO_ROOT/app/etc/ 
        - Overdose_CMSContent::etc/ 
        - Overdose_CMSContent::etc/od_cms/
     
     2. Split content by files.
        Split content by files (by id/store or any other logic). Block`s content should be placed in a few files with strict mask - cms_block_data[your_id].xml,
        Pages in - cms_page_data[your_id].xml. For example - cms_page_data_home.xml, cms_page_data_no-route.xml. 
     This kind of configuration files should be placed only in Overdose_CMSContent::etc/od_cms/.
 
   - Configuration files for blocks and pages mainly have the same structure. Examples you can find in:
     - Overdose_CMSContent::etc/od_cms/cms_block_data.xml.sample
     - Overdose_CMSContent::etc/od_cms/cms_page_data.xml.sample
     - Overdose_CMSContent::etc/od_cms/cms_page_data_no-route.xml.sample
     
   - CASE #1 Create block or page:
   
        1. Add cms_block_data.xml(cms_page_data.xml) with desired configuration and save in app/etc folder (do not forget 
            to define the 'version' attribute)
            
        2. Run console command (php bin/magento od:cms:upgrade) or php bin/magento setup:upgrade
        
        3. Refresh cache
        
        Result: Block or page will be created and new record will be added to `od_cmscontent_version` table.
        
        !!!Note!!! If block or page already exists in magento - it will be updated according to data in your xml-file.
    
   - CASE #2 Update block or page
   
        1. Update config for desired entities in cms_block_data.xml(cms_page_data.xml)
        
        2. Set new version (should be greater than the previous one)
        
        3. Run console command (php bin/magento od:cms:upgrade) or php bin/magento setup:upgrade
        
        4. Refresh cache
        
        Result: Will be updated Block or page and corresponding record in `od_cmscontent_version` table

## Specifications

### Observers
- cms_block_save_before > Overdose\CMSContent\Observer\Cms\CmsSaveBefore
- cms_page_save_before  > Overdose\CMSContent\Observer\Cms\CmsSaveBefore
- cms_page_delete_commit_after  > Overdose\CMSContent\Observer\DeleteContentVersion
- cms_block_delete_commit_after  > Overdose\CMSContent\Observer\DeleteContentVersion

### Model
- content_version
	
### Tables
- od_cmscontent_version > Overdose\CMSContent\Model\ContentVersion
    
### Console commands
- od:cms:upgrade >  Upgrade configuration of CMS page/blocks
    - Options:
       - -t, --type=TYPE              > CMS-type to upgrade: [block|blocks|page|pages]
       - -i, --identifier=IDENTIFIER  > Comma-separated Identifiers of Block/Page to upgrade
- od:cms:history-clear > Will delete old history files, by default will be used: Period option
  - Options:
      - -t, --type=TYPE              > CMS-type to upgrade: [block|blocks|page|pages]

**od:cms:upgrade** [options]
   
   - Used to create/update cms-blocks/pages bases on data in `cms_block_data.xml` and `cms_page_data.xml`
   Alternative way: run `php bin/magento setup:upgrade`
   
   - Some main examples:
   
         `php bin/magento od:cms:upgrade -t block`          -- will update only Blocks (`cms_block_data.xml`)
         
         `php bin/magento od:cms:upgrade -t page`           -- will update only Pages (`cms_page_data.xml`)
         
         `php bin/magento od:cms:upgrade -t page -i home`   -- will update only page with identifier `home`

**od:cms:history-clear** [options]

- Used to manually clear old cms-blocks/pages which are located in var/cms/history folder
- Examples:

      `php bin/magento od:cms:history-clear -t block`          -- will delete only history Blocks
      
      `php bin/magento od:cms:history-clear -t page`           -- will delete only history Pages


### Custom configuration files
- cms_block_data*.xml > Create or update cms-blocks
- cms_page_data*.xml > Create or update cms-pages

### Delete old files by Cron
   - For use this possibility we need turn on it in admin panel. **"Delete Backups By Cron"**
   
#### There are 3 settings:

   - Cron Run Settings:
   
         1. Frequency - set cron period when will be run cron (Daily, Weekly, Monthly)
      
         2. Start Time - set time, when will be run cron. 
   
   - Delete Methods:
   
         1. Method:
      
            a) By Periods - delete files by periods, exclude files younger than week.

            b) Older Than:

               b-1) Period - set period (Days, Weeks, Months or Years). Files older than it will be delete.

               b-2) Number - set quantity of periods.
   
   - Logs:
   
      Write logs. Which files were deleted.

## Support
Magento 2.3 | Magento 2.4
:---: | :---:
ok | ok

