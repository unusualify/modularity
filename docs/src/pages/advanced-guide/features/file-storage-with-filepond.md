---
outline: deep
sidebarPos: 5

---

# File Storage with Filepond

`Modularity` provides two different file storage functionality, with file library method and filepond. These two systems, differentiate over `file - fileable object` relationship and input component used over forms. This documentation will only cover the filepond mechanism.

## Storage Mechanism

Filepond storage mechanism is design based on [FilePond Vue Component Docs](https://pqina.nl/filepond/docs/api/server/), which requires and serves `temporary asset` processing. For an example, let's say project have system users and users can upload their avatar(s). 
* When a file is uplaoded through the FilePond interface, it is sent to our backend via a secure API endpoint. 
* Then, our `FilePondManager` processes the file upload request, performs necessary validations and stores the file in temporary file storage path and file data in `temporary file table`.
* During this stage, the file is cached to echance performance and allow for any further processing or validation checks. And it is ready for permanent storage
* Once the associated model form is confirmed or saved, the file is then moved from the temporary cache to its permanent storage location and a file object will be created on the permanent asset table.

  
::: info

This approach ensures efficient file handling, reducing the load on the system and improving the overall user experience. Our architecture ensures high reliability and scalability, capable of managing multiple concurrent uploads seamlessly.

:::

Regarding the object relations, `modularity's filepond` offers `one to many polymorphic` relation between assetable objects and assets. Database structure can be observed below for user-assets mechanism.

<img src="https://i.ibb.co/WvdQsCh/Screenshot-2024-07-23-at-11-53-36.png" alt="filepond_db_relations" border="0" />

::: tip

In order to implement and use filepond on file storage, please see [Input FilePond](../../get-started/components/input-filepond.md)

:::
