<!---
# This file is part of the ChillDev FileManager bundle.
#
# @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
# @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
# @version 0.1.1
# @since 0.0.1
# @package ChillDev\Bundle\FileManagerBundle
-->

# Configuration

**ChillDevFileManagerBundle** allows you to define *disks* scopes to be accessible by file manager. Bundle's configuration namespace is `chilldev_filemanager`.

## Disks

Disk is a directory that will act as a wrapper for file manager - it will operate only within selected scopes (you can define multiple disks):

```yaml
chilldev_filemanager:
    disks:
        disk1_id:
            label: "Disk label"
            source: "/path/to/directory/"
        disk2_id:
            label: "Another disk label"
            source: "/path/to/another/directory/"
```

Configuration key is used as internal disk identifier. Disk *label* is displayed in web frontend and *source* is a path to directory to use as a wrapper.
