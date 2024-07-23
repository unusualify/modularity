---
outline: deep
sidebarPos: 5

---


# Filepond - File Input Component

`FilePond` is a JavaScript library that provides smooth drag-and-drop file uploading. By implementing the FilePond Vue component for image and file uploads, `Modularity` offers users easily implementable, configurable, and versatile file processing functionality.

::: tip One to Many Polymorphic Bounding

There is another way to process files/ medias with modularity, that is using file/media libraries. Unlike using file/media library, `FilePond with Modularity` offers `one to many` bounding between models and files.

:::
## Feature Implementation Road Map
### Trait Implementation

::: tip 

  Add `HasAsset` and `AssetTrait` to your route's model and repository respectively to implement file processing mechanism.

:::

In order to effectively use the FilePond component and its functionalities on the desired model, you need to add two traits: `AssetTrait and HasAsset`. The `AssetTrait` should be implemented in the `repository`, interacting with the module's data storage mechanism and handling the file storage process. Conversely, the `HasAsset` trait should be implemented in the `model`, introducing relationship and casting methods to the parent model to bind files.

::: info

Modularity serves most of the functionalities over traits. In order to get clear information about mechanism, please see [FilePond Related Traits Page](https://i.kym-cdn.com/entries/icons/original/000/011/976/That_Would_Be_Great_meme.jpg)

:::

### Route Config - Input Configuration

Route's configuration files allow you to configure input component and its metadata. After adding given traits above, define `filepond` component to route's input array to use `FilePond` vue component. 

  <br/>

  #### Simple Usage
  
  `Type` parameter should be given as `filepond`

  ```php
    'web_company' => [
            'name' => 'WebCompany',
            'headline' => 'Web Companies',
            'url' => 'web-companies',
            'route_name' => 'web_company',
            'icon' => '',
            'table_options' => [
                //..code
            ],
            'headers' => [
                //..code
            ],
            'inputs' => [
                //..code
                [
                    'name' => 'avatar',
                    'label' => 'Avatar',
                    'type' => 'filepond',
                    '_rules' => 'sometimes|required|min:3',
                ],
            ],

  ```

  ### Advanced options and avaliable props

  <br/>

  #### `accepted-file-types`

  Controlls the allowable file types to be uploaded to your model. For an example, Can be defined as `file/pdf, image/*` to allow all image types and pdf types only. Different types should be seperated with comma `,` .

  * `Input Type:` `String`
  * `Default:` `file/*, image/*` (all types of files and image types)
  
  <br/>
  
  #### `allow-multiple`

  Configures multiple file uploading functionality allowance.

  * `Input Type:` `Boolean`
  * `Variance:` `true|false|null`
  * `Default:` `true` 

  <br/>

  #### `max-files`

  Controlls the maximum number of files can be upload.

  * `Input Type:` `String|Number|null`
  * `Default:` `null` (unlimited)


  <br/>

  #### `allow-drop`

  Enables or disables the drag and drop functionality.


  * `Input Type:` `Boolean`
  * `Variance:` `true|false|null`
  * `Default:` `true` 

  <br/>


  #### `allow-browse`

  Enables or disables the file browser functionality.


  * `Input Type:` `Boolean`
  * `Variance:` `true|false|null`
  * `Default:` `true` 

  <br/>

  #### `allow-replace`

  Allow drop to replace a file, only works when `allow-multiple` is `false`


  * `Input Type:` `Boolean`
  * `Variance:` `true|false|null`
  * `Default:` `true` 

  <br/>

  #### `allow-remove`

  Allow remove a file, or hide and disable the remove button.


  * `Input Type:` `Boolean`
  * `Variance:` `true|false|null`
  * `Default:` `true` 

  <br/>

  #### `allow-process`

  Enable or disable the process button.


  * `Input Type:` `Boolean`
  * `Variance:` `true|false|null`
  * `Default:` `true` 

  <br/>

  #### `allow-image-preview`

  Configures the image preview will be shown or not.

  * `Input Type:` `Boolean`
  * `Variance:` `true|false|null`
  * `Default:` `false` 

  <br/>

  #### `drop-on-page`

  FilePond will catch all files dropped on the webpage

  * `Input Type:` `Boolean`
  * `Variance:` `true|false|null`
  * `Default:` `false` 
  
  <br/>

  #### `drop-on-element`

  Require drop on the FilePond element itself to catch the file.

  * `Input Type:` `Boolean`
  * `Variance:` `true|false|null`
  * `Default:` `true` 
  
  <br/>

  #### `drop-validation`

  When enabled, files are validated before they are dropped. A file is not added when it's invalid.
  
  * `Input Type:` `Boolean`
  * `Variance:` `true|false|null`
  * `Default:` `false` 
  
  <br/>










