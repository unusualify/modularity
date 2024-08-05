---
# sidebarPos: 3
---
# Tabs <Badge type="tip" text="^0.10.0" />

The `ue-tabs` component combines **v-tabs** and **v-tabs-window** components as a one component. You must pass items prop into the component for generating component tab structure.

## Usage
It has 'items' prop as object, every keys meet a tab, every values fill the tab-windows.
``` php
  @php
    $items = [
      'Hepsi' => [
        ['id' => 1, 'name' => 'Deneme', 'description' => 'Description 1'],
        ['id' => 2, 'name' => 'Deneme 2', 'description' => 'Description 2'],
        ['id' => 3, 'name' => 'Yayın 3', 'description' => 'Description 3'],
        ['id' => 4, 'name' => 'Yayın 4', 'description' => 'Description 4'],
      ],
      'Deneme' => [
        ['id' => 2, 'name' => 'Deneme 2', 'description' => 'Description 2'],
      ],
      'Yayın' => [
        ['id' => 3, 'name' => 'Yayın 3', 'description' => 'Description 3'],
        ['id' => 4, 'name' => 'Yayın 4', 'description' => 'Description 4'],
      ]
    ]
  @endphp

  <ue-tabs :items='@json($items)'>
    <template v-slot:window="windowScope">
        <v-expansion-panels>
            <v-row>
                <template v-for="(item, i) in windowScope.items" :key="`window-row-${i}]`">
                    <v-col cols="12" lg="6">
                        <v-expansion-panel>
                            <v-expansion-panel-title> @{{ item.name }}</v-expansion-panel-title>
                            <v-expansion-panel-text>
                                @{{ item.description }}
                            </v-expansion-panel-text>
                        </v-expansion-panel>
                    </v-col>
                </template>
            </v-row>
        </v-expansion-panels>
    </template>
  </ue-tabs>
```

> [!IMPORTANT]
> This component was introduced in [v0.10.0]


### 
