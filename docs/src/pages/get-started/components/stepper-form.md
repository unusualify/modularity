---
# sidebarPos: 3
---
# Stepper Form <Badge type="tip" text="^0.9.2" />

The `ue-stepper-form` component adds multistaging forms within a ui structure. Each form in stepper form behaves like standard **ue-form** component. It also offers some features in addition to the form such as previewing form data. 


## Usage
It has 'forms' prop as array, it's every element is a form consisting of fields such as title and schema. The schema field must be input schema made up of standard inputs.
``` php
  @php
    $forms = [
      [
        'title' => 'Title 1',
        'id' => 'stepper-form-1',
        'previewTitle' => 'custom preview title for title of preview card',
        'schema' => $this->createFormSchema([
          [
            'type' => 'any-type',
            'name' => 'name-1',
            ...
          ],
          [
            'type' => 'any-type',
            'name' => 'name-2',
            ...
          ]
        ])
      ],
      [
        'title' => 'Title 2',
        'schema' => $this->createFormSchema([
          [
            'type' => 'any-type',
            'name' => 'name-1',
          ]
          [
            'type' => 'any-type',
            'name' => 'name-2',
          ]
        ])
    ],
    ]
  @endphp

  <ue-stepper-form :forms='@json($forms)'/>
```

> [!IMPORTANT]
> This component was introduced in [v0.9.2]


### 
