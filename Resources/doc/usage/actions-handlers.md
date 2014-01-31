<!---
# This file is part of the ChillDev FileManager bundle.
#
# @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
# @copyright 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
# @version 0.1.3
# @since 0.1.3
# @package ChillDev\Bundle\FileManagerBundle
-->

# Actions handlers

Out of the box, bundle provides just some basic filesystem operations (like copying, moving etc.). But you can do much more with your files. You can easily extend file manager to implement new features for operating on files - you can register own file actions handlers.

## Implementation

To do so, you need to implement `ChillDev\Bundle\FileManagerBundle\Action\Handler\HandlerInterface`:

```php
namespace Application\Action\Handler;

use ChillDev\Bundle\FileManagerBundle\Action\Handler\HandlerInterface;
use ChillDev\Bundle\FileManagerBundle\Filesystem\Disk;

use Symfony\Component\HttpFoundation\Request;

class MyHandler implements HandlerInterface
{
    public function getLabel()
    {
        // action label that will appear in files browser
        return 'My action';
    }

    public function supports($mimeType)
    {
        // cheks if action can be executed on given type
        return preg_match('#^image/#', $mimeType) > 0;
    }

    public function handle(Request $request, Disk $disk, $path)
    {
        // handle action request
        // return response
    }
}
```

*Note:* `supports()` method is only checked when generating UI in files browser. You can always enforce calling the action on a file by forming URL. We don't want to limit this, very same way you can always try to call any software on your computer by passing explicit path to target file.

## Registring handlers

Ok, so we have a handler. But it's not enough - you need to register it as DI service to let filemanager know about it. To do so, you need to to register your handler with `chilldev.filemanager.action_handler` DI tag:

```xml
<service id="application.action_handler.my_handler" class="Application\Action\Handler\MyHandler">
    <tag name="chilldev.filemanager.action_handler" action="my-action"/>
</service>
```

With this, your handler is registred as action `my-action` (note the attribute `action` in DI tag element).

From now on your action trigger (action link) will be listed in _Actions_ column for all files that it supports.

## Routing

You can also enforce calling you action handler on particular file (even not detected to be supported) by generating and accessing URL for action route. Action route name is `chilldev_filemanager_actions_handle` and it takes your action name, disk ID and target path. For example you can use:

```php
<?php $view['router']->generate(
    'chilldev_filemanager_actions_handle',
    ['action' => 'my-action', 'disk' => 'disk-label', 'path' => '/path/to/file']
); ?>
```
