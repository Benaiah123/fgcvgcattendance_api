<?php
// public_html/api/lib/serviceMap.php
// Maps service name (from front end) → DB table + column info.

return [
    'Hour of Encounter' => [
        'table'  => 'hour_of_encounter',
        'fields' => [
            'Adult-Onsite' => ['column' => 'adult_onsite', 'type' => 'int'],
            'Kids'         => ['column' => 'kids',         'type' => 'int'],
            'Teens'        => ['column' => 'teens',        'type' => 'int'],
        ],
    ],

    'Hour of Encounter Online' => [
        'table'  => 'hour_of_encounter_online',
        'fields' => [
            'Adult-Online' => ['column' => 'adult_online', 'type' => 'int'],
        ],
    ],

    'Main Church Sunday Worship service' => [
        'table'  => 'main_church_sunday_worship',
        'fields' => [
            'Onsite'         => ['column' => 'onsite',         'type' => 'int'],
            'Decision'       => ['column' => 'decision',       'type' => 'int'],
            'Rededication'   => ['column' => 'rededication',   'type' => 'int'],
            'First timers'   => ['column' => 'first_timers',   'type' => 'int'],
            'New membership' => ['column' => 'new_membership', 'type' => 'int'],
        ],
    ],

    'Bible study' => [
        'table'  => 'bible_study',
        'fields' => [
            'Onsite(Adult)' => ['column' => 'onsite_adult', 'type' => 'int'],
            'Onsite(Kids)'  => ['column' => 'onsite_kids',  'type' => 'int'],
        ],
    ],

    'Bible study Online' => [
        'table'  => 'bible_study_online',
        'fields' => [
            'Youtube'   => ['column' => 'youtube',   'type' => 'int'],
            'Zoom'      => ['column' => 'zoom',      'type' => 'int'],
            'Facebook'  => ['column' => 'facebook',  'type' => 'int'],
            'Instagram' => ['column' => 'instagram', 'type' => 'int'],
        ],
    ],

    'Youth focus worship service' => [
        'table'  => 'youth_focus_worship',
        'fields' => [
            'Onsite'       => ['column' => 'onsite',       'type' => 'int'],
            'Decision'     => ['column' => 'decision',     'type' => 'int'],
            'Rededication' => ['column' => 'rededication', 'type' => 'int'],
            'First timers' => ['column' => 'first_timers', 'type' => 'int'],
        ],
    ],

    'Youth focus worship service Online' => [
        'table'  => 'youth_focus_worship_online',
        'fields' => [
            'Youtube'   => ['column' => 'youtube',   'type' => 'int'],
            'Instagram' => ['column' => 'instagram', 'type' => 'int'],
            'Facebook'  => ['column' => 'facebook',  'type' => 'int'],
        ],
    ],

    'Teens Church sunday worship service' => [
        'table'  => 'teens_church_sunday_worship',
        'fields' => [
            'Onsite'              => ['column' => 'onsite',             'type' => 'int'],
            'Decision(Salvation)' => ['column' => 'decision_salvation', 'type' => 'int'],
        ],
    ],

    'Friday prayer meeting' => [
        'table'  => 'friday_prayer_meeting',
        'fields' => [
            'Onsite(Adult)' => ['column' => 'onsite_adult', 'type' => 'int'],
            'Onsite(Kids)'  => ['column' => 'onsite_kids',  'type' => 'int'],
            'Decision'      => ['column' => 'decision',     'type' => 'int'],
            'Testimony'     => ['column' => 'testimony',    'type' => 'text'],
            'Remarks'       => ['column' => 'remarks',      'type' => 'text'],
        ],
    ],

    'Friday prayer meeting Online' => [
        'table'  => 'friday_prayer_meeting_online',
        'fields' => [
            'Youtube'   => ['column' => 'youtube',   'type' => 'int'],
            'Zoom'      => ['column' => 'zoom',      'type' => 'int'],
            'Facebook'  => ['column' => 'facebook',  'type' => 'int'],
            'Instagram' => ['column' => 'instagram', 'type' => 'int'],
        ],
    ],

    'Children Church Worship sunday ' => [
        'table'  => 'children_church_worship',
        'fields' => [
            'LIGHT BEARERS'    => ['column' => 'light_bearers',   'type' => 'int'],
            'Cup Bearers'      => ['column' => 'cup_bearers',     'type' => 'int'],
            'Preteens(Cadets)' => ['column' => 'preteens_cadets', 'type' => 'int'],
            'Creche'           => ['column' => 'creche',          'type' => 'int'],
        ],
    ],

    'Sunday School' => [
        'table'  => 'sunday_school',
        'fields' => [
            'Adult'    => ['column' => 'adult',    'type' => 'int'],
            'Youth'    => ['column' => 'youth',    'type' => 'int'],
            'Teens'    => ['column' => 'teens',    'type' => 'int'],
            'Children' => ['column' => 'children', 'type' => 'int'],
        ],
    ],

    'Special Event' => [
        'table'  => 'special_events',
        'fields' => [
            'Event Name'            => ['column' => 'event_name',            'type' => 'text'],
            'Onsite'                => ['column' => 'onsite',                'type' => 'int'],
            'Youtube'               => ['column' => 'youtube',               'type' => 'int'],
            'Zoom'                  => ['column' => 'zoom',                  'type' => 'int'],
            'Facebook'              => ['column' => 'facebook',              'type' => 'int'],
            'Instagram'             => ['column' => 'instagram',             'type' => 'int'],
            'Decision'              => ['column' => 'decision',              'type' => 'int'],
            'Rededication'          => ['column' => 'rededication',          'type' => 'int'],
            'First timers'          => ['column' => 'first_timers',          'type' => 'int'],
            'Children'              => ['column' => 'children',              'type' => 'int'],
            'Teens'                 => ['column' => 'teens',                 'type' => 'int'],
            'Holy Spirit Baptismal' => ['column' => 'holy_spirit_baptismal', 'type' => 'int'],
            'Testimony'             => ['column' => 'testimony',             'type' => 'text'],
        ],
    ],
];