<?php

return array(
    'iconic' => array(
        'sizes' => array(
            '8x8', '12x12', '16x16', '24x24', '32x32'
        ),
        'template' => array( // alias, size
            'url' => '/images/icons/iconic/light_gray/{alias}_{size}.png',
            'hover' => '/images/icons/iconic/orange/{alias}_{size}.png',
        ),
        'aliases' => array(
            'add' => 'plus',
            'delete' => 'trash_stroke',
            'up' => 'arrow_up',
            'down' => 'arrow_down',
            'move' => 'move_alt1',
            'edit' => 'cog',
            'settings' => 'equalizer',
            'sitemap' => 'calendar_alt_stroke',
            'files' => 'folder_stroke',
            'exit' => 'x',
        )
    ),
    'simplicio' => array(
        'sizes' => array(
            '32x32', '48x48', '64x64', '128x128'
        ),
        'template' => array( // alias, size
            'url' => '/images/icons/simplicio/{size}/{alias}.png',
        ),
        'aliases' => array(
            'add' => 'document_add',
            'delete' => 'document_delete',
            'up' => 'direction_up',
            'down' => 'direction_down',
            'move' => 'calendar',
            'edit' => 'document_edit',
            'settings' => 'application',
            'sitemap' => 'document_search',
            'files' => 'folder',
            'exit' => 'notification_error',
        )
    ),
    'tutorial9' => array(
        'sizes' => array(
            '32x32',
        ),
        'template' => array( // alias, size
            'url' => '/images/icons/tutorial9/{alias}32.png',
        ),
        'aliases' => array(
            'add' => 'plus',
            'delete' => 'linedpaperminus',
            'up' => 'uparrow',
            'down' => 'downarrow',
            'move' => 'exchange',
            'edit' => 'paperpencil',
            'settings' => 'gear',
            'sitemap' => 'article',
            'files' => 'folder',
            'exit' => 'stop',
        )
    ),
    'rinoa' => array(
        'sizes' => array(
            '24x24',
        ),
        'template' => array( // alias, size
            'url' => '/images/icons/rinoa/{alias}.png',
        ),
        'aliases' => array(
            //'add' => 'add',
            //'delete' => 'delete',
            //'up' => 'up',
            //'down' => 'down',
            'move' => 'go',
            'edit' => 'document_edit',
            'settings' => 'applications',
            'sitemap' => 'home',
            'files' => 'folder',
            'exit' => 'close',
        )
    ),
    'led-icons' => array(
        'sizes' => array(
            '16x16',
        ),
        'template' => array( // alias, size
            'url' => '/images/icons/led-icons/{alias}.png',
        ),
        'aliases' => array(
            //'add' => 'add',
            //'delete' => 'delete',
            'up' => 'arrow_up',
            'down' => 'arrow_down',
            'move' => 'arrow_inout',
            'edit' => 'pencil',
            'settings' => 'cog',
            'sitemap' => 'application_view_list',
            'files' => 'folder',
            'exit' => 'cross',
        )
    ),
    'drf' => array(
        'sizes' => array(
            '32x32',
        ),
        'template' => array( // alias, size
            'url' => '/images/icons/drf/{alias}.png',
        ),
        'aliases' => array(
            'add' => 'new doc', // plus
            'delete' => 'trash',
            'up' => 'up_alt',
            'down' => 'down_alt',
            'move' => 'refresh',
            'edit' => 'edit',
            'settings' => 'window',
            'sitemap' => 'search',
            'files' => 'folder',
            'exit' => 'public',
        )
    ),
    'fatcow' => array(
        'sizes' => array(
            '16x16', '32x32',
        ),
        'template' => array( // alias, size
            'url' => '/images/icons/fatcow/{size}/{alias}.png',
        ),
        'aliases' => array(
            'add' => 'page_add',
            'delete' => 'cross',
            'up' => 'arrow_up',
            'down' => 'arrow_down',
            'move' => 'application_cascade',
            'edit' => 'page_edit',
            'settings' => 'cog',
            'sitemap' => 'sitemap',
            'files' => 'folder',
            'exit' => 'door',
        )
    ),
);