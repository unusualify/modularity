<?php

return [
    'hasOne' => [
        'related' => [
            'required' => true,
            'position' => 0,
        ],
        'foreignKey' => [
            'required' => false,
            'position' => 1,
            'default' => NULL,
        ],
        'localKey' => [
            'required' => false,
            'position' => 2,
            'default' => NULL,
        ],
    ],
    'hasOneThrough' => [
        'related' => [
            'required' => true,
            'position' => 0,
        ],
        'through' => [
            'required' => true,
            'position' => 1,
        ],
        'firstKey' => [
            'required' => false,
            'position' => 2,
            'default' => NULL,
        ],
        'secondKey' => [
            'required' => false,
            'position' => 3,
            'default' => NULL,
        ],
        'localKey' => [
            'required' => false,
            'position' => 4,
            'default' => NULL,
        ],
        'secondLocalKey' => [
            'required' => false,
            'position' => 5,
            'default' => NULL,
        ],
    ],
    'morphOne' => [
        'related' => [
            'required' => true,
            'position' => 0,
        ],
        'name' => [
            'required' => true,
            'position' => 1,
        ],
        'type' => [
            'required' => false,
            'position' => 2,
            'default' => NULL,
        ],
        'id' => [
            'required' => false,
            'position' => 3,
            'default' => NULL,
        ],
        'localKey' => [
            'required' => false,
            'position' => 4,
            'default' => NULL,
        ],
    ],
    'belongsTo' => [
        'related' => [
            'required' => true,
            'position' => 0,
        ],
        'foreignKey' => [
            'required' => false,
            'position' => 1,
            'default' => NULL,
        ],
        'ownerKey' => [
            'required' => false,
            'position' => 2,
            'default' => NULL,
        ],
        'relation' => [
            'required' => false,
            'position' => 3,
            'default' => NULL,
        ],
    ],
    'morphTo' => [
        'name' => [
            'required' => false,
            'position' => 0,
            'default' => NULL,
        ],
        'type' => [
            'required' => false,
            'position' => 1,
            'default' => NULL,
        ],
        'id' => [
            'required' => false,
            'position' => 2,
            'default' => NULL,
        ],
        'ownerKey' => [
            'required' => false,
            'position' => 3,
            'default' => NULL,
        ],
    ],
    'hasMany' => [
        'related' => [
            'required' => true,
            'position' => 0,
        ],
        'foreignKey' => [
            'required' => false,
            'position' => 1,
            'default' => NULL,
        ],
        'localKey' => [
            'required' => false,
            'position' => 2,
            'default' => NULL,
        ],
    ],
    'hasManyThrough' => [
        'related' => [
            'required' => true,
            'position' => 0,
        ],
        'through' => [
            'required' => true,
            'position' => 1,
        ],
        'firstKey' => [
            'required' => false,
            'position' => 2,
            'default' => NULL,
        ],
        'secondKey' => [
            'required' => false,
            'position' => 3,
            'default' => NULL,
        ],
        'localKey' => [
            'required' => false,
            'position' => 4,
            'default' => NULL,
        ],
        'secondLocalKey' => [
            'required' => false,
            'position' => 5,
            'default' => NULL,
        ],
    ],
    'morphMany' => [
        'related' => [
            'required' => true,
            'position' => 0,
        ],
        'name' => [
            'required' => true,
            'position' => 1,
        ],
        'type' => [
            'required' => false,
            'position' => 2,
            'default' => NULL,
        ],
        'id' => [
            'required' => false,
            'position' => 3,
            'default' => NULL,
        ],
        'localKey' => [
            'required' => false,
            'position' => 4,
            'default' => NULL,
        ],
    ],
    'belongsToMany' => [
        'related' => [
            'required' => true,
            'position' => 0,
        ],
        'table' => [
            'required' => false,
            'position' => 1,
            'default' => NULL,
        ],
        'foreignPivotKey' => [
            'required' => false,
            'position' => 2,
            'default' => NULL,
        ],
        'relatedPivotKey' => [
            'required' => false,
            'position' => 3,
            'default' => NULL,
        ],
        'parentKey' => [
            'required' => false,
            'position' => 4,
            'default' => NULL,
        ],
        'relatedKey' => [
            'required' => false,
            'position' => 5,
            'default' => NULL,
        ],
        'relation' => [
            'required' => false,
            'position' => 6,
            'default' => NULL,
        ],
    ],
    'morphToMany' => [
        'related' => [
            'required' => true,
            'position' => 0,
        ],
        'name' => [
            'required' => true,
            'position' => 1,
        ],
        'table' => [
            'required' => false,
            'position' => 2,
            'default' => NULL,
        ],
        'foreignPivotKey' => [
            'required' => false,
            'position' => 3,
            'default' => NULL,
        ],
        'relatedPivotKey' => [
            'required' => false,
            'position' => 4,
            'default' => NULL,
        ],
        'parentKey' => [
            'required' => false,
            'position' => 5,
            'default' => NULL,
        ],
        'relatedKey' => [
            'required' => false,
            'position' => 6,
            'default' => NULL,
        ],
        'relation' => [
            'required' => false,
            'position' => 7,
            'default' => NULL,
        ],
        'inverse' => [
            'required' => false,
            'position' => 8,
            'default' => false,
        ],
    ],
    'morphedByMany' => [
        'related' => [
            'required' => true,
            'position' => 0,
        ],
        'name' => [
            'required' => true,
            'position' => 1,
        ],
        'table' => [
            'required' => false,
            'position' => 2,
            'default' => NULL,
        ],
        'foreignPivotKey' => [
            'required' => false,
            'position' => 3,
            'default' => NULL,
        ],
        'relatedPivotKey' => [
            'required' => false,
            'position' => 4,
            'default' => NULL,
        ],
        'parentKey' => [
            'required' => false,
            'position' => 5,
            'default' => NULL,
        ],
        'relatedKey' => [
            'required' => false,
            'position' => 6,
            'default' => NULL,
        ],
        'relation' => [
            'required' => false,
            'position' => 7,
            'default' => NULL,
        ],
    ],
];

    