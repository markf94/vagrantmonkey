# ZfServerUrl

This is a module written to work with the Zend Server UI, and which provides a
extension of `Zend\View\Helper\ServerUrl` overriding the `detectHost()` method
in order to fix [zendframework/zend-view#4](https://github.com/zendframework/zend-view/pull/4).

## Installation

1. Untar the archive in your `module/` subdirectory; it should create a
   `ZfServerUrl` subdirectory with this file, and the files `Module.php` and
   `ServerUrl.php`.

2. Add the entry `ZfServerUrl` to your `config/application.config.php` module
   list.

Once that is done, the `ServerUrl` helper provided in this module should now be
used by the `PhpRenderer`, providing the fix for the issue.
