<?php

/**
 * Main bundle layout (placeholder to re-define in your application).
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */

$view['title']->append($view['translator']->trans('File manager'));

$view['slots']->output('_content');
