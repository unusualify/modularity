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
            'default' => null,
        ],
        'localKey' => [
            'required' => false,
            'position' => 2,
            'default' => null,
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
            'default' => null,
        ],
        'secondKey' => [
            'required' => false,
            'position' => 3,
            'default' => null,
        ],
        'localKey' => [
            'required' => false,
            'position' => 4,
            'default' => null,
        ],
        'secondLocalKey' => [
            'required' => false,
            'position' => 5,
            'default' => null,
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
            'default' => null,
        ],
        'id' => [
            'required' => false,
            'position' => 3,
            'default' => null,
        ],
        'localKey' => [
            'required' => false,
            'position' => 4,
            'default' => null,
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
            'default' => null,
        ],
        'ownerKey' => [
            'required' => false,
            'position' => 2,
            'default' => null,
        ],
        'relation' => [
            'required' => false,
            'position' => 3,
            'default' => null,
        ],
    ],
    'morphTo' => [
        'name' => [
            'required' => false,
            'position' => 0,
            'default' => null,
        ],
        'type' => [
            'required' => false,
            'position' => 1,
            'default' => null,
        ],
        'id' => [
            'required' => false,
            'position' => 2,
            'default' => null,
        ],
        'ownerKey' => [
            'required' => false,
            'position' => 3,
            'default' => null,
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
            'default' => null,
        ],
        'localKey' => [
            'required' => false,
            'position' => 2,
            'default' => null,
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
            'default' => null,
        ],
        'secondKey' => [
            'required' => false,
            'position' => 3,
            'default' => null,
        ],
        'localKey' => [
            'required' => false,
            'position' => 4,
            'default' => null,
        ],
        'secondLocalKey' => [
            'required' => false,
            'position' => 5,
            'default' => null,
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
            'default' => null,
        ],
        'id' => [
            'required' => false,
            'position' => 3,
            'default' => null,
        ],
        'localKey' => [
            'required' => false,
            'position' => 4,
            'default' => null,
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
            'default' => null,
        ],
        'foreignPivotKey' => [
            'required' => false,
            'position' => 2,
            'default' => null,
        ],
        'relatedPivotKey' => [
            'required' => false,
            'position' => 3,
            'default' => null,
        ],
        'parentKey' => [
            'required' => false,
            'position' => 4,
            'default' => null,
        ],
        'relatedKey' => [
            'required' => false,
            'position' => 5,
            'default' => null,
        ],
        'relation' => [
            'required' => false,
            'position' => 6,
            'default' => null,
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
            'default' => null,
        ],
        'foreignPivotKey' => [
            'required' => false,
            'position' => 3,
            'default' => null,
        ],
        'relatedPivotKey' => [
            'required' => false,
            'position' => 4,
            'default' => null,
        ],
        'parentKey' => [
            'required' => false,
            'position' => 5,
            'default' => null,
        ],
        'relatedKey' => [
            'required' => false,
            'position' => 6,
            'default' => null,
        ],
        'relation' => [
            'required' => false,
            'position' => 7,
            'default' => null,
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
            'default' => null,
        ],
        'foreignPivotKey' => [
            'required' => false,
            'position' => 3,
            'default' => null,
        ],
        'relatedPivotKey' => [
            'required' => false,
            'position' => 4,
            'default' => null,
        ],
        'parentKey' => [
            'required' => false,
            'position' => 5,
            'default' => null,
        ],
        'relatedKey' => [
            'required' => false,
            'position' => 6,
            'default' => null,
        ],
        'relation' => [
            'required' => false,
            'position' => 7,
            'default' => null,
        ],
    ],
];
