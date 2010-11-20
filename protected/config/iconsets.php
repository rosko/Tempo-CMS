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
);