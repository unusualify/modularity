# Changelog

All notable changes to `modularity` will be documented in this file

## v0.22.0 - 2024-12-25

### :rocket: Features

- add GitHub Actions workflow to automatically close issues when associated PRs are merged by @OoBook in https://github.com/unusualify/modularity/commit/c6c31667f38b22d57b5907b488297599a648bc18
- add CreateConsoleCommand and command stub for modularity command generation by @OoBook in https://github.com/unusualify/modularity/commit/ec1df30b25a2282c62b9f6cca5eacab648071cd1
- add functions to retrieve package version and update .env file by @OoBook in https://github.com/unusualify/modularity/commit/16a2d5e1ff8757f515bf695c4219e79077fe43a5
- add GetVersionCommand to retrieve package version by @OoBook in https://github.com/unusualify/modularity/commit/191fc39b27297da854079facc136f20335be8174
- add CacheVersionsCommand to cache package versions by @OoBook in https://github.com/unusualify/modularity/commit/a6b7f2930f2c68474b46fe167c09a023b53ff417
- enhance application configuration and sidebar display by @OoBook in https://github.com/unusualify/modularity/commit/dbdbdb96ed6907d75a292dafc1b6a46f21ff094c
- add caching functions for translations by @OoBook in https://github.com/unusualify/modularity/commit/89fc2f143b5a273118a0b3c74f359c91b813acef
- integrate translations into the application by @OoBook in https://github.com/unusualify/modularity/commit/46a1f1a06e43b71a6b7e9063bce01c6fcb24d13e
- add async validation method to input hook by @OoBook in https://github.com/unusualify/modularity/commit/9c8e2fd113f17bc3135473c70236b2e9f5c549cd
- emit 'submitted' event on form submission by @OoBook in https://github.com/unusualify/modularity/commit/07580686ca2a500b6d34c930d875092f2f826f8c
- integrate Fileponds functionality into User entity and repository by @OoBook in https://github.com/unusualify/modularity/commit/ef94f411d1d8f2eb513b645a2363a986aaec7c6a
- implement InputHydrator class for dynamic input handling by @OoBook in https://github.com/unusualify/modularity/commit/662f670b18f97a5c10783d3583a33f2b68b8aa1d
- add Urls view composer for dynamic URL binding by @OoBook in https://github.com/unusualify/modularity/commit/667fc625337e4d21431ad0fe36c1ab11905f859a
- add input configuration and hydration functions by @OoBook in https://github.com/unusualify/modularity/commit/6a6a086658be2ea192dc14d4d8b926f4b3f31415
- enhance profile management and UI integration by @OoBook in https://github.com/unusualify/modularity/commit/c60c4730fbc323e2cf63bb7239009eadf49fb038
- add dynamic URL binding to head script by @OoBook in https://github.com/unusualify/modularity/commit/1f1de72deaba2b673411a9d813db26996865dabc
- enhance sidebar for user role display and information by @OoBook in https://github.com/unusualify/modularity/commit/90dac8ee0b8fdf90bbb2f97148575da8e34583c0
- add path and namespace concatenation functions by @OoBook in https://github.com/unusualify/modularity/commit/7d3ad2f90958fe74623254f226fe283e0bb9289b
- add vendor path and namespace retrieval methods by @OoBook in https://github.com/unusualify/modularity/commit/38d55a131f511cbbe9460454a6b97f55b90e115d
- add command to generate model traits by @OoBook in https://github.com/unusualify/modularity/commit/4ceea130128d66069f15b20d1a10e7cc70e5cb58
- add command to generate repository traits by @OoBook in https://github.com/unusualify/modularity/commit/bc4ba0be4d94ba5fa88b125a423fe22c33a21107
- add command to create modularity features by @OoBook in https://github.com/unusualify/modularity/commit/b7b4a2a9554607e367c1c59c01fd376b8e88e585
- add command to flush Modularity caches by @OoBook in https://github.com/unusualify/modularity/commit/28419f5774689d298f8f8c1201a045cdf4ede33a
- add cache management methods by @OoBook in https://github.com/unusualify/modularity/commit/ce3490b6718fe4a51b4140d10c4fccedb9625aa6
- enhance file preview layout and add date display by @OoBook in https://github.com/unusualify/modularity/commit/095ff9609d23e2246a5ac904fb5dd0197a33a9fa
- add user relationship method for authorized entities by @OoBook in https://github.com/unusualify/modularity/commit/0950d6ac24b1a5be190a4a8271c8f1160a28900a
- add created_at field to mediableFormat method for enhanced file metadata by @OoBook in https://github.com/unusualify/modularity/commit/e8cb26d8b536812b549f5dc4454523de37873b5e
- add modularity input formatting functions for enhanced input management by @OoBook in https://github.com/unusualify/modularity/commit/07b23841d709ce51d8c3d7c74435bb195fa84f1c
- :sparkles: add new chat and chat_messages configurations for modularity by @OoBook in https://github.com/unusualify/modularity/commit/730b9cfca0bf65a79e7528e57cb5c9db79b11bb6
- :sparkles: - Introduced 'chats' and 'chat_messages' entries in the tables configuration to support modular chat functionalities. - This addition enhances the application's capability to manage chat-related data, aligning with recent modularity improvements. by @OoBook in https://github.com/unusualify/modularity/commit/b3e859e20e3ca51847086a4f9b2407e4d113d8f5
- add new theme styles and SVG icons for unusual theme by @OoBook in https://github.com/unusualify/modularity/commit/79e178bfa84838191046e4f44da4eeab63be1145

### :wrench: Bug Fixes

- remove applyCasting error return null by @OoBook in https://github.com/unusualify/modularity/commit/1c135ebc079e5183848f969d66e31a0acbaf3023
- remove applyCasting error return null by @OoBook in https://github.com/unusualify/modularity/commit/62b5cb5fcf88feace64e4fab596583938d88e261
- refine company registration logic to ensure proper redirection based on user status and route checks by @OoBook in https://github.com/unusualify/modularity/commit/b9ce789b1ca297b0ec337bd000a60c6ca05d7a84
- remove unnecessary logging in loadLocaleMessages function by @OoBook in https://github.com/unusualify/modularity/commit/9627deece243075536c357986096feef65c8227a
- improve TRANSLATIONS check in loadLocaleMessages function by @OoBook in https://github.com/unusualify/modularity/commit/6b6d332acefcfe9b4cab27aeed2ac3a6a14e37be
- set fixed namespace value in configuration by @OoBook in https://github.com/unusualify/modularity/commit/68b050610f87bdcfa074b5d93e9d432c9c50dc41
- enhance cache management in ModuleMakeCommand, ModuleRemoveCommand, and RouteMakeCommand by @OoBook in https://github.com/unusualify/modularity/commit/b90533b2ec6099c8c9d7d4d7a0eb28fd2bc4e75b
- correct duplicate grey-lighten-6 color definition by @OoBook in https://github.com/unusualify/modularity/commit/c9d8d7e8160ba95f8748932bf2c2b63715878a3a
- update import paths in vue-component.stub for correct module resolution by @OoBook in https://github.com/unusualify/modularity/commit/3a698d9a31581234ab3dea965f8a04a8938cc23b
- correct import paths in v-input-chat test file by @OoBook in https://github.com/unusualify/modularity/commit/9c7d604c84144c24f409c04152f0bfc4cdd61e88

### :recycle: Refactors

- remove unused modal and permission handling functions to clean up code by @OoBook in https://github.com/unusualify/modularity/commit/5f622aa09bbff0190a3d76566695f490498def60
- clean up layout file by removing commented script tags by @OoBook in https://github.com/unusualify/modularity/commit/9a7df0a13249616b74240f898ee2aae552df7dd4
- remove commented-out font and CSS preload links by @OoBook in https://github.com/unusualify/modularity/commit/bdf75e7c367d234558b64ccdcc7a2bf2e9242f5a
- remove commented-out pre-scripts section by @OoBook in https://github.com/unusualify/modularity/commit/cd58b26f28f9e4770392a762b259aa63f3111735
- simplify input configuration and hydration methods by @OoBook in https://github.com/unusualify/modularity/commit/5eb635cfecd4d28337fc29d9ebcf03947878ac56
- streamline input hydration process by @OoBook in https://github.com/unusualify/modularity/commit/e40cae8de35dae17cd085eb4a636df17155f5a91
- clean up unused action comments by @OoBook in https://github.com/unusualify/modularity/commit/1cfcb2060c56f403aae351d42a2e46acebe4aaea
- update file handling structure and improve clarity by @OoBook in https://github.com/unusualify/modularity/commit/fd61ab2e75f7f8e2a3024627f63e9903f060ca16
- streamline current user data handling by @OoBook in https://github.com/unusualify/modularity/commit/95ae763093e230e9b7041ddb27389a7cb235ca18
- improve user profile retrieval logic by @OoBook in https://github.com/unusualify/modularity/commit/99db6dda8764237edc5a013054b41347e2b76dd5
- update model and migration command syntax by @OoBook in https://github.com/unusualify/modularity/commit/cc7c208961c0f9c3f5790706a2b1932122a9c0eb
- update command signature and remove deprecated methods by @OoBook in https://github.com/unusualify/modularity/commit/cb524b58811ac6f2708653b2cf89f1792ad6b2d0
- update command signature and enhance user feedback by @OoBook in https://github.com/unusualify/modularity/commit/443925db2b09a9843cd0b1c63d47e40b1ea97660
- remove debug statement and clean up code by @OoBook in https://github.com/unusualify/modularity/commit/5d6e4f1ecff6141249b8d25ab5928dc9363caff5
- update command signature for consistency by @OoBook in https://github.com/unusualify/modularity/commit/d753d26492f9a8aa50dbceab139ecc5fa991b6a4
- comment out cache flush call in flushModuleCache method by @OoBook in https://github.com/unusualify/modularity/commit/4b858016f386db268d2ebfba754ccb43c1f2729f
- update cache configuration to use environment variables by @OoBook in https://github.com/unusualify/modularity/commit/5182d48ba4bb7216162b4b4ae8ac30561e292115
- update cache configuration logic by @OoBook in https://github.com/unusualify/modularity/commit/84ffc2e132b1a30e534e8b1eed4c8823e2853051
- enhance file handling and slot integration by @OoBook in https://github.com/unusualify/modularity/commit/f8ad80cd12eb6462fc8e41fb5f9b312d80b9ae84
- improve layout and structure for version display on superadmin user by @OoBook in https://github.com/unusualify/modularity/commit/7975c1c7c3b8cf6f9064ebf382f961acf6f2af0b
- update $log method to return log output by @OoBook in https://github.com/unusualify/modularity/commit/526d42b76f519133cc786d43f810e713922d1efa
- rename command and add alias by @OoBook in https://github.com/unusualify/modularity/commit/d47ef76c23f46e5f79c915bb5ce5ec97bde08d24
- replace default input retrieval with modularity function by @OoBook in https://github.com/unusualify/modularity/commit/5aa785eb12ac0d3adfc3b758c83fca0d3473bc29

### :memo: Documentation

- update repository URLs and branch naming conventions by @OoBook in https://github.com/unusualify/modularity/commit/9bf9a5d71516fc8b743848f6796a21a5c6579409

### :lipstick: Styling

- add getShowFields comments by @OoBook in https://github.com/unusualify/modularity/commit/7e1cd4c4c82a31279567d4848802df9c8899255a
- remove unused code and clean up component structure by @OoBook in https://github.com/unusualify/modularity/commit/14fb498602c80db9a258a93c26211028e95b1dd1

### :green_heart: Workflow

- :construction_worker: add new issue workflows by @OoBook in https://github.com/unusualify/modularity/commit/aea5272a4d6670aa8534423ddeecef30a69e590b
- :bug: fix getting the severity value and use $GITHUB_OUTPUT due deprecation warnings by @OoBook in https://github.com/unusualify/modularity/commit/dbb4589db6c5ff1d2a536580704ac60883ba752a
- :green_heart: add github-issue-parser action by @OoBook in https://github.com/unusualify/modularity/commit/e6382e63f146142b91194e3e4650827af758834f
- :bug: remove BODY print by @OoBook in https://github.com/unusualify/modularity/commit/1ffc2331dc2b924bef2f4d6722ebb1d60d70f680
- :bug: comment out template-path parameter by @OoBook in https://github.com/unusualify/modularity/commit/e8ac93f6f8ef2fcecf78a3a69131f20619595ca1
- :bug: add actions/checkout by @OoBook in https://github.com/unusualify/modularity/commit/34e62b9f0012c58c5bba9407779b2ac6505b0d6d
- update create-issue-branch.yml by @web-flow in https://github.com/unusualify/modularity/commit/433cb727c59b6e2c763f24c7f66a80dddd24a6c0
- update create-issue-branch.yml by @web-flow in https://github.com/unusualify/modularity/commit/5b417a0238cab06d5afff7f633f310ae6b0550d7
- update create-issue-branch.yml by @web-flow in https://github.com/unusualify/modularity/commit/5c3316a707326fd98d9a8fa920f20a548b39f2b9
- update create-issue-branch.yml by @web-flow in https://github.com/unusualify/modularity/commit/f5055fce9198f419b20a9d93d89ba94fcfffced2
- :bug: add permissions by @OoBook in https://github.com/unusualify/modularity/commit/a1a99c4a4d3dc4f155a5e0501e91991d95ad9f14
- :bug: add create-issue-branch action by @OoBook in https://github.com/unusualify/modularity/commit/f8784310eb4f21c2bdeca1bcdfec0cb2ffd66325
- :bug: add debug prints by @OoBook in https://github.com/unusualify/modularity/commit/081d67682620fea503f9b517764c423fa5678d4b
- remove LOG_LEVEL environment variable from create-issue-branch workflow by @OoBook in https://github.com/unusualify/modularity/commit/14b2ee649f3a60b31ad83219464fe36f0300a2d0
- refactor create-issue-branch workflow to directly configure Git and create branch by @OoBook in https://github.com/unusualify/modularity/commit/f7fc925be0907363092b81fee1d9881b14203a4e
- update create-issue-branch workflow to use action for branch creation by @OoBook in https://github.com/unusualify/modularity/commit/c92c3359cab9f8aaecb8c7e1498295b7b9f27110
- streamline create-issue-branch workflow by directly configuring Git and enhancing branch creation process by @OoBook in https://github.com/unusualify/modularity/commit/dec75b55a1485440a903dd73f5889a9f0c4ba107
- add 'issues' permission to create-issue-branch workflow for enhanced issue management by @OoBook in https://github.com/unusualify/modularity/commit/0a69358515023398f30a9d2f9af842da47408853
- enhance create-issue-branch workflow to include SHA in branch description for better traceability by @OoBook in https://github.com/unusualify/modularity/commit/8f7185280cf8d01fe2030e8e9728bbbdbb5f6d51
- refactor create-issue-branch workflow to use GraphQL API for branch creation and improve linking to issues by @OoBook in https://github.com/unusualify/modularity/commit/3e53fb5acffff256274bbe919f77d91e845996b7
- update create-issue-branch workflow to include source branch SHA in GraphQL mutation for improved branch linking by @OoBook in https://github.com/unusualify/modularity/commit/f9a2e713d28c35d8c50df20a57dd7e979d4424a1
- enhance create-issue-branch workflow to retrieve and utilize repository and issue Node IDs for improved branch linking by @OoBook in https://github.com/unusualify/modularity/commit/695821b3f58caf087efabad50d4d151031908ffc
- clarify issue number handling in create-issue-branch workflow by converting it to integer for GraphQL query by @OoBook in https://github.com/unusualify/modularity/commit/ec6ee320672173cb628b2568298fa72f447b9784
- add PR template checker to enforce conventional commit messages and update checklist on experimental mode by @OoBook in https://github.com/unusualify/modularity/commit/6c32e3b7461f7056af13c5ffd4e4581a169c822f
- enhance release workflow to check for Vue changes, install dependencies, and build artifacts before committing updates by @OoBook in https://github.com/unusualify/modularity/commit/a205e964bcc20e8c3aad4f3ab128525bb2e3f103
- add PHPStan configuration and GitHub Actions workflow for static analysis by @OoBook in https://github.com/unusualify/modularity/commit/a4db3c4eb485f3906b0c9aa9b0910c4ced998826
- update pr-template-check.yml by @web-flow in https://github.com/unusualify/modularity/commit/08f720b69e26a229fa74e3b44a08d30ccf850253

### :beers: Other Stuff

- :art: upgrade issue templates by @OoBook in https://github.com/unusualify/modularity/commit/a1fb332b048048ac2a3272b461c4d5b94d61da15
- simplify issue templates by removing title input fields and updating descriptions for clarity by @OoBook in https://github.com/unusualify/modularity/commit/62d6117826925663f78eda5bef1d186c4396e85b
- update pull request template to include a checklist and refined types of changes by @OoBook in https://github.com/unusualify/modularity/commit/7ebbb8781308803c8dfb1942aa4ba1a05d113f3e
- update vitest and related packages to version 2.1.8 by @OoBook in https://github.com/unusualify/modularity/commit/23efe989313049b0f8a51a8f91ddb69b6ce60732
- add anonymous image for user profiles by @OoBook in https://github.com/unusualify/modularity/commit/837e409c3e848b7f60616a2a7719e9c463562043
- add success message after command creation by @OoBook in https://github.com/unusualify/modularity/commit/fe73d1f7d855781cff39f216cab93493051b8378
- hide command from console output by @OoBook in https://github.com/unusualify/modularity/commit/3686c8506eaa8e4abf298df2442ccddc21b19c59
- add moment.js dependency by @OoBook in https://github.com/unusualify/modularity/commit/368d6e4f0f4fe6064e138f28840a7ed7257262d8
- add invokeRule function for enhanced rule processing in experimental mode by @OoBook in https://github.com/unusualify/modularity/commit/ccbca4747431491f721dc9c63a9c6d491be6d094
- enhance initialization script with moment.js and pluralize imports by @OoBook in https://github.com/unusualify/modularity/commit/58af445dcfdc4dd61bac86cff6c2f4019c841612

## v0.21.0 - 2024-12-09

### :rocket: Features

- :sparkles: add __extractForeignKey helper to find foreign name of a model by @OoBook in https://github.com/unusualify/modularity/commit/ed7054f570b06ead264a2c31cd769cfaf5a10360
- :art: add unique feature to the repeater input by @OoBook in https://github.com/unusualify/modularity/commit/dfbc4645ae532c873fc1a39213651d7ee2a479e1
- :recycle: add getTranslationLanguages method to get system languages by @OoBook in https://github.com/unusualify/modularity/commit/ee050e912237baf0bfa598ba9723d354220f84e4
- :art: add modelValue setter to formatSet if the inputPropFormat matches modelValue or model by @OoBook in https://github.com/unusualify/modularity/commit/e3de2df6f85289fcffbe7a0fc7208a23e8d62e9c
- :art: add trigger feature to TabGroup to update inner repetitive schemas by @OoBook in https://github.com/unusualify/modularity/commit/c51de594b6b47014eaef3d5daca4d5c6205273c5
- :sparkles: add ue-filepond-preview experimental component to show file/image details by @OoBook in https://github.com/unusualify/modularity/commit/7f4414c9da448ac9740d5c6d9d68dca0ea120eaa
- :art: add noRecords attr to prevent making query to db by @OoBook in https://github.com/unusualify/modularity/commit/d59893e631a5a3cd763e3345f3c0803a0cd16e9b
- :art: add a appends check of the model to get right columns by @OoBook in https://github.com/unusualify/modularity/commit/447b735653716b44a92af5d57e68b799b9e9bacc
- :sparkles: add new tag input connecting to Taggable structure by @OoBook in https://github.com/unusualify/modularity/commit/3fa0360e85599ed10556d959c7e17a286b590cec
- :art: add allowedRoles feature to the customRow attribute by @OoBook in https://github.com/unusualify/modularity/commit/a5a630a1f3eec0f817971021de0206cb8839dc14
- :art: add new headerCenter and top slots to Form component by @OoBook in https://github.com/unusualify/modularity/commit/4391ce4300f7e8c4e3708d404d470bb8b9976374
- :sparkles: add new viewOnlyComponent feat to inputs when it is in the nonAllowed roles by @OoBook in https://github.com/unusualify/modularity/commit/6213baec76ff9f6ff19731127d3f934c87eb95cb
- :sparkles: add form action feature into Form by @OoBook in https://github.com/unusualify/modularity/commit/e4a79f49fab271010acc43dc570de606716887c6
- :art: add form-top slot into Table by @OoBook in https://github.com/unusualify/modularity/commit/ba6aae5b2bc1608915dc59faa2f6f218d8e673a4
- :art: add hasVatRate into Price by @OoBook in https://github.com/unusualify/modularity/commit/fb5bf09b5aa8582682cb42f995be0e08d73415d8

### :wrench: Bug Fixes

- :bug: fix auth pages form widths && buttons && translations && colors by @gunesbizim in https://github.com/unusualify/modularity/commit/558f08e20a106fdd70c416fb5e4f41718da6df7c
- :bug: fix third party button spacings by @gunesbizim in https://github.com/unusualify/modularity/commit/3e16c70d374e62695655e542c16609878bb97886
- :bug: fix spacing on right side of auth pages by @gunesbizim in https://github.com/unusualify/modularity/commit/9df49c0dbd63138fddb51d1cb2a84f37afc710eb
- :bug: fix custom font-size for description text on the right column by @gunesbizim in https://github.com/unusualify/modularity/commit/ad72001d231c27266cf8066f1d738b7cc55413de
- :bug: fix colors for future used gray-color-lighten-1 for third party auth buttons && removed unnecessary scss by @gunesbizim in https://github.com/unusualify/modularity/commit/6ee1fd28f29e25090fae177e3d366210196c3223
- :bug: fix forgot password auth page create account button link by @gunesbizim in https://github.com/unusualify/modularity/commit/566836964db5736a08a2ac095232dc3daccbc0b2
- :bug: fix stateable hydrate if default_states array of strings convert to array of objects by @gunesbizim in https://github.com/unusualify/modularity/commit/c110404b04ea3cbbdba6ea6531fdc60c0c33d431
- :bug: fix usage of padding on auth forms && remove unnecessary scss by @gunesbizim in https://github.com/unusualify/modularity/commit/6207a9617614758a4676bd56bd517eabe70b7571
- :bug: move pivotModel generation to back of the additional models by @OoBook in https://github.com/unusualify/modularity/commit/d1784fe8f261f47c6455ae12b1a266e697488aae
- :bug: add default true value to active column of translation table migrations by @OoBook in https://github.com/unusualify/modularity/commit/6476b56ddf5a2dd10dadb3c8eecf24633be12819
- :ambulance: change lastModel as lastIndex for chain methods of pivot tables and fix calculation of schemas number on pivotableRelationships by @OoBook in https://github.com/unusualify/modularity/commit/72f32c0b7632e7e65e45d20bb05753a9fdfb8497
- :art: append ':' and '_' characters into pattern to cast strings including these by @OoBook in https://github.com/unusualify/modularity/commit/d8742188172a1eb542aa95855c2c3224eceea96f
- :bug: add disabled styling into checklist's checkboxes by @OoBook in https://github.com/unusualify/modularity/commit/445d80e17609e8a18e8f977ca7e8340c81ad7b55
- :bug: protect previous value disabled prop by @OoBook in https://github.com/unusualify/modularity/commit/c57b317cecbd1db3239051886cc9736be7d7c7e4
- :bug: prevent the formatSet on loading item by @OoBook in https://github.com/unusualify/modularity/commit/35948d8f4c6051085ba54f468d1bfbcbbaee7517
- :bug: add parenthesis if the element is third on formatPreview. by @OoBook in https://github.com/unusualify/modularity/commit/aeb0217a67484fd4b5213fcecd9b3f491e13c542
- :art: change padding of table's titles and change flex behaviour of iterator's actions by @OoBook in https://github.com/unusualify/modularity/commit/0282d184ae9b7a512bc6604886ae4be2edea7c69
- :bug: remove ml-auto due to lead to confusions on putting multiple items on the slot by @OoBook in https://github.com/unusualify/modularity/commit/914022075fb93fbbca16e0867dd3313fc02317c8
- :art: add valueChanged parameter as true on handleEvents by @OoBook in https://github.com/unusualify/modularity/commit/c36be9ed3c5a70bd6568628fb1cc692e9416d622
- :bug: put action items d-flex wrapper at right slot of Title.vue by @OoBook in https://github.com/unusualify/modularity/commit/5bdf8dc09163324076cb937724ff07f4999f8e83
- :bug: add a handle to pass fallthrough attributes by @OoBook in https://github.com/unusualify/modularity/commit/498b449b6762770999ca746f34807f45d92478a5
- :bug: remove coloring the sheet inputs by @OoBook in https://github.com/unusualify/modularity/commit/21e5da144fb930fb254d8c96753578b14b5b84ca
- :bug: set originalDisabled to disabled prop by @OoBook in https://github.com/unusualify/modularity/commit/800bf56006718402b52d661efa123ab02e5e1b81
- :art: return respond file type on filepond preview response by @OoBook in https://github.com/unusualify/modularity/commit/ad3bfd8f81d10e6c877b4ce13c15b02856d40ae3
- :art: return records by serializing on translation models at list method of repository by @OoBook in https://github.com/unusualify/modularity/commit/9459eee32edac86589d286fd82e72fd3724d61bb
- :bug: remove column fetching on repository list from cascade inputs of morphTo by @OoBook in https://github.com/unusualify/modularity/commit/1f43c024fd35791547da0df4b7154d0d2b1e7606
- :bug: remove __log helper by @OoBook in https://github.com/unusualify/modularity/commit/afcfc21b8ae54bcad901944fd1a2203df200bc6b
- :bug: handle json nested fields with '->' character at first level. by @OoBook in https://github.com/unusualify/modularity/commit/04007806539994e1b5a08c084c9cccdc239aaceb
- :bug: fix unnecessary density usage && translations by @gunesbizim in https://github.com/unusualify/modularity/commit/a88d287b272913c3af5d798248d3b83925a638bc
- :bug: fix restore unique_table rule by @gunesbizim in https://github.com/unusualify/modularity/commit/0fc24da2177e488160a74be9f393148218e4fd42
- :bug: fix button density to default on auth pages by @gunesbizim in https://github.com/unusualify/modularity/commit/a30b6b7742cc96200f147fc46634ed35804b4fba
- :bug: fix moved widgets.php to /merges && removed publishing code from LaravelServiceProvider by @gunesbizim in https://github.com/unusualify/modularity/commit/48518ca405ae575613763c7607449f7792a95731
- :bug: use applyCasting on string elements case by @OoBook in https://github.com/unusualify/modularity/commit/5eccdce8a30fb470b0a0e817825c046cdb966cab
- :bug: add class hidden into col and fix disabled case by @OoBook in https://github.com/unusualify/modularity/commit/369aa1157009c993cf774906f243d7916f732c2a
- fix payment price relationship by @OoBook in https://github.com/unusualify/modularity/commit/6537c43d46995062d8bd05b6066ccd060bc1f33f
- :bug: call triggers on changing fields by @OoBook in https://github.com/unusualify/modularity/commit/4eb2b2b3943775ae52d10576b61d89a22fc227bd
- :bug: reformat modelValue of Repeater if not formatted by @OoBook in https://github.com/unusualify/modularity/commit/508d5765b1041f658dbd81a3909aa1fdfb69756a
- remove isArray check from RecursiveStuff by @web-flow in https://github.com/unusualify/modularity/commit/07fd65de93106ca99e82d401da4df3e22194957f
- remove extra if check from RecursiveStuff by @web-flow in https://github.com/unusualify/modularity/commit/456ee3c5db06f37e553adaf8b53518711ee54494
- :art: remove showSelect from users except superadmin by @OoBook in https://github.com/unusualify/modularity/commit/f454598b9d869929b50c819d97be747f40aebd87
- remove applyCasting error return null by @OoBook in https://github.com/unusualify/modularity/commit/067c4428b5830714be1471ea33d54409794de140
- remove applyCasting error return null by @OoBook in https://github.com/unusualify/modularity/commit/7c6005f6683e115c226c820887d89e97aa9f676d

### :recycle: Refactors

- :recycle: refactor dashboard to use connector instead of controller by @gunesbizim in https://github.com/unusualify/modularity/commit/8e948a77ad59a811930d73a4cb1f8d30d2f32e63
- :recycle: refactor dashboard creation logic by @gunesbizim in https://github.com/unusualify/modularity/commit/5cabce6d94a1997186c11e5b72c4a4850e2aef5f
- :recycle: refactor of BoardInformationPlus usage and component by @gunesbizim in https://github.com/unusualify/modularity/commit/047686993309061345e1083f95e9b07a2fe371ec
- :recycle: refactor remove unnecessary variable and log by @gunesbizim in https://github.com/unusualify/modularity/commit/ba3b90dc52efabf6e3217da20d386de4fb6b96d8
- :recycle: refactor HasStateable trait and update hydrate accordingly by @gunesbizim in https://github.com/unusualify/modularity/commit/30e71f2360aae1b11ac754b94ff944e124ecce48
- :recycle: refactor dashboard table && boardInformationPlus && controller && feature UWidget by @gunesbizim in https://github.com/unusualify/modularity/commit/34b3b18052dbce6dfb95e56547e6faf32b5fa1bc
- :recycle: refactor publish.php && LaravelServiceProvider.php && create widgets.php for publish by @gunesbizim in https://github.com/unusualify/modularity/commit/5bfc6ba9caf6bea9b38df761bd49f6233ef9488f
- :lipstick: update button/input sizes acc. to density by @OoBook in https://github.com/unusualify/modularity/commit/df07aa7c1346de4296d6e3f79de377468e9937f0
- :recycle: update comparison table input to show comparator values and add active highlighted by @OoBook in https://github.com/unusualify/modularity/commit/85bc22dd0bef68424ae0662c1cbda1dbf0659cef
- :recycle: use getTranslationLanguages method on getModel by @OoBook in https://github.com/unusualify/modularity/commit/a2b951b3c12dbf5db7bfde9c9367fecd1d9c640e
- :lipstick: update the styles as in the design. by @OoBook in https://github.com/unusualify/modularity/commit/7749d7166fe02bdb46aaa3f0f3188267bd91fcc3
- :recycle: remove the autocomplete styling by @OoBook in https://github.com/unusualify/modularity/commit/0a8ccd7225c1c48fe2da64abb58771363e4a0381
- :recycle: change filepond.preview binding as uuid by @OoBook in https://github.com/unusualify/modularity/commit/28b1f688cc7428bea45777fa8f0f5db454b63b8f
- :recycle: prevent making query to db on MorphTo input type by @OoBook in https://github.com/unusualify/modularity/commit/9d67730ecb02b68d1668d0028d14dd3a691717c4
- :recycle: use isTranslationAttribute method of hasTranslation instead of using in_array helper by @OoBook in https://github.com/unusualify/modularity/commit/050160120e737e25d5cf0941b42433d60867c133
- :recycle: remove Model type from morphTo relations by @OoBook in https://github.com/unusualify/modularity/commit/e40b7ed467f3ba299eedb04c51f0c5b86b8c7660
- :art: add applyCasting method to use it by @OoBook in https://github.com/unusualify/modularity/commit/732691fe4bbdcf87d3bde93191d8ada320344b9d

### :lipstick: Styling

- :green_heart: update space and new lines by @OoBook in https://github.com/unusualify/modularity/commit/d3807262217b3d8ce72ef680a519ba03562d53a1
- :green_heart: remove dd's from formatWiths by @OoBook in https://github.com/unusualify/modularity/commit/14910ff952a037d6bacdb439eaadec44369bcfb1
- lint coding styles by @OoBook in https://github.com/unusualify/modularity/commit/6155bb0d7ea49038fd4667cc144677b163e14e6f
- lint coding styles by @invalid-email-address in https://github.com/unusualify/modularity/commit/0e9ece78a07a17ecc41d10bcaf6a17a3326ede7c

### :white_check_mark: Testing

- :test_tube: add UEConfig to checklist test by @OoBook in https://github.com/unusualify/modularity/commit/24c5c3f9792fd03c04ad163f8648a104b462281b
- :test_tube: change vuetify plugin as UEConfig by @OoBook in https://github.com/unusualify/modularity/commit/9a36e606ab6d2c1befa1510695efb8259f6e05aa

### :package: Build

- :building_construction: add new v0.20.0 build by @OoBook in https://github.com/unusualify/modularity/commit/38e50a43e9a8ed2c043f4a98fb8d44e8d12b63cb
- :building_construction: add new v0.20.1 build by @OoBook in https://github.com/unusualify/modularity/commit/efa248c330bafe7b933b903a434df991067aa3fd
- :building_construction: add new v0.21.0 build by @OoBook in https://github.com/unusualify/modularity/commit/aad79b04ee71da81f2b80dd9565478a4ad3dc9a1
- :building_construction: add new v0.22.0 build by @OoBook in https://github.com/unusualify/modularity/commit/766831ef9a5f0537b17a57c0c2f6a0d26365a1cc

### :beers: Other Stuff

- :lipstick: change edit icon content by @OoBook in https://github.com/unusualify/modularity/commit/a5ead38ea263453f334c94dd701b9a773a8444b7

## v0.20.0 - 2024-12-09

### :rocket: Features

- :sparkles: add currency exchange on payment form by @gunesbizim in https://github.com/unusualify/modularity/commit/767a9424fc644e4602c8d985e53a6138b9b81970
- :sparkles: add custom payment service button styling to payment services by @gunesbizim in https://github.com/unusualify/modularity/commit/6235a891e86add90229eae3f6b6521529fccafd4
- :sparkles: add cardType to payment services by @gunesbizim in https://github.com/unusualify/modularity/commit/0577d5f0c34f6ad256553753dea82b40db1cde5d
- :sparkles: add currencyServices relation to payment for available currencies with specific payment method by @gunesbizim in https://github.com/unusualify/modularity/commit/03c24c80b36a9458560adf23bad9cfee911f436b
- :sparkles: add align class to title by @gunesbizim in https://github.com/unusualify/modularity/commit/ecdcc61e68d867dbab7ecf81b0407c92e9fd41b1
- :sparkles: update ui for currency selection on payment form by @gunesbizim in https://github.com/unusualify/modularity/commit/da21b0cdb6f5bc372dcb8d5470f000d0ff640d90
- :art: add label to RadioGroup by @OoBook in https://github.com/unusualify/modularity/commit/ba66fc1ce5e24d0b19d5c83f76d8adb015f507fc
- :art: handle divider case on hydrating input by @OoBook in https://github.com/unusualify/modularity/commit/1e8e9bb1fbdf078482fc50f55a39e89f0eed6b15
- :art: add a feature shwoing labels as headers like a table by @OoBook in https://github.com/unusualify/modularity/commit/58cc1558086fd5f686aaf9341657fb1819f45a4d
- :art: add default form attributes and module specific form attributes by @OoBook in https://github.com/unusualify/modularity/commit/6d62a57950b919376accc5b4282654eed6f12e16
- :art: add new translated input helpers by @OoBook in https://github.com/unusualify/modularity/commit/9e3f7847418ba8872ca20be01499d7b30b12e493
- :triangular_flag_on_post: add locale hook to handle languages, active language by @OoBook in https://github.com/unusualify/modularity/commit/33302ebbc3f869d5bb016111a1dcfe698d564ee9
- :art: add a translation info to append of label by @OoBook in https://github.com/unusualify/modularity/commit/106df083bc64abf5900baa8278177f23c2d84591
- :lipstick: add coloring patterns by @OoBook in https://github.com/unusualify/modularity/commit/f1037558414eabcf0d2868a5c11c83be8d7c3a91

### :wrench: Bug Fixes

- :bug: fix selected input styling && design issue by @gunesbizim in https://github.com/unusualify/modularity/commit/bce51c7a0efedc655275f192ab880b167fa4a042
- :bug: fix hasDivider prop && title align- and text- prop by @gunesbizim in https://github.com/unusualify/modularity/commit/d5e58b6b726e71cfc3d0615cf8eef7e28b2b74f9
- :bug: fix the flexibility of right slot of title by @OoBook in https://github.com/unusualify/modularity/commit/19f3c6cb5cc7b740fd9601ae6bc82ccadad03ab5
- :bug: fix ux issues of last step form's summary by @OoBook in https://github.com/unusualify/modularity/commit/0cb1b2f6735f4581a23b6725e1a9d177b7679965
- :art: update profile structre acc. to new theming structure by @OoBook in https://github.com/unusualify/modularity/commit/209dded0df42b6558a87dbb0849f9a9151654b9a

### :recycle: Refactors

- refactor for conflict by @gunesbizim in https://github.com/unusualify/modularity/commit/ef418c7d1b4be2f19b2e6721cfa846cd8069d8ed
- :recycle: refactor conflict fix by @gunesbizim in https://github.com/unusualify/modularity/commit/7faa14caca1c79a307b0c35719394e8c1542a1dd
- :art: arrange wrap/group's title/subtitle styling by @OoBook in https://github.com/unusualify/modularity/commit/e61ac751303805df875d31141754701f5bffcf66
- :bug: remove emphasize from text display sub text by @OoBook in https://github.com/unusualify/modularity/commit/0f3784af53fec2ee3fae341c5096b607ceb52cb1
- :recycle: upgrade advanced filter menu by @OoBook in https://github.com/unusualify/modularity/commit/a72f5365f8d136bdfb14d1c0c99e1afde797f55f

### :white_check_mark: Testing

- :test_tube: change vuetify plugin as UEConfig by @OoBook in https://github.com/unusualify/modularity/commit/369c390c012020af2f4961be48f645288e12c8c9

### :package: Build

- :building_construction: add new v0.20.0 build by @OoBook in https://github.com/unusualify/modularity/commit/27ea59d21b988c7b1266fe5e135baaada1306c42

### :beers: Other Stuff

- :children_crossing: remove job title from user profile by @OoBook in https://github.com/unusualify/modularity/commit/76af300a737611ea36f5ba6fc7971ac3a2db727f

## v0.19.1 - 2024-11-27

### :wrench: Bug Fixes

- :ambulance: hotfix HasStateable duplicate state issue by @gunesbizim in https://github.com/unusualify/modularity/commit/2faf67ff012f102f915ab5b8e3446d7e4dc23860
- :ambulance: hotfix HasStateable duplicate state issue by @gunesbizim in https://github.com/unusualify/modularity/commit/48b61df2d2e31afebc81a3ede0f056d0f865768b

### :lipstick: Styling

- lint coding styles by @invalid-email-address in https://github.com/unusualify/modularity/commit/e1058b90e52bc5ddfd887185859011157e00ebd5

## v0.19.0 - 2024-11-20

### :rocket: Features

- add pint command end of generating route by @OoBook in https://github.com/unusualify/modularity/commit/2b027d80facb77f769dc4bffd865fa1a61ca2869
- prepare snapshot fields before save by @OoBook in https://github.com/unusualify/modularity/commit/30539b7b46dec40c06b8314a115f5b22d2d367f7
- :art: add controls-position feature top/bottom and also use it on mobile by @OoBook in https://github.com/unusualify/modularity/commit/07c8481f02d26d7856d341b7a6327934607c227d
- add controlsPosition prop to useTable by @OoBook in https://github.com/unusualify/modularity/commit/1a6541bcb25c9cb1bf32666dba375dcfda761dab
- :art: add assignability of an array or string value into PaymentTrait by @OoBook in https://github.com/unusualify/modularity/commit/39cd9bea42c761e0e6e4453843ecb8bf340a6c42
- :bug: add flex-wrap feature to PropertyList by @OoBook in https://github.com/unusualify/modularity/commit/36375802eef35c52bd37580b15fe4a9f1259f592
- :art: add final form structure into StepperForm by @OoBook in https://github.com/unusualify/modularity/commit/fdee92dbc2d07e31bf44d7a1dca82bd38e3a46a4
- add $headline root helper by @OoBook in https://github.com/unusualify/modularity/commit/e71ff251cffc00fb24201a88ac9930e048ec5b04
- :art: put columnStyling on ConfigurableCard by @OoBook in https://github.com/unusualify/modularity/commit/90e39babc9284276c422d90e86c76552e7063037
- :art: add morphTo filter for unspecific parents taking the type from repository by @OoBook in https://github.com/unusualify/modularity/commit/d7858f6dbcb620f05ee2181f2996bbf4e98f8f6a
- :art: develop editable/creatable input structure with hidden/boolean cases by @OoBook in https://github.com/unusualify/modularity/commit/f5386adb12ef5433101cbdab2b9ee6f1bf660e80
- :sparkles: add wavy border mixins by @OoBook in https://github.com/unusualify/modularity/commit/f34b0d7fb6d598e89388e845eef8576d64ab4ad2
- :sparkles: add striped and highlighted features to comparisonTable by @OoBook in https://github.com/unusualify/modularity/commit/65322ec5719484e60c90467561c959235cf30c17
- :sparkles: upgrade checklist component by @OoBook in https://github.com/unusualify/modularity/commit/4a627af22d77228ed834a62a6ec7676f00cb5c91

### :wrench: Bug Fixes

- :adhesive_bandage: add ssl link of Roboto's font family by @OoBook in https://github.com/unusualify/modularity/commit/40c5f16c761d8556bab984b1bc2190aeaae22820
- :bug: fix table header translation when translation doesn't exist && refactor label translation by @gunesbizim in https://github.com/unusualify/modularity/commit/b1bca14418b7105e922775bfab8bbe309bffda00
- :bug: fix responsiveness of auth pages by @gunesbizim in https://github.com/unusualify/modularity/commit/7e4118f7a7fe082119dfd4538132ab32975c44d4
- :bug: fix responsive auth pages && auth page translations && profile translations && refactor manageForm by @gunesbizim in https://github.com/unusualify/modularity/commit/4de6ec08445a8ec6902cf49ecb8e3a781bf369da
- add snapshot attributes on modelMakeCommand by @OoBook in https://github.com/unusualify/modularity/commit/e94b0d6df2fe7e5e7f0b2dc40de1bdc6e16a8030
- :lipstick: change flex structure of window of stepperform by @OoBook in https://github.com/unusualify/modularity/commit/b4b414cedc735a625840d725042ee2a4cc5df6e5
- :egg: add custom components to UEConfig by @OoBook in https://github.com/unusualify/modularity/commit/9915615fca8a19c6f7993bd1f14c09cd4e2a98c3
- :bug: change isEditing condition as gt -1 by @OoBook in https://github.com/unusualify/modularity/commit/2c095f3ebef479c42f410016ea68d155c8faead3
- :bug: update forgotten lodash methods by @OoBook in https://github.com/unusualify/modularity/commit/2196265b5191a650dcc7483b9b5d1f935b86bb88
- :bug: add '|' character into pattern of $castValueMatch by @OoBook in https://github.com/unusualify/modularity/commit/bb63cf871b140c599134dabdcc4730d90f570c81
- :ambulance: update forgotten methods of lodash by @OoBook in https://github.com/unusualify/modularity/commit/68d40735fc556e19487217a9324dc28ff56a6e0d
- :bug: put underscore preserved_state variable due to confusion with other fillable by @OoBook in https://github.com/unusualify/modularity/commit/069eca879a83fa4fb5b7ea84a4e856d9fa68f1bc
- :bug: put element variable on itemAction instead of null item by @OoBook in https://github.com/unusualify/modularity/commit/0e5b527b467b8a84008c69f21d3703421638b6fc

### :recycle: Refactors

- :sparkles: add table header translations by @gunesbizim in https://github.com/unusualify/modularity/commit/a0c489ca021a01097cfde9abe2aa28cb979f2afa
- :recycle: refactor translateHeaders method to be more efficient by @gunesbizim in https://github.com/unusualify/modularity/commit/3a7066c94d217ea0f677913efa2506ec7f363e40
- add new HasPriceable trait using Oobook/HasPriceable and use it traits by @OoBook in https://github.com/unusualify/modularity/commit/c13ffd04146dad92edbcc32e8422b15c0a1699c0
- move configurableCard and propertyList components on the generics by @OoBook in https://github.com/unusualify/modularity/commit/ec7489972bc573afa4c5f08018ee2b947378f7b9
- :recycle: change stateable preview structure by @OoBook in https://github.com/unusualify/modularity/commit/61bfb83138fd86c728a7ae40d7f4008ffcb532d6
- :recycle: break down the Table into smaller composables by @OoBook in https://github.com/unusualify/modularity/commit/f01d226fc96417510e694169df2fd0772b467437
- :recycle: get lodash methods with underscore by @OoBook in https://github.com/unusualify/modularity/commit/6a6d4fc6c2d4ae51c7b71c052ab2c6b222354e22

### :lipstick: Styling

- lint coding styles by @invalid-email-address in https://github.com/unusualify/modularity/commit/b556f10de404c6b978be6a27ddd85821e05a96c0

### :white_check_mark: Testing

- remove snapshots of configurable-card test by @OoBook in https://github.com/unusualify/modularity/commit/b4b539ea5ca5a60491819dd6ce196f0eb6f3573c

### :package: Build

- :building_construction: add new v0.19.0 build by @OoBook in https://github.com/unusualify/modularity/commit/65e6ca423af02d9ee35c64d11a2d5e31497a6adf

### :beers: Other Stuff

- by @OoBook in https://github.com/unusualify/modularity/commit/4d56e6f05aed373bcb6f0d566cd33810e0eeeec2
- :technologist: add PressReleaseCardIterator as experimental by @OoBook in https://github.com/unusualify/modularity/commit/bf418a3cbaa8382435761c656fb73d7a7719d23d

## v0.18.0 - 2024-11-13

### :rocket: Features

- :sparkles: table updates by @gunesbizim in https://github.com/unusualify/modularity/commit/cd8e15bfdbedeab09a4ea7f0fe7576b6c29915b6
- :sparkles: pr fixes by @gunesbizim in https://github.com/unusualify/modularity/commit/f7807269828f6c3167ebc09d55575db3f4033598
- :sparkles: pr fixes by @gunesbizim in https://github.com/unusualify/modularity/commit/703d1ef50c8cdcd1d841da32667c2d6084705fe2
- :recycle: removes unnecessary checks by @gunesbizim in https://github.com/unusualify/modularity/commit/678ff5f7df010043746a8bd7c27fff700a09ac46
- :sparkles: add filterHeadersByRoles method into ManageUtilities by @OoBook in https://github.com/unusualify/modularity/commit/d825387e7bf323b661211dfe33180c83a9a0852c
- :art: add pushQueryParam helper by @OoBook in https://github.com/unusualify/modularity/commit/88c6a9f8ac101f22b6ae3c97f456db6798341093
- :art: add selectable columns feature by custom by @OoBook in https://github.com/unusualify/modularity/commit/95e134bbd7aa1a5b4fa5c5a0ffb28e90eb4bfa16
- :sparkles: adds color as table header option by @gunesbizim in https://github.com/unusualify/modularity/commit/c869fb7e6b7a8ae9888ee716a56cf188af500db7
- add slots for each segment on ConfigurableCard by @OoBook in https://github.com/unusualify/modularity/commit/f0e6932b40b87497a57781e481831574c82e52f6
- :art: add base_price set attribute into modelHelpers by @OoBook in https://github.com/unusualify/modularity/commit/c2472efa9547dd771341ec42ba5c31ff5a3431f9
- add payload watcher to resend request by @OoBook in https://github.com/unusualify/modularity/commit/3a3f5d636cb33a652f53ccb95e0ff385579eeaf1
- :art: add formattedSummary into notation util by @OoBook in https://github.com/unusualify/modularity/commit/6696c92e78f2a8a95716567c29f8c15e59f491ed
- add lastStepForm feature for addons by @OoBook in https://github.com/unusualify/modularity/commit/49e71a898ed21d496c9087dd4a953aaa94a9b8d6

### :wrench: Bug Fixes

- :bug: where a key is not defined but tried to accessed on retrieved by @gunesbizim in https://github.com/unusualify/modularity/commit/222c45697fe8b44eb383a9d67e1ae9a2b07d687e
- :bug: logic error fixed by @gunesbizim in https://github.com/unusualify/modularity/commit/beeb65374b177dc7ca46ee395e806d1bd3d8e7f0
- :bug: where initial_state is not set on model by @gunesbizim in https://github.com/unusualify/modularity/commit/0bcedc5d91243f18ddc3e398d247eb803a165607
- :recycle: refactor initial_state to defaultState and add defining default_state option. by @gunesbizim in https://github.com/unusualify/modularity/commit/7afee33d028f8a9602246089e8c80810699ac575
- :bug: fix the issue where initialState and defaultState are mixed by @gunesbizim in https://github.com/unusualify/modularity/commit/3c79184f0431b5d8d712cd2ff5a8fec3dd372110
- fix tr border radius of Table.vue and add merge config fields by @OoBook in https://github.com/unusualify/modularity/commit/7f92c6a476053a2579be3fd79a5ce18e97cfb2ae
- :ambulance: change isEditing variable from Number to Boolean by @OoBook in https://github.com/unusualify/modularity/commit/c06f8a21967824bd4afce6e2cb0eaa5618a2ba06
- fix serializeParameters recursive call by @OoBook in https://github.com/unusualify/modularity/commit/5a5645fb3a62e2135225a455943582d9031de6dd
- handle getSchema recursive if only its type is in [wrap,group,repeater, input-repeater] by @OoBook in https://github.com/unusualify/modularity/commit/a60aed228c9a51dc3aa971001132b20d7b6623d4
- add isEditing prop in order to pass Form components by @OoBook in https://github.com/unusualify/modularity/commit/22cc259a09ccef408bacea559d61097f70b775c3
- :bug: auth pages responsive design fixes with global button and input styling updates by @gunesbizim in https://github.com/unusualify/modularity/commit/65ada8085172ac4f96c237d6f4ebc926d002f77b
- :bug: fixed the issue where createOnModal is false for add button but still shown by @gunesbizim in https://github.com/unusualify/modularity/commit/7b8bd65fb6339048f266f678f0a42d106e08b700
- :bug: fixed phone input issues && labels && titles by @gunesbizim in https://github.com/unusualify/modularity/commit/d7659b76675e5bf6931250ab138b234e5eedf5df
- :ambulance: remove recursive lines by @OoBook in https://github.com/unusualify/modularity/commit/6d27391cd50e4b40543fbd9f7eb9cdaa8bc92f7e

### :recycle: Refactors

- :recycle: stateable structure changed by @gunesbizim in https://github.com/unusualify/modularity/commit/4b3cfa79fb55d4dfea4d47fa901273e58624f799
- :recycle: removed unnecessary checks by @gunesbizim in https://github.com/unusualify/modularity/commit/2a9b17340b5825b888cd92394c231e641ccd340b
- :recycle: move filterFormSchemaByRoles into ManageUtilities as filterSchemaByRoles by @OoBook in https://github.com/unusualify/modularity/commit/b563f77c49dae6956ba650b386682d646af15d12

### Styling

- lint coding styles by @invalid-email-address in https://github.com/unusualify/modularity/commit/2f0c7199c11d8a5858ffc25000409f9295f53c6b

### :white_check_mark: Testing

- :white_check_mark: snapshots updated vitest && added test:update command for snapshot by @gunesbizim in https://github.com/unusualify/modularity/commit/6bd8f404ad6e985312aed5d0e97e4e0ac3f986c0
- by @OoBook in https://github.com/unusualify/modularity/commit/5bcae46f86cb58ccea5e377aaddef643dfa5887f

### :package: Build

- :building_construction: add new v0.18.0 front build by @OoBook in https://github.com/unusualify/modularity/commit/fd672168b6e9da2ad4448bc9f8809d900b2eece5

### :beers: Other Stuff

- Merge branch 'refs/heads/release/v0.17.0'
- Update CHANGELOG
- Merge branch 'dev' into feature/general-table-update
- Merge branch 'feature/general-table-update' of https://github.com/unusualify/modularity into feature/general-table-update
- Merge pull request #74 from unusualify/feature/general-table-update

The stateable task was completed as I wanted. The Initial state, default state, and default_states structures look completed at first glance.

- add comments of filterHeadersByRoles method by @OoBook in https://github.com/unusualify/modularity/commit/d955f7f56fb7f6987deca9647c2dddc9593a6dd1
- Merge pull request #75 from unusualify/feature/add-allowed-roles-to-headers

Feature/add allowed roles to headers

- Merge pull request #76 from unusualify/bugfix/auth-pages

fix(auth page responsive & design fixes): :bug: auth pages responsive…

- Merge pull request #77 from unusualify/bugfix/edit-profile

fix: :bug: fixed phone input issues && labels && titles

- Merge pull request #78 from unusualify/feature/table-header-color

feat(table): :sparkles: adds color as table header option

- Merge pull request #79 from unusualify/feature/add-new-cards-to-stepper-form

Feature/add new cards to stepper form

- merge remote-tracking branch 'origin/dev' into release/v0.18.0

## v0.17.0 - 2024-11-06

### :rocket: Features

- :sparkles: stateable (dynamic enums) by @gunesbizim in https://github.com/unusualify/modularity/commit/596c2be27da6b54dadb9afd5d510490491bae937
- add input-filepond case to get display value of it by @OoBook in https://github.com/unusualify/modularity/commit/547014e58b6d93f9b7f2af17cee79d7cc7b93d14
- :art: add regex conditional to display preview by @OoBook in https://github.com/unusualify/modularity/commit/52936232d6605ad36bdd5e15b7eca64d8d9fa6fd
- :art: add printRequest component to print data coming from an endpoint by @OoBook in https://github.com/unusualify/modularity/commit/9d5865bb5b51442be247b4a730de9bc11c30dafb
- :art: add new currencyExchange service by @OoBook in https://github.com/unusualify/modularity/commit/12504b6a14b25699303f7fb3b8f2ba2e3dd4e0e6
- :art: add ux upgrades on StepperForm by @OoBook in https://github.com/unusualify/modularity/commit/ee6cdbc46631ec31cf3ed742cf55bf35119a6c96
- :sparkles: table updates, states table new field migrations by @gunesbizim in https://github.com/unusualify/modularity/commit/fb1fce48a91731e48623c3b3590a9db6dfc5c3e0

### :wrench: Bug Fixes

- add Roboto font family into b2press theme by @OoBook in https://github.com/unusualify/modularity/commit/90714c9e8dee1ea2dc45d2f5e8d4b8d1d4f6d39a
- :ambulance: remove is_payable bullshiit leading prices table to drop by @OoBook in https://github.com/unusualify/modularity/commit/3f8c58ea3e4bb5bfcf91479b925da87536483ac9
- :bug: set embeddedForm  as false on default by @OoBook in https://github.com/unusualify/modularity/commit/c256d31243442e11a9f8c11148ee5f9cd1c26a9c
- convert save-success to messages. by @OoBook in https://github.com/unusualify/modularity/commit/3a2ffd6a0e558a5400e653f7141d2283b2fd8aed
- :bug: get admin with role scope by @OoBook in https://github.com/unusualify/modularity/commit/17be0370e3e17a5d3e563ab8e533bd43be125cef
- remove indexing problems for uuidMorphs by @OoBook in https://github.com/unusualify/modularity/commit/9ba0afe10ba63316a5ddfd9d85d78fe6802a2828
- add previous components by @OoBook in https://github.com/unusualify/modularity/commit/5158f3256551e544919c9e66e756234584121540
- :bug: migration fix by @gunesbizim in https://github.com/unusualify/modularity/commit/056327826ae5b7aada0cdd026b9c6a3ff2eaef6b
- add getIndexUrls into dashboard and profile by @OoBook in https://github.com/unusualify/modularity/commit/2b44d586ad43b4cee4fb423d127d06367aa5d09e
- add default and client keys into profileMenu by @OoBook in https://github.com/unusualify/modularity/commit/36c10ac3419853638ad439e49e0a9d31fac0067e

### :recycle: Refactors

- :recycle: utilities as system module by @gunesbizim in https://github.com/unusualify/modularity/commit/7cc6332f207eb2fd50cc953eccfc3ceafe621570

### Styling

- lint coding styles by @invalid-email-address in https://github.com/unusualify/modularity/commit/632254be57dc21840d48f43006957f7090c2faa1
- :lipstick: change padding-top to 4 by @OoBook in https://github.com/unusualify/modularity/commit/6a5bdf52b7cfe2ce495601d85ef3b2b7b4f28c1e
- :lipstick: add text-body-1 for subText by @OoBook in https://github.com/unusualify/modularity/commit/0a3e83503b6ad2189ef214c78536b84fbb0510ec
- add md threshold for radio buttons by @OoBook in https://github.com/unusualify/modularity/commit/7029c67581d45d95a12031eaa5e09131834aa489

### :package: Build

- add new v0.17.0 front build by @OoBook in https://github.com/unusualify/modularity/commit/5b7b7069b2839744268c95088628b58b8cce8322

### :beers: Other Stuff

- Merge branch 'refs/heads/release/v0.16.0'
- Update CHANGELOG
- Merge branch 'dev' into feature/enum
- Merge pull request #72 from unusualify/feature/enum

Feature/enum branch passed tests, so we can merge it in experimental mode. We check it out again later.

- add Vat and Total localization keys by @OoBook in https://github.com/unusualify/modularity/commit/d63f912627eef02ac388b9bc82cd3ee0e01fdff3
- change defult input paddings by @OoBook in https://github.com/unusualify/modularity/commit/9d459fdf19a766873a502be64641909bb087c99e
- add save-success key to translation by @OoBook in https://github.com/unusualify/modularity/commit/f330d2908dce79d9c9d70a1383e5bb59fa51abe2
- fix mb-theme to mb by @OoBook in https://github.com/unusualify/modularity/commit/0508c3f71464c73d2e103922df7bf181c6591a43
- :recycle: clear navigation defaults by @OoBook in https://github.com/unusualify/modularity/commit/92119ee22efbf5afa85a7b49d1e059ec4e4fede4
- Merge pull request #73 from unusualify/feature/currency-exchange-service

Feature/currency exchange service has passed tests.

- Merge remote-tracking branch 'origin/feature/general-table-update' into dev

## v0.16.0 - 2024-11-04

### :rocket: Features

- :sparkles: Global scrollable directive with height modifier by @gunesbizim in https://github.com/unusualify/modularity/commit/db119c1728c0a6bc39dc97eef9e2b7262e892b6c
- :sparkles: add hasRequestInProgress feature for tracking any request in progress by @OoBook in https://github.com/unusualify/modularity/commit/343472acf3786eac1264bee1a088293f921dd333
- add __isBoolean helper by @OoBook in https://github.com/unusualify/modularity/commit/65f303d46a500a489683771a03d8a1de8cb0deae
- :sparkles: images of payment services to the seeder by @gunesbizim in https://github.com/unusualify/modularity/commit/39cc53dc017fb9cf0bb0bc169bc9e9b63b970b90
- :sparkles: add curly braces expression into translation's replacement pattern by @OoBook in https://github.com/unusualify/modularity/commit/e4381a2b6b9fe8502f0807541de5d99bcea1f111
- add en-US number format by @OoBook in https://github.com/unusualify/modularity/commit/62f77a7fb722c46e00f10ce0fc05aa6359c2bca7
- :sparkles: translatable title and modified title component by @gunesbizim in https://github.com/unusualify/modularity/commit/768434641a54852bde1ef22790d551a5fc4e7d09
- :lipstick: upgrade theming structure by @OoBook in https://github.com/unusualify/modularity/commit/b670fc992c9214d80bd37937f85d02e120e709bd
- :art: add validation ui styling to ue-tab-group by @OoBook in https://github.com/unusualify/modularity/commit/28a2e259c3efd0e770ccedf56fc97a3c4dc03228
- :art: add new infrastructure helpers by @OoBook in https://github.com/unusualify/modularity/commit/66e003c6a748c32d2f444bf2132b1f4ccad8f189
- :art: add sidebar toggling methods and mutations to config store by @OoBook in https://github.com/unusualify/modularity/commit/01fcf6e1725abd95546661e9edada9a00284ae42
- add ue-text-display component by @OoBook in https://github.com/unusualify/modularity/commit/21cdffd750433bb2ac351f081a0e89f962845fe4
- :art: add new notation util for various dot formation by @OoBook in https://github.com/unusualify/modularity/commit/0187c7e6fb8aba4f0ed5f0eef186f454a49f7a82
- add title and subtitle elements for wrap and group by @OoBook in https://github.com/unusualify/modularity/commit/4ec3a0d5e1fb4f4b64d0bdb50de15e1f3fa63bf2
- add ue-overlay generic class by @OoBook in https://github.com/unusualify/modularity/commit/c7729c2936c0ba00450e61d3e46a45f43ab1159d
- :art: add new ue-propert-list comp. to list bold and desc. elements of arrays by @OoBook in https://github.com/unusualify/modularity/commit/75cd736a3c3da0a809de7a6b008bcf18b27b3338
- :art: add ue-dynamic-component-renderer for parse vue component literals into object by @OoBook in https://github.com/unusualify/modularity/commit/d85fcd6c3ab231fe4b23821824adf81f00e779e6
- :sparkles: add configurableCard for various card combinations by @OoBook in https://github.com/unusualify/modularity/commit/2bc39845fa3935ee78b02fabeab7dca549f4e204

### :wrench: Bug Fixes

- merge remote-tracking branch 'origin/main' into dev by @OoBook in https://github.com/unusualify/modularity/commit/29c387ae064552f3c7dea15abbea2923246881f6
- :bug: CreditCard component image url fix by @gunesbizim in https://github.com/unusualify/modularity/commit/acdd24c36e7d3a16e9516ded8d07bc75553c43a2
- :bug: responsiveness of auth pages by @gunesbizim in https://github.com/unusualify/modularity/commit/65c4762496c259fb150b6235a6281f0156a5e481
- update title localization of form for new fields group by @OoBook in https://github.com/unusualify/modularity/commit/08d2f3a70c0237eb22b5f8c3f5c3bcad85645b07
- :bug: payment service icons by @gunesbizim in https://github.com/unusualify/modularity/commit/0fbf429071ac7ce2d886d56d1e9de8ecb6f77d30
- :bug: default selected payment service && error response of payment by @gunesbizim in https://github.com/unusualify/modularity/commit/24ada13bda8b4b165f7dc5a966d3421d663cdcbc
- :bug: sidebar issue for files by @gunesbizim in https://github.com/unusualify/modularity/commit/0c73fde84028f37139350bb1f3730a1d9ecd6334
- remove v--theme class from v-field of inputs by @OoBook in https://github.com/unusualify/modularity/commit/6301a81ef209d1d8b07fa3a87cac230e7ddd0165
- merge remote-tracking branch 'origin/bugfix/responsiveAuthPages' into dev by @OoBook in https://github.com/unusualify/modularity/commit/44f50bee6267acf676f4484474dfcb0123d6c960
- :bug: add CONFIG mutation into useSidebar by @OoBook in https://github.com/unusualify/modularity/commit/118ad2f4ea51b21d3314623abd07600ea239f290
- :bug: add a check for whether title is object or string by @OoBook in https://github.com/unusualify/modularity/commit/fb09615a2eac83abcbd6bec64e8424829a1b52b4
- remove csrf variable from Filepond by @OoBook in https://github.com/unusualify/modularity/commit/554949e41aebcb40d3b98bc28bfb3540b47b6890
- put bg if isset and put default and right slot by @OoBook in https://github.com/unusualify/modularity/commit/464785dd166c79d6dbd83b8a381100bff4b22d9d
- :bug: add methods as ref by @OoBook in https://github.com/unusualify/modularity/commit/de96770bf690a752ee6fce07ab724b4c9579eb88
- update the self value of related field as the value but not the object key by @OoBook in https://github.com/unusualify/modularity/commit/022ddf8492e6cdd7ce7894f7c1995ff64985cc1d
- :bug: add set sidebar false if it's mdAndDown by @OoBook in https://github.com/unusualify/modularity/commit/56f64cc376fc4300127d88c158d9aceba87e5647
- :bug: get $csrf from root methods by @OoBook in https://github.com/unusualify/modularity/commit/7c3a68e0943a91f32667cb61499c318623b09a1c
- :bug: change activeMenu ux mechanism by @OoBook in https://github.com/unusualify/modularity/commit/1511ef93bf1a30e39893e59a378c627fb8e85cb7
- :bug: move v-html directive from template to span by @OoBook in https://github.com/unusualify/modularity/commit/5d8c434e77ec15229b80450eccb51a4775a7d070
- :art: add different ui options and ux operations into StepperForm by @OoBook in https://github.com/unusualify/modularity/commit/041b8893418413623ffc243ad6eec8fb92561786

### :recycle: Refactors

- :recycle: priceController optimized by @gunesbizim in https://github.com/unusualify/modularity/commit/c516084300b7ccaf5fde410841edb26a802cb98d
- combine array ad object conditions by @OoBook in https://github.com/unusualify/modularity/commit/5416b48c37884387882cd40121f22dfca5ecd026
- :recycle: configure title.vue structure with props by @OoBook in https://github.com/unusualify/modularity/commit/f0e9cc604e9138d3abb9414f8ef1d3016cd7b985
- :recycle: configure title.vue structure with props by @gunesbizim in https://github.com/unusualify/modularity/commit/d7c0815fc44265e9e1465a02bd05034274b1a513
- update price structure as in standard inputs, add additional attrs requiring on ui by @OoBook in https://github.com/unusualify/modularity/commit/37fc0e3abb46fedc07d2421f29a1cb28dc129b73
- :art: add new sidebar structure, and update unnecessary dipslay thresholds by @OoBook in https://github.com/unusualify/modularity/commit/b70a3e5e1fcb7ae287c1fde418d19b517c787c58
- add icon and remove colors from impersonate toolbar by @OoBook in https://github.com/unusualify/modularity/commit/e79550ff0bb889896f35f1646989ecc243c1e3b9

### Styling

- add white-space:normal to RadioGroup by @OoBook in https://github.com/unusualify/modularity/commit/c9382791ce5409896f3881d3d20aa6f6eab42063
- :wastebasket: clean the hooks by @OoBook in https://github.com/unusualify/modularity/commit/d5275b87cd17d5087516f4fa6ecdfa4280c273e1

### :package: Build

- :building_construction: add pluralize package to frontend by @OoBook in https://github.com/unusualify/modularity/commit/74966b30b07cb09a7880cd47fd3f4ef19d154314
- :arrow_up: upgrade vuetify from 3.6.13 to 3.7.3 by @OoBook in https://github.com/unusualify/modularity/commit/8fcabe5d9318850f30ded5b2213dfa5b2b741ea8
- :building_construction: add new v0.16.0 front build by @OoBook in https://github.com/unusualify/modularity/commit/03b463f073dd800b64af1c3fde34a26c2dc5c18d

### :beers: Other Stuff

- :recycle: comment old development variables by @OoBook in https://github.com/unusualify/modularity/commit/a306972ab2e9ece0e7f629a48fdd3c5186f259c6
- Update CHANGELOG
- safety commit before merge
- Merge remote-tracking branch 'origin/dev' into dev
- live fixes
- Merge remote-tracking branch 'origin/main' into dev
- Merge remote-tracking branch 'origin/refactor/priceController' into dev
- Merge remote-tracking branch 'origin/testing/payment-testing-live' into dev
- Merge remote-tracking branch 'origin/feature/scrollable-directive' into dev
- add facade comments by @OoBook in https://github.com/unusualify/modularity/commit/26fd5a89257e249e7ba6a3376aacac34820691ec
- Merge pull request #71 from unusualify/bugfix/internal-payment-services

fix: :bug: CreditCard component image url fix

- add missing fields by @OoBook in https://github.com/unusualify/modularity/commit/d569bb7ae249053b335ccd855441c51270457353
- remove log by @OoBook in https://github.com/unusualify/modularity/commit/ff1a98b1b1be6d48f16606c51a2bd79af18961e1
- add missing icons for navigation by @OoBook in https://github.com/unusualify/modularity/commit/8737da67cd45267e890ec57b004a2b30695bf3f7
- add some icons into mdi.js by @OoBook in https://github.com/unusualify/modularity/commit/4976b38d5b6f0a0032c9a1a4d3a148232940e9dc
- add some snapshots by @OoBook in https://github.com/unusualify/modularity/commit/247a97e83fafc8340f77314a1100b973e98ac91f
- add some test methods to getFormData by @OoBook in https://github.com/unusualify/modularity/commit/6521092963fb5e4cee178b502950a164e5bc59d5
- :alembic: add experimental configurableCardHelper by @OoBook in https://github.com/unusualify/modularity/commit/7093bf8d801ed00c99c841ca31ada569e3a4cb99

## v0.15.1 - 2024-10-07

### :wrench: Bug Fixes

- change filepond facade and service class directory by @OoBook in https://github.com/unusualify/modularity/commit/db2de645dd5cdcec99ad053f5b4838a4a983599f

### :beers: Other Stuff

- Update CHANGELOG
- Merge pull request #69 from unusualify/hotfix/v0.15.1

fix: change filepond facade and service class directory

## v0.15.0 - 2024-10-04

### :beers: Other Stuff

- change command signatures by @OoBook in https://github.com/unusualify/modularity/commit/29a7d9a5ea2c2054ab9ca0355331b7e82b7a8911

## v0.14.1 - 2024-10-04

### :wrench: Bug Fixes

- :ambulance: remove systemPayment migrate calling from InstallCommand by @OoBook in https://github.com/unusualify/modularity/commit/18309f0c8b6ebb3a58581ad521f98bf63edf5e26

### :beers: Other Stuff

- Update CHANGELOG

## v0.14.0 - 2024-10-04

### :rocket: Features

- add SystemPayment migration to install command by @OoBook in https://github.com/unusualify/modularity/commit/f95226dd5a3641fd495f2596cbadd50294267204

### :wrench: Bug Fixes

- add ClassMapGenerator dependency to the Finder class by @OoBook in https://github.com/unusualify/modularity/commit/c3e942d966a025cf0b21a17f966f86167fe5a9ce

### :beers: Other Stuff

- organize seeder for test and setup by @OoBook in https://github.com/unusualify/modularity/commit/efde13964e747a72ba90a541c14a155fd8988188
- Merge pull request #67 from unusualify/dev

Dev

- Update CHANGELOG

## v0.13.0 - 2024-10-04

### :rocket: Features

- :sparkles: media library tag filters && ui update by @gunesbizim in https://github.com/unusualify/modularity/commit/9761ab91f112dc6f5344599e5e046ccec2e84dac
- :sparkles: default system seeder by @gunesbizim in https://github.com/unusualify/modularity/commit/23e69e30e9e614d9171ffbeb6931dc0557a0ca0b

### :wrench: Bug Fixes

- :bug: add slotable input to processedInputs by @OoBook in https://github.com/unusualify/modularity/commit/827e44c0b3c21c75298daa86d1966e3a50501e44
- set currency acc. to priceable setUserCurrency by @OoBook in https://github.com/unusualify/modularity/commit/3eeac3360ddab4f31dd3621d234ccc2f0272be9b
- change currency behaviour and fix price calculating on update by @OoBook in https://github.com/unusualify/modularity/commit/79ef5709565c1d65b359359b901dc50c5b21d4c1
- change orders of Relation and Payment traits by @OoBook in https://github.com/unusualify/modularity/commit/07219430736b947c79b6c3b96b366dfb79ed3257

### :recycle: Refactors

- :recycle: add is_external and is_internal field into payment_services create migration by @OoBook in https://github.com/unusualify/modularity/commit/c8c3c81b574de099b6ee3aa7da458d8782ed318c

### Styling

- set ordered_traits to false by @OoBook in https://github.com/unusualify/modularity/commit/825423c319cd43db38fa23c904469e1a6dc8fca2
- change return value on comment by @OoBook in https://github.com/unusualify/modularity/commit/96413571fd7e4601d810a2394fa1cc44a0c3e40d

### :package: Build

- :building_construction: build the frontend for v0.13.0 by @OoBook in https://github.com/unusualify/modularity/commit/4fab4ce7dc072b22bd8adadde81e872ebe52e97c

### :green_heart: Workflow

- fix checkout before merge by @OoBook in https://github.com/unusualify/modularity/commit/992025145948ef81699de438cf2f3b447d3c870d

### :beers: Other Stuff

- merge remote-tracking branch 'origin/main' into dev by @OoBook in https://github.com/unusualify/modularity/commit/789798c05c2e2ba99573a25a492743804181bb56
- assess currency config fields by @OoBook in https://github.com/unusualify/modularity/commit/63d8e93a6d8c20ec0c0f8b23aadfc21ba17b61fa
- merge remote-tracking branch 'origin/feature/media-tags' into dev by @OoBook in https://github.com/unusualify/modularity/commit/58f465fe3dc773459f2a49041a1c95763b2ae5ad
- change currency order in seeder by @OoBook in https://github.com/unusualify/modularity/commit/a26be282472c612fc5d1806d345a97a374ede9eb
- add SystemPayment Main Seeder by @OoBook in https://github.com/unusualify/modularity/commit/b1f6efcec6072bf8b32371626e1ef4294694858b
- add snapshot and payable configs to the publishes by @OoBook in https://github.com/unusualify/modularity/commit/2edd7f6a1c3eda3c5ff7aea00c04810cb664e93c
- Merge pull request #66 from unusualify/feature/system-seeders

feat: :sparkles: default system seeder

## v0.11.0 - 2024-10-02

### :rocket: Features

- add new CacheList command by @OoBook in https://github.com/unusualify/modularity/commit/537588472a7258669c836be3fc73fa63e12ae0ac
- :art: add currency preset according to locale by @OoBook in https://github.com/unusualify/modularity/commit/623d9f3ad4e659a1df62093fee6fdbfc6356e379
- :sparkles: add modularity:pint command for modules and modularity by @OoBook in https://github.com/unusualify/modularity/commit/a4b2c8e393aac286cf3edfe3a95cddd9e9ccf2be

### :wrench: Bug Fixes

- :bug: missing migration for is_external && is_internal fields on payment_services table by @gunesbizim in https://github.com/unusualify/modularity/commit/c88bcddcc310fe80974f3a6946532be0e208cc9b
- :bug: creditCard component fixes and icon change for pay action by @gunesbizim in https://github.com/unusualify/modularity/commit/0c327f8e2c366403bbe162f25a5f235d6f8d95d2
- :bug: added tags method to coreController && fixed the tags issue on relationTrait && updated paymentServiceSeeder for ideal payment by @gunesbizim in https://github.com/unusualify/modularity/commit/18bb8f66f7753f2b443eb3e64dfcea562dd2b9ab
- add if Snapshot model has priceable by @OoBook in https://github.com/unusualify/modularity/commit/c8f38b6912557281cb6da698fbce5f86a2a1aa24
- :ambulance: reset cache if paths does not match with base path by @OoBook in https://github.com/unusualify/modularity/commit/2863bf407e6f48fb7f284e39d2ce59cc67ff3204
- :bug: remove class v-btn--uppercase from default v-btn by @OoBook in https://github.com/unusualify/modularity/commit/26c96d29939422b6760870b60a0d45f4b82486c7
- add pint.json of the modularity as config into the pint command by @OoBook in https://github.com/unusualify/modularity/commit/ecb291789f957c495508735384c1bd3b09bd6d18
- :bug: get module view path from module->getDirectoryPath by @OoBook in https://github.com/unusualify/modularity/commit/904925a03f7b573209b5b723207481a30202896f
- enum for php >=8.1 by @OoBook in https://github.com/unusualify/modularity/commit/301036d8da97a708b82db54d7efd3cc11ffb1baa

### :recycle: Refactors

- add payment config for payable transactions by @OoBook in https://github.com/unusualify/modularity/commit/c74182e3801b12bd2359d0a4e321c2eb59bc1a47

### :memo: Documentation

- :pushpin: pin php support to 8.1 >=  for release 1 by @OoBook in https://github.com/unusualify/modularity/commit/85604530dff0abb8bc23e3e86413d5442e50ec52

### Styling

- :art: lint generally files according to pint.json added newly by @OoBook in https://github.com/unusualify/modularity/commit/b2c12b510e8969350790c3f768989ee01431971f

### :green_heart: Workflow

- change working-directory for vue tests by @OoBook in https://github.com/unusualify/modularity/commit/c88b8d6065bdfff2ef9008813c2fcfcd8c6cdc74
- change main.yml by @OoBook in https://github.com/unusualify/modularity/commit/ab12b1b827590644372d85b40d15fbbd18023669
- change main.yml by @OoBook in https://github.com/unusualify/modularity/commit/02a1756423a781b7ac69ee83308be75bedc43425
- change main.yml by @OoBook in https://github.com/unusualify/modularity/commit/a25c79b00e7d02d590bebf863cbd18647ed31398
- :green_heart: add laravel and vue conditions for testing by @OoBook in https://github.com/unusualify/modularity/commit/741e78858478772a5009f6107981a5a1ad459f8f
- create debug.yml by @web-flow in https://github.com/unusualify/modularity/commit/45c17d2414a9d496fd31a108ff7f9f37c76fb543
- remove vue and laravel tests from release action by @OoBook in https://github.com/unusualify/modularity/commit/badab6074abee05b5d29effc56e6f23b28acde4e
- Update debug.yml by @web-flow in https://github.com/unusualify/modularity/commit/43ba43843e4491a24948b50f27e7fb513654d6be
- add linter before releasing by @OoBook in https://github.com/unusualify/modularity/commit/503b06560b1a910ffe50a90a5d5edc3d6ec0d26e

### :beers: Other Stuff

- Update CHANGELOG
- fix psr-4 issues by @OoBook in https://github.com/unusualify/modularity/commit/a40820ad37d733f34e2c25c96fce38d2c2d9c303
- Merge pull request #62 from unusualify/dev

We have merged ci changes.

- remove manifest dd by @OoBook in https://github.com/unusualify/modularity/commit/6a69715b542af1ee37aa816bb0f88ff264b0ee59
- Update main.yml
- Merge remote-tracking branch 'origin/main' into dev
- Update main.yml
- Update main.yml
- Update main.yml
- add pint for dev changes by @OoBook in https://github.com/unusualify/modularity/commit/42c2abc524ff2023fa024541d132eebf018b8413
- configure pint.json file by @OoBook in https://github.com/unusualify/modularity/commit/49a31ef3c1c4aefa580a75eb04cab4427a74ad83
- Merge pull request #63 from unusualify/dev

It's tested successfully.

- merge remote-tracking branch 'origin/feature/media-tags' into dev by @OoBook in https://github.com/unusualify/modularity/commit/be75c556f19b6157b5ed52c354b14f52c1399a00
- update php version by @OoBook in https://github.com/unusualify/modularity/commit/2e1671b08288d96de8a60c610d0b6a28944e2b64
- add pint scripts by @OoBook in https://github.com/unusualify/modularity/commit/69087a2afda0fb0564e1f3fd56a957cb18d1fecf
- Merge pull request #64 from unusualify/chore/pint-fix-all

Thanks, the pint command is so good starting point. To style available files is also testing case, it has run successfully. That's done.

- Merge remote-tracking branch 'origin/main' into dev
- Update debug.yml
- Merge remote-tracking branch 'origin/main' into dev
- Update debug.yml
- merge remote-tracking branch 'origin/main' into dev by @OoBook in https://github.com/unusualify/modularity/commit/64af45efe03b236665c9bf6b97389bcf2fbb2067
- merge remote-tracking branch 'origin/main' into dev by @OoBook in https://github.com/unusualify/modularity/commit/8e3423785ce7d9cfe8f170be65e9496f993d86e8

## v0.10.0 - 2024-09-26

### :rocket: Features

- :sparkles: introduce preview on media files uploaded with filepond by @ilkerciblak in https://github.com/unusualify/modularity/commit/1622e35b5ae752840914d6e18efc56a14d59993b
- :sparkles: introduce file type validation functionality to filepond component by @ilkerciblak in https://github.com/unusualify/modularity/commit/7e0c64e2aa1a350f8beea789e1de84edc0e6e8fb
- :art: add merge mechanism for laravel localization by @OoBook in https://github.com/unusualify/modularity/commit/648d39c2f76783fba73a1577c903ef8828b7daa9
- :sparkles: table name option to migration make command by @gunesbizim in https://github.com/unusualify/modularity/commit/75af31ac5b2489d2d5c34a99e52dd079d1524c24
- add new route-custom-model script by @OoBook in https://github.com/unusualify/modularity/commit/9c5e3432443ea3adc1b39abe518eeef87b9ff31c
- :sparkles: payment trait && required module files such as payment services && payment by @gunesbizim in https://github.com/unusualify/modularity/commit/4335a8aa617734de8657f056fc09945dfce38b6a
- :sparkles: default currency for payment services by @gunesbizim in https://github.com/unusualify/modularity/commit/74094cc22c733042fba697367b540286e3e760c5
- :sparkles: feat: :sparkles: payment trait && required module files such as payment services && payment continue by @gunesbizim in https://github.com/unusualify/modularity/commit/782943ec555f216f843c4bb8d371ddaa63dcd2a2
- :art: add change_array_file_array and add_route_to_config helpers by @OoBook in https://github.com/unusualify/modularity/commit/3aedeecaaa36cdbf29a877ba294e9ebb758e7382
- :sparkles: paymentcontroller for systempayment by @gunesbizim in https://github.com/unusualify/modularity/commit/cf88c26cc62dae997d8c1232b4bcb8d3a01488e3
- :art: add new url query handling functions to pushState by @OoBook in https://github.com/unusualify/modularity/commit/15416c9d5802f5fdcf36e5ca4416436a2b41cb44
- :art: add useModule for creating view boilerplate to components like table and tabGroups by @OoBook in https://github.com/unusualify/modularity/commit/9f031c8be41db9247d06fd13adf9a5b2f9b7e9c5
- :art: add ue-tabs component by @OoBook in https://github.com/unusualify/modularity/commit/9a277652fa63d5129f0c973d1a4b2fea702d7609
- :art: add tab-groups component by @OoBook in https://github.com/unusualify/modularity/commit/08354cd0baa003d1cfc16e0517887998133bacc8
- :sparkles: credit card form component by @gunesbizim in https://github.com/unusualify/modularity/commit/470105233756bd874fc5d819d97810fefdf91ea4
- :art: add no-migration flag into make:module and make:route commands to not create add migration if custom-model exists by @OoBook in https://github.com/unusualify/modularity/commit/53e555dfd621eeda4f3f9327a53bbc7b53100672
- :art: add new method to get all module models by @OoBook in https://github.com/unusualify/modularity/commit/30a7719ba835b8ab3860e063476c915b34aae0ff
- :art: add new method to Finder to find all model classes by @OoBook in https://github.com/unusualify/modularity/commit/9fb5c83b7087c06c8b1143ec3ed79576a4d03623
- :art: add cross relationship structure by @OoBook in https://github.com/unusualify/modularity/commit/3bf41e0db782b6e9c0888d0cba95947629325fd1
- :sparkles: introduce draggable table rows by @ilkerciblak in https://github.com/unusualify/modularity/commit/34c4d1e78d829f1aacae723ee2780f905a18a96a
- :sparkles: implemented end to end drag-drop reorder functionality with optimistic ui approach by @ilkerciblak in https://github.com/unusualify/modularity/commit/ca18777d112b650ceab2d25cd81ca489d97bb5dc
- :sparkles: creditcard, creditcardform and payment vue components by @gunesbizim in https://github.com/unusualify/modularity/commit/d35cc9cf2de6a7a7e86fe0903f927ba5eb50ef1a
- :sparkles: icons to payment services by @gunesbizim in https://github.com/unusualify/modularity/commit/046c760cb5935a98f54dd2e89d468d3bd8f713e8
- :art: add hydrateInputType feature for predefined input types by @OoBook in https://github.com/unusualify/modularity/commit/cfa3902944fb6ea93bac926dea6b6da9c97ebe8b
- :art: enhance Tabs component responsing to array items by @OoBook in https://github.com/unusualify/modularity/commit/3c8899964eff6f1f7c3ff600dcb61241a9e274a4
- :art: add noUpperCase and noBold props to Title.vue by @OoBook in https://github.com/unusualify/modularity/commit/b8f465ab417f763b2c76a8f3cdd1d58d847bf2e0
- :art: convert RadioGroup into a component having only radio buttons by @OoBook in https://github.com/unusualify/modularity/commit/469a306643d396de46f166fc523c10c1f4c0b729
- add __dot and __wildcard_change helpers by @OoBook in https://github.com/unusualify/modularity/commit/d969164470cb61aac3f4589b031cd7b183527e54
- :art: upgrade formatSet and formatFilter events for non-array model by @OoBook in https://github.com/unusualify/modularity/commit/c673cd2c2319670bca5b0da5cfb7877832fbbca0
- add checklist-group to ManageForm by @OoBook in https://github.com/unusualify/modularity/commit/65c819b3b99a75e139b1b07e82bd6f42ab26fdc5
- :art: add default values for ext date andtime by @OoBook in https://github.com/unusualify/modularity/commit/56c0d08a5bc47ce06fedd662857dcdc1c8e03186
- add relative condition to getDirectoryPath by @OoBook in https://github.com/unusualify/modularity/commit/3204fd87b497042c7111f18e7f28e3e0765b0f1d
- :art: add getTitleField method on ModelHelpers by @OoBook in https://github.com/unusualify/modularity/commit/00659671cdae49833d2251f4c77fe0cd0e118d1f
- :art: use getTitleField of the model on nested case by @OoBook in https://github.com/unusualify/modularity/commit/77b765430fbd0c177cd8896ec7fe3291b23fbc4a
- add new input_types by @OoBook in https://github.com/unusualify/modularity/commit/a8c6e58c6da0f9001bbeee864248772f521de8e6
- update inputs of form_draft wrt input_types structure by @OoBook in https://github.com/unusualify/modularity/commit/d42793f1b6103fa5dd06e1f3375494e22375e5ca
- :art: add vue input component generator command by @OoBook in https://github.com/unusualify/modularity/commit/f4987f42ff45b4abf3b1eda1a6807d5a1a1d6776
- :art: add input hydrate class generator command by @OoBook in https://github.com/unusualify/modularity/commit/d8ac8e0e295a8e5ffde694eae376776b00f69be9
- :art: add necessary input hydrate classes by @OoBook in https://github.com/unusualify/modularity/commit/a1e2a64829743e5bb6a407d5c8403d50c8160cbe
- :art: add VSheetRounded alias into vuetify by @OoBook in https://github.com/unusualify/modularity/commit/63b66a5d2715e48629e3d3b7b4536960daaee13e
- :art: add snakeToHeadline helper by @OoBook in https://github.com/unusualify/modularity/commit/161fc594733ec61cdcf184fa802df87f6b6f13e1
- :art: upgrade tokenizePath helper by @OoBook in https://github.com/unusualify/modularity/commit/90c69e81039462b5d49b069eb5061eaec1ff3ada
- :art: add prependSchema ext event by @OoBook in https://github.com/unusualify/modularity/commit/fc28754d7ee74840ab11685ebdb8c9c8e0dd7e34
- :sparkles: pay action, customFormModal added && paymentServiceComponent fixed by @gunesbizim in https://github.com/unusualify/modularity/commit/e0bb05d7bb4f987decc594caf9b63dcafa24c7eb
- :sparkles: price controller, migration for is_payable field && payment service seeder added by @gunesbizim in https://github.com/unusualify/modularity/commit/ed02ebe06dfbb5d9d0792559b9b0df5e84ef3bd3
- :sparkles: default payment services added by @gunesbizim in https://github.com/unusualify/modularity/commit/b51ac66e3305f80ae822b30ba3691e25a44eaa88
- :sparkles: paymentServices and required flow by @gunesbizim in https://github.com/unusualify/modularity/commit/c52acd3c0d70fb55f06ed420f021c2a97b996ef0
- :sparkles: a helper function for removing query params by @gunesbizim in https://github.com/unusualify/modularity/commit/e91328c29194c1623e20f9b4f723095231d14982
- :sparkles: payment service and its components revised for the flow by @gunesbizim in https://github.com/unusualify/modularity/commit/c97bd7d60d820ea3a8fee49c3b7b3146b25258b6
- :sparkles: customModal revised for more general use by @gunesbizim in https://github.com/unusualify/modularity/commit/d0917b155f71a501dd486194257249e53aab6aad
- :sparkles: paypal first response moved into package by @gunesbizim in https://github.com/unusualify/modularity/commit/1f48834de5c6eba21088f321d82d360113257e1e
- :adhesive_bandage: add disabled props to Checklist & RadioGroup by @OoBook in https://github.com/unusualify/modularity/commit/576d242675932239a2b5f88b33282291d081ae7a
- :sparkles: add dialog alert first version by @OoBook in https://github.com/unusualify/modularity/commit/97b453d50dfb33dcd6782c18f00d66cf26c092ba
- :sparkles: add dialog alert first version by @OoBook in https://github.com/unusualify/modularity/commit/b69929b61a40371f00ab4d52a1f7599d5d9f214f
- :art: add formatPrependSchema event by @OoBook in https://github.com/unusualify/modularity/commit/f0113cda179bffe30bf6cbc6c67b34a6311cabcb
- :art: add alpha version of completing of stepperForm by @OoBook in https://github.com/unusualify/modularity/commit/3c0933e2b2150bcf34dd8044776a8e763b3b067b
- :art: add moduleFrontRoutes macro to Route by @OoBook in https://github.com/unusualify/modularity/commit/bbe936af4671f5d5467ec083f18f251b29385cbc
- upgrade routing on provider by @OoBook in https://github.com/unusualify/modularity/commit/8389f64925d1324f2abcf8ca8e04bf30e6a10d09
- :art: add replace_curly_braces helper to change model bindings of endpoints by @OoBook in https://github.com/unusualify/modularity/commit/7bc55ee50092367c74bc5919e468cfc33942a3d3
- :art: convert  route front controller extends BaseController on stub file by @OoBook in https://github.com/unusualify/modularity/commit/513fa72bd29d3755fc9ccf9c06b30c276469209e
- :art: add getColums to get column names of db table by @OoBook in https://github.com/unusualify/modularity/commit/202efcde3907ba06a57e875bac169dac92062a11
- :art: add ManagePrevious trait to track previous route and structuring by it by @OoBook in https://github.com/unusualify/modularity/commit/801ab53e5c3d75cd639b0911765b98fb5fa87cf3
- :art: add array handling for 'ext' key by @OoBook in https://github.com/unusualify/modularity/commit/d14f1f1c2fb421959905ac9586d4323af70b3577
- :art: add uuid suffix on headers by @OoBook in https://github.com/unusualify/modularity/commit/d2f65280fc3d5e203e4f7db2bbd9b1853220dfa0
- :art: add ManagePrevious trait to BaseController by @OoBook in https://github.com/unusualify/modularity/commit/21c30ce1663e2145ffeb55f0d5048692c2c67432
- :adhesive_bandage: add parentName to group schema inputs by @OoBook in https://github.com/unusualify/modularity/commit/0ca0d1963e7ef9247f67ec22d9f13452b0fbe3be
- add hasManyRelations into getFormFields method of RelationTrait by @OoBook in https://github.com/unusualify/modularity/commit/8c9bbee47e040317474b375cf9417610c1fd8d4b
- :sparkles: icon made dynamic by @gunesbizim in https://github.com/unusualify/modularity/commit/0f4c652d728b068da7970cf53cea7e9c53a8661a
- :sparkles: __removeQueryParams functionality moved to hook file by @gunesbizim in https://github.com/unusualify/modularity/commit/86eefa4548aa6bb5255e442a90efa53c366ca7fb
- :sparkles: __removeQueryParams functionality moved to hooks by @gunesbizim in https://github.com/unusualify/modularity/commit/cea2b7ba14bac700855afd8c9053e5e61d8319f8
- :sparkles: reverseDot method added to init.js by @gunesbizim in https://github.com/unusualify/modularity/commit/1c32ea06b595c2102f667e6fafebf36e9185f988
- :art: add getCloneSourceFields method to modelMakeCommand for hasCloneTrait by @OoBook in https://github.com/unusualify/modularity/commit/d02e4e79c8add59022af38f8c1c92003ead9aa98
- :art: add array_except helper by @OoBook in https://github.com/unusualify/modularity/commit/1a88c817b9462b8c2b20765815164b67d6067ccc
- :art: add new method string generator methods by @OoBook in https://github.com/unusualify/modularity/commit/54411c234ab879af17784fda42b51fa78b30de89
- :sparkles: svg icon component by @gunesbizim in https://github.com/unusualify/modularity/commit/137d99b72cd9c2335c4ebe092ed57b3f1660c377
- :sparkles: nested dynamic blocks for recursive stuff component by @gunesbizim in https://github.com/unusualify/modularity/commit/dd88a9d606112de9697bdc347a56febe062e164d
- :sparkles: new authentication pages by @gunesbizim in https://github.com/unusualify/modularity/commit/3eed15f13e13d214cfe2f36f3b191b1ee01e4103
- :art: add methods for snapshot trait by @OoBook in https://github.com/unusualify/modularity/commit/de4a5e11c866c29bd6ae7df6431d7434cd3cd91e
- add HasUuid feature by @OoBook in https://github.com/unusualify/modularity/commit/f8c095ea3180d4e3d2a5a9331fcedab0a19f7381
- add MethodTransformers trait by @OoBook in https://github.com/unusualify/modularity/commit/71afc7228139f1ee90b12bf3c55de3c9414deb8c
- :art: add title or name check for array values for cells preview by @OoBook in https://github.com/unusualify/modularity/commit/80a0689bd404da6762c5399bbdff027a956858e4
- :art: add new addible traits to traits config by @OoBook in https://github.com/unusualify/modularity/commit/517b345b927c086c85957188c2d34452e96abc2b

### :wrench: Bug Fixes

- :bug: fix some code syntax related bugs by @ilkerciblak in https://github.com/unusualify/modularity/commit/84be6d5108c690cfaa2eb910c2fd50895d67c984
- :bug: fix state management issue on new file uploads and new form processes by @ilkerciblak in https://github.com/unusualify/modularity/commit/31de48634b8da210b19b01e0e19ddcbd78a16c91
- remove dd for translation by @OoBook in https://github.com/unusualify/modularity/commit/24caae8e2b7641400ec6c85b4816a363d4020264
- :bug: remove unnecessary base_prefix field from config by @OoBook in https://github.com/unusualify/modularity/commit/3fc4a3a357f57c4ae3d878a190a80fb843e26171
- :art: add override model fillable by @OoBook in https://github.com/unusualify/modularity/commit/d57a94386d40114f74a8028769ed27a0fd7366a7
- add lang files of system modules and fix some controller sources by @OoBook in https://github.com/unusualify/modularity/commit/b4474af08f8eb10a139e10ad6801cb4cc5ec7a0c
- :bug: directory path for system modules by @gunesbizim in https://github.com/unusualify/modularity/commit/dd4c86257bdc14f5c3be9b153aa3712b8fa94cae
- :bug: price calculation error where related class doesn't have HasPriceable trait by @gunesbizim in https://github.com/unusualify/modularity/commit/a338dbc8e9a8644ad0210e2078be04eb0f2fa83f
- :bug: where PaymentTrait is triggered eventhough there is no relation by @gunesbizim in https://github.com/unusualify/modularity/commit/3bb621d5c545c760f5acea22dfa2c9fe45d07488
- :ambulance: add default fields as an array for empty fields and schema by @OoBook in https://github.com/unusualify/modularity/commit/3119aa2d0a63cdb188450c2cca6dd9a15f857414
- :bug: fix form.validModel issue on useTable by @OoBook in https://github.com/unusualify/modularity/commit/cceab5a6c9b839008ec88161afcfdbb22abba02c
- :bug: add BelongsToMany condition into addWiths structure by @OoBook in https://github.com/unusualify/modularity/commit/004ebe59a9f27200346326ad54a5c80ef11e0b58
- :bug: fix searching filter for translation columns by @OoBook in https://github.com/unusualify/modularity/commit/10c3008045509d082d51b9158f4b71ea5308f9d2
- :bug: fix filepond convention issues by @OoBook in https://github.com/unusualify/modularity/commit/4d04f10a608796964e74b35f99c684ae89daf93b
- :bug: add api.languages endpoint to auth layout by @OoBook in https://github.com/unusualify/modularity/commit/19cbab153d6f5dc3ba58537dc77cdc0dc842023f
- :bug: fix test-route-morphTo by putting morphTo into schema flag instead of relationships by @OoBook in https://github.com/unusualify/modularity/commit/8ef04d6b829a972f9b386a303d067ba1a9871ede
- :ambulance: fix advancedFilter condition of related button by @OoBook in https://github.com/unusualify/modularity/commit/2f46fd77291b5053660277f03c5b9416bc0f45fb
- :bug: rebuild flattenArray on model structure changed by @OoBook in https://github.com/unusualify/modularity/commit/b56b8e8a928d2e515417d4d4a895d10bb8f6bd72
- :bug: implement default table names for something by @ilkerciblak in https://github.com/unusualify/modularity/commit/0210d68e8fd4699ad749d2dd4046be55077784a0
- :bug: add modelValue to ue-tabs v-model by @OoBook in https://github.com/unusualify/modularity/commit/11c6f8b78a6e976d98887573cb3bd0ca417243cb
- :bug: fix object editing remove-update file issue by @ilkerciblak in https://github.com/unusualify/modularity/commit/1aac3a2de9f2ffabdfd5e5bb0cd8ed389e58ff50
- :bug: share inputEvent coming from wrap and group form-base by @OoBook in https://github.com/unusualify/modularity/commit/2b9cc83a6fa75ea115318747c64b65777d94d2f9
- :art: recall invokeRuleGenerator if input has schema object by @OoBook in https://github.com/unusualify/modularity/commit/5ca19f5f38c658c92eb59ec0770f2c8f22a32c8a
- :bug: fix group type cases in getFormData by @OoBook in https://github.com/unusualify/modularity/commit/caea306358a4343b94bcf43e8930f0f15be8e920
- :bug: remove the key from previewModel if it is removed from the models by @OoBook in https://github.com/unusualify/modularity/commit/6303a3b47f3c6d138db396bed3336808b45274d7
- :art: reconfigure model and inputSchema if model structure changes by @OoBook in https://github.com/unusualify/modularity/commit/a9dbf32847f0c1407409766981f00d56f18d6b79
- :bug: fix wrap and group hydrates by @OoBook in https://github.com/unusualify/modularity/commit/231d532c611c534a72856615ac6dbca846988316
- :bug: add module getDirectoryPath into MigrateCommand by @OoBook in https://github.com/unusualify/modularity/commit/0c172873f130d268704f1b807fa6f767cfacce6c
- add payable package into composer by @OoBook in https://github.com/unusualify/modularity/commit/98473a37704d55f31d30e7fe5465cffc22591115
- :bug: payment trait relation by @gunesbizim in https://github.com/unusualify/modularity/commit/ba04bfa80c66269a1b7468a3904daf4ea9829f1d
- :bug: fix spreading unless morphTo's schema exists by @OoBook in https://github.com/unusualify/modularity/commit/7960ab4fdd47548330036ab50ad2dbb4a2bebea3
- :bug: fix fields suffixed _timestamp and _relation on sorting by @OoBook in https://github.com/unusualify/modularity/commit/522b90c282bf9d82a78df777e500141b8f9a5596
- :bug: fix fields suffixed _timestamp and _relation on sorting by @OoBook in https://github.com/unusualify/modularity/commit/3b9b72585a3a2fc60238a5d2eb25564cca6dd94a
- :bug: fix getting advancedFilters with filtering by @OoBook in https://github.com/unusualify/modularity/commit/6252713df8d520f0a5cadbcb9ac85c7ecee8ffbd
- :bug: fix item's column if $column variable is array by @OoBook in https://github.com/unusualify/modularity/commit/d29e7e1913a9ed98d17bf17b409f694d594b480b
- :bug: fix caret goes start point on each inputting by @OoBook in https://github.com/unusualify/modularity/commit/a3e6e1ab81beb16818b6cce010c0bb997f62741f
- :bug: fix recursive searching if modelValue is null by @OoBook in https://github.com/unusualify/modularity/commit/dd736904cc7434229391686f2bbc07b07f138a4d
- :bug: add preg_quote for action matching by @OoBook in https://github.com/unusualify/modularity/commit/d997dd7f61331ddf2b9776866d99147c231f0a46
- :art: add default handling in RadioGroupHydrate by @OoBook in https://github.com/unusualify/modularity/commit/204df8391f284c442dacbf1c277dd8120dc4983e
- :art: change label-idle translation in FilepondHydrate by @OoBook in https://github.com/unusualify/modularity/commit/ed71b655326d6274a39d06f9289c8280eeb46ad7
- :bug: fix key getter if next keys chain is key in related object by @OoBook in https://github.com/unusualify/modularity/commit/f0a8da45f36ffb35dce217b2b3344c809fe972df
- :bug: add name prefix if it is wrap in a group input by @OoBook in https://github.com/unusualify/modularity/commit/583287853d692dd4c785564bee1808b042947779
- :adhesive_bandage: fix setSchemaError while handling response errors by @OoBook in https://github.com/unusualify/modularity/commit/bcb09f1c9a2712d2e7dabb67d795b9a190af0314
- :adhesive_bandage: fix wildcard_change pattern with '?' by @OoBook in https://github.com/unusualify/modularity/commit/c8c4c3c80ce1685249a77e6636766e044a6cbfb2
- :adhesive_bandage: fix error handling on error response from api by @OoBook in https://github.com/unusualify/modularity/commit/ee23825759839f92d6c3002a4d66b30261445238
- :bug: fix group type on changes by @OoBook in https://github.com/unusualify/modularity/commit/e2216dee051ba0904d8d4a82f9c4f59c60440389
- :adhesive_bandage: fix state handling on changing schema and model structure by @OoBook in https://github.com/unusualify/modularity/commit/617ff03845a3ff1796e42e9d4cf9d218e6a4ca0f
- :adhesive_bandage: fix refresh command with unusual rollback and migrate commands by @OoBook in https://github.com/unusualify/modularity/commit/d5323b06232f7fcf1c155db5de8daf72b036a957
- :adhesive_bandage: fix guest user cases about middleware, trait and navigation by @OoBook in https://github.com/unusualify/modularity/commit/440a5f4d93e128c0510d7f59bebdc76fda2a3103
- :adhesive_bandage: fix guest user cases about middleware, trait and navigation by @OoBook in https://github.com/unusualify/modularity/commit/0959ad8fd73dafdd0a775bc2b549bd1d98b3d201
- :bug: fix filepond on multiple input name and preview route by @OoBook in https://github.com/unusualify/modularity/commit/8204900aec35d11cfa4bf6fec8d4846f6e055026
- :adhesive_bandage: fix default value of selectable inputs if not multiple by @OoBook in https://github.com/unusualify/modularity/commit/bf48e005c4c56d414db4ebaf67ac063598674db8
- :adhesive_bandage: fix itemTitle of items if not exists by @OoBook in https://github.com/unusualify/modularity/commit/0f746097d17e7b101d8f15a079075b34cedf7264
- :adhesive_bandage: add itemValue and itemTitle defaults into RepeaterHydrate by @OoBook in https://github.com/unusualify/modularity/commit/661fb5710b55abfa64382d4624a7d67ea486ad73
- :bug: fix morph id colums as uuidMorphs by @OoBook in https://github.com/unusualify/modularity/commit/cfa973de2cb64f294a7566d6c9478e0ef1861982
- :adhesive_bandage: fix guest user cases about middleware, trait and navigation by @OoBook in https://github.com/unusualify/modularity/commit/813584f603a7bbca0cf8df7e014a457b101f3e4d
- :bug: add morphTo fields to fillable generator by @OoBook in https://github.com/unusualify/modularity/commit/1253a721cb21f2240c178deb09f20dc198a025b3
- :bug: add a check to handle third-part traits as featured-trait by @OoBook in https://github.com/unusualify/modularity/commit/c77f0ff129a0a038355988757577e72f6cc36471
- move the repositoryClass method outside from try-catch by @OoBook in https://github.com/unusualify/modularity/commit/779705e7cb9301f7dbc7d20142a7f6fedfe1b08d
- :bug: where object price is present but there is no price for the object by @gunesbizim in https://github.com/unusualify/modularity/commit/08adab8651f72fd92e217bafa4e25a8f158e32ac
- :bug: method that causes unnecesarry initialize removed by @gunesbizim in https://github.com/unusualify/modularity/commit/8bfa6775bdefea4343f24bbcc056b7645a90ebd1
- :bug: where module is undefined on ManageForm in some cases by @gunesbizim in https://github.com/unusualify/modularity/commit/83b13193ca435658928ea871872c68a3baf82a2a
- change filepond preview route name on mediableFormat by @OoBook in https://github.com/unusualify/modularity/commit/c99924cc0f5abce62fb5a6124c227bd594862c19
- organize composer file with php requirements and test requirements by @OoBook in https://github.com/unusualify/modularity/commit/bc26495b73884178519b185f91d8c4d1dcd8d310

### :recycle: Refactors

- :recycle: add get_file_string helper and it's usages by @OoBook in https://github.com/unusualify/modularity/commit/d85c2058b003c58cf9dfaa2a45ad263edac1acc5
- :recycle: add if condition to array helpers and refactor usages by @OoBook in https://github.com/unusualify/modularity/commit/b0209d2fbda6fd26bbb5279a7ef4567b234db0e1
- :art: add only route config to config array for preventing to remove namespaces and comments by @OoBook in https://github.com/unusualify/modularity/commit/3a5ea67a19f15c4d28d7056d79a2ae4c2f88081e
- :recycle: remove double quotes from parameter of setModule by @OoBook in https://github.com/unusualify/modularity/commit/dd1ff51acd89a3c5a847d3c7adb623f98dd601e8
- add response to callback function on get method by @OoBook in https://github.com/unusualify/modularity/commit/b752e006c86116885408e1f1be2128009a0c0b86
- :art: reorder defining routes for fixing slug structures by @OoBook in https://github.com/unusualify/modularity/commit/3329da4e43a851b431c3f8827465bbbec6a7167e
- :recycle: refactor bottom slot and prettify file by @OoBook in https://github.com/unusualify/modularity/commit/9711655167ffa41e2293125b715f62bc1a5291c8
- :recycle: remove embeddedForm on default by @OoBook in https://github.com/unusualify/modularity/commit/e1f30f590d6890c1405246ac5af227419b7332ae
- :recycle: merge namespaces into one variable by @OoBook in https://github.com/unusualify/modularity/commit/46cdca0ca5aaef5667dedf5c126af552e444461f
- :recycle: refactor index.blade structure by @OoBook in https://github.com/unusualify/modularity/commit/492ba2196f3e2ee5b7d817a46e30552716e3a8ed
- :recycle: refactor getFormData method by @OoBook in https://github.com/unusualify/modularity/commit/9eecd5886e0e149b7de8b70c15e3d8e7b7eec8b3
- :recycle: change radio-group to checklist-group by @OoBook in https://github.com/unusualify/modularity/commit/3e66b15b20a1895552fdb477d3e9fa405b338976
- :recycle: use ue-tabs with windows slot in TabGroup.vue by @OoBook in https://github.com/unusualify/modularity/commit/ddf691076fc831e300e7d9f27a76e5a534441870
- :recycle: remove Object.assign and put todo note draggableItem by @OoBook in https://github.com/unusualify/modularity/commit/d5a4f258aef187eb9c22eac012633257d9b16b71
- :recycle: convert custom-input-... pattern to input-... pattern by @OoBook in https://github.com/unusualify/modularity/commit/58ad62eb85711d04ae38d5407d6f5834408db9b1
- add connector to permissions input of role route by @OoBook in https://github.com/unusualify/modularity/commit/871821ae287256250da2a4ce5c77a6399783952a
- :recycle: modals to come from unified modal object and table ui fixes by @gunesbizim in https://github.com/unusualify/modularity/commit/2b37ad8b69fd03fdafc722d1af29a7f4e5d4fb85
- :recycle: convert hydrate input switch cases into hydrate class by @OoBook in https://github.com/unusualify/modularity/commit/9dca404d42207888e43f91e6ebe69ca2034c2320
- :art: change some translation fields for grouping by @OoBook in https://github.com/unusualify/modularity/commit/7284216e7ce4858660fe4cc4c294dc4b79b0a533
- :recycle: PaymentService hydration changed by @gunesbizim in https://github.com/unusualify/modularity/commit/032ed262286e2be3980c112feaf3255c3a85ccca
- :fire: inside of paymentController cleaned by @gunesbizim in https://github.com/unusualify/modularity/commit/314491173beca8c96c91616223501e4dbd5f9249
- :recycle: update loading-text translation by @OoBook in https://github.com/unusualify/modularity/commit/366a45e084fb218689ea0038245b143b9fc43e51
- :recycle: clone formatter array by @OoBook in https://github.com/unusualify/modularity/commit/ee320a1027a0aca92b56c0ac7767862e2aa662b8
- :recycle: refactor chunkInputs, flattenGroupSchema by @OoBook in https://github.com/unusualify/modularity/commit/8aaaafc06d809e63382258a92bbe58e563688892
- add ternary in case empty by @OoBook in https://github.com/unusualify/modularity/commit/50a214f37023643a4831f9880be95df5b91350e9
- :recycle: payment parameter removed from datatable.js by @gunesbizim in https://github.com/unusualify/modularity/commit/b3b3ffd4fbfd411cbfa8e89adbb48819384b6480
- :recycle: sidebar logo replaced with new svg icon component by @gunesbizim in https://github.com/unusualify/modularity/commit/4f6b2fd881cdc4e06825be39968b68577c78d541
- :recycle: deprecated xlink:href replaced with href by @gunesbizim in https://github.com/unusualify/modularity/commit/1abe1e8f13847178436b327b53047a6e899256d1
- :recycle: payment service pay method call changed by @gunesbizim in https://github.com/unusualify/modularity/commit/0b6c8f90293c25e4bfd7a362dc543436fa64923a
- move src/Database to database dir by @OoBook in https://github.com/unusualify/modularity/commit/4d0f26afa3bbd6e937bb7832e85486c25834d438
- change Priceable namespace as Oobook by @OoBook in https://github.com/unusualify/modularity/commit/024f11f2b423cc7282137fad3c14a0d99d26529b
- :recycle: remove methods of ManageEloquent from ModelHelpers by @OoBook in https://github.com/unusualify/modularity/commit/59baa1003b151a35344028ae97a530c81a28f83b
- :recycle: move methods of MethodTransformers from repository by @OoBook in https://github.com/unusualify/modularity/commit/8654f03672a52d41b3c71dfe308f734841f0cde6
- add $this->app instead of app helper in setModule by @OoBook in https://github.com/unusualify/modularity/commit/bf6127008dc5d1d77b95fb276df99dc2f28bb828
- :recycle: lang files updated by @gunesbizim in https://github.com/unusualify/modularity/commit/ae96e12d5a3af82e349a60eb32ac1cd915540215
- add realpath with relative path for scan paths by @OoBook in https://github.com/unusualify/modularity/commit/fde6e7a52b5c533e62212a39222db33b62b0dcb3

### :memo: Documentation

- :memo: payment trait documentation update by @gunesbizim in https://github.com/unusualify/modularity/commit/7a77a50fb0b6046291ae1ea10ebd9880f70a50f4
- :memo: add brief documentation about filepond usage and component by @ilkerciblak in https://github.com/unusualify/modularity/commit/a786fee15650e17dcd435ef9f9de0095b0a8ed2e
- :memo: payment documentation update by @gunesbizim in https://github.com/unusualify/modularity/commit/899a8d4197adb62369b9edf37c612682e4d5e984
- :memo: payment documentation grammer and typo fixes by @gunesbizim in https://github.com/unusualify/modularity/commit/60c9c9169af309ce7796c8364e4f5942bdc7e2e8
- :memo: payment documentation grammer and typo fixes by @gunesbizim in https://github.com/unusualify/modularity/commit/51fcf5b6e4d73204aa11559227328526161fcd9d
- :memo: add feature related documentation about filepond and file storage by @ilkerciblak in https://github.com/unusualify/modularity/commit/886bdef6899f23557f2ac682946de64c8f4bca44
- add checklist-group input by @OoBook in https://github.com/unusualify/modularity/commit/d121469db52e7e55e77d88e24e0ba8e6ef96e3d5
- add radio-group input by @OoBook in https://github.com/unusualify/modularity/commit/56874a8c8e2bbd578b276de033d8dbe5be7fbf69
- change namespace of priceable on payment doc by @OoBook in https://github.com/unusualify/modularity/commit/f8c14f1e499b12088735873ed44df26c133aef82

### Styling

- :lipstick: refactor filling main body on master, and Table.vue by @OoBook in https://github.com/unusualify/modularity/commit/c53b3a0ae9ba2c037b54627437ba4d819ce9baa6
- fix psr-4 issues on advancedFilters by @OoBook in https://github.com/unusualify/modularity/commit/abfdb3c91cf032ea0d564c3ab31b2b97ccd44e7b
- fix space issue of route-controller.stub by @OoBook in https://github.com/unusualify/modularity/commit/fc6f0b413989147244c083114c8f5efbafd4db0d
- :lipstick: success color update by @gunesbizim in https://github.com/unusualify/modularity/commit/6ed227afe6fa8045335de2cb2d06562878df5c39
- :lipstick: change Logout buttons by @OoBook in https://github.com/unusualify/modularity/commit/659def38d1f9daa746d6c95a6128625a0e51137d
- remove resposne log on SAVE_FORM by @OoBook in https://github.com/unusualify/modularity/commit/76d83ccefcafdaf67b9c5febc3120ecc091ef364
- fix psr-4 standards of PanelController by @OoBook in https://github.com/unusualify/modularity/commit/f24dd00ec9c91c5bcbba322cc424e326571adbdd
- comment unnecessities of RelationTrait by @OoBook in https://github.com/unusualify/modularity/commit/bd27c28cc6d03e9b8ca135a3018e0ec2b91094ef
- cleaning some files by @OoBook in https://github.com/unusualify/modularity/commit/c6ad3cc27370592987098d4cbf269faba8ae4a60
- clean LanguageMiddleware by @OoBook in https://github.com/unusualify/modularity/commit/131a5551d43604e480851fb40308e912354ac3a5

### :white_check_mark: Testing

- add RouteGeneratorTest draft by @OoBook in https://github.com/unusualify/modularity/commit/d5b69da1915abfdee7fa69878b1ca28740f2fc7c
- :white_check_mark: add test drafts of FileActivator and RouteGenerator by @OoBook in https://github.com/unusualify/modularity/commit/9c9645d69dd642f1c72d025550f37cb0f20d7e51

### :green_heart: Workflow

- add vite test action for vue sources by @OoBook in https://github.com/unusualify/modularity/commit/5bf59ec76e4af0b1308847815e6ca01b269d1d3e
- :green_heart: add laravel matrix tests by @OoBook in https://github.com/unusualify/modularity/commit/9baa3cb76f0e845c73ed0d993aaea48099cada35
- change on push event as only for dev branch by @OoBook in https://github.com/unusualify/modularity/commit/104d40dea36c172583561f4a4d47709a67761cb8
- add releasing workflow by @OoBook in https://github.com/unusualify/modularity/commit/2cd4c9e0ef195e9a2c35b669e07b1068109ea32f
- add workflow_dispatch for manual release by @OoBook in https://github.com/unusualify/modularity/commit/4019b383e71cf20b94d7a957923416eac3bda1df
- :green_heart: add update changelog workflow by @OoBook in https://github.com/unusualify/modularity/commit/839d3e1ef4104ce1b20b5746bdd74661fee41b48

### :beers: Other Stuff

- :art: introduce some more property definition on new file component by @ilkerciblak in https://github.com/unusualify/modularity/commit/c52b027cb58363585a3ce5994fca7318853e4a1d
- :wrench: restructure lang files to publish and merge by @OoBook in https://github.com/unusualify/modularity/commit/3c2eb1a22dc83421d91087b193416c47c389cec9
- :art: improve code structure of the filepond component by @ilkerciblak in https://github.com/unusualify/modularity/commit/4d43ad085c749eaceebf5e69808182873202984c
- :art: introduce more properties in manageform process by @ilkerciblak in https://github.com/unusualify/modularity/commit/800f238382246116fcbcc1ebc5e2167c5579f723
- Merge remote-tracking branch 'origin/feature/payment-trait' into dev
- Merge remote-tracking branch 'origin/feature/payment-trait' into dev
- Merge remote-tracking branch 'origin/feature/payment-trait' into dev
- add draft structure for draggable Table by @OoBook in https://github.com/unusualify/modularity/commit/7adaeed6b1376dc8d00c3e3a184948cadef4cbf3
- rename draggable on Repeater by @OoBook in https://github.com/unusualify/modularity/commit/e2a7a168c95068e640d177d7941b49a1cf2e64fa
- add faq and all keys by @OoBook in https://github.com/unusualify/modularity/commit/189e692b04573ad6c7e5455f95f5d812ae0089c6
- Merge remote-tracking branch 'origin/feature/payment-trait' into dev
- Merge remote-tracking branch 'origin/feature/filepond-implementation' into dev
- Merge remote-tracking branch 'origin/docs/filepond-documentation' into dev
- remove NodeTrait because it does not exists on composer by @OoBook in https://github.com/unusualify/modularity/commit/f66a32abe7b15d6427c054c2c87478ccdde6d173
- style(): fix psr-4 issues on traits
- Merge remote-tracking branch 'origin/dev' into feature/draggable-table-row
- :zap: add backend functionality of the draggable feature by @ilkerciblak in https://github.com/unusualify/modularity/commit/50bba0a128173f5de4705d747df582664b396a02
- change language and timezone input wrt input type by @OoBook in https://github.com/unusualify/modularity/commit/e445d6ad869bebed2ff1797dc134cbd7eae65985
- Merge remote-tracking branch 'origin/feature/draggable-table-row' into dev
- Merge remote-tracking branch 'origin/feature/CreditCard' into dev
- Merge remote-tracking branch 'origin/bugfix/filepond-remove-bug' into dev
- add payable package in composer by @OoBook in https://github.com/unusualify/modularity/commit/c6bbf298f2b6d7029eb5a3e547296f3057601ea9
- by @OoBook in https://github.com/unusualify/modularity/commit/6fefa596f2469e0d3c22372979b72c174fb3683c
- Merge remote-tracking branch 'origin/dev' into feature/payment-currency
- Merge remote-tracking branch 'origin/dev' into feature/payment-currency
- :fire: remove alert mixin by @OoBook in https://github.com/unusualify/modularity/commit/058fa5eff00ebfd8ebdd5b458722363f7393405c
- unset lang-publish by @OoBook in https://github.com/unusualify/modularity/commit/3bdb1418418e7664630473226b5ef8101517235e
- add item to formData as parameter by @OoBook in https://github.com/unusualify/modularity/commit/e693a085d8cd2ebd1275bba1c0e3d6736039f9f2
- add hidden prop to developer commands by @OoBook in https://github.com/unusualify/modularity/commit/67f877195643449385ea3264cef325f8b1310d97
- :coffin: remove dead-catty table names by @OoBook in https://github.com/unusualify/modularity/commit/9fd41f248ef395962106aeafd91e1b3e775f4446
- separate packages from oobook by @OoBook in https://github.com/unusualify/modularity/commit/82fa7109b72158ca9dba1b2b5b0ac52330572c10
- Merge remote-tracking branch 'origin/refactor/make-table-modals-object' into dev
- Merge remote-tracking branch 'origin/feature/payment-currency' into dev
- Merge remote-tracking branch 'origin/feature/svg-icon' into dev
- Merge remote-tracking branch 'origin/feature/recursive-stuff' into dev
- Merge remote-tracking branch 'origin/bugfix/payment-trait' into dev
- Merge remote-tracking branch 'origin/feature/authentication-pages' into dev
- Merge remote-tracking branch 'origin/feature/authentication-pages' into dev
- Merge remote-tracking branch 'origin/test/draft-tests' into dev
- Merge remote-tracking branch 'origin/test/draft-tests' into dev
- add CHANGELOG file by @OoBook in https://github.com/unusualify/modularity/commit/b196b19b3e434c865e2c906721ce67da51d0bbb5

## v0.0.0 -

- Initial Tag
