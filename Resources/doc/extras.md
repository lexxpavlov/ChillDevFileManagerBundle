<!---
# This file is part of the ChillDev FileManager bundle.
#
# @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
# @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
# @version 0.1.2
# @since 0.1.2
# @package ChillDev\Bundle\FileManagerBundle
-->

# Extras

## SonataAdminBundle integration

You can integrate **ChillDevFileManagerBundle** with [**SonataAdminBundle**](https://github.com/sonata-project/SonataAdminBundle) by adding file manager block to administration dashboard panel. For now integration just adds block with disks list to administration dashboard. More features are planned.

**Note:** Integration is only possible if you use **Symfony 2.2** or newer.

Here is complete configuration snippet:

```yaml
sonata_block:
    blocks:
        # you need to register block service
        chilldev.filemanager.block.disks_list:

sonata_admin:
    dashboard:
        blocks:
            -
                # you need to add block to admin dashboard
                position: "right"
                type: "chilldev.filemanager.block.disks_list"


chilldev_filemanager:
    # you need to enable block service as it's disabled by default
    sonata_block: true
```
