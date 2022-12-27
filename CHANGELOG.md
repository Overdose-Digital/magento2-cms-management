# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.2] - 31-10-2022
### Added
- Unit tests for Block, Model and Controller classes.

### Changed
- Made minor change in code to fix php8.1 compatibility issue.
- Updated return type of function.

### Removed
- Removed leftover docblock params for a function.q

## [2.0.1] - 30-10-2022
### Added
- Backup preview now show title and identifier.
- Added Apply button with confirm popup to recover page builder content and save block/page content.
### Changed
- Backup preview now in json format.
- Updated notes for "Clear history" feature. (Make it more clear).
- Enable module by default.
- Some BE refactoring.
### Removed
- Removed menu item "View history" and config for this.

## [2.0.0] - 26-06-2022
### Added
- Feature "clear history"  
  -- Automatic deleting an old history files by crone  
  -- CLI command for clearing history files
- Feature "separate files support" (you can import/export to/from separate xml/json files).
- Simple Unit Test for some thin places.

### Changed
- Refactored code.
- Position of system configs.
- Import form moved to UI instead of direct blocks.
- Support store ID.

### Fixed
- Using system configs.

## [1.1.0] - 10-06-2022
### Added
- Possibility to import/export CMS pages or blocks.
- Automatic deleting an old history backups.
- Refactored code.

