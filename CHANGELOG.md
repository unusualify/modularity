# Changelog

All notable changes to `modularous` will be documented in this file

## v12.4.0 - 2026-07-10

### :rocket: Features

- implement URL-keyed stale cache middleware and enhance presentation item caching logic by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/da3bf010d7f8b8f71e0f0f7707504f725efa4fc5
- add commands for purging and warming presentation item caches with options for module, route, locale, and dry run by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6b57a0e0ee1bf9d2139982f8ee83489bb8211ba5

### :wrench: Bug Fixes

- update CacheRevalidateController namespace from 'Api' to 'API' for consistency across the application by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9aca5626bb3aceb7e4446197ca922d13cfa4b7a1

### :zap: Performance

- enhance synchronization logic to support linked records and improve handling of missing remote records by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/ced77f93dc30b6d8b5712158307ffbb923531232

### :recycle: Refactors

- update ResourceCacheActionsTrait import and improve panel route name prefix logic by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6df9f0aff8abdc89f04df7fd480912e77c83f009

### :memo: Documentation

- expand documentation for public presentation item caching, including configuration, SWR, and webhook revalidation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/655cf3334bf591f0b2f2815fbd5d06accc37283c

### :lipstick: Styling

- lint coding styles for v12.4.0 by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e2b36edad76b7beda1945f9a3314afb162c488c8

### :white_check_mark: Testing

- add comprehensive tests for cache purging, warming, and revalidation functionality, including command line interactions and webhook handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/1242757a2ceab7524c60f698422de8ae339ddc6f
- add extensive unit tests for various components including Module, RouteGenerator, and new HTTP middleware, enhancing coverage and functionality validation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/b5d727595532d80f6d37f84672b04356d4dc61bb
- rebind modularous activator in TestCase and TestModulesCase to ensure correct module activation during tests by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/423d05754c8a4b2ebb88f07a657a79242a3df066
- update StaleFileCacheTest to use class basename for dynamic path generation, improving maintainability by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6339011364a25108a8001bafa726686939025020

### :green_heart: Workflow

- remove deprecated branches from CI configurations and enhance coverage reporting in 12.x workflow by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7992f9e8c3dc3ee04c0224a2b3d13ef50459c567

### :beers: Other Stuff

- update PHPUnit configuration for coverage reporting and add coverage driver script by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/bc7d47602c1c39bd6980f09b3a17626465b5b75c

## v0.58.9 - 2026-07-07

### :wrench: Bug Fixes

- update event handling for model saves by @celikerde in https://github.com/unusualify/modularous/commit/efb8f4cd916f3093cf77a5181755390270589833
- remove debug statements and streamline update logic by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/c7bf15c83a56039f73a3c194be91862d285a8c0b

## v12.3.0 - 2026-07-03

### :rocket: Features

- enhance caching functionality with new presentation item cache and logging features; add cache management messages in English and Turkish by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f2c4c446bb14a823e83cf34df759130f4a9b2d58
- add caching methods and remote API source checks to enhance module functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/82a097e9386c7d58cdcaf6238d50b58f4b78e61e
- streamline route registration by consolidating additional route logic for admin and API types by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/da6d2887530f5148ebb021701d98c13da819b284
- enhance route handling by adding console context support and expanding custom route options for modules by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/fee7e82a08c3689df9ae2d63f3e641db3dafd390
- implement presentation item cache warmup functionality and enhance cache key generation with error handling for missing IDs by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/26845eb8cd0ba5e88d347ff06df29f1a211487a5
- add support for presentation item cache clearing and warming in CacheClearCommand and CacheWarmCommand by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/c88cc032887188e98f5af917e4fbb98ef9f2a600

### :wrench: Bug Fixes

- update resource cache check to utilize ModularousCache for improved cache action validation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/bfc7cd876b736a14cee34b416b41584785db4791

### :recycle: Refactors

- update import path for HasPresenter trait to new namespace structure by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7b1b416548b01187b5cbac39a5e853edd3a33d47
- standardize parameter annotations and improve cache handling methods across various classes by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/19da7b50ef726b7a3177bdb413a2fca2791e78e7
- introduce ResourceCacheActionsTrait for enhanced resource cache management in repositories by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/bee07e2a2844ff2c11098e3ba5bbbf832652c41f

### :memo: Documentation

- add comprehensive documentation for module route cache, including configuration, cache types, invalidation, and console commands by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/3c38fcf880f9e604fa39608ca1cf27fa8e20cd5a

### :white_check_mark: Testing

- add tests for cache observer functionality and enhance cache configuration options by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/89566d451c4d1ee5aca9f5c5502b3d5d0de8ac0c
- add missing newline at the end of the migration file for test_module_items table by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/fb446e0006b2fb64a1b06ff1bcf8a1c2f88f809d

### :green_heart: Workflow

- update branch reference in GitHub Actions workflow to use the default branch instead of a hardcoded value by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/82b115f7efd887e627fe33d2e99810f7846d07b8

## v12.2.0 - 2026-06-27

### :rocket: Features

- add FlushLibraryUploadsCommand and OrphanUploadCleanup service for managing orphan upload folders by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e2d9ad1058d722d1da15106f8b794ecf5101a204
- implement pagination controls for media grid with next and previous navigation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7be53a3dd699bebc5a80be798daec44f3a74de13
- add FlushLibraryUploadsCommand and OrphanUploadCleanup service for managing orphan upload folders by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/b62975f0118cfeeb6b9b79734fbe115743759f47
- implement pagination controls for media grid with next and previous navigation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e43e1358f0342d5ea54c552424cf77082c52523f
- add method to ensure routes statuses file exists and update tests by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f3281052e50cef2f72dbbbf73ff91157b8136fab
- update command signatures to include module argument and pretend option for better migration control by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/c62162fa7f6c1cd47186199e23f0639a01332453
- enhance command signature with options for force removal and dry run, and add confirmation prompt in production by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/837299d06126654919d44070b04b2508cbdb5026
- implement draggable table functionality with enhanced item management and pagination controls by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/a009528bc7627e6a4ba675728e457ed5a418ceea
- implement payment calculation command and associated services for processing payments, including input validation and result handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/b3600e8cb660d08a4247d05ca45cdc1f8103c885
- enhance role validation logic to prevent non-superadmin users from assigning the superadmin role by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9f6768742722af2929ebeb624b6c35944a225595
- add EditorHydrate class for managing editor input schema and implement related Vue components for enhanced editing capabilities by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9962f231e5ba3898d79dd7648422e094d5052cc8
- enhance locale input rendering logic and visibility handling for heavy input types by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/3125b753c0242731aa5ab775213f8dd62711cad5
- implement caching and management commands for public URL registry, enhancing performance and cache handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/b96daada593a99996b2e7a92b57bf010fba91488
- refactor loading state management in table components and actions, replacing 'loading' with 'isTableBusy' for improved clarity and functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6fcc00ca44b32f87e165639c03c21bd4eb11b598
- add Remote API integration with configuration, caching, rate limiting, and console commands; create migration and documentation for remote API sources by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/dbda9169fb4aa80e396202c94a898818139f9d09
- enhance traitsMethods to support snake_case method names by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f28ee7965c6ada4a13d364d6ad4bcd28a9e3b89e
- integrate sass-embedded for modern compiler support and enhance Vite configuration with shared Sass options by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/1ae4d442b8364aeb451f5f39a2deb5cc132649a7

### :wrench: Bug Fixes

- correct tags mapping to ensure proper array formatting in mediableFormat method by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/81ae5798d1b1100c0170089e778b5c638906c965
- correct tags mapping to ensure proper array formatting in mediableFormat method by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/419a4fc1953ef65aa2e9527697b3dfb208643d21
- convert APIX to API folder on Cms/Http by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/caecd28d818ea5005e516f6519ddacbf2362f345
- update view namespace for favicons inclusion by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/61cba20ad93d64327381d98e811832aa73b1c062
- update view namespace for favicons inclusion by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/721877c17f92b314dddb6b3bebaa60b73369b076
- inject modalService with default null to prevent errors when not installed by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/53dfb2c398307640aed4907ca229b9360c45093e
- replace direct store import with useStore hook for improved reactivity by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/fbf7d725c6976fc60764a283457dc8f9eb1fdcd7
- change modelValue prop type from Object to Boolean and update default value to false by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9629763253ec70329fbf24cb99ad2ba327675172
- refine method name handling to ensure proper combination of snake_case method names with trait names by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7c290b6f3de0e29b65786c9cbf64eacb039e6acc

### :recycle: Refactors

- enhance media filtering UI with improved tag selection and mobile responsiveness by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/a45537eb72f46444ba2f0357099b57020c1004d4
- enhance media filtering UI with improved tag selection and mobile responsiveness by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5042c982cf63f1b0fef5a2a664491cfef5a06c08
- remove commented-out code and debug statements for cleaner implementation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/da86034c694c599fac78d32d4694ea56173bb441
- comment out unused view paths in stubs for clarity by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/58c4b570a536a641c5b053cb2b3d1e45e869d3a6
- remove commented-out debug statements for cleaner code by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/1c4627235382b460c6507cbfa80339be03561069
- update trait references to include full namespace paths and add repeater feature configuration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/34b55da79f1ce499663eaeca07fcee99c8937792
- improve repository retrieval logic and add error handling for missing repositories by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6b3d2f85c529cc1cb9299015f5357b464ce410ec
- enhance TestModulesCase and IsolatedTestModules for improved route management and fixture synchronization by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/91b73a228010f67a44305758ea49724c6b688064
- remove outdated component documentation for assignee details and chat message; add new documentation for filter, uploader, impersonate toolbar, logout modal, revolut checkout, alert, auth, blocks, board information plus, btn, collapsible, configurable card, copy text, currency number, and data iterators by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/00a06c9e788fb7df2e14b7b466a06e4a585f1fc5

### :memo: Documentation

- update project name from "Modularity" to "Modularous" in HTML files and adjust related documentation; remove outdated module creation assets and add new installation guide by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/67c6175b2572b8963d86469534118782de02f09d

### :lipstick: Styling

- lint coding styles for v12.2.0 by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/0c740aff90bb47f791d72c1c7002e046e13724a7

### :white_check_mark: Testing

- add OrphanUploadCleanupTest to verify orphan folder deletion functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d1b92a79d52e79e3c6ac9cb653fb82f652820041
- add OrphanUploadCleanupTest to verify orphan folder deletion functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/265753e28a5d397eced724a4aa80e61ffe071099
- add throttling test for verification link sending by @celikerde in https://github.com/unusualify/modularous/commit/e84f4b1f39fd7b9b5c75f877cd9c962231f33aea
- update exception messages and method names to follow snake_case conventions by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/01dafe31cca10b2ca917b0d91679eb88c2e35893
- restore commented-out logic for dynamic statuses file naming based on environment variables by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/fdd7ded2940483cf9bf0fc0563e54d2b9a5de368
- introduce IsolatedTestModules for improved test module management and dynamic status handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6c09b16e2c0722f823415dfa8996844a2efe98a5
- enhance tests with fixture models, stubs, and improved structure for better maintainability by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/610896c553bceef6ac2129bb916573d890a1522d
- enhance route registration and availability checks in TestModulesCase and ConnectorTest by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/07ef119664f20ce178ecda3133c788ac0336b17b
- streamline module activation handling in TestModulesCase and improve cache management by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/0a6e8e22a66d131bb1b7d47b4afddcd13a721f89
- update import paths for TableFormatterCell and ConfigurableCard components to reflect new directory structure by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/15d19d32a1223bd44f2cc1798a0a57afb8d41a06
- add afterEach cleanup in VInputAssignment tests to ensure proper unmounting and DOM reset by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/369489b470462c7ddee6d81391a5ae0f9a774f5d

### :package: Build

- update build artifacts for v12.2.0 by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e2e872e563ea963299a0fdf41abec392b8f60dad

### :green_heart: Workflow

- change test command from 'test:fast' to 'test' by @web-flow in https://github.com/unusualify/modularous/commit/18ea19a1d4ad90b7b3651137b88d74093643276c
- update Node.js version in CI workflow from 20.x to 22.x by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7d5bb339f5bbccaffef8064bc36d91796c00bc2c
- update test command in CI workflow to use 'composer test:fast' for improved execution speed by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/1ae6c4280b083f2b32b9a2c0e01435b790543460
- change test command in CI workflow from 'composer test:fast' to 'composer test' for consistency by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6c6d6dfcf81cf6a91f0a97766469d667d7b53b4d

### :beers: Other Stuff

- add API controllers for routing metadata, homepage tests, layout management, and promotions by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f53176337ff76d38f7359289ea33c9f1772d5da7
- add TODO comments for performance optimizations in item resolution and presentation rendering by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e8fb4c277bcd4891497f63d782e61c633b6196b6
- add php_unit_method_casing configuration for snake_case method names by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/394e26cc2e763eba391eea3291c9e2ec0e63c5be
- update @tiptap packages to version 3.27.1 and add new Babel dependencies in package.json and package-lock.json by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/37a2c0123c34178e76f1b4e78f1e29d988abccef
- remove unused configuration files and dependencies from vue.vue.cli project by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/bbe154cf65c9b0f0d14dee3e8d8ba1e9ba520b41

## v12.1.7 - 2026-06-27

### :wrench: Bug Fixes

- add authorization handling in HasAuthorizable trait by @celikerde in https://github.com/unusualify/modularous/commit/c4c37ae3e11bf10614ee367c98e8a03d16944b03
- correct import statement for AbstractPaginator by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/90d187e0e655e2dc0d21d145afe8d861baed81dc

### :beers: Other Stuff

- remove outdated entry for v12.1.7 by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/ac6219016590076457a3bc156e3e5e249d8a38d7

## v11.1.3 - 2026-06-27

### :wrench: Bug Fixes

- enhance authorization handling for model saves by @celikerde in https://github.com/unusualify/modularous/commit/cde74e32086198cbbcb02d18e742d7dca75348ba

## v0.58.8 - 2026-06-27

### :wrench: Bug Fixes

- enhance locale value retrieval logic by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/333f8313e39d497a087c27d273ad6239a290f479

## v0.58.7 - 2026-06-25

### :wrench: Bug Fixes

- add model unauthorization handling by @celikerde in https://github.com/unusualify/modularous/commit/1daa04d8b6eb3d1d3f1ab72103cc4ab2c27b58bc

## v12.1.6 - 2026-06-24

### :wrench: Bug Fixes

- enhance data response handling with new resource resolution logic by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d157b6d2b67e50a4152849e17ec1c39a8aa7466a

## v11.1.2 - 2026-06-24

### :wrench: Bug Fixes

- enhance data response handling with new resource resolution logic by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/61212a230223671b5895a6d4f932c494f68a1f33

## v12.1.5 - 2026-06-23

### :wrench: Bug Fixes

- enhance value handling for translated objects in getModel function by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/db9e08b59c5d832119d5a0080a6152c7210d4ab2

## v11.1.1 - 2026-06-23

### :wrench: Bug Fixes

- merge default values into translated objects for improved data handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d9b33e1d1899b8beb16c4a32b169e46b8a6dbdd7

## v0.58.6 - 2026-06-23

### :wrench: Bug Fixes

- enhance response handling for API resources by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/0c9317f66d3b260ee30fa2dd1375034911fdcdbb
- improve translation handling and default value assignment by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9d77eedaa764c81378e55063dedf0a9c0c6ab957

### :white_check_mark: Testing

- add unit test for collection response with flat pagination by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/ac8b7013f41f512c1ca5a8194491bd6143b6adf3

## v12.1.4 - 2026-06-23

### :rocket: Features

- include favicons in app layout and head partials by @celikerde in https://github.com/unusualify/modularous/commit/4ece28d10708bcf7c0d37cf2e6d3e9d7a137e7cb
- add dynamic favicon links with cache-busting for improved asset management by @celikerde in https://github.com/unusualify/modularous/commit/67fac06e56d48115a0ac81fdd607b3aba92d7630

### :wrench: Bug Fixes

- update isSuperAdmin check to use camelCase property by @celikerde in https://github.com/unusualify/modularous/commit/24a2a627225e0c64ae30ade7d08ae12744451085
- implement transformer logic for data handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/dddd0b682024f0539cbf824a3f9dd775ec3f94c9
- introduce base Resource class and corresponding tests for data transformation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/54306eccf55f9b54f7cbbe7cd9f1fd020253a15c

## v11.1.0 - 2026-06-23

### :rocket: Features

- enhance recent impersonation tracking by @celikerde in https://github.com/unusualify/modularous/commit/a9965ca92577792415a7e4d4b1f4cf7cb2627d4a
- refactor impersonation config and add recent impersonations retrieval by @celikerde in https://github.com/unusualify/modularous/commit/a3d1f035dba59b26516c13bf934ebb4c0d6d2ddd
- add recent items support and enhance serach behaviour by @celikerde in https://github.com/unusualify/modularous/commit/94793ac3f9c43f24cfab0e9de705da7a1271bafe
- introduce JSON field input component with CodeMirror integration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d5dbcda24260598afd05b71eb3c52dd6143dd947
- enhance repeater component with collapsible functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/15b3bf8103b22174a5d1e0ece37987dfa804ee76
- add new image formats to supported extensions by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d4a6523d18c021a26bae4689838888b97d7b9f36
- enhance image component and hooks for better media management by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/adb2af72552797a8d8140a7de8376dcc428137bb
- integrate provide for form payload management by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/4b4cae8c4079125c9576b63190d53d3e127f0d92
- add new hooks for enhanced textarea functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/59fb6a76881db60db5f0a38240cf5f36c086b156
- enhance media loading and pagination functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/71fbfa0a0136977a7eccd526bf04c069b396f49d
- refactor layout and introduce FormLocaleSelector component by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/ac00776b72863bc0e92c119e5b191bda577be081
- enhance sidebar menu item naming logic by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e7d7d487dab1823b47e2f322b16219332064713f
- enhance publishable traits and metadata handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/808f5778bcb0b25158ba3ced2a07365c7924520e
- introduce ImageGalleryHydrate and Vue component for image gallery input by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f7c0bf9c0ec60d3b6221211d159d3b71ade7e098
- add current page information to index data response by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/705710d61389061ed9fbe460e7c8f16d373701bd
- enhance route registration based on frontend request configuration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/123771d648e18805d0c7fdadb652b2a7fe0b627d
- extend supported file formats by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/dae75df03964bbc9e4ecefea0953675f22ead26c
- enhance form field handling by chunking inputs by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f77ab2e47edb3839320f99b4e45bf85bf2ff9727
- implement raw configuration caching for improved performance by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d91f22652a39ece9ab19bd134aba868b8dbeb10e
- add new component for layout blade editing by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/4eb63dc30748e7a2da093007acbb389f1773c074
- add CodeMirror packages for enhanced code editing capabilities by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/1f878a12128fff9cfd9ad4190039d401170083ca
- extend input registry with new components and add tests by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/fcbe1e32f79eb5eb2c24fbba4879f9ee49194399
- add new layout blade hints and warnings by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/31cc65d90688ede017958c0e1806907ada3bb100
- introduce layout builder and stylesheet management by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/2a92e3e58a8bfff720adadf18f0e521a7041a3eb
- implement stylesheet compilation service and related utilities by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/54e43956c33d97cbf4fa77bc354cd6c2a2cbff7d
- implement CMS page layout resolution service by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/486c73183db435d7674a4f4fd084c5f9e689c204
- add request exclusion logic for stylesheets by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/974a1382610ba81a58775b44e9657b5f310c1a7e
- add layout builder views and structure by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/48e219a6f924a51bb31291a125c27482018f5ed8
- add CmsLayoutShellPreviewPlaceholder for layout preview functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5e7030f009d2a0fb20ca729dcb8cd5a332528cda
- add CmsPageLayoutPresentationWrapper for enhanced layout rendering by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/8c83e1fb84d02027960d4ef28159b811f9115506
- enhance view name resolution for module routes by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/86f01088229ce72f454ae7e7d2a1344eab4830be
- introduce layout rendering classes for enhanced CMS functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/ed61cc662be5eafbec786f375c4aa153626ee27b
- introduce StylesheetManager for handling stylesheets in CMS by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/3e2627b8a08d2ce42e7e86ee3fe714df24cd6e7f
- add master layout Blade template for CMS by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/10b8083acddf2d92b0fdca81c77a635e153ceb4d
- add PublicStyleSheetAssetController for serving compiled CSS by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6efc6645930fcea452f1de13df05f5881e4ce3f2
- add LayoutBuilderHtmlPreviewController for CMS layout previews by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7a8e422df0152db6bd5c003c60bcaf26fda9689f
- add LayoutBuilderShellDraftPreviewController for draft layout previews by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7a51ace3f0cd6354fd39a0690d8215f2b961cab8
- enhance SEO handling for staging environments by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/508fd81dbf528fecdbecd07ec60787eea2cbb006
- enhance CMR functionality and public stylesheet routing by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/2525f964f59c5d47675ec41d9ef58ea87e3cc7b8
- add default extra table fields in CMS foundation migration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/c6d39a19b38097588e0706c33a2aa3976cfaba3d
- add LayoutBuilderMiddleware for dynamic layout sharing by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/3bcd85092a3a50be246cc2e0040d7a7f05f27606
- add new API routes for page layouts and stylesheet recompilation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/17a48c788e7ca1bac8eec344c2c6cd32e9d6e101
- add Turkish and English translations for CMS modules by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5405bdb8a9139902d4c0535ebd34116f0a1dd781
- enhance module route selection with page layout filtering by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/352954cb363065b7e55b99f2afb9e3774122c1cf
- enhance view rendering logic for CMS previews by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9b06ccb83e7fa6fa636d50c4cbf0a7331e3f5c2c
- introduce LayoutBladesHydrate for CMS layout management by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/25fade1d17d25d716efadb7f2b9e87b87bdc65a3
- enhance input processing with hydration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/8073a4f0f17c18b39045090d3c0a5bb5c91ffee6
- update dependencies and add SCSS suggestion by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/8e3a6d490b507f327ac66f79faee5c22ca6f33e8
- add FlushLibraryUploadsCommand and OrphanUploadCleanup service for managing orphan upload folders by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e2d9ad1058d722d1da15106f8b794ecf5101a204
- implement pagination controls for media grid with next and previous navigation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7be53a3dd699bebc5a80be798daec44f3a74de13

### :wrench: Bug Fixes

- update MediaLibraryController import path in seeders by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9cc4b80e237fac709703c703ed2b27555712abc6
- update switch true/false values to handle boolean resolvedValue by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6f045248652efd89d5ed3c948cfff95e64a3d62e
- ensure errorMessages defaults to an empty object by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/c0def392d3930a5314024788e78341bb603ae33d
- improve tag value handling for translated and associative arrays by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5798f07019ac06d104f721eebffe08a7c6ad2a34
- improve form field handling for non-serialized objects by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/367a13f298fcc5ca3a5fd4eacb2e035473193eec
- enhance translation input handling logic by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5e8c454e0dc800942d59b915aeb4ef766dc41cc9
- correct canonical URL generation logic by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5c508f1b67869d1610e5a3a37ae17f6fb8a130df
- update CMS configuration check for home route redirection by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/456ae850f736de6ca5b6421423ed90ecc65b8283
- rename function for clarity in impersonation configuration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/dbd05ff07617e6c10d9327324cc33f6234bf57b8
- implement injection markers for modular head and footer content in layout builder by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/84ddaf893468af42aacfc791599fa5384f47501d
- use dynamic table names for layout builders and style sheets in draft validation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/0a18f42f4ed7b463c0110a1c20b688d09be9f5c2
- update icons for CMS modules to improve visual consistency by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/29c4e298c95d6a105c9b49236b4846df0faf6cd5
- enhance get method to support case value retrieval by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/269dd88eace5b560e05a86bd30b2cf8c755d0938
- correct tags mapping to ensure proper array formatting in mediableFormat method by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/81ae5798d1b1100c0170089e778b5c638906c965
- update isSuperAdmin check to use camelCase property by @celikerde in https://github.com/unusualify/modularous/commit/876cb63c34f0e3aaf9066e665c5a0614908b0d8f
- implement transformer logic for data handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/966490b846ad415cbef4910f3baeec1418fc9146
- introduce base Resource class and corresponding tests for data transformation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6b8084df6fe3bf3ae47020710117af1ebe9b4c34

### :recycle: Refactors

- replace Config facade mocks with real config values in notification tests by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e7ec126531cdc49af559f368bec5484ef1c8b9d7
- update MySqlGrammar instantiation in CollationSelectorTest by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/798fe85b96ca40a28f85bcd596a61290c8f89229
- comment out test_get_by_status method in ModularousTest by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/1a96da003ab7e6c5460026d498171daf0aa246e2
- comment out setPublishStartDateAttribute method by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/06a3fa4973525f2aeeb873c4539374cf2acdc699
- improve content handling and attribute management by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e48366bcd9bde9df26c439f83ab9d85ca681b806
- improve method naming and documentation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/20c730557794d9c619bc5c8afa4e91a05abe1569
- update method visibility and improve code clarity by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/56bce701e0e0ab7274d844c1b3ecf1f62900e092
- streamline model resolution and error handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7be3fe89c2abffc7e4b3c0b159fb89be41e2931f
- enhance public CMS presentation rendering by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5f904cfa90e376d15eb892bd3d0e3fe718f957f1
- simplify getModel method by utilizing repository to retrieve model instance by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d2ee4b93ad704238177e796230e4f5119f0ea022
- enhance media filtering UI with improved tag selection and mobile responsiveness by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/a45537eb72f46444ba2f0357099b57020c1004d4

### :memo: Documentation

- update PHP and Laravel version requirements in README by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/bc67831d84e1c976d43193ad1f312951a2b46150

### :lipstick: Styling

- lint coding styles for v12.1.0 by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7230c276612e9405cc80a2bbb77ae52d19c80324
- lint coding styles for v12.1.3 by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/b884cf703a0a8a1220b233ce028470f2c75c9a4a

### :white_check_mark: Testing

- distinguish event dispatch verification for email registration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f31cbcbb3f4bf586e012230265c4c258e7c348c1
- correct route action method parameter in ModuleTest by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/24328382db4126c570edacacb6732364d3dee2eb
- comment out environment setup logic in TestModulesCase by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/768a13d4c6dfc527417d46d68af8dd10a4109c05
- add unit test for JSON field input handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/96647fb41802c0e035846456807ad4f85b400c53
- enhance utility CSS generation and testing by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9139f3ff195de4ff14b721ff81f4cafa628e5ec9
- add OrphanUploadCleanupTest to verify orphan folder deletion functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d1b92a79d52e79e3c6ac9cb653fb82f652820041

### :package: Build

- update build artifacts for v12.1.0 by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/4ec8b4d6903f9aa88d05a0c3b8b9b1f2d5730c34
- update build artifacts for v12.1.3 by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/933363dc2bcf5a9110466baeda41b284ec3dbe94

### :beers: Other Stuff

- update composer dependencies and versions by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e606f8e2ba1fd55fff1e939e2f33a4d1f0c362dc
- add Laravel 12.x support to issue templates by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d47d6ca63d480188d0f734f8a9135b98db4ff4e6

## v0.58.5 - 2026-06-23

### :rocket: Features

- introduce base resource transformer with merge functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/a9840679ece59ecc6dbe994be9819299e041c4fa

### :wrench: Bug Fixes

- update super admin check to use consistent naming convention by @celikerde in https://github.com/unusualify/modularous/commit/f346696d945bf5f6bd57689e19f496ee8961c126
- implement transformer logic for data handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/748da610e27161b8a886a39cf249f6e27d4cbde5

### :recycle: Refactors

- streamline data transformation in getJSONData method by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/0debba906cd77c7d41411caba3fe8c50c91b1619

## v12.1.3 - 2026-06-16

### :rocket: Features

- add FlushLibraryUploadsCommand and OrphanUploadCleanup service for managing orphan upload folders by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e2d9ad1058d722d1da15106f8b794ecf5101a204
- implement pagination controls for media grid with next and previous navigation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7be53a3dd699bebc5a80be798daec44f3a74de13

### :wrench: Bug Fixes

- correct tags mapping to ensure proper array formatting in mediableFormat method by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/81ae5798d1b1100c0170089e778b5c638906c965

### :recycle: Refactors

- enhance media filtering UI with improved tag selection and mobile responsiveness by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/a45537eb72f46444ba2f0357099b57020c1004d4

### :lipstick: Styling

- lint coding styles for v12.1.3 by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/b884cf703a0a8a1220b233ce028470f2c75c9a4a

### :white_check_mark: Testing

- add OrphanUploadCleanupTest to verify orphan folder deletion functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d1b92a79d52e79e3c6ac9cb653fb82f652820041

### :package: Build

- update build artifacts for v12.1.3 by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/933363dc2bcf5a9110466baeda41b284ec3dbe94

## v12.1.2 - 2026-06-15

### :wrench: Bug Fixes

- update icons for CMS modules to improve visual consistency by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/29c4e298c95d6a105c9b49236b4846df0faf6cd5
- enhance get method to support case value retrieval by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/269dd88eace5b560e05a86bd30b2cf8c755d0938

### :recycle: Refactors

- simplify getModel method by utilizing repository to retrieve model instance by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d2ee4b93ad704238177e796230e4f5119f0ea022

## v12.1.1 - 2026-06-15

### :wrench: Bug Fixes

- rename function for clarity in impersonation configuration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/dbd05ff07617e6c10d9327324cc33f6234bf57b8
- implement injection markers for modular head and footer content in layout builder by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/84ddaf893468af42aacfc791599fa5384f47501d
- use dynamic table names for layout builders and style sheets in draft validation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/0a18f42f4ed7b463c0110a1c20b688d09be9f5c2

## v12.1.0 - 2026-06-15

### :rocket: Features

- enhance recent impersonation tracking by @celikerde in https://github.com/unusualify/modularous/commit/a9965ca92577792415a7e4d4b1f4cf7cb2627d4a
- refactor impersonation config and add recent impersonations retrieval by @celikerde in https://github.com/unusualify/modularous/commit/a3d1f035dba59b26516c13bf934ebb4c0d6d2ddd
- add recent items support and enhance serach behaviour by @celikerde in https://github.com/unusualify/modularous/commit/94793ac3f9c43f24cfab0e9de705da7a1271bafe
- introduce JSON field input component with CodeMirror integration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d5dbcda24260598afd05b71eb3c52dd6143dd947
- enhance repeater component with collapsible functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/15b3bf8103b22174a5d1e0ece37987dfa804ee76
- add new image formats to supported extensions by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d4a6523d18c021a26bae4689838888b97d7b9f36
- enhance image component and hooks for better media management by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/adb2af72552797a8d8140a7de8376dcc428137bb
- integrate provide for form payload management by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/4b4cae8c4079125c9576b63190d53d3e127f0d92
- add new hooks for enhanced textarea functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/59fb6a76881db60db5f0a38240cf5f36c086b156
- enhance media loading and pagination functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/71fbfa0a0136977a7eccd526bf04c069b396f49d
- refactor layout and introduce FormLocaleSelector component by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/ac00776b72863bc0e92c119e5b191bda577be081
- enhance sidebar menu item naming logic by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e7d7d487dab1823b47e2f322b16219332064713f
- enhance publishable traits and metadata handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/808f5778bcb0b25158ba3ced2a07365c7924520e
- introduce ImageGalleryHydrate and Vue component for image gallery input by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f7c0bf9c0ec60d3b6221211d159d3b71ade7e098
- add current page information to index data response by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/705710d61389061ed9fbe460e7c8f16d373701bd
- enhance route registration based on frontend request configuration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/123771d648e18805d0c7fdadb652b2a7fe0b627d
- extend supported file formats by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/dae75df03964bbc9e4ecefea0953675f22ead26c
- enhance form field handling by chunking inputs by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f77ab2e47edb3839320f99b4e45bf85bf2ff9727
- implement raw configuration caching for improved performance by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d91f22652a39ece9ab19bd134aba868b8dbeb10e
- add new component for layout blade editing by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/4eb63dc30748e7a2da093007acbb389f1773c074
- add CodeMirror packages for enhanced code editing capabilities by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/1f878a12128fff9cfd9ad4190039d401170083ca
- extend input registry with new components and add tests by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/fcbe1e32f79eb5eb2c24fbba4879f9ee49194399
- add new layout blade hints and warnings by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/31cc65d90688ede017958c0e1806907ada3bb100
- introduce layout builder and stylesheet management by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/2a92e3e58a8bfff720adadf18f0e521a7041a3eb
- implement stylesheet compilation service and related utilities by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/54e43956c33d97cbf4fa77bc354cd6c2a2cbff7d
- implement CMS page layout resolution service by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/486c73183db435d7674a4f4fd084c5f9e689c204
- add request exclusion logic for stylesheets by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/974a1382610ba81a58775b44e9657b5f310c1a7e
- add layout builder views and structure by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/48e219a6f924a51bb31291a125c27482018f5ed8
- add CmsLayoutShellPreviewPlaceholder for layout preview functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5e7030f009d2a0fb20ca729dcb8cd5a332528cda
- add CmsPageLayoutPresentationWrapper for enhanced layout rendering by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/8c83e1fb84d02027960d4ef28159b811f9115506
- enhance view name resolution for module routes by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/86f01088229ce72f454ae7e7d2a1344eab4830be
- introduce layout rendering classes for enhanced CMS functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/ed61cc662be5eafbec786f375c4aa153626ee27b
- introduce StylesheetManager for handling stylesheets in CMS by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/3e2627b8a08d2ce42e7e86ee3fe714df24cd6e7f
- add master layout Blade template for CMS by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/10b8083acddf2d92b0fdca81c77a635e153ceb4d
- add PublicStyleSheetAssetController for serving compiled CSS by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6efc6645930fcea452f1de13df05f5881e4ce3f2
- add LayoutBuilderHtmlPreviewController for CMS layout previews by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7a8e422df0152db6bd5c003c60bcaf26fda9689f
- add LayoutBuilderShellDraftPreviewController for draft layout previews by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7a51ace3f0cd6354fd39a0690d8215f2b961cab8
- enhance SEO handling for staging environments by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/508fd81dbf528fecdbecd07ec60787eea2cbb006
- enhance CMR functionality and public stylesheet routing by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/2525f964f59c5d47675ec41d9ef58ea87e3cc7b8
- add default extra table fields in CMS foundation migration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/c6d39a19b38097588e0706c33a2aa3976cfaba3d
- add LayoutBuilderMiddleware for dynamic layout sharing by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/3bcd85092a3a50be246cc2e0040d7a7f05f27606
- add new API routes for page layouts and stylesheet recompilation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/17a48c788e7ca1bac8eec344c2c6cd32e9d6e101
- add Turkish and English translations for CMS modules by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5405bdb8a9139902d4c0535ebd34116f0a1dd781
- enhance module route selection with page layout filtering by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/352954cb363065b7e55b99f2afb9e3774122c1cf
- enhance view rendering logic for CMS previews by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9b06ccb83e7fa6fa636d50c4cbf0a7331e3f5c2c
- introduce LayoutBladesHydrate for CMS layout management by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/25fade1d17d25d716efadb7f2b9e87b87bdc65a3
- enhance input processing with hydration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/8073a4f0f17c18b39045090d3c0a5bb5c91ffee6
- update dependencies and add SCSS suggestion by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/8e3a6d490b507f327ac66f79faee5c22ca6f33e8

### :wrench: Bug Fixes

- enhance reason input with overflow handling and submission logic by @celikerde in https://github.com/unusualify/modularous/commit/12f4ac07f78ab148e286cc485079cc510a0e3c1c
- prevent mutation of title interpolation props by @celikerde in https://github.com/unusualify/modularous/commit/1717a2836cb5135aab5de25cabb4b28405f299d1
- enhance payment processing experience by @celikerde in https://github.com/unusualify/modularous/commit/23e3f8aa27592047ca7b8320f965c84fbd35ad6a
- add initial item locking functionality by @celikerde in https://github.com/unusualify/modularous/commit/6bfb162ad7af68279d6772793000679c7132f373
- update switch true/false values to handle boolean resolvedValue by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6f045248652efd89d5ed3c948cfff95e64a3d62e
- ensure errorMessages defaults to an empty object by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/c0def392d3930a5314024788e78341bb603ae33d
- improve tag value handling for translated and associative arrays by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5798f07019ac06d104f721eebffe08a7c6ad2a34
- improve form field handling for non-serialized objects by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/367a13f298fcc5ca3a5fd4eacb2e035473193eec
- enhance translation input handling logic by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5e8c454e0dc800942d59b915aeb4ef766dc41cc9
- correct canonical URL generation logic by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5c508f1b67869d1610e5a3a37ae17f6fb8a130df
- update CMS configuration check for home route redirection by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/456ae850f736de6ca5b6421423ed90ecc65b8283

### :recycle: Refactors

- comment out setPublishStartDateAttribute method by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/06a3fa4973525f2aeeb873c4539374cf2acdc699
- improve content handling and attribute management by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e48366bcd9bde9df26c439f83ab9d85ca681b806
- improve method naming and documentation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/20c730557794d9c619bc5c8afa4e91a05abe1569
- update method visibility and improve code clarity by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/56bce701e0e0ab7274d844c1b3ecf1f62900e092
- streamline model resolution and error handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7be3fe89c2abffc7e4b3c0b159fb89be41e2931f
- enhance public CMS presentation rendering by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5f904cfa90e376d15eb892bd3d0e3fe718f957f1

### :lipstick: Styling

- lint coding styles for v12.1.0 by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7230c276612e9405cc80a2bbb77ae52d19c80324

### :white_check_mark: Testing

- add unit test for JSON field input handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/96647fb41802c0e035846456807ad4f85b400c53
- enhance utility CSS generation and testing by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9139f3ff195de4ff14b721ff81f4cafa628e5ec9

### :package: Build

- update build artifacts for v12.1.0 by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/4ec8b4d6903f9aa88d05a0c3b8b9b1f2d5730c34

## v11.0.2 - 2026-06-15

### :wrench: Bug Fixes

- enhance reason input with overflow handling and submission logic by @celikerde in https://github.com/unusualify/modularous/commit/12f4ac07f78ab148e286cc485079cc510a0e3c1c
- prevent mutation of title interpolation props by @celikerde in https://github.com/unusualify/modularous/commit/1717a2836cb5135aab5de25cabb4b28405f299d1
- enhance payment processing experience by @celikerde in https://github.com/unusualify/modularous/commit/23e3f8aa27592047ca7b8320f965c84fbd35ad6a
- add initial item locking functionality by @celikerde in https://github.com/unusualify/modularous/commit/6bfb162ad7af68279d6772793000679c7132f373

## v0.58.4 - 2026-06-08

### :wrench: Bug Fixes

- update global scope and currency formatting by @celikerde in https://github.com/unusualify/modularous/commit/5475b1bc90794d4f4f70c8af49a3e957c5d0e6a2
- enhance reason input with overflow handling and submission logic by @celikerde in https://github.com/unusualify/modularous/commit/12f4ac07f78ab148e286cc485079cc510a0e3c1c
- prevent mutation of title interpolation props by @celikerde in https://github.com/unusualify/modularous/commit/1717a2836cb5135aab5de25cabb4b28405f299d1
- enhance payment processing experience by @celikerde in https://github.com/unusualify/modularous/commit/23e3f8aa27592047ca7b8320f965c84fbd35ad6a
- add initial item locking functionality by @celikerde in https://github.com/unusualify/modularous/commit/6bfb162ad7af68279d6772793000679c7132f373

## v12.0.1 - 2026-05-12

### :rocket: Features

- add method to retrieve index appends configuration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/55826a8aa1bfe69c9894d2ae817c5fa52d1ca415
- enhance setup command with repository and timeout options by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/04f2d0214220dd73d3a23341bc9789f9cd092236

### :wrench: Bug Fixes

- update sidebar expandHover setting to 'mini' by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/eeb1ab9254e8f756710cfd713874e41df702e866
- update middleware references to include modularous prefix by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/867273e32ef08356a150e1398fc45f718d3ffc1b
- update cache check to include console environment by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/4bd518c16ddbf043c89169b01fa49116b732a9b0
- update parent segment trait and add database existence check by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/bea2e95cd2779bbd59d59697cd51e9de57323fab

### :beers: Other Stuff

- add Laravel 12.x support to issue templates by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d47d6ca63d480188d0f734f8a9135b98db4ff4e6

## v11.0.1 - 2026-05-12

### :rocket: Features

- add method to retrieve index appends configuration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/55826a8aa1bfe69c9894d2ae817c5fa52d1ca415
- enhance setup command with repository and timeout options by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/04f2d0214220dd73d3a23341bc9789f9cd092236

### :wrench: Bug Fixes

- update sidebar expandHover setting to 'mini' by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/eeb1ab9254e8f756710cfd713874e41df702e866
- update middleware references to include modularous prefix by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/867273e32ef08356a150e1398fc45f718d3ffc1b
- update cache check to include console environment by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/4bd518c16ddbf043c89169b01fa49116b732a9b0
- update parent segment trait and add database existence check by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/bea2e95cd2779bbd59d59697cd51e9de57323fab

### :memo: Documentation

- correct toolkit name in README from 'Modularty' to 'Modularous' by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/3cc29870dd9d8dfbd06157d4f09faa1eb9103baa

## v11.0.0 - 2026-05-09

### :rocket: Features

- implement CSRF token handling in form submissions and axios requests by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/c9db616f7fa68581c2cc1a34e0ce3a4270484c63
- add OtpInputHydrate class for OTP input handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/fff2a14931bbc67474dc3a7f28c8c08626217302
- add source_revision_id to fillable fields and implement isDraft method by @celikerde in https://github.com/unusualify/modularous/commit/6238526c993df4724f9aa3d1fc8b149582bb4d23
- implement revision management trait for entities by @celikerde in https://github.com/unusualify/modularous/commit/5ef457542f28dc2c9a99c33385fed18146047b8b
- add trait for managing revisions with creation, restoration, and preview functionalities by @celikerde in https://github.com/unusualify/modularous/commit/2634c48b4f3bcf4bc868258bb1e2c5b7dde35efa
- add revision management data to form response by @celikerde in https://github.com/unusualify/modularous/commit/48ba5986b87924364f9d5ad84b0c00cf8e29e0e0
- add restoreRevision method for handling revision restoration and preview functionality by @celikerde in https://github.com/unusualify/modularous/commit/7ee785bc00242250ee443c5a209c99aa74bc9856
- enable 'restoreRevision' macro for revision restoration functionality by @celikerde in https://github.com/unusualify/modularous/commit/8b14cc4759e5fdb292a1fbc27d91a8fc1a55ab95
- add generic preview components for dynamic module previews by @celikerde in https://github.com/unusualify/modularous/commit/7fca3645213694b4b3ceb27864aba33b9bfd85e3
- implement RevisionsList component for displaying and managing revisions by @celikerde in https://github.com/unusualify/modularous/commit/08843f88ee4a74610e8493b57cee357c76b9279d
- enhance form component with revision management and preview functionality by @celikerde in https://github.com/unusualify/modularous/commit/34beefd82c7c0be37ef6a0285bd5c6163fcacaf6
- use configurable default role for user registration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/4a43a5964b62f0b1f373d200ad39e9af80934557
- add StepUpChallenge component for OTP verification by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f759aa521f1c71e9a6443d53146978b7d8acf811
- enhance input fetching with configurable keys for pagination and data by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6009b0198e8a35cdcd9492438ae62441c0f641f1
- implement step-up verification handling in form submission by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/11a785605ad38f720fc827bc72ee271450f04036
- implement MFA setup and authentication handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/4455fc47ea4853b53179a1ceededd48b6232da91
- add dynamic middleware management by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/574e16364aed51bdc7c157d8d53dba3d05390b29
- implement capabilities and step-up verification system by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/33005484985794bb65a5c6e69944c56d2fc5730e
- add Google 2FA fields to users table for multi-factor authentication support by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/795fb3be91b4fea972cad1cc59b264f765450986
- add ManagePreview trait for handling preview and revision functionalities by @celikerde in https://github.com/unusualify/modularous/commit/fb48459c06f234c626b87f4c41586a462794cb87
- implement RevisionHydrate class for input handling by @celikerde in https://github.com/unusualify/modularous/commit/0f318639b0c8acc570a9708933a0a210fb741750
- add new routes for revision management by @celikerde in https://github.com/unusualify/modularous/commit/17be84b24b02462ff7636d7a13b944fa0d052b60
- create Revision component for displaying individual revisions by @celikerde in https://github.com/unusualify/modularous/commit/7c4c0d3eca1a138b0d5871c1164dd43b35e3f602
- enhance form handling with user role checks and schema updates by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e2eab16684ca0eb356c13d85d3d9b0d86fb6bac0
- add hasPermission method for permission checks by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/fb70c20a3634ace831096f9d177d17932111bac3
- enhance permission management functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/4ac20237b9023622ad84819eccb81a18fde41283
- introduce custom ValidationException and factory for enhanced error handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5d151d9a5d1b297f20a21684f7939a689c344a2f
- add obj prop to components for enhanced data handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d14a5e5907a4cad6423c1ec41ff23ebb4e65ff86
- introduce useForm, useFormResponseStatus, useResponseAlert, useStepUpChallenge for enhanced form handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/0572ef39ded3cc81512818f8eae2aa418acb96a1
- add SlugInputValidationController and related components for slug validation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/50e708d489032e671b65c59daf1e236e68e0d8cc
- implement revision approval and rejection workflow by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6e680afcec908c3edf73095a18031df32fd7cda4
- add scrollStrategy prop for improved modal behavior by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/efe5265977ec61a7c47747399eeb85927d4eb7f0
- add scrollStrategy prop to Modal component for improved behavior by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/27c7c8212df5e70c16445e1e800757044e0e0b3a
- add Revision.vue for managing revision workflows by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/1fe9177e4e0395e5054093c23c19cb4ba8152523
- add SlugInputValidationService for slug validation logic by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/caf4095eabf701755a4a6b452648658a65a02d23
- add FormSecondaryInputs for enhanced input handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5dcb0bf4974a55a3681119222b7e3c0788f5a6ce
- add isSecondary property for enhanced input handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/648ed3f8cf3d92be4d61a6bdeafae4710635e09c
- enhance language handling in revision comparison by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/188f44036dc3fcf3fa140a8a46ec435f93f19d66
- enhance slug handling and active state management by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/1e024feb0aff26dd1bb4c61fa78a4e5614d94804
- enhance v-switch binding for dynamic formatting by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/ac406ce4c5141f83d90128b31959b9e05f5477e4
- implement non-blocking warning handling in Inertia by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/cc2beb9c5eefb4d84c91befffe45aec8e7d66d36
- enhance tooltip display with conditional rendering by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7f63bd25afba2983fd76bda658f1e07c336c3988
- add scopeVisible method for date-based visibility filtering by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9dd2155bf5688fedf88a7c5285411099970d4ee5
- implement CSV bulk import/export functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7f34b114216bd8d26a222606b7882907f347b7d0
- introduce Publishable trait and metadata support by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/451354b62e0f2f841e3ea3507b86693e3f80c033
- add translatable metadata support for models by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e5f47f5e3f28176d622b10e150682c1f460b994b
- invoke trait methods after setting table actions by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/90c40e295075aadd533b52ce2c11dbbe4a8b761b
- add handleResponse method for response processing by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d8ea822bf9dfea2c37c65a0539976037a5cea9c0
- enhance migration helpers with publishable and translatable metadata support by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/651fc1af61271ad2e30359343c28af97e2e10b68
- enhance input event handling with update functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/58505aa995fbd06e044ab0aa975edb93391ee8d2
- correct date field names in visibility scope by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/088a2e6cd005c754a28dd428dc13109d68f43bbe
- add translated properties support and update input exclusion by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f96b503061cd3a4d9db91fd557c3f4168f442195
- implement new hydrate class for module route models by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/de9b814a038bade47ad2b4a89d7487a74bc865f5
- add file existence check before attaching files by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f22c0f7173a1d60e6b8a72dcfd3ff313d0c4c2bc
- enhance slug management and generation features by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7b8bb6d4dd7b702fd92b2837ca4b13d4f7f71b49
- add FormPublicLinks and FormSignedPublicPreview components by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/fa78e8348ae228bef966e3482ae885091d83af96
- update theme colors for improved aesthetics by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/ca2e951419d527f509b9c79076a5360399f48617
- introduce foundational CMS module with configuration and routing by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d61f08f89fc04ddc63851420bfe706984dae836f
- introduce custom Validator for placeholder normalization by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/160411aae8722248edd9fa4a61b1ff883e78cefc
- add block property to account button slot for improved layout by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d7dc3c47a67ba5bb19b212f2bfb07866675b3d83
- add slug generation endpoint and enhance slug management by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/0448fb42e905a86568a7296daebe934f77ef6f26
- enhance preview functionality with locale handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d0443dc64387bca59567d51b59f38967a1cd6d92
- add ParentSegments, Promotion, and SiteSeo components by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/63f52b50c4b825e1e217c04e36b1392e8e7e6e77
- add bulk import/export messages and signed preview links by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/3fcf3cf96b7a6d1870e31200f42776f0981c3042
- add new message keys for user notifications and SEO settings by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f2402a6f77b79b60034032ffa864264e09a8ec67
- update package-lock and package.json with new libraries by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/2009b96a989b953a77ab01a294c8680ed0eb4a90
- add mcamara/laravel-localization package by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/915e74c19aa1865e94d52949be6e9abec39253a8
- comment out CMS-related menu items for future reference by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/b89292a308bd00bb184bc24bdd5051e0c8d5b429
- update validation messages to use placeholder syntax by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/1691f73b5c9a2b3d61ea0e1bfc3ce258cfa504ee
- add new module route features for CMR, Parent Segment, and Publishable by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/c86356ada58edbf5e3a4cbb1dc68c4893712bda6
- add asset publishing for translation package by @celikerde in https://github.com/unusualify/modularous/commit/9b01817f1c77ad29e5d700ed621b4e6f54ddc5cf

### :wrench: Bug Fixes

- update form validation handling and model binding by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/de8e1b34c76731c40f7c839c027f369fc2b737c8
- enhance SvgIcon component with improved styling and structure by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/59291172d363ec2043685965e05cb207dd2ac0ae
- correct argument description for input creation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/ae2e4012c5389b2d93adc708f40771b836da9a38
- enhance authentication handling for Inertia and AJAX requests by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/ff7e410c7de76d6338ae8b390f5afe7a654ed4a6
- enhance google oauth button flex by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/bb88148aced5b072b8cfb4dbb55f71ecfb7dea5a
- update validateStatus to include 428 status for POST and PUT requests by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/cde57c0c37e5214911ce12ae5d39cf10c23676b7
- update global scope and currency formatting by @celikerde in https://github.com/unusualify/modularous/commit/5475b1bc90794d4f4f70c8af49a3e957c5d0e6a2
- add afterSaveTranslationsTrait method for timestamp management by @celikerde in https://github.com/unusualify/modularous/commit/c2ac417060c98952e0c098ad379a92c70447b4a3
- correct early return logic for handled status by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7eaaffeeb675e367c4fb8106442577936622d452
- update mediableActive assignment to use reactive refs by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e0d708513b8a779d5e160f3dda86ddc01bb0c8a4
- remove CSRF token wrapping from post and put requests by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7a2cc97cd42b3505cc85040a39c337eddb8a5717
- improve validation rules for array and string types by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6ab629eee459ce1aafafda0cfb7139a7c6d0d27c
- add auto-hide feature for input details by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9fa22f2a84bd2626f4a8dfea39b3747c311f7896
- change rate column type from unsignedFloat to decimal by @celikerde in https://github.com/unusualify/modularous/commit/3ef9007ce3d2ac9acb951f04403547d1264fa5c2
- set default cache configuration for testing environment by @celikerde in https://github.com/unusualify/modularous/commit/eac1df5b8f251386ee027e012cbc1c900bfb9327
- update spritemap route handling for compatibility with vite-plugin-svg-spritemap v2.3.x by @celikerde in https://github.com/unusualify/modularous/commit/d2f32df15b80ef90f925e56215549293ec4725c6
- disable auto-inherit of attributes and explicitly forward $attrs by @celikerde in https://github.com/unusualify/modularous/commit/47b5307ac1e2cc47b666eaf22c92fba54915b656
- handle endpoint hydration and improve prop types by @celikerde in https://github.com/unusualify/modularous/commit/3305086760fe0646401a93d20923c535488f0ea7
- set default for type prop to prevent errors during hydration by @celikerde in https://github.com/unusualify/modularous/commit/4d6141f7a9bd07fda82fc75b9693eb8170917d8e
- update redirect handling for unauthenticated requests by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/bd0863644407e26a54564f9fdcfe79262fd33991
- update tags table configuration for locale handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/40300e5b78d68d76f2ed0eb77265da3f3d53eea5
- improve pattern invalidation handling for non-Redis drivers by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/a711fdefac60b8c6406dfa78808b36b7ff2a9354
- update session handling in LoginControllerTest by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/1505b61b8bb68708ba26e1f43f27ed936be9ba2d

### :recycle: Refactors

- simplify context usage and improve slot bindings by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/3d1babf4b3188be0d0f9ed580a57559d734ff99f
- replace HasRoles trait with Rolable and streamline role management by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/244231174843a400587812b0990033e25fd72aa1
- streamline authentication handling with centralized redirect logic by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/8598744d5940cd000f15c2037befa39a31707bbd
- update visibility and add new methods for revision management by @celikerde in https://github.com/unusualify/modularous/commit/4a2e9453909fb7ff259db623af1f44e899f22bca
- integrate ManagePreview trait and remove obsolete restoreRevision method by @celikerde in https://github.com/unusualify/modularous/commit/96972dd6172f863b356f6d2472d758a8836428ac
- streamline middleware handling and permission checks by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/0b4761845ac641ff6d3479e4fdbf057255adca46
- remove RevisionsList and associated restore revision dialog by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9896f976d7efadf3c3ad357e5fa8159dbbb4b9da
- streamline return structure and clean up unused methods by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/bfee78a8c50723e87e33aa34bda53a3e63631204
- update user role checks to utilize store getters by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/c516dd8d300857ed714378aad28a519106a510ab
- introduce useFormBase and useFormBaseLogic hooks for form handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/eda1617f4df73920a6e0b372cff7258f0e3f3c10
- reorganize form hooks and remove deprecated components by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/19c4c8a61a3a4a1a12f2bc8c2ed334ef88fb7f65
- streamline permission checks in Gate definitions by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e6373ee76d9f87c9fb025113f39e862e40f824c8
- move form properties to FormSchema trait by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/389cf4a1abc20cf5760c7cecd5abdd5c242653ae
- enhance previewForRevision method to include formSchema by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/21244bdf1311823c0e487f6e2c1985a444ae4c27
- enhance image preview handling and add HEIC support by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f04e5a2368cd1cbbad26ae6ea7cd15cc68bc5916
- improve field handling and event processing by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/268029927507077127d0dac7d7fa1642cbe66b92
- reintroduce v-input-locale for translated schema handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/42bf94afe1a4cc9d79af2fd594329e51a47fcaab
- extract getActiveContentLocale utility for improved locale handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/282aa175ac6ee73283dcab3929724806f3b6920a
- enhance slug handling and translation normalization by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6296367e62d09bb52f34a9751a5e6e96330e8053
- reorganize controller namespaces for improved structure by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/db5bd5b6fe5c4d6a1f49456d6a89b31ea6603271
- introduce comprehensive API traits for enhanced functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/506e0837e8c01b7503349ea95afcdadd81bf74ff
- enhance formEvents layout and functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/72586204912aeebc9313870a972aee1826946fe8
- update controller namespace for consistency by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/47ed098e264e392a16c753d75fce3d3f7dd245de
- remove obsolete test file by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/118c293d4774f6a62caf320e5e133409e11bb75a
- improve class name resolution and add utility method by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9c7e15dcacb9f1a62857e8b291c0e410b3ebb281
- improve trait resolution logic by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/fbcecc24ec5b8d359adbc1266fd2735d85b087ab
- add module route validation method and simplify module retrieval by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d4eda178b0ed181fa37c2d4836dda20d3445c534
- simplify model retrieval logic by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/891a9b5146db9eee27949e372ee4f991058cabdf
- reorganize traits and introduce HasPresenter trait by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/2730c34260b5fac18daea913fb5aa34c25bf4f47
- remove HasRevisions trait to streamline codebase by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/8097582348f5bbf7707ddc546b80c392ab5cf09a
- simplify update method and remove unnecessary transaction handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/655d332a1da681ee54c2b5ddae3da38a56131e7e
- simplify translation field handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/b8d32b3c1212649ffdccf59d2c66653a219d68a7
- clean up getTranslations method by removing unused variables and comments by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/2271b281def926ec1cb5f83681bc1fed2a3acecd
- update getRepositoryItem method to return item ID by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f841687a0d72462bed104fbf6593cd15b5d1ed10
- simplify migration classes and improve error handling by @celikerde in https://github.com/unusualify/modularous/commit/78de0ba735f961f482bfa2c33584515518c0af67
- enhance country seeder and add new database seeders by @celikerde in https://github.com/unusualify/modularous/commit/8d49623a4178410b54d64a3b4091e598a3b34adf
- improve module creation and status handling by @celikerde in https://github.com/unusualify/modularous/commit/c727ae119eb5a6e2a2db5d9b2d3e533841e55269
- enhance installation process with improved seeding and error handling by @celikerde in https://github.com/unusualify/modularous/commit/40adbe5a3063decb81c296945d34998aece9f4ba
- update asset loading to use Telescope's inline methods by @celikerde in https://github.com/unusualify/modularous/commit/07b1846ef2af63542361170a2c8fa7f89b152925
- update rolesMetaRelation to use PermissionRegistrar instance by @celikerde in https://github.com/unusualify/modularous/commit/d7a88316eb40e0c5dad9618e1004ccc1a3954167
- update rolePivotKey to use PermissionRegistrar instance by @celikerde in https://github.com/unusualify/modularous/commit/fca7c2c3cff287fcc86ed55d51c723c50612ae5c
- switch response factory from Laravel to Symfony by @celikerde in https://github.com/unusualify/modularous/commit/1dbe514fffc810360ccce51a292344681b4bc59c
- enhance URL handling by allowing null source host by @celikerde in https://github.com/unusualify/modularous/commit/e0950b757a9f3d5c751eb084426a48720132de35
- improve addPath method documentation and parameter type by @celikerde in https://github.com/unusualify/modularous/commit/3cc97f43a994b847cbb8b06fef3fdf716b7912df
- improve addPath method documentation and parameter type by @celikerde in https://github.com/unusualify/modularous/commit/0d12071c595b6186a00797646c77dd8c9ec58592
- update prop definitions to remove required constraints by @celikerde in https://github.com/unusualify/modularous/commit/f7835fe54f02e912b8d1d40dec1b36ec154d9ab8
- enhance alignment prop to support additional CSS text-align aliases by @celikerde in https://github.com/unusualify/modularous/commit/d451e5e2f1587c1b64860ad36573c6d0168c370b
- enhance permission configuration options by @celikerde in https://github.com/unusualify/modularous/commit/bf8c2f26750ce8bd8c894b16cc80379ce9c349b3
- update submission method determination for editing state by @celikerde in https://github.com/unusualify/modularous/commit/ff287ffbd4e9e75e2f68033951fcfe7b70d7a675
- enhance date formatting to handle invalid dates gracefully by @celikerde in https://github.com/unusualify/modularous/commit/d7825d95570d2b945615a39e6a26fa2a0e63a3b8
- prevent exposure of internal locale reference by @celikerde in https://github.com/unusualify/modularous/commit/c289707a10161e0c1e6d9907d1aabea03fe42e43
- update modelValue prop to accept both Array and Object types by @celikerde in https://github.com/unusualify/modularous/commit/af50948ba7562dc679242092c641fc154db7322d
- ensure boolean evaluation for hideIcons and isHoverable properties by @celikerde in https://github.com/unusualify/modularous/commit/7d64db3943c79e597cd20ec21777e266a363873a
- normalize fixed column prop for consistent boolean evaluation by @celikerde in https://github.com/unusualify/modularous/commit/9d8f73b10607e7176b7e23b6231c1c681a43a60b
- improve media type reset logic to handle empty state.types by @celikerde in https://github.com/unusualify/modularous/commit/23626956907a03967e6ad3cfae06ef1a85fe28e1
- normalize col property to handle various input formats by @celikerde in https://github.com/unusualify/modularous/commit/251718c782cd715e8d341c769e80604cd3f1e449
- improve input hydration logic for 'spread' type by @celikerde in https://github.com/unusualify/modularous/commit/7e61dd2c28495b758d11abb41a52e5efb6cb9852
- integrate HasSpreadable trait and adjust spreadableSavingKey by @celikerde in https://github.com/unusualify/modularous/commit/9c18dc706c322ee158cd9ef2c0cd1b55066c991b
- update CORS configuration and Sass deprecation handling by @celikerde in https://github.com/unusualify/modularous/commit/609577a26a968c6e9f3c03fa2fe8e316852d28dc
- adjust data prop to accept null and remove required constraint by @celikerde in https://github.com/unusualify/modularous/commit/23f5bf4ffa2db05b38880f3d1932fd3d53f0806d
- enhance type handling and uploader initialization logic by @celikerde in https://github.com/unusualify/modularous/commit/31309a21188a96e4a1345536ccf6c604a5d1abb9
- comment out unused VTreeview component by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9d19bcf118c71de063410d1ecf2e8bb95450eedc
- improve role relation handling by using config values by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/b52e29c2010357805e87da9f337635404ed9379d
- enhance table restoration by sorting dependencies by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5ff2701833abc6863e82c79116a2ac579c151185
- rename package from Modularity to Modularous across all files by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e538d90120dac73221f5ab06102c012638be9d9d

### :memo: Documentation

- remove deprecated command documentation files by @celikerde in https://github.com/unusualify/modularous/commit/a36e2d78d7745a2273972f9eb0b57671a8280236
- add documentation for new components in Modularous by @celikerde in https://github.com/unusualify/modularous/commit/8fa55a79734bb40f4f304f915331c89a0122747a
- add comprehensive documentation for new console commands by @celikerde in https://github.com/unusualify/modularous/commit/7c4ec674ee27762afcb20ecc2258d467177d71c3
- update sidebar positions and correct typos in custom auth pages by @celikerde in https://github.com/unusualify/modularous/commit/f9b10895442286894c1d6b9b3b2687e72766ab40
- add comprehensive documentation for new input components by @celikerde in https://github.com/unusualify/modularous/commit/369067301bd731a64a0199f4c79c6c78074729bf
- reorganize and enhance documentation for generics features by @celikerde in https://github.com/unusualify/modularous/commit/65ca3ff7556fec7ccded915fbb3ced82276af8ee
- update sidebar positions and add overview documentation by @celikerde in https://github.com/unusualify/modularous/commit/6b762d0d71331e72cce8cff267eaedc904c6e163
- add comprehensive recipes for CRUD module, custom input, file uploads, and state machine workflow by @celikerde in https://github.com/unusualify/modularous/commit/96ce234efc1cd546800f47b08c4f2a0adda9d675
- replace index.md with overview.md for improved documentation structure by @celikerde in https://github.com/unusualify/modularous/commit/c33c914a473abec7a8d88527a9c5a15f815b4df2
- update project references and improve sidebar structure by @celikerde in https://github.com/unusualify/modularous/commit/f429936d18aac8795165e09cdc71270ea21a7176
- update project name and links for consistency by @celikerde in https://github.com/unusualify/modularous/commit/ba8a10ef69f8238efdbd7aa30c72a636a5a8c9f6
- add DocsAuditCommand and corresponding tests for documentation consistency by @celikerde in https://github.com/unusualify/modularous/commit/119eccd04372c6b8c232045a1eb2b898e8cdbb12
- add detailed documentation for ModularityActivator and ModuleActivator by @celikerde in https://github.com/unusualify/modularous/commit/0efe54ce71a0cccc85edbcf8bbb7fd3557fde8bf
- add comprehensive documentation for registration brokers by @celikerde in https://github.com/unusualify/modularous/commit/e61480466aa51033878f062327365cea8bc1c967
- add comprehensive documentation for various entities by @celikerde in https://github.com/unusualify/modularous/commit/fb3fac46f492fb74fd888eb0b8ed1b4879896cfc
- add detailed documentation for entity traits by @celikerde in https://github.com/unusualify/modularous/commit/aee7e1c83ff4ceaf54eae9307003b531ca754ca1
- add comprehensive documentation for events and listeners by @celikerde in https://github.com/unusualify/modularous/commit/b60e87920e8a07afd6543680dc4c46c8642ef753
- add comprehensive documentation for Modularous facades by @celikerde in https://github.com/unusualify/modularous/commit/a70266920722b2d5229ef50f5269ac1b07e62c30
- add comprehensive documentation for Modularous generators by @celikerde in https://github.com/unusualify/modularous/commit/c6e948223ec5bdd80b82af5ec5af8d9a23e5bf93
- add comprehensive documentation for Modularous helper functions by @celikerde in https://github.com/unusualify/modularous/commit/173b9dbdb8d3398998bf09ac2482360bfe17358e
- add comprehensive documentation for Modularous controllers by @celikerde in https://github.com/unusualify/modularous/commit/e0580ec594c45ed4d8009a4126e7dfada8f50cb1
- add comprehensive documentation for Auth and System notifications by @celikerde in https://github.com/unusualify/modularous/commit/4df234cf164dd651b114832f3a70213c0b614267
- add comprehensive documentation for Modularous service providers by @celikerde in https://github.com/unusualify/modularous/commit/32379983a3bbe3372f1bb1ae8381cd9cebf8ca4d
- add comprehensive documentation for repository traits by @celikerde in https://github.com/unusualify/modularous/commit/9595820ece62ec43dd004eb599d17fc6f8f45484
- add comprehensive documentation for ChatableScheduler and FilepondsScheduler by @celikerde in https://github.com/unusualify/modularous/commit/808229c28c730196e8f4452120e32003272384d9
- add comprehensive documentation for Modularous services by @celikerde in https://github.com/unusualify/modularous/commit/6031c5b3eabc935047f537608d448f17bfb4362f
- add comprehensive documentation for support classes by @celikerde in https://github.com/unusualify/modularous/commit/fb0b1073455f8ff52f00140ffda47a7426d9b796
- add comprehensive documentation for backend architecture and components by @celikerde in https://github.com/unusualify/modularous/commit/0bdcdaad9bf8501eda3f5bb9222980921b133ead
- add comprehensive documentation for table composables by @celikerde in https://github.com/unusualify/modularous/commit/3e6e18422ed4195c908ed01b32f147a133c22c90
- add comprehensive documentation for frontend structure and composables by @celikerde in https://github.com/unusualify/modularous/commit/d81c7dc8845bb47d84526eb385b6fd852f30ed99
- update references and correct naming in documentation by @celikerde in https://github.com/unusualify/modularous/commit/7656d3a640f6383819455fb4f5c6c1b364027770
- add comprehensive configuration documentation for Modularity by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5726a99c0e485cff87eb3925316a8dd48ea2ede9
- add documentation for module system and structure by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6a97ebc2e478016378b7fcc1bedf5839d6eaf3e5
- add overview documentation for security services and CMS routing by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6685e8d1ad6c2556325ebe32ba405d7a37bd4a92
- add initial draft for sitemap system requirements and implementation plan by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/76e7046f1ec8cdca6f1aa4dcb6cc5a6bb50217da
- add migration guide for transitioning from Vuex to Pinia by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/966ecce86a3050e4aab7949f0b3a4107a94a3431
- update README to enhance Upgrade Guide section by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/641302bac0729d3c0419b4aa1d7e0ce3a5a0330f

### :white_check_mark: Testing

- add comprehensive tests for Revision and HasRevisions traits by @celikerde in https://github.com/unusualify/modularous/commit/300b757df2b50290d92db336a8cfe9a599c56127
- add unit tests for MFA and field permission checks by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/4b825469f997e146270f550d62241f9437949741
- introduce comprehensive frontend test plan for Vue package by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/2de8f9caa1c54b922b5c7a6a7d44171eb33652f2
- rename source_revision_id to source_id and update related tests by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/79e121e6f443eaa83e7be86874d5d19378b3d306
- add phpunit configuration file for documentation tests by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/623969b6b66f0ec95623053dcc9d93e245e80260

### :package: Build

- remove unused assets and files from the modularity package by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/67c6a96be8c2690c36a96ef9948e209e4e65d546

### :green_heart: Workflow

- add CI workflows for Node.js and Laravel across multiple versions by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/eb09871cae373cbcf7bdef883403a79651b95117

### :beers: Other Stuff

- add pragmarx/google2fa package for multi-factor authentication support by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/91011b91baacfdf5c7e486be26c837431c0699dd
- improve type hinting and code formatting by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/3789e89df1b1ebc125ccefdddcb49a8b3bdfaf7b
- update max line length setting for markdown files by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/c6f977ec7a7a11febd5ad8ba41fb744aa5b3ebab
- update dependencies in composer.json and composer.lock by @celikerde in https://github.com/unusualify/modularous/commit/9c130609e6c77137efe1948fe52e04fd1cc6b32f
- update package versions and lockfile format by @celikerde in https://github.com/unusualify/modularous/commit/f6cb78c7eb399c7bb708950b78613644d71d1b82
- update dependency versions for improved compatibility by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d45a6397de6a51c05665f754b6ab99b84fe7f66d
- update awesome-phonenumber to version 2.73.0 and add license information by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/a25bd5247dab0b78a37e3cae62880cddedfadb72
- update command alias from 'unusual:dev' to 'modularity:dev' by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/79bd909ac08b46869cd96577a98816597bc2540e
- add upgrade guide and rename script for Modularous migration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/42e835c26b1f7a3af19f8f781d0ef35982f3e3e0
- update branch references from '0.x' to '11.x' in configuration and documentation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7939f177f9399b823cd2c634ac1a633bf68b09c6
- update issue templates to include affected version dropdown by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/ebd3b9e8aa32d6a9b7156d07f0e4d51520f5339b

## v0.58.3 - 2026-04-01

### :wrench: Bug Fixes

- merge formAttributes without losing in AuthFormBuilder by @web-flow in https://github.com/unusualify/modularous/commit/b95edf9a477fb5c9cc9616d1288037a191139535

## v0.58.2 - 2026-03-28

### :wrench: Bug Fixes

- remove unused transfer_details attribute by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/33b87e329779e58cbb180c3dacdd3d7544964833

## v0.58.1 - 2026-03-25

### :wrench: Bug Fixes

- remove unused initialization methods by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7b14079a423ba3bc15cd4247a0a82049051e8bc1
- update amount formatting and enable additional appends by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5407dae9a0785c1c6818116b58a2a1176535c69d

## v0.58.0 - 2026-03-24

### :rocket: Features

- Add comprehensive test suite for modularous components, traits, services, and support classes, alongside minor fixes and configuration updates. by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/05376bf48a314a671aff951d95d2f9e29a271f15
- add 'test' option for enabling test mode in module generation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/2f614360c7a53fb04e6f62bbd431b21f5c682dfa
- add setName and getName methods for improved name handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/047f8979dc97dd923eb5cbe35cac8aa6deec0dcc
- enhance path validation and improve glob to regex conversion for safer file handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9a98b3863ffd26f9039c168188b310384514bf5c
- add deferred configuration for auth component and pages by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/37682eea30a3bdc15dd434f63d784dff21c77795
- add 2FA login routes for enhanced security by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/aacf4bfa7244f3200f385c7f37ce6dc80e6d0e6b
- add publishing for modularous authentication views by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/b0ee58ad0719a88ccbc36274a26e11999c0050bd
- remove unused SVG icons and update existing ones by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5c51fd028fbf52d2c1c22694315934d2eb5ba5bd
- add default font size variable and apply it to html element by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/acf321b998210bed8dd57667b774d26b47ce8325
- implement user interface preferences management by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/64540b64be87a0d18a87d7f0fa45024cb5751d42
- integrate Ziggy support into Vue application by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9ba8ab305487153b8a2136128bfb448a9148e019
- implement command discovery functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/cb5c56d3bc3afb92795dacd0f4bb94d385644556
- add command to list route enable/disable status per module by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/213572b70ac70dc6bf074dc69ed1a915cb27f2c8
- add custom exception class for modularous errors by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/456cfa4b1ee1ab21c3b58824fc4cc2901d087d63
- implement currency provider interface and related services by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9a3e90538b4784feb6607e638579b96e21f7a25d
- add optional currency provider configuration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/b5b477162f71e1345936a28f1c7e89488155ef43
- enforce Composition API style for new components by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/b61e18eb586adcddbd8fafbec044f46fc7629e7f
- enhance test coverage configuration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/3d537fe8ad44ee21f3c548c14dd6a0f3b6c6e8bc
- introduce InputRenderer and registry for dynamic component mapping by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e3a1d1ba6850115dc076e07af126d5ab3dc344ce
- enhance item action handling with dynamic target support by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/784fb922c5b0724af6e40fe9d2a7de73e0a91f4f
- implement client-side grouping functionality and enhance table formatting by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/cc233cc9e7c80c4ab93ab66560220a8a8cead03c
- add static method for retrieving trait methods by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/b98ae306e0a528b486c2aa72290fd105487bb11e
- enhance user role management and company existence checks by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e4c0d90449d3e6992cb61d2703910d52b71002fb
- enhance global scopes and existence checks for various entities by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/fbbb672b87158a55bcb617eb1dddf6999c0342ae
- integrate new connector handling in Input and Repeater hydration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7ae916e11645b104b48cd369e291793d227edbbf
- integrate ManageAppends and ManageWiths traits for enhanced data handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/8d08766f929ee6526a933f7461d198d58201bcb8
- enhance table handling with new eager loading and custom row features by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/54af77dfd0de76991f8de895596bfdd52d6f4f5c
- add global scope for paid status and improve payment status checks by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e80507cc0459d8b25e036015f00beb1265d5f27b
- add configuration option for item eager formatting by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/89ae1fd9b3a3bb75cadb020d953d057bcda1fcbb
- add ui_preferences column to users table by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/c6fdb7a40e7c7b57d0965d5041f106c44c67ec50
- enhance company configuration and validation display by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/4af0cee06731d9a4e92318bfdbb3becf5b1c957c
- add fixed last column feature for improved table usability by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/1eaeee6558333b7a06d75ee8faddd8e7be9631e8
- enhance form fields retrieval with relationship appends by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/86865cc4e25117063a948a48607c03d99a623232

### :wrench: Bug Fixes

- update package name from 'Modularous' to 'Modularous' in AboutCommand for consistency by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f391f425e1158a7ca2ffbb7d9a55ffe8b2bbb228
- check for repository existence before handling migration batches by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d55abf229d7d318738ddc36f8c6e36772b21e5cc
- correct file convention typo and update return types for clarity by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/3d98ccc699098450ada3c55954e1fa7387484afc
- adjust installed path resolution for Testbench compatibility by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/ed29587e5879049d746588ce90601c7618e275d7
- update Turkish login title for clarity by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/8d44e4a95294c3691e6a0576ebfb007068f4f364
- enhance schema input source loading check by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/3d8a15980c8a4dfe7fd7753da8c3db76b8513df2
- update user creation method to use updateOrCreate for better handling of existing users by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/cc77676460df248546e88cba6ee92383740ce47b
- ensure columns are set correctly for list method by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/02ab929558967296af808f8d55aa101af2a754c4
- add routes configuration option for additional API resource routes by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f068ae9e12e418b2b040902a4b71dba9ab01039a
- handle missing media locales gracefully by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/529ea25b5b2c76741ad355f1b24cc93797ccc8e3
- add paginate method for dynamic pagination based on request parameters by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/c37aa0bc7decc446de7bd0f670200d0b6585ff9a
- standardize capitalization in provider confirmation messages by @celikerde in https://github.com/unusualify/modularous/commit/7c431b9401de774721adc8f964c500ea58c42b6c
- update provider confirmation message capitalization by @celikerde in https://github.com/unusualify/modularous/commit/00b8344d68b667766be7b7476eecc1b26f473e16
- update benchmark function signature to include elapsed time reference by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d88cad2b2fbb8e4c38e1864fb3dece16140ef4bd
- update getFormActions call to include context parameter by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/ffc119b5b5a3924a80e54a922c05bf201bfe4264
- correct equality check in initial value mapping by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/01f245c92194dccec8bd44789f358cbf68a79b1c

### :zap: Performance

- enhance form action retrieval logic based on modal settings by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6c4623bdf33d1fbee802350c02608c91a7987458
- improve form schema handling and relationship mapping by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/8098eee696e1c6b43bdffd2f326c900d81021343
- add conditional form handling for modal editing by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/2eb5a73ac228a21ea19aa63a3ee995ad359cc407
- enhance eager loading and relationship handling across models by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f39d6c5dda4840667906b502ad52bce7858f9c5b

### :recycle: Refactors

- streamline ModuleActivator initialization and enhance caching logic for module statuses by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9832621f44599d2dffb67e8e1ee857a52357afbb
- simplify module management methods, enhance authentication guard/provider retrieval, and improve caching logic by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/82f1dc82a606f4dc500ad1af602548cd23b3bfaf
- consolidate object and collection handling in run method for improved readability by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/61228a901b0c87bfa308388017599230ba34b320
- replace deprecated Config facade with helper function for retrieving view paths by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/c0494b95b48cc9441543b18b5c12a0e5be6dbe23
- remove backslash from Cache facade usage for consistency by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e7a4e897e2608fa9268cb22b18169953077bfaf6
- streamline stub base path configuration by trimming trailing slashes by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f9c75ef973041c58b822476e9f1466b35318cbcc
- remove unnecessary blank line for cleaner code by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e7b3e4d8fd26813c513425ad7e52301b8fa989f5
- streamline method signatures, enhance clarity, and improve handling of module and translation settings by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/cbac97ec0c3d68911c2af7f2a6fa535f2212199b
- replace glob with RecursiveDirectoryIterator for improved file loading efficiency by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/75504203e00b1bb2670e9b42accbbfbbb819333e
- clean up getModularousTraits function and update return type for activeModularousTraits by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/38a0706e0b06908112c3c981649089c05c1cca16
- replace ModularousActivatorTest with a new implementation using Mockery for improved test structure and clarity by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/0fee8a95b13562225d30923e1b232c7edb300d36
- update exception handling methods to improve authentication checks and visibility, ensuring proper handling of HTTP exceptions by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/c2be79b857330df3998c880ab6128fe0cc46aa57
- remove deprecated controllers and implement new registration flow by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/cd8aff5f7e34b13d6410adb0615c8ac9c898fcd3
- correct class binding for bottom section and button alignment by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/2a26e1d8b27de59ba955501dd1777deb3d85304e
- update field lengths and types in companies, chats, and user_oauths tables by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/079217616e33f1e0d7559a76819f3d32a096fa36
- clean up code and update method return type by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e8ddae74bdb27beb20211bd2427a7d3d47cb1220
- improve hydration logic and handle reserved keys by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/56cf385676665e5f445153f92b5742cd5e077850
- introduce console command naming conventions and update command signatures by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/ba35bd77c3e3611302981310a9c69059ca94a9d9
- replace debugging code with structured logging and custom exceptions by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/17631c850afa7c5830801a9a3fa9fb0ab036398f
- replace debugging with structured logging and custom exceptions by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/aeb4eb902846d258fa6bf3a35c71670cf4707bcd
- enhance route middleware handling and type safety by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/77796ad4f3edd1d0b0453114cf66edc491fc1760
- improve type handling and method calls by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/0bd11a3b1def352ebc3e8b8b6a3376c3217705a5
- add setComponent method for improved component name handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/b68d9f533af46ac93930d00f7770b0348bdf1938
- enhance error handling and method naming consistency by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e972b921f4101dc22f38e9442544c9fddcb875fa
- extract AJAX handling into ManageIndexAjax trait by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/c90258dbc9abab77cf8fe21fbdb730aff15bf32f
- update form validation and component organization by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/12f5642873da3150b73c7ef4bea45f58289bba57
- simplify constructor by using app() for dependency resolution by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/a9e203aa4ccc15e4d109fbd83519aad5eda5ae15
- remove unused API controllers by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/903e3757074b0d5dcddf200fd16edc9131c79a14
- remove unused SCSS files and mixins by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/da4ab10093fcfbd8e8b1a0de62ced376dce915d0
- update modal binding and integrate useModal hook by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/dd3eba1f6d764b8336e606d12860ff637a65dfd2
- replace mixin with props and useMediaItems hook by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9c8e91607091e68d2bb9cf5e4e4b209c81844fa0
- replace mixin with useLocale hook and streamline state management by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/489a2f12b20c756ab1c96a93cb55d8c2c91da33d
- remove unused mixins and integrate hooks for improved functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/0ff6656598369cdfb190c0a57cbc36d8bf99ed84
- enhance functionality by integrating media library hooks and updating props by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/cb9d865202dcab2b4785478df35ba37e917ad1ac
- integrate new media hooks and streamline existing functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/cbf17bdb398d3951dcfdef10eb6de0160c8f4c59
- modularize global helper functions and improve backward compatibility by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/07418efc078753fa22f36aafeae633e3c8f52114
- update useLocale hook to improve locale management by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/03dcd1a366c070964ad9c12e9fdf037c53ca7386
- clean up preview method and improve output buffering by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d4395d3ba3ac3cbb96083eed0ea866f80d3d8759
- improve table item rendering and formatting logic by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/92aa8711b30aec79c428ba81f6163e8dc02852c7
- streamline sidebar behavior and improve responsiveness by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/12bf202e4274fc55b85acebbfaf62d50cec63d55
- implement cached user currency retrieval and update price queries by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/ce476f1a840a20d20530aac46d0d7209245afe47
- enhance form field retrieval with serialization control by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/70e4f1e98342ebf117e4780e7e66697f29b88790
- optimize authorization record handling and existence checks by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/0463ae08d638265a2516c7ae772c6f8758338eb7
- clean up input data by unsetting unnecessary fields by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9da94ba6fc1995b2f9a922e71cb4d4c7d75eb296
- update user role checks to use new attribute naming convention by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9bbfd04ab5a0729a85100b925948e95b03e68ac7
- enhance header management and relationship handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/64db9a98f05b1f52e503ca3424fce13a89b4af11
- remove PaymentableRelation class by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/881e420266f73dd0a221cb5f24f1b0b72cf46033
- update payment attributes and scopes for improved functionality by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/b704670f3fe92617d9ca402ee685839dc9de472f
- improve translation service registration logic by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/acb8396101f43e20486debb2a6bb3ae5506bd6bd
- comment out timezone configuration in login form by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5329e34085af9764e91ba0323c45c64f48856fa2
- enhance form item retrieval with additional metadata by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7e4c69edef4d306b7e24cd9805796a0ce8be6799
- remove z-index from last column styling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/94b3c5c5b53952d0700feef4c029ff83ec8e43a2

### :memo: Documentation

- add custom auth pages documentation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/bfa0d1c18554acce906babc436d21934f90fe242
- add comprehensive guide for console command structure and conventions by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/0e8064129c2bf5670dc5c3aefab0d50246fcf89f
- add class-level documentation for input schema hydration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/43af2cc65a6268551e9068d9e9bd178ebc5b611d
- update package structure and add schema hydration guidelines by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/b3413111f7840844d1e9c6e330b67f69a44db960
- add comprehensive frontend enhancement suggestions document by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5c7fad61d7115fa838f19d1bdace44951eb5d9b6
- remove default port from preview script in package.json by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f6be1b88032dbfbd047a0e8c4644e32ab972bd4a

### :lipstick: Styling

- lint coding styles for v0.58.0 by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/972f5d3a90561efaf0f6f3d5599f812d2821f96c

### :white_check_mark: Testing

- Update PHPUnit configuration to use custom bootstrap file and enable cache directory by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/db150ec143a730d60249208aeb48e688800d1a2b
- add TestModulesCase class and enhance configuration for module paths and statuses by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/c85b4e304d9adbffdbfdf8af3e4685d5b429f871
- add comprehensive test suite for ModuleActivator class, including instantiation, route status management, and JSON file handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/d80f4876ce55a3f6b76b06078fdc7f020b20d554
- add comprehensive test suite for Modularous class, covering module management, path validation, caching, and URL handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e07d2d362d6ebcc708e891b9626721bba7bf2551
- introduce MockModuleManager for enhanced module testing capabilities by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/a89d2b95d1fc9137714d3fb323d5e155d3b2e3ac
- add comprehensive test suite for module functionality, including configuration loading, route management, and service path validation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/95429e066d2de89b06e225c68dd8f4651563e5f5
- initialize SystemModule with configuration, routes, controllers, entities, and repositories by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/93b14598b6135e4ced6a614a1dd37ae8ca67f977
- add initial test suite for console command functionality, including module creation and cleanup by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5868548e4eb9ef46e18ca9bbd5c29a450a8301fc
- add comprehensive test suite for various facades, ensuring correct resolution and functionality of each facade in the Modularous package by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/0cecd567f5f919b5d57bd1e544de0fbe1873f7b4
- add comprehensive test suites for various generators including Generator, LaravelTestGenerator, RouteGenerator, StubsGenerator, and VueTestGenerator, ensuring correct functionality and configuration handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/06bd188c3c9a3adcc8da4007db28ba05649b4245
- add comprehensive test suites for various helper functions including ArrayHelpers, ColumnHelpers, ComponentHelpers, ComposerHelpers, ConnectorHelpers, DbHelpers, FrontHelpers, I18nHelpers, InputHelpers, MediaHelpers, MigrationHelpers, ModuleHelpers, and RouterHelpers, ensuring correct functionality and edge case handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/51212cea17701f5ee6fb9a9be9e77f08ab31fd9c
- add unit tests for NavigationMiddleware functionality, including instantiation, request handling, and sharing navigation configuration with layouts by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/df44786fe9a0e3bfccb1f7bde2da58f9d641e059
- add comprehensive test suite for Listener functionality, including mail configuration handling, notification path management, and event processing behavior by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/20e5f067afe697951093901ed8f3bc97f029e79f
- add comprehensive test suite for ModularousLogHandler, covering instantiation, log writing, email notification behavior, log rotation, and message formatting by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/2e9069af0d217ea270c8911e089b8a8036ac52ae
- add comprehensive test suites for EmailVerificationNotification, GeneratePasswordNotification, and ResetPasswordNotification, covering constructor behavior, mail channel handling, and URL generation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9a545c8808d0395fd1d015226d64a3b4c73bf42f
- add comprehensive test suites for ChatableScheduler and FilepondsScheduler, covering instantiation, model processing, error handling, and logging behavior by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/54290eaedd865193c6bd0ebcc1e7f0d274811c64
- add comprehensive test suites for various services including Assets, BroadcastManager, CacheRelationshipGraph, Connector, CurrencyExchangeService, FilepondManager, FileTranslation, MessageStage, and ModularousCacheService, ensuring correct functionality and edge case handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/226d2c99cac3643a9c14f75c85ca9de0dec00944
- add comprehensive test suites for CoverageAnalyzer, FileLoader, Finder, RegexReplacement, ModelRelationParser, SchemaParser, and ValidatorParser, ensuring correct functionality and edge case handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/75f61c1064f38191644534c5632e4cd6ed333e4e
- add comprehensive test suites for Cache, ManageNames, ManageTraits, Misc, Model, and Relationship traits, ensuring correct functionality and edge case handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9e87a5a3665a243efc81be9cf613c7dfed8931be
- add comprehensive test suites for AuthConfigurationException, ModularousSystemPathException, and ModuleNotFoundException, ensuring correct exception handling and message validation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/3cfcf7455e22804bee75ba368e79fd84ef317b2b
- Exclude StubsGeneratorTest from PHPUnit configuration and disable the coverage facade test. by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f2912e44e3bda18ed5239b36b80997c34b49e8c7
- add comprehensive tests for authentication controllers by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/8c9a06187f6f90d50a749e1bbab3b3a3fcf980b1
- add unit tests for Main, Sidebar, SidebarContent, and navigation hooks by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/98a49c6141da25db074ae29182163526c118b497
- handle changed default use_inertia behavior by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/b422ef24117427118937b61888f25ffe16680ec3
- enhance test coverage and add new tests for various components by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/3aef10b3300cdde8e0896acc29484e8988077eea
- add comprehensive tests for LocaleTagsCast, CacheObserver, and enums by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/01afa2e62cc2a1d7d29cb6e5f47454475e8adee6
- enhance jsdom setup with CSRF token and utility helpers by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/ee9a71fa1f95cfe2b04a0eb493617801e8dbeec6
- add comprehensive unit tests for various components and hooks by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/61cfa697c6a68a82de401a7fdb98f9aaccfddb73
- add TableEagerMergeTest for merging indexed arrays by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/a22da88f3ddd776d8c3279b6852eb0ad974bd6b5

### :package: Build

- update build artifacts for v0.58.0 by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/4b9e4d008fccd545fdb1b4207be7e08b7de59700

### :green_heart: Workflow

- update Laravel test command to `composer test:fast` by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/714c51b80af117c1abd9388c60f279bb5fcc96d9

### :beers: Other Stuff

- update composer.json to include paratest, adjust test scripts, and update package versions in composer.lock by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/ce6a483c8371d9f8cf3fd301673b1ccbd8f810e0
- add /tmp-modules/ to ignore list for temporary module files by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/160dbc9e76adee80e8e415f1b7f28c824de55921
- add create and update lifecycle docs by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/be154b2458584769ff673bda445aa64450b10aba
- update asset references and remove deprecated files by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9bed6d1146a69f7bee7a1ceda7c9010e098d46a5

## v0.57.4 - 2026-03-03

### :wrench: Bug Fixes

- change minimum length requirement for email validation from 3 to 1 by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/54c35f9937faa7bc01049f17cc87cb2b3152bb22

## v0.57.3 - 2026-02-18

### :wrench: Bug Fixes

- ensure default value for dependentWarmingEnabled is true when not set; refactor Repository methods to check for preventDependentWarming method before calling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e17dbffe963d66fbfbcf5fe771b5cbcaa82d1f5f
- enhance transfer button logic and track transfer completion state by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/4783d918cec686b96024dc6f8f3c6797dbf2e62b
- set default company type to 'system' if not provided by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/34d888d8ad56af7ee9c3032e4b850c7de745176a
- ensure Eloquent model is touched after saving payment by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/1a443d93c5c1e0e0bf1d88b1286a2adc8f3f9ecb
- update payment conversion logic to handle modularous payload and currency conversion more accurately by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5cb297408e9c5d45eb20a6d8efbfe79a08240a58

## v0.57.2 - 2026-02-07

### :wrench: Bug Fixes

- update package name references from 'modularous' to 'modularous' in configuration files for consistency by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/882d336944ac7e721e7e34ff1a11c80562e2a1f0

## v0.57.1 - 2026-02-06

### :wrench: Bug Fixes

- update package name from 'modularous' to 'modularous' for correct version retrieval by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/4c3b2fafdce971e3e8f06ed7a76a5f64db5be8ba

## v0.57.0 - 2026-02-06

### :rocket: Features

- add RedirectsUsers trait for handling user redirection paths by @celikerde in https://github.com/unusualify/modularous/commit/ddcd7dd144364ba6489c490f32503d625b634cd0
- add tests for email registration and notification sending functionality by @celikerde in https://github.com/unusualify/modularous/commit/1d314f6b7ef7f88d8b9afe0b9bc6f87813a0278f
- implement log file rotation and dynamic log path configuration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6dcff85de160cad10fe05094abe0a9d50c993b79
- add failure handling method to log job errors by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/4c962c34ffd9f31dae94654d9c323ab3a0496346
- enhance issue branch creation with version detection and customizable dev branch input by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/7571d830e6f7f6795ba670c3c3420373d503d33c
- add modularous_path function to retrieve vendor path if not already defined by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/4456d13487fd7cf558ba3d39ccd432e30f582e40
- introduce coverage analysis service, facade, and configuration for enhanced code coverage reporting by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/426f5eee999a6a03d1596b9d3e77d4ffcc167259
- add commands for coverage analysis, PR checks, report generation, test generation, and real-time monitoring by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/073286731527f86d0fcb63da704d4b75e96fa7ce
- add transactionSnapshot method for detailed transaction data by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/eb536d6683237d8450af5bef9cd692c942014d02
- implement collapsible panel structure for improved item display and interaction by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/23e86bf9b36abe09f6635428f906914069f409bd
- add transaction summary to language files and update transactionSnapshot method for improved data handling by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/66788017f2ca84de6254f412428c0db70dcf3b93
- include 'paid_at' timestamp in transaction snapshot for enhanced payment details by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/10354bb0eeb3500ed82d988df77374616d13a78c
- enhance item formatting with conditional rendering for formatters by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/eee0595f82ca5cea2a23ad9d2fe2d41efe71fdf9
- add Traitify trait for dynamic method and property retrieval based on trait names by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/5758840bb12d6da01ba5d229a72a3c131c8ffbd8
- override newInstance method to propagate dependentWarmingEnabled flag to new instances, enhancing model behavior with Traitify integration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/3c34a52bc8e74a4561f037935283d558b8f2b165
- enhance cache invalidation logic with options for dependent warming control by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/8c2228dc284c7530bd99b22038f9ff4f7d4c1968
- enhance newInstance method to ensure dependentWarmingEnabled flag is propagated to new instances, improving model behavior consistency by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/4db05e9e460acf275fc07db56b3fea04f1d798ac

### :wrench: Bug Fixes

- enhance cache key structure for form and formatted items by @celikerde in https://github.com/unusualify/modularous/commit/2047bb2df73fe4cf9e3b5154e29b68449cacd6d4
- standardize cache key format for form and formatted items by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/1ee78ff14e12609365f1e58c0ff467f16ce85f59
- emit events for success, error, and cancel actions by @celikerde in https://github.com/unusualify/modularous/commit/170ea399547fc8a10cd2fce4fc8700fcf043f978
- update CastPattern regex for improved validation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/49973d11a151667fad45abcfd80aee9e90b2a0ec
- disable currency selection during form loading by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6499cb222e9a5156bf74a7f35bcfb36d5d4d80f8
- comment out cache invalidation logic and conditionally warm up cache for non-new models by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/ac0daa213f4237c6b9ca1a700f007e96adb910a6
- restore cache invalidation logic for model creation by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/8ce71f6d46fb5b61151edc9e163593d2afa061a6
- refine cache invalidation logic to prevent unnecessary cache clearing for newly created models by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f23e0132e1ee39d8de7af47b3ccf97fb1f15cecc
- simplify custom row data handling by removing role checks and ensuring unique item attributes by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/48e626642c30168a38744e5cf515d21118147c45
- update composer package name from 'modularous-dev' to 'modularous-dev' for consistency by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/8606d880a6668c20cfa95538a53ee821e961dd76
- update vendor path resolution to support both 'modularous' and 'modularous' packages, throwing an exception if neither is found by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/1305cf21f2fbece69a4c699e82f9bff16f08a192

### :recycle: Refactors

- remove redundant authorization update logic and simplify user retrieval method by @celikerde in https://github.com/unusualify/modularous/commit/e43e62ffa29291cd0fdfe433556ab8a9df3d2462
- extract modularous log channel configuration into a separate method and add notification failure log channel by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/f3fdbe9d0d4a26cd8fcacb66773b2178b937ca11
- remove unused test suites from phpunit configuration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/85166ba65977fb286c9d7d6454d7511c99eebca9
- remove debug dump statements to clean up cache observer logic by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/500c9df4b186bc19b8e4f0ed87f52783166ff48e
- consolidate trait methods and properties, integrating Traitify for enhanced dynamic retrieval by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/c9a8b6b7199ef01f0b5bfbdffff81cf32d620dc7
- remove commented-out code for clarity in asset path configuration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/98b4e779f0f0d2ade2e1c2419b281873e0be77c0

### :memo: Documentation

- add comprehensive guidelines for Modularous package development across various components by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/05e48403ed5d476fb00869130e4e2f145cf3c0e7
- correct directory path for test files in guidelines by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/e330e631799814ccaf138d0ce5c9c4aa1a023c49
- add context file outlining package purpose, core concepts, and guidelines for modular application development by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/94254b3e71f47cf9311e2648d3bc00fa7be8a43d
- add structured patterns and rules for modular application development, including module structure, service provider patterns, and CI/CD guidelines by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/33154692912c656c02f32246b70bc790e4338a61
- add template for coverage-driven unit tests for unusualify/modularous package by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9828d5c7e4262b9fa6fc7425e38a69fad108615b
- remove outdated rules and guidelines for controllers, models, and repositories; introduce new modularous development rules by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/db5dcfeba7bec996a9bc5bb05ff2088568a3f8a9

### :lipstick: Styling

- lint coding styles for v0.57.0 by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/9265fb098f8dd11be34f9cc8ee0bc96a59cefdb4

### :white_check_mark: Testing

- add test for assignments deletion upon force delete of the model by @celikerde in https://github.com/unusualify/modularous/commit/a50d185e9e2157f31da26063c39c19f012178984
- add tests for custom macro functionality and PostgreSQL ILIKE scope handling by @celikerde in https://github.com/unusualify/modularous/commit/c0ebf8eabb4769e6a52ad4b3aa4e50fb3a5fee59
- add tests for saving event without model authorized type and query scope without user by @celikerde in https://github.com/unusualify/modularous/commit/73bbebf4b679b263cabd24df843fe1883743e5b9
- add soft delete functionality and related tests for creator record management by @celikerde in https://github.com/unusualify/modularous/commit/ec334c9e2d51ca628ae912c7fb0aee060a1d5664
- add tests for managing fileponds state changes including addition, deletion, and retrieval by @celikerde in https://github.com/unusualify/modularous/commit/2f0a1e06f7f4896509ed7e831b60267b8c9a1435
- add tests for model update events and handling of non-existent image attributes by @celikerde in https://github.com/unusualify/modularous/commit/06239c38fccc312b03ef070b1e3c9a1dccc157ae
- add tests for handling chatable notifications and booting chatable models by @celikerde in https://github.com/unusualify/modularous/commit/f7e835c65f4e2e7c0bec31ac2cfdc56ce269b0c1
- add tests for original base price relationship and language-based price queries by @celikerde in https://github.com/unusualify/modularous/commit/3c2411d3ccea74ae1e044c0e7be74f31b368a6e6
- add tests for formatted price attributes excluding and including VAT by @celikerde in https://github.com/unusualify/modularous/commit/e37ad32f408a327e9f786e917b31c5ce6e84f898
- add tests for creating, updating, and managing spreadable models with payloads by @celikerde in https://github.com/unusualify/modularous/commit/c281754806fe019bff706f5c9944a3d6c4c8ff78
- add tests for retrieving state attributes and initial state handling in stateable models by @celikerde in https://github.com/unusualify/modularous/commit/e9a6e917d01acc67e675d1c295200bd84164357d
- add tests for translatable model activity logging and batch handling by @celikerde in https://github.com/unusualify/modularous/commit/8debb40941e5fe3df27504a37a33c2006f3a475c
- add tests for state hydration with localization and fallback locale handling and other methods. by @celikerde in https://github.com/unusualify/modularous/commit/deda1eaced2c94a8958fea10d83aaffe2d4798c3
- implement comprehensive code coverage analysis and integrate AI agent rules and CI workflows. by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/c456349e79577181705a3350e2260ae895e9c65d

### :package: Build

- update build artifacts for v0.57.0 by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/ef86bb9c49c35766b5017e8b3d674b72115823c1

### :green_heart: Workflow

- add scenarios for issue branch creation testing by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/372fcc978425b84dc557a5d224a60e9eceab7670
- rename automated actions to autonomous for consistency by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/4595ec0ce2fcaa996c71e2ff089a7389303ff448
- add CI scenarios for push events including security fixes, new services, and documentation updates by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/212dc5a6cd240e7674690a7ade77d308b16cf43d
- add CI workflow for PHPUnit testing with coverage enforcement by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/246585b8f6ea3e86e98dce776bc0b7d192b66559
- update pull request branch from main to 0.x in CI workflow configuration by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/35f30c3746189fe9ba5304185db83a6085ba0355

### :beers: Other Stuff

- introduce new modularous rules for controllers, models, repositories, and frontend standards by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/64594fa1cf906bf87affccb580c9ecabb5c24bf8
- add .taker/ to ignore list for AI-related files by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/60c39608a49a0b9fb63b28f57e9b7d92e468e9ce
- rename .taker/ to .modulai/ for AI-related files by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/6ad775a5eabe7888a3ab88168db16f9e284165cf
- update package name by @web-flow in https://github.com/unusualify/modularous/commit/562bf4aa10cad0d7d61fdc900c253ada868a5c12
- fix JSON syntax in composer.json by @web-flow in https://github.com/unusualify/modularous/commit/09d175f2c497c717c2dbf00ced785d58cf0120a1
- update references from 'modularous' to 'modularous' across documentation files for consistency by @oguzhanbukcuoglu in https://github.com/unusualify/modularous/commit/73d63155e5e665e79b3ba845a80868ac2a2b5ac9

## v0.56.7 - 2026-01-28

### :wrench: Bug Fixes

- adjust middleware for registration route and enhance session check in ImpersonateMiddleware by @OoBook in https://github.com/unusualify/modularous/commit/eadb70d3cec065a293bc7ab5b7d1e700aaa2b645

## v0.55.6 - 2026-01-20

### :wrench: Bug Fixes

- refresh model before invalidating cache to ensure accurate cache state by @OoBook in https://github.com/unusualify/modularous/commit/2a90e55d3b16eecf59f046dcaa99c3018958b7a1

## v0.55.5 - 2026-01-19

### :wrench: Bug Fixes

- improve retrieval of creator information for payment payload by @OoBook in https://github.com/unusualify/modularous/commit/c98f9ac638ac7c26b5623f856e1e29eff393e75c

## v0.55.4 - 2026-01-16

### :rocket: Features

- add conditional touch for models with mustTouchable property by @OoBook in https://github.com/unusualify/modularous/commit/c583ef95b47a4b65e129372cfe54056a533d25b3

### :wrench: Bug Fixes

- reset model state update flags after state update to prevent unintended behavior by @OoBook in https://github.com/unusualify/modularous/commit/e133dfa916caa2e0d17647b4992393a46abf900c
- add email and custom_creator_id to payment payload for user verification by @OoBook in https://github.com/unusualify/modularous/commit/6ca50f0458e87eeaac23a48771a8539c64d42340

### :lipstick: Styling

- lint coding styles for v0.55.4 by @OoBook in https://github.com/unusualify/modularous/commit/bfa7c02353624c437f411292e76f038ca3502440

## v0.55.3 - 2026-01-13

### :rocket: Features

- include locale in cache parameters for form items and table items to enhance localization support by @OoBook in https://github.com/unusualify/modularous/commit/45032133306b58b7f626295a3608b1802f929359

### :lipstick: Styling

- lint coding styles for v0.55.3 by @OoBook in https://github.com/unusualify/modularous/commit/8c3aac40bc6e62da22672b2f79ed2082a9ceebee

## v0.55.2 - 2026-01-12

### :rocket: Features

- add enabled currencies configuration and scope method by @OoBook in https://github.com/unusualify/modularous/commit/5b5701ed781e354afe6874367821b28a8767216f
- add getConfigFieldsByRouteRaw method for retrieving raw configuration fields by @OoBook in https://github.com/unusualify/modularous/commit/8b8661ca81c5970ec89a604e0dba7ed702269324

### :wrench: Bug Fixes

- update currency item title and modify items retrieval to use a connector for better data handling by @OoBook in https://github.com/unusualify/modularous/commit/fc5618fac49095296beef127a90c9776e6956752

### :recycle: Refactors

- replace getConfigFieldsByRoute with getConfigFieldsByRouteRaw and introduce calibrateFilter method for enhanced filter processing by @OoBook in https://github.com/unusualify/modularous/commit/6955053283bf7222cbac50842064243d1acc340e

### :lipstick: Styling

- lint coding styles for v0.55.2 by @OoBook in https://github.com/unusualify/modularous/commit/6ec8e44b66b36162fbb15eaf678c28c8f9db3e3e

## v0.55.1 - 2026-01-12

### :rocket: Features

- add WellPrint component for formatted text \n and hypertext links handling by @celikerde in https://github.com/unusualify/modularous/commit/c9d5ec9f54e0d423ed54486737221da74889cb79

### :recycle: Refactors

- replace HTML rendering with WellPrint component for message content display by @celikerde in https://github.com/unusualify/modularous/commit/0a3a7a17afc31022f301e6447063493a21b426f8
- enhance message title rendering with WellPrint component and propagate additional attributes by @celikerde in https://github.com/unusualify/modularous/commit/858a4dfb919662f1c96531c649e15eeddf774078

### :package: Build

- update build artifacts for v0.55.1 by @OoBook in https://github.com/unusualify/modularous/commit/91bfada34db2f5c47a4327e8168b3ae0e40da92d

## v0.55.0 - 2026-01-12

### :rocket: Features

- add getController method to retrieve main route controller and improve route handling by @OoBook in https://github.com/unusualify/modularous/commit/efed0a17c7a4d0b15cf25a9b9d26822f130beec6
- introduce ModuleableInterface and Moduleable trait for module management by @OoBook in https://github.com/unusualify/modularous/commit/05c941f4309e52c88ac417e5d3afe37506cd1910
- add SerializeModel trait for serializing and unserializing Eloquent models with relationships by @OoBook in https://github.com/unusualify/modularous/commit/9fc8aac1ee393d1d4230a21ad6a1d743234d9633
- introduce ModularModel trait for extracting module names and route identifiers from Eloquent models by @OoBook in https://github.com/unusualify/modularous/commit/a5e98856bac65fb039c99d1595ad28ca2414a4ff
- replace vatRates condition with showVatRate and update discount label for clarity by @celikerde in https://github.com/unusualify/modularous/commit/a97719b0d448e0a0174942058193430f64d49524
- add CacheGraphCommand and CacheRelationshipGraph service for managing modularous cache relationship graphs by @OoBook in https://github.com/unusualify/modularous/commit/63385a465461c14585ed9368bc865d2bed82c740
- implement ModularousCache facade and service for enhanced caching capabilities, including support for tags and granular invalidation by @OoBook in https://github.com/unusualify/modularous/commit/2142de0cf15d0a869395d89dc4a7dd558da66a29
- add console command for clearing modularous caches with granular options for modules and routes by @OoBook in https://github.com/unusualify/modularous/commit/b50a6b5236ea5b3f5ec41451ebf939d8f4fc41a1
- add console command to display modularous cache statistics, including options for keys, dependencies, and relationship graph summary by @OoBook in https://github.com/unusualify/modularous/commit/cd0b5e9f3ad5e6445ecbeaa205ba51de67144e61
- introduce CacheableInterface, UserAwareCacheInterface, and related traits for enhanced caching functionality by @OoBook in https://github.com/unusualify/modularous/commit/483cc6d91496cfebe698ca2ac8068bb9e4cdd202
- implement caching functionality with CacheObserver and related traits for automatic cache invalidation in models by @OoBook in https://github.com/unusualify/modularous/commit/0415ca79a520130ce7191a02068720f7073e4f54
- add trait to manage touch behavior for Eloquent models, allowing conditional updates based on internal state by @OoBook in https://github.com/unusualify/modularous/commit/929efbc3d6e6e5bbb6468a57caf0358249967447
- introduce CacheableTrait for enhanced caching with relationship tracking, including methods for granular cache invalidation and data retrieval by @OoBook in https://github.com/unusualify/modularous/commit/02a88a4cfd0a872779ab292c66fe71357393d4d5
- extend Repository class to implement CacheableInterface, UserAwareCacheInterface, and ModuleableInterface; enhance update method to return boolean indicating change status by @OoBook in https://github.com/unusualify/modularous/commit/62361ba58ae2dad8f842869a94673de6f25c676d
- implement caching for count methods to improve performance and reduce database queries by @OoBook in https://github.com/unusualify/modularous/commit/aaa81dfbad1a8448f7abc7cdf9230c452a1d7107
- add caching support for paginated results with new getPaginator and getCached methods, enhancing performance and serialization of results by @OoBook in https://github.com/unusualify/modularous/commit/13bbd47692eecd4dba86eaf3c8380ecf65b62c4d
- enhance afterSaveRelationships method to conditionally touch Eloquent models based on relationship sync results, improving update logic and performance by @OoBook in https://github.com/unusualify/modularous/commit/f8cf520cd7aa8fb91e57ae924d22c6b5b02b8256
- add user-aware cache support to AssignmentTrait and CreatorTrait with new boolean properties by @OoBook in https://github.com/unusualify/modularous/commit/b08188dc3bd265a6977582ddedb3a891252a9715
- implement mustTouchEloquentModel method across various traits to ensure Eloquent models are updated conditionally based on changes by @OoBook in https://github.com/unusualify/modularous/commit/70860cee7e71c6746b5f47fae4c4a798cff7ae79
- refactor filter handling by introducing handleFilterCount method and restructuring getTableMainFilters for improved clarity and functionality by @OoBook in https://github.com/unusualify/modularous/commit/14e4679f3e3bf14a939981558f24dc2d7796aee9
- refactor getFormItem method to support optional default scopes and introduce formItem method for item processing by @OoBook in https://github.com/unusualify/modularous/commit/a626831aff0a3d4c6f765dbec3cbb1563d56512a
- introduce CacheableResponse trait for enhanced caching and relationship ID extraction in response handling by @OoBook in https://github.com/unusualify/modularous/commit/4f658bdd52778d3518d8838bcc9a758132fa6938
- implement CacheableInterface and enhance response handling with CacheableResponse trait; improve nested attribute retrieval logic and refactor JSON data handling for better clarity by @OoBook in https://github.com/unusualify/modularous/commit/765283c64dfa3480b5d6b3691ecdfb84ea694dc2
- implement ModuleableInterface, refactor module handling methods, and enhance assignment update logic for improved clarity and functionality by @OoBook in https://github.com/unusualify/modularous/commit/705b9a718843f7d35fbbab1549d4252bf341bb9b
- add routeName property to enhance routing clarity and consistency by @OoBook in https://github.com/unusualify/modularous/commit/df21103b986ae06311c5e6076fa78a2c64f402f6
- update related chatable and processable models on store, update, and destroy actions for improved data integrity by @OoBook in https://github.com/unusualify/modularous/commit/a5e6ef1097d0ddf38f0b051cc8e8ee85f4f4b564
- add CacheWarmCommand for warming modularous caches with options for specific modules and routes by @OoBook in https://github.com/unusualify/modularous/commit/ab16056e8189cbab64d03231f18ceacf3818f54e
- add HasCaching trait to Payment entity for improved caching capabilities by @OoBook in https://github.com/unusualify/modularous/commit/a3f8aca575f8a735197be98e8aaf108034f12aba
- add command to check database and connection collations by @OoBook in https://github.com/unusualify/modularous/commit/e7933e8586d14005f02e266043d360071cdd4332
- enhance searchInRelationships method to support search collation based on query conditions by @OoBook in https://github.com/unusualify/modularous/commit/64d797779f37234117b3b75d18e6c695fea6c1a7
- enhance advanced filter functionality with categorized sections and active filter management by @OoBook in https://github.com/unusualify/modularous/commit/12dfdb4dcd1633fe79c22f68f3d003a3c6a7581f
- add currency relationship to Payment entity by @OoBook in https://github.com/unusualify/modularous/commit/0b84623f1020f693efe5502e63495c9448f8fce7
- add advanced filtering options for payment status, service, and currency in configuration by @OoBook in https://github.com/unusualify/modularous/commit/dd0a95c707332a73f80d1b1ce674f14e2aa95f48

### :wrench: Bug Fixes

- update payment status conditions to include PROVISION and REFUNDED by @OoBook in https://github.com/unusualify/modularous/commit/3e2a9485e1248e30de53166ec558b5421e43e044
- rename payment-related keys in routes_statuses.json for consistency by @OoBook in https://github.com/unusualify/modularous/commit/6adc7193f5851e195a5b50b7299e33f294c20876
- ensure filters are merged safely by handling potential null values for defaultFilters and filters by @OoBook in https://github.com/unusualify/modularous/commit/7b9e576421eae68ccb3799f608ef8fcd9b71716f
- ensure payment is updated with the current timestamp if receipts are saved and payment was not previously changed by @OoBook in https://github.com/unusualify/modularous/commit/0b54f35882007433069cf69b28bd2e32e88aada6
- add return type hint to creator method for improved type safety by @OoBook in https://github.com/unusualify/modularous/commit/4b5bdefac722071c7589ca4d95122ed7d4becded
- remove oobook currency model from payment by @web-flow in https://github.com/unusualify/modularous/commit/64745c8c9b7543657ab94e31042d04a2a4f3c9fb
- enhance custom creator validation to handle null creator cases by @OoBook in https://github.com/unusualify/modularous/commit/fe5b93a885b57b1ac2a46da37825c8ee2ad42d8f
- set stateableChanged to true when state is updated by @OoBook in https://github.com/unusualify/modularous/commit/255e2666a99b6c10030f718dfabb6799df2b3899
- simplify condition for touching Eloquent model in afterSaveRelationships method by @OoBook in https://github.com/unusualify/modularous/commit/5dd717619f13b9c0b10b276a937dc2c4e4f7c7be
- change return type declaration for getCacheModuleRouteName method by @OoBook in https://github.com/unusualify/modularous/commit/4e7e252d9111b417894623715c9e455db778fda3
- improve route configuration retrieval by adding null check for module by @OoBook in https://github.com/unusualify/modularous/commit/d33bf5b118109ab1d0d5182d5a79105b63cdc4bd
- add null safe operator for user currency retrieval in price methods by @OoBook in https://github.com/unusualify/modularous/commit/d87b30a8468dd71adbb2e862ecafc98d366a9035
- initialize dependents array for route dependency tracking by @OoBook in https://github.com/unusualify/modularous/commit/f8fca28079b4369df3a3dcdec4c9908ba895969e

### :zap: Performance

- ensure form actions are only merged for non-AJAX requests or Inertia AJAX requests by @OoBook in https://github.com/unusualify/modularous/commit/b4ddcc9ed3dd601205899ff61f673660bcc31f4c

### :recycle: Refactors

- streamline form schema setup and improve route name handling by @OoBook in https://github.com/unusualify/modularous/commit/b98d0e480a94efd5c80805645eb8e36cd9da1bfd
- enhance module and route handling by deprecating old methods and introducing new accessors by @OoBook in https://github.com/unusualify/modularous/commit/6c536dd930bca560689bec41791dcf87f4fb89cf
- replace deprecated UFinder with ModularousFinder and add traitProperties method for improved property handling by @OoBook in https://github.com/unusualify/modularous/commit/1d322ee70ad0164414e45a3975bff7bccd64b269
- streamline model update logic across HasAuthorizable, HasCreator, HasSpreadable, HasStateable, and Processable traits to ensure proper touch behavior on non-dirty models by @OoBook in https://github.com/unusualify/modularous/commit/55ccb065c1a4485b2f7b065b031dfecff4e0ad10
- streamline code by removing unused properties and methods, and introduce TableItem trait for improved item handling and formatting by @OoBook in https://github.com/unusualify/modularous/commit/24c453bf16de613d02a4cc3f7298ab3108037941
- update getFormData method to improve item retrieval and streamline form data processing by @OoBook in https://github.com/unusualify/modularous/commit/fa39140ee63a9a4f94b6979d8b6487e4063721c4
- update method calls to use getModuleName and getRouteName for improved consistency and clarity by @OoBook in https://github.com/unusualify/modularous/commit/8a442e1508f57e33f62aed37c4f2aff3ade54292
- remove type hints for query parameters and enhance JSON field handling in addSearchCollationToQuery method by @OoBook in https://github.com/unusualify/modularous/commit/3008a52f1a90dcdf7970dec2a277f6aa1823a36b
- improve relationship handling in filter method for better query support by @OoBook in https://github.com/unusualify/modularous/commit/9db1c96dfe1f136ca6d2f6faa0e6f4a65f214b2b
- update VAT rate visibility condition and streamline discount input rendering by @OoBook in https://github.com/unusualify/modularous/commit/74de7e89dda7036cce5deebe0a5f27f804dd7df9
- enhance VAT rate visibility condition to include discount check by @OoBook in https://github.com/unusualify/modularous/commit/2c1d1ca8b8b6f8fec656bfa852073bcceb221722

### :lipstick: Styling

- lint coding styles for v0.55.0 by @OoBook in https://github.com/unusualify/modularous/commit/54b5439ef9402e19b5bcd1ea4231d64a1036a237

### :white_check_mark: Testing

- refresh chat message instance after accessing user profile and creator by @OoBook in https://github.com/unusualify/modularous/commit/0cae3bffbb424ecf14385e9794a636d12e46b488
- update assertions for custom_creator_id to ensure it is present in the mapped fields by @OoBook in https://github.com/unusualify/modularous/commit/0879a9c56879142c94c23ffee809e38aa3785aed
- reorder test model initialization to improve setup clarity by @OoBook in https://github.com/unusualify/modularous/commit/790ecb60b4225ade78ba303da7f3fa13b44c582c

### :package: Build

- update build artifacts for v0.55.0 by @OoBook in https://github.com/unusualify/modularous/commit/99a4274d2737c2cd95759317fab3a0f2d368f9a3

### :green_heart: Workflow

- update composer install command to ignore platform requirements by @web-flow in https://github.com/unusualify/modularous/commit/5fd3198e14cc01a89a7fcdd5b2326c3ed8469d06
- update composer install command to ignore specific PHP platform requirements by @OoBook in https://github.com/unusualify/modularous/commit/0e4644f824ee4ac370e585bc072cf7d155d83aac
- set PHP platform version to 8.4.0 in composer configuration by @OoBook in https://github.com/unusualify/modularous/commit/ebdf5009f3eec6141336520f382b18255fb0f8b3
- update PHP platform version in composer configuration from 8.4.0 to 8.1.0 by @OoBook in https://github.com/unusualify/modularous/commit/8585a44f916d4d95c48275742c77cdc63e832910
- update PHP platform version in composer configuration from 8.1.0 to 8.2.0 by @OoBook in https://github.com/unusualify/modularous/commit/f1d96cfdf91aaca88d9e1e6d16d99b447ecaec32
- update composer update command to ignore PHP platform requirements by @OoBook in https://github.com/unusualify/modularous/commit/9ccb3be7f58124c6a5d68493f5304cc8fc5414bd
- update PHP platform version in composer configuration to 8.4.99 by @OoBook in https://github.com/unusualify/modularous/commit/eb9e80a5ee145cf26deb45f20544404833b4e74f
- modify composer install and update commands to remove PHP platform requirement ignore by @OoBook in https://github.com/unusualify/modularous/commit/72a23d584e292e98f6c24292bf45478871f2eaa8
- set PHP platform version in composer configuration dynamically based on matrix by @OoBook in https://github.com/unusualify/modularous/commit/97feab0391186e5b38b9af052c48d9359e1b43f4
- remove PHP platform version specification from composer configuration by @OoBook in https://github.com/unusualify/modularous/commit/335e8779cee8202d1c5708318693e9c6eab460f9
- enhance composer commands in CI workflow to dynamically handle PHP version and Laravel dependencies by @OoBook in https://github.com/unusualify/modularous/commit/0cbbee895fdf227cc05d52047fc6ce48b10a45a9
- set PHP platform version to 8.2.0 in composer configuration by @OoBook in https://github.com/unusualify/modularous/commit/87835fa9a69ae874a182f85a5411f81189f332b2
- update GitHub Actions workflow to improve dependency handling and naming conventions by @OoBook in https://github.com/unusualify/modularous/commit/92f82a15d9e8fe54af44097fc91eda9ef5e2629b
- remove PHP platform version specification from composer configuration by @OoBook in https://github.com/unusualify/modularous/commit/30780877cf6ef183b6ee7bfe772c4baf2e3becd7
- modify composer install command in CI workflow to ignore platform requirements by @OoBook in https://github.com/unusualify/modularous/commit/fcb87e64feb6274baa926ea33e7f1531ccd5abf1
- comment out PHP platform version configuration in CI workflow for clarity by @OoBook in https://github.com/unusualify/modularous/commit/51e5348495de26a37cef386f00dd93a3416f9a81
- update composer install command in CI workflow to remove platform requirement ignoring by @OoBook in https://github.com/unusualify/modularous/commit/bb01872e1ff15bcb9c5b2dd557404232bec76349
- update composer commands in CI workflow to ignore platform requirements during installation by @OoBook in https://github.com/unusualify/modularous/commit/9ef2386c4a3266ba3a2eae172ae71aefaadbed45
- comment out composer update command in CI workflow for clarity by @OoBook in https://github.com/unusualify/modularous/commit/908dd9e2568e5c88a9d4282b93b3b6ce35a4987d

### :beers: Other Stuff

- update composer.lock entry to be commented out by @OoBook in https://github.com/unusualify/modularous/commit/0d8de3e8e74323c5990e6a5d339371dd07d5b8f4
- add composer.lock file to lock project dependencies by @OoBook in https://github.com/unusualify/modularous/commit/405c1f4c84e5058c51283be23387c622f3978e4f
- remove commented-out migration methods for cleaner code by @OoBook in https://github.com/unusualify/modularous/commit/3681bc7517180521d13817569bb6e227ed489f2f
- remove commented-out asset publishing code for improved clarity by @OoBook in https://github.com/unusualify/modularous/commit/7c26e0494e19a670266cc7b4d0ba21617a7a0380
- remove commented-out properties in constructor for cleaner code by @OoBook in https://github.com/unusualify/modularous/commit/31434e6a44df4812cb11ed5c60ab6a0df46c3c29
- remove commented-out code in constructor for improved readability by @OoBook in https://github.com/unusualify/modularous/commit/896a1fbe1fe034a1e71ed8cd0c943698a33234e2
- remove commented-out module activation line in constructor for improved readability by @OoBook in https://github.com/unusualify/modularous/commit/004432346cfc3a2021c1cc5fc9ac8a6ad9ea90da
- include composer.lock to ensure consistent dependency management by @OoBook in https://github.com/unusualify/modularous/commit/9068ddbd2ca5d29c3c43d4b55bb15e9a959f23fb
- remove composer.lock file to prevent dependency locking issues by @OoBook in https://github.com/unusualify/modularous/commit/237a9e23f6e458d1b418bdd0cc1fc63f0e5da882
- remove additional commented-out code for improved readability by @OoBook in https://github.com/unusualify/modularous/commit/376be7ea833a2fe5be44f80c51799de8bef09dcf
- remove commented-out debug statement for cleaner code by @OoBook in https://github.com/unusualify/modularous/commit/d956d0d48d1bf569937e66bb4783616c4c531eed
- remove commented-out debug statement in formatCached method for cleaner code by @OoBook in https://github.com/unusualify/modularous/commit/a86235b2e8b49c0d8668f6dd363a90b1c121c638
- remove commented-out debug statement in formatCached method for improved code clarity by @OoBook in https://github.com/unusualify/modularous/commit/5be715d6991a79e10c3d3182f5a85bf956788753
- remove commented-out debug statement in getRouteActionUrl method for improved code clarity by @OoBook in https://github.com/unusualify/modularous/commit/7a5f2939802350e5e8c046dc5fcf7f0f2bb6e73e
- remove commented-out debug statement in scan method for improved code clarity by @OoBook in https://github.com/unusualify/modularous/commit/3e6bfaf4f835d90f7ebc3a619822edb964421c93
- remove commented-out return statement in getVendorPath method for improved code clarity by @OoBook in https://github.com/unusualify/modularous/commit/6c9b581b1e72b9027bf35756169334ac67f31a9b
- remove commented-out code in clearCache method for improved code clarity by @OoBook in https://github.com/unusualify/modularous/commit/932c5e61a277f76945c8806adb1fc38b442bdc3c
- remove commented-out code in hasStatus method for improved code clarity by @OoBook in https://github.com/unusualify/modularous/commit/1d62c6fd3b89c4e3a9cee014d7677f181ded7b18
- update .gitignore to comment out composer.lock for clarity by @OoBook in https://github.com/unusualify/modularous/commit/16841b8803caf72bcb9500ebd8ce521986de871d
- add composer.lock file to lock project dependencies and ensure consistent installations by @OoBook in https://github.com/unusualify/modularous/commit/1e69c8a309bc0ba62649ab46cfebf17a8835a5a2
- remove commented-out code in delete method for improved code clarity by @OoBook in https://github.com/unusualify/modularous/commit/8c42d52d8f47f1e1cd14a805766283ee2f540b61
- remove commented-out code in getModulesStatuses method for improved code clarity by @OoBook in https://github.com/unusualify/modularous/commit/d236b3d23e1a6730cbb4cc7dae84b5c4c01e97ac
- remove commented-out constructor call for improved code clarity by @OoBook in https://github.com/unusualify/modularous/commit/6799e8c80e583ed6ea33287b936c1caa3b21576d
- remove commented-out setModule method for improved code clarity by @OoBook in https://github.com/unusualify/modularous/commit/d873275a9f73561dc1fcc45676b8a89fe5b5bb5b
- remove commented-out use statement for improved code clarity by @OoBook in https://github.com/unusualify/modularous/commit/d4dcc0164993dc4dbf903e7788222b0b58eb165e
- remove unused use statement for improved code clarity by @OoBook in https://github.com/unusualify/modularous/commit/e617462510f2500c04933075a18863dbf2f6b4c6

## v0.54.0 - 2025-12-25

### :rocket: Features

- add database creation prompt and implement database creation logic for multiple drivers by @OoBook in https://github.com/unusualify/modularous/commit/c04b87e4e6f0e2731c7df2ea25b239a03ae62b71
- add new email validation messages for improved user feedback by @OoBook in https://github.com/unusualify/modularous/commit/953e2fd7d78c39d377da11ce46a10e65b3b6ba26
- introduce schema management in Repository class and add Schema trait for enhanced input handling by @OoBook in https://github.com/unusualify/modularous/commit/78fb96a2c1910ff6c5466b86334718b4d1d4d6e0
- add targetTypeKey property and enhance event handling with new getter methods by @OoBook in https://github.com/unusualify/modularous/commit/7bd47deb04dc3b3252ee1c06e087265f5f6d6d94
- enhance polymorphic input handling by introducing newConnector support and improving model retrieval logic by @OoBook in https://github.com/unusualify/modularous/commit/0994d8bd3ad5a1204a81e919f1824e3cff0378c8
- add schema setting in getFormFields method to improve input handling by @OoBook in https://github.com/unusualify/modularous/commit/8bbe7114b39a53c8ec20f2da096a8211de5b1375
- introduce ResolveConnector trait for improved connector repository resolution by @OoBook in https://github.com/unusualify/modularous/commit/912448c7578a0ab78299fbafcf7651a6e6951f47
- add CheckSnapshot trait for managing snapshot relations and foreign key retrieval by @OoBook in https://github.com/unusualify/modularous/commit/c7538f8dbf7c80a7e062b9788c7d44ba23d56484
- add makeMorphToName function for generating morph names with suffix by @OoBook in https://github.com/unusualify/modularous/commit/8ef6a732d2b38850d648fee21e22ba57c09e5610
- enhance translation handling with bypass and activation methods by @OoBook in https://github.com/unusualify/modularous/commit/9e86cc69791e87adabd0f35292029cbfa239ae6a
- add tooltip conditions handling for comparator items by @OoBook in https://github.com/unusualify/modularous/commit/e77b1e6dce2c1e28779650e2ccd2a7e900572bc9
- implement search collation feature with configuration option by @OoBook in https://github.com/unusualify/modularous/commit/a53097adc352b70c0c721f71a2ea63c3c0c915e5

### :wrench: Bug Fixes

- update PriceableObserver namespace for correct path reference by @OoBook in https://github.com/unusualify/modularous/commit/daf2ea7fd446bb04efa4e17ab0edb5c0d81f6261
- ensure user currency is set only if currency model exists by @OoBook in https://github.com/unusualify/modularous/commit/caaf19a04357d09769ac0606c7edba4dba65a1d7
- add language publishing logic if language path does not exist and improve header filtering in config update by @OoBook in https://github.com/unusualify/modularous/commit/4ae5ad13f2a1bce618f3a1592147868fe74e2b80
- enhance getRawConfig method to handle missing config files and provide default values by @OoBook in https://github.com/unusualify/modularous/commit/ac5dea66b4fb2090e8b977f0605f5254ba1bbb6d
- update sidebarOptions and secondarySidebarOptions to use empty array as default values by @OoBook in https://github.com/unusualify/modularous/commit/8a70b6f42cd68a3cecc888d22beb1e0775e480c2
- correct variable name in slugify function to ensure proper string handling by @OoBook in https://github.com/unusualify/modularous/commit/3009ca61c490d1196f0e54fbdc2684865ae2fcaf
- correct comment syntax for setCascadeSelect method call by @OoBook in https://github.com/unusualify/modularous/commit/1a56a73512deceee89ec43f85abe8374e6ff1eab
- add camelCase pattern matching to hydrateSelectableInput method for improved input handling by @OoBook in https://github.com/unusualify/modularous/commit/0df7185f90650b657482d841d08cb65c9a304a72
- add return type declaration for medias method to improve type safety by @OoBook in https://github.com/unusualify/modularous/commit/37a0fadb8e3dae01b20114e980ff55784e1d60c8
- enhance error handling in hydrateInput method by validating model existence and improving exception messages for morphTo input by @OoBook in https://github.com/unusualify/modularous/commit/2d325000697876aa7b0c4e2323eaa3099b61de46
- initialize modelInstance variable for input hydration process by @OoBook in https://github.com/unusualify/modularous/commit/ad4c95dcffb8a65bf10f68f4e2839324df085931
- print object values not being a tooltip by @OoBook in https://github.com/unusualify/modularous/commit/f3302541a2306827b31be33cef09c6e3189789c1

### :recycle: Refactors

- simplify dashboard UI settings by removing unused block configurations by @OoBook in https://github.com/unusualify/modularous/commit/3575687cd505c1f2fcbac3644f7293fc30eed107
- replace insert with updateOrCreate for currencies, price types, roles, and VAT rates by @OoBook in https://github.com/unusualify/modularous/commit/07ca41b8b87ddcbbb96bf6b7cd345610f30b9ad2
- replace user creation with createOrUpdate method and streamline role assignment by @OoBook in https://github.com/unusualify/modularous/commit/c01a873a495b40784bb696ca8b2fabc5f2940a7d
- streamline language publishing logic by consolidating path determination for ignored languages by @OoBook in https://github.com/unusualify/modularous/commit/26cc94a0a426b15d41bf262be758da8c477fb902
- rename and enhance relation handling methods for clarity and consistency by @OoBook in https://github.com/unusualify/modularous/commit/8549bc32522477c6fa2c5861cd66e1a2f63c226d
- reorganize repository methods and introduce getModel method for improved model retrieval by @OoBook in https://github.com/unusualify/modularous/commit/cc34718a4e6470fc966c902e895b2db183659453
- update file and media handling methods to accept object parameter for improved context and consistency by @OoBook in https://github.com/unusualify/modularous/commit/fe5d2bcad38f1d9ee99e8b124c63d50a7135f47a
- rename inputs method to getRawInputs for clarity and consistency in input retrieval by @OoBook in https://github.com/unusualify/modularous/commit/f95cac214347aba7575606b3eb7dd66503952dbc
- streamline relationship handling by replacing repository calls with model lookups and enhancing error logging by @OoBook in https://github.com/unusualify/modularous/commit/18360c337cc066ab6129fe1f6205153a009adedc
- update hydrate method to handle input type and root assignment based on original type by @OoBook in https://github.com/unusualify/modularous/commit/18412ba40d9b63a3b89b01f43684e0293084fe98
- streamline locale handling and input retrieval in afterSaveRepeatersTrait method by @OoBook in https://github.com/unusualify/modularous/commit/cc66d8d893d35db616d3e6900909b6266957edf7
- comment out unused unsetColumns logic in afterSaveRepeatersTrait method for clarity by @OoBook in https://github.com/unusualify/modularous/commit/9ae05049d1ff89877009e3a2e9a6fe9cb823f825
- remove commented-out code and simplify spreadable input key check for clarity by @OoBook in https://github.com/unusualify/modularous/commit/a6175abd9aad4fb5abbd0442533feab90f93e419
- improve input handling and simplify tag processing logic in afterSaveTagsTrait and getFormFieldsTagsTrait methods by @OoBook in https://github.com/unusualify/modularous/commit/f8c8505a50e4ddf5b5886bd395ae7307cac0c331
- unify slug handling methods and improve slug attribute retrieval by @OoBook in https://github.com/unusualify/modularous/commit/ded28efa03b6a7cfb09d94671ef0abc87a0b63b0
- update query type hints from Query\Builder to Eloquent\Builder for improved clarity by @OoBook in https://github.com/unusualify/modularous/commit/fe52b4acb7c4796c9dd714fa3b70a86951b99675

### :lipstick: Styling

- lint coding styles for v0.54.0 by @OoBook in https://github.com/unusualify/modularous/commit/878cf7fab27443c58a3dc91e1ead289968ca9dff

### :white_check_mark: Testing

- add tests for defined relations and foreign key methods by @OoBook in https://github.com/unusualify/modularous/commit/2a4cb3d1ab993f2ff65bc6e17ef0c5946dbdbc84
- enhance file and media attachment tests to support multiple files and improve assertions by @OoBook in https://github.com/unusualify/modularous/commit/a7db278738ffd15dfb3015ab5fb027dd6097693a
- add comprehensive tests for schema input retrieval and chunking methods by @OoBook in https://github.com/unusualify/modularous/commit/c5f57ab53f1d1bb9d5221b8da0a8b154548a6ae8
- implement comprehensive tests for various relationship types including morphMany, hasMany, and belongsToMany, enhancing repository functionality and error handling by @OoBook in https://github.com/unusualify/modularous/commit/28dfbef3f16ac3ca539febe675e97bbabfa0ae55
- add unit test for getCountFor method, including validation for existing and non-existent scopes by @OoBook in https://github.com/unusualify/modularous/commit/b119406159f5ab63b59fc537fbc18e6d942f2235
- add comprehensive unit tests for RepeatersTrait functionality, including handling of translated and non-translated inputs by @OoBook in https://github.com/unusualify/modularous/commit/70a365a4067bcf32860aabb3ac87dac168f4bb3f
- add unit test for before_save method to ensure spreadable model creation when not existing by @OoBook in https://github.com/unusualify/modularous/commit/3de3ac4a39392b86f98bc800c68ecfca69d5e7e9
- add unit tests for handling translated tags in create and getTags methods by @OoBook in https://github.com/unusualify/modularous/commit/18f2c5bfec6120b7cc5c5a661a96f355e1930688
- add new fields for publishing and create slugs table; update TestModel fillable attributes by @OoBook in https://github.com/unusualify/modularous/commit/80e87298a49e64e5db5ce71aa010b6980915f045
- add 'published', 'public', and date fields; create TestModelSlug class for slugs management by @OoBook in https://github.com/unusualify/modularous/commit/cddb301102814efa128d118f9d8eceacd8189b87
- remove 'translations' from defined relations assertions for clarity by @OoBook in https://github.com/unusualify/modularous/commit/30eb9907ebb2d6f0d2e1dcfd492b622e124b984d
- add comprehensive unit tests for slug generation, updates, and retrieval across multiple locales by @OoBook in https://github.com/unusualify/modularous/commit/289caa0755b67aa596c182a4e154dd236304e81f
- add unit tests for collation selection logic and query handling by @OoBook in https://github.com/unusualify/modularous/commit/2ccdefef0c2d4b3ab781448e2ba270d3c3e928c3
- replace Query\Builder with Eloquent\Builder in mock setups for consistency by @OoBook in https://github.com/unusualify/modularous/commit/88d5ff78e08b5eaa050b214928386b8e89f28cf2

### :package: Build

- update build artifacts for v0.54.0 by @OoBook in https://github.com/unusualify/modularous/commit/29a9b5a15ea9801f4f73e3a6506f5a49897e9007

### :green_heart: Workflow

- add 'test' label condition to issue branch creation logic by @OoBook in https://github.com/unusualify/modularous/commit/db10065f93364dd7f2c48b65b9ff3b4d8663def9

### :beers: Other Stuff

- add new test issue template by @web-flow in https://github.com/unusualify/modularous/commit/3e51c1f00ad97cf6835e8c0767b1268247669183
- fix text field as textarea by @web-flow in https://github.com/unusualify/modularous/commit/e2c94b97254c46c06cd34bb0168e54a20cb72ca6
- add test tube icon by @web-flow in https://github.com/unusualify/modularous/commit/7e7a12406ad6349a37f4ee180592b0f76670fd46
- change description for test issue template by @web-flow in https://github.com/unusualify/modularous/commit/03025734baa07572397e6cf4e1b9be4cd02dbd4d
- remove commented debug statement from getCountFor method for cleaner code by @OoBook in https://github.com/unusualify/modularous/commit/d658322406c1a3c812a2c51ef5d4cdb968eb7d45
- move type definition of payment currency's default_vat_rates input to uppermost by @OoBook in https://github.com/unusualify/modularous/commit/f234aed89ddfcf439cecdcc138e1e75323afbc4b
- comment out unused code for improved readability in getFormFieldsRepeatersTrait method by @OoBook in https://github.com/unusualify/modularous/commit/211157bd20da432fc0aca69766b98f06b3268023
- comment out unused translation handling logic for improved readability in getFormFieldsTranslationsTrait method by @OoBook in https://github.com/unusualify/modularous/commit/2b025bdaa27f3e5fe7662427beb36306fab9b40f

## v0.53.0 - 2025-12-10

### :rocket: Features

- rename 'item' to 'haystack' for clarity, add handleItemConditions function to manage item state based on conditions by @OoBook in https://github.com/unusualify/modularous/commit/1d7147927091e45e607e672df47b634b69698937
- add new time-related fields and validation messages for improved localization support by @OoBook in https://github.com/unusualify/modularous/commit/ac1ee5b01b3bbb6bcd7a4174013f0e203cbf9388
- add loadCommands method to dynamically register console commands from specified directory by @celikerde in https://github.com/unusualify/modularous/commit/bf7543c36ada3ef275b1174e4180a746a57414dc
- add loadCommands call in bootModules to register module commands during boot process by @celikerde in https://github.com/unusualify/modularous/commit/4ed44d2a23e18502a7b3cc4349948592a4764c00
- add preliminaries handling and localization support for preliminary documents in both English and Turkish by @OoBook in https://github.com/unusualify/modularous/commit/89e644f2d5ffebe8bbcde3388b961962b10a798d
- add mergeChangedRelationships method to combine relationship collections by @OoBook in https://github.com/unusualify/modularous/commit/0a04ab409a3d2b6a34682a8033fd7a4418bbc514
- add support for outro lines in notifications and enhance mail message formatting by @OoBook in https://github.com/unusualify/modularous/commit/c1c17e79dea4858f599fe200b90e2df426be719d
- enhance filepond management with new methods for tracking deleted and new fileponds by @OoBook in https://github.com/unusualify/modularous/commit/2ef317591a4502ee9e77bb6c696420bb41574d57
- integrate EventChanges and EventUser traits for enhanced event handling by @OoBook in https://github.com/unusualify/modularous/commit/d2c5a4dae2011179e0f8533683c68504ff35e937
- enhance record restoration by checking foreign key existence before updating or inserting records by @OoBook in https://github.com/unusualify/modularous/commit/bece483f52281b0a3956753fc824cfd66376454f
- enable timestamps for Stateable model and implement migration backup/restore functionality by @OoBook in https://github.com/unusualify/modularous/commit/7143ebef051dc617b5eb1ad0f94e3d43392f1892
- enhance message display with avatar and timestamp formatting improvements by @celikerde in https://github.com/unusualify/modularous/commit/7641b646dbc6da1e5b6d390d15b7e4584103a305
- add 'is_provided' attribute and update payment status checks to include PROVISION by @celikerde in https://github.com/unusualify/modularous/commit/30e08aeab5266fc07e9c3982f0e1046dac1471f0
- improve message layout and timestamp formatting, enhancing readability and responsiveness by @celikerde in https://github.com/unusualify/modularous/commit/990b26e7f1924da1680748c2842cd246d1f335f4
- add total label slot and update total display structure for improved flexibility by @OoBook in https://github.com/unusualify/modularous/commit/dbb93e8e5d775450fd2cd63475c3dfdf927b8455
- implement RecursiveStuff utility class for dynamic HTML element management and rendering by @OoBook in https://github.com/unusualify/modularous/commit/04afd1b6adfb7515882e447b662274ca8c8c162e
- implement language-based pricing functionality with configuration options and currency handling by @OoBook in https://github.com/unusualify/modularous/commit/fea88fce79a89e8cf3397744ea54078073213c43
- update createDefaultTranslationsTableFields to accept foreign key parameter and improve foreign key handling by @OoBook in https://github.com/unusualify/modularous/commit/e0d73c4cb6e752ecb1072fee86e41b940aafeeab
- enhance getTableSchema to dynamically determine primary key for various database drivers by @OoBook in https://github.com/unusualify/modularous/commit/40a92f346c4e7715f63a4c9e762d94926d1064d2
- implement translated attribute transformation and enhance fill method for locale handling by @OoBook in https://github.com/unusualify/modularous/commit/e5e7492e7296e175f730dc1af9c29c8f35cea67a
- add slot for underside customization with form item and schema bindings by @OoBook in https://github.com/unusualify/modularous/commit/cb98d4dab16ea18ac639dee715180614a7482751
- add tooltip support for comparators and enhance item rendering logic by @OoBook in https://github.com/unusualify/modularous/commit/440ae4eef20f80f94d50513d8cec8db270931ed8
- add focus handling for search text field and improve slot usage for combobox by @OoBook in https://github.com/unusualify/modularous/commit/1be2a576ea4349aaf20b778219d43aac6e0740f9
- add trait for managing translations with methods to retrieve and find translation keys by @OoBook in https://github.com/unusualify/modularous/commit/d1ef36d28c482e73087f2d9e1b218db0beba1735
- enhance translation handling in form data and view layout methods by @OoBook in https://github.com/unusualify/modularous/commit/a328fa5e6e6caee0e2d6e2ee23fe29e737b57067
- add new payment-related keys for English and Turkish languages by @OoBook in https://github.com/unusualify/modularous/commit/474635c4025ecbc3b7c5b8510549626cd4475a61

### :wrench: Bug Fixes

- merge email from OAuth user during registration process by @OoBook in https://github.com/unusualify/modularous/commit/ab8cec4bb0917a9d3b3f3ba4163e1dcf9dba96a8
- update rules handling to use isset for better validation checks by @OoBook in https://github.com/unusualify/modularous/commit/8b7cbb0489dcba47ce12f3f766ebaeeccf19bf08
- ensure input is retained on removeFilepond error for better error handling by @OoBook in https://github.com/unusualify/modularous/commit/a027dc01b74e0a02d1d73eb1497311f84e95bf50
- update validation messages to use placeholders for better localization support by @OoBook in https://github.com/unusualify/modularous/commit/ccd6970bf97439bbe13617b8f37ce4f7d8253a1f
- add emit definitions for modelValue, form updates, and submit events to enhance component interactivity by @OoBook in https://github.com/unusualify/modularous/commit/287a64dd2c4e6f6078b0dc6afa9a83dfcc314348
- update payment status from COMPLETED to PROVISION for bank transfer payment handling by @celikerde in https://github.com/unusualify/modularous/commit/115c7f4c2374bf9c122b8e028bfe5b89ffbc84f6
- update PROVISION color to 'info' and icon to 'mdi-progress-clock' by @celikerde in https://github.com/unusualify/modularous/commit/4d931014214573f5795ffabbf989dd1d242cdc09
- update filterEndpoint assignment to use null coalescing operator and remove commented-out code for clarity by @OoBook in https://github.com/unusualify/modularous/commit/4d86cc8ea6695d7dd890e4ab031be05cc4f7a9c7
- update getRouteActionUrl to append query parameters for replacements in URL generation by @OoBook in https://github.com/unusualify/modularous/commit/8c990e33aa435b865e24dd35c9c55eaeee96f6d5
- enhance user creation by including surname and name handling from OAuth response by @OoBook in https://github.com/unusualify/modularous/commit/8dcc54a8990957cee85f90189dbc6e78f1e9e743
- update TestModel class to extend from Unusualify\Modularous\Entities\Model for proper functionality by @OoBook in https://github.com/unusualify/modularous/commit/4e83984c1d1e7f29a188aa0e3e7914743424c228
- update PaymentStatus namespace to use Modularous entity for consistency by @OoBook in https://github.com/unusualify/modularous/commit/e730d99c8633ff2038e517596936ab661cd4ad05
- update color assertion for PROVISION status from 'secondary' to 'info' by @OoBook in https://github.com/unusualify/modularous/commit/4be9a3ac798466aaf63afc571e464e3278b6febc

### :zap: Performance

- enhance user impersonation functionality with new props and input component by @OoBook in https://github.com/unusualify/modularous/commit/80c33e0f604838bcb857530ca04b85eb616bd9b2

### :recycle: Refactors

- remove redundant profile route group for cleaner routing by @OoBook in https://github.com/unusualify/modularous/commit/223bab19145111b9bca6f0169ef9692dd822829e
- add variant and density as prop, enhance item slot binding by @OoBook in https://github.com/unusualify/modularous/commit/b967385918878900ebfe93715a00a90e1067f024
- remove ManageEvents trait and related event handling code for cleaner controller logic by @OoBook in https://github.com/unusualify/modularous/commit/d3445d5e5c10b6060d84f355f6c41c2857ccb24d
- improve date parsing with error handling, remove unused event firing method, and update repository test for better clarity by @OoBook in https://github.com/unusualify/modularous/commit/acf165bd2747780a69c2aa677a476980b3adf52a
- remove unused filterBack method and clean up getShowFields logic by @OoBook in https://github.com/unusualify/modularous/commit/3a17245cfbbb71de07d2ce0f175c05d6c0d09a21
- replace addRelationFilterScope with addRelationFilterScopeByRelationName for improved clarity in tag filtering by @OoBook in https://github.com/unusualify/modularous/commit/f516a83e64ebacfb2179f643822c18af62de8595
- improve route sorting logic for better clarity and precedence handling by @OoBook in https://github.com/unusualify/modularous/commit/ef01ea987ea958101360d0717af7be98ce86e280
- add return type declaration to getRepository method for improved type safety by @OoBook in https://github.com/unusualify/modularous/commit/8014c56d7c641cf5108803e877d43a3cbf7034c8
- enhance pagination handling and streamline query methods, including deprecating unused parameters and improving code clarity by @OoBook in https://github.com/unusualify/modularous/commit/3c24d4d9222057aa0ef3dfad88b62870378a089a
- update repository type declaration and streamline form item retrieval by removing deprecated parameters by @OoBook in https://github.com/unusualify/modularous/commit/c8d6cb99de83027cb4d610543bf86c3aa298a2b7
- update getFormFieldsFilesTrait method to accept schema parameter for improved flexibility and enhance related tests for file handling by @OoBook in https://github.com/unusualify/modularous/commit/0b29842987e6e24d5bfe3a8db3ce7ab2b1593902
- update getFormFieldsImagesTrait to utilize schema parameter for improved media handling and enhance related tests for image hydration and field retrieval by @OoBook in https://github.com/unusualify/modularous/commit/28f1a32075dcf24c0b56c13535307477b72908fd
- modify methods to accept schema parameter for improved flexibility and streamline repeater input handling by @OoBook in https://github.com/unusualify/modularous/commit/6be8607f3fca9f5b743c11c2af5e3d710b2726d1
- rename Post and CopyPost to Product and CopyProduct, updating related references and enhancing test clarity by @OoBook in https://github.com/unusualify/modularous/commit/84dade6602d961ea448b3d88f4fbdd70e6d60149
- replace checkItemConditions with handleItemConditions for improved condition handling and streamline input processing by @OoBook in https://github.com/unusualify/modularous/commit/96daafbd0a8bbb0e3cbae46f0385d00cc3b3e13d
- enhance input hydration by incorporating model notation and improving event formatting logic by @OoBook in https://github.com/unusualify/modularous/commit/2e376a367a111328c22bae33706a9023706b11cc
- implement AssignmentModal component for creating assignments, enhancing form validation and user experience by @OoBook in https://github.com/unusualify/modularous/commit/7a716bcf7f414a41435b33a54cb699a03ad621f0
- introduce AssigneeDetails component for improved assignment management, including file handling and enhanced UI interactions; update localization for description and files fields by @OoBook in https://github.com/unusualify/modularous/commit/fff5aac300a629de8cbc21beac725a98d3afe282
- update repository property type declaration for improved clarity by @OoBook in https://github.com/unusualify/modularous/commit/47bafb6109e779ec4aaf74a29fc2a8e269c265dc
- rearrange label slot and title component for improved readability and structure by @OoBook in https://github.com/unusualify/modularous/commit/315b752696465921989b6f43370389f240e8cb74
- refactor to utilize new traits for user, URL, and state management, enhancing modularous and code organization by @OoBook in https://github.com/unusualify/modularous/commit/14c36b6b1860b4153a956c9b39d74b68d27a2720
- comment out isPaid and isUnpaid methods that are unnecessary. by @celikerde in https://github.com/unusualify/modularous/commit/96fe2062e59597740588e5a9d14d1aec0db15589
- replace individual timestamp fields with timestamps() method for cleaner migration definition by @OoBook in https://github.com/unusualify/modularous/commit/dcf13b4c5b2c3b1b0cfb274716d2bca3d5fee526
- reorganize component structure and improve item selection logic for better maintainability by @OoBook in https://github.com/unusualify/modularous/commit/3130428e42a6f554f114471a128c50f132884b2d
- streamline props definition and enhance template structure for improved clarity by @OoBook in https://github.com/unusualify/modularous/commit/dc32510fce004df02e05d4c4a69cb4f47c46ed53
- uncomment and implement isPaid and isUnpaid methods for better price mutator functionality by @OoBook in https://github.com/unusualify/modularous/commit/4d5007bc6e2053807e48b7760c6cacdd186bccfc
- integrate ManageTranslations trait and streamline translation retrieval for CRUD operations by @OoBook in https://github.com/unusualify/modularous/commit/8ad578189874c259213e789a790d8c846197df6c
- remove console logs for cleaner code and maintain tooltip functionality by @OoBook in https://github.com/unusualify/modularous/commit/9bb3a0771524be0b8ddad1e5d49bbdde65782e93

### :lipstick: Styling

- lint coding styles for v0.53.0 by @OoBook in https://github.com/unusualify/modularous/commit/9927790145e78063e07ba41550b1b7195743b64c

### :white_check_mark: Testing

- add comprehensive tests for file and media repositories, including filtering, creation, and deletion behaviors by @OoBook in https://github.com/unusualify/modularous/commit/acfdbfe262129890479cadbdee091c7b1aba0278
- add tests for filtering with relation scopes and counting by status slug, including schema updates for published column by @OoBook in https://github.com/unusualify/modularous/commit/edf7e19867129db05109a49b250a71154f07b7fc
- add user name handling in oauthCreateUser method and implement OauthTraitTest for comprehensive testing by @OoBook in https://github.com/unusualify/modularous/commit/5f7dca0a8e7b39737e1291c12b0bb8e8fb9583e9
- add comprehensive tests for process handling in ProcessableTrait, including input collection, process ID management, and form field generation by @OoBook in https://github.com/unusualify/modularous/commit/f1cafaaabbe6745dbfd09dccaa5c4a8a72361348
- add comprehensive tests for stateable functionality, including state filtering, counting by status, and retrieving stateable lists by @OoBook in https://github.com/unusualify/modularous/commit/9fbfcad026969e846d5d605c26e4b28316c4c6f9
- implement Post and PostTranslation models with relationships, and update test fixtures for comprehensive testing of posts and translations by @OoBook in https://github.com/unusualify/modularous/commit/2a44c0a660f79c5510600228d4917e83097e7158
- enhance base price tests by adding assertions for formatted price attributes, ensuring correct formatting for raw amounts, discounts, and VAT calculations by @OoBook in https://github.com/unusualify/modularous/commit/dafa32a261e81fa8d136754d1c5010bbd3c6a736
- add translationForeignKey property to TranslationsTestModel for enhanced translation handling by @OoBook in https://github.com/unusualify/modularous/commit/8abd688ff8193c8fd759faf2ad599c00e799901f
- add comprehensive tests for repository query functionalities, including pagination, filtering, and relationship handling by @OoBook in https://github.com/unusualify/modularous/commit/bd5bb79868d67a31b154f9cb3344fe08f75e8081
- enhance tests for fileponds trait by adding new ponds and validating translated roles, improving coverage for file handling scenarios by @OoBook in https://github.com/unusualify/modularous/commit/0fd1bbf8affad805117396f0c083625594005af1
- remove obsolete test file for IsTranslatable trait by @OoBook in https://github.com/unusualify/modularous/commit/8c6308c595f3494ea8c3e4b2d6884e90b5709456
- correct assertion for timestamps property to reflect expected behavior by @OoBook in https://github.com/unusualify/modularous/commit/b0332857107f33bc115cf9405aaa3ca97a74e516
- add 'has_language_based_price' attribute and enable mutation for priceable models by @OoBook in https://github.com/unusualify/modularous/commit/27961307a1f7a25f7fccd966d78e17e13e5cabbc

### :package: Build

- update build artifacts for v0.53.0 by @OoBook in https://github.com/unusualify/modularous/commit/4eb103622c9f7278768d1c99a234d489053b2431

### :beers: Other Stuff

- add default properties for form and table actions, attributes, and orders by @OoBook in https://github.com/unusualify/modularous/commit/dd0a3529edd8d248d9cc8720cd0a9915ae3d1573
- remove console log from message box height calculation for cleaner code by @OoBook in https://github.com/unusualify/modularous/commit/51d4477b792980a778ac3374b41f055f53349a70
- remove unnecessary console log for cleaner code by @OoBook in https://github.com/unusualify/modularous/commit/179f6f49bf87b5aeb84baf92b33a0fb3cbd90cbd
- remove unnecessary console log for cleaner code by @OoBook in https://github.com/unusualify/modularous/commit/af4544541aa8416ea08e1a8e06fc139e4afb2cec
- remove commented-out debug code for cleaner implementation by @OoBook in https://github.com/unusualify/modularous/commit/a171fff43c5e39894de1ed94f75f247d04d1fb52
- correct indentation in props definition for improved readability by @OoBook in https://github.com/unusualify/modularous/commit/c44d8165aaee0c2d260f7c69f78a35ca18b981cd

## v0.52.1 - 2025-11-19

### :wrench: Bug Fixes

- replace hardcoded strings with translatable strings for payment status warnings by @celikerde in https://github.com/unusualify/modularous/commit/641b28ccf7938d12bbc27ee66fcfbb7871a0b3c2

### :lipstick: Styling

- lint coding styles for v0.52.0 by @OoBook in https://github.com/unusualify/modularous/commit/c472f84843e1bb97d14f0480de36661e9346cdca

## v0.52.0 - 2025-11-19

### :rocket: Features

- add support for OAuth registration in ModularousUserRegistering and ModularousUserRegistered events by @OoBook in https://github.com/unusualify/modularous/commit/7e4a6180f07360b2a535cc81a2aca3509300a45c
- add spreadableKeys property and method to retrieve keys from spreadable content by @OoBook in https://github.com/unusualify/modularous/commit/db8035bfd08854f77ce989879f6af43af31a242a

### :wrench: Bug Fixes

- update scopeIsCancelled to reflect correct status as CANCELLED by @OoBook in https://github.com/unusualify/modularous/commit/540bbe7e01018ac23df27e7d2041ad40bc913850
- update table name in getTable method and enhance tests for assignment status accessors and relationships by @OoBook in https://github.com/unusualify/modularous/commit/443dbf72c9ef91e89057abf8f89762b57c2fa26b
- add spreadableClass property to extend ModularousCompany functionality by @OoBook in https://github.com/unusualify/modularous/commit/edab9509f8a0d1dc86857abddad991d2179dac9c

### :recycle: Refactors

- rename user-related methods for clarity and update tests for user existence checks by @OoBook in https://github.com/unusualify/modularous/commit/f3d14bad2e7dd9502e4931c690b38eaa3397da34
- remove unused route binding methods to streamline code by @OoBook in https://github.com/unusualify/modularous/commit/d3078cf774a51678ec81a808dd6fbab6e29b0b27
- implement HasCompany trait for user-company relationship and enhance company validation logic by @OoBook in https://github.com/unusualify/modularous/commit/e8db90c69f5187fafdcd045a4a2ac9197b0cbcd5

### :lipstick: Styling

- lint coding styles for v0.52.0 by @OoBook in https://github.com/unusualify/modularous/commit/458f6a0657b26cd87a8684130409acce1974b524

### :white_check_mark: Testing

- add CHECKOUT and PROVISION statuses to enum tests by @OoBook in https://github.com/unusualify/modularous/commit/b2f9a132dce29d4dc4698f65e2d834da6c819435
- add assertions for stateable state changes in updating check by @OoBook in https://github.com/unusualify/modularous/commit/e0e9501bea05dc731c89d1e82a211d864423ed41
- add test for repeatable relationship and create TaggedTest for table retrieval by @OoBook in https://github.com/unusualify/modularous/commit/ff103a647ad501c463a2f3763849c8765b7ba04b
- add tests for extra metadata fields and ensure proper handling in fillable attributes by @OoBook in https://github.com/unusualify/modularous/commit/26f83b6223b0692133166708ae6583b152c1f68a

### :beers: Other Stuff

- add docblock for spreadableClass property to improve code documentation by @OoBook in https://github.com/unusualify/modularous/commit/8d7161b2bfb6cba26bf7fc35ad686baae31d7daf

## v0.51.6 - 2025-11-14

### :wrench: Bug Fixes

- update trueValue and falseValue to use integers instead of booleans by @OoBook in https://github.com/unusualify/modularous/commit/f1831ea23e0426999a9b419eafb626b6e1b43c33

## v0.51.5 - 2025-11-13

### :rocket: Features

- enhance parseConnector method to support object notation parsing by @OoBook in https://github.com/unusualify/modularous/commit/cf383031b7fd653eece9435fd17115c13a4caca5
- integrate newConnector handling in input hydration for polymorphic inputs by @OoBook in https://github.com/unusualify/modularous/commit/cec3b3e87327540f72f31bd71806b8beb62b6e1f

### :wrench: Bug Fixes

- simplify model value updates and remove redundant checks by @OoBook in https://github.com/unusualify/modularous/commit/9b8a7744ab9e091931039f4e44781d6ecf61c080

### :lipstick: Styling

- lint coding styles for v0.51.5 by @OoBook in https://github.com/unusualify/modularous/commit/34e53307fdda251e2dae66f142fa123bbb5b5b43

### :package: Build

- update build artifacts for v0.51.5 by @OoBook in https://github.com/unusualify/modularous/commit/93831b61abdc6fe42ae9227906b419629e1882e5

## v0.51.4 - 2025-11-12

### :wrench: Bug Fixes

- handle undefined slots gracefully by using optional chaining by @OoBook in https://github.com/unusualify/modularous/commit/fbe1c835fedf65ab58b4698003844169a40c3192

### :package: Build

- update build artifacts for v0.51.4 by @OoBook in https://github.com/unusualify/modularous/commit/4d232bcf54db78a5fd45f825e31ba2867f54884b

## v0.51.3 - 2025-11-12

### :wrench: Bug Fixes

- correct query syntax in isClient method for role checking by @OoBook in https://github.com/unusualify/modularous/commit/a3cff0089b51e5d5c8af48507de0e40324282e3b

## v0.51.2 - 2025-11-12

### :rocket: Features

- add lock_company_edit configuration and update ProfileController to handle company edit locking by @OoBook in https://github.com/unusualify/modularous/commit/73bd4e81ba0a75eb726ff8ab80412ba89c84b5fe

### :recycle: Refactors

- optimize isClient method and update billing banner logic for better clarity by @OoBook in https://github.com/unusualify/modularous/commit/b7cf264dfae31bde8d2955606a9d769069cac9f2

### :lipstick: Styling

- lint coding styles for v0.51.2 by @OoBook in https://github.com/unusualify/modularous/commit/d5a0c6b19bed45b87cac083456175867e6f4b26b

## v0.51.1 - 2025-11-11

### :wrench: Bug Fixes

- update billing banner reference to use profile object by @OoBook in https://github.com/unusualify/modularous/commit/3d37eeb2934dd60bd62f369e76897dc56a050f6e

### :package: Build

- update build artifacts for v0.51.1 by @OoBook in https://github.com/unusualify/modularous/commit/5d192547b06150364679bf1c91bdd41aa292c176

## v0.51.0 - 2025-11-11

### :rocket: Features

- add matchAnyPattern function and enhance array casting logic for improved attribute handling by @OoBook in https://github.com/unusualify/modularous/commit/4d6d8b158b9507f93845114207e8c50083a3593e
- add English and Turkish slug dictionaries for enhanced string handling by @OoBook in https://github.com/unusualify/modularous/commit/cd6d8166b2b5e0bda5829dbbb8926df2d4c9a26d
- enhance tag management by adding dynamic slot support and update payload handling by @OoBook in https://github.com/unusualify/modularous/commit/bbd2a9faf404853b1254a62367115e8c82e5add1
- implement locale-specific tagging functionality with migration, controller, and model updates by @OoBook in https://github.com/unusualify/modularous/commit/340b17b1aceadcd44ad2d74fc576160d01bdd746
- add maxFileSize parameter to attachment input for enhanced file upload control by @celikerde in https://github.com/unusualify/modularous/commit/cc68e0167606555a6ff82bb80ad65689e41565ee
- enable sorting and searching for 'name' and 'surname' fields in user configuration by @OoBook in https://github.com/unusualify/modularous/commit/78ae5a0b73941c15a9734b8a0d8565ab15083d21
- add filter options for company selection in user configuration by @OoBook in https://github.com/unusualify/modularous/commit/8ded4a75bc4ff1f84ee890dcf405ef2789a28ac3
- add repeatable method for morphTo relationship handling by @OoBook in https://github.com/unusualify/modularous/commit/f379303ab8217a3f823f322309fc30024b048f86
- update registration notification to use configurable email verification class by @celikerde in https://github.com/unusualify/modularous/commit/862abd4cb8f9752ea2c08120fc73cf9202e0db6c
- add success message for request submission in English and Turkish by @celikerde in https://github.com/unusualify/modularous/commit/b856fc73ba486dc691d4f3ecb1f13383125f2c5b
- update isInApp method to exclude blank items from modularous route check by @OoBook in https://github.com/unusualify/modularous/commit/c733a490c20202e44aab3775571e2bbc5f30dcb7
- enhance repeater functionality with role and locale filtering, and add utility methods for retrieving repeater fields and roles by @OoBook in https://github.com/unusualify/modularous/commit/99b70376ac6d07187cfb2aec4b5ac6de850446b4
- enhance repeater functionality with new object handling, unique input filtering, and improved model hydration methods by @OoBook in https://github.com/unusualify/modularous/commit/600e32315c31a91d2bd298919b2b0da40f3295c2
- add new modal service and response component for email verification by @celikerde in https://github.com/unusualify/modularous/commit/79aa22a386a80a4db606c2a1a713b7c3010973cd
- add new payment statuses CHECKOUT and PROVISION with corresponding labels, colors, and icons by @OoBook in https://github.com/unusualify/modularous/commit/566408b25ba643e42c75ab51f2252830b307b4f7
- add default VAT rates for currencies and enhance PaymentCurrency model with VAT rate handling by @OoBook in https://github.com/unusualify/modularous/commit/49c9cb3c2cac6ffbad3de0b2b0fe2f48dfc88c50
- :sparkles: implement country-based VAT rates and billing banner functionality by @OoBook in https://github.com/unusualify/modularous/commit/2db64ff132bb1ab5430ab24cf3eb3e5c629f7a6b
- add defaultPaymentPriceFields method for dynamic price field handling by @OoBook in https://github.com/unusualify/modularous/commit/8084a12e11f8e4f90ace57b4c5d5d7d1fd866c1e
- implement user verification event and trigger on email link send by @OoBook in https://github.com/unusualify/modularous/commit/3d6464f6b80b7d52c21d0dd0fe42fae75f681368
- :sparkles: implement UTM parameters service with middleware and facade by @OoBook in https://github.com/unusualify/modularous/commit/424b214ee1f6e0c428d3ac9800a66b7ce0b568a5
- enhance state management in models by @celikerde in https://github.com/unusualify/modularous/commit/cd1ae86b0c4066f541c22864fd486fdad6990f6a
- revert success route for email verification registration and update redirect logic in SendsEmailVerificationRegister trait by @celikerde in https://github.com/unusualify/modularous/commit/5777e653283a8b6844d4c35284dd57097e7cdab1

### :wrench: Bug Fixes

- enhance attribute handling in Locale component by ensuring rules are initialized properly by @OoBook in https://github.com/unusualify/modularous/commit/89f603fe9ed02247cb5c1f8df68c990498575792
- correct return statement for attribute casting in RecursiveStuff component by @OoBook in https://github.com/unusualify/modularous/commit/3bf64ca928445501a25a54cb3bd20063aacc71c7
- ensure 'translated' property defaults to false if not set in schema inputs by @OoBook in https://github.com/unusualify/modularous/commit/3c8b05738462b3b4dec410fb7f1ccd220de55185
- add conditional rendering for displayedLocale in Locale component by @OoBook in https://github.com/unusualify/modularous/commit/2cd3fc1d81ef82a62b799e7173d1cb0f4cf4943a
- ensure formatter is an array and validate its presence before filtering by @OoBook in https://github.com/unusualify/modularous/commit/dc0a66d6e12bb5b9655840db86a78173bb6f69fd
- adjust conversion decimals to zero for accurate currency conversion results by @OoBook in https://github.com/unusualify/modularous/commit/cffd359cf4129c43e9abf6e70ee2d8b4e1341315
- simplify model parsing by directly using raw model values by @OoBook in https://github.com/unusualify/modularous/commit/ab46a6c141e39ca21f1fc9602d21e6d01c8d640c

### :recycle: Refactors

- add validate button for super admins in Form component; remove redundant validate button from Table component by @OoBook in https://github.com/unusualify/modularous/commit/2ee2efacd83ac32a21c8af7ddb4d9826210e9c7a
- update payment status conditions to use constants and modify headline for MyPayment by @celikerde in https://github.com/unusualify/modularous/commit/078876fc399badc165aa029e484359947aaaaeec
- streamline tag creation logic by removing commented code and simplifying variable usage by @OoBook in https://github.com/unusualify/modularous/commit/82b907fdccdaa589cb54c3d25f3e3a555c1a05fa
- simplify locale tags initialization and improve fillable attribute handling by @OoBook in https://github.com/unusualify/modularous/commit/ff5ba7d06f03afcc4808aae80ae429d56939b9cb
- remove unnecessary language variable addition in runTest method by @OoBook in https://github.com/unusualify/modularous/commit/7e19ede22f43f85521cae171c2a054ff482c2cf8
- rename company_registration middleware to modularous.company.registration for consistency by @OoBook in https://github.com/unusualify/modularous/commit/2cdb8298e8abf0bed749047424b08f2cc6f2c161
- restore and clean up definedRelations method with proper documentation by @OoBook in https://github.com/unusualify/modularous/commit/d5918a0353b067d8c477a40f045d1f97178ef953
- disable view generation in modules configuration for improved clarity by @OoBook in https://github.com/unusualify/modularous/commit/33fcc0e50be02b53d7699af43195cdcce4a20c56
- add selectable property to input hydrates and implement selectable input handling by @OoBook in https://github.com/unusualify/modularous/commit/a8af8a712f13d3be787013107a8d2c5ac8cc6b64
- comment out success route for email verification registration by @celikerde in https://github.com/unusualify/modularous/commit/155495f4ed15de8fbb8c14851e45a0bf4e70ea7b
- clean up response structure in sendVerificationLinkResponse method by @celikerde in https://github.com/unusualify/modularous/commit/a51a7db77ee01f84e169ce1f5a6d9921deb8f418

### :lipstick: Styling

- lint coding styles for v0.51.0 by @OoBook in https://github.com/unusualify/modularous/commit/a204630117a2dbd26acf1e3926f517ee642a1d8a

### :white_check_mark: Testing

- enhance PHPUnit commands and add repository tests by @OoBook in https://github.com/unusualify/modularous/commit/6a083972e407f7cea044d6ed5d2b2befdbdf1feb
- add empty line for improved readability in PricesTraitTest by @OoBook in https://github.com/unusualify/modularous/commit/a26bc4fc5022904496969f720ba9e8f7214f68b3

### :package: Build

- update build artifacts for v0.51.0 by @OoBook in https://github.com/unusualify/modularous/commit/5ea841a4356caaf06a3c2bf91706bba3a7037fe5

### :beers: Other Stuff

- remove console log for dark and light symbol values by @OoBook in https://github.com/unusualify/modularous/commit/03d9c15e8ddb123b7c9e33ba891275a90243cc35

## v0.50.1 - 2025-10-27

### :recycle: Refactors

- update cache retrieval method in Tag component for improved state management by @OoBook in https://github.com/unusualify/modularous/commit/25d00bd2e0e2d2cf6db523b701971d918ac83019

### :package: Build

- update build artifacts for v0.50.1 by @OoBook in https://github.com/unusualify/modularous/commit/2b0863af8d491795d15af7e1e31d45751e7f45ef

## v0.50.0 - 2025-10-27

### :rocket: Features

- introduce dynamic logo symbol and sidebar attributes for improved customization by @OoBook in https://github.com/unusualify/modularous/commit/2692be0056d5fff5b7e70fd7d7d2716f624f3436
- add support for HTML labels and enhance label slot functionality by @OoBook in https://github.com/unusualify/modularous/commit/6462db4ebb4b46fc7ca4641b34231fb09a710274
- add functions to check SVG symbol existence and retrieve modularous logo symbol for enhanced icon management by @OoBook in https://github.com/unusualify/modularous/commit/2c063c81e9bb0787cf5112a9639505269e7a44da
- add getThemePath method to retrieve theme directory for enhanced theming support by @OoBook in https://github.com/unusualify/modularous/commit/5fc3f5cb2044fffcd8a0776fb3b94372af7966d4
- enhance sidebar logo symbol retrieval with locale support for improved customization by @OoBook in https://github.com/unusualify/modularous/commit/d98fc83955b4580d11ff04f4e4c272f5b2b8fcfb
- add get_modularous_locale_symbol function for locale-based logo symbol retrieval by @OoBook in https://github.com/unusualify/modularous/commit/06971b60b21fb065075d928bcf0f9b64c3f6e56f
- add computed properties for ambient state including isHot, appName, and appEnv for enhanced configuration management by @OoBook in https://github.com/unusualify/modularous/commit/df44726057d9d8b54035d5d76bdc38aeb4ac7a13
- add helper functions for SVG symbol management, including existence checks and locale-based symbol retrieval by @OoBook in https://github.com/unusualify/modularous/commit/987a4296c567ebd57b4ef2ee839250b28619f3b2
- introduce useSvg hook for SVG symbol management, including existence checks and locale symbol retrieval by @OoBook in https://github.com/unusualify/modularous/commit/d388d761a5b6e6b626597c0c87e223bc9920d7d2
- implement localization for state names using dynamic localization notation for improved multi-language support by @OoBook in https://github.com/unusualify/modularous/commit/308ea67fc42c227370b6d6ea5e491f6fa70099ec

### :wrench: Bug Fixes

- update IP retrieval method for geoip locale detection by @OoBook in https://github.com/unusualify/modularous/commit/cc884aeb3febb8a1bc3b7fe5b7fb893c503bb4d7
- add language attribute to user registration for improved localization by @OoBook in https://github.com/unusualify/modularous/commit/71f2e7618135e55f2694e46aa9e317389a937519
- enhance tag component by integrating cache functionality and updating store mutations for improved state management by @OoBook in https://github.com/unusualify/modularous/commit/21e47cc90b0118c9ea0a9cf5c3604d617c9f66b3

### :recycle: Refactors

- streamline modal structure and improve slot usage for terms and conditions by @OoBook in https://github.com/unusualify/modularous/commit/eb029ad339788ae7d491a867833ea9e0f2f4f0f1
- remove style prop and simplify template structure for better readability by @OoBook in https://github.com/unusualify/modularous/commit/96a3f3a1668ac816c0a39fbd403f5a22e32fc89f
- enhance logo customization by adding locale support and dynamic attributes for improved branding by @OoBook in https://github.com/unusualify/modularous/commit/88afbe029472ab19324e4d2fbed5ddeed50268a8
- add 'hideDetails' attribute to various form fields for improved UI customization by @OoBook in https://github.com/unusualify/modularous/commit/593f9d8f3351767ef741b0a95005bce6f250f6ec
- streamline logo symbol management by replacing dynamic retrieval with static assignments for improved performance and consistency by @OoBook in https://github.com/unusualify/modularous/commit/85af66ea067bfad6dc3d52db3876039181f8f237
- remove hardcoded locale assignment to allow dynamic locale configuration by @OoBook in https://github.com/unusualify/modularous/commit/8e7394aee3d43dfc902c818c9bc94120363d65cc
- comment out unused attribute casting and remove empty data and created hooks for cleaner component structure by @OoBook in https://github.com/unusualify/modularous/commit/7da9883d50ae2f6add30881c30c57fff3520a3bb
- replace useStore with direct store import for improved clarity and performance by @OoBook in https://github.com/unusualify/modularous/commit/45fbfe217b825a9bf41a28686b8948d6b556ca05
- remove variant binding from combobox and add default variant prop for improved flexibility by @OoBook in https://github.com/unusualify/modularous/commit/f1cb1b9b8244f229ef2bcd886eee79d64b99d21c
- enhance locale input handling by replacing lodash's __isObject with isObject and improving item assignment logic for better clarity by @OoBook in https://github.com/unusualify/modularous/commit/42dbe5d1d9dfa9f5fac690e0bc5c1de90ffa566b
- integrate useCastAttributes for improved attribute handling in locale input by @OoBook in https://github.com/unusualify/modularous/commit/229e48251f4e752ebebbd74d94e0a63736332839

### :lipstick: Styling

- lint coding styles for v0.50.0 by @OoBook in https://github.com/unusualify/modularous/commit/4fdeeab5ccd0490bf81c57445ee5c75ebbcb75c2

### :package: Build

- update build artifacts for v0.50.0 by @OoBook in https://github.com/unusualify/modularous/commit/364dd848d408fa4a966c58366f7bec0ec701476d

## v0.49.2 - 2025-10-22

### :wrench: Bug Fixes

- update VAT display to use translation key for localization by @celikerde in https://github.com/unusualify/modularous/commit/5a053008d647a2300fdea81a5a243f6b9f5396ab
- update button text to use translation keys for localization by @celikerde in https://github.com/unusualify/modularous/commit/a89ff1a15beedde7012a5bcacbdc2bc3f6867885
- update labels to use translation functions for localization by @celikerde in https://github.com/unusualify/modularous/commit/b696b7c7a78fa5bc521a9a8fcfb83be4fc336cff
- apply translation functions to status labels for localization by @celikerde in https://github.com/unusualify/modularous/commit/0a60d43e22d7aaf79ed4ba7540e2a7023f7058b6
- apply translation functions to labels for localization by @celikerde in https://github.com/unusualify/modularous/commit/66765bdfa0bce732eeae4fb12605e133e419169a
- apply translation functions to alert messages and validation rules for localization by @celikerde in https://github.com/unusualify/modularous/commit/ba3dd24360a65cc15ee472b7084ba9859db77358
- apply translation function to "Total Pay" label for localization by @celikerde in https://github.com/unusualify/modularous/commit/08a9c42a7a07f9a5581d70de124d0239ec7afd24
- update VAT display to use translation key for localization by @celikerde in https://github.com/unusualify/modularous/commit/69c26bda2dc1985b04babaaab8511948af4fd247
- apply translation function to status text, description, and alert messages for localization by @celikerde in https://github.com/unusualify/modularous/commit/e06c7b06a6c67228c8946aeaaa27d65ee3705cde
- apply translation function to sub-text for localization by @celikerde in https://github.com/unusualify/modularous/commit/81a0c02a9d049de37281e247f61a6dad1cb700c7
- apply translation function to success message for localization by @celikerde in https://github.com/unusualify/modularous/commit/0eb7e11bed840a993cbd75d69f3ffce9cc318d90
- add locale to modularous payload and refactor transaction fee handling for improved localization by @OoBook in https://github.com/unusualify/modularous/commit/c1114ad7fe82efbd87d44c2bce1b5a08fa50f46b
- reorder locale determination logic for improved user language handling by @OoBook in https://github.com/unusualify/modularous/commit/d1192d3537439adef2bc774b44caa478a7fdb4c4
- update middleware key for consistency in localization handling by @OoBook in https://github.com/unusualify/modularous/commit/8dbc0662d6a14fade27de271dcd146ac003cfea5
- apply translation function to currency label and transaction fee description for improved localization by @OoBook in https://github.com/unusualify/modularous/commit/84c9f8a2682bc3b85d3289dbf61c476cc63df09c
- update preferredLocale method to return user language or app locale for improved localization by @OoBook in https://github.com/unusualify/modularous/commit/96c576c36549f77e371ad3ecf07d1c904d832fdd
- add edit message for My Payment in English and Turkish language files by @OoBook in https://github.com/unusualify/modularous/commit/7f5b9790aef02d45983cd845fa2b4fe3f86d12cb

### :recycle: Refactors

- enhance validation messages with translation support by @celikerde in https://github.com/unusualify/modularous/commit/880485b5e647cc62cf02f0803036684486682e52
- integrate moment.js for locale handling and add additional locales for improved localization support by @OoBook in https://github.com/unusualify/modularous/commit/36a8af636b2825ba74d99368cb13c33e6a26c140
- add language files for German, English, French, Dutch, and Turkish in FilePond component by @OoBook in https://github.com/unusualify/modularous/commit/db77c60aa1a93d28bf282f95572f0330c8f4b7bc

### :lipstick: Styling

- lint coding styles for v0.49.2 by @OoBook in https://github.com/unusualify/modularous/commit/0a5b1075cf2d6c8ca60378a757e153da3c681331

### :package: Build

- update build artifacts for v0.49.2 by @OoBook in https://github.com/unusualify/modularous/commit/59d6f2dfacb210a21eccc9653e2eef9ae02d5642

## v0.49.1 - 2025-10-20

### :wrench: Bug Fixes

- add transaction fee calculation to checkout process by @OoBook in https://github.com/unusualify/modularous/commit/e46d4e5603cd7e7b349b001872994c7329709372
- add refresh options to edit method by @OoBook in https://github.com/unusualify/modularous/commit/002e8cdaea2fc49630db5a73d36638b5bf9d64ec

## v0.49.0 - 2025-10-18

### :rocket: Features

- add number input handling to getModel function by @OoBook in https://github.com/unusualify/modularous/commit/fc8a783166eb5c5bd1c3779b128696452789dbe1
- add configuration option for including transaction fee by @OoBook in https://github.com/unusualify/modularous/commit/ab40791fa637804839fbd01c56a5ec9e21e8b8d2
- add transaction fee percentage to payment services by @OoBook in https://github.com/unusualify/modularous/commit/544b6197f4e2c713cc811bdc501728e182743e1f
- enhance transaction fee handling in payment process by @OoBook in https://github.com/unusualify/modularous/commit/e05a5df4d28d46fc80abb9162c9717adc201cdf6
- enhance payment configuration with new input types and roles by @OoBook in https://github.com/unusualify/modularous/commit/b2a36c3298c01f1adbd6184f19d1d2f020e5d258
- enhance currency handling and total amount calculations by @OoBook in https://github.com/unusualify/modularous/commit/d64cbbdcb7372a77c0e6a64f99de38106b5925d5
- add computed attribute for name with rate by @OoBook in https://github.com/unusualify/modularous/commit/550375c33a9e83737aef32872c92b4c60469786f
- enhance payment price updates with VAT and discount handling by @OoBook in https://github.com/unusualify/modularous/commit/f011edf1ef6b0b1bb7e01543a5cd1ae629bb5e86
- add hide-details attribute to input component and remove debug log by @OoBook in https://github.com/unusualify/modularous/commit/f55eb43050c5b3f772d20a8fb1ad7974e7bfe4c4
- update confirmation messages and translations in English and Turkish by @celikerde in https://github.com/unusualify/modularous/commit/43948392a43000fba19e770415225a2bc0208dc0
- add new configuration files for deferring UI settings, navigation, and user forms by @OoBook in https://github.com/unusualify/modularous/commit/8d296599b3c7757ec113ba4274caada905302ed0
- add English language file for Vuetify components by @OoBook in https://github.com/unusualify/modularous/commit/897c481fd30fdf535a339f84ad3eecf9f8f78256
- add Turkish language file for Vuetify components by @OoBook in https://github.com/unusualify/modularous/commit/aa1901105cc7b612aeab572a865103940b82c0ed
- enhance translatedAttribute method to support locale selection by @OoBook in https://github.com/unusualify/modularous/commit/a034dc3b7519dfc5a25c91bc55a4103f166b3218
- add mergeConfigFrom function for configuration merging by @OoBook in https://github.com/unusualify/modularous/commit/7c5bc60c66169a02c96ed24fdbd254f351f318f4
- introduce LoadLocalizedConfig middleware for dynamic configuration loading by @OoBook in https://github.com/unusualify/modularous/commit/43c532d11af6466a29fd7c718ea68bc74afc3a3d
- enforce English locale for translations index route by @OoBook in https://github.com/unusualify/modularous/commit/587c7faa16b3b05f6176c7806c5ea564c537b3ba
- enhance language handling and form translation support by @OoBook in https://github.com/unusualify/modularous/commit/1c3c9e3aa64a5678b9f65e3d621338fc83960bb5
- restructure language files and enhance translation handling by @OoBook in https://github.com/unusualify/modularous/commit/53ef13e2db1e95c0c0bf96b26613c53f236ed06e
- expand language files and improve translation structure by @OoBook in https://github.com/unusualify/modularous/commit/c5a273532103596f2b0c3053df9de97f1a489ba5
- enhance title handling and add attribute casting by @OoBook in https://github.com/unusualify/modularous/commit/8e83f0c928b251c760a1e96c0b4ac5aa22fdfca8
- add Spatie Laravel-Permission middleware aliases by @OoBook in https://github.com/unusualify/modularous/commit/5d80e49a2fabccb48b86a855f792906a4e3b19ac
- add raw route and config retrieval methods by @OoBook in https://github.com/unusualify/modularous/commit/94bdfc65a0022f33d33cf63008d0bb361978ed65
- add resetConfig method for configuration management by @OoBook in https://github.com/unusualify/modularous/commit/f23876e9ffc7d28ff91338b90d1fd165ac11c7e4
- add SyncTranslationsCommand and enhance translation publishing by @OoBook in https://github.com/unusualify/modularous/commit/142aeb1d676f51711cdd26c7e03a2c5a69d4603f
- add user and URL tracking to model events by @OoBook in https://github.com/unusualify/modularous/commit/be02f2f39f26e5d0dd19bab19c2d337e312ad331
- add tracking for changed attributes and relationships by @OoBook in https://github.com/unusualify/modularous/commit/69f78f9425a2c5a4ac20d354fb79cea0cd39b932
- add new labels for Messages and Open by @celikerde in https://github.com/unusualify/modularous/commit/723abc512935e7443ac6d7c77b23546b2d63a07b
- add new labels for improved localization support by @celikerde in https://github.com/unusualify/modularous/commit/fb5f878ee1afeda12763d718904520655e81d6f5
- enhance Turkish language support with new labels by @celikerde in https://github.com/unusualify/modularous/commit/150617d4dd104c73245d196ca2277b58316f7072
- implement locale preference for user entity by @celikerde in https://github.com/unusualify/modularous/commit/f9d3753891406850ad5c5f1878a055a182589c18
- expand English language support with new labels by @celikerde in https://github.com/unusualify/modularous/commit/7a2847926e113d1f1423a88002154b03b82e01a3
- expand Turkish language support with new labels by @celikerde in https://github.com/unusualify/modularous/commit/1f54ace5498daaa0efdddd085880f3e342e6f65b

### :wrench: Bug Fixes

- enhance validation rules for name and surname fields by @celikerde in https://github.com/unusualify/modularous/commit/b64af91bcb72800a420da06a81906b9ba4ce4e15
- enhance minRule and add nameRule for improved input validation by @celikerde in https://github.com/unusualify/modularous/commit/51f1eed795501e514b4d4c0ca90ef9d3149485e7
- update validation rules for company field and refine name/surname rules by @celikerde in https://github.com/unusualify/modularous/commit/6322b522a1f38e299d4bfc1a723b9433ea7f0d15
- normalize name and surname inputs during user registration by @celikerde in https://github.com/unusualify/modularous/commit/6e728010999128bc9d61dc5d763d7d3c54be9155
- trim whitespace from name input in user registration test by @celikerde in https://github.com/unusualify/modularous/commit/aa0563d2f012590e915386904b27771bde46d43c
- uncomment environment variable setup in runVueProcess method by @OoBook in https://github.com/unusualify/modularous/commit/d3f242c010224ae44a168dd9ddb14cae1960d2ba
- enhance validation for name and surname fields by @celikerde in https://github.com/unusualify/modularous/commit/700ea6365066c2ff662f49c9934af726c7f570c2
- refine name validation regex to disallow consecutive hyphens by @celikerde in https://github.com/unusualify/modularous/commit/05b495429c3747bf68554b8343831343fc79a1de
- add allowed roles for payment refund dialog by @OoBook in https://github.com/unusualify/modularous/commit/03022496c094aa556c8e6f741f2aef6da69a78ba
- set default active tab to 0 by @OoBook in https://github.com/unusualify/modularous/commit/adfef47c053f60a14c02252292f4d76137092673
- update markReadMyNotifications method to handle Inertia requests by @OoBook in https://github.com/unusualify/modularous/commit/6f296ba9106d647647de33b263369fa48d64c0de
- uncomment markAsRead logic in markReadMyNotifications method by @OoBook in https://github.com/unusualify/modularous/commit/106afe9fe743efaab68ee297561e7d72a06e5552
- correct button text translation keys in registration success messages by @OoBook in https://github.com/unusualify/modularous/commit/5852fa7c8ef728db515f7f140259f9dc4c1b3c49
- improve locale determination logic by refining conditions for auto locale detection by @OoBook in https://github.com/unusualify/modularous/commit/93e8ea20c7a3eb157310b4a74f53e19480974dea
- standardize 'VAT' to 'Vat' and update Turkish state label by @celikerde in https://github.com/unusualify/modularous/commit/44a728b8b4fecb0f9dda6aa75768e57c025602cb
- update English and Turkish language files for consistency and clarity by @celikerde in https://github.com/unusualify/modularous/commit/42f5400d31078d36b30bf0a82970c8cb6b473132
- update media library dialogs in English and Turkish by @celikerde in https://github.com/unusualify/modularous/commit/6f965f431decf627e968fdaf627dfae146848baf
- update English and Turkish messages for consistency and clarity by @celikerde in https://github.com/unusualify/modularous/commit/db14bad61a696eaa0cf25b2538f30d280a4be8d9
- add 'price_type' entry to English and Turkish language files by @celikerde in https://github.com/unusualify/modularous/commit/554b214a463fb76859809b4e55ef49a2b7e41c15
- restore 'previous' entry in English and Turkish pagination files by @celikerde in https://github.com/unusualify/modularous/commit/e45aaf984fbe42af6304beeb61447dae4cf2d16a
- update English payment messages and add Turkish translations by @celikerde in https://github.com/unusualify/modularous/commit/75a0883fb40e12c36b7d03ece5d7723f5c95dad5
- update English and Turkish table headers for consistency by @celikerde in https://github.com/unusualify/modularous/commit/33258907028f35da4f4d9214aa6797a0c8a401f4
- update English and Turkish validation messages for consistency by @celikerde in https://github.com/unusualify/modularous/commit/bb97d09300680f1a0260bc578d9dbfc86b2e52d5
- update English and add Turkish email verification messages by @celikerde in https://github.com/unusualify/modularous/commit/6b50bc13a61d5130994a427802ba91ebdb26c3ec
- update English translation file for clarity and consistency by @celikerde in https://github.com/unusualify/modularous/commit/453eada666493a61547cdfbc21481ed7fdab5e6a
- enhance English and Turkish language files with new entries and updates by @celikerde in https://github.com/unusualify/modularous/commit/56947391f7506ca961c34a6b63b2dc72ed4d42d6
- update translation for 'or' prompt in Auth component by @celikerde in https://github.com/unusualify/modularous/commit/10818823a1a80874e421e660e0e2fc17e67d1a96
- update setStateablePreview method to use locale parameter in translatedAttribute by @OoBook in https://github.com/unusualify/modularous/commit/429194791a7220798e9ca2de34d63f25bc3e9ceb
- add rules prop and restore hide-details for date input component by @OoBook in https://github.com/unusualify/modularous/commit/f80feea44ca8cb2d7218ad119476bcdcfdff3eea
- update group title handling and improve item grouping logic by @OoBook in https://github.com/unusualify/modularous/commit/1bd14c58fb9080bb7d98c307dc7b4e128d97bd7b
- update English and Turkish authentication language files by @celikerde in https://github.com/unusualify/modularous/commit/d196db123c85d4c087d70e1d199839d9dfce316d
- enhance state hydration with locale fallback support by @OoBook in https://github.com/unusualify/modularous/commit/b7176983cf277f35ffaa2b43cd4081b255c0ad7c
- correct punctuation in 'forgot-password' label for localization consistency by @celikerde in https://github.com/unusualify/modularous/commit/02cc3539bc6e4214ab05cbf8afdba8ed91762a5b
- correct button text key for email verification success form by @celikerde in https://github.com/unusualify/modularous/commit/031fff7b403ad850a995ab5830b6e014c8e821b1
- handle missing getChangedRelationships method gracefully by @OoBook in https://github.com/unusualify/modularous/commit/34eba15b38ffb15504419cc0847859e80bfb1650

### :recycle: Refactors

- remove console logs for cleaner code by @OoBook in https://github.com/unusualify/modularous/commit/f973429eb80e9cfcec731d61060e73cf256ad034
- modularize Vuetify initialization and enhance i18n integration by @OoBook in https://github.com/unusualify/modularous/commit/43c8231836fa8818bce0edd53c158d0819dffa33
- remove console log from global Vue mixin by @OoBook in https://github.com/unusualify/modularous/commit/d7e579fb69b399cd3fa169b3fdefc167829ccc1f
- replace getRouteConfig with getRawRouteConfig by @OoBook in https://github.com/unusualify/modularous/commit/35a38a62dccce507079d521e38d5ab4835144779
- implement preload methods and clean up constructor logic by @OoBook in https://github.com/unusualify/modularous/commit/e573399e0d4281dc67381515c20f7061de6fc281
- enhance file and image handling with fallback locale support by @OoBook in https://github.com/unusualify/modularous/commit/f0c1a42da4cd950438ee6f1b362aedc632370d34
- implement loadConfig method for dynamic configuration loading by @OoBook in https://github.com/unusualify/modularous/commit/6165bfeefc969ac4d6cae402dd516c4bf6535d2b
- improve input handling and repository checks by @OoBook in https://github.com/unusualify/modularous/commit/d946f3732a6fb1bd31ae4a4666c6574034752089
- update middleware aliases for modularous by @OoBook in https://github.com/unusualify/modularous/commit/5650f5f56776fde24cb2235272e940f22bef1a75
- enhance replaceCallback function for array handling by @OoBook in https://github.com/unusualify/modularous/commit/7f2f6478e2ce18fffddb599b5afd32f296377d4a
- enhance attribute handling for nested keys by @OoBook in https://github.com/unusualify/modularous/commit/fd93b9e72ed2865aa135defeea6c3a45eb116cd9
- improve locale handling and error logging by @OoBook in https://github.com/unusualify/modularous/commit/0c66c3a571bb1f0466c4371a3cfe418df96444e9
- update labels for localization support by @celikerde in https://github.com/unusualify/modularous/commit/8656cdf93329266a40728156ba83cd5ee4d42199
- update labels for localization support by @celikerde in https://github.com/unusualify/modularous/commit/d784091f0f53517a727cc276ef2e10cdc2186532
- integrate translation function for validation messages by @celikerde in https://github.com/unusualify/modularous/commit/67d4536d0f6a2ff7aa5b5637b3abc059057f1692
- update static labels to use translation function by @celikerde in https://github.com/unusualify/modularous/commit/abae7709a410dbe717afa6e59e87f784406e0077
- implement translation function for input validation and placeholders by @celikerde in https://github.com/unusualify/modularous/commit/fb223070b75cb15844b52b22d1a5661847f9019e
- update welcome message to use dynamic app name by @celikerde in https://github.com/unusualify/modularous/commit/a5a2cf6b6f03f77a74ec0872c981dbde13984d30

### :lipstick: Styling

- lint coding styles for v0.49.0 by @OoBook in https://github.com/unusualify/modularous/commit/1d46fe10473c4e1922a77c1dfe56b767a5eede1f

### :white_check_mark: Testing

- add unit test for normalizeName method by @celikerde in https://github.com/unusualify/modularous/commit/a1c4d01240439366645d62bbc9fdb74e0d9ae973
- add setup for payment_currency_payment_service table creation by @OoBook in https://github.com/unusualify/modularous/commit/e2a6daa90e5925650b31fae4843f10e970c3fa38
- add TranslationServiceProvider to package providers by @OoBook in https://github.com/unusualify/modularous/commit/0edeb47fb2c01eb52a311632f3f6a497bd903a37

### :package: Build

- update build artifacts for v0.49.0 by @OoBook in https://github.com/unusualify/modularous/commit/e4cc78ab5e0073a4a031ac8ba5f52364b43d6749

### :green_heart: Workflow

- enable pushing changes in release workflow by @OoBook in https://github.com/unusualify/modularous/commit/cd02ef028b72748c4504997238393f78f4e48652

### :beers: Other Stuff

- update English and Turkish language files with new authentication phrases and improve existing translations by @OoBook in https://github.com/unusualify/modularous/commit/64b14ab6e1ad09a3b29474d09dd662d01116205b
- format command signature and add alias by @OoBook in https://github.com/unusualify/modularous/commit/9972288803e459bd7d798a713846c9d9cbf487b1
- add available user locales configuration by @OoBook in https://github.com/unusualify/modularous/commit/c6cfdaaf3572fd8a92cc4f2d4ba389835563fdfb

## v0.48.1 - 2025-10-08

### :wrench: Bug Fixes

- update npm install command to include legacy peer dependencies by @OoBook in https://github.com/unusualify/modularous/commit/6642965bb085331b94508c9264574c8af56679fc

## v0.48.0 - 2025-10-06

### :rocket: Features

- add isModularousRoute method to check route prefixes by @OoBook in https://github.com/unusualify/modularous/commit/6855101483b90ad529739959f1e2e7256d476a9e
- enhance cache functionality with new hasCache getter and store state access by @OoBook in https://github.com/unusualify/modularous/commit/7b2e04dc70ec89aedc1d94022ccee9b804a015ae
- add isSameUrl function to compare URLs without query parameters by @OoBook in https://github.com/unusualify/modularous/commit/4c49e0540f98523fcd59cbb5a02a5fde05898701
- add custom hook for user state management by @OoBook in https://github.com/unusualify/modularous/commit/61ffe75c862f30370a88a9c8a74072feb410f61c
- add inertiajs and tightenco/ziggy dependencies by @OoBook in https://github.com/unusualify/modularous/commit/20d6e0de16c291bbac5549638b4ba54473973395
- add Inertia.js and Laravel Vite plugin by @OoBook in https://github.com/unusualify/modularous/commit/39e60795a922e96b1122398a333db16071bc032e
- add Ziggy support detection and update path resolution by @OoBook in https://github.com/unusualify/modularous/commit/f79de218ed00b22c3b7f1df47219438d769bb935
- add modularous configuration functions for navigation, authorization, impersonation, and localization by @OoBook in https://github.com/unusualify/modularous/commit/49987bf2502d9f00ca1f6542d79383fe7e30dc76
- enhance sidebar menu item handling by @OoBook in https://github.com/unusualify/modularous/commit/8bd6b9a759e310b411fea7554ac8dfdb190e313b
- add function for module path retrieval by @OoBook in https://github.com/unusualify/modularous/commit/7d05123574136adfe72537b8be7af98cb3839ad3
- add noSchemaUpdatingProgressBar prop by @OoBook in https://github.com/unusualify/modularous/commit/f06d3383109a5f544bab778baa4ca8313ac635a5
- add noSchemaUpdatingProgressBar prop to StepperContent component by @OoBook in https://github.com/unusualify/modularous/commit/045433634787c09031f0d9759e0f9508f344b4b6
- add isSamePath function for path comparison by @OoBook in https://github.com/unusualify/modularous/commit/1a0483b04d7f7c1a82e11323261eeda5887a0bda
- implement Inertia.js support and related components by @OoBook in https://github.com/unusualify/modularous/commit/dab9461b5fb97db588cfffd5f1d931c87584432a
- add noSchemaUpdatingProgressBar prop to multiple form controllers by @OoBook in https://github.com/unusualify/modularous/commit/157613435993c56d2aecd7fa1239aa20121701b8
- refactor dashboard layout and controller for Inertia support by @OoBook in https://github.com/unusualify/modularous/commit/c2d5d2a4c69c3f254e384077a5d069b4ab89920a
- enhance navigation component with Inertia support and data-level attribute by @OoBook in https://github.com/unusualify/modularous/commit/4f0fc612bcd2532a5482c496c4f95392f93185a2
- update Form component to use formActionsActive for action visibility by @OoBook in https://github.com/unusualify/modularous/commit/c4e79256f498fc2651988569f84fd43fc83cfc9b
- enhance button click handling with Inertia support by @OoBook in https://github.com/unusualify/modularous/commit/e4206858e5ad7928bfcfbe290ad78a03d964e92d
- add useConfig and useUser hooks for improved functionality by @OoBook in https://github.com/unusualify/modularous/commit/6fd5800df7b112b5079514cb0563dc0100eb7c20
- integrate Inertia support in item actions for improved page reload handling by @OoBook in https://github.com/unusualify/modularous/commit/d7b112d480b458c429812e6b33d681f9380bceff
- refactor login form handling with Inertia support by @OoBook in https://github.com/unusualify/modularous/commit/05e02c29c07a203b450204c580f22ff1bcd7d144
- update table configuration with minWidth and formatter changes by @OoBook in https://github.com/unusualify/modularous/commit/0c4f77307c9a31fbd9a6e777cef34bcf80b9f565
- implement select hook with customizable properties by @OoBook in https://github.com/unusualify/modularous/commit/a3dcc52dba62cf4e5e8bb064340b0971b4ceaf23
- add pagination hook with customizable properties by @OoBook in https://github.com/unusualify/modularous/commit/d98130a1ed96ab5465e164a0399340718e477199
- enhance input handling with initial value and object conversion support by @OoBook in https://github.com/unusualify/modularous/commit/1c1c6e8d37ab5423ede51ce2c3961fcc9b105b95
- enhance form schema handling with repository methods by @OoBook in https://github.com/unusualify/modularous/commit/86be99105373f5305762c2ecd5d45698f58dc745
- enhance data retrieval with appends and exceptIds support by @OoBook in https://github.com/unusualify/modularous/commit/18eb77d6a24e9b2d6244b580b504f614818a7e0d
- introduce BrowserHydrate and Vue component for enhanced item selection by @OoBook in https://github.com/unusualify/modularous/commit/326c0ba3b50a5925ccf54f9777dde976b4151442
- add email_with_company attribute for enhanced user information by @OoBook in https://github.com/unusualify/modularous/commit/33a2305262afb59c22b8d8958d33cdf31e3765b7
- implement CreatorHydrate class for custom input handling by @OoBook in https://github.com/unusualify/modularous/commit/65ac20933aa080fd2e25771bc4b4f59f09958a48
- integrate CreatorTrait for enhanced payment record management by @OoBook in https://github.com/unusualify/modularous/commit/71cdc0509fc1ac852b3b4a0ee09387a696c08d70
- add guest user handling to media modal functionality by @OoBook in https://github.com/unusualify/modularous/commit/f5e3d87293a476c183eeb7118b15d468c41ed523
- add input fields for payment amount and payer email by @OoBook in https://github.com/unusualify/modularous/commit/0b9363a1a99b91dce3ee708dc2ba1ed797ca8219
- add global method to check component existence by @OoBook in https://github.com/unusualify/modularous/commit/534590b271261c0c2a89227a256cc9a2dfc696ef
- add top and bottom slot components for enhanced layout flexibility by @OoBook in https://github.com/unusualify/modularous/commit/7f8f99cea57870e3d3288d23498b9bc4f5a4b2e9
- conditionally register custom components for custom builds by @OoBook in https://github.com/unusualify/modularous/commit/f899e57cf1f1520447d885e34e1e590ae4dd2db9
- enhance Vue build process with custom environment support by @OoBook in https://github.com/unusualify/modularous/commit/2c8e10d9acaefd5a6ddbd8798bca70e2cb758ca3

### :wrench: Bug Fixes

- disable clearable option for email input in password form by @celikerde in https://github.com/unusualify/modularous/commit/44319e1e34e85cf1c4b211858a60a0b9ff525e2f
- ensure login modal is triggered on 401 error response by @OoBook in https://github.com/unusualify/modularous/commit/85b5daccc274cd6bc026cf00d842e27472b4d62f
- update modal-service route to bypass middleware by @celikerde in https://github.com/unusualify/modularous/commit/86fe16325e3060534431da3fd7af64a41efda263
- enhance error handling for OAuth authentication by @celikerde in https://github.com/unusualify/modularous/commit/9cdfd8002e0209bee1d7fefc1ab53f2b7225cc1f
- update null and undefined check for newValue assignment by @OoBook in https://github.com/unusualify/modularous/commit/1f3c530b0e5ce89a205e716c37863399b742843d
- correct reference to TableForms in custom form model assignment by @OoBook in https://github.com/unusualify/modularous/commit/29bf1a48537039aeb5e1a82d3d6a89a15e65d16d
- improve badge value handling with lodash utilities by @OoBook in https://github.com/unusualify/modularous/commit/806092f25a51f25c7b1972f34eb2ccfbe0f3da56
- disable router reload for alert timeout handling by @OoBook in https://github.com/unusualify/modularous/commit/7f5f3da088828121cf49676c1aa5c0f319ed63cd
- readd bulkForceDelete functionality for improved item management by @OoBook in https://github.com/unusualify/modularous/commit/469722e2431d42e48d04aa7938a75256a0c5c347

### :recycle: Refactors

- refactor store initialization into a separate partial by @OoBook in https://github.com/unusualify/modularous/commit/92692add98c3084e443221e81de0dd8992ed4318
- simplify title handling in head partial by @OoBook in https://github.com/unusualify/modularous/commit/2fd61b5cf5a0082062156eab63a7a0deb2dca70d
- remove deprecated stepper form component by @OoBook in https://github.com/unusualify/modularous/commit/7c5dc2ea68d3b80d59ac0607ad6ef6ae167b78a9
- improve caching mechanism for taggable items by @OoBook in https://github.com/unusualify/modularous/commit/b7865004a2883c824f66bc00f8b7dc38704d9935
- streamline form handling and remove unused store modules by @OoBook in https://github.com/unusualify/modularous/commit/8d6291eaa9bc5dd5da2ea4a4c6a8b71333750fcb
- comment out session expiration dialog code for future reference by @OoBook in https://github.com/unusualify/modularous/commit/05ff9f19f23017a9cc4f5d5c44da608f00be82e3
- streamline global properties registration and clean up imports by @OoBook in https://github.com/unusualify/modularous/commit/b26d94bdab42d13bf2e38f2af8f8a937ac7f6288
- update file name for clarity and consistency by @OoBook in https://github.com/unusualify/modularous/commit/d290d39c743e2794d03b0643498e29a859436344
- update store access and improve code clarity by @OoBook in https://github.com/unusualify/modularous/commit/38d4bf0c9289b63ec89f08f6344083b83376c478
- streamline authorization data handling in view composer by @OoBook in https://github.com/unusualify/modularous/commit/0edd70c30b904d954f97fb5ef6a10d2a31354d9c
- simplify impersonation data handling in view composer by @OoBook in https://github.com/unusualify/modularous/commit/09d6ffdf79fccfe1f85981836d49c7b4dafc6b72
- simplify navigation data handling in view composer by @OoBook in https://github.com/unusualify/modularous/commit/457b421f7286eb29f073ae94804ce5328fbc68a8
- simplify localization data handling by @OoBook in https://github.com/unusualify/modularous/commit/c3506739f5b55931fea2ac6ae238e5b2f54feadd
- deprecate getRoutes by @OoBook in https://github.com/unusualify/modularous/commit/896a4df733b4c1e8a3857ff75a51cd01d868bd54
- migrate to script setup syntax by @OoBook in https://github.com/unusualify/modularous/commit/200ef42122fd66deaaa819bd4c4cb69144cdd5f0
- comment out Ziggy definition for clarity by @OoBook in https://github.com/unusualify/modularous/commit/815899121fc42546a14823b7cce571af53fe7a17
- remove debug log statement from action activator template by @OoBook in https://github.com/unusualify/modularous/commit/2bdf8b94a5d1b7a96477621d746db543cdf77b06
- clean up master layout by removing unused media library references by @OoBook in https://github.com/unusualify/modularous/commit/d03d344da4ede9fb2f5d82c912759e42cf006958
- enhance tooltip and item action handling in table component by @OoBook in https://github.com/unusualify/modularous/commit/8c50f79156e9508966e76e79c0bf288fc746217b
- simplify sidebar information display and update dialog conditions by @OoBook in https://github.com/unusualify/modularous/commit/2d25bd346e5d88a2d46c28e6d54e8416f40ceb3b
- streamline state and method management with reactive objects by @OoBook in https://github.com/unusualify/modularous/commit/7a7b120272ef4793e6824aa6949515939f170ecf
- update loading state handling and improve input fetch integration by @OoBook in https://github.com/unusualify/modularous/commit/5a3ffe0205e51a00808f52c6e2a8d9e978f5102c
- streamline creator record handling during model saving and creation by @OoBook in https://github.com/unusualify/modularous/commit/89fe2071d146e535cb6890b72fe1817a21256d51

### :lipstick: Styling

- lint coding styles for v0.47.0 by @invalid-email-address in https://github.com/unusualify/modularous/commit/88c198086f1fdc350d50dace66954ecbe086f049
- lint coding styles for v0.48.0 by @OoBook in https://github.com/unusualify/modularous/commit/0a08f6024c6caf057ec0d22f577bf5d0b32f3821

### :package: Build

- update build artifacts for v0.47.0 by @invalid-email-address in https://github.com/unusualify/modularous/commit/3982ca98e5de8430314c1285fe9ca4f9337edf94
- update build artifacts for v0.48.0 by @OoBook in https://github.com/unusualify/modularous/commit/aa16e991f53b36983c132f4d3b11141c5a483a9f
- update build artifacts for v0.48.0 by @OoBook in https://github.com/unusualify/modularous/commit/b488e0bef5a4312dec99463c970abd3e0accae2c

### :green_heart: Workflow

- disable pint and vue build operations by @web-flow in https://github.com/unusualify/modularous/commit/d8e2680042ff3ccfa6fdd00e705bf74db0a7c8d2

### :beers: Other Stuff

- add debug logging for inertia setup process by @OoBook in https://github.com/unusualify/modularous/commit/6d8b2c93a4bf564dfe8ec5bb3914ccd937cd100e
- remove unused SASS styles to streamline component by @OoBook in https://github.com/unusualify/modularous/commit/065b51fef9627d58bc04fb38dc690f9976b62d36
- remove commented-out console log statements for cleaner code by @OoBook in https://github.com/unusualify/modularous/commit/7e88ac964ad56e96362d869f482732e0ba5f62bd
- refactor environment variable loading and improve app directory resolution by @OoBook in https://github.com/unusualify/modularous/commit/d96fca80aa9492f0e1ca50946102abc355f2c1a3
- update ignored files for environment configurations by @OoBook in https://github.com/unusualify/modularous/commit/12a644b43eaf751a92c5da91aa4bd77a72bdd445

## v0.47.0 - 2025-09-25

### :rocket: Features

- add creatorCompany relationship and update table name configuration by @OoBook in https://github.com/unusualify/modularous/commit/614925b1be7e894ce683fc52e8deae20781310f3
- introduce PaymentableRelation for enhanced morphTo functionality by @OoBook in https://github.com/unusualify/modularous/commit/6594e06a02b410052b2d618eee57e4732718c399

### :wrench: Bug Fixes

- add user registration events for OAuth login flow by @celikerde in https://github.com/unusualify/modularous/commit/0d4db8ebf123dd4138059b202cf09c9387621708
- enable logging conditionally based on activitylog configuration by @OoBook in https://github.com/unusualify/modularous/commit/3ee5184469b5748ca6e5372469be9c0c1543de28
- resolve actionUrl in form attributes for table row actions by @OoBook in https://github.com/unusualify/modularous/commit/8e96ced3f35f1e85917683214fbcf770190523cd
- clone modelValue from form attributes for custom forms by @OoBook in https://github.com/unusualify/modularous/commit/426c08e76174997fb2d3db254c5376c9203a2abd
- handle max value conversion for array and object validation by @OoBook in https://github.com/unusualify/modularous/commit/c91838d2b1cc47f9d0aa52043a7b45c9674d2276
- update default company table name in user migration by @OoBook in https://github.com/unusualify/modularous/commit/db6d50ec8a33c5609b7efb4453f57b1370ae7cc0
- handle exceptions during OAuth user retrieval by @OoBook in https://github.com/unusualify/modularous/commit/c79618fa7beb665af541bdedc72e524a4eac68b7

### :recycle: Refactors

- simplify click handler and introduce computed property for next action status by @OoBook in https://github.com/unusualify/modularous/commit/3198d38cdab82120e929b003450bd35f3471895b
- comment out validation logic in initializeHasUuid method and related tests by @OoBook in https://github.com/unusualify/modularous/commit/018b51d3e826f7edc0f5ee63a6252a914b5c394b

### :white_check_mark: Testing

- enable activity logging for authenticated and unauthenticated users by @OoBook in https://github.com/unusualify/modularous/commit/85c087be24c3741ec5e680a66681821b1476bc7a

### :package: Build

- update build artifacts for v0.46.1 by @invalid-email-address in https://github.com/unusualify/modularous/commit/979cb7b60ba87ea801b4430d4209a1e88c8e24e1
- update build artifacts for v0.47.0 by @OoBook in https://github.com/unusualify/modularous/commit/a8781a21a3dc3abe149ef9e4901918e0ba8b9ad6

## v0.46.1 - 2025-09-17

### :wrench: Bug Fixes

- clean up unused styles and improve component structure by @OoBook in https://github.com/unusualify/modularous/commit/d10c448f39308123c1172f4ae0302753fd956950

### :package: Build

- update build artifacts for v0.46.0 by @invalid-email-address in https://github.com/unusualify/modularous/commit/5926e060a571a02ba17dea92aed2e2e71784ea4f
- update build artifacts for v0.46.1 by @OoBook in https://github.com/unusualify/modularous/commit/b519e935fa578139ef3a92ef3b588588d3029800

## v0.46.0 - 2025-09-17

### :rocket: Features

- enhance UUID management and validation logic by @OoBook in https://github.com/unusualify/modularous/commit/4d5d6f65ed76c1f80b0f5be36c6fba4b40320e7a
- enable soft deletes and update migration for modularous assignments by @OoBook in https://github.com/unusualify/modularous/commit/6fc68b315e167dbe3a64d26bea35985d44a0d087
- implement soft delete handling for assignments on model deletion by @OoBook in https://github.com/unusualify/modularous/commit/90d2177ae6f694c401b5800828b14518fe89e812
- add methods for counting chat messages and unread messages by @OoBook in https://github.com/unusualify/modularous/commit/ec96c559d9a9059eea7062edda0d522e6d0f0f51
- enhance checklist component with improved styling and functionality by @OoBook in https://github.com/unusualify/modularous/commit/94a3402259b641c73d006d365a11acb8edd4f55d
- add transaction fee details and enhance payment UI by @celikerde in https://github.com/unusualify/modularous/commit/a0da5b5a3d91ad8311a8dde5f8325478bc1defcb

### :wrench: Bug Fixes

- enhance credit card payment service check by @OoBook in https://github.com/unusualify/modularous/commit/bec8ffc0b297f6b97722aec318f51237819d134a
- improve company validation in validCompany method by @OoBook in https://github.com/unusualify/modularous/commit/51baefc9bff18b3923c7f61ce1adcd68775f2622
- expand accepted file extensions for transfer receipt uploads by @celikerde in https://github.com/unusualify/modularous/commit/df0c8fe9b0433b6ce35c5f9a0be03e44267b80bc
- optimize hasFilepond method for efficiency by @OoBook in https://github.com/unusualify/modularous/commit/1b622a5b83f4b327c418ab81059089142b4fe71e
- update isPublished method to prioritize published property by @OoBook in https://github.com/unusualify/modularous/commit/6baa72eff7fc1747c682e049674019c98a64d854
- restore 'content' field in fillable attributes test by @OoBook in https://github.com/unusualify/modularous/commit/d3ce945512137d79b0f75dfd6fac8882e91c7c8c
- add 'notified_at' attribute to fillable fields by @OoBook in https://github.com/unusualify/modularous/commit/4fac394e4b962e3855a11faa9cd8108d64fa6bf8
- handle payment gateway retrieval in serviceClass method by @OoBook in https://github.com/unusualify/modularous/commit/445c99f78b6dd377a75020f1afef39a5ae42e25e
- implement booted method to unset price saving key before saving by @OoBook in https://github.com/unusualify/modularous/commit/82e3f030e401c0dd8ffd33c79c915ae2b76a7ea7
- enhance translation model detection logic by @OoBook in https://github.com/unusualify/modularous/commit/0d62f7b5043715ba7708f6f654e726dbbe08c483
- update query table reference for exceptIds scope by @OoBook in https://github.com/unusualify/modularous/commit/03336a52c315a7b1cb0539bf8208030d8067946f
- enhance logging behavior and clean up unused methods by @OoBook in https://github.com/unusualify/modularous/commit/341c2d106fd33127c3df1c291bb5f8d462f1d317
- update table name reference for state translations by @OoBook in https://github.com/unusualify/modularous/commit/c0541c633dba104e705901ab298f520120635737
- correct table name reference in test setup by @OoBook in https://github.com/unusualify/modularous/commit/4eadf9879ce04ff95034a06d0f982ed7949f5179
- enhance notification data handling and context sanitization by @OoBook in https://github.com/unusualify/modularous/commit/11a996d07da9f69d2efd00812a5295338c9cc0f0
- improve date handling and validation by @OoBook in https://github.com/unusualify/modularous/commit/894b0df691ede7b594d59d0270b972c2872dfc99
- enhance form validation and reset logic by @OoBook in https://github.com/unusualify/modularous/commit/76037f5e74c017055fd6b64ee95ff9f0ee352c94
- update form validation and assignment creation logic by @OoBook in https://github.com/unusualify/modularous/commit/bb837c634408e896c6acb902dd3b564204b60bb8

### :recycle: Refactors

- reorganize traits into Core namespace by @OoBook in https://github.com/unusualify/modularous/commit/377f8bdd1fc41be23b8fe11197c86e6157aa1043
- change traits as secondary traits for modularous by @OoBook in https://github.com/unusualify/modularous/commit/bc1fd7711eacf6d1c7e0f597d1dcc10338ec6949
- reorganize traits and introduce HasOauth trait by @OoBook in https://github.com/unusualify/modularous/commit/256a46bcaa7d70a6117702800bed65570d838954
- deprecate old HasScopes trait and integrate CoreHasScopes by @OoBook in https://github.com/unusualify/modularous/commit/be1781cbbb86f27a3ff68856e62c03bca3c2937f
- update HasRelated trait to Secondary namespace by @OoBook in https://github.com/unusualify/modularous/commit/f252a5b59adb3f2751d5f5f6bae467b6f98c117c
- move ModelHelpers to Core namespace by @OoBook in https://github.com/unusualify/modularous/commit/92199ba5c35dbfb6a667c5882679b38dac22f07c
- optimize scopeIsActiveAssigneeForYourRole method by @OoBook in https://github.com/unusualify/modularous/commit/e7c7ecd0c9e811c19c09111a01d5515a07e76a39
- make uuid field unique in fileponds table by @OoBook in https://github.com/unusualify/modularous/commit/4762850bdd82ffaaee833ac26c4feef8bac7f1f9
- enhance position management logic in creating and ordering by @OoBook in https://github.com/unusualify/modularous/commit/bad69c91c4e8d99751f5199263edd632a5080882
- remove obligatory authorization roles by @OoBook in https://github.com/unusualify/modularous/commit/a9f7364a4998c452a60942e78c095dd013e79660
- remove unused retrieved event handler by @OoBook in https://github.com/unusualify/modularous/commit/15a65a6d294349871e56b76ed05f6a096cb3ddd1
- update creatorChatMessages method and add truncateChat functionality by @OoBook in https://github.com/unusualify/modularous/commit/5fa5b182900768cbf7e3eb70d6cb03a0efb1fedb
- enhance authorization handling and improve code clarity by @OoBook in https://github.com/unusualify/modularous/commit/241043ba0c05b655f8cf209a7331232cee7e0894
- rename and deprecate authorization scope for clarity by @OoBook in https://github.com/unusualify/modularous/commit/5910c923f3d0ec04665f660ab9fecd22e650e5c8
- update unreadForYou scope to use hasAccessToCreation by @OoBook in https://github.com/unusualify/modularous/commit/c10743ad69b21f16263011533590e2109e80b5ca
- remove deprecated fillable handling for HasCreator trait by @OoBook in https://github.com/unusualify/modularous/commit/bd2375f11ae676bb27d1f34ed5a2f07421d5a6c5
- improve class handling and rename authorization methods by @OoBook in https://github.com/unusualify/modularous/commit/ab193fe751931e5701857133b73fc131b218f67e
- rename creator records table for consistency by @OoBook in https://github.com/unusualify/modularous/commit/f472805de9abcf8bde04d2dbed5b8c0ee4d99c23
- change creatableClass property to static by @OoBook in https://github.com/unusualify/modularous/commit/0a41622ce525004088d8cf2b5cab3c6bdcf1d485
- update media tables naming for consistency by @OoBook in https://github.com/unusualify/modularous/commit/17b421d22379581c6dcccb5e1184f80bf642a21b
- add payment_service_id, price_id, and currency_id to up_payments table by @OoBook in https://github.com/unusualify/modularous/commit/97ac688d8debd4ad7daf761f3666431cb574ac69
- remove unused bootHasPriceable method by @OoBook in https://github.com/unusualify/modularous/commit/02f5a6b25f03cca6bdedba067a82258efdea0c43
- streamline payment price retrieval methods by @OoBook in https://github.com/unusualify/modularous/commit/bec956dca46bacfea17ad74f8156466a681cbbce
- remove unused methods and streamline process handling by @OoBook in https://github.com/unusualify/modularous/commit/2cbd4ea265b36a92770b9943e47a7272b0d1c61b
- enhance process history management and streamline status checks by @OoBook in https://github.com/unusualify/modularous/commit/19437885523eb424d5e892ba9aa1f4d6f6a928ca
- remove deprecated methods and improve stateable updating logic by @OoBook in https://github.com/unusualify/modularous/commit/fd1a44144a92f0d72c36f2549fddf8215e94eddb
- remove unused translation model method and clean up code by @OoBook in https://github.com/unusualify/modularous/commit/4627d8cdc3b2d0ccfa1f35a541d8c24e007b20fd
- update assignments table name and implement migration backup by @OoBook in https://github.com/unusualify/modularous/commit/12f8cabef7c839e6aa834403243f46c6edf379b2
- streamline process update logic by @OoBook in https://github.com/unusualify/modularous/commit/63583c8ecbff067ee1d670d2169fa244971b3c71

### :lipstick: Styling

- apply consistent spacing in enum test cases by @OoBook in https://github.com/unusualify/modularous/commit/d5854f338610f2af82d24a9d08fc00daf48346f5
- restore content field in fillable array by @OoBook in https://github.com/unusualify/modularous/commit/834d7e34f823ffb4793c3ad8ba5a2fd9743f1d89

### :white_check_mark: Testing

- add comprehensive enum tests for AssignmentStatus, PaymentStatus, Permission, ProcessStatus by @OoBook in https://github.com/unusualify/modularous/commit/bfc4505cd3972b4ea46ccda0c7d1d3d1e012ef98
- add comprehensive tests for IsTranslatable trait functionality by @OoBook in https://github.com/unusualify/modularous/commit/6bfecd35ecebbeb9cfa8aee2cfb7ded8b2ed09e2
- add comprehensive tests for IsSingular trait functionality by @OoBook in https://github.com/unusualify/modularous/commit/40d7d027275d32bca2ca385e16d1fd68dd33c326
- add comprehensive tests for HasUuid trait functionality by @OoBook in https://github.com/unusualify/modularous/commit/236cdc355e69fa4ca889227fd6525fdd9a26427c
- add comprehensive tests for HasRepeaters trait functionality by @OoBook in https://github.com/unusualify/modularous/commit/d5f98ac6939c9b1c4a64943c2fad38e0c18d753b
- add comprehensive tests for HasPresenter trait functionality by @OoBook in https://github.com/unusualify/modularous/commit/fd5e27dca73eb1a6686596fbdc9a1bbfc962bf20
- add comprehensive tests for HasPosition trait functionality by @OoBook in https://github.com/unusualify/modularous/commit/d48da30e72e1a1918835c4dabc0cd0baf9a63117
- add comprehensive tests for HasFileponds trait functionality by @OoBook in https://github.com/unusualify/modularous/commit/924042a2e6eb04c2aaa77b9ddac156bd3f1617dc
- add comprehensive tests for Assignable trait functionality by @OoBook in https://github.com/unusualify/modularous/commit/8eeb0a1fb95dea77e39c086682acb478667aa304
- add tests for active assignee and assigner names, and assignment status by @OoBook in https://github.com/unusualify/modularous/commit/d87e597ffd44258c65ea536c5247d50c652364a9
- add comprehensive tests for Chatable trait functionality by @OoBook in https://github.com/unusualify/modularous/commit/07d39c81b348c07fa6a819ff466ae918633b1493
- add comprehensive tests for HasAuthorizable trait functionality by @OoBook in https://github.com/unusualify/modularous/commit/67f9b08e3c178c2a6084c89d6f57c1cc81c958e9
- add comprehensive tests for HasCreator trait functionality by @OoBook in https://github.com/unusualify/modularous/commit/f84ab75e96eb5136e42f0d5fbc89860bf638394e
- add comprehensive test suites for file and image handling traits by @OoBook in https://github.com/unusualify/modularous/commit/1d87955df904400c5a7bd41b659d9ed011e19f72
- add comprehensive tests for payment trait functionality by @OoBook in https://github.com/unusualify/modularous/commit/4bc0064f043a57c1d60ea3769ed4214a3bfb15d5
- add comprehensive tests for HasPriceable trait functionality by @OoBook in https://github.com/unusualify/modularous/commit/9e4f358f8a4c1923187f8f6e234e7d39b547d090
- add comprehensive tests for Processable trait functionality by @OoBook in https://github.com/unusualify/modularous/commit/664100b154df03ecd901137016d24b897944ae43
- add comprehensive tests for ChangeRelationships trait functionality by @OoBook in https://github.com/unusualify/modularous/commit/112f4fdeeca39317373a741a14ec86cae70ba728
- add comprehensive tests for HasScopes trait functionality by @OoBook in https://github.com/unusualify/modularous/commit/4796321fc5a5df8506544d4e404f718a2a53d5f1
- add translatable locales configuration for testing environment by @OoBook in https://github.com/unusualify/modularous/commit/b140c64f0fbcc3904cf27b83c66f013a891ed9f3
- add comprehensive tests for ModelHelpers trait functionality by @OoBook in https://github.com/unusualify/modularous/commit/b7cbdca594f0f54ce08f9d7fd5ac4af4cd655d34
- add comprehensive tests for HasStateable trait functionality by @OoBook in https://github.com/unusualify/modularous/commit/67e87bd99733ac0f8ff102d2d841e0318dbaf4d5
- add comprehensive tests for ModelEvent functionality by @OoBook in https://github.com/unusualify/modularous/commit/670b5b0cfd0ea8c0ce83740b3e13c978e2be91a3

### :package: Build

- update build artifacts for v0.46.0 by @OoBook in https://github.com/unusualify/modularous/commit/9feb62a5205b4d4f05ea576931bb17fad1a2fd8e

### :beers: Other Stuff

- update configuration for priceable and payable entities by @OoBook in https://github.com/unusualify/modularous/commit/e9130bbb33105ad2c24f0fb42c8f74c3b2899c69
- remove unused HasScanModule trait by @OoBook in https://github.com/unusualify/modularous/commit/4bb54d75eeb9d5f4ef5cd71c6d44fff40e63c2e4
- comment out unused booted method for process validation by @OoBook in https://github.com/unusualify/modularous/commit/0c067e09b5daf039598637350f350c67738f3abc
- update code coverage configuration and add coverage report files by @OoBook in https://github.com/unusualify/modularous/commit/3882bc0a2e08f2c63c040421b48bf3ba7c4ac70c
- update PHPUnit scripts for test execution by @OoBook in https://github.com/unusualify/modularous/commit/d9f75ea70fc806d48f3accf2945a457818043f20
- add specific test scripts for various components by @OoBook in https://github.com/unusualify/modularous/commit/215ab62bc63a7f88cbf11718c12dc895169ab34f

## v0.45.2 - 2025-09-09

### :wrench: Bug Fixes

- comment out phone validation in validCompany method by @OoBook in https://github.com/unusualify/modularous/commit/47a9a9525a2fa4ead1bdfc0c23552a35ae639f4c

### :recycle: Refactors

- clear default channels for stateable, chatable, assignable, and authorizable notifications by @OoBook in https://github.com/unusualify/modularous/commit/f25abe42ed2f07a543709af9ce0e0bb81db8c747

### :package: Build

- update build artifacts for v0.45.1 by @invalid-email-address in https://github.com/unusualify/modularous/commit/e152e6a47d8a23eef2c1670559ae2ec42ac73ba0

## v0.45.1 - 2025-09-08

### :recycle: Refactors

- rename v-date-input to v-input-date for consistency by @OoBook in https://github.com/unusualify/modularous/commit/c2c383ba157453c98c2fc2d85823f134ae43a392
- streamline date input handling and improve readability by @OoBook in https://github.com/unusualify/modularous/commit/4682f5eba166f2d5555ebe464a5cf50ac473fbdd

### :lipstick: Styling

- lint coding styles for v0.45.0 by @invalid-email-address in https://github.com/unusualify/modularous/commit/07b93c8906dc23da8d3a47bb57c3b2cdf0f267df

### :package: Build

- update build artifacts for v0.45.0 by @invalid-email-address in https://github.com/unusualify/modularous/commit/7868fc76c31ab21e3003b472ba663e1f3e582360
- update build artifacts for v0.45.1 by @OoBook in https://github.com/unusualify/modularous/commit/3e7e9746bf6d0d747ffe20e3419728d650784504

## v0.45.0 - 2025-09-08

### :rocket: Features

- integrate logo symbol into authentication layout by @OoBook in https://github.com/unusualify/modularous/commit/88e23216809d2c818eee52b050aee66931bb4f7c
- add DateHydrate class and Vue component for date input handling by @OoBook in https://github.com/unusualify/modularous/commit/3251afc3b9e5537e24f104bea0d50ede208cc0ca
- integrate Revolut Checkout components and update dependencies by @OoBook in https://github.com/unusualify/modularous/commit/59bdcc03f1091c0673cf4dc3016c62cd8f694b0a
- enhance input fields with density prop and adjust styles by @OoBook in https://github.com/unusualify/modularous/commit/b77410a59eaf93ce0af3a8eee5358970f8e1f32e
- add service class and built-in form attributes by @OoBook in https://github.com/unusualify/modularous/commit/0241f06d57a892867b400c424ffd5f2a60ea2026
- add attributes for credit card payment service and built-in form by @OoBook in https://github.com/unusualify/modularous/commit/e58c07a09744ba692d137b5a5a85cf763297e20b
- implement checkout method for payment processing by @OoBook in https://github.com/unusualify/modularous/commit/0b43fe7f0fb66cf47b3b90dfb1369860c3245cf7
- refactor currency handling and integrate built-in payment form by @OoBook in https://github.com/unusualify/modularous/commit/8f0d2b6c2f7a1ff4f38c4af8d55933394a51afbd
- add logout confirmation messages and titles by @celikerde in https://github.com/unusualify/modularous/commit/1db313b671e18a69a345145cbdaa28535544172d
- add English language support for payment messages by @celikerde in https://github.com/unusualify/modularous/commit/fb73b95c0c80c9eacf0298a8b0f6d77545e30ca4
- enhance dialog messages and layout for process updates by @celikerde in https://github.com/unusualify/modularous/commit/b0ad89eec7b29f4afa9da70f7c07898669a05308
- enhance action handling with dynamic confirmation modals by @celikerde in https://github.com/unusualify/modularous/commit/8b70896f8dbfb9b12dac7b9f4e2cb8e954edec5c
- add trait for managing changed relationships by @celikerde in https://github.com/unusualify/modularous/commit/baf86c4ae20663cb122c2b29719320677f7c6a51
- dispatch unread chat message event on notification handling by @celikerde in https://github.com/unusualify/modularous/commit/484612dc1238012bf47d36c150ea8990e6ff3c85
- enhance afterSaveRelationships method to track changes by @celikerde in https://github.com/unusualify/modularous/commit/3724fecfee3b9c5d8c5a261bd7fe12072821a72b
- integrate ChangeRelationships trait for enhanced relationship management by @celikerde in https://github.com/unusualify/modularous/commit/9bf4de13facb322c661e6a9f886b71667cc9c05d
- add custom ResetPasswordNotification for enhanced user experience by @celikerde in https://github.com/unusualify/modularous/commit/54a625fafb7392ac48a338221dea5936a0447c7d
- add sendPasswordResetNotification method for password reset functionality by @celikerde in https://github.com/unusualify/modularous/commit/661b5ce2ed508e195e5af7770b8d17abcc83ac6d
- add event dispatching for model creation and updates by @celikerde in https://github.com/unusualify/modularous/commit/507d8984bc8653a81f7ca0e381186335657e8523
- introduce AfterSendable interface for notification handling by @celikerde in https://github.com/unusualify/modularous/commit/bf8be5dbdb9619449699d23c20d155ec12610f0d
- add valid channels and validation methods for notifications by @celikerde in https://github.com/unusualify/modularous/commit/dafd9fc5889939e176f591c32b4428157fe90e71
- add events for authorizable creation and updates, and unread chat messages by @celikerde in https://github.com/unusualify/modularous/commit/3b9d8a791ab7df519ea515b499d3b7b605df875d
- add title justification property to form component by @OoBook in https://github.com/unusualify/modularous/commit/55ca4e482762ead1511915f8b10b548180867e57
- add computed properties for delete dialog title and description by @OoBook in https://github.com/unusualify/modularous/commit/b1cc8a4c7c31fda5a9b410cac6de5c557956e6cf
- add valid_company attribute and enhance company validation logic by @OoBook in https://github.com/unusualify/modularous/commit/3a0f74d0d0033deca184f24eccdf369b48ff4e72
- include additional user attributes in profile data by @OoBook in https://github.com/unusualify/modularous/commit/9b9485c1c9c4d03ab7b952bdf82cae191d76b805
- enhance condition checks for item actions by @OoBook in https://github.com/unusualify/modularous/commit/669b1c1e023d1a7496595fa154bfb406520f4c17
- enhance form action handling with draft support by @OoBook in https://github.com/unusualify/modularous/commit/2712858d995a04b411a8bcc833af4210ac94a4d7
- enhance notification channel handling by @OoBook in https://github.com/unusualify/modularous/commit/313842ebb49fe8cb26fab0ca4e0dea31faeb721f
- add Revolut payment service configuration and image by @OoBook in https://github.com/unusualify/modularous/commit/3b871543e62ac73e4a8d22310a906fb235e46810
- enhance condition evaluation with support for complex logic by @OoBook in https://github.com/unusualify/modularous/commit/089a98c581f14fc9c35e319298fae8e6ea6c1675

### :wrench: Bug Fixes

- simplify body description structure in modal by @OoBook in https://github.com/unusualify/modularous/commit/f0c112515d2313feb9c4a7ddb0a3094200f99b80
- improve body description structure in modal by @OoBook in https://github.com/unusualify/modularous/commit/80174d46920ec4a022510b18185c7d703a826c76
- update success message for task assignment notification by @celikerde in https://github.com/unusualify/modularous/commit/cae2087c16e91ed8334fc20b833ae7228ea5823e
- correct error handling response data structure by @celikerde in https://github.com/unusualify/modularous/commit/5747cb79a5e8e73a0efd04e3851bec0abd6e4d36
- update response modal message styling for better readability by @celikerde in https://github.com/unusualify/modularous/commit/ca022c244b42905e4592d04afd4b14093ca7d5d7
- update button text for password reset form by @celikerde in https://github.com/unusualify/modularous/commit/bea43f000a84ee5e4c3b4af9ec5cc882bbfdd080
- update success message for profile update response by @celikerde in https://github.com/unusualify/modularous/commit/699077df333459a3e2a6a0c524d5665c52152a13
- enhance validation rules for form fields by @celikerde in https://github.com/unusualify/modularous/commit/96ea09a24c55b6f2eb655f0abe7953f1dea5df75
- update companies name field length and related fields by @celikerde in https://github.com/unusualify/modularous/commit/ed3d93c05667953bab4ae3c79ed8ef52ef8cb531
- reorder modal attributes for consistent rendering by @OoBook in https://github.com/unusualify/modularous/commit/d36f4ce2bf60a25fd08ef3736d2605fbfd697a4e
- add padding to input components by @OoBook in https://github.com/unusualify/modularous/commit/750acd3263652f90cbbbfe9c65143fdf73af6a87
- enhance file preview functionality with conditional download by @OoBook in https://github.com/unusualify/modularous/commit/55ab5a9070437fce517ae5bdd03ced079637e16f

### :recycle: Refactors

- update payment routes and enhance payment service hydration by @OoBook in https://github.com/unusualify/modularous/commit/8c5860f24c1ea2050a2095ee1c00d75af2f5b78a
- update logout messages for localization by @celikerde in https://github.com/unusualify/modularous/commit/fa22f3d5098f435670429ca7a0496fd1d9955df0
- enhance state management with caching and attribute access by @celikerde in https://github.com/unusualify/modularous/commit/848b199e13339a91408c9ef37356f878de2556b6
- update modal configuration and comment out unused message field by @celikerde in https://github.com/unusualify/modularous/commit/30500b79b1b9f40dcc1a6a6b3a5fe97d1e40c2fb
- localize payment success and error messages by @celikerde in https://github.com/unusualify/modularous/commit/076d14dee9cb3a84b27bce1deb36aae942ed82b6
- clean notification channel values for improved validation by @celikerde in https://github.com/unusualify/modularous/commit/553a6124a61b8cecb9813c62ab9f815ee0c9adb4
- implement AfterSendable interface and enhance channel validation by @celikerde in https://github.com/unusualify/modularous/commit/42da0a372a6caf461347351ef8bcdd94a2347ad3
- streamline login shortcut schema handling by @OoBook in https://github.com/unusualify/modularous/commit/52e2102d41071b529e4f128c064523953baab1ee
- enhance attribute casting logic and introduce matching functions by @OoBook in https://github.com/unusualify/modularous/commit/8513a0745541deb744b298af1fd338bc564521a9
- streamline attribute casting and matching logic by @OoBook in https://github.com/unusualify/modularous/commit/7593edbf38d7c337e6bbbf8196e84670ca521c8a
- integrate attribute casting utility and simplify value matching by @OoBook in https://github.com/unusualify/modularous/commit/ab7729b628ff77a0fb780ebd23f65996f215d3dc
- remove unused formatter and simplify action formatting by @OoBook in https://github.com/unusualify/modularous/commit/f856de9dd37d2d2aea9201322012027e82c62f40
- remove deprecated casting logic to enhance clarity by @OoBook in https://github.com/unusualify/modularous/commit/dff237d382aa3fbeee856bf49759235efe8f4fd1
- remove deprecated value matching functions for improved clarity by @OoBook in https://github.com/unusualify/modularous/commit/d9f3810d1b2bf40031982643a64787e07b1d6b61
- remove fullscreen button from modal and update delete dialog attributes by @OoBook in https://github.com/unusualify/modularous/commit/f9e3864244e88e5e2b4844fe5a32b8bed5582a3c
- update deletion confirmation messages for improved clarity by @OoBook in https://github.com/unusualify/modularous/commit/2508caa29adb603c995a5b325179cfc3827e38a0
- update afterNotificationSent method to accept notifiable parameter by @OoBook in https://github.com/unusualify/modularous/commit/61bd5b688f2f2107b824aaa9b975f3d588e59926
- introduce TaskCreatedNotification and update notification handling by @OoBook in https://github.com/unusualify/modularous/commit/3206d9829dd9876bd413f0183441194f3a336303
- remove via method from notification classes by @OoBook in https://github.com/unusualify/modularous/commit/084605bb3d46ffc830bdf8954fdd3fc64bcf1de0
- enhance table row actions with dynamic conditions and form attributes by @OoBook in https://github.com/unusualify/modularous/commit/b8991625c5f54dd137e20c757f694903a6bb6ee3
- update modal attributes and improve styling by @OoBook in https://github.com/unusualify/modularous/commit/b2f93004bf45c6288c4a2da1fbc505f9249b7a03
- integrate Vuex store for enhanced condition checks by @OoBook in https://github.com/unusualify/modularous/commit/3efd7de81dac89d1bb6e3ae8bd8523de44e387c9

### :lipstick: Styling

- lint coding styles for v0.44.0 by @invalid-email-address in https://github.com/unusualify/modularous/commit/30957ac09dfe081d981e90e20a8ba9aa6891d2dd
- improve method formatting for clarity by @OoBook in https://github.com/unusualify/modularous/commit/01b193a0aa1a022a25b82442ba266c4e6b461938

### :white_check_mark: Testing

- add comprehensive unit tests for condition evaluation functions by @OoBook in https://github.com/unusualify/modularous/commit/20291944f980b02a49fa3c2dc1a34ee54eb533d8

### :package: Build

- update build artifacts for v0.44.0 by @invalid-email-address in https://github.com/unusualify/modularous/commit/5d8f4d069d27386149fed081f2f8668635d0507e
- update build artifacts for v0.45.0 by @OoBook in https://github.com/unusualify/modularous/commit/27aa9a827266804f27cdbfd67ccaf7db52d22222

### :beers: Other Stuff

- update unusualify/payable dependency version to ^0.12 by @OoBook in https://github.com/unusualify/modularous/commit/30ab7659a5d76076650d9da54c254cf8929824aa
- rename companies table in migration file by @celikerde in https://github.com/unusualify/modularous/commit/00a8c9e4a8996ec01a5c770c596d406f198ac5e4

## v0.44.0 - 2025-08-26

### :rocket: Features

- enhance value assignment logic for various input types by @OoBook in https://github.com/unusualify/modularous/commit/b4a11c985038860f8c151c4e7bd1278a2b056ffa
- enhance message box functionality and textarea behavior by @OoBook in https://github.com/unusualify/modularous/commit/00273292e20e58c406078fb551e7a4bee45c81c5
- add 'density' property to published input type configuration by @OoBook in https://github.com/unusualify/modularous/commit/11b6d050e3e9b80a577d12937f4f05ffe6dd72bf
- enhance modal title customization and layout by @OoBook in https://github.com/unusualify/modularous/commit/99dd9beeb7c765bf2d3cdc2ff7e20aff91196558
- enhance modal functionality and layout by @OoBook in https://github.com/unusualify/modularous/commit/63faece7e701773db027bed71b68842d381ef0e6
- enhance custom form modal attributes and structure by @OoBook in https://github.com/unusualify/modularous/commit/337c23cad94865ce703ebfc6d9dd9ef9391ab23b
- enhance payment form modal attributes and structure by @OoBook in https://github.com/unusualify/modularous/commit/6f07b25e99f43143f1a10c6214a2183ae2985376

### :wrench: Bug Fixes

- add accepted_at field and improve assigner assignment logic by @OoBook in https://github.com/unusualify/modularous/commit/39609d1dbcaf8b422547b5b62b1d2b08f9887bd5
- correct typo in fillable attribute name from 'repatable_id' to 'repeatable_id' by @OoBook in https://github.com/unusualify/modularous/commit/0e27104bbb47caa59a6120ff81d6fde5d16898da

### :recycle: Refactors

- add chat_id to fillable attributes by @OoBook in https://github.com/unusualify/modularous/commit/9011bcfb0ab00030e081d7d6e07a74ee26f94273
- add process_id to fillable attributes by @OoBook in https://github.com/unusualify/modularous/commit/0cd94b8481ddedd2d4aa8b90fe63256f66b68193
- remove unused attributes from fillable array by @OoBook in https://github.com/unusualify/modularous/commit/26920bdfefd716e23331a723f078f9ac51ae080d
- update table name and fillable attributes in Spread model by @OoBook in https://github.com/unusualify/modularous/commit/d8bf8588813788c50ddfbdc0dfbebc7dfd3b970a

### :white_check_mark: Testing

- add comprehensive tests for Assignment model functionality by @OoBook in https://github.com/unusualify/modularous/commit/c026e041c5abdb575fd898c35676c29f2b5dbd1e
- add comprehensive test suites for ChatMessage and Chat models by @OoBook in https://github.com/unusualify/modularous/commit/38c49f9d21bb409127b3a9e158dc00e33aeaf079
- add comprehensive tests for Filepond model functionality by @OoBook in https://github.com/unusualify/modularous/commit/be81654b25ec806873001d451ec561d25fbb2112
- add comprehensive tests for TemporaryFilepond model functionality by @OoBook in https://github.com/unusualify/modularous/commit/cca080b37ef62c57985cd55513ed2cc1c8463b48
- add comprehensive test suites for Process and ProcessHistory models by @OoBook in https://github.com/unusualify/modularous/commit/a57244152352c7da75257538a9fecb31c4ebeb67
- add comprehensive test suites for Stateable and State models by @OoBook in https://github.com/unusualify/modularous/commit/80dab314fe859e9b7ccb9a1e45f3949ec55fa73d
- add comprehensive tests for Authorization model functionality by @OoBook in https://github.com/unusualify/modularous/commit/bc6c115260cc6d856bdf9a20b594b68403bc5080
- add comprehensive tests for CreatorRecord model functionality by @OoBook in https://github.com/unusualify/modularous/commit/3e03cd2c3f9f5222edf87be738fda079dbaffdda
- add comprehensive tests for Repeater model functionality by @OoBook in https://github.com/unusualify/modularous/commit/f6d76ad191a688f2e28c24c9227e7ca01d82bdff
- add comprehensive tests for Singleton model functionality by @OoBook in https://github.com/unusualify/modularous/commit/04532237fdb8f9af8281150315641fa198e606c5
- add comprehensive tests for Spread model functionality by @OoBook in https://github.com/unusualify/modularous/commit/41e77f67a7b2ca66b32f39499ce208716f43ede9

### :package: Build

- update build artifacts for v0.43.0 by @invalid-email-address in https://github.com/unusualify/modularous/commit/bdaaa10c77ec9868d8171e5fa46e8cd1c12f0ead
- update build artifacts for v0.44.0 by @OoBook in https://github.com/unusualify/modularous/commit/ba0786f2a6f304424c30970759c7db7347a339dd

## v0.43.0 - 2025-08-25

### :rocket: Features

- enhance modal handling with URL parameters by @OoBook in https://github.com/unusualify/modularous/commit/a979910fee094d033e65804a4dc03c564965b283

### :wrench: Bug Fixes

- update validation triggers for form fields by @OoBook in https://github.com/unusualify/modularous/commit/464017aa88762d2d30b12f96d47b2855db22ca5d
- enhance global error handling notifications by @OoBook in https://github.com/unusualify/modularous/commit/6743fbe22fa34f9d54a717f746ec3808a7454fc0
- improve error handling for 403 status by @OoBook in https://github.com/unusualify/modularous/commit/f71c41043e283a6aeff135bf50568eefadb276a7
- remove validation triggers for password fields by @OoBook in https://github.com/unusualify/modularous/commit/fca871f6e9db79422187285e7cd2d733c5a20387

### :recycle: Refactors

- unify formatter handling and improve tooltip functionality by @OoBook in https://github.com/unusualify/modularous/commit/e9e4ce1a76b9cbf1d91da0b8b87512a3b7ccc8e3
- replace createModalService with modularous_modal_service by @OoBook in https://github.com/unusualify/modularous/commit/768cbf40f698fc9389d14045b1a3d028e1572f03

### :package: Build

- update build artifacts for v0.42.2 by @invalid-email-address in https://github.com/unusualify/modularous/commit/9e8faa8f7d989e856140c061aad42b990df77db8
- update build artifacts for v0.43.0 by @OoBook in https://github.com/unusualify/modularous/commit/b1c9d2267a1f007daa53048494515b348a56abc7

## v0.42.2 - 2025-08-21

### :rocket: Features

- enhance dynamic Vue component creation and directive handling by @OoBook in https://github.com/unusualify/modularous/commit/5f659327e8991f381d42185a92d72a032f75ae1f

### :recycle: Refactors

- update component structure for improved directive handling by @OoBook in https://github.com/unusualify/modularous/commit/5fede90dab8bfea98d63e3348d3045d7508cfcbd

### :package: Build

- update build artifacts for v0.42.1 by @invalid-email-address in https://github.com/unusualify/modularous/commit/2a897b6f22c40c2f6db5a7f7248524aa18a1ce97
- update build artifacts for v0.42.2 by @OoBook in https://github.com/unusualify/modularous/commit/3c3b7ccd2b810125d3e5702a72d8dfcfe1e17721

## v0.42.1 - 2025-08-20

### :wrench: Bug Fixes

- simplify accepted file types in AssignmentHydrate and update filepond name in ChatHydrate by @OoBook in https://github.com/unusualify/modularous/commit/6149b805ff301715730958889270e7c6cfc67815
- refine layout classes for improved UI consistency by @OoBook in https://github.com/unusualify/modularous/commit/c3cba2ee3d493903c3856a33c6b2fd4b4f2ade1f
- wrap modal content in flex container for improved alignment by @OoBook in https://github.com/unusualify/modularous/commit/2c7753ef74558e357b3362e56451e0fe87abfca8
- update default class for improved spacing in form component by @OoBook in https://github.com/unusualify/modularous/commit/37bf51e2d55abd125dbfa688cd3d3dc1f2e4ed2b

### :lipstick: Styling

- lint coding styles for v0.42.0 by @invalid-email-address in https://github.com/unusualify/modularous/commit/7e9ff30fb36e22518df17c4e3c9f3885cd0c23b3

### :package: Build

- update build artifacts for v0.42.0 by @invalid-email-address in https://github.com/unusualify/modularous/commit/1a7b5d1278b79c734cc4ef687469946491fbf3be
- update build artifacts for v0.42.1 by @OoBook in https://github.com/unusualify/modularous/commit/b67c3cd0872987ace2832cbcdca0a9f461566522

## v0.42.0 - 2025-08-20

### :rocket: Features

- enhance process status handling and UI interactions by @OoBook in https://github.com/unusualify/modularous/commit/8910ccce3a32ca3a42133cc6eedb3187ca8d54ee
- add syncStateData method to manage absent states by @OoBook in https://github.com/unusualify/modularous/commit/52439b5233f873bc2444514ccf65bc706bd0ff8d
- add console command for syncing stateable model states by @OoBook in https://github.com/unusualify/modularous/commit/7c98a72de40607d3d0c3fb08ec5df2adc41aedb1
- enhance mobile responsiveness and styling options by @OoBook in https://github.com/unusualify/modularous/commit/f7588f497f73f454425b01acd6c3c383deadaa0d
- add sidebar bottom navigation group by @OoBook in https://github.com/unusualify/modularous/commit/8ffcbee5834749998ff5be678d9b8d28cd3df6a6
- enhance data handling with lodash get function by @OoBook in https://github.com/unusualify/modularous/commit/e996c34083d7cabb7a9c645344d2a747ab3d1f3a
- update sidebar and profile menu configuration by @OoBook in https://github.com/unusualify/modularous/commit/f27d24e663e0e135839da6652003e7036b98defa
- log successful state synchronization message by @OoBook in https://github.com/unusualify/modularous/commit/e0b02d67833ca143a23d8e261430146a53fdf29c
- enhance rate limiting logic with host validation by @OoBook in https://github.com/unusualify/modularous/commit/13808bbefabc1cda77d4c990ccd43882af7bad70
- enhance table functionality with selectable rows and improved header rendering by @OoBook in https://github.com/unusualify/modularous/commit/841903fedb462bed208acce8cf95cd57f6a2d45d
- update payment status enum and add utility methods by @OoBook in https://github.com/unusualify/modularous/commit/fb2401fbb4f1d2287a95176426e6a3aacd896ba9
- streamline form schema and table header handling by @OoBook in https://github.com/unusualify/modularous/commit/2336f4b615a0078a4a129d6e553d2a3d0a036d77
- enhance payment model with status attributes and global scope by @OoBook in https://github.com/unusualify/modularous/commit/35bca03633a7be73b8b0e7fb220ab5c01c9409de
- add saveForm functionality to form submission handling by @OoBook in https://github.com/unusualify/modularous/commit/b80f812d00a7eb5939200578c132f1b634f2c4ca
- enhance PaymentService with spreadable trait by @OoBook in https://github.com/unusualify/modularous/commit/1e7ad066a94ac54793dedfab28a1a2da5d522102
- implement ShouldQueue interface for asynchronous processing by @OoBook in https://github.com/unusualify/modularous/commit/a6f9ae2256f40294e2ea15e67b9c299706a6286b
- add bank receipts and transfer details functionality by @OoBook in https://github.com/unusualify/modularous/commit/f0929e8c4d13fd71c2a95e6888cbd05f0cfda69a
- enhance payment service input handling and transfer functionality by @OoBook in https://github.com/unusualify/modularous/commit/e0f65d68316f642b4936991ed7af17432e21039c
- add updateOrNewPayment method for payment management by @OoBook in https://github.com/unusualify/modularous/commit/2dd857c6342d2114420d89c5a004575e9e647486
- enhance payment processing with transfer support and validation by @OoBook in https://github.com/unusualify/modularous/commit/b2ca9e47bfb0c16584d089ce83c1c718284d1ec3
- add emoji picker component by @celikerde in https://github.com/unusualify/modularous/commit/1de3858a0cf2d1d638e13eb4c107bc67f9852af4
- enhance chat input functionality and UI by @celikerde in https://github.com/unusualify/modularous/commit/aaeb16fece40ad21ab5dd27a297443d1cfc8b0b7
- enhance email verification process with dynamic parameters by @OoBook in https://github.com/unusualify/modularous/commit/03d7771af8c00dfc24cee0dd97035a502300ca0d
- enhance registration notification with dynamic parameters by @OoBook in https://github.com/unusualify/modularous/commit/c4e1879c0917636ed631793cf819b9babfc896be
- enhance complete registration form and event handling by @OoBook in https://github.com/unusualify/modularous/commit/c16e9e361e7680a257ac13ac7296be8157676d4f
- enhance user registration process with company association by @OoBook in https://github.com/unusualify/modularous/commit/5ca884b3e59452a06abacf3707abb4b67a007b76
- add email verification option for registration by @OoBook in https://github.com/unusualify/modularous/commit/25c47509abe299446d0e673680e771d568305793
- enhance registration form styling and event handling by @OoBook in https://github.com/unusualify/modularous/commit/9b832046cc35c674542c927ef28f9aed9e5f921a
- implement redirect functionality with middleware and service by @OoBook in https://github.com/unusualify/modularous/commit/e6f4dc713f4c5bbd31431f798e000d4c63bef68a
- add stop on defect option for PHPUnit testing by @OoBook in https://github.com/unusualify/modularous/commit/1392671e07a5af4a59f8b10fd70ae8f7ffe03395
- enhance form actions retrieval and merging logic by @OoBook in https://github.com/unusualify/modularous/commit/9fdff977302718a9a4f1ad6f2a8547aacd0a6130
- enhance payment form actions and conditions by @OoBook in https://github.com/unusualify/modularous/commit/cb4924e62d94f0ab9c97d061b7278492c08afdc9
- add payment completion check and modal service handling by @OoBook in https://github.com/unusualify/modularous/commit/50b50c1a1ebfb9b157e8e321c6531059cc9dce99
- enhance pinned message display and layout adjustments by @OoBook in https://github.com/unusualify/modularous/commit/b819327dc278b3d8c94cfeb3902567e68b280c53
- enhance modal handling with URL parameters by @OoBook in https://github.com/unusualify/modularous/commit/4f1b8151f3229d079cc0bda2b45cd2fc6246456c
- enhance input hydration and formatting functions by @OoBook in https://github.com/unusualify/modularous/commit/246332f49783bb5a33854e8be13792471cbc5f19
- add modularous modal and form service functions by @OoBook in https://github.com/unusualify/modularous/commit/c8f3d8405facd17cb7a70251fc832c272d76b5c6
- add modal service API endpoint for session data retrieval by @OoBook in https://github.com/unusualify/modularous/commit/3ce2d48857c221b581251829f42c3e810e5544c8
- implement dynamic payment middleware configuration by @OoBook in https://github.com/unusualify/modularous/commit/6b220904e1ba27fc9dc5f73c40328e32ccf04d15
- update form draft settings and labels by @OoBook in https://github.com/unusualify/modularous/commit/37c94777953465fafac9a9f1ef1732c78e6db100
- enhance state hydration and configuration methods by @OoBook in https://github.com/unusualify/modularous/commit/d44bffc1fb35c5d4c12e816d17d0bfca3b4cd648
- add email verification pre-registration flow by @OoBook in https://github.com/unusualify/modularous/commit/c9461bbf93ed7f9fd459330a6437808e1241ef1c
- implement email verification check for registration by @OoBook in https://github.com/unusualify/modularous/commit/9a7d06f0d5f6545528e760fb39447a50802b929e
- add dynamic column configuration for image display by @OoBook in https://github.com/unusualify/modularous/commit/475826ce63a33d5cd1101cbaa3e08431960de6dc
- add dynamic column configuration for image display settings by @OoBook in https://github.com/unusualify/modularous/commit/3027386ef40179191bc544f404fff99bb59b5324
- add ABN AMRO payment service images by @OoBook in https://github.com/unusualify/modularous/commit/9b188e036bac49837698ea427553e95186140503

### :wrench: Bug Fixes

- update Google sign-in button label for OAuth consistency by @celikerde in https://github.com/unusualify/modularous/commit/bb9dcaf2b62acc654b9338cf1a21da783eca11de
- update Google sign-up button label and route for OAuth by @celikerde in https://github.com/unusualify/modularous/commit/15b73990727aadb82bc149f30d469e3c752fa074
- update Google sign-in button label and route for OAuth, remove Apple sign-in button by @celikerde in https://github.com/unusualify/modularous/commit/eae18c748d6d9888c6c58777a5569f61f7ddbc87
- remove unnecessary condition in bottom slot rendering by @OoBook in https://github.com/unusualify/modularous/commit/30dae37c1889efdbb80c9021b4281158abe34910
- correct request parameter for eager loading includes by @OoBook in https://github.com/unusualify/modularous/commit/895ceb8d5cfc41ee48f9c75e1b51c4f2239e968c
- enhance includes handling for eager loading by @OoBook in https://github.com/unusualify/modularous/commit/6e8c774a992c62b25ddc61a57a18e76c6791635a
- enhance scrollbar styling for improved aesthetics by @OoBook in https://github.com/unusualify/modularous/commit/51546cf09fc6e382be6b6048ccf230a7cddbd665
- update creatable class reference in company relationship query by @OoBook in https://github.com/unusualify/modularous/commit/cbfe83ffa0d99a90be5b664d2a9720edd60dae34
- improve route configuration update logic by @OoBook in https://github.com/unusualify/modularous/commit/4d912d93eee95b556d48cea58ae5377c859f90b8
- improve source normalization logic by @OoBook in https://github.com/unusualify/modularous/commit/88d326bb3e18f7e7303678b7d5faa5ca8d23a5bd
- improve handling of newValue for array and string types by @OoBook in https://github.com/unusualify/modularous/commit/aaebfcb4e3706acedff39c686d1c940b7725c5b0
- override dynamic service class retrieval for payment gateways by @OoBook in https://github.com/unusualify/modularous/commit/835f5709ec4084e0578b91b18635159fddc57241
- update file upload constraints for transfer receipts by @OoBook in https://github.com/unusualify/modularous/commit/14c02e0386f444e9f9e2b3753ce93b8ff9e32004
- use null coalescing assignment for label initialization by @celikerde in https://github.com/unusualify/modularous/commit/298f5457baed0f4073b8fab507825b74ae93c766
- ensure company_id check for role authorization in query scope by @OoBook in https://github.com/unusualify/modularous/commit/dd2af6d26445683ecad0d9310fe86ef08592365c
- update maxRule logic to handle empty values correctly by @OoBook in https://github.com/unusualify/modularous/commit/5c93caa35fd4d77df77ca48fa9f82faaaa241c6f
- update content field of chat_messages table to be nullable by @OoBook in https://github.com/unusualify/modularous/commit/7ace2b47505964d0f3785f654264f30b66ef62d1
- handle null content in message formatting by @OoBook in https://github.com/unusualify/modularous/commit/e9f381680ac8ffa8bcbe61bd80001275ed70589c
- improve form actions merging logic by @OoBook in https://github.com/unusualify/modularous/commit/7eea6ebe55589cc4ae425b7681de66802ac700dd
- update dynamic attribute syntax for message and redirect elements by @OoBook in https://github.com/unusualify/modularous/commit/0e467c510c2aa3b933d09223118535315e71274f
- adjust class attributes for improved layout consistency by @OoBook in https://github.com/unusualify/modularous/commit/d8e715effc4f3d459e023c725f820624945d7783
- remove unnecessary console log for title props by @OoBook in https://github.com/unusualify/modularous/commit/8742a5e3e0ad33097f3c60753bb72392ecf0663d
- update key handling and conditional rendering logic by @OoBook in https://github.com/unusualify/modularous/commit/303bb5590b8baae2d3d0ffee5c4d81d59a9c8054
- update file upload settings for avatar component by @OoBook in https://github.com/unusualify/modularous/commit/46c6e5f761181c87123856b184b1ea8cdea049ab
- update attribute naming for image preview setting by @OoBook in https://github.com/unusualify/modularous/commit/aa3ad4a98711b8e5dbcf582c12d1a2ea3f904bbc
- add density property to componentProps for compact display by @OoBook in https://github.com/unusualify/modularous/commit/730b16678b7da8a6a76e05be6eb09a75448c94a4
- update class for card text alignment in modal component by @OoBook in https://github.com/unusualify/modularous/commit/29f0911458edadaafa5297eb6cda56ca344f6c71
- update token field type in user_oauths table by @OoBook in https://github.com/unusualify/modularous/commit/6c2e7f312e1d040ea5520c64e70e4c55683f0319

### :recycle: Refactors

- update title alignment and add mobile dialog button by @OoBook in https://github.com/unusualify/modularous/commit/79c5a733f08fd1caf06f0a04e41487d25c4303a7
- update OAuth sign-in and sign-up button labels by @celikerde in https://github.com/unusualify/modularous/commit/0ac1ec5fc2828a33064a9de4d23fb13cbb2315dd
- improve alert commit structure and update card variant by @OoBook in https://github.com/unusualify/modularous/commit/458b235d389bf28ebc996991a1a44967e9c7d0d0
- streamline checkbox layout and improve styling by @OoBook in https://github.com/unusualify/modularous/commit/06cdbbe59bcea9b685bed231a763374a0766b017
- improve styling and structure of the comparison table by @OoBook in https://github.com/unusualify/modularous/commit/291c32562e8eb1c7a19799a800d1cbc52a4c6036
- update payment status and user email configuration by @OoBook in https://github.com/unusualify/modularous/commit/8f68d16c80f11a278288c6c8e0441128fe380826
- remove unused slot comments to clean up template by @OoBook in https://github.com/unusualify/modularous/commit/a772a01ed78f612eda4ebfda86a336c99b3c310f
- update invoice file upload limit and restrict role access by @OoBook in https://github.com/unusualify/modularous/commit/1957b36e87326c1ab127baa00b0fcdb6388d8f92
- streamline element handling and improve attribute casting by @OoBook in https://github.com/unusualify/modularous/commit/69a466369bf7b2c31eaff11bc018688ea12e931c
- enhance relationship data handling in getItemColumnData method by @OoBook in https://github.com/unusualify/modularous/commit/cae68f400b9d26785d2ff643746c4c4cd46f70c0
- enhance key parsing logic for input schema by @OoBook in https://github.com/unusualify/modularous/commit/d0881ff72bccbafb7e695dca46f9e54a55a2d87f
- enhance inputs and chunkInputs methods for improved flexibility by @OoBook in https://github.com/unusualify/modularous/commit/39eebfa97455e87daab931e2650eeb504225b8ca
- update input handling in beforeSave method by @OoBook in https://github.com/unusualify/modularous/commit/3b444a970561ec07c509026004f65f9dfbb18e13
- enhance message content formatting by @celikerde in https://github.com/unusualify/modularous/commit/f89bfc8ee8fda5d8359296cb860c4ea836090695
- improve layout and class management for form components by @OoBook in https://github.com/unusualify/modularous/commit/8e48fc195104d4fb2db85b59b830c56bed704fc6
- streamline user registration verification logic by @OoBook in https://github.com/unusualify/modularous/commit/4dd93869d8c55f45b1d5894f7d1d0639be2723b4
- implement pre-registration and complete registration forms by @OoBook in https://github.com/unusualify/modularous/commit/2b3304d3d8d0be283c4e228cf14fa4dfbd62eaa4
- update route names for pre-registration by @OoBook in https://github.com/unusualify/modularous/commit/fb33acbbae6ef0bc5091097e271ff2bcd29b4f5f
- update form attributes and response formatting by @OoBook in https://github.com/unusualify/modularous/commit/19e5b4f51c6a4dcc8e3eec55b9254280497205c1
- adjust padding and layout for improved UI consistency by @OoBook in https://github.com/unusualify/modularous/commit/0a66ffdac2ba009787b5438cacbd5063a834ba49
- enhance form layout and styling for improved user experience by @OoBook in https://github.com/unusualify/modularous/commit/cceeb9cc3a07f108aaefcfd1042614e95bcc932e
- remove unnecessary class attributes for cleaner layout by @OoBook in https://github.com/unusualify/modularous/commit/fa9363da705710fd067fee9d69bf70d605abc930
- update form toggle attributes for improved styling by @OoBook in https://github.com/unusualify/modularous/commit/ace3fa8ae866accacdc969c79274e0b3d3f8fd94
- enhance Published toggle attributes for improved styling by @OoBook in https://github.com/unusualify/modularous/commit/9c04b1961d5879409b8bc7cbe19ec55cf3dd3471
- improve company attribute handling in email registration by @OoBook in https://github.com/unusualify/modularous/commit/6a5192996f19ebb80d3b1174a64fa43f68b9106f
- separate spread_payload assignment for clarity by @OoBook in https://github.com/unusualify/modularous/commit/b656b9295773b877d8945f3ce7cf9cb6dc257d1b
- transition to script setup and enhance layout by @OoBook in https://github.com/unusualify/modularous/commit/2557b057ba7db436c1dd1be68d2d902c89a490e4
- enhance table properties and styling by @OoBook in https://github.com/unusualify/modularous/commit/0a71ac8cdf7bbd648956eebef1cf336c50c66953
- enhance step icon rendering and interactivity by @OoBook in https://github.com/unusualify/modularous/commit/8009994262f8ad1461f6a36bd908d8b472c2e812
- improve stepper window styling and dynamic height handling by @OoBook in https://github.com/unusualify/modularous/commit/908f51d6d0391aae21c7394505b5308aa4f4b30d
- enhance stepper layout and dynamic behavior by @OoBook in https://github.com/unusualify/modularous/commit/0e8879a81e273408d1b4fd38b7068e107e2005a1
- enhance regex pattern for value validation by @OoBook in https://github.com/unusualify/modularous/commit/3ba76088f89e96381ba3d32716eda0e752dd3738
- update label and subtitle rendering for improved HTML support by @OoBook in https://github.com/unusualify/modularous/commit/0ccd6be1cb7c0654980e48e8a11a09e76f1b9a0d
- update maxHeight default value for improved layout consistency by @OoBook in https://github.com/unusualify/modularous/commit/dc60dc3d222bfd07a672955ff1030097cdc751a3
- enhance mobile responsiveness and header properties by @OoBook in https://github.com/unusualify/modularous/commit/db2469b86a22dba601b5a4d4317cb66bab62cbc1
- update button click handler for step navigation by @OoBook in https://github.com/unusualify/modularous/commit/d2408024f950a5aebddcacac1a256c43b61a84f1
- improve layout and styling for label and subtitle by @OoBook in https://github.com/unusualify/modularous/commit/a67bbf39cd12a4ce621e913293e34aba83fc67b7
- simplify message content handling and clean up styles by @OoBook in https://github.com/unusualify/modularous/commit/8f49a20e02405755ccbf45a046f36e1bbfad1125
- update background and text color styles for improved consistency by @OoBook in https://github.com/unusualify/modularous/commit/1d8bef9785f20c6f4ed40c1ad45ae40d117a2960
- streamline method handling and remove unused code by @OoBook in https://github.com/unusualify/modularous/commit/2262e234d76c7fef1ef68e9c39b857bd6076d6d5
- streamline dialog and additional section layout by @OoBook in https://github.com/unusualify/modularous/commit/b88231a7596b219b1acf429fad282fb97520c1dc
- enhance variable pattern handling and replace logic by @OoBook in https://github.com/unusualify/modularous/commit/638087dca97780f9f8a5cbdda9694c4c71cebef4
- enhance attribute pattern matching and replacement logic by @OoBook in https://github.com/unusualify/modularous/commit/5815d678e892507c9dc195eb4e61082dce87dc2c
- enhance payment update logic and modularous integration by @OoBook in https://github.com/unusualify/modularous/commit/b7d57fdbc3081396bf99f145749185c2a808318d
- integrate HasSpreadable trait for enhanced functionality by @OoBook in https://github.com/unusualify/modularous/commit/711195667d4ea913d0ca355eaa413be2744ed10c
- integrate SpreadableTrait for enhanced payment handling by @OoBook in https://github.com/unusualify/modularous/commit/a8e6253f9afb7c50346974e43145afa0276c0d4a
- add spread payload structure for payment services by @OoBook in https://github.com/unusualify/modularous/commit/9040c06904508a2ec2a2c027aae6f8ad41d36aff
- update payment configuration structure for improved clarity by @OoBook in https://github.com/unusualify/modularous/commit/f917f09ed9a27291c6158261121c3ad8fe5a6d65
- enhance payment record creation logic by @OoBook in https://github.com/unusualify/modularous/commit/acaefad3c961714dac0da3ec25a0e2c7d5277031
- enhance model creation logic for form actions by @OoBook in https://github.com/unusualify/modularous/commit/521623d4ee840d7f9dd33a13f9b272c1ef40a572
- update class structure for form actions layout by @OoBook in https://github.com/unusualify/modularous/commit/de62ae5a394ccaee065eacdde6e0182f3fb359d6
- update evaluation pattern and enhance attribute casting logic by @OoBook in https://github.com/unusualify/modularous/commit/acef2ad745ac797e3deb0d0f65ee88fbb13cd2b4
- enhance action handling and filtering logic by @OoBook in https://github.com/unusualify/modularous/commit/716346797680f4884bc8e58e0f37cdf3250f3681
- simplify class attributes for layout consistency by @OoBook in https://github.com/unusualify/modularous/commit/25a9997a6eb2feaa02de80dcd96ba573a9e16f9c
- introduce closure transformation functions by @OoBook in https://github.com/unusualify/modularous/commit/bf65b9c9c70d36e5186143b76dd12983e615e5e0
- replace createModalService with modularous_modal_service by @OoBook in https://github.com/unusualify/modularous/commit/fbc086a432dac89c82d9d8962248cb34e9634fbb
- adjust padding for payment form layout by @OoBook in https://github.com/unusualify/modularous/commit/562c198a0c5e2420b56e3ddd8a2a13d6938e03e5

### :lipstick: Styling

- improve computed property structure for clarity by @OoBook in https://github.com/unusualify/modularous/commit/d49897aad7ae1ea16bc9cf0d022621affeac53ba

### :white_check_mark: Testing

- enhance role-based authorization tests and mock implementations by @OoBook in https://github.com/unusualify/modularous/commit/97955bfe790cd9da5c0e089de19b1541905bb1e1
- enhance email registration tests with event assertions and company creation validation by @OoBook in https://github.com/unusualify/modularous/commit/4626a76aaae2376eb9dd669b28c98cb847028dad

### :package: Build

- update build artifacts for v0.42.0 by @OoBook in https://github.com/unusualify/modularous/commit/8f4ff86d177d7ec7be4499ed80f8d133f889d870

## v0.41.0 - 2025-07-31

### :rocket: Features

- integrate profile menu into layout and sidebar components by @OoBook in https://github.com/unusualify/modularous/commit/2b3e35fb35dde1df0125e2b7d974ed87272e336d
- enhance payment module with new features and attributes by @OoBook in https://github.com/unusualify/modularous/commit/05d72abec59d899e3e1b91a960ccad8db6a2baa2
- introduce MyPayment module with CRUD functionality by @OoBook in https://github.com/unusualify/modularous/commit/6c20d78d7299554c37825168bd2db88b5c10efae
- add support for searching in relationship fields by @OoBook in https://github.com/unusualify/modularous/commit/baf0d1bec62b4c3fda53cfa9519fde3a607e3fa4
- add loading spinner for improved user experience by @OoBook in https://github.com/unusualify/modularous/commit/9127e46372ad721137d619d11f5353797279ef1f
- prevent profile dialog opening for guest users by @OoBook in https://github.com/unusualify/modularous/commit/c171d2618eded23fd06b6f84895901afc54f130b
- add events for Filepond lifecycle management by @OoBook in https://github.com/unusualify/modularous/commit/96e581a069c103f6695b8c3b85a1597aaf2895b9
- enhance notification redirection logic by @OoBook in https://github.com/unusualify/modularous/commit/a2c54b2f9fec5a19a72af6298e3cf5ea28ceb14c
- add filepondable method for polymorphic relationships by @OoBook in https://github.com/unusualify/modularous/commit/2876c356fe6f828ecd5fa981c10c8cdaaf7adfb3

### :wrench: Bug Fixes

- improve creator relationship logic and clean up unreachable code by @OoBook in https://github.com/unusualify/modularous/commit/65735e15dc10f2bdc0a66843a2db995372b1bad5
- reorder HTML elements by length for improved matching by @OoBook in https://github.com/unusualify/modularous/commit/843ddc04d58fe194a4c0f7f2b94f95de06309168
- extend loading spinner duration for better user experience by @OoBook in https://github.com/unusualify/modularous/commit/2483c234cc7a35dbbaba1a5bdaf5427f8fbc34e7

### :recycle: Refactors

- add getFilepondableClass method for improved filepond handling by @OoBook in https://github.com/unusualify/modularous/commit/cdbaf86470aa754d0c1eadf2650f7f10dffa29bb
- enhance creator relationship handling and add user-specific creation scope by @OoBook in https://github.com/unusualify/modularous/commit/8c5037d6d1ece6b5bf5312e0055d04411a0ec62a
- update controller namespace for improved compatibility by @OoBook in https://github.com/unusualify/modularous/commit/b95cbcc79267bf0e674c8def9f0f2809634f78b3
- update slot structure for improved layout flexibility by @OoBook in https://github.com/unusualify/modularous/commit/46af081423788d070a65a35c429b840ec3d9ba09
- streamline layout and include modular slots by @OoBook in https://github.com/unusualify/modularous/commit/458abda47f15c9157742b01f020a49f4ef024600

### :lipstick: Styling

- lint coding styles for v0.41.0 by @OoBook in https://github.com/unusualify/modularous/commit/098a7a3d28775567c80b8f8f87514cb1c16e808f

### :package: Build

- update build artifacts for v0.41.0 by @OoBook in https://github.com/unusualify/modularous/commit/99910da8a627574288e705d5af8edd9e65c6cedd

## v0.40.0 - 2025-07-25

### :rocket: Features

- enhance notification configuration and extend FeatureNotification capabilities by @OoBook in https://github.com/unusualify/modularous/commit/e50c2ece25f0e7acc78051bd638a681480705c9f
- add customizable salutation to email template by @OoBook in https://github.com/unusualify/modularous/commit/c6ede94f889643acda9802e52559f79cd7c77a9c
- enhance notification handling with customizable callbacks by @OoBook in https://github.com/unusualify/modularous/commit/c1b813b976e2c10ec9d1a77fa0d174edc88b734a
- implement dynamic success and error messages for CRUD operations by @OoBook in https://github.com/unusualify/modularous/commit/e0385081bff7bc2d5e3633dee92e8adf146628f6
- introduce HeaderHydrator for dynamic header management by @OoBook in https://github.com/unusualify/modularous/commit/fce4314bb6ffa2fa1b0d90bbd2b2b84bf33cff67
- add 'table-cell' to valid display values by @OoBook in https://github.com/unusualify/modularous/commit/07b5900488829f3a9c6e32a2cfe8b2ad51ab2433
- enhance mobile action visibility and responsiveness by @OoBook in https://github.com/unusualify/modularous/commit/9a9297b361d5a9b76f2520bbadb1f1349071eece
- add support for HTML elements and enhance method handling by @OoBook in https://github.com/unusualify/modularous/commit/c706f5968436b32fba52b6e8c5107b98d37f5e56
- add closure value transformation for dynamic input handling by @OoBook in https://github.com/unusualify/modularous/commit/615f50c6be775c3ab98ef23397260742e686ff0b
- add marked library for markdown parsing by @OoBook in https://github.com/unusualify/modularous/commit/54e20055f7b521803e49764e7e5748279a686675
- enhance table interactivity and responsiveness by @OoBook in https://github.com/unusualify/modularous/commit/4b0e1677dc01979b7e37d75ac454e31e22b65e2f
- enhance checkbox functionality and styling by @OoBook in https://github.com/unusualify/modularous/commit/3b0c3683d5f91cf38d47fed731158a3da5b36572
- implement markdown rendering component with table of contents by @OoBook in https://github.com/unusualify/modularous/commit/6378dad76cd1d34c265b879fb64b4982f000046c

### :wrench: Bug Fixes

- update notification handling to use chat model by @OoBook in https://github.com/unusualify/modularous/commit/3468c7873410834a82264dbb487658f6da31cfb4
- update password rules for enhanced security by @OoBook in https://github.com/unusualify/modularous/commit/1c963ab7ae5d754b6ddb53b69d1b5304b7f2d300
- integrate OauthTrait and enhance user creation logic by @OoBook in https://github.com/unusualify/modularous/commit/6ab4da808499df9c385410a5b8789b18370e6f1c
- add password validation rules for user updates by @OoBook in https://github.com/unusualify/modularous/commit/69c28e9d9660555848b6361c3bf74238004e306d

### :recycle: Refactors

- enhance user fetching logic and toolbar display by @OoBook in https://github.com/unusualify/modularous/commit/5f0a48ad82667c2cff94bf9de458d297778b5a9c
- specify Assignment model type in constructors and enhance event handling by @OoBook in https://github.com/unusualify/modularous/commit/f6de7426c698ae018acb40b3b29efad6be350490
- enhance type safety and improve notification methods by @OoBook in https://github.com/unusualify/modularous/commit/c2720b2f5946f9cc397fe8230de9cf2991f49edb
- clean up unused notification code in StateableListener by @OoBook in https://github.com/unusualify/modularous/commit/fc3f77109d786ee4893710b01f6bde55f1b54a96
- streamline column configuration with HeaderHydrator by @OoBook in https://github.com/unusualify/modularous/commit/4bb8a42b7a03f5ad7493d1a7f69f5341a7cf914a
- enhance route resolution logic for admin and general routes by @OoBook in https://github.com/unusualify/modularous/commit/687e862a782c74a09297dfa9c146886571ef0316
- improve element handling and attribute hydration by @OoBook in https://github.com/unusualify/modularous/commit/38f8eae40e5fcf6df861d3ad97e0d784115eb279
- enhance navigation and configuration handling by @OoBook in https://github.com/unusualify/modularous/commit/41ee2f89a0aa95d01f3d3587d3ec1db808abf4ab
- streamline layout structure and enhance mobile responsiveness by @OoBook in https://github.com/unusualify/modularous/commit/6e28a7ac287ed0a7ebec08fc8e350e1584ffc09b
- update column layout for form fields to improve responsiveness by @OoBook in https://github.com/unusualify/modularous/commit/d28056f5ad709058bd0413e710e83f25d5b89deb
- improve route resolution logic for better error handling by @OoBook in https://github.com/unusualify/modularous/commit/d0b5877f1bd7c1ece4a2c3eb56dbb8c66e8fcc41
- update styling and layout for improved responsiveness by @OoBook in https://github.com/unusualify/modularous/commit/2dc3c9179599fa7dd35a5bb142a9b5f250ed8417
- enhance layout and responsiveness of submit section by @OoBook in https://github.com/unusualify/modularous/commit/f0aa3b3e151b35c1e96f25d8c1c81df533fdbe67
- comment out terms of service validation rule by @OoBook in https://github.com/unusualify/modularous/commit/8a818316f259a4671aa063e004068a0c020f04c2

### :lipstick: Styling

- lint coding styles for v0.40.0 by @OoBook in https://github.com/unusualify/modularous/commit/e7c1a19d76da3bbc448f866e1497c31c04215097

### :package: Build

- update build artifacts for v0.40.0 by @OoBook in https://github.com/unusualify/modularous/commit/868192c6215861b344ace4e4559a9814dc093872

## v0.39.0 - 2025-07-19

### :rocket: Features

- enhance message display with truncation and responsive design by @OoBook in https://github.com/unusualify/modularous/commit/b8d244c698dc50ead53358af6c6926b4659a453d
- enhance chat component with subtitle and file upload support by @OoBook in https://github.com/unusualify/modularous/commit/2a8092b4f0b61ec825d277fe590bfedd0e071ad3
- add methods for app and admin URL handling by @OoBook in https://github.com/unusualify/modularous/commit/740b7424925651e5ede67a21ec9dbc678bf8270c
- enhance error pages with modularous design and functionality by @OoBook in https://github.com/unusualify/modularous/commit/c8f03f854e2386ab93f6ff312d64a739d4e01e65
- add getByIdWithScopes method and enhance getById with scopes support by @OoBook in https://github.com/unusualify/modularous/commit/b67af011222ea075a8919c7977903c797595dea7
- enhance getFormItem method to support authorization scopes by @OoBook in https://github.com/unusualify/modularous/commit/1ddb179f24c9f0c8a5895c7a4f9125f930f4f820
- enhance index method to support eager loading by @OoBook in https://github.com/unusualify/modularous/commit/f0677690aba7e9fe26d563a0fbffcabe89201453
- implement reusable error card component for 403, 404, and 500 pages by @OoBook in https://github.com/unusualify/modularous/commit/141f0dd0440b34f5d7a6256ee60d5dd36a2f148d
- enhance layout and styling options by @OoBook in https://github.com/unusualify/modularous/commit/7e3d0bd0c49b8f60f9513e7beaaf5f3b96a64e1d
- add responsive visibility trait for dynamic class management by @OoBook in https://github.com/unusualify/modularous/commit/8d1b9cf4f1bd7e26e47d34c07a30c0d3513d5e38
- integrate responsive visibility into form and table actions by @OoBook in https://github.com/unusualify/modularous/commit/c61bf2c606e17b873fb2f138b3c8dba6448ee7f9
- enhance filter and action item classes for improved styling by @OoBook in https://github.com/unusualify/modularous/commit/1866a68736d6e69e481bf3e4f89c05ed60d5f949
- add flexBreakpoint prop for responsive layout control by @OoBook in https://github.com/unusualify/modularous/commit/c5822190caa628194042f90329252994101eddb4
- add TranslatableServiceProvider to package providers by @OoBook in https://github.com/unusualify/modularous/commit/fd18886aa331e7af37c940264bf5a5d064d60ea0
- add language parameter handling for request localization by @OoBook in https://github.com/unusualify/modularous/commit/550bca22d6ecc87c353d88a0c0ae1e02ed4d91d9
- add hasScope method for dynamic scope checking by @OoBook in https://github.com/unusualify/modularous/commit/bb70b721b2bfc5cac40b4c1313686c5b9e1bc779
- :sparkles: enhance API route registration and modularous support by @OoBook in https://github.com/unusualify/modularous/commit/f086bc2049c3733cfcd3791130f966d676f91934
- :sparkles: implement new base API controller with modular traits by @OoBook in https://github.com/unusualify/modularous/commit/1f0d3559bbc98d8f18d1e2dbc855e888dfc57004
- add API configuration file for modularous support by @OoBook in https://github.com/unusualify/modularous/commit/ac42ac05fbe3ab8aae006d0b15ad0a7090cafb8e
- refactor UserController to extend ApiController and enhance API capabilities by @OoBook in https://github.com/unusualify/modularous/commit/b757c5920a191b59aa282c28e2c3ef5b84152da4
- add header title functionality to layout by @celikerde in https://github.com/unusualify/modularous/commit/e4deb2210c8ae6a0ca5f80b58d8a5e5ea41effc7
- enhance dashboard view with dynamic page and header titles by @celikerde in https://github.com/unusualify/modularous/commit/a095631a21883cef706e2f945ac8ebf1ecff39c5
- add dynamic page and header titles to profile settings view by @celikerde in https://github.com/unusualify/modularous/commit/d7f520c56146fa2a5fc15aae51fc60b9aed9826a
- add non-run events on create to prevent execution during initial setup by @OoBook in https://github.com/unusualify/modularous/commit/b36bc3aef114577c0b9f81fa65046ac7a9433262
- add support for 'xxl' breakpoint in responsive design by @OoBook in https://github.com/unusualify/modularous/commit/63f5a690abe1d256d665326ee8e34c7dd477f3c5
- include request in user registration event by @OoBook in https://github.com/unusualify/modularous/commit/013fa43c719a53a981d446b5d54d33be388912dc
- add front and API controller paths for module generation by @OoBook in https://github.com/unusualify/modularous/commit/c1e011a4e9825cb7912e001dec928b1411f5a6c4
- add slot for appending custom actions to the table actions component by @OoBook in https://github.com/unusualify/modularous/commit/6ed620997ca58c958c9e70638f6d52595a9cb7a9
- add ModelHelpers trait to Role entity by @OoBook in https://github.com/unusualify/modularous/commit/903190234213b914138eabcc8bb16ed8b8c01cdc

### :wrench: Bug Fixes

- improve responsiveness and text overflow handling by @OoBook in https://github.com/unusualify/modularous/commit/aee7fbef9e77b78fd73c2dc84129b5e50e390abc
- enhance rowActions handling by @celikerde in https://github.com/unusualify/modularous/commit/c40931a51cabb2496a75bdb03ca2270b0c81b6e3
- enhance mandatory item handling and input validation by @OoBook in https://github.com/unusualify/modularous/commit/517fbf6e8416da9887662ffba841a78428abe1d6
- enhance header visibility control for mobile displays by @celikerde in https://github.com/unusualify/modularous/commit/9a67e037fd6d557df95a86dba5bbc0f33b80a301
- update page title generation in success view to use the correct namespace by @OoBook in https://github.com/unusualify/modularous/commit/8fe80a34da6e027e0f014a10ee9dcdc05681f79b
- improve parameter parsing in setEvents method by @OoBook in https://github.com/unusualify/modularous/commit/15c97e860bd88aacc4d1a1f10a2fdebd880cd487
- enhance scopeIsStateables method to handle string input for codes by @OoBook in https://github.com/unusualify/modularous/commit/7da9b4efab55a0669af1b378978750303f0fa110
- enhance pagination and items per page handling for responsive design by @celikerde in https://github.com/unusualify/modularous/commit/8b6e66826ca4c8e0a4c2828e187fce70352afab1
- improve authorization check for index options by @OoBook in https://github.com/unusualify/modularous/commit/47604955d60badad1de13b1766321c7b8ef52d89
- update registration response handling by @OoBook in https://github.com/unusualify/modularous/commit/09f1f24c2ee70ffe5f8c436ad6626fc192664714
- update button class and layout adjustments for improved responsiveness by @OoBook in https://github.com/unusualify/modularous/commit/5515b9a46f3d4077fb8b7f570e26e0ce9ca30e91
- adjust layout classes for improved responsiveness by @OoBook in https://github.com/unusualify/modularous/commit/fc5df0551fa636245d4729a196d073c1045f49f0
- update toolbar title alignment for improved layout by @OoBook in https://github.com/unusualify/modularous/commit/fe4bc2ee6ca57b257950b7af9aba81d489b1b631
- enhance layout and padding options for improved responsiveness by @OoBook in https://github.com/unusualify/modularous/commit/c42c9fc93cd792dce84cb652f54025b8695a4e69

### :recycle: Refactors

- update app and admin URL handling by @OoBook in https://github.com/unusualify/modularous/commit/19d1cab0b05b17c58c121fc36f469256267b7fbc
- add elements property to component state by @OoBook in https://github.com/unusualify/modularous/commit/87bca1da9bab28146c94b364ab606995c47e153c
- update admin app URL handling and improve URL retrieval logic by @OoBook in https://github.com/unusualify/modularous/commit/d5349f819c3f232594cec509b8d7108e69ca8a37
- change Model class to abstract by @OoBook in https://github.com/unusualify/modularous/commit/517780ed5b72054e344d3f8db2807dd2b85a618f
- enhance styling for selected and disabled states by @OoBook in https://github.com/unusualify/modularous/commit/0622a93ba7070610aca34fd971e637afd02c9617
- improve admin app URL handling and logic by @OoBook in https://github.com/unusualify/modularous/commit/6ac635a3f3014d221aa91391374711fae9d401d7
- streamline module route registration and enhance logic by @OoBook in https://github.com/unusualify/modularous/commit/c7f30637e02709864340a9ab591a54ca7135a752
- enhance configuration handling and constructor logic by @OoBook in https://github.com/unusualify/modularous/commit/e2552305c111182791820512571914387b97456e
- enhance language handling and fallback logic by @OoBook in https://github.com/unusualify/modularous/commit/0dca6f4892d5a98de8c887c0e2a9bff6ca8253ea
- simplify schema generation by extracting logic into a separate function by @OoBook in https://github.com/unusualify/modularous/commit/eb83fdaa9bbcd5fd8ec0874a29c830d5a5750d8c
- enhance modularous_format_input and modularous_format_inputs functions by @OoBook in https://github.com/unusualify/modularous/commit/9f378611ce8e3800359d97dc13f3db4262ff522f
- change createFormSchema method visibility from protected to public by @OoBook in https://github.com/unusualify/modularous/commit/f578dad8525ec931310435cafdd69aae5a4ff37a

### :memo: Documentation

- add Allowable trait for role-based access control in arrays and collections by @OoBook in https://github.com/unusualify/modularous/commit/b02428967ed37e3b6ba5fdcc020c2c90e519cc9d
- add responsive visibility guide for modularous trait by @OoBook in https://github.com/unusualify/modularous/commit/9b0c7aa2f17a5ce0c75633c89074676255ca56a5

### :lipstick: Styling

- improve button binding syntax for clarity by @OoBook in https://github.com/unusualify/modularous/commit/7e786e3c81acff7e27f1e7850555a47b1567f707
- lint coding styles for v0.39.0 by @OoBook in https://github.com/unusualify/modularous/commit/b87a2b933d440063e282c84fd144bd9bde248c1b

### :white_check_mark: Testing

- add comprehensive tests for model functionality by @OoBook in https://github.com/unusualify/modularous/commit/46121cfbaa4f3601bca178cd06357cd1b2489b1e
- update admin_app_url configuration to use an empty string instead of null by @OoBook in https://github.com/unusualify/modularous/commit/de512046c9ef336797391786dd4e39cf129186a8

### :package: Build

- update build artifacts for v0.39.0 by @OoBook in https://github.com/unusualify/modularous/commit/b59f859175578fe3f22e821a547f460e2f1d6110

### :green_heart: Workflow

- change version of automated-issue-carrier to v1.0.2 by @web-flow in https://github.com/unusualify/modularous/commit/636f044911e2da7c3bed6ba2c3d0d7cfd43cfcae
- change projects input as wildcard by @web-flow in https://github.com/unusualify/modularous/commit/7757858975116cf1ee670704cd8962ff0c4f3573
- test context print by @web-flow in https://github.com/unusualify/modularous/commit/9bc31da33df1a5d91a07e60f896e7fa876a59c9b

### :beers: Other Stuff

- improve error logging and clean up code by @celikerde in https://github.com/unusualify/modularous/commit/6719f101a6cfcb48132e9f92487f467342743adb

## v0.38.0 - 2025-07-08

### :rocket: Features

- enhance login modal with session expiration message and reload functionality by @OoBook in https://github.com/unusualify/modularous/commit/7e78d21517134913e3689a8f9ef465b39222a115
- enhance formatPrependSchema to support ordering of prepended keys by @OoBook in https://github.com/unusualify/modularous/commit/ce6c9685c3985497d408972b9ec94eb9cc7a3686
- update button actions for notifications by @OoBook in https://github.com/unusualify/modularous/commit/0921f1637f56489b084324a89ca6bac791b31fa3
- implement sourceLoading state management in useInputFetch by @OoBook in https://github.com/unusualify/modularous/commit/e2096eec2532f9fa2c80348a767565f071f3fece
- add loading indicator for schema input source by @OoBook in https://github.com/unusualify/modularous/commit/cd79e7890e839bbd3c1342111d45748d0d0ff87a
- enhance loading state management and update event handling by @OoBook in https://github.com/unusualify/modularous/commit/1f7dadfa3c2c23cd9dd5f8be2852ab5d3c4a9302
- enhance processable details display and validation logic by @OoBook in https://github.com/unusualify/modularous/commit/8a108b0950a58d17c02cc2036cdc3b2f5e87d55f
- add status informational message to process entity by @OoBook in https://github.com/unusualify/modularous/commit/1869cf4a4701ca1707cd851e70c28d3cab162395

### :wrench: Bug Fixes

- correct project name in issue automation by @OoBook in https://github.com/unusualify/modularous/commit/30d7c2bb83bcfe2180f7ef6610060715eee8bf9f
- improve key ordering logic for prepended keys by @OoBook in https://github.com/unusualify/modularous/commit/3c64bad5ceb6262a839801071b76c126a4b6850c
- update route reference to use Module.transNameSingular by @OoBook in https://github.com/unusualify/modularous/commit/a0b20c8c2d1c90cde300aff32d9e3c48f5c90649
- handle undefined item properties in action rendering by @OoBook in https://github.com/unusualify/modularous/commit/7b70a220c7695b93921c7d7bf7e64ca87b4ed557
- update formatter structure for pricing configuration by @OoBook in https://github.com/unusualify/modularous/commit/6c7506f67528f5af630dfd7661b5090ae3cce267
- update condition for form schema value assignment by @OoBook in https://github.com/unusualify/modularous/commit/f2221181cc528f5370711c4f1d01a1045ae93e4e

### :recycle: Refactors

- update action handling to use visibleRowActions by @OoBook in https://github.com/unusualify/modularous/commit/515b278843fbc192fc6b91f6a0b10f4eebfa0fe5
- enhance action rendering and component handling by @OoBook in https://github.com/unusualify/modularous/commit/713220af2995c8e1823b119559f95cd094303ae6
- refactor process model and validation logic by @OoBook in https://github.com/unusualify/modularous/commit/69378864a7ed6fbb57dcdeb45e89f2e14c5c0748
- enhance file validation and rules management by @OoBook in https://github.com/unusualify/modularous/commit/f26013a4637b0b0a2b3daba18a33a845ca4d68c2
- simplify status labels for clarity by @OoBook in https://github.com/unusualify/modularous/commit/aa5b06bae45e0d61c4bdc8e630e062bd189aa7ee
- improve layout and structure of process details display by @OoBook in https://github.com/unusualify/modularous/commit/86da6f30844f39755d3707299c9ff5e22225b21d
- update layout and improve informational message display by @OoBook in https://github.com/unusualify/modularous/commit/8c97f40c8c38e3460eebe14ea044d69230f2a406
- adjust layout for process title and status chip by @OoBook in https://github.com/unusualify/modularous/commit/bd05d70846bf1b24110205fdab60304acbdd16f6

### :lipstick: Styling

- lint coding styles for v0.38.0 by @OoBook in https://github.com/unusualify/modularous/commit/88af8989771ae2e042f6e28f12e9c0099b9cd206

### :white_check_mark: Testing

- mock vue-i18n and useAuthorization for improved test isolation by @OoBook in https://github.com/unusualify/modularous/commit/9bb7507e95efe1d98fdc41f5c45232225f528001

### :package: Build

- update build artifacts for v0.38.0 by @OoBook in https://github.com/unusualify/modularous/commit/4f73919ce26c5785098adee52eb50240b130a3ee

### :green_heart: Workflow

- add workflow to automate issue labeling and project management by @OoBook in https://github.com/unusualify/modularous/commit/f07e4aca774d70ec22e059fcb57a41c7166b1925
- add test flag to project item workflow by @OoBook in https://github.com/unusualify/modularous/commit/6b423a8b1b54de7372cf83bb2372ba7d0c558f77
- disable test flag in project item workflow by @OoBook in https://github.com/unusualify/modularous/commit/6d8bfd0d53185bc405ea7b1f47764c0aa60d346e
- enhance issue closing workflow with repository input and token handling by @OoBook in https://github.com/unusualify/modularous/commit/8158cb19db0930030d74ec7b7feb7a11ce02b3ff

### :beers: Other Stuff

- add .secrets to .gitignore by @OoBook in https://github.com/unusualify/modularous/commit/91898f903954d4b42a10e4a596840f40644a4b8a
- update automated issue carrier version and add step ID by @OoBook in https://github.com/unusualify/modularous/commit/4d3f4e1a29b0cf0a859bea073f3e3d41b817cb59
- update automated issue carrier version to v1 by @OoBook in https://github.com/unusualify/modularous/commit/93d2d2bef7b625fa575ff99429c5ce9289c700c4
- add sourceLoading option to default input configuration by @OoBook in https://github.com/unusualify/modularous/commit/ada91ecdd5728d4a12cc29a672ead35abc01c284
- add 'loadedFile' prop to input emits for enhanced file handling by @OoBook in https://github.com/unusualify/modularous/commit/ae253b38dcc9603bace93d0ab64ca206ee2e4121
- allow rightSlotMinWidth and rightSlotMaxWidth to accept string values by @OoBook in https://github.com/unusualify/modularous/commit/41d1fa8d65310b2b806a86fe2476b5b8ea3a8ef0
- add test:stop-on-error script for improved testing control by @OoBook in https://github.com/unusualify/modularous/commit/06ca63d1e8a8265af673604096a706e8c9543a76

## v0.37.0 - 2025-06-30

### :rocket: Features

- update sidebarMenuItem class assignment for improved flexibility by @OoBook in https://github.com/unusualify/modularous/commit/a733477b65394cb659c3eab7f92c142014bf5b46
- add ModularousNotificationSentListener to handle notification events by @OoBook in https://github.com/unusualify/modularous/commit/5acc360f4e148abc6865e84d9499884b56f75d10
- introduce ModularousFinder facade for enhanced modularous support by @OoBook in https://github.com/unusualify/modularous/commit/fbb9976bcf3a2088c54266966273116bf7edf9de
- add method to retrieve models using a specific trait by @OoBook in https://github.com/unusualify/modularous/commit/b2421bc4728936ae0dc7b3063161a99d9a0adb7f
- implement chat notification system with notified_at field by @OoBook in https://github.com/unusualify/modularous/commit/0ed637c0ff3ab6823afec1f4f22d8b20bf5478c2
- enhance data binding with formItem integration by @OoBook in https://github.com/unusualify/modularous/commit/c5737258dd1c586a6b1cf729fbbcdfd872779f67
- integrate formItem for enhanced data binding by @OoBook in https://github.com/unusualify/modularous/commit/4de49f0e759f209e64f94cceeea4fe68ac7e81b9
- enhance form component with subtitle support and improved data binding by @OoBook in https://github.com/unusualify/modularous/commit/d5538049f1b8b86c51e6261d092ee773a0360dd5
- add page title callback functionality by @OoBook in https://github.com/unusualify/modularous/commit/2e19e3d857685d588b7542b3aca0938cf4a91172

### :wrench: Bug Fixes

- add 'sometimes' rule to roles validation for improved flexibility by @OoBook in https://github.com/unusualify/modularous/commit/3a20d1e47833b97c0803489dea3c62218f20096b
- ensure getModelTitleField returns a default empty string for missing title values by @OoBook in https://github.com/unusualify/modularous/commit/8742f03c866bcb2ad3d69e3d9f57b8200d2c71a9
- update command signature to use option for days parameter by @OoBook in https://github.com/unusualify/modularous/commit/e903535f1125d64b5c7f920de1a1d783aedeeaf6
- adjust header padding for improved layout consistency by @OoBook in https://github.com/unusualify/modularous/commit/278d51cd99c8c6923b3883a1c47d528cc13dafbf
- add color prop and improve button styling by @OoBook in https://github.com/unusualify/modularous/commit/c350372ea8bcdc5d8d53f752764fc037ce9bf719
- move class definition to computed property by @OoBook in https://github.com/unusualify/modularous/commit/3a63c31c79f0e8af668b2c66271fa49c87966975
- update notification color for unread messages by @OoBook in https://github.com/unusualify/modularous/commit/1a6760f36a07bb28addcab72ee17a9f90cdde603

### :recycle: Refactors

- comment out unused orange color definitions for clarity by @OoBook in https://github.com/unusualify/modularous/commit/02a2344b40ad26a356c55ff044a343d68996127d
- comment out rules for logo field to enhance clarity by @OoBook in https://github.com/unusualify/modularous/commit/a2ad6c337ee27176866be83fdfb8cd04649b56df
- enhance accessibility and modal management by @OoBook in https://github.com/unusualify/modularous/commit/586d2c922c0af730b3e44951b17dc90ff3a99f65
- remove unused media library model binding by @OoBook in https://github.com/unusualify/modularous/commit/1d566337529005978c891888d9ede6cbdfd4b781
- adjust layout for improved responsiveness by @OoBook in https://github.com/unusualify/modularous/commit/019add47ba423e2413471e30ddfcb854a54e12c3
- update layout and improve data presentation by @OoBook in https://github.com/unusualify/modularous/commit/6ca469c998529ebe79ec41a8364951ef5b2fa5e7
- update button to span for improved styling and functionality by @OoBook in https://github.com/unusualify/modularous/commit/6e8878ec32dd0cd3526a52c33be3d2762a8860ad
- enhance profile menu configuration for user roles by @OoBook in https://github.com/unusualify/modularous/commit/63b3210ee9ec724a7cf3d040fc335a15006acf0e
- improve form handling with enhanced title and submission logic by @OoBook in https://github.com/unusualify/modularous/commit/a990507b7f68638db50c51fb34e46cfd1103a785
- add title configuration for improved table presentation by @OoBook in https://github.com/unusualify/modularous/commit/640e4a36c5c3a420a132073e4034af369399c039
- enhance relationship data handling in getItemColumnData method by @OoBook in https://github.com/unusualify/modularous/commit/1506e3ca87e45ea41d74714c37f641e4b03371b6
- improve checkbox rendering and add active color props by @OoBook in https://github.com/unusualify/modularous/commit/dd4beb98f215342fee453742f613fe5de088246a
- enhance checkbox rendering and add new props for customization by @OoBook in https://github.com/unusualify/modularous/commit/04842c301267deffbb7de5c58383ce464dbcb2f2
- simplify template structure and adjust title padding by @OoBook in https://github.com/unusualify/modularous/commit/dde856b5e1fe10a76787432b7314266e4ce3bf75
- enhance page title handling across authentication views by @OoBook in https://github.com/unusualify/modularous/commit/362e0b59138dc4d857e82aaf9696201d3b2431cd
- streamline user registration process by @OoBook in https://github.com/unusualify/modularous/commit/ede0df9aca0dcfbfaa2bb1a19a5065fc092f62bc

### :lipstick: Styling

- lint coding styles for v0.37.0 by @OoBook in https://github.com/unusualify/modularous/commit/5499bd8789cb1ac473d1ba9f725184a45e4328ae

### :white_check_mark: Testing

- update role creation and registration success message by @OoBook in https://github.com/unusualify/modularous/commit/bacde4a6e99301ba27b7a146f4d43febe3986de5

### :package: Build

- update build artifacts for v0.37.0 by @OoBook in https://github.com/unusualify/modularous/commit/759a08b2493fc623211975dfeb18c225e8ea9ab5

### :green_heart: Workflow

- change accepted label as planned to create a branch by @web-flow in https://github.com/unusualify/modularous/commit/1e9c3dbf23a7b003e15cde8b9f6f0677d65bbddd

### :beers: Other Stuff

- add new language entries for create and validate actions by @OoBook in https://github.com/unusualify/modularous/commit/a510db8d81c9f6e291d10b6213044816168ad72f

## v0.36.0 - 2025-06-22

### :rocket: Features

- add file size validation options to Filepond component by @OoBook in https://github.com/unusualify/modularous/commit/ace4d9111250a39f07b0ff901096b0f75e1016aa
- add protectDefiner and protectedInputs props for enhanced input protection by @OoBook in https://github.com/unusualify/modularous/commit/10601d90e48c761e163c8990a7f3b56172e28a07
- enhance getByIds method with lazy loading support by @OoBook in https://github.com/unusualify/modularous/commit/c048a782fd1621acdf061fd969849b04f324be22
- add scope methods for state filtering by @OoBook in https://github.com/unusualify/modularous/commit/a960c813bb9d34b3cef24fa3dfdcfc1f33abd419
- enhance form handling with improved data structure and response management by @OoBook in https://github.com/unusualify/modularous/commit/b8e251f0d07856b0602b63c1ee690a5710443dc6
- improve field selection logic and UI feedback by @OoBook in https://github.com/unusualify/modularous/commit/3d4e8ef04b679c6d5dc0a25c394dd939dad7a597
- implement modular logging system with email notifications by @OoBook in https://github.com/unusualify/modularous/commit/c54da5ba7a790f2a4ebb110cc96130a63f04c3fe
- add hydrate_input_type function for enhanced input processing by @OoBook in https://github.com/unusualify/modularous/commit/c6852a56abd4815bba355188d7545f004673953b

### :wrench: Bug Fixes

- improve afterSaveRelationships logic and add logging for numeric data by @OoBook in https://github.com/unusualify/modularous/commit/3213c8677fab0b2e5dbb26a953cf7548e5fd55e8
- update getFormattedIndexItems method to use data_get for improved clarity by @OoBook in https://github.com/unusualify/modularous/commit/16693b790151a1c3e1352e6ae88c37e6320f4e74
- improve loading state handling in StepperPreview component by @OoBook in https://github.com/unusualify/modularous/commit/7c2efbddfbdd5a3bc351063dd070fa06744055af

### :zap: Performance

- simplify getIndexData method by removing unused variables by @OoBook in https://github.com/unusualify/modularous/commit/d24fae361fc38e84192b8450b9512a3d82530fe9

### :recycle: Refactors

- update component naming conventions to PascalCase by @OoBook in https://github.com/unusualify/modularous/commit/6a8f3972d506c13ac34f377e9f106608a96f69cb
- update responseModalOptions type and clean up code by @OoBook in https://github.com/unusualify/modularous/commit/cb9be188baab878ac0e5ece83c54dcc1afc5958c
- simplify input hydration logic by utilizing hydrate_input_type function by @OoBook in https://github.com/unusualify/modularous/commit/fccb2d1b48ba504d805f5a926043575045616ee2

### :lipstick: Styling

- lint coding styles for v0.36.0 by @OoBook in https://github.com/unusualify/modularous/commit/860f84daebd603ad9025eb021ad96fcb015780b6

### :package: Build

- update build artifacts for v0.36.0 by @OoBook in https://github.com/unusualify/modularous/commit/dc4740dbadb8572cd3b6df2236a75d000f4c5c02

### :beers: Other Stuff

- remove logging statement for cleaner code by @OoBook in https://github.com/unusualify/modularous/commit/ad2e9e2086f75a73518629d922745d7a91610acf

## v0.35.0 - 2025-06-16

### :rocket: Features

- enhance model retrieval with connectedRelationship support by @OoBook in https://github.com/unusualify/modularous/commit/28facf156ecb04b57aafd07ac395d0bfc1afc0fc
- enhance getById method with lazy loading support by @OoBook in https://github.com/unusualify/modularous/commit/192e68930b162cd8b85b35c41dc3c127929381c1
- enhance hydration logic to support lazy loading by @OoBook in https://github.com/unusualify/modularous/commit/283bcba15d8c1b208db50eed8fa347bfe17b5003
- add support for lazy loading in filter formatting by @OoBook in https://github.com/unusualify/modularous/commit/6a590571b1bb3cb04544695f9c95a0ce366b4b41
- add comparatorValue prop and improve value retrieval by @OoBook in https://github.com/unusualify/modularous/commit/4614a57a67908935a2937a014ec6eaff50fe498f
- enhance afterSaveRelationships method for improved relationship management by @OoBook in https://github.com/unusualify/modularous/commit/b88b99c30b97952c709b457caf1b975f62dd1c42
- add new Expansion component for collapsible content by @OoBook in https://github.com/unusualify/modularous/commit/7dbcb5e47d2067efe0919cd3fd14c9b298640253
- enhance schema binding with additional props by @OoBook in https://github.com/unusualify/modularous/commit/d5a2b921d54d5e6e1c059b0a1647b158a7582b57
- add sorting functionality for checklist items by @OoBook in https://github.com/unusualify/modularous/commit/6be32e1bb7de13b2d23848ffd7e8ef7cc1b83899
- add FileFactory and enhance File entity with size attributes by @OoBook in https://github.com/unusualify/modularous/commit/12489fbe7c8c660369a274036144aa0748aefc07
- introduce MediaFactory and integrate HasFactory trait by @OoBook in https://github.com/unusualify/modularous/commit/580fe5fd49298b71b60ff8983f6e4874c26a0a52
- enhance orderByCurrencyPrice and orderByBasePrice methods by @OoBook in https://github.com/unusualify/modularous/commit/ab6e11a8240269d427d6c6eb8fc7d531a64229b8
- add validation rules to Price input component by @OoBook in https://github.com/unusualify/modularous/commit/abe71305a098bc9eef183a8a6eafbc30ff10a26b
- add pricing saving key to default attributes by @OoBook in https://github.com/unusualify/modularous/commit/a044df7b5e667e97aecf3c4cdd43d030113bb921
- set default price value in hydrate method by @OoBook in https://github.com/unusualify/modularous/commit/e9ebfe1f55f55ac33f687c4a4e075b93671e2974
- enhance v-text-field attributes for improved validation by @OoBook in https://github.com/unusualify/modularous/commit/53d30b0b2efd35b23ce1c25e09db8d87714cab79
- add errorMessages prop for enhanced error handling by @OoBook in https://github.com/unusualify/modularous/commit/1968d8528bae12f68e24c8939526827f65a95df7
- refactor input handling and enhance error management by @OoBook in https://github.com/unusualify/modularous/commit/b90273e3fa3d865ce0ac30acbb9fa16f348cc4de
- enhance respondWithRedirect method to accept additional attributes by @OoBook in https://github.com/unusualify/modularous/commit/331bae87bed548857be1bbf44ed9fbaaffdb819e
- update getTableAttribute method to accept a default value by @OoBook in https://github.com/unusualify/modularous/commit/14e60f367c66c917af449a123a2f4004d73bad0b
- add redirect option after item creation by @OoBook in https://github.com/unusualify/modularous/commit/2989b666c2007b012c50db0aafa08d725155dbc0
- enhance redirect logic based on response data by @OoBook in https://github.com/unusualify/modularous/commit/e16ce750a79706876bb3679d57aa582a9fe66a2f

### :wrench: Bug Fixes

- add null/undefined check for target input by @OoBook in https://github.com/unusualify/modularous/commit/6faa39c45336c03374affed60989fa3bc80641ff
- update minValueRule to handle undefined and null values by @OoBook in https://github.com/unusualify/modularous/commit/cafacc4baf71140ff69273024cfffde32da63b06

### :recycle: Refactors

- update getById method to use named parameters for improved clarity by @OoBook in https://github.com/unusualify/modularous/commit/4d6db1d00da8e702757bfb5c84364d226b7104c0
- remove commented-out debug code in getFormFieldsRelationships method by @OoBook in https://github.com/unusualify/modularous/commit/197d4942a06a9d0ed33ffc291b769be0e1d444e8
- remove commented-out code related to HasCreator trait by @OoBook in https://github.com/unusualify/modularous/commit/632baa12b16b4ca7297733399df4feeec6603f00
- streamline configuration and cache handling by @OoBook in https://github.com/unusualify/modularous/commit/be6cc18e4611ac62a0a04557eaa2328b828be858
- optimize admin URL configuration handling by @OoBook in https://github.com/unusualify/modularous/commit/a75f74ac602fb392d111b7393004fdceebee9ffd
- replace modularousConfig calls with Modularous methods by @OoBook in https://github.com/unusualify/modularous/commit/aebadf8c328f4d198326672101782e101e591082
- replace Fragment with span for improved HTML semantics by @OoBook in https://github.com/unusualify/modularous/commit/178108f43fd5f756ba15b32d22020c7e917e5899
- streamline modal rendering and improve validation checks by @OoBook in https://github.com/unusualify/modularous/commit/e1b4b84812c50ba033d74b76f35748c029b1e5b9

### :lipstick: Styling

- lint coding styles for v0.35.0 by @OoBook in https://github.com/unusualify/modularous/commit/d823ae6a938af2817d017d4ef3ec4ae3ac978dae

### :white_check_mark: Testing

- add comprehensive tests for File model functionality by @OoBook in https://github.com/unusualify/modularous/commit/eb332a0de23cabc38ac4a51c8441643e69d1a40c
- enhance modularous configuration setup for testing by @OoBook in https://github.com/unusualify/modularous/commit/f80e4b45cf340548114623f8cf0e90baf48fa3c8
- add comprehensive tests for Media model functionality by @OoBook in https://github.com/unusualify/modularous/commit/5c833e8a8eed702b6d19b87e6f3c7aa00bc77fe9
- enhance updateProcess method and mock UeForm validation by @OoBook in https://github.com/unusualify/modularous/commit/fd31b1b56def53a5776185c5c9bd59ea65f15782

### :package: Build

- update build artifacts for v0.35.0 by @OoBook in https://github.com/unusualify/modularous/commit/5009e2ca0176e7d3212db40de3c87ef6af745a4c

## v0.34.0 - 2025-06-11

### :rocket: Features

- enhance sidebar menu item handling with role-based access control by @OoBook in https://github.com/unusualify/modularous/commit/00fb5eb2a7ea5769f5c625a699925d594e72d953
- enhance validation and button behavior for improved user experience by @OoBook in https://github.com/unusualify/modularous/commit/289e479c9e267beb66db1de7b169cb8b49ea0bfd
- add computed property for flattened processable details by @OoBook in https://github.com/unusualify/modularous/commit/d446e1fa807efafa069840720d033e8cafbdbcfb
- enhance email message handling for notifications by @OoBook in https://github.com/unusualify/modularous/commit/5d88430e4f0de22a423fc3c9fafadc4c52e10b38
- add stateableCode attribute for improved state management by @OoBook in https://github.com/unusualify/modularous/commit/ffecc32e1f3e24379ac59b7993d85167ebeabf19
- add noAutoGenerateSchema prop to control schema generation by @OoBook in https://github.com/unusualify/modularous/commit/adaeb6823cc3cfad15cb442278efb8de5a7389a0
- add no-auto-generate-schema attribute to Form component by @OoBook in https://github.com/unusualify/modularous/commit/2caaaca7571630ca6d2bee165481e1237ed89f69
- integrate authorization checks for form submission by @OoBook in https://github.com/unusualify/modularous/commit/9791f7443a661e0374897eebf31e2ad40c050c57
- enhance button behavior based on submittability state by @OoBook in https://github.com/unusualify/modularous/commit/845631e3c99e7801189164f75518b323a76ca462

### :wrench: Bug Fixes

- streamline translation query handling for improved clarity by @OoBook in https://github.com/unusualify/modularous/commit/d9012ea6af886f7927a36d527f9df961579fe88b
- improve file removal logic for better error handling by @OoBook in https://github.com/unusualify/modularous/commit/47c724989df2b04fcc4c94e2154583fc98a928ce
- update tooltip visibility condition for search functionality by @OoBook in https://github.com/unusualify/modularous/commit/a1b20dded7b69e110d0e131a2f5ab0b68f85a42b

### :recycle: Refactors

- update layout and clean up unused code by @OoBook in https://github.com/unusualify/modularous/commit/37269df843ec343dd6ba5ba31f3bc0d061f0e5ff
- comment out unused logic for clarity and streamline caching by @OoBook in https://github.com/unusualify/modularous/commit/a7bac31e2f072db9b2f9c72a907f8b4b9467fcd3

### :lipstick: Styling

- lint coding styles for v0.34.0 by @OoBook in https://github.com/unusualify/modularous/commit/25435834383782daa948eb8d38920c53d5232007

### :package: Build

- update build artifacts for v0.33.0 by @invalid-email-address in https://github.com/unusualify/modularous/commit/5211f01f2a90f775476099f6a562c9d2acb54ef6
- update build artifacts for v0.34.0 by @OoBook in https://github.com/unusualify/modularous/commit/29c3c095240cd9f3bf89dcb5dd37ac28fccb5e05

## v0.33.0 - 2025-06-05

### :rocket: Features

- enhance table functionality with pagination and max height support by @OoBook in https://github.com/unusualify/modularous/commit/9b0da3f9b3919043c87d3dcb9dc565f83a949c11
- add header removal functionality and enhance header filtering by @OoBook in https://github.com/unusualify/modularous/commit/592cf11359a57e570790d0a6db0c1f5a2b208976
- enhance header actions and search functionality by @OoBook in https://github.com/unusualify/modularous/commit/7e7de44b4e2669676709a6c929e8414676695327
- enhance response modal with dynamic properties by @OoBook in https://github.com/unusualify/modularous/commit/8e1f4c0d1a92376889d76753e0e9a62210d8482f
- add success alert on assignment creation by @OoBook in https://github.com/unusualify/modularous/commit/7d2ef950a439d56e1ae0f7613ef620a072da5ef4
- add items per page options for improved pagination flexibility by @OoBook in https://github.com/unusualify/modularous/commit/f6941150cf82286d742b6bcf59b2b1690aa369ae

### :wrench: Bug Fixes

- update formatter to use selected headers for improved functionality by @OoBook in https://github.com/unusualify/modularous/commit/94fd9383837a5cd3da44d6d79aa624e2f20c349e
- ensure future date validation considers time by resetting hours to midnight by @OoBook in https://github.com/unusualify/modularous/commit/da048137a8aba446e03169f8a386abd1202f2c17
- close create form modal after submission to improve user experience by @OoBook in https://github.com/unusualify/modularous/commit/a0aa424b3f8320180e0eefb84ba9ac9431cf4417
- adjust pagination logic to handle zero items per page correctly by @OoBook in https://github.com/unusualify/modularous/commit/f05da66b4c7f1b66f6c67e936a4edcc41fe213b8
- handle null schema in getFormFieldsRelationships to prevent errors by @OoBook in https://github.com/unusualify/modularous/commit/917284e2516da5529ef41b19f1217fdaa555097e
- modify translation query logic for improved flexibility by @OoBook in https://github.com/unusualify/modularous/commit/fb53ca5707a77aa2dce2abd0d3f01a3ee2e47494

### :recycle: Refactors

- increase scrollbar width and enhance thumb styling by @OoBook in https://github.com/unusualify/modularous/commit/6046ed2a56c553a7c1ea0be7a7c156819c81f9ba
- optimize header filtering logic and improve null handling by @OoBook in https://github.com/unusualify/modularous/commit/1e3fe4168d836a899be5e9ae6a6fbc3c81e39ce9
- update default header properties for improved consistency and functionality by @OoBook in https://github.com/unusualify/modularous/commit/a61355025fbe99c45ba732dae4aac81f6b1650fe

### :package: Build

- update build artifacts for v0.33.0 by @OoBook in https://github.com/unusualify/modularous/commit/bd0ed1e5b24a8be0b29f837ee869e79d5c5ecb7c

### :beers: Other Stuff

- change default width for action headers from '100px' to 100 for consistency by @OoBook in https://github.com/unusualify/modularous/commit/be983fa88371b8c8db5aaa715e13f3679830b63d

## v0.32.0 - 2025-06-04

### :rocket: Features

- add 'Surname' field to SystemUser configuration by @OoBook in https://github.com/unusualify/modularous/commit/9d4bd1a74c9ab614f1f013a2aeecf82754e9e867
- change Role model of Role module by @OoBook in https://github.com/unusualify/modularous/commit/2340b2a2ada7f6a388e86b8338a53b1fc772a6b2
- add setFirstDefault feature to InputHydrate class by @OoBook in https://github.com/unusualify/modularous/commit/49e91e66ba1f1d236fc47d8884ea9b55d1f5c039

### :wrench: Bug Fixes

- update item deletion checks for consistency by @OoBook in https://github.com/unusualify/modularous/commit/16b11e1dcca4a46073ca106ab6d91912a1be5c08
- update name validation rule to require a minimum of 2 characters by @OoBook in https://github.com/unusualify/modularous/commit/9fdaf72318e925846e60dc699f07b7445c690124
- comment out 'editable' property in SystemUser configuration by @OoBook in https://github.com/unusualify/modularous/commit/08953614d0d684940be7b5ec91996a3f04c92b10
- add password generation feature and enhance reset password form by @OoBook in https://github.com/unusualify/modularous/commit/5e0eaffb740417d06f50767257525512d4b9a3e3
- change validation rule of UserRequest by @OoBook in https://github.com/unusualify/modularous/commit/fbcf2119115c263630c1091be46d818e1a33b250

### :recycle: Refactors

- restrict users from role updating except superadmin and admin by @OoBook in https://github.com/unusualify/modularous/commit/9c4e802e872d126364032b93d421048a441fe24e

### :lipstick: Styling

- lint coding styles for v0.32.0 by @OoBook in https://github.com/unusualify/modularous/commit/27ff5c8615f85143433ece9dbf903d27bb59f43f

### :package: Build

- update build artifacts for v0.32.0 by @OoBook in https://github.com/unusualify/modularous/commit/d3cc676eb795ca047e3027984ad7a29f5008fc43

## v0.31.0 - 2025-06-02

### :rocket: Features

- enhance pagination component with custom buttons and loading indicator by @OoBook in https://github.com/unusualify/modularous/commit/9af28a989141c3289077928a80042b6593d1f345
- add pagination options for footer component by @OoBook in https://github.com/unusualify/modularous/commit/483ab5781cc6d5152db3a91c1857eb1b74cb7ca2
- add conditional visibility for filter buttons by @OoBook in https://github.com/unusualify/modularous/commit/71c7cf5d479bbbb6204b62a8aaccdd290e395a94
- enhance widget configuration handling by @OoBook in https://github.com/unusualify/modularous/commit/3dcead55c2c2347be7d2fcfac47c112ee242bd3b
- enhance header layout with subtitle support by @OoBook in https://github.com/unusualify/modularous/commit/de00a0655ae7a129f018c27013f0c000dd76399c
- add rounded and elevation props for enhanced styling by @OoBook in https://github.com/unusualify/modularous/commit/878caf940518d3c40c2a8aaa65b8c8462f4e8e1f
- introduce slots for value and label customization by @OoBook in https://github.com/unusualify/modularous/commit/591be788aebb7767a819c3be2dfc3d6eb14bad07
- add new color variables for secondary and green themes by @OoBook in https://github.com/unusualify/modularous/commit/500dba8b45a65dad561ab45bffa416ded99098da
- set timezone on form mount by @OoBook in https://github.com/unusualify/modularous/commit/f26119e865dd989e0f71651d716e29b35c37c161
- add hidden timezone input field by @OoBook in https://github.com/unusualify/modularous/commit/e297f7573d39795ba6dfc5565c3c09126180fc28
- implement auto locale detection based on environment setting by @OoBook in https://github.com/unusualify/modularous/commit/76a94aa433507eca5392cd6eb243e69b65a318d0
- add method to update event parameters by @OoBook in https://github.com/unusualify/modularous/commit/59f4b2cf9e0a28fda8a3bcec7356f7f5caa82189
- store user timezone in session after authentication by @OoBook in https://github.com/unusualify/modularous/commit/642b45b5126cc120d18b1311d27d34b83ed29367
- add  creator record scope by @OoBook in https://github.com/unusualify/modularous/commit/96d840b529286b72ef662b86ec71b3c1a06aa95d
- add timezone field to login forms by @OoBook in https://github.com/unusualify/modularous/commit/ac5e93cd9e867618d9e6eea7efb7e7b427909003
- update column ratios and add date formatting for text inputs by @OoBook in https://github.com/unusualify/modularous/commit/44939718b7dd1a94bca1095dcf38cd32e3902f7a
- enhance last status assignment query with timezone support by @OoBook in https://github.com/unusualify/modularous/commit/d55fbaf3c38a2cc8151f5ee15bfe48d6c54b9475
- enhance date range handling in metrics processing by @OoBook in https://github.com/unusualify/modularous/commit/0d9f9e1f26631d8bbb2f293601689c3d7c9007ab
- add scopes for unanswered chat messages by @OoBook in https://github.com/unusualify/modularous/commit/f4a590b45b68bc2f1100e78ec4e06c460a162fdd
- enhance metrics filtering and date input handling by @OoBook in https://github.com/unusualify/modularous/commit/5a80ae26bacdcf655a7b612e0b2fa076889380fd
- streamline count retrieval and add table filters by @OoBook in https://github.com/unusualify/modularous/commit/a9c68cdb6cdcb89fb2c36a85bf46f9a7165af710
- add methods for table filters and count retrieval by @OoBook in https://github.com/unusualify/modularous/commit/6729c779bd09b1d93e6eecf753606937ede029df
- add method for table filters based on authorization by @OoBook in https://github.com/unusualify/modularous/commit/94c098d0dc7b87bd9e1104da39a6cdc03d94775b
- add table filters for assignment retrieval by @OoBook in https://github.com/unusualify/modularous/commit/443cdc1c281c527e71246b78b98207344816f2ab
- add validation for user roles to prevent assignment of superadmin role by @OoBook in https://github.com/unusualify/modularous/commit/208384b08e950aa009eaeab92fbbbd68e7bfc822
- add scopes for filtering chat messages by creator by @OoBook in https://github.com/unusualify/modularous/commit/0e6be1da5424cc2758c1e7e4b668ed9a62b63fab
- add events for process history creation and update by @OoBook in https://github.com/unusualify/modularous/commit/8eaaf4865d7a872796b713236172a02d1a18fcd2
- include SystemPaymentDatabaseSeeder in default seeding process by @OoBook in https://github.com/unusualify/modularous/commit/bff6b7215071d9290d29ae115edeaadff88906eb
- configure translatable locales for country seeding by @OoBook in https://github.com/unusualify/modularous/commit/7bca40432d545c03be813c03964cbd6d101b3407
- introduce event for user registration process by @OoBook in https://github.com/unusualify/modularous/commit/f6c7ed1f7e0dfcec7317295596be39bcec3b3ad8

### :wrench: Bug Fixes

- ensure custom options are merged correctly by @OoBook in https://github.com/unusualify/modularous/commit/cd35891969b098026dda6e7f3d7c77457c10a97b
- update asset publishing tag for modularous by @OoBook in https://github.com/unusualify/modularous/commit/41b5af2a6e7ae15a93550e93d5283c23074cbdac
- add support for hidden input type by @OoBook in https://github.com/unusualify/modularous/commit/b1cf78ac62e2ddcbcbbbd7ecda4045a20ed9e550
- update available user locales to include only English by @OoBook in https://github.com/unusualify/modularous/commit/158c594e091227e528903274d6d6531289a4ac32
- adjust max-height style for responsive design by @OoBook in https://github.com/unusualify/modularous/commit/b3ab4277a1907f2ded3e43e3081ff450fea63240
- enhance event class selection and module integration by @OoBook in https://github.com/unusualify/modularous/commit/e992fccfe8e8901465cb78b9f903558260617228
- update badge color and styling for improved visibility by @OoBook in https://github.com/unusualify/modularous/commit/7e1c0a2d8cfee10302aaf883497112735084d14a
- adjust responsive width values for display sizes by @OoBook in https://github.com/unusualify/modularous/commit/70c87bf3df44721d7c83f9936478a737972c7e64
- update default page value and query parameter handling by @OoBook in https://github.com/unusualify/modularous/commit/79558fd1c009769e95a8ffa2186dbe37baf000f0
- handle string interval conversion for date calculations by @OoBook in https://github.com/unusualify/modularous/commit/4b2838d8870ba3caa3774d513417743364d7a6ef
- exclude interfaces from model retrieval logic by @OoBook in https://github.com/unusualify/modularous/commit/e6f0b48538cd75f88c9ccd9de2ec7665749ba110
- handle exceptions during model retrieval by @OoBook in https://github.com/unusualify/modularous/commit/d420f953926593ee165951b5d7c62e5dcc3f6352
- enhance default value handling for multiple inputs by @OoBook in https://github.com/unusualify/modularous/commit/4a8a19078119ad5cc8cb6e9657e3cbc91a89aba3
- improve pagination logic and loading state handling by @OoBook in https://github.com/unusualify/modularous/commit/988a7ffc7c83c7505e9aa61982405389a0f4fccb
- add 'spreadable' property to email fields for enhanced flexibility by @OoBook in https://github.com/unusualify/modularous/commit/e2b2c3c0572e6738dbe62a998f7a392de442b8ea
- improve sort handling in orderScope method by @OoBook in https://github.com/unusualify/modularous/commit/0030ce6ccd2cdebf4e1c23bc72981b239fe1b057
- update default page value for improved pagination behavior by @OoBook in https://github.com/unusualify/modularous/commit/3f5bed676cdaac58842f70f6d957302955e7ea3c
- add 'force' property to main filters for enhanced functionality by @OoBook in https://github.com/unusualify/modularous/commit/8c2157e38e6a33f3329be1de8e8505599e205d18
- update description rendering logic for improved clarity by @OoBook in https://github.com/unusualify/modularous/commit/650366a6648d183451d2ed0db4c741e7eda4f619
- add height class to checkbox for improved layout consistency by @OoBook in https://github.com/unusualify/modularous/commit/96ece835bd3421b3aa92d375a22161197809c7af
- extend regex to include 'Span' for component creation by @OoBook in https://github.com/unusualify/modularous/commit/aeb4d73c4aba5951d03b8627e0acb7c3d367bfee
- update dialog visibility conditions for guest users by @OoBook in https://github.com/unusualify/modularous/commit/086595b65d4eae2a83ba7b93ebf33803f0f701ea
- enhance currency conversion and exchange rate handling by @OoBook in https://github.com/unusualify/modularous/commit/e61a72bb583620d51d79cd1a7a5f0bdb740831dd
- remove debug statement from rulesForTranslatedFields method by @OoBook in https://github.com/unusualify/modularous/commit/e1f2f372ed3f9f42af85ff9045f1ba43904aea4f
- update asset publishing tag for modularous by @OoBook in https://github.com/unusualify/modularous/commit/8ec6f96cb7f08662c75bebbcc287bf18fb0c7ec9
- update roles configuration for improved component integration by @OoBook in https://github.com/unusualify/modularous/commit/4930fdf6dce72a559fb5ae750d21ca9bb6d11a42
- clean up code and improve query handling by @OoBook in https://github.com/unusualify/modularous/commit/ca865eac273fc9c94a4084003840a891ad544102
- update column classes for improved text wrapping by @OoBook in https://github.com/unusualify/modularous/commit/b47c3706d276988597de9baa86588b069ee67449
- update pagination logic and variable naming for clarity by @OoBook in https://github.com/unusualify/modularous/commit/fdd78fb2e6a0c53fa3c7b0b4af77e9950b1ea0db
- refine pagination logic and improve readonly state handling by @OoBook in https://github.com/unusualify/modularous/commit/4aed602d45db7523812014dffa3c4bc287b8b90e

### :recycle: Refactors

- add setWidgetAlias method and update widget class by @OoBook in https://github.com/unusualify/modularous/commit/d88013dd79d54f9ad813903a6d076051f07b3962
- update default table attributes for styling enhancements by @OoBook in https://github.com/unusualify/modularous/commit/ee6282f5cb840d9d04416cb1685987cff2c55ff5
- update class attributes for improved styling by @OoBook in https://github.com/unusualify/modularous/commit/5ef1d619241f5afca9e39bfff03b20e215f10814
- refactor filter retrieval and enhance flexibility by @OoBook in https://github.com/unusualify/modularous/commit/8bee6dbfcf0fd279f2f30736e7070c09558cf33a
- enhance input handling and default value management by @OoBook in https://github.com/unusualify/modularous/commit/24730904cf20b82cf2afe67a5fcc9762bf535ff0
- enhance input props and default value handling by @OoBook in https://github.com/unusualify/modularous/commit/c1b240fa975433d04da9672b106a3f98af6d2082
- update input properties for user roles and avatar handling by @OoBook in https://github.com/unusualify/modularous/commit/c601b9e11800cfd2209c519e5039d7265dee5417
- streamline right slot structure for improved readability by @OoBook in https://github.com/unusualify/modularous/commit/d72991d3022d9da31271a6297a61d521ba82c362
- adjust divider margins for improved layout consistency by @OoBook in https://github.com/unusualify/modularous/commit/eaca77b7e78f1d01abc0f2fd0f08ef490821b2d3
- remove unused seeder for default test users by @OoBook in https://github.com/unusualify/modularous/commit/5ea106ad3141a8c3c02e743fcf47c0f84642cff1

### :lipstick: Styling

- lint coding styles for v0.31.0 by @OoBook in https://github.com/unusualify/modularous/commit/039dd2993541c759d4a2365f03f1096c965a7f4a

### :package: Build

- update build artifacts for v0.31.0 by @OoBook in https://github.com/unusualify/modularous/commit/9dfca9fad7a1d61493cf24aab05fb237f18554cb

### :beers: Other Stuff

- remove unnecessary newline in config.php by @OoBook in https://github.com/unusualify/modularous/commit/70ebd8d726d364b0918fe69e6efeba3fd9a5a00a
- add todo lastHistory method by @OoBook in https://github.com/unusualify/modularous/commit/357ef4759bde97b9b44c09e52a1e2e44d4afc1f8
- remove initializeInput by @OoBook in https://github.com/unusualify/modularous/commit/e96a2880d374523e17fe4cd6bf82451114b06f43
- add country permissions for role management by @OoBook in https://github.com/unusualify/modularous/commit/b8170fd61d7cf1a5730a4c7b0000316e634f2915

## v0.30.0 - 2025-05-26

### :rocket: Features

- add clearOnSaved prop to reset form state on successful submission by @OoBook in https://github.com/unusualify/modularous/commit/15163ba394a6f5577e1327ee3e4545ae977beee6
- add clearOnSaved option and update response structure by @OoBook in https://github.com/unusualify/modularous/commit/b68803c0aef29bdfc23778b0ace19082b4ee18bb
- add subtitle prop and enhance layout by @OoBook in https://github.com/unusualify/modularous/commit/fe4cad39d934982f7f6705cfdd50adab1c04626b
- add 'title' column to roles and update DefaultRolesSeeder by @OoBook in https://github.com/unusualify/modularous/commit/c17888069069fb9014406f3e5aa5ac9876b0789a
- :sparkles: add terms and conditions checkbox component by @OoBook in https://github.com/unusualify/modularous/commit/cc565190eeaee920c35a30875ac91a5e4e60f2c3
- add country management functionality by @OoBook in https://github.com/unusualify/modularous/commit/a824c63265e56b61f1d4bfd8415ca44f3e86534a
- restructure sidebar layout and enhance functionality by @OoBook in https://github.com/unusualify/modularous/commit/01b97067c19f52be1f6b2f6d96358d625a4be0de
- enhance company entity with country relationship by @OoBook in https://github.com/unusualify/modularous/commit/f6e23061b0d325d6d76777f9b81ccbcb976c0cd5
- add country-related entries to merges configuration by @OoBook in https://github.com/unusualify/modularous/commit/6fd369465ee9a2a4e644b284e77dc6c5cc7da585
- enhance registration process with company details and validation rules by @OoBook in https://github.com/unusualify/modularous/commit/1bf2779bd250467bb035ece84a536abd2812f36c
- update user configuration with country selection and form adjustments by @OoBook in https://github.com/unusualify/modularous/commit/66528b50cbb0ddb58466ee4214b455f85aa54cea
- add country_id validation rule for company requests by @OoBook in https://github.com/unusualify/modularous/commit/df859af2f857b3e7fd9f5651988a7390081baf7d
- add country_id validation rule for user requests by @OoBook in https://github.com/unusualify/modularous/commit/0b192b33321aaf333dcd94968d646a15112da69f
- add 'Company' permission and update deletion permissions by @OoBook in https://github.com/unusualify/modularous/commit/470ec686c5c2ade8787e340cde17d9e5d4d5a03a
- enhance query parameter handling and response updates by @OoBook in https://github.com/unusualify/modularous/commit/8a0f05ae6182cedcbb3fdde5c36ab9877ed4e6fb
- enhance pagination logic to support specific record retrieval by @OoBook in https://github.com/unusualify/modularous/commit/7a74ab7873996c5d497ab64a826580d5e8061236
- add optional ID parameter for item retrieval by @OoBook in https://github.com/unusualify/modularous/commit/fca47ef4bbd12214b0ae48355215c7694cd1f241
- add configuration for notification channels by @OoBook in https://github.com/unusualify/modularous/commit/80c16640782b934b84bc4d7b46e81f71399e2d88
- :sparkles: implement state change notifications by @OoBook in https://github.com/unusualify/modularous/commit/420519d30eaa5632eb23bbfbf1eb63126a515402
- enhance scope filtering with dynamic configuration by @OoBook in https://github.com/unusualify/modularous/commit/0780cfdcce081fca1252b1d5e0701fbc34fb7e9e
- enhance dynamic filtering capabilities by @OoBook in https://github.com/unusualify/modularous/commit/6b432079cbfd9d43fea4e56ea49c2de6a71a4a92
- update navActive configuration retrieval by @OoBook in https://github.com/unusualify/modularous/commit/873d435a345802ae10f5754a4b850bc725be7e99
- add readonly prop to checklist items for enhanced control by @OoBook in https://github.com/unusualify/modularous/commit/632f100bb292853bb29fce24180fe2e4c7c8d3a4
- add new hook for dynamic attribute casting by @OoBook in https://github.com/unusualify/modularous/commit/26c3250a39831934e1fb6ae4024f00b8f49663da
- add functions to update table elements and their attributes by @OoBook in https://github.com/unusualify/modularous/commit/24511152bd85adc6c77ab6ced899d45051205589
- enhance action handling with pre-processing and dynamic modal support by @OoBook in https://github.com/unusualify/modularous/commit/11dbc2067364228ee175946b72d12301eaea4683
- implement MyNotification feature with CRUD operations and routing by @OoBook in https://github.com/unusualify/modularous/commit/5c79f6d57d33e92806cbe6b29f1f4d3a47dbe73a
- enhance routing for MyNotification with bulk mark read functionality by @OoBook in https://github.com/unusualify/modularous/commit/312128a13a4e78c2cfaffd068269d60bcc97ef62
- add MyNotification permissions for various roles by @OoBook in https://github.com/unusualify/modularous/commit/07c2151e8bd8b69f426c2e2f60bf6ecf7adb46b5
- enhance notification handling with unique token and HTML message support by @OoBook in https://github.com/unusualify/modularous/commit/0ec04f4a34d75241aa2c445810dcf7fd459cdefc
- enhance status formatting with dynamic icon and color support by @OoBook in https://github.com/unusualify/modularous/commit/ffce0c68f804008a7cf6f06996175cd59739f235
- introduce FeatureNotification class for enhanced notification handling by @OoBook in https://github.com/unusualify/modularous/commit/db221afad15dacc0c321feeec1ae45cdda733833
- add event and notification classes for assignment management by @OoBook in https://github.com/unusualify/modularous/commit/19a88755a7979cb8e8fd94aeb46af75d0238b480
- add payment event and notification classes by @OoBook in https://github.com/unusualify/modularous/commit/7639d0c841e650e680fb734a8e33256ed327ecc6
- add subject attribute to Notification entity by @OoBook in https://github.com/unusualify/modularous/commit/a1046c6d5dbbb24fb0330253d3ee5c0dd012e1c1
- register new event listeners for assignment and payment events by @OoBook in https://github.com/unusualify/modularous/commit/025014b363c82d7109cb8ccf39c725c6304a0c9b
- implement polymorphic relation for paymentable model by @OoBook in https://github.com/unusualify/modularous/commit/272554a573d24267b9c9c59a07ad01e836f31e0f
- add badge support to navigation items by @OoBook in https://github.com/unusualify/modularous/commit/e85687f6600ea5e6d87a86eab98000cd80951182
- add subtitle to payment table options for improved user guidance by @OoBook in https://github.com/unusualify/modularous/commit/2854c2ac072e19e42c23ce9b9710710f703d6126
- add item deletion status computation by @OoBook in https://github.com/unusualify/modularous/commit/27c2b08df398b40f966423abd74afe8b6d2f7aa5
- add refreshOnSaved prop and enhance saveForm logic by @OoBook in https://github.com/unusualify/modularous/commit/c6e1a332f1eff9e100bdac6b3e21f3ee8132e051
- add getCustomRowData method by @OoBook in https://github.com/unusualify/modularous/commit/bd599497ec0ec87c33c17b3bfa5df19cd4c3abc0
- add isAuthorized attribute for user authorization check by @OoBook in https://github.com/unusualify/modularous/commit/f79222b5684566b9f8b3119c95f987fb4b10ceac
- enhance notification routing logic by @OoBook in https://github.com/unusualify/modularous/commit/dde02551fc81908836037138c81fb48695af2abe
- add model and token accessors by @OoBook in https://github.com/unusualify/modularous/commit/f41ef62141c0c05023e4768085dfe433d8590950
- add scope for retrieving unread notifications by @OoBook in https://github.com/unusualify/modularous/commit/451b768a9e74ed7fea4b9a3e3a909eab153c2a61
- add new price mutators for payment status by @OoBook in https://github.com/unusualify/modularous/commit/92b0b44f1f3f3303fca935db3046863ecb0b98ae
- enhance payment status initialization and mutator logic by @OoBook in https://github.com/unusualify/modularous/commit/b0d992f498069ce1058c9e8ecc45f003d8a873cc
- update locale and currency configuration by @OoBook in https://github.com/unusualify/modularous/commit/10fc6ac83a9c502e90ddd74fc59e9323d81f25d3
- add refund status and formatted payment status attributes by @OoBook in https://github.com/unusualify/modularous/commit/b862552d85c7acb878320b4a95471ae35be8b112
- update notification channels and add TaskAssignedToAuthorizableNotification by @OoBook in https://github.com/unusualify/modularous/commit/a1ad565e4cb40653ec9cb238f789b43318b19c07
- add scope for checking base price existence by @OoBook in https://github.com/unusualify/modularous/commit/4de977e379156a7989c75cc3763db21b82b931ad

### :wrench: Bug Fixes

- update payable table name and add foreign keys for payment service, price, and currency by @OoBook in https://github.com/unusualify/modularous/commit/d982ee8271687d6edc25208f790f75d940fa2971
- update payment currency ISO code and include additional fields in payment service creation by @OoBook in https://github.com/unusualify/modularous/commit/b6305a5e434d5b4710b02fb33b5c4606333cefff
- update width values for responsive design by @OoBook in https://github.com/unusualify/modularous/commit/6453fb90044637ec6f8c644df167bcdecdcfc65d
- enable validation rules for form fields by @OoBook in https://github.com/unusualify/modularous/commit/8b6f7f28b77387a06a7219f88d57989c9662521a
- add email verification upon password reset by @OoBook in https://github.com/unusualify/modularous/commit/dced0ecd28fdaa34ef285524fc0481d35e5dd050
- add mobile breakpoint prop for improved responsiveness by @OoBook in https://github.com/unusualify/modularous/commit/8866004e51330a08920b9d28368b5d78e8aaa29b
- add preview key handling for input fields by @OoBook in https://github.com/unusualify/modularous/commit/a811b48409f5775435bb06941f72f0cbf671bccb
- bind data to preview component for enhanced functionality by @OoBook in https://github.com/unusualify/modularous/commit/add9840199ef944bce74929ae6bda31a11f4d97f
- enhance rules handling for input validation by @OoBook in https://github.com/unusualify/modularous/commit/13ab2e3a0e8d1969432cdd23306dffa565057a4f
- improve filepond rules handling for attachments by @OoBook in https://github.com/unusualify/modularous/commit/ee830ab27be76aee36ab50074424ae1cb94d33b3
- enhance layout with padding adjustments by @OoBook in https://github.com/unusualify/modularous/commit/295829aa45d3b21e1c61fd43d6b148b020fb341f
- adjust form class padding for improved layout by @OoBook in https://github.com/unusualify/modularous/commit/e4de1faa1ac1d3601be30bf9ea216c37ed261470
- enhance filepond configuration for attachments by @OoBook in https://github.com/unusualify/modularous/commit/1d66ff2ad763d3f6adc66e857afc8864e6fc8a25
- update notification structure and functionality by @OoBook in https://github.com/unusualify/modularous/commit/42fa6f49655e3dad25e6f9ba1e1d2fd9d5f16eaf
- correct URL handling in getRouteActionUrl method by @OoBook in https://github.com/unusualify/modularous/commit/7934c3a7a902f27672627e386d8b5f2d9ff8f2be
- return rules in mergeSchemaRules method by @OoBook in https://github.com/unusualify/modularous/commit/c5d277f27fa048fd23a2bcb3829028dda736abc0
- adjust price value handling in getFormFieldsPricesTrait method by @OoBook in https://github.com/unusualify/modularous/commit/591b1d3921990b6bac3547a197b82b7c47e20365
- correct price calculation logic in price formatting method by @OoBook in https://github.com/unusualify/modularous/commit/112cc62dac8c8c1810d07112c003f56f4b9e8487
- update search key retrieval in manage table trait by @OoBook in https://github.com/unusualify/modularous/commit/c01f8fbbcb38ac54a9e6fbbc1473924fbaa15034
- improve payment price handling in afterSavePaymentTrait method by @OoBook in https://github.com/unusualify/modularous/commit/d2dbde0616fd14914eda30550b544842685aeefe
- update description rendering to support HTML content by @OoBook in https://github.com/unusualify/modularous/commit/e195f56e0be13c49e1726b68fba7b86da5faf13b
- update migration to add payment_service_id and currency_id foreign keys by @OoBook in https://github.com/unusualify/modularous/commit/1de18fc15c78f234d1b3251e6095e011cffb65d5
- update modal title and class attributes for notifications by @OoBook in https://github.com/unusualify/modularous/commit/43654c00010c3b12e47c034fae723c587b3960f6
- update conditions for table row actions to exclude completed payments by @OoBook in https://github.com/unusualify/modularous/commit/cf7bc6eb978290f6c79d8468848436d935f1cd6a
- uncomment notification for task assignment by @OoBook in https://github.com/unusualify/modularous/commit/d24f38deec8bdb24249b23d13a0438178397b929
- update getNotificationUrl method signature by @OoBook in https://github.com/unusualify/modularous/commit/e837b36e68b9e33dc8f84e8c4e20db24e489f9a6
- use updateQuietly for assignment status update by @OoBook in https://github.com/unusualify/modularous/commit/11e6bd16511e8b3399b49ef61e5e09ad2ce29043
- adjust raw amount calculations for price handling by @OoBook in https://github.com/unusualify/modularous/commit/da0a946085ead99601996f30dffa3829ec6cbc3a
- update payment price handling logic by @OoBook in https://github.com/unusualify/modularous/commit/026279634b713515b7e60cbdee79035c5ebd4f89
- ensure minimum repeats are met on initialization by @OoBook in https://github.com/unusualify/modularous/commit/7c877ac4543dc13214b2823094cfdbe4d01f6c18
- comment out itemTitle in payment service configuration by @OoBook in https://github.com/unusualify/modularous/commit/4b5211ea23cb869e3eabdb68e52e40c76f7b67eb
- improve mail message formatting for payment notifications by @OoBook in https://github.com/unusualify/modularous/commit/61a9a7456c00cdbf3209f0ee8951ad445f031a77
- enhance currency handling by @OoBook in https://github.com/unusualify/modularous/commit/84e90cbd554c6664266022f5e005fc531815ca14
- update facade reference from UNavigation to Navigation by @OoBook in https://github.com/unusualify/modularous/commit/074fac8ed6fb90ed06f76d800998b4672fedd6ed
- improve configuration handling for sidebar and profile menus by @OoBook in https://github.com/unusualify/modularous/commit/f4f0147547f08ed67cdfa3f4e80760418a0fb69d
- improve response handling in update method by @OoBook in https://github.com/unusualify/modularous/commit/87b54b89ee1c5ca6c2ee8a64c7c665c4fad9cad1
- update currency display logic and improve formatting by @OoBook in https://github.com/unusualify/modularous/commit/9fd7e05d5011e4c099cf894ac155e8b98b3ef6c5
- run impersonate middleware before 'language' middleware in core middleware group by @OoBook in https://github.com/unusualify/modularous/commit/28da4b9c8e83dadbb208568512e141fc12e0e368
- update persistent prop in Assignment component modal by @OoBook in https://github.com/unusualify/modularous/commit/c0e069bea983e51f231da194e183d8391ea056f7
- handle null authorization record in getAuthorizedModel method by @OoBook in https://github.com/unusualify/modularous/commit/9868e9775008611b6fcd9df7f28a37b79c54a2c0
- enhance event class selection and module event path handling by @OoBook in https://github.com/unusualify/modularous/commit/bedc00dd9e7082c471f37a318649cd2759891a08

### :zap: Performance

- enhance performance returning index resource by @OoBook in https://github.com/unusualify/modularous/commit/e5fed06ffe060e328c4e802b0d5de65c229bb675

### :recycle: Refactors

- simplify form schema creation and clean up unused code by @OoBook in https://github.com/unusualify/modularous/commit/b38f3fa9b27e936aad93da690dd38791e1ca791b
- comment out unused Apple sign-in button code by @OoBook in https://github.com/unusualify/modularous/commit/8684c6a76993e1e5fbe37fc5ed5b822120584bba
- make pageTitle variable optional for improved flexibility by @OoBook in https://github.com/unusualify/modularous/commit/ec09a4d37c48d1d1bafa6d0bc9404d509f3843c0
- update tab management and subtitle styling by @OoBook in https://github.com/unusualify/modularous/commit/186077a80c082acdf96ee2515207cead9f711835
- enhance tab component with additional properties by @OoBook in https://github.com/unusualify/modularous/commit/6a97aa26fd0cc605185a54d24723ff13f383e61c
- update scheduler commands for consistency by @OoBook in https://github.com/unusualify/modularous/commit/d1240926319442179433fead58f6d6076ae035d1
- remove debug statement from register method by @OoBook in https://github.com/unusualify/modularous/commit/05977944870cc3efa0bb19edbd1203d395e5cfe8
- enhance layout and search functionality by @OoBook in https://github.com/unusualify/modularous/commit/18c072173a275354daa8e673d34b17be6694bf86
- improve slot binding in authentication layout by @OoBook in https://github.com/unusualify/modularous/commit/5c1d24c38123825edef3d2f04093986cfe60af9c
- streamline form structure and enhance button options by @OoBook in https://github.com/unusualify/modularous/commit/9cde7c0d07d6be3ff323d12b3b894e65a61cba95
- update terms of service checkbox type and rules by @OoBook in https://github.com/unusualify/modularous/commit/d032ce245392b7d305d5983a3eaa2170f036229c
- comment out unused class properties for clarity by @OoBook in https://github.com/unusualify/modularous/commit/166fe8bc4b6bce7ec49330ee257907fdc95373fe
- rename re_password field and comment out validation rules by @OoBook in https://github.com/unusualify/modularous/commit/baa1db74f22ccdc07ae971d5700d705e5c074a90
- enhance layout responsiveness and styling by @OoBook in https://github.com/unusualify/modularous/commit/8be3cb6236eb712ba16b6b6187a4e2396347391b
- enhance layout responsiveness with mobile breakpoint and order adjustments by @OoBook in https://github.com/unusualify/modularous/commit/2c0112ea610e41da16619644124cf1b9b12ba30b
- uncomment vertical divider for improved layout clarity by @OoBook in https://github.com/unusualify/modularous/commit/800c87b6c135a3f3f67341e6682909cc964a2143
- enhance entity functionality with traits by @OoBook in https://github.com/unusualify/modularous/commit/3a8731e64cadf0a1392952a118813e62578795eb
- comment out module activator for clarity by @OoBook in https://github.com/unusualify/modularous/commit/5569ba438daad264710d5e1bd2651113757ca439
- update country field to country_id for consistency by @OoBook in https://github.com/unusualify/modularous/commit/ecf6fe63b1a62651eea64157bf5164f219d82867
- update country field to country_id and adjust validation rules by @OoBook in https://github.com/unusualify/modularous/commit/60b56915361e2669b6814b8859761c50c83c5599
- comment out hardcoded company field values by @OoBook in https://github.com/unusualify/modularous/commit/955f2df43433b46a39110f96e79d1d523d34a02a
- update country field to country_id in companies and users tables by @OoBook in https://github.com/unusualify/modularous/commit/10e7f2e6c84ba8d6bea9903b38aac10a46634086
- update country field to country_id for consistency by @OoBook in https://github.com/unusualify/modularous/commit/13a3627727faee99e89459e7924e938971df13ee
- move observer to Entities by @OoBook in https://github.com/unusualify/modularous/commit/faef5cd96280d7754f474b782844f663ee54b256
- enhance recursive component with bind-data support by @OoBook in https://github.com/unusualify/modularous/commit/9b2d00fef8200b496a7a1323f3a90a27145c2daf
- enhance input properties and schema handling by @OoBook in https://github.com/unusualify/modularous/commit/35aea951412ac775a27b7217efa9cf5cac6f8d8c
- comment out unused field cleanup logic by @OoBook in https://github.com/unusualify/modularous/commit/24dce74ffe61c0058e055bcc69839f064754b7f5
- streamline state management and enhance relationships by @OoBook in https://github.com/unusualify/modularous/commit/068398a5d26f19ce808c41e19222d735637e79d7
- rename URI methods to URL and update references by @OoBook in https://github.com/unusualify/modularous/commit/5cc49296bea437aec0c7a7d28fed17acf1a9cc69
- optimize query parameter handling and local storage management by @OoBook in https://github.com/unusualify/modularous/commit/e583bf25d001e6c0a4e303f59ec2a635327dd1bc
- enhance route handling and refactor endpoint methods by @OoBook in https://github.com/unusualify/modularous/commit/fecad839b72b2529b7fdce30058a36c79112aa26
- comment out unused price-related logic in updating hook by @OoBook in https://github.com/unusualify/modularous/commit/4764d2cc670047d063f8642febe8b24058e69cf8
- remove obsolete notification form and index views by @OoBook in https://github.com/unusualify/modularous/commit/0dd921e89b48ce799ab67ebe3a23bdb9e992a5a0
- add seeder for default countries by @OoBook in https://github.com/unusualify/modularous/commit/79ecdeaadc23eb0cc01b51cce31340f4bf114f2e
- streamline table row action configuration by @OoBook in https://github.com/unusualify/modularous/commit/34589eb8eac720eb033a67514dd28935008316a5
- clean up user seeder by removing commented-out entries by @OoBook in https://github.com/unusualify/modularous/commit/5b2991f2dec01c31b2f428f932445963c3549472
- clean up code by removing unnecessary whitespace and updating key definitions by @OoBook in https://github.com/unusualify/modularous/commit/cf93cbe1e768748c665ca41a5a6a9e126fcabe3b
- enhance modal component with fullscreen and title features by @OoBook in https://github.com/unusualify/modularous/commit/7c9171d3094864dfeed1aabf1d777dacc4a1d0f9
- update text rendering to support HTML content by @OoBook in https://github.com/unusualify/modularous/commit/d3ecd2259b7e1c6b22585d04e97ab7f74d6ecd33
- remove unnecessary URL manipulation in put method by @OoBook in https://github.com/unusualify/modularous/commit/79496ef634d1f5483af5603847b58201ca13212b
- simplify notification access control and streamline show method by @OoBook in https://github.com/unusualify/modularous/commit/599476ccd72746cadf1e27e2a47bc768417f3842
- rename UNavigation by @OoBook in https://github.com/unusualify/modularous/commit/ef7ebf90c8abf2bfee255f68e7898af4ef661ac3
- enhance sidebar menu item handling by @OoBook in https://github.com/unusualify/modularous/commit/03f927326d9325d34e2dd6d4e5ad5ef6e4b33f57
- streamline navigation configuration handling by @OoBook in https://github.com/unusualify/modularous/commit/c75d3fa231a483e567592c624560d25b4b1819ad
- update button text and layout adjustments by @OoBook in https://github.com/unusualify/modularous/commit/38130f3f21b0ca808949573108a90a8108f49863
- update notification configuration and improve user guidance by @OoBook in https://github.com/unusualify/modularous/commit/050d6ca246ce87276b4698fbd327986695673eeb
- streamline filter method implementation by @OoBook in https://github.com/unusualify/modularous/commit/b661439c6c05d6108f8af68b97ce49eb790ae401
- enhance item action handling and add new props by @OoBook in https://github.com/unusualify/modularous/commit/cf93ecb85b3f6c375ca326764c72491986bf5ac4
- update action handling based on item deletion status by @OoBook in https://github.com/unusualify/modularous/commit/7341cada0ba99cc8ea58b0635c425490bcfb4b0b
- pass isEditing prop to slots for improved state management by @OoBook in https://github.com/unusualify/modularous/commit/9c7e48f723d1a282766b961b1819c4ed05264184
- enhance component props for improved customization by @OoBook in https://github.com/unusualify/modularous/commit/cd382050fd512335b8eabeb13ab96ad5e2205fcc
- extract getExactScope method for improved scope management by @OoBook in https://github.com/unusualify/modularous/commit/069fe9f59a4db5e8626a922873dc3dc9bae6d368
- utilize getExactScope for mainFilters by @OoBook in https://github.com/unusualify/modularous/commit/a08283b3dda3621d556230040428781d38dadc80
- streamline form data handling in getFormData method by @OoBook in https://github.com/unusualify/modularous/commit/1eebae241201c56fe2d1e789e5ebbc53b00a8f75
- comment out draft status filter for clarity by @OoBook in https://github.com/unusualify/modularous/commit/fb0f557f69ddfc36517fab157ba56535f6c03032
- update action colors and enhance navigation action merging by @OoBook in https://github.com/unusualify/modularous/commit/45bb574d2a10c8c67460c9986d348808df1429d2
- simplify price retrieval logic in afterSavePaymentTrait method by @OoBook in https://github.com/unusualify/modularous/commit/55e50e9c724398a382fcbced011418cfa8e79ef9
- update getMailMessage method signatures to include notifiable parameter by @OoBook in https://github.com/unusualify/modularous/commit/e539830977e3272945f5704cf0f4d6a403a37b9f
- update notification methods for improved clarity and structure by @OoBook in https://github.com/unusualify/modularous/commit/ed914d55b08a8ff127094f51fc6b9c4ce3d745f8
- streamline notification methods and enhance structure by @OoBook in https://github.com/unusualify/modularous/commit/43ed283743e507532cab7aa505a71a8129c22cf6
- enable editing on modal and comment out delete action by @OoBook in https://github.com/unusualify/modularous/commit/742127cb9b4df917c8d2651206ac859c1ea29844
- notify all superadmins on payment failure by @OoBook in https://github.com/unusualify/modularous/commit/8a84129cb3f84282714eec04d8398efaf67c58bd
- remove unused imports for cleaner code by @OoBook in https://github.com/unusualify/modularous/commit/1eb2304447e99e89cdcbc0acf7df5a8d4a8ac87a

### :lipstick: Styling

- format class definition for improved readability by @OoBook in https://github.com/unusualify/modularous/commit/265b8b49cb3024604e170dd82e36957663ffed23
- add PHPDoc comments for methods by @OoBook in https://github.com/unusualify/modularous/commit/de049c01660cb8ce806c2328c98c89cbf1a947cd
- center align "Go Back" button in modal options by @OoBook in https://github.com/unusualify/modularous/commit/ef4dcf6ccbc2f4cd7ce4a630cc48e462179fb74b
- lint coding styles for v0.30.0 by @OoBook in https://github.com/unusualify/modularous/commit/02ebf9ea6af267674192e115642cea1e5447d25f

### :white_check_mark: Testing

- remove country field from factories and tests by @OoBook in https://github.com/unusualify/modularous/commit/69dbbf28142f8a7859366e65537c547ec05df28e

### :package: Build

- update build artifacts for v0.30.0 by @OoBook in https://github.com/unusualify/modularous/commit/3f5734d21a1fd2641894a9eedf41614380ab11cc

### :beers: Other Stuff

- add success messages for authentication by @OoBook in https://github.com/unusualify/modularous/commit/569de4e8471f1f3fd3e675ddffe704d34b4b94d4
- add page title for verification success view by @OoBook in https://github.com/unusualify/modularous/commit/df74140a6cd8e0667b15759681a99184c399d65f
- comment out unused Apple sign-in button code by @OoBook in https://github.com/unusualify/modularous/commit/f71cc4bf42ec053ca44868234b09f65cb2e180ae
- add terms and conditions language strings by @OoBook in https://github.com/unusualify/modularous/commit/c60ad18cbc956db7b753bd7497b8d79c735b896d
- add full-stack and Laravel guidelines for development by @OoBook in https://github.com/unusualify/modularous/commit/8023fdc5e797955ce001a357d86cf7a9b502b2a8
- comment out unused endpoint logic by @OoBook in https://github.com/unusualify/modularous/commit/8565983c0e298dc9f4e5987b2033543df8e638d0
- update table row action definitions for payments by @OoBook in https://github.com/unusualify/modularous/commit/06de5212767ff0b7d3e94ada1b6f5a227dcf702d
- add allowedRoles and new related field to payment configuration by @OoBook in https://github.com/unusualify/modularous/commit/d2ef298e208752a328283ead6e062e1e8bc525f1

## v0.29.1 - 2025-05-12

### :wrench: Bug Fixes

- update method calls to retrieve table columns for improved accuracy by @OoBook in https://github.com/unusualify/modularous/commit/98385cff7bebb63e997f2753bb50e081967089f1
- simplify route URL generation by removing unnecessary parameters by @OoBook in https://github.com/unusualify/modularous/commit/84a92b193712b7562a694e19afc4bae45d4dc1df

### :green_heart: Workflow

- update manual-release.yml by @web-flow in https://github.com/unusualify/modularous/commit/e1b03b2374be74d00411101cdc07eeeb3a2d8cc1

## v0.29.0 - 2025-05-12

### :rocket: Features

- add password confirmation label for user input by @OoBook in https://github.com/unusualify/modularous/commit/f8a8408db4a1264ec472006f8801c4b2fcf565bd
- add success message for password saving by @OoBook in https://github.com/unusualify/modularous/commit/6e5add4d8acf25ddd85331a71ecededa1b1576c3
- enhance form component with button positioning by @OoBook in https://github.com/unusualify/modularous/commit/85ccb9456258db6d423e2b94ea58ec383039dfcc
- add options slot for enhanced form flexibility by @OoBook in https://github.com/unusualify/modularous/commit/b9cde09e8e542a73ef59bd70e45f239ed2ccbe09
- add 'not exists' condition for item checks by @OoBook in https://github.com/unusualify/modularous/commit/ee0c84ddb20c90432f789ed72168fdf56574a175
- add password generation notification functionality by @OoBook in https://github.com/unusualify/modularous/commit/ac109b58c0e37fe4abf024c9ce7c7ea504dc4ac0
- implement password reset functionality by @OoBook in https://github.com/unusualify/modularous/commit/3c94a6909cba607a00f8d51c4f6e0caed21b469f
- add email verification functionality by @OoBook in https://github.com/unusualify/modularous/commit/4961b2654f81a67106e96dedaef7eb2f09d78c0c
- implement custom email verification logic by @OoBook in https://github.com/unusualify/modularous/commit/b79f15a70a3765b100f372502e49ba768664573d
- enhance user profile editing with email verification by @OoBook in https://github.com/unusualify/modularous/commit/ec972b67e61d7bf937f5007ef756525db92a8eec
- add email verification and password generation routes by @OoBook in https://github.com/unusualify/modularous/commit/4380f6c0635293092fda0715949eef5749d9afd4
- implement user creation with password reset notification by @OoBook in https://github.com/unusualify/modularous/commit/d319cd53c7547965774ad3ffb5ca6fc4c8a98542
- enhance assignment scopes and add new methods by @OoBook in https://github.com/unusualify/modularous/commit/655089c351cd61520388d1378e7ff60c499d3b57
- add authorization usage check method and integrate Allowable trait by @OoBook in https://github.com/unusualify/modularous/commit/b3108d57320685b20e314238f1e0667acf55f860
- add new query scopes for assignment filtering by @OoBook in https://github.com/unusualify/modularous/commit/9d0477830c6b0346b55fe920ead923eaeee59e4c
- enhance assignment filtering and request handling by @OoBook in https://github.com/unusualify/modularous/commit/ea12e4394d52d655c738a3782a634d39228e15ab
- add new authorization and task-related translations by @OoBook in https://github.com/unusualify/modularous/commit/10ec80590914bd7098857261667fb1444f2ea57e
- enhance task-related translations for better clarity by @OoBook in https://github.com/unusualify/modularous/commit/0b39ca5c5c251c3622060f2d32f0065d4156b419
- enhance role-based assignment query logic by @OoBook in https://github.com/unusualify/modularous/commit/6b494fc4cd6c47f19df35c2d6a92bf2b0235e1cf
- enhance filtering capabilities for assignments by @OoBook in https://github.com/unusualify/modularous/commit/56e8f46bdbd333ddb9cbfbcbf2170327942b732e
- enhance filter interaction with active state indication by @OoBook in https://github.com/unusualify/modularous/commit/cac026d162c121c93efdda90f1226b77ec4ef30a
- add statusIconColor method for assignment status representation by @OoBook in https://github.com/unusualify/modularous/commit/5e77eb44b062cbe2c541d579fa84f9dbd90ef6d5
- add methods for active assigner name and assignment status representation by @OoBook in https://github.com/unusualify/modularous/commit/d9d8dfa704f03637fc771b71e66ce720d810b2fc
- enhance filter logic with role-based permissions by @OoBook in https://github.com/unusualify/modularous/commit/4fa627e09b765a4d1e52171ebb0c4e36b6a52a85
- integrate Allowable trait for role-based component visibility by @OoBook in https://github.com/unusualify/modularous/commit/f7e71d78297b9af1b7842fb5c740d6db03e4feb0
- add appendIcon and appendIconAttributes props for enhanced icon display by @OoBook in https://github.com/unusualify/modularous/commit/c2d929a120c19e2ceda6e562f78f7346e3e4ecd3
- enhance metrics component with new props and functionality by @OoBook in https://github.com/unusualify/modularous/commit/66d74b9cff4642a5a224b4672b8e48c1a0c5b229
- add MetricController for handling metrics requests by @OoBook in https://github.com/unusualify/modularous/commit/f4d9f9d86b3ed082e3cd1ae4182771e1ead613fe
- add date range filtering scopes by @OoBook in https://github.com/unusualify/modularous/commit/2e0ef9a742d10dcfc1ac920f31fee0da995eea38
- enhance event management with new methods by @OoBook in https://github.com/unusualify/modularous/commit/b0362270c87fde75ab3f8c4ee7483d8470c94717
- introduce MetricsWidget for enhanced metrics display by @OoBook in https://github.com/unusualify/modularous/commit/5d24ea58de82e60595a48b4ac407a7abe6b4d04e
- add process status scopes for Eloquent queries by @OoBook in https://github.com/unusualify/modularous/commit/951a1e2cce875868e950e6af7358d09e957a4327
- add process status query scopes by @OoBook in https://github.com/unusualify/modularous/commit/c4dfa408c06c03c596ad4730214b189877062044
- add query scopes for chat messages by @OoBook in https://github.com/unusualify/modularous/commit/04864dfcfa9e633f5ae53441fc6232e16819ed93
- add scope for chat messages awaiting reaction by @OoBook in https://github.com/unusualify/modularous/commit/40e23b757fbb1978becf2dcac68d06be2a687bfa
- integrate ChatMessageScopes for enhanced querying by @OoBook in https://github.com/unusualify/modularous/commit/c4f52495de9ebf6bc970e9bb63bf66b3cc47e69c
- integrate ProcessScopes for enhanced querying by @OoBook in https://github.com/unusualify/modularous/commit/0182d12efc517a144e2370924b9e19650aa1c8e8
- integrate ProcessableScopes for enhanced querying by @OoBook in https://github.com/unusualify/modularous/commit/02c8d7e2126a5d5976182a55aa1ef78f15c56b72
- enhance authorization handling with new user retrieval method by @OoBook in https://github.com/unusualify/modularous/commit/429fc8b0836022cd06dfe95d3fd99e7f8f4fb659
- add HasProcesses trait for managing process relationships by @OoBook in https://github.com/unusualify/modularous/commit/e9b0db0a21b7cc5b19e0a97ca71662ed27590952
- handle callable metric values in metrics processing by @OoBook in https://github.com/unusualify/modularous/commit/48d0a935ffc1e9cd635bed9c18611684b0452adb
- enhance user profile data handling in JavaScript by @OoBook in https://github.com/unusualify/modularous/commit/afb1143ff28330c0da5956279bea5735a0a3e431
- enhance modal component with new props and layout adjustments by @OoBook in https://github.com/unusualify/modularous/commit/c8d88f75290c6483714fb54f3fc5701133c40e3b
- implement global modal service for dialog management by @OoBook in https://github.com/unusualify/modularous/commit/e727b2dc79bae189f0aeac6f7c9475fce78a7f4d
- implement dynamic modal component for flexible dialog rendering by @OoBook in https://github.com/unusualify/modularous/commit/797afe4e76ee79b1a1aa7000a26e612da78456a7
- register DynamicModal and ModalService components by @OoBook in https://github.com/unusualify/modularous/commit/add12816806b244bcfd8365ce342a82d646a7f64
- add DynamicModal component to layout by @OoBook in https://github.com/unusualify/modularous/commit/dbc62d4f6c0c6fea4776a70dc39061f10ed4d442
- add useDynamicModal hook and update index.js exports by @OoBook in https://github.com/unusualify/modularous/commit/33254deaeb73302cab7ebee07824d69f6da0b9a4
- add success and error response handlers for modal service by @OoBook in https://github.com/unusualify/modularous/commit/1296bfbe3643cb72bc82e8af44c16bf1941fd4f6
- add isGuest state and getter for user module by @OoBook in https://github.com/unusualify/modularous/commit/8081697f2c9f360847d01009372e8a1e9736a5a9
- integrate response handlers for improved error management by @OoBook in https://github.com/unusualify/modularous/commit/568d9b9a667d5cfa676c20e65a8a82a1923729c4
- enhance login form with dynamic attributes by @OoBook in https://github.com/unusualify/modularous/commit/3cfe6d353015ae72df7480b07dd296a9a7c17b36
- create event class for user registration by @OoBook in https://github.com/unusualify/modularous/commit/883c3665c551fa0129dbbdf1df86cfcdd95faaa5
- trigger ModularousUserRegistered event on user registration by @OoBook in https://github.com/unusualify/modularous/commit/d54c694915032be6eea7a61e70fc1690c036896e
- add registration form fields and validation rules by @OoBook in https://github.com/unusualify/modularous/commit/2705cf9160d6d291f5102e9cec9f23ecb179e323
- add dynamic attributes for registration form by @OoBook in https://github.com/unusualify/modularous/commit/9be6a3d148f3876904508c6e3bee890d9ed2dbe0
- add FilepondAvatar component and hydrate class for file uploads by @OoBook in https://github.com/unusualify/modularous/commit/e5cbed2824c6daf1946c25c6a6f612623eabccdf
- add avatar field configuration for user profile by @OoBook in https://github.com/unusualify/modularous/commit/5563d11a2aa25991b5dd693b36e83fc7a61f3776
- add disabled prop to phone input component by @OoBook in https://github.com/unusualify/modularous/commit/5945895c7e0af76762d92bd76ae03eb3a97fc64f
- create Company entity extending ModularousCompany by @OoBook in https://github.com/unusualify/modularous/commit/3c02ebe59d684836dc52dd7931273258b10e8e08
- integrate SpreadableTrait into Company entity and repository by @OoBook in https://github.com/unusualify/modularous/commit/ccac192c5e95f4489e19e1341ebe18ac3c882181
- add removeQueryKeys function to manage URL query parameters by @OoBook in https://github.com/unusualify/modularous/commit/8a79345629cfaf0d27373d34d8ab763bd835c466
- implement state management for table parameters by @OoBook in https://github.com/unusualify/modularous/commit/06e447db1d1b8d5a9a7cd2e33888944337a07f23
- integrate useTableState for improved filter management by @OoBook in https://github.com/unusualify/modularous/commit/a5053dba57d7ae91c6794438e5d3f056f7daefbd
- enhance URL generation for table actions with query parameters by @OoBook in https://github.com/unusualify/modularous/commit/11988d6cb979145a3d1b2f425206cb93ab457ab3
- add disabled prop to FilepondAvatar component by @OoBook in https://github.com/unusualify/modularous/commit/7384b85fc0cfe361022926b246b52d80af64b528
- add file upload and checkbox inputs for user configuration by @OoBook in https://github.com/unusualify/modularous/commit/ad074d4c38f3aec787dae27128cef084738c8ded
- enhance user configuration fields and labels by @OoBook in https://github.com/unusualify/modularous/commit/c87e70dd5fa7b496dc71babb98721eb087ece2db
- enhance validation rules and messages for company data by @OoBook in https://github.com/unusualify/modularous/commit/7cb7ad90f866eeb7625b95c6e7ea9b6a03bc36d3
- add protectInitialValue prop to input properties by @OoBook in https://github.com/unusualify/modularous/commit/7fb4101745e2d6e8f305303aca9535b09f0dbe42
- add protectedLastStepModel and protectInitialValue prop by @OoBook in https://github.com/unusualify/modularous/commit/512c0a0b9e62af272b79ef606908eaa56fcdc499
- add readonly functionality based on protectInitialValue by @OoBook in https://github.com/unusualify/modularous/commit/edee176bf604f0138037730ea7f0663dc040fbb9
- update button label to use translation and add readonly prop by @OoBook in https://github.com/unusualify/modularous/commit/a1fdc9af52063aef5e806edc1efbcbd9dafcd8ed
- add gutter support and improve class handling by @OoBook in https://github.com/unusualify/modularous/commit/9ccb8988d0b1da30c34a0bcb0d5fa1819d330e17
- implement Price route to SystemPricing module by @OoBook in https://github.com/unusualify/modularous/commit/43ea25ae37330a4b7b2787f987d7ff5b44f38fd8
- add new payment service and payment labels by @OoBook in https://github.com/unusualify/modularous/commit/80826d2551424201b63fded90cb320c544957d08
- add support for unformatted JSON responses with pagination options by @OoBook in https://github.com/unusualify/modularous/commit/bbdabbb063dfe9fb8fde1d8904fe3d5dd48bf8b7
- add invoice file attribute for enhanced payment details by @OoBook in https://github.com/unusualify/modularous/commit/26892015c85e05ed1cd58423ddde9c47ad373602
- add methods to check trait input availability by @OoBook in https://github.com/unusualify/modularous/commit/eaffabdcd6b8102337a10e6e313125e5853d8a27
- enhance slot functionality for action components by @OoBook in https://github.com/unusualify/modularous/commit/9325eb0400839948fc016c314bbda0d2e3c53052
- integrate HasFileponds trait and enhance morph relations by @OoBook in https://github.com/unusualify/modularous/commit/02b8526d7c329923bdb022d9983459352277444f
- add attachment handling for assignments by @OoBook in https://github.com/unusualify/modularous/commit/21493318077e91c3ed720ee22c86b741ee98a3c9
- enhance file attachment handling in hydrate method by @OoBook in https://github.com/unusualify/modularous/commit/0470e85cc8fed5fa60b078b47fd21f4bf13cea42
- update assignment component and endpoints for improved functionality by @OoBook in https://github.com/unusualify/modularous/commit/aeba14af114093153095fc3bdedff128f0554d84
- add updateInput method for enhanced input handling by @OoBook in https://github.com/unusualify/modularous/commit/783291862925219d36fcf5c4ce1a7c9415c9e72a
- add new hook for fetching input data with pagination and search capabilities by @OoBook in https://github.com/unusualify/modularous/commit/33ae30a5f8f297cafbd3bdd1d08c4056a460a1ac
- add new alert hook for managing alerts in the store by @OoBook in https://github.com/unusualify/modularous/commit/2dc15ea2e182f8c4e46817aee0aa0dd0a3fa77cd
- add 'update:input' event to input emits for enhanced input handling by @OoBook in https://github.com/unusualify/modularous/commit/fe7c71bf005f8e8408947a8569611bba891cbc6c
- add props for title divider and body padding control by @OoBook in https://github.com/unusualify/modularous/commit/d4560b8182a12a296dbf0065be4e4b2f17a801c0
- enhance input component with fetch capabilities and slot support by @OoBook in https://github.com/unusualify/modularous/commit/92aac9725f403d1b34fe57f17b778eb1ee4d2987
- add final form subtitle prop for enhanced form customization by @OoBook in https://github.com/unusualify/modularous/commit/9215b611d55d314c704a33f7386b028e66b2a1f4
- integrate connector functionality for dynamic action handling by @OoBook in https://github.com/unusualify/modularous/commit/7c2d5b7ae58ed5fa1149056cc89247f529f11ba2
- add conditional rendering for cancel and confirm buttons by @OoBook in https://github.com/unusualify/modularous/commit/d8892f46cb7c898d54c1e784d134810c12f4a820
- enhance slot rendering for dynamic content by @OoBook in https://github.com/unusualify/modularous/commit/4ce8c221d528b9da817c216e35c82333cb329c6d
- implement URL parameter handling for modal opening by @OoBook in https://github.com/unusualify/modularous/commit/df20744c867cfa9e84389fca0df5c6bbdc4df222
- add functions to remove URL parameters and update history state by @OoBook in https://github.com/unusualify/modularous/commit/7cac2e36c1be9c4b75827b2857c4ebeb1928b1b3
- add default action handler for item actions by @OoBook in https://github.com/unusualify/modularous/commit/fccfab36777fe15eac2269999a80dc713caab2d1
- add default description for action confirmation prompts by @OoBook in https://github.com/unusualify/modularous/commit/09f2130e865047d8ac01173a42dd5a6837fd4bba
- add subtitle support and improve layout for final form display by @OoBook in https://github.com/unusualify/modularous/commit/eb5eb9e679af99eccbc9051d09f58f348e409d3a
- enhance navigation actions retrieval with custom actions support by @OoBook in https://github.com/unusualify/modularous/commit/cd2628c96080ff09430631020ded885a2338cd43
- enhance table row actions with permission checks and route resolution by @OoBook in https://github.com/unusualify/modularous/commit/50b2a4569c85052d45e4fe2eaf662a51520154f9
- include exchange rate in conversion response by @OoBook in https://github.com/unusualify/modularous/commit/d6f3988dc717d70939192f25cecfaa482582e27f
- filter routes based on front route availability by @OoBook in https://github.com/unusualify/modularous/commit/76c32451969b1dbdf014b87524cf3eb3787f2cb2
- add replicating method to handle price attribute removal by @OoBook in https://github.com/unusualify/modularous/commit/38ed82e88d234671f24981924ac29651d3086dfd
- add payment_service_id column to unfy_currencies table by @OoBook in https://github.com/unusualify/modularous/commit/d3118630f50ffb511e2e2bb8d8bfaf7fc7d3c660
- enhance PaymentCurrency model with new relationships and fillable properties by @OoBook in https://github.com/unusualify/modularous/commit/367b1b01ffbd7a978d107c02bad58ec436f64f86
- enhance PaymentService model with new attributes and relationships by @OoBook in https://github.com/unusualify/modularous/commit/2402a0644cb185f60493ae875d60c9b31b3f900d
- update payment services configuration and add new services by @OoBook in https://github.com/unusualify/modularous/commit/14f4c80bc171ae53cec70ee6897dc2c303642dbd
- prepare payment_currency_payment_service table for future seeding by @OoBook in https://github.com/unusualify/modularous/commit/79f73f79dad5afd7e8f88c00bdc7feec1ad7e0f6
- enhance payment processing and response handling by @OoBook in https://github.com/unusualify/modularous/commit/58c2c185c2a23d3fb19ccc196e4c4cb9fdd48b8a
- add computed property for assignment presence and improve avatar handling by @OoBook in https://github.com/unusualify/modularous/commit/d361f5858620788a02646143edb16ad84426d7cf

### :wrench: Bug Fixes

- change orWhereHas to whereHas for translation filtering by @OoBook in https://github.com/unusualify/modularous/commit/8938bce8810439365e9a5aa89354f32cca147f5c
- update assignment status handling with enums by @OoBook in https://github.com/unusualify/modularous/commit/1ad6883a1a462c9434b54381ef491a32f19d6152
- update validation rules for user locale input by @OoBook in https://github.com/unusualify/modularous/commit/5d5cabbb7eec16bacc629a953bb29718b3cc65b8
- update assignment query methods for role-based checks by @OoBook in https://github.com/unusualify/modularous/commit/20b404b45ad78a68eb104a8f57ff4ac4bb498b5a
- restore and enhance team-pending-assignments filter by @OoBook in https://github.com/unusualify/modularous/commit/4ab60980e6e62cfb435631988df990d854245018
- update condition for table row actions to use total price by @OoBook in https://github.com/unusualify/modularous/commit/050639f85c3e680bab7c9e2a1348ecdef9e15fe7
- update price calculation to use total price instead of price including VAT by @OoBook in https://github.com/unusualify/modularous/commit/85c441c4544cce534958f103dc780a8d49807dcd
- ensure safe merging of 'with' relationships in list method by @OoBook in https://github.com/unusualify/modularous/commit/77a4a7b29d99c2c02dec91d3eb90dbea3d7c0d51
- rename and enhance lastChatMessage method for improved querying by @OoBook in https://github.com/unusualify/modularous/commit/0052e2a17bdc714c231af35743484609ebeb92df
- update column configuration for responsive design by @OoBook in https://github.com/unusualify/modularous/commit/f7d632a2d1f2cc2c225da09da799bbf6b70b6b05
- enhance guest navigation profile menu handling by @OoBook in https://github.com/unusualify/modularous/commit/89d58696e7e6c014b338dd53d0153eec6cdf2562
- update module route registration with domain configuration by @OoBook in https://github.com/unusualify/modularous/commit/5a49443e20042e51c704beadb5d3a7f79dcfbbc8
- improve request handling and data retrieval by @OoBook in https://github.com/unusualify/modularous/commit/8e4dd2334c2b7e8e8f27dcb0f9be39fc1dd3829e
- enhance modal body rendering logic by @OoBook in https://github.com/unusualify/modularous/commit/929935b042fc332286de7581431e3647d683043f
- correct unique validation rule for email field by @OoBook in https://github.com/unusualify/modularous/commit/bfa604f2aaab84e7f2b0a5e06bc4932e0124d343
- update controller imports for consistency by @OoBook in https://github.com/unusualify/modularous/commit/0b20c4afbd96010a28559dac0a3b26577deb7960
- improve locale handling in getFormFieldsFilepondsTrait method by @OoBook in https://github.com/unusualify/modularous/commit/a9f072e6e555268807c82ee6b09270ecd5dfc0b9
- improve spreadable creation and update logic by @OoBook in https://github.com/unusualify/modularous/commit/8e85360a33de6188441cf6112ee550ff91d39a4d
- improve filter handling in getRequestFilters method by @OoBook in https://github.com/unusualify/modularous/commit/f91e87c49029ed51c131f1715f432b8de514a3a0
- improve column handling and uniqueness in list method by @OoBook in https://github.com/unusualify/modularous/commit/d73f7be252aa8057a5978a0c304ffd8cc9d1cf08
- improve file information retrieval and storage path handling by @OoBook in https://github.com/unusualify/modularous/commit/7d36b42f250a2577ee24ed0d319c958f46508d75
- add 'spreadable' attribute to form draft configuration by @OoBook in https://github.com/unusualify/modularous/commit/faf64996dd60ec93a4300ac749606403e057e275
- import inject from Vue for improved modal functionality by @OoBook in https://github.com/unusualify/modularous/commit/e001222dabf91823636b042d7c7d1f58223f72b0
- conditionally render modal body description based on component state by @OoBook in https://github.com/unusualify/modularous/commit/2266603a9811dbaa4739c252ea4d387dc773d892
- enhance route parameter handling for objects and associative arrays by @OoBook in https://github.com/unusualify/modularous/commit/82e465200f231432a214f511a0bb132280706291
- adjust price saving value calculation for accurate representation by @OoBook in https://github.com/unusualify/modularous/commit/afbbdeea6b77dec5d20b69c9c40693e35243a59d
- update reference to main instance in utility methods by @OoBook in https://github.com/unusualify/modularous/commit/7345ff04717be3e026c04b1b6bd63882b3b4fed9
- refine 401 error handling for unauthenticated responses by @OoBook in https://github.com/unusualify/modularous/commit/d143fae672b5b402c04a592343c3dc87b27cdb8c
- update button text for clarity by @OoBook in https://github.com/unusualify/modularous/commit/6965e7bb457ec698b5595dd4352b92818336f98e
- improve previous route matching with enhanced error handling by @OoBook in https://github.com/unusualify/modularous/commit/78a51e064f59d3e7e0416d4054f57825d30aa1e1
- handle division by zero in VAT percentage calculation by @OoBook in https://github.com/unusualify/modularous/commit/04740cdb3edae63a4c50fd528eeed54989f679c8
- correct return statement formatting for modelValue handling by @OoBook in https://github.com/unusualify/modularous/commit/3420797ff34e293502688ca728fcafcb3f34b6df

### :recycle: Refactors

- update validation rules and add reset password form by @OoBook in https://github.com/unusualify/modularous/commit/6b001072a4150ad8d39a5df63cc224ea5dca83bc
- update validation rules for user creation and update by @OoBook in https://github.com/unusualify/modularous/commit/2742d21411c75afd28dc3496922f654e590e8b8c
- enhance layout flexibility with conditional rendering by @OoBook in https://github.com/unusualify/modularous/commit/b5664eaf4b7bddeff6f97229869057eeb6dee8af
- update prop type to support multiple data types by @OoBook in https://github.com/unusualify/modularous/commit/731764a378cbc6507d8fa5103fe636b8b61f98e8
- improve children element handling in addChildren method by @OoBook in https://github.com/unusualify/modularous/commit/0c2265afb8e5f7fdde8f50bd9f0538f56bee4979
- enhance grid section creation with improved attribute handling by @OoBook in https://github.com/unusualify/modularous/commit/95a90fe0673eab91afaf24f53b43476ed48d27fc
- streamline price calculation logic by @OoBook in https://github.com/unusualify/modularous/commit/7170ba84cbaab1a9226440a87f0923293cce7e6a
- enhance profile layout with additional class by @OoBook in https://github.com/unusualify/modularous/commit/16a2be684a0f3570a8e0da9b62f549adff1d3f8d
- simplify assignment status handling in createAssignment method by @OoBook in https://github.com/unusualify/modularous/commit/71f9e7f902cb3daaeb7cc7ad3f97ace5a0369f65
- streamline login handling and response structure by @OoBook in https://github.com/unusualify/modularous/commit/ddd3ca6e5fc7d46cd5dec25917a731697151381a
- move dashboard route in bottom line by @OoBook in https://github.com/unusualify/modularous/commit/a2e9cb18c214d61b754c5515d9ba3780b5061e07
- enhance user input configuration with additional fields by @OoBook in https://github.com/unusualify/modularous/commit/95f24e2f9f54f99efb7a60d063a9255a96eaa197
- enhance user model with company handling and email verification by @OoBook in https://github.com/unusualify/modularous/commit/6af0a1de0f43a1029d63709e340c76d16d4cf337
- streamline reset password form handling by @OoBook in https://github.com/unusualify/modularous/commit/909734cd9ca2c4ee04cd452ee899efe4f9ee0caa
- comment out hasAuthorization scope setting by @OoBook in https://github.com/unusualify/modularous/commit/8f2d97a9893ad35b599a80f5d7a51c04e91c7ded
- update scope key for assignment filtering by @OoBook in https://github.com/unusualify/modularous/commit/d79e2d70345aa064be2f0ea1fb7c5866bd12b872
- update table configuration and attributes by @OoBook in https://github.com/unusualify/modularous/commit/bc8757569f903b68d140cb88445822c2b716ee90
- simplify button class configuration by @OoBook in https://github.com/unusualify/modularous/commit/662f70c031c2ba78df0cba30f145b691e8bf25be
- add forgot password form schema and update controller by @OoBook in https://github.com/unusualify/modularous/commit/5046f3cd13b5d25b5fa1aea04d14c433092a9365
- update form attributes and button configurations by @OoBook in https://github.com/unusualify/modularous/commit/6c35aa41ba70169fe3aab078d52185636060255b
- update button attributes for improved styling by @OoBook in https://github.com/unusualify/modularous/commit/bbef4c0ac9b017b4439831f30849735bb72b6bbc
- update reset password form attributes by @OoBook in https://github.com/unusualify/modularous/commit/24e63594dced3857f677e604b5123f674435b67f
- introduce assignable scopes for enhanced query capabilities by @OoBook in https://github.com/unusualify/modularous/commit/bcb00a70ee7d719e05f21a5190ff0eb92ca1a035
- modularize assignment query scopes by @OoBook in https://github.com/unusualify/modularous/commit/219921be7e0546740bf4d886a4244937065976e4
- streamline user assignment checks and enhance query scopes by @OoBook in https://github.com/unusualify/modularous/commit/7e620bb391280af85d00f6a5984f34013e8f7c08
- streamline event handling logic by @OoBook in https://github.com/unusualify/modularous/commit/fcef063a2b6ff8a674d634ca3db37be86ab528da
- update HasChatable as Chatable by @OoBook in https://github.com/unusualify/modularous/commit/80fbd8d4acd71ca6451f8e3d0dd094475d66a542
- update `scopeHasUnreadChatMessages` to utilize the `unread` method for better readability. by @OoBook in https://github.com/unusualify/modularous/commit/3a0876326c11c582a08f4c2432a3cf68f1bd9e11
- streamline title component props and styles by @OoBook in https://github.com/unusualify/modularous/commit/32c9850ba42a11dd2e21eb98098218ad1ae87f65
- update class binding for form component by @OoBook in https://github.com/unusualify/modularous/commit/8c161873a8187e9e2d1f758fc69f5fe9c3e14f81
- add Filepond hook and props factory by @OoBook in https://github.com/unusualify/modularous/commit/c367092d47e9d9a93a33a13e7f90ec340abff28c
- enhance file upload component with improved class binding and slot handling by @OoBook in https://github.com/unusualify/modularous/commit/6b7f7f8127d0a0070af77999b20df500f1e09a4e
- rename methods for consistency and enhance save logic by @OoBook in https://github.com/unusualify/modularous/commit/684a97de7265479ef2e2388ac70ec43b9c161f01
- update logout modal design and text by @OoBook in https://github.com/unusualify/modularous/commit/1b105ad829bfc8c8eec9d7be7a2399a67c85d22b
- update modal close button logic by @OoBook in https://github.com/unusualify/modularous/commit/5159668833fb377a188a688d7a92b010e6a5d396
- update default values for banner and button text by @OoBook in https://github.com/unusualify/modularous/commit/060ad0b8abc30c21e246ba2a0597b5be27378942
- comment out company validation logic by @OoBook in https://github.com/unusualify/modularous/commit/c21546c4847c43adafad8b9679a6add4a3b5e434
- reorganize fields and update labels for clarity by @OoBook in https://github.com/unusualify/modularous/commit/4cc412a48010bafb71d4a5b0f9a76b9b03b8f930
- update profile editing functionality and improve form structure by @OoBook in https://github.com/unusualify/modularous/commit/69129da7a03eed9165164391ba6f86452ddd3d8a
- enhance form error handling and schema management by @OoBook in https://github.com/unusualify/modularous/commit/22f0d166b76480b86638380e911651ce2aaa0a3a
- update user table references for consistency by @OoBook in https://github.com/unusualify/modularous/commit/6f5384509843b43df3c8776df53edd962579aa60
- replace transition with VExpandTransition and simplify height management by @OoBook in https://github.com/unusualify/modularous/commit/927b6a03bb88acd94ab06af4a8356f0fd94a58e4
- rename metric-card class to ue-metric and adjust padding by @OoBook in https://github.com/unusualify/modularous/commit/0e3798c49e8958b058596a7624e973f6b9ba2252
- enhance relationship handling for nested keys by @OoBook in https://github.com/unusualify/modularous/commit/9acf5a9b43a32866b5b56b92d2f26818aad5daff
- rename raw_price to raw_amount and update related logic by @OoBook in https://github.com/unusualify/modularous/commit/8d08925d9112c1baa379210e953858d35fcffc81
- update table configuration and input fields by @OoBook in https://github.com/unusualify/modularous/commit/921b9b66aafe181b1d71070af2075651f93f40a7
- enhance Payment and PaymentRepository with Fileponds support by @OoBook in https://github.com/unusualify/modularous/commit/bc36711721a2c251bcc715f506f4970cc04d9b69
- enhance URL handling functions by @OoBook in https://github.com/unusualify/modularous/commit/0b1bbcf62808902d083c6bc3024463353fef18e9
- enhance select component with multiple selection and loading state by @OoBook in https://github.com/unusualify/modularous/commit/5014dcf580030721d9af85498b973e670ceb0d89
- remove unused methods and clean up code by @OoBook in https://github.com/unusualify/modularous/commit/3401ef3a5db846fca1984a6b843865172139dc1f
- enhance endpoint resolution in setDefaults method by @OoBook in https://github.com/unusualify/modularous/commit/a85b0ea6ba1ca442b34c313e0329b2f074ebc65a
- enhance list method with pagination support by @OoBook in https://github.com/unusualify/modularous/commit/ffec89fb8e169025dac2a114ee3c4d8a3b7222a4
- add resolve_route function for dynamic URL generation by @OoBook in https://github.com/unusualify/modularous/commit/6486ff312ec52e0ae119d281296c0f157906172c
- enhance input handling and add itemsPerPage property by @OoBook in https://github.com/unusualify/modularous/commit/ffc4bcfd2fcfc511d7117277bab678a681693cf2
- simplify URL generation for table actions by @OoBook in https://github.com/unusualify/modularous/commit/cb2149b757f492fffaa3221d00705b2dbe09035c
- optimize payment price retrieval methods by @OoBook in https://github.com/unusualify/modularous/commit/200fe69ef425ac155735a801dbc9f05109364080
- update roles input configuration for enhanced functionality by @OoBook in https://github.com/unusualify/modularous/commit/7c62553d929176a4ebe4b27410fa6115b448c23b
- simplify payment field retrieval by @OoBook in https://github.com/unusualify/modularous/commit/5a871e43eb0cd31fed3a0ad689b9f60c0c24e960
- remove debug response from register method by @OoBook in https://github.com/unusualify/modularous/commit/7bfcd81e82bfc34562fb082000cb95b373b6811e
- update invoice file input configuration by @OoBook in https://github.com/unusualify/modularous/commit/c685e2e43cd382ed164472ea958f767122d91c04
- optimize price calculation logic by @OoBook in https://github.com/unusualify/modularous/commit/6b6b1c45ef5345c28c8cd0f0bc95ee35a0457324
- enhance file input configuration with new properties by @OoBook in https://github.com/unusualify/modularous/commit/8a95e24656d2273dc80d2b485c0ef0e71876f448
- enhance file information retrieval and component integration by @OoBook in https://github.com/unusualify/modularous/commit/a1b6bcec25199671efa106f14d4026d0062ee7fc
- standardize property naming conventions by @OoBook in https://github.com/unusualify/modularous/commit/6a481adbe7a842e0323c86666f20789bad2e7399
- simplify class structure and remove unused methods by @OoBook in https://github.com/unusualify/modularous/commit/c02ddbf36bcdc846b0beade9387ca4d42408a103
- update property names and logic for improved clarity by @OoBook in https://github.com/unusualify/modularous/commit/e307b516f8b63f1d609eecc52a80a8ef70afeb6d
- add HasFileponds trait for enhanced file handling by @OoBook in https://github.com/unusualify/modularous/commit/4ecff56fb84b7fe1f522e20a5594381cb8e486ce
- update header logic for improved clarity by @OoBook in https://github.com/unusualify/modularous/commit/d1590daa8dbaf29c49bab4d4ca69128241d795b8
- remove unused afterDelete method and update default input handling by @OoBook in https://github.com/unusualify/modularous/commit/3fd2e40a1ff7cd469367d7225f00d3cf2b02262e
- enhance spreadable trait functionality and attribute handling by @OoBook in https://github.com/unusualify/modularous/commit/87772a8b82dac0d13ed8d73edf7a5cbf0b2edf2f
- add column configuration and spreadable saving key by @OoBook in https://github.com/unusualify/modularous/commit/7a3e665f6451e2ef049877dc80a49f32799300bd
- enhance form layout and structure in modal by @OoBook in https://github.com/unusualify/modularous/commit/ec652d55c5da027121c23610feefd83293c98f8d
- update repeater input handling and schema processing by @OoBook in https://github.com/unusualify/modularous/commit/7ef472861d3ec8f654702fc8555ae11b180716c0
- move script to script 'setup' by @OoBook in https://github.com/unusualify/modularous/commit/648d49f054c2a9b4bf1da0a3a344b4fe6648cfb3
- enhance localization and streamline component structure by @OoBook in https://github.com/unusualify/modularous/commit/b5c43ebf2059b1027ad190303b586b912b2ffab5
- streamline sidebar layout and improve user profile display by @OoBook in https://github.com/unusualify/modularous/commit/47a8bef87237f9922fcd40a7cdcb47ee1063ae68
- update payment handling logic and improve code clarity by @OoBook in https://github.com/unusualify/modularous/commit/21dc3fe74da7eab5dd4bb1a87712182547b9892d
- streamline URL query merging and add array to query string conversion by @OoBook in https://github.com/unusualify/modularous/commit/69284acfdf337c2b85531d11eb000fb55bfabf8c
- update column retrieval methods for consistency by @OoBook in https://github.com/unusualify/modularous/commit/fa07477c2c74c3fe50db22457d714b5c6b653c26
- enhance media retrieval with locale support by @OoBook in https://github.com/unusualify/modularous/commit/11ac36572166d530e078a9b2da95bac2f1042329
- optimize data retrieval and enhance currency handling by @OoBook in https://github.com/unusualify/modularous/commit/b90ac2198e763bfca939173c2c9c620a63918230
- update config access method for improved clarity by @OoBook in https://github.com/unusualify/modularous/commit/ae63bdba1d0b6f08896908767a6817e86890557c
- enhance file handling logic for nested structures by @OoBook in https://github.com/unusualify/modularous/commit/7a4fbcd98115c3e9bb350b8cab7528ebbedf860f
- enhance payment service configuration and add action buttons by @OoBook in https://github.com/unusualify/modularous/commit/a20f01abb56c2745361aff7d7d7bada28cf1d67d
- update payment services table structure for improved uniqueness and defaults by @OoBook in https://github.com/unusualify/modularous/commit/b5e15d3a8ec381a17887faa71f34f56c77d90260
- update currency handling and add foreign key constraints by @OoBook in https://github.com/unusualify/modularous/commit/cd8a09418fae442a13ebe3f91bce7dc62398073b
- update payments table structure for improved clarity and defaults by @OoBook in https://github.com/unusualify/modularous/commit/ef9e5a6b0f9f466127ce26ca739d85d82b4d41b0
- enhance Payment model structure and formatting by @OoBook in https://github.com/unusualify/modularous/commit/597bb798721f95f5d2508480445ec22ac7b928dd
- simplify template structure and enhance layout by @OoBook in https://github.com/unusualify/modularous/commit/6d97586851f016a81f78946fd459dd04c1ea443c

### :lipstick: Styling

- remove unnecessary console log from created lifecycle hook by @OoBook in https://github.com/unusualify/modularous/commit/dd7994c52708f955214b997eaa0529e5ab637cc8
- streamline SCSS structure for input assignment component by @OoBook in https://github.com/unusualify/modularous/commit/b6eeb4c059555184dcc9654312cf35a09888e870
- update button class for improved text styling by @OoBook in https://github.com/unusualify/modularous/commit/e9a3744ed24a85ebf729721ccb9e1a0a111c0184
- lint coding styles for v0.29.0 by @OoBook in https://github.com/unusualify/modularous/commit/e5127f8fefcfb5d887736ab3fc9dc2e1026de864

### :white_check_mark: Testing

- remove deprecated test_valid_company method by @OoBook in https://github.com/unusualify/modularous/commit/fe5bc31168a8b50c302885ec43af71ecd06cb8b4
- update company test to include spread payload and personal attribute by @OoBook in https://github.com/unusualify/modularous/commit/11cd0f5604fb67e4f09b3d7753ef56a3eb097f2f
- update endpoint naming and enhance modal test coverage by @OoBook in https://github.com/unusualify/modularous/commit/49843db3f7a9dcdf46e2b1a34d5741b97f76f0c8

### :package: Build

- update build artifacts for v0.29.0 by @OoBook in https://github.com/unusualify/modularous/commit/90d04e52330b9b94719d6b935d941acb6014edfe

### :beers: Other Stuff

- add verification messages for email confirmation by @OoBook in https://github.com/unusualify/modularous/commit/8cfe0fd12da124c4089e99b956242d54823b02f6
- enhance filter organization and clarity by @OoBook in https://github.com/unusualify/modularous/commit/80910f71a641005744e3407ba70440e4b2f74d8f
- add new comments by @OoBook in https://github.com/unusualify/modularous/commit/e80e1b97d646864b8cabcf4990517faa257ca824
- add Turkish translations for authorization and assignment terms by @OoBook in https://github.com/unusualify/modularous/commit/5ec4a82c8c7e8b444c9fe75c99c23a57545269c4
- comment out unused ue-form component for future reference by @OoBook in https://github.com/unusualify/modularous/commit/b394c84127c107d8df4341ca0505fb3d6c0d5bf5
- add Company entity import for user management by @OoBook in https://github.com/unusualify/modularous/commit/2ec40efd615c22fac83b514f666a74b71e2f6cb9
- enhance lastStatusAssignment scope with date filtering by @OoBook in https://github.com/unusualify/modularous/commit/564eb806f29a57eef61acdb41677f0b76903ffb8
- add subtitle prop to Metrics component for enhanced display options by @OoBook in https://github.com/unusualify/modularous/commit/9a65fc2abd43560ae862ac3d54c492201f5b79be
- remove console logs and commented code across components by @OoBook in https://github.com/unusualify/modularous/commit/b554ae2b764dd31a557031baeac0af4d9dfe9122
- remove console log for improved code clarity by @OoBook in https://github.com/unusualify/modularous/commit/19cd4928ada068fb1ee217d1218b6bfd4e0eb673

## v0.27.0 - 2025-02-24

### :rocket: Features

- change base module structure in order to make compatible modules foldering by @OoBook in https://github.com/unusualify/modularous/commit/f0f259698d3646578a82b61d609926b0b64ba9cd
- add configurable cache driver for module caching by @OoBook in https://github.com/unusualify/modularous/commit/d1d62f9e4eaeb375223fbb7cff2b1a4d25dfcf08
- add translation caching and configuration methods by @OoBook in https://github.com/unusualify/modularous/commit/861a483826991d802424cb47ad879b6a5a5f04f4
- enhance authentication redirect handling by @OoBook in https://github.com/unusualify/modularous/commit/08e3fd5df17732f3bb1207b5dea831381a8636c4
- update payments table migration operation by @OoBook in https://github.com/unusualify/modularous/commit/53cca61788b4c24bdb85e00d2ec83b5ac6a28222
- add development environment detection and vendor path methods by @OoBook in https://github.com/unusualify/modularous/commit/682b051bd28136f5a0102911626dd1ca7d4d6a16
- enhance CreateOperationCommand with flexible operation generation by @OoBook in https://github.com/unusualify/modularous/commit/458ca6fbca686f5246a1fe01d3f5b1547809e027
- add ProcessOperationsCommand for flexible Modularous operation processing by @OoBook in https://github.com/unusualify/modularous/commit/a8f4d92fb885c959afb7135c1d6d76a3064b8d8d
- add new helper functions for string formatting and code documentation by @OoBook in https://github.com/unusualify/modularous/commit/78bd09fdf33564f26f2003145c8244f537ff4203
- add Horizon configuration and layout files for job monitoring by @OoBook in https://github.com/unusualify/modularous/commit/9466c10015b9b566d4be46daf4711aaa75863fc2
- add Telescope configuration, migration, and layout files for enhanced monitoring by @OoBook in https://github.com/unusualify/modularous/commit/1c518cb25eee8ee77751044c4f5ca48c07a3c279
- add maintenance mode view for user notifications by @OoBook in https://github.com/unusualify/modularous/commit/abd60841e783dc6633ed93ed5546b3cc2ec28108
- add CleanTemporaryFilepondsScheduler command for managing temporary fileponds by @OoBook in https://github.com/unusualify/modularous/commit/cf1892838288bce9e095f5d442c19b56b22ef6a2
- add Composer root name to environment variables in CacheVersionsCommand by @OoBook in https://github.com/unusualify/modularous/commit/42c4f1c6686753fe9521fa8219401cfb8721a7ac
- enhance CreateConsoleCommand to include formatted signature documentation by @OoBook in https://github.com/unusualify/modularous/commit/cee9caaacdd5962ab945f6acd4140801bf5262ec
- refactor BaseCommand to implement PromptsForMissingInput and add namespace handling methods by @OoBook in https://github.com/unusualify/modularous/commit/07b67de264c195c4b3be0cee2c052bf50700f311
- add CreateHorizonSupervisorCommand and supervisor.stub for Horizon configuration by @OoBook in https://github.com/unusualify/modularous/commit/2f5aab8beb9a6f08707c20e4ab4af0a9564060c8
- add 'no-plain' option to ModuleMakeCommand for route creation control by @OoBook in https://github.com/unusualify/modularous/commit/b61b8f601365578ba2b7186e597d263011a0eb6f
- add EventMakeCommand and event.stub for creating Laravel events by @OoBook in https://github.com/unusualify/modularous/commit/f9d4e6abacbc80bfbd518a5247a2189cbfba2c12
- add ListenerMakeCommand and listener.stub for creating Laravel listeners by @OoBook in https://github.com/unusualify/modularous/commit/3bd3f7dd2deddd68cd9fbb95e6a054e22a139b85
- refactor ModuleServiceProvider to register module providers and streamline middleware handling by @OoBook in https://github.com/unusualify/modularous/commit/08c6e3386970bf6798488fff089b21e00217d5d1
- add broadcasting channels for modular event handling by @OoBook in https://github.com/unusualify/modularous/commit/40a7200ddbe342bfbebba8e55cc62e16c8a341d5
- enhance BaseServiceProvider with scheduled commands and modularous improvements by @OoBook in https://github.com/unusualify/modularous/commit/842ba531263352b57b9b7c35a449aadf60bae4a2
- update LaravelServiceProvider to enhance asset and config publishing by @OoBook in https://github.com/unusualify/modularous/commit/955691fff61fb9ab8f2314c3336dffe622b62bab
- add migration for notifications table by @OoBook in https://github.com/unusualify/modularous/commit/37fc3554be9b603d8005cb8d4e5960e5cc99de87
- add laravel-echo and pusher-js dependencies to enhance real-time event broadcasting by @OoBook in https://github.com/unusualify/modularous/commit/2323de4a719c248a3ce764764b342d2a2b77d474
- implement broadcasting plugin for real-time event handling by @OoBook in https://github.com/unusualify/modularous/commit/757255d2ef1cbcec3f4fbae821ab9f52a1f2f863
- add ModularousSystemPathException for production environment protection by @OoBook in https://github.com/unusualify/modularous/commit/937a12b99d91e62cf8d350a424bfb4ccd86a6592
- add methods to dynamically manage system modules path by @OoBook in https://github.com/unusualify/modularous/commit/5ce56e4b8be24a0423fc58a4d524393cf3fa5609
- enhance modularousTraitOptions() with signature generation support by @OoBook in https://github.com/unusualify/modularous/commit/fd503f16a4c3f234d214edc35b01b963d0d2ed6b
- add isModularousModule method to Module class by @OoBook in https://github.com/unusualify/modularous/commit/7b82534b352de9a80e74ccf25e5bfc66aeee38f0
- add system group configuration for Modularous modules by @OoBook in https://github.com/unusualify/modularous/commit/0f91a812c7a7ffee70a9d3965794819905aa4378
- add self option validation in BaseCommand by @OoBook in https://github.com/unusualify/modularous/commit/ae6fc29464b556f43eb09bef10c271da23d3aaf6
- :sparkles: add Singleton feature for Modularous modules by @OoBook in https://github.com/unusualify/modularous/commit/e2acc0253f4e29e4cf79ed5398f00e01dc8c1afe
- add ManageSingleton trait for singleton controller management by @OoBook in https://github.com/unusualify/modularous/commit/9d9877eff4263e18378372b3e9a9b880bde32ac2
- add ManageEvents trait for controller event handling by @OoBook in https://github.com/unusualify/modularous/commit/646925bbc0a98bff9f707a3a8cd4732bccb37b23
- add singleton detection and absolute URL support in Module methods by @OoBook in https://github.com/unusualify/modularous/commit/c18049705064aa9fb33c84c2cc0ead5653111380
- support singleton model retrieval in Repository update method by @OoBook in https://github.com/unusualify/modularous/commit/0f784ea2426a14d92beabffb8ff831444570b2d1
- enhance sidebar menu routing for singleton and non-singleton modules by @OoBook in https://github.com/unusualify/modularous/commit/622c7c08562bb658399f5b921406f765a882f648
- update schemas configuration for enhanced model publishing by @OoBook in https://github.com/unusualify/modularous/commit/0e7cd9dc53bafd6a918d99231765308c737c00af
- enhance route registration for singleton and non-singleton modules by @OoBook in https://github.com/unusualify/modularous/commit/d448b0bea1e53f9a97b00f6b322fcad6acb9a920
- improve back link generation for singleton modules by @OoBook in https://github.com/unusualify/modularous/commit/7df1a8c8a635a61bd0048fac7a44cc05e4aefd5b
- improve CreateFeatureCommand with self-module flag and table naming by @OoBook in https://github.com/unusualify/modularous/commit/046d3047f2fcec1355507df88a37053bfa3f7d11
- enhance EventMakeCommand with abstract event class selection and self-module support by @OoBook in https://github.com/unusualify/modularous/commit/689a0c6bda29740e3e5b8a29ad50ad01c654eea8
- enhance ListenerMakeCommand with self-module support and event selection by @OoBook in https://github.com/unusualify/modularous/commit/8d54e9a1e98052825cf221448eb8cd2732332184
- add migration publishing method to LaravelServiceProvider by @OoBook in https://github.com/unusualify/modularous/commit/bed5113d2aa5cb213a2a7b451fb1335a667f4963
- create priceable and modularous database migrations by @OoBook in https://github.com/unusualify/modularous/commit/92c54b8a6817ebb697ac6dfb71f81db47704b9a8
- improve MorphedByMany migration generation with dynamic model and table names by @OoBook in https://github.com/unusualify/modularous/commit/35dce9418b956c109c435bdd3cd015a26f27aa47
- add profile dialog state and methods to user store and common methods by @OoBook in https://github.com/unusualify/modularous/commit/5eb1127262c4df3075d204024285864914e5827b
- add profile dialog and avatar functionality to Main and Sidebar layouts by @OoBook in https://github.com/unusualify/modularous/commit/155968dbd48ac5a5ec75abb3ca054bdcff9d688d
- create abstract ModelEvent class for broadcasting model-related events by @OoBook in https://github.com/unusualify/modularous/commit/3c1d6a00e46947254074242fecae488aff6b1c77
- add show modal functionality to useTableModals hook by @OoBook in https://github.com/unusualify/modularous/commit/aaf653261eaf6ff4241a8196b1b15b49238d6234
- enhance useTableItemActions hook with advanced action handling and responsive display logic by @OoBook in https://github.com/unusualify/modularous/commit/3263ad7fb89fad8825b2eb9e39c1b6739e98a11e
- create RecursiveDataViewer component for nested data visualization by @OoBook in https://github.com/unusualify/modularous/commit/04d69bf2569cb33636492037020513b573b11a9e
- add show modal template to Table component by @OoBook in https://github.com/unusualify/modularous/commit/e2d7f261421a55b16eb027670c4c8243af640395
- add dynamic method call utility to UEConfig plugin by @OoBook in https://github.com/unusualify/modularous/commit/81d5af7fb8056efed1ca4e5e9e4cf34d2da51e52
- implement ModularousActivator for module status management by @OoBook in https://github.com/unusualify/modularous/commit/b51f13dca73fd3c2eba19fe43130a4d4b2537b6f
- create ModuleActivator for route status management by @OoBook in https://github.com/unusualify/modularous/commit/f1725a599f05851993c1c94ebf53e4d648fff002
- add new Permission enum cases for activity and show actions by @OoBook in https://github.com/unusualify/modularous/commit/2f8f0e1e7d7ba377806271a3ff5241763349a8ca
- enhance ModelEvent constructor with optional serialized data by @OoBook in https://github.com/unusualify/modularous/commit/f4fd31278d036957e4414cf8633fcd7138b20f24
- add DispatchEvents trait for model event handling by @OoBook in https://github.com/unusualify/modularous/commit/63f7967ce4bbfcef5c1ca7b072723ed0e5204924
- enhance Repository with activity logging and event dispatching by @OoBook in https://github.com/unusualify/modularous/commit/54ce2d2932da1bd70a938713ca544b9672893a00
- create base Listener class for dynamic notification handling by @OoBook in https://github.com/unusualify/modularous/commit/b9ef4e09bb98b811ef6eb9ab8e16dbde38f163d2
- add show and activity actions to table management by @OoBook in https://github.com/unusualify/modularous/commit/e9c9cd7eacd46cef8ddd07e6bfb9d518089d9934
- update PanelController with show and activity permission configurations by @OoBook in https://github.com/unusualify/modularous/commit/bdc0a9ad6c10555277291d8ef2f3f39f3be0b6cc
- add activity logging for translatable models by @OoBook in https://github.com/unusualify/modularous/commit/c04295f354263b69265e438bcbe377b3598966d2
- create SystemNotification module for comprehensive model event notifications by @OoBook in https://github.com/unusualify/modularous/commit/b97b0ed3bc3b0f40c1dc456e167f8045b51f9996
- create BroadcastManager for dynamic event broadcasting configuration by @OoBook in https://github.com/unusualify/modularous/commit/341d72ccba06c943e6d621ba946b00eba576157f
- add Modularous module activator configuration by @OoBook in https://github.com/unusualify/modularous/commit/2090bb23ba8340a003aeb208579ca40721781656
- add Telescope frontend assets for Vue application by @OoBook in https://github.com/unusualify/modularous/commit/aa9f75400e16168a4183345254b3c9cd13bd377c
- add file management methods to FilepondManager by @OoBook in https://github.com/unusualify/modularous/commit/82bcbe8cf6f6152717fe0ebc31a30b883a7e83e2
- publish Telescope frontend assets alongside Modularous assets by @OoBook in https://github.com/unusualify/modularous/commit/5535149a372cb4f6d8aa23c1dd9ec00cf0df34d9
- add FilepondsScheduler for automated temporary file cleanup by @OoBook in https://github.com/unusualify/modularous/commit/ae01f2d43ebf61cca3ccbc69b38d602e57ab7619
- limit maximum file uploads in Filepond configuration by @OoBook in https://github.com/unusualify/modularous/commit/19f63ee3d2425d0d7a4f26ea913fb99dcbfe389f
- add FilepondFlushCommand for manual temporary file cleanup by @OoBook in https://github.com/unusualify/modularous/commit/9c07df69c0a693cb66cc1b5efa77b384ec9c3445
- create show layout blade template for Modularous by @OoBook in https://github.com/unusualify/modularous/commit/f6dab7005397c3d04324aa5a25e0de63b6598931
- add mail configuration and enable conditional email notifications by @OoBook in https://github.com/unusualify/modularous/commit/2ab8eac72c4f3aae50320b2be91bec6bbac83a8d
- add system group configuration to SystemSetting module by @OoBook in https://github.com/unusualify/modularous/commit/e984645551bbf383abb602a46ca3ee74b8e284bc
- create CheckboxCard Vue component for enhanced input selection by @OoBook in https://github.com/unusualify/modularous/commit/efa5d643823e3a910898f5d78471c63673d7fd81
- add description support to RadioGroup component by @OoBook in https://github.com/unusualify/modularous/commit/4def929b08ab8038b00a24a52781855ab2f0808e
- enhance Checklist component with advanced configuration and rendering options by @OoBook in https://github.com/unusualify/modularous/commit/e96d8478507a2660193da8252e073887db1e756c
- enhance RadioGroup component with dynamic description rendering by @OoBook in https://github.com/unusualify/modularous/commit/3a0fce955808072318f2afcbd674ac3f889a0207
- enhance Filepond component with additional rendering and configuration options by @OoBook in https://github.com/unusualify/modularous/commit/45610d35599b5d25a60b4f0ca0ef26fa25065ed8
- enable HTML rendering for subtitle in CustomFormBase component by @OoBook in https://github.com/unusualify/modularous/commit/8d3cefd1c8ccfb5b4883f6766518898079b5f07e
- add conditional locale chip rendering in Locale component by @OoBook in https://github.com/unusualify/modularous/commit/ea2aa89dd42cc805c8d16fd609cffd4c709ba4c2
- enhance list method with improved translation and relationship handling by @OoBook in https://github.com/unusualify/modularous/commit/49875cdfea1ccfe05fa73cf20e4b8bf11f27f161
- add noEager option to skip eager loading in TabGroupHydrate by @OoBook in https://github.com/unusualify/modularous/commit/9c9693d759d25f9e79ada8d9e1e865483f625ac9
- add file type validation support in FilepondHydrate by @OoBook in https://github.com/unusualify/modularous/commit/f14fae76f134c1edbacc6c1b67e2068cdf0b5fa6
- disable activity logging when user is not authenticated by @OoBook in https://github.com/unusualify/modularous/commit/a82125757fd0247948c2c80d133fa37c7afd05b2
- add whereTranslation scope for querying translatable models by locale by @OoBook in https://github.com/unusualify/modularous/commit/338ed4cb189033a6de1ed92fcb9c3e139f68e186
- add polymorphic input type handling in ManageForm trait by @OoBook in https://github.com/unusualify/modularous/commit/5c206130c1e8acdc6ea42c41fbf8a75720ad1ec0
- add translation deletion for soft-deletable models by @OoBook in https://github.com/unusualify/modularous/commit/aa5eb5936d2acb73cc035a441a4a9ae20b3547aa
- add columnClasses prop to ConfigurableCard component by @OoBook in https://github.com/unusualify/modularous/commit/187430bebd215f011e2628a4efe5557b6f7cca23
- add support for 'title' input type in ManageForm trait by @OoBook in https://github.com/unusualify/modularous/commit/19b5ed57fe03ffa47de53fc66003167d2013be96
- add support for 'title' input type in CustomFormBase component by @OoBook in https://github.com/unusualify/modularous/commit/1201f306010127732b68973fcfe1605004b0fb14
- add id attribute to FormOld component for improved targeting by @OoBook in https://github.com/unusualify/modularous/commit/ae5103934c0ae92d0dbb082ed696f3c8a0c40d86
- add FormTabs component for dynamic multi-tab form input by @OoBook in https://github.com/unusualify/modularous/commit/b8cf16dec17283ecb501ba975ca2d24fbd3f4308
- implement TaggerHydrate and Tagger component for dynamic tag input management by @OoBook in https://github.com/unusualify/modularous/commit/adbb5450ea5690afeef5bfbd929b710539abe53a
- add custom suffix support to morph-related helper functions by @OoBook in https://github.com/unusualify/modularous/commit/3f948636fa38fd762ca217c0f933c07af1243223
- add backtrace_formatter helper function by @OoBook in https://github.com/unusualify/modularous/commit/96a6785503cc54e35ab68bd373449755cd8f4e0a
- add Authorization feature with comprehensive model and trait support by @OoBook in https://github.com/unusualify/modularous/commit/f576410cebedfb0a61ddfd47241d1a2dd86d47b6
- add default authorization configuration to ModelHelpers trait by @OoBook in https://github.com/unusualify/modularous/commit/31c66c91fa3786f180af21101c04a4de241d3aaa
- add default creator model to ModelHelpers trait by @OoBook in https://github.com/unusualify/modularous/commit/410232f6f828d82e99d09a7cde7b63933e877a56
- dynamically extend fillable attributes for trait-based models by @OoBook in https://github.com/unusualify/modularous/commit/777b58be53a7d9fe08f3b03f8655a9aa26b9ccfb
- add company-related attributes to User model by @OoBook in https://github.com/unusualify/modularous/commit/e51338cd7fe5aba3fb5ed05d7bc1cdcb2f887c37
- add schema update event emission in Form component by @OoBook in https://github.com/unusualify/modularous/commit/0db38a72b64c05707cc4c5d4a08c4fd265a0e9ff
- enhance AuthorizeHydrate with dynamic model authorization and role-based filtering by @OoBook in https://github.com/unusualify/modularous/commit/52194cc1a3a89945ae353abdd57afaeafa2ee707
- enhance filter method with advanced scope and argument parsing by @OoBook in https://github.com/unusualify/modularous/commit/8619b9a5282e56515aee52788ab7ea8e307e1ac9

### :wrench: Bug Fixes

- remove debug statements from search method by @OoBook in https://github.com/unusualify/modularous/commit/9983b5cd7d25bc111d839759081abd5f2f6ded51
- update PR template check to fetch changed files dynamically by @OoBook in https://github.com/unusualify/modularous/commit/39ef3f0197bf659b1eca70957bf3311feb04ad10
- correct module directory method call in FileActivatorTest by @OoBook in https://github.com/unusualify/modularous/commit/409a090052e2056ae111f87cb3e914f1ded489b5
- improve JSON response for login redirects by @OoBook in https://github.com/unusualify/modularous/commit/9edfd68dd2a1b2e5688a9dbb4afd728b8e8188b0
- update navigation link to point to admin dashboard by @OoBook in https://github.com/unusualify/modularous/commit/eeddc178c933187ca6908c4531356ef249b9f281
- update environment variable prefix in Vite configuration by @OoBook in https://github.com/unusualify/modularous/commit/99f960080bb8446747c0a08a3aca39078515ffc7
- improve Locale input component initialization logic by @OoBook in https://github.com/unusualify/modularous/commit/f087942a27edbafd5ac072d7693e6e88ec5594ad
- enhance Fileponds handling with locale and role support by @OoBook in https://github.com/unusualify/modularous/commit/89379de237c778b860fd8dacded9ddd33bb80661
- improve getFormUrl method with error handling and parameter correction by @OoBook in https://github.com/unusualify/modularous/commit/348aa282da969b337e4bd4cd37c00a043a5f3b43
- adjust FormSummaryItem button margin styling by @OoBook in https://github.com/unusualify/modularous/commit/04c9587dd7e2fac4c775dda4954d2b74ffda9c1a
- improve value formatting in PropertyList component by @OoBook in https://github.com/unusualify/modularous/commit/8a821fa46b312943dda52cee9a71037bb419bd57
- conditionally render StepperPreview form data section by @OoBook in https://github.com/unusualify/modularous/commit/4af8c6688798c9e2f1e4e91220b95c4cb0edff26
- simplify morph to many relations sync logic by @OoBook in https://github.com/unusualify/modularous/commit/7ce791b673f12a3536172bf73d75d26acfd350f8
- update translation languages field handling in TranslationsTrait by @OoBook in https://github.com/unusualify/modularous/commit/cf0902d9998a3d8b572f10744b7990358ba83654
- remove debug logging in StepperForm component by @OoBook in https://github.com/unusualify/modularous/commit/eee5bf246f90552e6f863ff38ec1c066a0ebddbf
- enhance StepperContent component data management and event handling by @OoBook in https://github.com/unusualify/modularous/commit/63b70070e0892ae2113ce9ee3d63165ece5a698d
- add color prop to authentication form titles and reset form by @OoBook in https://github.com/unusualify/modularous/commit/2fbadceabacf5311f815ca66aaf5bb1c0d81813c
- import USER mutation in Main.vue layout component by @OoBook in https://github.com/unusualify/modularous/commit/3c388c4c8da57e109fc51fa96336a31d9c26e89f

### :recycle: Refactors

- simplify cache clearing method by @OoBook in https://github.com/unusualify/modularous/commit/2139cdaeff6328e07436df5d6dab588745234b33
- relocate impersonation routes to web routes by @OoBook in https://github.com/unusualify/modularous/commit/3b533c298251d2a86ba2bfe52a9d50bd17dcab8e
- optimize admin user table migration operation by @OoBook in https://github.com/unusualify/modularous/commit/b10579f36e7e68bea11cc9a17037df927830e08f
- improve vendor path methods and documentation by @OoBook in https://github.com/unusualify/modularous/commit/a3e0595ca654f350690c14ddf01f355c4b963787
- update vendor path method and documentation by @OoBook in https://github.com/unusualify/modularous/commit/f92f92b492d0562b9354d1129af5f323bee96f24
- update AboutCommand with dynamic configuration and version retrieval by @OoBook in https://github.com/unusualify/modularous/commit/60dc59c749a2f20de3cbc6738d1d35292d39e51b
- clean up BaseServiceProvider configuration methods by @OoBook in https://github.com/unusualify/modularous/commit/22cfb1ddfee56387fc9a47b4ae79cc6fd8a6abb1
- update composer helper functions to use Modularous facade by @OoBook in https://github.com/unusualify/modularous/commit/547cb8605c5d8c4de88b4bc54eb545a0288a739d
- improve morph-related helper functions by @OoBook in https://github.com/unusualify/modularous/commit/30c0b7ac7f2b0a095a6acbc691b47e2a64592ed0
- improve translation and pivot table helper functions by @OoBook in https://github.com/unusualify/modularous/commit/97121a6915a7b3d4e4474e5a7035588b13233e1d
- update theme discovery functions to use File and Modularous facades by @OoBook in https://github.com/unusualify/modularous/commit/e8b059d04d42ee8a9df13b9e8d01159baebf3d73
- update morph pivot table stub to use named parameter by @OoBook in https://github.com/unusualify/modularous/commit/6b52a7e9e38a4a292f6bf7110b23f9d94fed451c
- enhance PintCommand with improved configuration and self-linting by @OoBook in https://github.com/unusualify/modularous/commit/ce499c891f0678b3bf6dd522f199165460323d31
- migrate state feature to use dynamic table configuration by @OoBook in https://github.com/unusualify/modularous/commit/8963d595393f64805881a525a1355cac3507a26d
- update BuildCommand to use Modularous facade for vendor directory by @OoBook in https://github.com/unusualify/modularous/commit/5cb244d1a16a59c3be8485b4a6fcb50ef58d3262
- modify modularous payments table operation async behavior by @OoBook in https://github.com/unusualify/modularous/commit/ed3969b8d197ea08e48307ab916ad5b591647c30
- simplify command signature definition in command.stub by @OoBook in https://github.com/unusualify/modularous/commit/12c235319a9bf57f6bcc78d0d69c504813b22d03
- remove unused boot and register methods in provider.stub by @OoBook in https://github.com/unusualify/modularous/commit/ac1f1a201b8fd8ac1d7faa0cc9dcdad775fe4695
- update navigation configuration for superadmin role by @OoBook in https://github.com/unusualify/modularous/commit/9e5e39882854b4c4da5f51b2ce59be70af871b1a
- reorganize controller traits and update import paths by @OoBook in https://github.com/unusualify/modularous/commit/50f20c764f49231e0be16144fa9591893afdcdee
- remove redundant view files from SystemPayment and SystemSetting modules by @OoBook in https://github.com/unusualify/modularous/commit/3ef64ae1f276d8e254e2353767da6dea2c8355cb
- remove commented-out module cache configuration code by @OoBook in https://github.com/unusualify/modularous/commit/d5827e14393e781bf2506c00eeafb9c5707cd21d
- update Form component and useForm hook to improve model handling by @OoBook in https://github.com/unusualify/modularous/commit/cd1334a9a118b9c64702038d1857b797de8c1066
- update trait command option shortcuts by @OoBook in https://github.com/unusualify/modularous/commit/08fd8963b59fc7b1003f780374198f12e2f63947
- remove debug logging in useTableItemActions hook by @OoBook in https://github.com/unusualify/modularous/commit/bb54800ff4d4815c976290b85ae21df56b8a05d6
- update authentication guard name to 'modularous' by @OoBook in https://github.com/unusualify/modularous/commit/a00db1bc95a2199215b7b46803f444448670d780
- remove deprecated admin routes file by @OoBook in https://github.com/unusualify/modularous/commit/675887add6f140129275aa3e53d83ea86f53a32c
- enhance BaseCommand with trait options and module name retrieval by @OoBook in https://github.com/unusualify/modularous/commit/d61e77c1022be8d9a5bf9c9be35696c173ce25c5
- improve input components with enhanced label support by @OoBook in https://github.com/unusualify/modularous/commit/8ae65bb33097d207ed79d57690725a856185217c
- enhance useForm hook with computed formItem property by @OoBook in https://github.com/unusualify/modularous/commit/0b4ba8061a4eaa75d767ebeab3c436890ef64b5b
- improve Form component top inputs with margin and formItem prop by @OoBook in https://github.com/unusualify/modularous/commit/c4863030f6b88552905b09f2e1ad8995d6bd617e
- improve migration, model, and repository commands for singleton support by @OoBook in https://github.com/unusualify/modularous/commit/67b625917987c4b9788d5e70c395e624fae53c69
- update BaseController with singleton and event management traits by @OoBook in https://github.com/unusualify/modularous/commit/f3cac172944fb16e28cd8606024469be32e14d40
- improve form data retrieval with new methods for singleton and dynamic routing by @OoBook in https://github.com/unusualify/modularous/commit/eb2d4771f69101f90952b9395b4d7f733fea42d0
- modernize RouteMakeCommand with Laravel signature and trait support by @OoBook in https://github.com/unusualify/modularous/commit/9d13275f92d56f1eebd7e291dd38875ab7786985
- modernize ModuleMakeCommand with Laravel signature and enhanced options by @OoBook in https://github.com/unusualify/modularous/commit/d225892bc4ab74eb47d09b40aad9aa7aaafdc7d2
- modernize ModelMakeCommand and RepositoryMakeCommand with Laravel signature by @OoBook in https://github.com/unusualify/modularous/commit/7c5853ee3295d6ebf4013ebfa26ba5820b874821
- update migration names by @OoBook in https://github.com/unusualify/modularous/commit/445a0e39263630dda19835c98f9907d587acaffa
- improve createDefaultMorphPivotTableFields helper function by @OoBook in https://github.com/unusualify/modularous/commit/847995e2ed564e9d3b9f9884a1615f0b0163f3ce
- update payment currency payment service migration table creation by @OoBook in https://github.com/unusualify/modularous/commit/b742caeeaba4da0b774063e64cf06c32ccb315f1
- update Modularous guard name method in payment seeders by @OoBook in https://github.com/unusualify/modularous/commit/e6295f2ed508491c838ff9b87c8d7938c8bf73db
- modify migration loading and publishing behavior by @OoBook in https://github.com/unusualify/modularous/commit/c2f229ba08c222c654b64b40569d8c34ac24b57a
- update global properties method binding in UEConfig by @OoBook in https://github.com/unusualify/modularous/commit/73445b472b9028790128d5caf79e0eb2c554f990
- add ModelHelpers trait to SystemPricing entities by @OoBook in https://github.com/unusualify/modularous/commit/92eab0638cbb88e58b03bb12e8377b6cc6cf10b5
- update CurrencyRepository namespace import by @OoBook in https://github.com/unusualify/modularous/commit/e02c7328c91db7fa00ff3db3793e0a1f7437f4a4
- use actionShowingType on item actions by @OoBook in https://github.com/unusualify/modularous/commit/e45dadc2bf80532897388034599f8717b4d19b91
- adjust row action display thresholds for better responsiveness by @OoBook in https://github.com/unusualify/modularous/commit/88617cf3e38ddaae2fa58ced3fa36981aacfb4f4
- disable broadcasting configuration by default by @OoBook in https://github.com/unusualify/modularous/commit/bba3f8c0d3d64d620b913e530b4081dcc87c6a39
- remove $call method from commonMethods utility by @OoBook in https://github.com/unusualify/modularous/commit/0d9b84dfeb478bf0c3725b3cb45f74eef25ecbcf
- update event handling in ManageEvents trait by @OoBook in https://github.com/unusualify/modularous/commit/cb4327df8225dd6e466f915d3b46ee32d2790dca
- change getTitleField to getTitleValue by @OoBook in https://github.com/unusualify/modularous/commit/2b326cb75a2b9b92960d1fb327dc4e8700fd818b
- clean up commented code and simplify action logging in BaseController by @OoBook in https://github.com/unusualify/modularous/commit/0725cb7c4cee59ed63b0279213ab5130cd8bcc7d
- update Modularous and Module classes for improved module management by @OoBook in https://github.com/unusualify/modularous/commit/058274fe7e19a913710421a7347c82445f77447e
- improve IsSingular trait with fillable attribute filtering by @OoBook in https://github.com/unusualify/modularous/commit/bda10e41671823dd65e35154c88afa09512edd3a
- enhance Filepond component with improved file handling and slot configuration by @OoBook in https://github.com/unusualify/modularous/commit/75fb4d3fcf7aa1ec8d50a5e848df4545f06c0092
- simplify useForm hook by removing debug logging by @OoBook in https://github.com/unusualify/modularous/commit/04eb1739b906a3a3ec063f0ec7005b7beaca23b3
- adjust Locale component class binding logic by @OoBook in https://github.com/unusualify/modularous/commit/acbea14785e2b2873d840858b5d79e543fad8a4c
- update scheduler configuration for Fileponds and Telescope by @OoBook in https://github.com/unusualify/modularous/commit/12c4e01d369cc37d55966e8774be84ccc1407b20
- comment out submodule-related properties in BaseController by @OoBook in https://github.com/unusualify/modularous/commit/5abf7e0e74cba87ced37933860191b24e74633e8
- update free layout blade template with @push directive by @OoBook in https://github.com/unusualify/modularous/commit/892b19ee42c631a528774be0282e0f42ccb7f0d0
- update SystemSetting module configuration and model by @OoBook in https://github.com/unusualify/modularous/commit/d4dc39c33c7b78326ab519781564cbb5e7382304
- migrate migration classes to PHP 8.1 anonymous class syntax by @OoBook in https://github.com/unusualify/modularous/commit/732368ee52a2111eb3645104be5446e98b03761a
- update stateables migration with improved morphPivotTableFields generation by @OoBook in https://github.com/unusualify/modularous/commit/c163deab63b0024b5d84f9093face31d7d8c60d9
- enhance morphTo relation handling in repositories and form management by @OoBook in https://github.com/unusualify/modularous/commit/33ef4c70fb992e9b44693ae4bf4cb09b365be645
- modify MigrationMakeCommand option for relational table generation by @OoBook in https://github.com/unusualify/modularous/commit/d1c96369d4e167fc28872adc41e4c209c813ea53
- remove debug logging from Checklist component by @OoBook in https://github.com/unusualify/modularous/commit/410fbc371d3ef7dd4da68efbbf0d3e13bb08ca42
- improve variable naming in commonMethods utility by @OoBook in https://github.com/unusualify/modularous/commit/ea3d80399be6d3d106780e6e6b912846b54860c7
- add returnObject configuration to input hydrate classes by @OoBook in https://github.com/unusualify/modularous/commit/b7d83ea5b58c1b857a737df60a5097ad1ae4d866
- remove commented-out code in getFormData utility by @OoBook in https://github.com/unusualify/modularous/commit/1a59e3005cf7cda2ba9bcbd2cca1310f428b9122
- add v-fit-grid directive to StepperPreview component by @OoBook in https://github.com/unusualify/modularous/commit/60c43968a0df2897a5668fb25619a6d7912bfd58
- improve Checklist component with enhanced group and item rendering by @OoBook in https://github.com/unusualify/modularous/commit/5e96ac56b7d47f714eb3c1d74a1246d89c533290
- improve list method with enhanced column and translation handling by @OoBook in https://github.com/unusualify/modularous/commit/8ba817a512eb98aa3ba4454cd71a603d24769b0d
- enhance HasTranslation trait with advanced translation handling by @OoBook in https://github.com/unusualify/modularous/commit/f05a507f22614310e436e9fbc2523f099abb0fed
- improve polymorphic input handling in ManageForm trait by @OoBook in https://github.com/unusualify/modularous/commit/f0c9cc26f992c03709312c68fa2de1c596f0119a
- improve ModelHelpers trait with static method and default eager loading by @OoBook in https://github.com/unusualify/modularous/commit/f6f8fa3edeb9aa4a4ffa6206ee49af01fc3861af
- improve relationship column data retrieval in BaseController by @OoBook in https://github.com/unusualify/modularous/commit/1b86bf3a72852d36e0cdef49b3c2dc0f075055e1
- improve Repeater component styling and class naming by @OoBook in https://github.com/unusualify/modularous/commit/89cd8580cfa3eb85ff90e37b7d7f7fb69b2d8165
- enhance useInput hook with flexible model value update by @OoBook in https://github.com/unusualify/modularous/commit/7bb456734431289881ebe686f84cd57d97e1fab0
- improve Title component prop handling and class generation by @OoBook in https://github.com/unusualify/modularous/commit/fce588ce59f4e7f12807679f3475f61cfc13b5e3
- improve wildcard matching and value casting by @OoBook in https://github.com/unusualify/modularous/commit/7a54d87d9dc4f68d94eed66fee44d4116bd650b1
- improve component registration and import paths for custom inputs by @OoBook in https://github.com/unusualify/modularous/commit/469fe622caafbd402514c30062fca21a64f9bcd0
- simplify BaseController show method response handling by @OoBook in https://github.com/unusualify/modularous/commit/ebfb6ebf9a2b73bfc87244461bc165c80a66649e
- simplify tags method in CoreController by @OoBook in https://github.com/unusualify/modularous/commit/e8e6a3a65dc97b4026ea1541e809820e82603de6
- improve RadioGroup input default value handling by @OoBook in https://github.com/unusualify/modularous/commit/d16b5f1423eba0b87401022ba17d994590b98f9a
- prevent repository hydration in console environment by @OoBook in https://github.com/unusualify/modularous/commit/58d88de3048959849ef22ef4021f204e9b8d9d98
- improve Form component and useForm hook state management by @OoBook in https://github.com/unusualify/modularous/commit/37c95990dccfbadb5382fb83011310366885058f
- improve form data handling and error logging in getFormData utility by @OoBook in https://github.com/unusualify/modularous/commit/179eea7561802b2278d49fd011678344c474e3ce
- improve StepperPreview and StepperFinalSummary components by @OoBook in https://github.com/unusualify/modularous/commit/1b3e1e43894733758b36a42c025f20146c74dcc8
- improve StepperForm event handling and method signatures by @OoBook in https://github.com/unusualify/modularous/commit/a567156f5e7d6c77f144a83cff69ac5e4937acda
- improve ModularousActivator module status management by @OoBook in https://github.com/unusualify/modularous/commit/7a81aa6c08b33b257df93e387cfc20937f98efa5
- improve soft delete handling in HasTranslation and IsAuthorizedable traits by @OoBook in https://github.com/unusualify/modularous/commit/6a7a73453b05e859861143083b536ba5e23a63a6
- replace IsAuthorizedable with HasCreator trait by @OoBook in https://github.com/unusualify/modularous/commit/aea3418af5b2cfa7806e5c79ecf7304bf27935e7
- simplify HasCreator trait and remove unused methods by @OoBook in https://github.com/unusualify/modularous/commit/8f9ff397cdd2b53526fb5e36e4a0093adf8034b1
- simplify Modal component structure and enhance slot usage by @OoBook in https://github.com/unusualify/modularous/commit/b720e651686900b3fcb73d277f6eaa1ffd96e6ca
- enhance HasAuthorizable trait with more flexible authorization handling by @OoBook in https://github.com/unusualify/modularous/commit/27e2a0e177e746401f5d0b58b5d174e58d11007d
- enhance HasCreator trait with custom creator saving mechanism by @OoBook in https://github.com/unusualify/modularous/commit/22ccf7ec2cad20d498e1b4aa1930f2e1a3e1a07b
- improve HasPayment trait with more robust price and payment handling by @OoBook in https://github.com/unusualify/modularous/commit/a25825c009f6dd0b0bd5dfb2156c0a7e82f7b711
- enhance HasStateable trait with initial state handling by @OoBook in https://github.com/unusualify/modularous/commit/f51905a28a44860290907896ef8e91f765171cdd
- enhance Price model with flexible payment status filtering by @OoBook in https://github.com/unusualify/modularous/commit/26fd9f7d1c35584920038a00ce64af996de24d8f
- optimize Repository list method for translatable models by @OoBook in https://github.com/unusualify/modularous/commit/0347e6b93da1251de26941d7155ce1a7aa982e16
- update StepperForm modal design and interaction by @OoBook in https://github.com/unusualify/modularous/commit/a5d46bf178b5036e3984707ef3173dcd1b24343c
- improve backtrace_formatter with robust error handling by @OoBook in https://github.com/unusualify/modularous/commit/e17e6a6ce6c25eb96e52a047dedc1f5b2ad10a17
- add InnoDB engine configuration for modularous tags table by @OoBook in https://github.com/unusualify/modularous/commit/4fa352abe77ffb6eadf0bcdccab8b2e14b636df1

### :lipstick: Styling

- remove commented code and unused configurations in ModularousProvider by @OoBook in https://github.com/unusualify/modularous/commit/4cdc2576fcf11ef9dade2594517f4092e475cf85
- add comment to getModulePath of RepositoryInterface by @OoBook in https://github.com/unusualify/modularous/commit/4d9a6fce71d360b78c5133b7930d86d5524ea64b
- arrange custom modal actions by @OoBook in https://github.com/unusualify/modularous/commit/621a6eaa49c54faa87444965175ced098c4fdc8d
- clean up commented code and remove unused table props by @OoBook in https://github.com/unusualify/modularous/commit/ab27e286619d557df4a8be6782f65996ea5c88d9
- lint coding styles for v0.27.0 by @OoBook in https://github.com/unusualify/modularous/commit/04629430ecd60f413d68856711c9c19e775576d7

### :white_check_mark: Testing

- configure module scanning for test environment by @OoBook in https://github.com/unusualify/modularous/commit/edfe46447fab0ed3635021972292c939c558dfb1
- add Spatie Permission Service Provider to TestCase by @OoBook in https://github.com/unusualify/modularous/commit/7ba1585b62ed53a9d4dee41a655fb7c6ccb317ed
- add comprehensive helper function tests for format, migration, and sources by @OoBook in https://github.com/unusualify/modularous/commit/6ee0f0b6b9701641b5c47dbd1c2802bb205591aa
- add ResizeObserver polyfill for input-image component test by @OoBook in https://github.com/unusualify/modularous/commit/88af138ca05e222b5ca76d0af317499b4177629d
- add comprehensive ModularousActivator test suite by @OoBook in https://github.com/unusualify/modularous/commit/e6a5893b9f190ffcd9e12edd4c832b96bf65e121

### :package: Build

- update build artifacts for v0.27.0 by @OoBook in https://github.com/unusualify/modularous/commit/dfee69797532ae521679059cc2fbffc600d99db5

### :beers: Other Stuff

- add command aliases for operation creation by @OoBook in https://github.com/unusualify/modularous/commit/9ebdd52ca52a29a0ae0c17f7e36b98a63230107b
- add resize-observer-polyfill for browser compatibility by @OoBook in https://github.com/unusualify/modularous/commit/a1a4b39d50a209cc859f7217ee2e123aafd6e7c1
- update default theme from 'unusual' to 'unusualify' by @OoBook in https://github.com/unusualify/modularous/commit/2223d116b1ce0d49799af598e40dc86cca0f589e
- add fallback values for Reverb broadcasting configuration by @OoBook in https://github.com/unusualify/modularous/commit/995f3c4c5b3680828f03de3796873241ec865772
- add modularous regex replacement command for blade sections by @OoBook in https://github.com/unusualify/modularous/commit/e291c95a7ecb67125485873fcf77ffbb7e96be10
- comment out additional broadcasting channel configurations by @OoBook in https://github.com/unusualify/modularous/commit/6f7c65a8954ba8237a09f95ee3c5842d212d21ca

## v0.26.1 - 2025-02-02

### :wrench: Bug Fixes

- remove debug statements from search method by @OoBook in https://github.com/unusualify/modularous/commit/4828684da7f174c06ef7b543a06d16f47441e675

## v0.26.0 - 2025-02-01

### :rocket: Features

- add authentication guard and provider configuration methods by @OoBook in https://github.com/unusualify/modularous/commit/77ebbc63b78368d4c2cf19f791d5d4f6b97455b2
- add AuthConfigurationException for robust authentication setup by @OoBook in https://github.com/unusualify/modularous/commit/d2eb26d74deb6f3d57e81043db9db9a4f6e7cc60
- add CreateOperationCommand for generating one-time operations by @OoBook in https://github.com/unusualify/modularous/commit/26a06fd16799c5ee749de41995b54487d3b8c39d
- add PublishOperationsCommand and operation stub template by @OoBook in https://github.com/unusualify/modularous/commit/83868b000b55ae57a00458a5561a9490f744f17e
- update Laravel translation package configuration by @OoBook in https://github.com/unusualify/modularous/commit/80c124ac09ba94b888111d68f2272133317af2c2
- enhance service provider with operations publishing and config management by @OoBook in https://github.com/unusualify/modularous/commit/ffbdf133227da645971d7766583893b5281d05ff
- add one-time operations for system configuration updates by @OoBook in https://github.com/unusualify/modularous/commit/6838cd1e948b55cf01693fac9e63629383b63bc8
- enable module scanning and dynamic cache configuration by @OoBook in https://github.com/unusualify/modularous/commit/7aa1e3858b4deba4f024aeb55320720c169e6afd
- enhance modules config with environment-based cache settings by @OoBook in https://github.com/unusualify/modularous/commit/086d18738ca88d75d631c712c46a0055416b3df1
- add one-time operation for updating user guard names by @OoBook in https://github.com/unusualify/modularous/commit/d5560d2e8665adc14d5e74535d43d3ea9dbc0420
- add dynamic condition evaluation for table item actions by @OoBook in https://github.com/unusualify/modularous/commit/dd99615fcf26a34cb82dc32d58df1f9a871ef8ac
- improve input hook with enhanced default value handling by @OoBook in https://github.com/unusualify/modularous/commit/9b920d5f96f410b571cbc2ef4ed293059a6a3189
- enhance form data handling with new utility functions by @OoBook in https://github.com/unusualify/modularous/commit/318b5c172145e9068f47f1a7b9e0a871480f16c4
- improve prepend schema key tracking and deletion by @OoBook in https://github.com/unusualify/modularous/commit/0834ac7d2c85d614a2d6beb9c982a40b2dd6ff52
- improve table form action handling and validation reset by @OoBook in https://github.com/unusualify/modularous/commit/e55ac219334a0e11d4533d86a9299c353c695014
- add FormActions component for dynamic form interactions by @OoBook in https://github.com/unusualify/modularous/commit/41b218e2ff411da95708446ec18c774954618ffe
- add useForm hook for comprehensive form management by @OoBook in https://github.com/unusualify/modularous/commit/03ab5e83f04d265342fd7eee34917938846ff8b2
- add stepper components for multi-step form workflow by @OoBook in https://github.com/unusualify/modularous/commit/d623f403f454db80734b1f73a47b8cd5418dcc49
- add SystemPricing module entities and repositories by @OoBook in https://github.com/unusualify/modularous/commit/11a6d0a8b9756d6fc2e9d919729038aada8713a6
- add PaymentStatus enum for payment state management by @OoBook in https://github.com/unusualify/modularous/commit/a71363a2b9727757f44caeff9e18d3fcc49b300f
- update HasPriceable trait with SystemPricing module integration by @OoBook in https://github.com/unusualify/modularous/commit/4450a3a40bf969ae65e53e0b47b351078a949f3d
- add default value for tab group input hydration by @OoBook in https://github.com/unusualify/modularous/commit/7d7ebbf8b088ab17324d49a78ee332e2179e35a0
- enhance convertTo method with rounding and decimal precision by @OoBook in https://github.com/unusualify/modularous/commit/09e08bf5e5d85edccd8fba12463c8511e6564fca
- add new format events for model clearing and item resetting by @OoBook in https://github.com/unusualify/modularous/commit/31d72dd35cf9920649a6cbc5b53ce92cf875cb88
- add conditional filtering for pending payment states by @OoBook in https://github.com/unusualify/modularous/commit/07bb77e3e228c462686b361283de7fff136c2100
- enhance HasPayment trait with comprehensive payment state management by @OoBook in https://github.com/unusualify/modularous/commit/a45cb061a42c5ba54f2c0e76818ab96971c7933f
- implement intelligent price update strategy for unpaid and paid records by @OoBook in https://github.com/unusualify/modularous/commit/0ca5f39229b5921b33ba8bd50301ef58be868db9
- implement advanced currency conversion and payment processing by @OoBook in https://github.com/unusualify/modularous/commit/a596084c75faf2aad82ce45fd9271a9bc771aeba

### :wrench: Bug Fixes

- add default return URL configuration for payment module by @OoBook in https://github.com/unusualify/modularous/commit/bb3c81736224816cab67c681e87a247abb2500e6
- add configuration for VAT pricing mode by @OoBook in https://github.com/unusualify/modularous/commit/9a85a3ec9a2fc2f1b80e4e671ae301a7f43b2b47
- add error handling for module resolution by @OoBook in https://github.com/unusualify/modularous/commit/a1ab6ae0b9681878e2f85e6a6246cf4e247c654c
- improve default values for switch input hydration by @OoBook in https://github.com/unusualify/modularous/commit/904116fac66120d329db3e91e30e9b0b933ccfa7
- modify operation file naming convention by @OoBook in https://github.com/unusualify/modularous/commit/106cfb68f7892898e35801d99d3f02c05b5d88df
- remove PaymentTrait and add debug statements for search functionality by @OoBook in https://github.com/unusualify/modularous/commit/b72f8c0989429e0bc58bdfcb05f2f629bb13751a
- refactor filterScope method for more robust field handling by @OoBook in https://github.com/unusualify/modularous/commit/94ef1be3aa75b5e29f45d30878e78f66fcda4fa7

### :recycle: Refactors

- update BaseServiceProvider with modularous configuration and auth handling by @OoBook in https://github.com/unusualify/modularous/commit/67708ab5b1b06a6eda7b0a3ef9858c9abe9f72fa
- update authentication and configuration references across controllers by @OoBook in https://github.com/unusualify/modularous/commit/04bfb7d98947c070d28bb45c1e2a8652edc14752
- update translation configuration middleware by @OoBook in https://github.com/unusualify/modularous/commit/ccdba975d7da38630d6564d3ae142b238e568724
- enhance BaseServiceProvider with robust configuration and auth handling by @OoBook in https://github.com/unusualify/modularous/commit/6f18aec58f3a535af2d9dba272c59d707b8055eb
- update BaseServiceProvider with consistent configuration methods by @OoBook in https://github.com/unusualify/modularous/commit/87343f9efff65f3a53f93c9d1b990b08668f11c3
- dynamically configure auth guard in default seeders by @OoBook in https://github.com/unusualify/modularous/commit/2831284dc1c1b8b0e46959cc5bd9f741e48027f1
- update LoginController and ImpersonateMiddleware with dynamic authentication methods by @OoBook in https://github.com/unusualify/modularous/commit/c08a8fda856740d7fcad63c3239e6c9e376de585
- rename theme and update route middleware configuration by @OoBook in https://github.com/unusualify/modularous/commit/a55ba8dcf27db9d10f5758b16401a1367aa19f9c
- update default activity log table name by @OoBook in https://github.com/unusualify/modularous/commit/b289fab8f946e7e1c585c435d89fefec295ea0f5
- update module configuration for Vite and webpack replacement by @OoBook in https://github.com/unusualify/modularous/commit/1fcd3c6c7699e3a1154f9f5790af0692a0250872
- reorganize config publishing with vendor-specific configurations by @OoBook in https://github.com/unusualify/modularous/commit/6bdb3fb1445b34629ee3dc7a4e1a04db6bbac41f
- simplify module scanning and configuration management by @OoBook in https://github.com/unusualify/modularous/commit/eb04d6cd304da5f5584d10519eb4c34c36aaddf2
- enhance useItemActions hook with dynamic action handling by @OoBook in https://github.com/unusualify/modularous/commit/9131189b6d75e08f243a9a438082abef3469ff7e
- simplify Form component with useForm hook and FormActions by @OoBook in https://github.com/unusualify/modularous/commit/d369890770189e589b68ae34128fbcfc34467d5b
- update TabGroup component with minor form component changes by @OoBook in https://github.com/unusualify/modularous/commit/7dac4e395fb9f06206763e12544fd40cb4b78a77
- modularize StepperForm component with extracted subcomponents by @OoBook in https://github.com/unusualify/modularous/commit/e54973e613d413aa15ce5bc56fac57fad18c1e1f

### :lipstick: Styling

- lint coding styles for v0.26.0 by @OoBook in https://github.com/unusualify/modularous/commit/fa0e64a217bfbd5b2404673b2f6d5e556cb6f7df

### :package: Build

- update build artifacts for v0.26.0 by @OoBook in https://github.com/unusualify/modularous/commit/32c74a1edff6338f84f5efa70303e43af3500a0c

## v0.25.0 - 2025-01-22

### :rocket: Features

- :sparkles: add published status toggle to input types configuration by @OoBook in https://github.com/unusualify/modularous/commit/15b7da07bf9317da337dc8da5315619665948092
- :sparkles: enhance form component with switch inputs for better user interaction by @OoBook in https://github.com/unusualify/modularous/commit/97bb857f83167e82de62332ce43a10d7c8576c3d

### :recycle: Refactors

- :recycle: enhance array_merge_recursive_preserve function for improved flexibility by @OoBook in https://github.com/unusualify/modularous/commit/339c1e9eeb58b7515663cf5eb1aeea208c3b704b
- :recycle: optimize translation field handling in TranslationsTrait by @OoBook in https://github.com/unusualify/modularous/commit/9cf7655eb750718d64c48570c01618f7e4eed5cd
- :recycle: streamline table order management in ManageScopes trait by @OoBook in https://github.com/unusualify/modularous/commit/43ed1f7896100150b85a37b3ee2f190d39890280

### :lipstick: Styling

- lint coding styles for v0.25.0 by @OoBook in https://github.com/unusualify/modularous/commit/3839a76ddac516d93e75e038cf4cd4061274eb8b

### :package: Build

- update build artifacts for v0.24.1 by @invalid-email-address in https://github.com/unusualify/modularous/commit/95ef52136c3617bbb83b9400afaf8cbaa923fe78
- update build artifacts for v0.25.0 by @OoBook in https://github.com/unusualify/modularous/commit/0b57543880207984eae812486a6152b1c53a37fd

## v0.24.1 - 2025-01-21

### :wrench: Bug Fixes

- :sparkles: improve mandatory item handling in Checklist component by @OoBook in https://github.com/unusualify/modularous/commit/6b4c3d3f8e798e82098a02524085ed6556efd9d8

### :package: Build

- update build artifacts for v0.24.1 by @OoBook in https://github.com/unusualify/modularous/commit/69a6988298040a6ed5e95c5a634a6becf43424aa

## v0.24.0 - 2025-01-21

### :rocket: Features

- :sparkles: add spreadable feature && spreadable vue component by @gunesbizim in https://github.com/unusualify/modularous/commit/d7183193d0ba8ea0dd971732cb1d25943a7518ac
- :sparkles: add system setting module && general route by @gunesbizim in https://github.com/unusualify/modularous/commit/7bf04f5d7a55d390ddc53de3bebc290231e7fbf3
- :sparkles: add useItemActions hook for managing item actions by @OoBook in https://github.com/unusualify/modularous/commit/46a08b6f59a66d8248f02600f70f8cfcc0457745
- :sparkles: add transition directive for enhanced element animations by @OoBook in https://github.com/unusualify/modularous/commit/6612d595844954675556249d4d42adfbd3f6abf0
- enhance input initialization in useInput hook by @OoBook in https://github.com/unusualify/modularous/commit/aa725d7c6b9c98c9b808c6122e9d51faf0bd678e
- :sparkles: enhance form action permissions in ManageForm trait by @OoBook in https://github.com/unusualify/modularous/commit/b7e5f030431c02bc4cd9b1bbc16b88349785efdf
- :sparkles: add handleScopes method for dynamic query scope handling by @OoBook in https://github.com/unusualify/modularous/commit/b1cb4817a60b25e4c043fc23bf6730edd3829ff5
- :sparkles: implement StateableTrait for enhanced state filtering by @OoBook in https://github.com/unusualify/modularous/commit/127a7d2ed80cc36382cf43a09e4c400e20a05c50
- :sparkles: enhance price handling in HasPriceable trait with new attributes by @OoBook in https://github.com/unusualify/modularous/commit/803538d7f51b6ad52a4254321f5a8e0ecd07cf9d
- :sparkles: add mandatory item handling to Checklist component by @OoBook in https://github.com/unusualify/modularous/commit/90f98973a4415d6fbcd497285c54c5087f876d58

### :wrench: Bug Fixes

- :bug: fix currency seeder && table names && migrations by @gunesbizim in https://github.com/unusualify/modularous/commit/e47b6342ba01d5d38400daf9095c87d4258a7d4c
- :bug: adjust route export formatting in add_route_to_config function by @OoBook in https://github.com/unusualify/modularous/commit/712b70b25b814708c78748a95f01141b46f1c51a
- :bug: enhance media handling in ImagesTrait for improved localization support by @OoBook in https://github.com/unusualify/modularous/commit/4033a1b485407a23f0f63f308410c50fc715c2bc
- :bug: update payment price handling in PaymentTrait to support forced updates by @OoBook in https://github.com/unusualify/modularous/commit/c7211d64eab25a77071350399177f3a1c99d4414
- :bug: correct attribute naming for base price in StepperForm component by @OoBook in https://github.com/unusualify/modularous/commit/a5e60799a5ab594a1b7abe61316662d965be1e88

### :recycle: Refactors

- :recycle: refactor Spreadable to Spread on some files by @gunesbizim in https://github.com/unusualify/modularous/commit/e1be74ffaafdc11beb1088dd1201591a7872da21
- :recycle: comment out unused files_ option in Filepond component by @OoBook in https://github.com/unusualify/modularous/commit/69a9977dbc78a1b21d1337e97a4ae46339a94db9
- :recycle: integrate useItemActions hook and clean up Form.vue component by @OoBook in https://github.com/unusualify/modularous/commit/cfe9549766b5ba5ae410afb6ba8f9b5eeab02604
- :recycle: remove commented-out debug logs in getFormData utility by @OoBook in https://github.com/unusualify/modularous/commit/51307eda6dfb6de4422dd9c30f0f188f263f6e8e
- :recycle: update Spread model and migration to use UUID morphs and rename JSON fields by @OoBook in https://github.com/unusualify/modularous/commit/b0c5a8e03c8f85f964cdfa45e7a3e05495d6daf7
- :recycle: update MigrationMakeCommand to use schema parser variable by @OoBook in https://github.com/unusualify/modularous/commit/49bde06a6614aeddaa49fe2380d6316ccf2f31c6
- :recycle: update getStateableFilterList method call in ManageTable trait by @OoBook in https://github.com/unusualify/modularous/commit/4a3b10071b1c0add7f5ea55a277d037cbc230ea7
- :recycle: improve change tracking and trigger processing in TabGroup component by @OoBook in https://github.com/unusualify/modularous/commit/42880757a406509172bb238c3537107f0afcdc1c

### :lipstick: Styling

- :lipstick: clean up unused code and comments in Repository and MethodTransformers by @OoBook in https://github.com/unusualify/modularous/commit/884311d017725971f00449ea48d03bb9b1cc2ab0
- :lipstick: clean up Model and MethodTransformers classes by removing unused code by @OoBook in https://github.com/unusualify/modularous/commit/ab48d30acfe69f0553eaedf03fea7484756a1b19
- lint coding styles for v0.24.0 by @OoBook in https://github.com/unusualify/modularous/commit/27315d06a07c2d5f619b51770b482b561321642f

### :package: Build

- update build artifacts for v0.24.0 by @OoBook in https://github.com/unusualify/modularous/commit/bf59ce14e6d51080331ea6139c706d4e2770983c

### :beers: Other Stuff

- remove jsconfig.json file from vue.vue-cli directory by @OoBook in https://github.com/unusualify/modularous/commit/b28e62664768fb867aab7f616166bd3a2da992c3

## v0.23.1 - 2025-01-03

### :wrench: Bug Fixes

- comment out default locale initialization in HasStateable trait by @OoBook in https://github.com/unusualify/modularous/commit/27a1fc298d8cc9f77a01d597b4022ee558ef68da

## v0.23.0 - 2025-01-03

### :rocket: Features

- :sparkles: feature auth success pages by @gunesbizim in https://github.com/unusualify/modularous/commit/fe748eaf3994b03c3dcdd24368e2938927f9a404
- :sparkles: add composer helper functions for package management by @OoBook in https://github.com/unusualify/modularous/commit/5a0d79aa04ef5e925ef7c4b7205a58285f0f54c0
- :sparkles: add Verbosity trait for enhanced output control by @OoBook in https://github.com/unusualify/modularous/commit/248bc5e80f8e73029f263f0efcc22b107a539430
- :sparkles: add Pretending trait for dry run functionality by @OoBook in https://github.com/unusualify/modularous/commit/5640daa9baf8c1944e7288be99f39c4ab28dfbe4
- :sparkles: add regex replacement command and support class by @OoBook in https://github.com/unusualify/modularous/commit/c6ce18d065fcd4a8ecff9c2c82a66519c4c87de1
- :sparkles: enhance CreateFeatureCommand with additional trait and component options by @OoBook in https://github.com/unusualify/modularous/commit/22ae08d5a43114913a0051d0970855925bfc946d
- :sparkles: add GenerateCommandDocsCommand for extracting Laravel console documentation by @OoBook in https://github.com/unusualify/modularous/commit/2d4ea08d651987b0f3214472e064ff0644d8d40a
- :sparkles: add user profile management to Vuex store by @OoBook in https://github.com/unusualify/modularous/commit/2a1379b71ffd7701ccbccca31660f022089e3327
- :sparkles: add ambient module to Vuex store and enhance footer script by @OoBook in https://github.com/unusualify/modularous/commit/bd799fb417fa32e9a58607110c7ae80811579d48
- :sparkles: add development mode indicator to Main.vue by @OoBook in https://github.com/unusualify/modularous/commit/722b137895eb9d5690482c92898a79c4623ec1c2
- :sparkles: improve filter method in MethodTransformers trait by @OoBook in https://github.com/unusualify/modularous/commit/72e8e0a2be8dc66f5cb87e90b5dcf11440428cd9
- :sparkles: add stateable filtering to controller traits by @OoBook in https://github.com/unusualify/modularous/commit/75c4eb60d600afb3e39a7d87d6f0dc20377f4118

### :wrench: Bug Fixes

- :bug: fix paymentService modal && cardTypeSeeder images fix by @gunesbizim in https://github.com/unusualify/modularous/commit/84f574347a3dd692dce2aadec7254e306271e168
- :bug: fix reset password controller and mailing by @gunesbizim in https://github.com/unusualify/modularous/commit/d2999688660b424babe8f77e5c226384bbb44819
- add repository context to issue close command in GitHub Actions by @OoBook in https://github.com/unusualify/modularous/commit/305c7b7fc94332e5e013816ae5d254edf4a653dc
- :bug: enhance getTopSchema filtering logic for editing and creation states by @OoBook in https://github.com/unusualify/modularous/commit/474470acbb78b9930525c42754be57221c5d8ec9
- :bug: update useTable and useTableNames to utilize editedIndex from context by @OoBook in https://github.com/unusualify/modularous/commit/8571ab36f09e012cc1a15a1829a0363966fbbf14

### :recycle: Refactors

- :sparkles: standardize vendor path retrieval across the application by @OoBook in https://github.com/unusualify/modularous/commit/5aeb351552db134d3eb8050a57f92090401c3fb6
- :recycle: add CreateInputHydrateCommand for generating input hydrate classes by @OoBook in https://github.com/unusualify/modularous/commit/7faab412ed628fdd3c848a1419c0e2e57f78904c
- :recycle: add ComposerScriptsCommand for managing modularous composer scripts by @OoBook in https://github.com/unusualify/modularous/commit/064a50465a4e55ef7465e2b3ea5d9c8bcfa6a03a
- :recycle: rename Laravel test command for consistency by @OoBook in https://github.com/unusualify/modularous/commit/19da5ad74a55e460f83e1273fd92bcbb5d4bbee2
- :recycle: clean up and organize VitePress configuration and sidebar generation by @OoBook in https://github.com/unusualify/modularous/commit/ed5687458cbae9ef121c9e7ef18162287f227f04
- :recycle: remove deprecated layout files and streamline structure by @OoBook in https://github.com/unusualify/modularous/commit/c3bc2d7ad966627eecc07bd1f800322c5ad35342
- :recycle: replace @section with @push for STORE in multiple Blade views by @OoBook in https://github.com/unusualify/modularous/commit/5652424c2eb66dc66eb62234b8347231b7c670c9
- :recycle: clean up Blade views and streamline JavaScript handling by @OoBook in https://github.com/unusualify/modularous/commit/c4bfda3b06cf004b4f1f614b17d2ab897fd9d27d
- :recycle: simplify Vuex store configuration by removing unused state and mutations by @OoBook in https://github.com/unusualify/modularous/commit/45f1ee2c7624e5b870958cfd6f8b4b649387801d
- :recycle: update Sidebar.vue to utilize Vuex getters for user and app information by @OoBook in https://github.com/unusualify/modularous/commit/4918e8a843bf639a52b516c9af3fe27b9f38e982
- :recycle: move the methods must be on repository class by @OoBook in https://github.com/unusualify/modularous/commit/d4b08423beaeb70ffcfe2c65ef34913f7fe4bfd1
- :recycle: remove unnecessary parameter passing into createNonExistantStates by @OoBook in https://github.com/unusualify/modularous/commit/8b6b1cba27ecc6b96551f9dd79b8d553c1a88da6
- :recycle: enhance state management with new stateable methods by @OoBook in https://github.com/unusualify/modularous/commit/f58faef8715eafc0acb6d2004ed78c29e5dadb9a
- :recycle: simplify authorizedable fields in migration for unusual defaults by @OoBook in https://github.com/unusualify/modularous/commit/5472de12e0ca99266cc8f7877d03be02d415298b
- :recycle: remove StateableTrait as it is no longer needed by @OoBook in https://github.com/unusualify/modularous/commit/74a3c0d718db03165a303da6131543f74539bf4b
- :recycle: update chat attribute handling in HasChatable trait by @OoBook in https://github.com/unusualify/modularous/commit/7e0250736da1a9375439acc6efab90f9ca605718
- :recycle: enhance authorization handling in IsAuthorizedable trait by @OoBook in https://github.com/unusualify/modularous/commit/6f8f869511fdfdb6213773a29a8d21e93935e0e3
- :recycle: update default requirement in ChatHydrate to -1 by @OoBook in https://github.com/unusualify/modularous/commit/d7570fc38348b395791c9c26ddbb6935055f4733
- :recycle: enhance state management in HasStateable trait by @OoBook in https://github.com/unusualify/modularous/commit/28538a2eab48d5a27cf360512adff860c2ecef85
- update Chat component to improve message handling and user profile retrieval by @OoBook in https://github.com/unusualify/modularous/commit/ea12bd4be10128301f9f4d27da18c10340e84cb6
- enhance Filepond component to support file handling by @OoBook in https://github.com/unusualify/modularous/commit/5812e312da61f11884c92851f534826487dc1f85
- improve get_installed_composer function to support dynamic path resolution by @OoBook in https://github.com/unusualify/modularous/commit/84ed28ee2ad1f92039f720bed8cde767c4d02de3

### :memo: Documentation

- :sparkles: add multiple modularous commands for enhanced functionality by @OoBook in https://github.com/unusualify/modularous/commit/95eafc28e0686b93364d599b161e2da87a039502
- :sparkles: add new guide components and index documentation by @OoBook in https://github.com/unusualify/modularous/commit/1a90eb08f74bffd2fabde6da75232f47f0994080
- :sparkles: update index and remove deprecated API examples by @OoBook in https://github.com/unusualify/modularous/commit/a2a4ed048e0dedec9e3de79ea885e2e17fd4b14f

### :lipstick: Styling

- :art: clean up commented-out code in ReplaceRegularExpressionCommand by @OoBook in https://github.com/unusualify/modularous/commit/ce8dd4a9135bbf848d99574195664cceac435c02
- lint coding styles for v0.23.0 by @OoBook in https://github.com/unusualify/modularous/commit/3457d4c7b3173b7b95464a547125d3359d69dbe4
- lint coding styles for v0.23.0 by @OoBook in https://github.com/unusualify/modularous/commit/3ca5a1adb91be08397d76eb7a61bf17f8ddc3c88

### :white_check_mark: Testing

- add ModularousTest class for comprehensive module functionality testing by @OoBook in https://github.com/unusualify/modularous/commit/199b54df3468f2543539f82625d4b5271b33d2ae
- add comprehensive tests for Modularous functionality by @OoBook in https://github.com/unusualify/modularous/commit/f484ef8f94e359ac5496fdb49d31927e21f550bd

### :package: Build

- update build artifacts for v0.23.0 by @OoBook in https://github.com/unusualify/modularous/commit/d068e547cfc0b6afd89c58bde123bfe6348f65d7

### :beers: Other Stuff

- clean up commented-out code in PR template check workflow by @OoBook in https://github.com/unusualify/modularous/commit/85d6dea9323dc718def4e2f6053464a0f3ff1d14
- :sparkles: enhance Vue process configuration with environment variables by @OoBook in https://github.com/unusualify/modularous/commit/ab197cd1c3a2d877632f52cfc8c7b134d238fe83
- update getTopSchema to include editing state in Form.vue by @OoBook in https://github.com/unusualify/modularous/commit/8ed44eb6fbaf57f074166de5fe7174bac6cdf829

## v0.22.5 - 2024-12-26

### :lipstick: Styling

- lint coding styles for v0.22.4 by @invalid-email-address in https://github.com/unusualify/modularous/commit/43ea6698dfb4001b86c3a83eefd9163c4fa9714d
- lint coding styles by @OoBook in https://github.com/unusualify/modularous/commit/bc1851f193e0e4ad4209c1518a64529168f0075e

### :green_heart: Workflow

- Comment out PHP and Vue setup steps in release workflow by @OoBook in https://github.com/unusualify/modularous/commit/de5da26f57c32586480b9359e6565b1293498633

### :beers: Other Stuff

- Add development setup instructions and git hooks for release branches by @OoBook in https://github.com/unusualify/modularous/commit/a2eb1a1ee4874233bce057ddffe94161dde9ddb1

## v0.22.4 - 2024-12-26

### :wrench: Bug Fixes

- streamline chat instance creation logic by @OoBook in https://github.com/unusualify/modularous/commit/9509159b4bf112ae6d06f5b8976fe5784145a831

### :lipstick: Styling

- lint coding styles for v0.22.3 by @invalid-email-address in https://github.com/unusualify/modularous/commit/6dcc0bef1cfb20cdcfbb95ce6df7ab44ee9009b8

## v0.22.3 - 2024-12-25

### :wrench: Bug Fixes

- create chat instance if none exists during model booting by @OoBook in https://github.com/unusualify/modularous/commit/45f76044f2e9165f8476904c41d582b87b1748c7
- update route check for profile access by @OoBook in https://github.com/unusualify/modularous/commit/59bebcb6f36053da7ee07fc6b92ae3bbc06e4535

## v0.22.2 - 2024-12-25

### :package: Build

- update build artifacts for v0.22.1 by @invalid-email-address in https://github.com/unusualify/modularous/commit/4b8ea07c4e1d8345750c6e58e60ee10a654a2989
- update build artifacts for 0.22.2 by @OoBook in https://github.com/unusualify/modularous/commit/b84febecc40bea1554c90ff8ae15a9173544816f
- update build artifacts for v0.22.2 by @invalid-email-address in https://github.com/unusualify/modularous/commit/7fc99cd4bc369bd884ff6a729fce5b1069256f54

### :green_heart: Workflow

- update GitHub Actions workflow for release process by @OoBook in https://github.com/unusualify/modularous/commit/89cc9ae3c438e2c8ae09a3650acd30d564fc2b87

## v0.22.1 - 2024-12-25

### :wrench: Bug Fixes

- update modal action visibility condition by @OoBook in https://github.com/unusualify/modularous/commit/0141eb486efc3d9594dbe1e3e1ad06067b9f665f

### :lipstick: Styling

- lint coding styles for v0.22.0 by @invalid-email-address in https://github.com/unusualify/modularous/commit/87f14a150544be2a61f158d5740d0078b0acd5aa

### :package: Build

- update build artifacts for v0.22.0 by @invalid-email-address in https://github.com/unusualify/modularous/commit/712e5bfee037c291113090912400bd2d32cd9aaf

## v0.22.0 - 2024-12-25

### :rocket: Features

- add GitHub Actions workflow to automatically close issues when associated PRs are merged by @OoBook in https://github.com/unusualify/modularous/commit/c6c31667f38b22d57b5907b488297599a648bc18
- add CreateConsoleCommand and command stub for modularous command generation by @OoBook in https://github.com/unusualify/modularous/commit/ec1df30b25a2282c62b9f6cca5eacab648071cd1
- add functions to retrieve package version and update .env file by @OoBook in https://github.com/unusualify/modularous/commit/16a2d5e1ff8757f515bf695c4219e79077fe43a5
- add GetVersionCommand to retrieve package version by @OoBook in https://github.com/unusualify/modularous/commit/191fc39b27297da854079facc136f20335be8174
- add CacheVersionsCommand to cache package versions by @OoBook in https://github.com/unusualify/modularous/commit/a6b7f2930f2c68474b46fe167c09a023b53ff417
- enhance application configuration and sidebar display by @OoBook in https://github.com/unusualify/modularous/commit/dbdbdb96ed6907d75a292dafc1b6a46f21ff094c
- add caching functions for translations by @OoBook in https://github.com/unusualify/modularous/commit/89fc2f143b5a273118a0b3c74f359c91b813acef
- integrate translations into the application by @OoBook in https://github.com/unusualify/modularous/commit/46a1f1a06e43b71a6b7e9063bce01c6fcb24d13e
- add async validation method to input hook by @OoBook in https://github.com/unusualify/modularous/commit/9c8e2fd113f17bc3135473c70236b2e9f5c549cd
- emit 'submitted' event on form submission by @OoBook in https://github.com/unusualify/modularous/commit/07580686ca2a500b6d34c930d875092f2f826f8c
- integrate Fileponds functionality into User entity and repository by @OoBook in https://github.com/unusualify/modularous/commit/ef94f411d1d8f2eb513b645a2363a986aaec7c6a
- implement InputHydrator class for dynamic input handling by @OoBook in https://github.com/unusualify/modularous/commit/662f670b18f97a5c10783d3583a33f2b68b8aa1d
- add Urls view composer for dynamic URL binding by @OoBook in https://github.com/unusualify/modularous/commit/667fc625337e4d21431ad0fe36c1ab11905f859a
- add input configuration and hydration functions by @OoBook in https://github.com/unusualify/modularous/commit/6a6a086658be2ea192dc14d4d8b926f4b3f31415
- enhance profile management and UI integration by @OoBook in https://github.com/unusualify/modularous/commit/c60c4730fbc323e2cf63bb7239009eadf49fb038
- add dynamic URL binding to head script by @OoBook in https://github.com/unusualify/modularous/commit/1f1de72deaba2b673411a9d813db26996865dabc
- enhance sidebar for user role display and information by @OoBook in https://github.com/unusualify/modularous/commit/90dac8ee0b8fdf90bbb2f97148575da8e34583c0
- add path and namespace concatenation functions by @OoBook in https://github.com/unusualify/modularous/commit/7d3ad2f90958fe74623254f226fe283e0bb9289b
- add vendor path and namespace retrieval methods by @OoBook in https://github.com/unusualify/modularous/commit/38d55a131f511cbbe9460454a6b97f55b90e115d
- add command to generate model traits by @OoBook in https://github.com/unusualify/modularous/commit/4ceea130128d66069f15b20d1a10e7cc70e5cb58
- add command to generate repository traits by @OoBook in https://github.com/unusualify/modularous/commit/bc4ba0be4d94ba5fa88b125a423fe22c33a21107
- add command to create modularous features by @OoBook in https://github.com/unusualify/modularous/commit/b7b4a2a9554607e367c1c59c01fd376b8e88e585
- add command to flush Modularous caches by @OoBook in https://github.com/unusualify/modularous/commit/28419f5774689d298f8f8c1201a045cdf4ede33a
- add cache management methods by @OoBook in https://github.com/unusualify/modularous/commit/ce3490b6718fe4a51b4140d10c4fccedb9625aa6
- enhance file preview layout and add date display by @OoBook in https://github.com/unusualify/modularous/commit/095ff9609d23e2246a5ac904fb5dd0197a33a9fa
- add user relationship method for authorized entities by @OoBook in https://github.com/unusualify/modularous/commit/0950d6ac24b1a5be190a4a8271c8f1160a28900a
- add created_at field to mediableFormat method for enhanced file metadata by @OoBook in https://github.com/unusualify/modularous/commit/e8cb26d8b536812b549f5dc4454523de37873b5e
- add modularous input formatting functions for enhanced input management by @OoBook in https://github.com/unusualify/modularous/commit/07b23841d709ce51d8c3d7c74435bb195fa84f1c
- :sparkles: add new chat and chat_messages configurations for modularous by @OoBook in https://github.com/unusualify/modularous/commit/730b9cfca0bf65a79e7528e57cb5c9db79b11bb6
- :sparkles: - Introduced 'chats' and 'chat_messages' entries in the tables configuration to support modular chat functionalities. - This addition enhances the application's capability to manage chat-related data, aligning with recent modularous improvements. by @OoBook in https://github.com/unusualify/modularous/commit/b3e859e20e3ca51847086a4f9b2407e4d113d8f5
- add new theme styles and SVG icons for unusual theme by @OoBook in https://github.com/unusualify/modularous/commit/79e178bfa84838191046e4f44da4eeab63be1145

### :wrench: Bug Fixes

- remove applyCasting error return null by @OoBook in https://github.com/unusualify/modularous/commit/1c135ebc079e5183848f969d66e31a0acbaf3023
- remove applyCasting error return null by @OoBook in https://github.com/unusualify/modularous/commit/62b5cb5fcf88feace64e4fab596583938d88e261
- refine company registration logic to ensure proper redirection based on user status and route checks by @OoBook in https://github.com/unusualify/modularous/commit/b9ce789b1ca297b0ec337bd000a60c6ca05d7a84
- remove unnecessary logging in loadLocaleMessages function by @OoBook in https://github.com/unusualify/modularous/commit/9627deece243075536c357986096feef65c8227a
- improve TRANSLATIONS check in loadLocaleMessages function by @OoBook in https://github.com/unusualify/modularous/commit/6b6d332acefcfe9b4cab27aeed2ac3a6a14e37be
- set fixed namespace value in configuration by @OoBook in https://github.com/unusualify/modularous/commit/68b050610f87bdcfa074b5d93e9d432c9c50dc41
- enhance cache management in ModuleMakeCommand, ModuleRemoveCommand, and RouteMakeCommand by @OoBook in https://github.com/unusualify/modularous/commit/b90533b2ec6099c8c9d7d4d7a0eb28fd2bc4e75b
- correct duplicate grey-lighten-6 color definition by @OoBook in https://github.com/unusualify/modularous/commit/c9d8d7e8160ba95f8748932bf2c2b63715878a3a
- update import paths in vue-component.stub for correct module resolution by @OoBook in https://github.com/unusualify/modularous/commit/3a698d9a31581234ab3dea965f8a04a8938cc23b
- correct import paths in v-input-chat test file by @OoBook in https://github.com/unusualify/modularous/commit/9c7d604c84144c24f409c04152f0bfc4cdd61e88

### :recycle: Refactors

- remove unused modal and permission handling functions to clean up code by @OoBook in https://github.com/unusualify/modularous/commit/5f622aa09bbff0190a3d76566695f490498def60
- clean up layout file by removing commented script tags by @OoBook in https://github.com/unusualify/modularous/commit/9a7df0a13249616b74240f898ee2aae552df7dd4
- remove commented-out font and CSS preload links by @OoBook in https://github.com/unusualify/modularous/commit/bdf75e7c367d234558b64ccdcc7a2bf2e9242f5a
- remove commented-out pre-scripts section by @OoBook in https://github.com/unusualify/modularous/commit/cd58b26f28f9e4770392a762b259aa63f3111735
- simplify input configuration and hydration methods by @OoBook in https://github.com/unusualify/modularous/commit/5eb635cfecd4d28337fc29d9ebcf03947878ac56
- streamline input hydration process by @OoBook in https://github.com/unusualify/modularous/commit/e40cae8de35dae17cd085eb4a636df17155f5a91
- clean up unused action comments by @OoBook in https://github.com/unusualify/modularous/commit/1cfcb2060c56f403aae351d42a2e46acebe4aaea
- update file handling structure and improve clarity by @OoBook in https://github.com/unusualify/modularous/commit/fd61ab2e75f7f8e2a3024627f63e9903f060ca16
- streamline current user data handling by @OoBook in https://github.com/unusualify/modularous/commit/95ae763093e230e9b7041ddb27389a7cb235ca18
- improve user profile retrieval logic by @OoBook in https://github.com/unusualify/modularous/commit/99db6dda8764237edc5a013054b41347e2b76dd5
- update model and migration command syntax by @OoBook in https://github.com/unusualify/modularous/commit/cc7c208961c0f9c3f5790706a2b1932122a9c0eb
- update command signature and remove deprecated methods by @OoBook in https://github.com/unusualify/modularous/commit/cb524b58811ac6f2708653b2cf89f1792ad6b2d0
- update command signature and enhance user feedback by @OoBook in https://github.com/unusualify/modularous/commit/443925db2b09a9843cd0b1c63d47e40b1ea97660
- remove debug statement and clean up code by @OoBook in https://github.com/unusualify/modularous/commit/5d6e4f1ecff6141249b8d25ab5928dc9363caff5
- update command signature for consistency by @OoBook in https://github.com/unusualify/modularous/commit/d753d26492f9a8aa50dbceab139ecc5fa991b6a4
- comment out cache flush call in flushModuleCache method by @OoBook in https://github.com/unusualify/modularous/commit/4b858016f386db268d2ebfba754ccb43c1f2729f
- update cache configuration to use environment variables by @OoBook in https://github.com/unusualify/modularous/commit/5182d48ba4bb7216162b4b4ae8ac30561e292115
- update cache configuration logic by @OoBook in https://github.com/unusualify/modularous/commit/84ffc2e132b1a30e534e8b1eed4c8823e2853051
- enhance file handling and slot integration by @OoBook in https://github.com/unusualify/modularous/commit/f8ad80cd12eb6462fc8e41fb5f9b312d80b9ae84
- improve layout and structure for version display on superadmin user by @OoBook in https://github.com/unusualify/modularous/commit/7975c1c7c3b8cf6f9064ebf382f961acf6f2af0b
- update $log method to return log output by @OoBook in https://github.com/unusualify/modularous/commit/526d42b76f519133cc786d43f810e713922d1efa
- rename command and add alias by @OoBook in https://github.com/unusualify/modularous/commit/d47ef76c23f46e5f79c915bb5ce5ec97bde08d24
- replace default input retrieval with modularous function by @OoBook in https://github.com/unusualify/modularous/commit/5aa785eb12ac0d3adfc3b758c83fca0d3473bc29

### :memo: Documentation

- update repository URLs and branch naming conventions by @OoBook in https://github.com/unusualify/modularous/commit/9bf9a5d71516fc8b743848f6796a21a5c6579409

### :lipstick: Styling

- add getShowFields comments by @OoBook in https://github.com/unusualify/modularous/commit/7e1cd4c4c82a31279567d4848802df9c8899255a
- remove unused code and clean up component structure by @OoBook in https://github.com/unusualify/modularous/commit/14fb498602c80db9a258a93c26211028e95b1dd1

### :green_heart: Workflow

- :construction_worker: add new issue workflows by @OoBook in https://github.com/unusualify/modularous/commit/aea5272a4d6670aa8534423ddeecef30a69e590b
- :bug: fix getting the severity value and use $GITHUB_OUTPUT due deprecation warnings by @OoBook in https://github.com/unusualify/modularous/commit/dbb4589db6c5ff1d2a536580704ac60883ba752a
- :green_heart: add github-issue-parser action by @OoBook in https://github.com/unusualify/modularous/commit/e6382e63f146142b91194e3e4650827af758834f
- :bug: remove BODY print by @OoBook in https://github.com/unusualify/modularous/commit/1ffc2331dc2b924bef2f4d6722ebb1d60d70f680
- :bug: comment out template-path parameter by @OoBook in https://github.com/unusualify/modularous/commit/e8ac93f6f8ef2fcecf78a3a69131f20619595ca1
- :bug: add actions/checkout by @OoBook in https://github.com/unusualify/modularous/commit/34e62b9f0012c58c5bba9407779b2ac6505b0d6d
- update create-issue-branch.yml by @web-flow in https://github.com/unusualify/modularous/commit/433cb727c59b6e2c763f24c7f66a80dddd24a6c0
- update create-issue-branch.yml by @web-flow in https://github.com/unusualify/modularous/commit/5b417a0238cab06d5afff7f633f310ae6b0550d7
- update create-issue-branch.yml by @web-flow in https://github.com/unusualify/modularous/commit/5c3316a707326fd98d9a8fa920f20a548b39f2b9
- update create-issue-branch.yml by @web-flow in https://github.com/unusualify/modularous/commit/f5055fce9198f419b20a9d93d89ba94fcfffced2
- :bug: add permissions by @OoBook in https://github.com/unusualify/modularous/commit/a1a99c4a4d3dc4f155a5e0501e91991d95ad9f14
- :bug: add create-issue-branch action by @OoBook in https://github.com/unusualify/modularous/commit/f8784310eb4f21c2bdeca1bcdfec0cb2ffd66325
- :bug: add debug prints by @OoBook in https://github.com/unusualify/modularous/commit/081d67682620fea503f9b517764c423fa5678d4b
- remove LOG_LEVEL environment variable from create-issue-branch workflow by @OoBook in https://github.com/unusualify/modularous/commit/14b2ee649f3a60b31ad83219464fe36f0300a2d0
- refactor create-issue-branch workflow to directly configure Git and create branch by @OoBook in https://github.com/unusualify/modularous/commit/f7fc925be0907363092b81fee1d9881b14203a4e
- update create-issue-branch workflow to use action for branch creation by @OoBook in https://github.com/unusualify/modularous/commit/c92c3359cab9f8aaecb8c7e1498295b7b9f27110
- streamline create-issue-branch workflow by directly configuring Git and enhancing branch creation process by @OoBook in https://github.com/unusualify/modularous/commit/dec75b55a1485440a903dd73f5889a9f0c4ba107
- add 'issues' permission to create-issue-branch workflow for enhanced issue management by @OoBook in https://github.com/unusualify/modularous/commit/0a69358515023398f30a9d2f9af842da47408853
- enhance create-issue-branch workflow to include SHA in branch description for better traceability by @OoBook in https://github.com/unusualify/modularous/commit/8f7185280cf8d01fe2030e8e9728bbbdbb5f6d51
- refactor create-issue-branch workflow to use GraphQL API for branch creation and improve linking to issues by @OoBook in https://github.com/unusualify/modularous/commit/3e53fb5acffff256274bbe919f77d91e845996b7
- update create-issue-branch workflow to include source branch SHA in GraphQL mutation for improved branch linking by @OoBook in https://github.com/unusualify/modularous/commit/f9a2e713d28c35d8c50df20a57dd7e979d4424a1
- enhance create-issue-branch workflow to retrieve and utilize repository and issue Node IDs for improved branch linking by @OoBook in https://github.com/unusualify/modularous/commit/695821b3f58caf087efabad50d4d151031908ffc
- clarify issue number handling in create-issue-branch workflow by converting it to integer for GraphQL query by @OoBook in https://github.com/unusualify/modularous/commit/ec6ee320672173cb628b2568298fa72f447b9784
- add PR template checker to enforce conventional commit messages and update checklist on experimental mode by @OoBook in https://github.com/unusualify/modularous/commit/6c32e3b7461f7056af13c5ffd4e4581a169c822f
- enhance release workflow to check for Vue changes, install dependencies, and build artifacts before committing updates by @OoBook in https://github.com/unusualify/modularous/commit/a205e964bcc20e8c3aad4f3ab128525bb2e3f103
- add PHPStan configuration and GitHub Actions workflow for static analysis by @OoBook in https://github.com/unusualify/modularous/commit/a4db3c4eb485f3906b0c9aa9b0910c4ced998826
- update pr-template-check.yml by @web-flow in https://github.com/unusualify/modularous/commit/08f720b69e26a229fa74e3b44a08d30ccf850253

### :beers: Other Stuff

- :art: upgrade issue templates by @OoBook in https://github.com/unusualify/modularous/commit/a1fb332b048048ac2a3272b461c4d5b94d61da15
- simplify issue templates by removing title input fields and updating descriptions for clarity by @OoBook in https://github.com/unusualify/modularous/commit/62d6117826925663f78eda5bef1d186c4396e85b
- update pull request template to include a checklist and refined types of changes by @OoBook in https://github.com/unusualify/modularous/commit/7ebbb8781308803c8dfb1942aa4ba1a05d113f3e
- update vitest and related packages to version 2.1.8 by @OoBook in https://github.com/unusualify/modularous/commit/23efe989313049b0f8a51a8f91ddb69b6ce60732
- add anonymous image for user profiles by @OoBook in https://github.com/unusualify/modularous/commit/837e409c3e848b7f60616a2a7719e9c463562043
- add success message after command creation by @OoBook in https://github.com/unusualify/modularous/commit/fe73d1f7d855781cff39f216cab93493051b8378
- hide command from console output by @OoBook in https://github.com/unusualify/modularous/commit/3686c8506eaa8e4abf298df2442ccddc21b19c59
- add moment.js dependency by @OoBook in https://github.com/unusualify/modularous/commit/368d6e4f0f4fe6064e138f28840a7ed7257262d8
- add invokeRule function for enhanced rule processing in experimental mode by @OoBook in https://github.com/unusualify/modularous/commit/ccbca4747431491f721dc9c63a9c6d491be6d094
- enhance initialization script with moment.js and pluralize imports by @OoBook in https://github.com/unusualify/modularous/commit/58af445dcfdc4dd61bac86cff6c2f4019c841612

## v0.21.0 - 2024-12-09

### :rocket: Features

- :sparkles: add __extractForeignKey helper to find foreign name of a model by @OoBook in https://github.com/unusualify/modularous/commit/ed7054f570b06ead264a2c31cd769cfaf5a10360
- :art: add unique feature to the repeater input by @OoBook in https://github.com/unusualify/modularous/commit/dfbc4645ae532c873fc1a39213651d7ee2a479e1
- :recycle: add getTranslationLanguages method to get system languages by @OoBook in https://github.com/unusualify/modularous/commit/ee050e912237baf0bfa598ba9723d354220f84e4
- :art: add modelValue setter to formatSet if the inputPropFormat matches modelValue or model by @OoBook in https://github.com/unusualify/modularous/commit/e3de2df6f85289fcffbe7a0fc7208a23e8d62e9c
- :art: add trigger feature to TabGroup to update inner repetitive schemas by @OoBook in https://github.com/unusualify/modularous/commit/c51de594b6b47014eaef3d5daca4d5c6205273c5
- :sparkles: add ue-filepond-preview experimental component to show file/image details by @OoBook in https://github.com/unusualify/modularous/commit/7f4414c9da448ac9740d5c6d9d68dca0ea120eaa
- :art: add noRecords attr to prevent making query to db by @OoBook in https://github.com/unusualify/modularous/commit/d59893e631a5a3cd763e3345f3c0803a0cd16e9b
- :art: add a appends check of the model to get right columns by @OoBook in https://github.com/unusualify/modularous/commit/447b735653716b44a92af5d57e68b799b9e9bacc
- :sparkles: add new tag input connecting to Taggable structure by @OoBook in https://github.com/unusualify/modularous/commit/3fa0360e85599ed10556d959c7e17a286b590cec
- :art: add allowedRoles feature to the customRow attribute by @OoBook in https://github.com/unusualify/modularous/commit/a5a630a1f3eec0f817971021de0206cb8839dc14
- :art: add new headerCenter and top slots to Form component by @OoBook in https://github.com/unusualify/modularous/commit/4391ce4300f7e8c4e3708d404d470bb8b9976374
- :sparkles: add new viewOnlyComponent feat to inputs when it is in the nonAllowed roles by @OoBook in https://github.com/unusualify/modularous/commit/6213baec76ff9f6ff19731127d3f934c87eb95cb
- :sparkles: add form action feature into Form by @OoBook in https://github.com/unusualify/modularous/commit/e4a79f49fab271010acc43dc570de606716887c6
- :art: add form-top slot into Table by @OoBook in https://github.com/unusualify/modularous/commit/ba6aae5b2bc1608915dc59faa2f6f218d8e673a4
- :art: add hasVatRate into Price by @OoBook in https://github.com/unusualify/modularous/commit/fb5bf09b5aa8582682cb42f995be0e08d73415d8

### :wrench: Bug Fixes

- :bug: fix auth pages form widths && buttons && translations && colors by @gunesbizim in https://github.com/unusualify/modularous/commit/558f08e20a106fdd70c416fb5e4f41718da6df7c
- :bug: fix third party button spacings by @gunesbizim in https://github.com/unusualify/modularous/commit/3e16c70d374e62695655e542c16609878bb97886
- :bug: fix spacing on right side of auth pages by @gunesbizim in https://github.com/unusualify/modularous/commit/9df49c0dbd63138fddb51d1cb2a84f37afc710eb
- :bug: fix custom font-size for description text on the right column by @gunesbizim in https://github.com/unusualify/modularous/commit/ad72001d231c27266cf8066f1d738b7cc55413de
- :bug: fix colors for future used gray-color-lighten-1 for third party auth buttons && removed unnecessary scss by @gunesbizim in https://github.com/unusualify/modularous/commit/6ee1fd28f29e25090fae177e3d366210196c3223
- :bug: fix forgot password auth page create account button link by @gunesbizim in https://github.com/unusualify/modularous/commit/566836964db5736a08a2ac095232dc3daccbc0b2
- :bug: fix stateable hydrate if default_states array of strings convert to array of objects by @gunesbizim in https://github.com/unusualify/modularous/commit/c110404b04ea3cbbdba6ea6531fdc60c0c33d431
- :bug: fix usage of padding on auth forms && remove unnecessary scss by @gunesbizim in https://github.com/unusualify/modularous/commit/6207a9617614758a4676bd56bd517eabe70b7571
- :bug: move pivotModel generation to back of the additional models by @OoBook in https://github.com/unusualify/modularous/commit/d1784fe8f261f47c6455ae12b1a266e697488aae
- :bug: add default true value to active column of translation table migrations by @OoBook in https://github.com/unusualify/modularous/commit/6476b56ddf5a2dd10dadb3c8eecf24633be12819
- :ambulance: change lastModel as lastIndex for chain methods of pivot tables and fix calculation of schemas number on pivotableRelationships by @OoBook in https://github.com/unusualify/modularous/commit/72f32c0b7632e7e65e45d20bb05753a9fdfb8497
- :art: append ':' and '_' characters into pattern to cast strings including these by @OoBook in https://github.com/unusualify/modularous/commit/d8742188172a1eb542aa95855c2c3224eceea96f
- :bug: add disabled styling into checklist's checkboxes by @OoBook in https://github.com/unusualify/modularous/commit/445d80e17609e8a18e8f977ca7e8340c81ad7b55
- :bug: protect previous value disabled prop by @OoBook in https://github.com/unusualify/modularous/commit/c57b317cecbd1db3239051886cc9736be7d7c7e4
- :bug: prevent the formatSet on loading item by @OoBook in https://github.com/unusualify/modularous/commit/35948d8f4c6051085ba54f468d1bfbcbbaee7517
- :bug: add parenthesis if the element is third on formatPreview. by @OoBook in https://github.com/unusualify/modularous/commit/aeb0217a67484fd4b5213fcecd9b3f491e13c542
- :art: change padding of table's titles and change flex behaviour of iterator's actions by @OoBook in https://github.com/unusualify/modularous/commit/0282d184ae9b7a512bc6604886ae4be2edea7c69
- :bug: remove ml-auto due to lead to confusions on putting multiple items on the slot by @OoBook in https://github.com/unusualify/modularous/commit/914022075fb93fbbca16e0867dd3313fc02317c8
- :art: add valueChanged parameter as true on handleEvents by @OoBook in https://github.com/unusualify/modularous/commit/c36be9ed3c5a70bd6568628fb1cc692e9416d622
- :bug: put action items d-flex wrapper at right slot of Title.vue by @OoBook in https://github.com/unusualify/modularous/commit/5bdf8dc09163324076cb937724ff07f4999f8e83
- :bug: add a handle to pass fallthrough attributes by @OoBook in https://github.com/unusualify/modularous/commit/498b449b6762770999ca746f34807f45d92478a5
- :bug: remove coloring the sheet inputs by @OoBook in https://github.com/unusualify/modularous/commit/21e5da144fb930fb254d8c96753578b14b5b84ca
- :bug: set originalDisabled to disabled prop by @OoBook in https://github.com/unusualify/modularous/commit/800bf56006718402b52d661efa123ab02e5e1b81
- :art: return respond file type on filepond preview response by @OoBook in https://github.com/unusualify/modularous/commit/ad3bfd8f81d10e6c877b4ce13c15b02856d40ae3
- :art: return records by serializing on translation models at list method of repository by @OoBook in https://github.com/unusualify/modularous/commit/9459eee32edac86589d286fd82e72fd3724d61bb
- :bug: remove column fetching on repository list from cascade inputs of morphTo by @OoBook in https://github.com/unusualify/modularous/commit/1f43c024fd35791547da0df4b7154d0d2b1e7606
- :bug: remove __log helper by @OoBook in https://github.com/unusualify/modularous/commit/afcfc21b8ae54bcad901944fd1a2203df200bc6b
- :bug: handle json nested fields with '->' character at first level. by @OoBook in https://github.com/unusualify/modularous/commit/04007806539994e1b5a08c084c9cccdc239aaceb
- :bug: fix unnecessary density usage && translations by @gunesbizim in https://github.com/unusualify/modularous/commit/a88d287b272913c3af5d798248d3b83925a638bc
- :bug: fix restore unique_table rule by @gunesbizim in https://github.com/unusualify/modularous/commit/0fc24da2177e488160a74be9f393148218e4fd42
- :bug: fix button density to default on auth pages by @gunesbizim in https://github.com/unusualify/modularous/commit/a30b6b7742cc96200f147fc46634ed35804b4fba
- :bug: fix moved widgets.php to /merges && removed publishing code from LaravelServiceProvider by @gunesbizim in https://github.com/unusualify/modularous/commit/48518ca405ae575613763c7607449f7792a95731
- :bug: use applyCasting on string elements case by @OoBook in https://github.com/unusualify/modularous/commit/5eccdce8a30fb470b0a0e817825c046cdb966cab
- :bug: add class hidden into col and fix disabled case by @OoBook in https://github.com/unusualify/modularous/commit/369aa1157009c993cf774906f243d7916f732c2a
- fix payment price relationship by @OoBook in https://github.com/unusualify/modularous/commit/6537c43d46995062d8bd05b6066ccd060bc1f33f
- :bug: call triggers on changing fields by @OoBook in https://github.com/unusualify/modularous/commit/4eb2b2b3943775ae52d10576b61d89a22fc227bd
- :bug: reformat modelValue of Repeater if not formatted by @OoBook in https://github.com/unusualify/modularous/commit/508d5765b1041f658dbd81a3909aa1fdfb69756a
- remove isArray check from RecursiveStuff by @web-flow in https://github.com/unusualify/modularous/commit/07fd65de93106ca99e82d401da4df3e22194957f
- remove extra if check from RecursiveStuff by @web-flow in https://github.com/unusualify/modularous/commit/456ee3c5db06f37e553adaf8b53518711ee54494
- :art: remove showSelect from users except superadmin by @OoBook in https://github.com/unusualify/modularous/commit/f454598b9d869929b50c819d97be747f40aebd87
- remove applyCasting error return null by @OoBook in https://github.com/unusualify/modularous/commit/067c4428b5830714be1471ea33d54409794de140
- remove applyCasting error return null by @OoBook in https://github.com/unusualify/modularous/commit/7c6005f6683e115c226c820887d89e97aa9f676d

### :recycle: Refactors

- :recycle: refactor dashboard to use connector instead of controller by @gunesbizim in https://github.com/unusualify/modularous/commit/8e948a77ad59a811930d73a4cb1f8d30d2f32e63
- :recycle: refactor dashboard creation logic by @gunesbizim in https://github.com/unusualify/modularous/commit/5cabce6d94a1997186c11e5b72c4a4850e2aef5f
- :recycle: refactor of BoardInformationPlus usage and component by @gunesbizim in https://github.com/unusualify/modularous/commit/047686993309061345e1083f95e9b07a2fe371ec
- :recycle: refactor remove unnecessary variable and log by @gunesbizim in https://github.com/unusualify/modularous/commit/ba3b90dc52efabf6e3217da20d386de4fb6b96d8
- :recycle: refactor HasStateable trait and update hydrate accordingly by @gunesbizim in https://github.com/unusualify/modularous/commit/30e71f2360aae1b11ac754b94ff944e124ecce48
- :recycle: refactor dashboard table && boardInformationPlus && controller && feature UWidget by @gunesbizim in https://github.com/unusualify/modularous/commit/34b3b18052dbce6dfb95e56547e6faf32b5fa1bc
- :recycle: refactor publish.php && LaravelServiceProvider.php && create widgets.php for publish by @gunesbizim in https://github.com/unusualify/modularous/commit/5bfc6ba9caf6bea9b38df761bd49f6233ef9488f
- :lipstick: update button/input sizes acc. to density by @OoBook in https://github.com/unusualify/modularous/commit/df07aa7c1346de4296d6e3f79de377468e9937f0
- :recycle: update comparison table input to show comparator values and add active highlighted by @OoBook in https://github.com/unusualify/modularous/commit/85bc22dd0bef68424ae0662c1cbda1dbf0659cef
- :recycle: use getTranslationLanguages method on getModel by @OoBook in https://github.com/unusualify/modularous/commit/a2b951b3c12dbf5db7bfde9c9367fecd1d9c640e
- :lipstick: update the styles as in the design. by @OoBook in https://github.com/unusualify/modularous/commit/7749d7166fe02bdb46aaa3f0f3188267bd91fcc3
- :recycle: remove the autocomplete styling by @OoBook in https://github.com/unusualify/modularous/commit/0a8ccd7225c1c48fe2da64abb58771363e4a0381
- :recycle: change filepond.preview binding as uuid by @OoBook in https://github.com/unusualify/modularous/commit/28b1f688cc7428bea45777fa8f0f5db454b63b8f
- :recycle: prevent making query to db on MorphTo input type by @OoBook in https://github.com/unusualify/modularous/commit/9d67730ecb02b68d1668d0028d14dd3a691717c4
- :recycle: use isTranslationAttribute method of hasTranslation instead of using in_array helper by @OoBook in https://github.com/unusualify/modularous/commit/050160120e737e25d5cf0941b42433d60867c133
- :recycle: remove Model type from morphTo relations by @OoBook in https://github.com/unusualify/modularous/commit/e40b7ed467f3ba299eedb04c51f0c5b86b8c7660
- :art: add applyCasting method to use it by @OoBook in https://github.com/unusualify/modularous/commit/732691fe4bbdcf87d3bde93191d8ada320344b9d

### :lipstick: Styling

- :green_heart: update space and new lines by @OoBook in https://github.com/unusualify/modularous/commit/d3807262217b3d8ce72ef680a519ba03562d53a1
- :green_heart: remove dd's from formatWiths by @OoBook in https://github.com/unusualify/modularous/commit/14910ff952a037d6bacdb439eaadec44369bcfb1
- lint coding styles by @OoBook in https://github.com/unusualify/modularous/commit/6155bb0d7ea49038fd4667cc144677b163e14e6f
- lint coding styles by @invalid-email-address in https://github.com/unusualify/modularous/commit/0e9ece78a07a17ecc41d10bcaf6a17a3326ede7c

### :white_check_mark: Testing

- :test_tube: add UEConfig to checklist test by @OoBook in https://github.com/unusualify/modularous/commit/24c5c3f9792fd03c04ad163f8648a104b462281b
- :test_tube: change vuetify plugin as UEConfig by @OoBook in https://github.com/unusualify/modularous/commit/9a36e606ab6d2c1befa1510695efb8259f6e05aa

### :package: Build

- :building_construction: add new v0.20.0 build by @OoBook in https://github.com/unusualify/modularous/commit/38e50a43e9a8ed2c043f4a98fb8d44e8d12b63cb
- :building_construction: add new v0.20.1 build by @OoBook in https://github.com/unusualify/modularous/commit/efa248c330bafe7b933b903a434df991067aa3fd
- :building_construction: add new v0.21.0 build by @OoBook in https://github.com/unusualify/modularous/commit/aad79b04ee71da81f2b80dd9565478a4ad3dc9a1
- :building_construction: add new v0.22.0 build by @OoBook in https://github.com/unusualify/modularous/commit/766831ef9a5f0537b17a57c0c2f6a0d26365a1cc

### :beers: Other Stuff

- :lipstick: change edit icon content by @OoBook in https://github.com/unusualify/modularous/commit/a5ead38ea263453f334c94dd701b9a773a8444b7

## v0.20.0 - 2024-12-09

### :rocket: Features

- :sparkles: add currency exchange on payment form by @gunesbizim in https://github.com/unusualify/modularous/commit/767a9424fc644e4602c8d985e53a6138b9b81970
- :sparkles: add custom payment service button styling to payment services by @gunesbizim in https://github.com/unusualify/modularous/commit/6235a891e86add90229eae3f6b6521529fccafd4
- :sparkles: add cardType to payment services by @gunesbizim in https://github.com/unusualify/modularous/commit/0577d5f0c34f6ad256553753dea82b40db1cde5d
- :sparkles: add currencyServices relation to payment for available currencies with specific payment method by @gunesbizim in https://github.com/unusualify/modularous/commit/03c24c80b36a9458560adf23bad9cfee911f436b
- :sparkles: add align class to title by @gunesbizim in https://github.com/unusualify/modularous/commit/ecdcc61e68d867dbab7ecf81b0407c92e9fd41b1
- :sparkles: update ui for currency selection on payment form by @gunesbizim in https://github.com/unusualify/modularous/commit/da21b0cdb6f5bc372dcb8d5470f000d0ff640d90
- :art: add label to RadioGroup by @OoBook in https://github.com/unusualify/modularous/commit/ba66fc1ce5e24d0b19d5c83f76d8adb015f507fc
- :art: handle divider case on hydrating input by @OoBook in https://github.com/unusualify/modularous/commit/1e8e9bb1fbdf078482fc50f55a39e89f0eed6b15
- :art: add a feature shwoing labels as headers like a table by @OoBook in https://github.com/unusualify/modularous/commit/58cc1558086fd5f686aaf9341657fb1819f45a4d
- :art: add default form attributes and module specific form attributes by @OoBook in https://github.com/unusualify/modularous/commit/6d62a57950b919376accc5b4282654eed6f12e16
- :art: add new translated input helpers by @OoBook in https://github.com/unusualify/modularous/commit/9e3f7847418ba8872ca20be01499d7b30b12e493
- :triangular_flag_on_post: add locale hook to handle languages, active language by @OoBook in https://github.com/unusualify/modularous/commit/33302ebbc3f869d5bb016111a1dcfe698d564ee9
- :art: add a translation info to append of label by @OoBook in https://github.com/unusualify/modularous/commit/106df083bc64abf5900baa8278177f23c2d84591
- :lipstick: add coloring patterns by @OoBook in https://github.com/unusualify/modularous/commit/f1037558414eabcf0d2868a5c11c83be8d7c3a91

### :wrench: Bug Fixes

- :bug: fix selected input styling && design issue by @gunesbizim in https://github.com/unusualify/modularous/commit/bce51c7a0efedc655275f192ab880b167fa4a042
- :bug: fix hasDivider prop && title align- and text- prop by @gunesbizim in https://github.com/unusualify/modularous/commit/d5e58b6b726e71cfc3d0615cf8eef7e28b2b74f9
- :bug: fix the flexibility of right slot of title by @OoBook in https://github.com/unusualify/modularous/commit/19f3c6cb5cc7b740fd9601ae6bc82ccadad03ab5
- :bug: fix ux issues of last step form's summary by @OoBook in https://github.com/unusualify/modularous/commit/0cb1b2f6735f4581a23b6725e1a9d177b7679965
- :art: update profile structre acc. to new theming structure by @OoBook in https://github.com/unusualify/modularous/commit/209dded0df42b6558a87dbb0849f9a9151654b9a

### :recycle: Refactors

- refactor for conflict by @gunesbizim in https://github.com/unusualify/modularous/commit/ef418c7d1b4be2f19b2e6721cfa846cd8069d8ed
- :recycle: refactor conflict fix by @gunesbizim in https://github.com/unusualify/modularous/commit/7faa14caca1c79a307b0c35719394e8c1542a1dd
- :art: arrange wrap/group's title/subtitle styling by @OoBook in https://github.com/unusualify/modularous/commit/e61ac751303805df875d31141754701f5bffcf66
- :bug: remove emphasize from text display sub text by @OoBook in https://github.com/unusualify/modularous/commit/0f3784af53fec2ee3fae341c5096b607ceb52cb1
- :recycle: upgrade advanced filter menu by @OoBook in https://github.com/unusualify/modularous/commit/a72f5365f8d136bdfb14d1c0c99e1afde797f55f

### :white_check_mark: Testing

- :test_tube: change vuetify plugin as UEConfig by @OoBook in https://github.com/unusualify/modularous/commit/369c390c012020af2f4961be48f645288e12c8c9

### :package: Build

- :building_construction: add new v0.20.0 build by @OoBook in https://github.com/unusualify/modularous/commit/27ea59d21b988c7b1266fe5e135baaada1306c42

### :beers: Other Stuff

- :children_crossing: remove job title from user profile by @OoBook in https://github.com/unusualify/modularous/commit/76af300a737611ea36f5ba6fc7971ac3a2db727f

## v0.19.1 - 2024-11-27

### :wrench: Bug Fixes

- :ambulance: hotfix HasStateable duplicate state issue by @gunesbizim in https://github.com/unusualify/modularous/commit/2faf67ff012f102f915ab5b8e3446d7e4dc23860
- :ambulance: hotfix HasStateable duplicate state issue by @gunesbizim in https://github.com/unusualify/modularous/commit/48b61df2d2e31afebc81a3ede0f056d0f865768b

### :lipstick: Styling

- lint coding styles by @invalid-email-address in https://github.com/unusualify/modularous/commit/e1058b90e52bc5ddfd887185859011157e00ebd5

## v0.19.0 - 2024-11-20

### :rocket: Features

- add pint command end of generating route by @OoBook in https://github.com/unusualify/modularous/commit/2b027d80facb77f769dc4bffd865fa1a61ca2869
- prepare snapshot fields before save by @OoBook in https://github.com/unusualify/modularous/commit/30539b7b46dec40c06b8314a115f5b22d2d367f7
- :art: add controls-position feature top/bottom and also use it on mobile by @OoBook in https://github.com/unusualify/modularous/commit/07c8481f02d26d7856d341b7a6327934607c227d
- add controlsPosition prop to useTable by @OoBook in https://github.com/unusualify/modularous/commit/1a6541bcb25c9cb1bf32666dba375dcfda761dab
- :art: add assignability of an array or string value into PaymentTrait by @OoBook in https://github.com/unusualify/modularous/commit/39cd9bea42c761e0e6e4453843ecb8bf340a6c42
- :bug: add flex-wrap feature to PropertyList by @OoBook in https://github.com/unusualify/modularous/commit/36375802eef35c52bd37580b15fe4a9f1259f592
- :art: add final form structure into StepperForm by @OoBook in https://github.com/unusualify/modularous/commit/fdee92dbc2d07e31bf44d7a1dca82bd38e3a46a4
- add $headline root helper by @OoBook in https://github.com/unusualify/modularous/commit/e71ff251cffc00fb24201a88ac9930e048ec5b04
- :art: put columnStyling on ConfigurableCard by @OoBook in https://github.com/unusualify/modularous/commit/90e39babc9284276c422d90e86c76552e7063037
- :art: add morphTo filter for unspecific parents taking the type from repository by @OoBook in https://github.com/unusualify/modularous/commit/d7858f6dbcb620f05ee2181f2996bbf4e98f8f6a
- :art: develop editable/creatable input structure with hidden/boolean cases by @OoBook in https://github.com/unusualify/modularous/commit/f5386adb12ef5433101cbdab2b9ee6f1bf660e80
- :sparkles: add wavy border mixins by @OoBook in https://github.com/unusualify/modularous/commit/f34b0d7fb6d598e89388e845eef8576d64ab4ad2
- :sparkles: add striped and highlighted features to comparisonTable by @OoBook in https://github.com/unusualify/modularous/commit/65322ec5719484e60c90467561c959235cf30c17
- :sparkles: upgrade checklist component by @OoBook in https://github.com/unusualify/modularous/commit/4a627af22d77228ed834a62a6ec7676f00cb5c91

### :wrench: Bug Fixes

- :adhesive_bandage: add ssl link of Roboto's font family by @OoBook in https://github.com/unusualify/modularous/commit/40c5f16c761d8556bab984b1bc2190aeaae22820
- :bug: fix table header translation when translation doesn't exist && refactor label translation by @gunesbizim in https://github.com/unusualify/modularous/commit/b1bca14418b7105e922775bfab8bbe309bffda00
- :bug: fix responsiveness of auth pages by @gunesbizim in https://github.com/unusualify/modularous/commit/7e4118f7a7fe082119dfd4538132ab32975c44d4
- :bug: fix responsive auth pages && auth page translations && profile translations && refactor manageForm by @gunesbizim in https://github.com/unusualify/modularous/commit/4de6ec08445a8ec6902cf49ecb8e3a781bf369da
- add snapshot attributes on modelMakeCommand by @OoBook in https://github.com/unusualify/modularous/commit/e94b0d6df2fe7e5e7f0b2dc40de1bdc6e16a8030
- :lipstick: change flex structure of window of stepperform by @OoBook in https://github.com/unusualify/modularous/commit/b4b414cedc735a625840d725042ee2a4cc5df6e5
- :egg: add custom components to UEConfig by @OoBook in https://github.com/unusualify/modularous/commit/9915615fca8a19c6f7993bd1f14c09cd4e2a98c3
- :bug: change isEditing condition as gt -1 by @OoBook in https://github.com/unusualify/modularous/commit/2c095f3ebef479c42f410016ea68d155c8faead3
- :bug: update forgotten lodash methods by @OoBook in https://github.com/unusualify/modularous/commit/2196265b5191a650dcc7483b9b5d1f935b86bb88
- :bug: add '|' character into pattern of $castValueMatch by @OoBook in https://github.com/unusualify/modularous/commit/bb63cf871b140c599134dabdcc4730d90f570c81
- :ambulance: update forgotten methods of lodash by @OoBook in https://github.com/unusualify/modularous/commit/68d40735fc556e19487217a9324dc28ff56a6e0d
- :bug: put underscore preserved_state variable due to confusion with other fillable by @OoBook in https://github.com/unusualify/modularous/commit/069eca879a83fa4fb5b7ea84a4e856d9fa68f1bc
- :bug: put element variable on itemAction instead of null item by @OoBook in https://github.com/unusualify/modularous/commit/0e5b527b467b8a84008c69f21d3703421638b6fc

### :recycle: Refactors

- :sparkles: add table header translations by @gunesbizim in https://github.com/unusualify/modularous/commit/a0c489ca021a01097cfde9abe2aa28cb979f2afa
- :recycle: refactor translateHeaders method to be more efficient by @gunesbizim in https://github.com/unusualify/modularous/commit/3a7066c94d217ea0f677913efa2506ec7f363e40
- add new HasPriceable trait using Oobook/HasPriceable and use it traits by @OoBook in https://github.com/unusualify/modularous/commit/c13ffd04146dad92edbcc32e8422b15c0a1699c0
- move configurableCard and propertyList components on the generics by @OoBook in https://github.com/unusualify/modularous/commit/ec7489972bc573afa4c5f08018ee2b947378f7b9
- :recycle: change stateable preview structure by @OoBook in https://github.com/unusualify/modularous/commit/61bfb83138fd86c728a7ae40d7f4008ffcb532d6
- :recycle: break down the Table into smaller composables by @OoBook in https://github.com/unusualify/modularous/commit/f01d226fc96417510e694169df2fd0772b467437
- :recycle: get lodash methods with underscore by @OoBook in https://github.com/unusualify/modularous/commit/6a6d4fc6c2d4ae51c7b71c052ab2c6b222354e22

### :lipstick: Styling

- lint coding styles by @invalid-email-address in https://github.com/unusualify/modularous/commit/b556f10de404c6b978be6a27ddd85821e05a96c0

### :white_check_mark: Testing

- remove snapshots of configurable-card test by @OoBook in https://github.com/unusualify/modularous/commit/b4b539ea5ca5a60491819dd6ce196f0eb6f3573c

### :package: Build

- :building_construction: add new v0.19.0 build by @OoBook in https://github.com/unusualify/modularous/commit/65e6ca423af02d9ee35c64d11a2d5e31497a6adf

### :beers: Other Stuff

- by @OoBook in https://github.com/unusualify/modularous/commit/4d56e6f05aed373bcb6f0d566cd33810e0eeeec2
- :technologist: add PressReleaseCardIterator as experimental by @OoBook in https://github.com/unusualify/modularous/commit/bf418a3cbaa8382435761c656fb73d7a7719d23d

## v0.18.0 - 2024-11-13

### :rocket: Features

- :sparkles: table updates by @gunesbizim in https://github.com/unusualify/modularous/commit/cd8e15bfdbedeab09a4ea7f0fe7576b6c29915b6
- :sparkles: pr fixes by @gunesbizim in https://github.com/unusualify/modularous/commit/f7807269828f6c3167ebc09d55575db3f4033598
- :sparkles: pr fixes by @gunesbizim in https://github.com/unusualify/modularous/commit/703d1ef50c8cdcd1d841da32667c2d6084705fe2
- :recycle: removes unnecessary checks by @gunesbizim in https://github.com/unusualify/modularous/commit/678ff5f7df010043746a8bd7c27fff700a09ac46
- :sparkles: add filterHeadersByRoles method into ManageUtilities by @OoBook in https://github.com/unusualify/modularous/commit/d825387e7bf323b661211dfe33180c83a9a0852c
- :art: add pushQueryParam helper by @OoBook in https://github.com/unusualify/modularous/commit/88c6a9f8ac101f22b6ae3c97f456db6798341093
- :art: add selectable columns feature by custom by @OoBook in https://github.com/unusualify/modularous/commit/95e134bbd7aa1a5b4fa5c5a0ffb28e90eb4bfa16
- :sparkles: adds color as table header option by @gunesbizim in https://github.com/unusualify/modularous/commit/c869fb7e6b7a8ae9888ee716a56cf188af500db7
- add slots for each segment on ConfigurableCard by @OoBook in https://github.com/unusualify/modularous/commit/f0e6932b40b87497a57781e481831574c82e52f6
- :art: add base_price set attribute into modelHelpers by @OoBook in https://github.com/unusualify/modularous/commit/c2472efa9547dd771341ec42ba5c31ff5a3431f9
- add payload watcher to resend request by @OoBook in https://github.com/unusualify/modularous/commit/3a3f5d636cb33a652f53ccb95e0ff385579eeaf1
- :art: add formattedSummary into notation util by @OoBook in https://github.com/unusualify/modularous/commit/6696c92e78f2a8a95716567c29f8c15e59f491ed
- add lastStepForm feature for addons by @OoBook in https://github.com/unusualify/modularous/commit/49e71a898ed21d496c9087dd4a953aaa94a9b8d6

### :wrench: Bug Fixes

- :bug: where a key is not defined but tried to accessed on retrieved by @gunesbizim in https://github.com/unusualify/modularous/commit/222c45697fe8b44eb383a9d67e1ae9a2b07d687e
- :bug: logic error fixed by @gunesbizim in https://github.com/unusualify/modularous/commit/beeb65374b177dc7ca46ee395e806d1bd3d8e7f0
- :bug: where initial_state is not set on model by @gunesbizim in https://github.com/unusualify/modularous/commit/0bcedc5d91243f18ddc3e398d247eb803a165607
- :recycle: refactor initial_state to defaultState and add defining default_state option. by @gunesbizim in https://github.com/unusualify/modularous/commit/7afee33d028f8a9602246089e8c80810699ac575
- :bug: fix the issue where initialState and defaultState are mixed by @gunesbizim in https://github.com/unusualify/modularous/commit/3c79184f0431b5d8d712cd2ff5a8fec3dd372110
- fix tr border radius of Table.vue and add merge config fields by @OoBook in https://github.com/unusualify/modularous/commit/7f92c6a476053a2579be3fd79a5ce18e97cfb2ae
- :ambulance: change isEditing variable from Number to Boolean by @OoBook in https://github.com/unusualify/modularous/commit/c06f8a21967824bd4afce6e2cb0eaa5618a2ba06
- fix serializeParameters recursive call by @OoBook in https://github.com/unusualify/modularous/commit/5a5645fb3a62e2135225a455943582d9031de6dd
- handle getSchema recursive if only its type is in [wrap,group,repeater, input-repeater] by @OoBook in https://github.com/unusualify/modularous/commit/a60aed228c9a51dc3aa971001132b20d7b6623d4
- add isEditing prop in order to pass Form components by @OoBook in https://github.com/unusualify/modularous/commit/22cc259a09ccef408bacea559d61097f70b775c3
- :bug: auth pages responsive design fixes with global button and input styling updates by @gunesbizim in https://github.com/unusualify/modularous/commit/65ada8085172ac4f96c237d6f4ebc926d002f77b
- :bug: fixed the issue where createOnModal is false for add button but still shown by @gunesbizim in https://github.com/unusualify/modularous/commit/7b8bd65fb6339048f266f678f0a42d106e08b700
- :bug: fixed phone input issues && labels && titles by @gunesbizim in https://github.com/unusualify/modularous/commit/d7659b76675e5bf6931250ab138b234e5eedf5df
- :ambulance: remove recursive lines by @OoBook in https://github.com/unusualify/modularous/commit/6d27391cd50e4b40543fbd9f7eb9cdaa8bc92f7e

### :recycle: Refactors

- :recycle: stateable structure changed by @gunesbizim in https://github.com/unusualify/modularous/commit/4b3cfa79fb55d4dfea4d47fa901273e58624f799
- :recycle: removed unnecessary checks by @gunesbizim in https://github.com/unusualify/modularous/commit/2a9b17340b5825b888cd92394c231e641ccd340b
- :recycle: move filterFormSchemaByRoles into ManageUtilities as filterSchemaByRoles by @OoBook in https://github.com/unusualify/modularous/commit/b563f77c49dae6956ba650b386682d646af15d12

### Styling

- lint coding styles by @invalid-email-address in https://github.com/unusualify/modularous/commit/2f0c7199c11d8a5858ffc25000409f9295f53c6b

### :white_check_mark: Testing

- :white_check_mark: snapshots updated vitest && added test:update command for snapshot by @gunesbizim in https://github.com/unusualify/modularous/commit/6bd8f404ad6e985312aed5d0e97e4e0ac3f986c0
- by @OoBook in https://github.com/unusualify/modularous/commit/5bcae46f86cb58ccea5e377aaddef643dfa5887f

### :package: Build

- :building_construction: add new v0.18.0 front build by @OoBook in https://github.com/unusualify/modularous/commit/fd672168b6e9da2ad4448bc9f8809d900b2eece5

### :beers: Other Stuff

- Merge branch 'refs/heads/release/v0.17.0'
- Update CHANGELOG
- Merge branch 'dev' into feature/general-table-update
- Merge branch 'feature/general-table-update' of https://github.com/unusualify/modularous into feature/general-table-update
- Merge pull request #74 from unusualify/feature/general-table-update

The stateable task was completed as I wanted. The Initial state, default state, and default_states structures look completed at first glance.

- add comments of filterHeadersByRoles method by @OoBook in https://github.com/unusualify/modularous/commit/d955f7f56fb7f6987deca9647c2dddc9593a6dd1
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

- :sparkles: stateable (dynamic enums) by @gunesbizim in https://github.com/unusualify/modularous/commit/596c2be27da6b54dadb9afd5d510490491bae937
- add input-filepond case to get display value of it by @OoBook in https://github.com/unusualify/modularous/commit/547014e58b6d93f9b7f2af17cee79d7cc7b93d14
- :art: add regex conditional to display preview by @OoBook in https://github.com/unusualify/modularous/commit/52936232d6605ad36bdd5e15b7eca64d8d9fa6fd
- :art: add printRequest component to print data coming from an endpoint by @OoBook in https://github.com/unusualify/modularous/commit/9d5865bb5b51442be247b4a730de9bc11c30dafb
- :art: add new currencyExchange service by @OoBook in https://github.com/unusualify/modularous/commit/12504b6a14b25699303f7fb3b8f2ba2e3dd4e0e6
- :art: add ux upgrades on StepperForm by @OoBook in https://github.com/unusualify/modularous/commit/ee6cdbc46631ec31cf3ed742cf55bf35119a6c96
- :sparkles: table updates, states table new field migrations by @gunesbizim in https://github.com/unusualify/modularous/commit/fb1fce48a91731e48623c3b3590a9db6dfc5c3e0

### :wrench: Bug Fixes

- add Roboto font family into b2press theme by @OoBook in https://github.com/unusualify/modularous/commit/90714c9e8dee1ea2dc45d2f5e8d4b8d1d4f6d39a
- :ambulance: remove is_payable bullshiit leading prices table to drop by @OoBook in https://github.com/unusualify/modularous/commit/3f8c58ea3e4bb5bfcf91479b925da87536483ac9
- :bug: set embeddedForm  as false on default by @OoBook in https://github.com/unusualify/modularous/commit/c256d31243442e11a9f8c11148ee5f9cd1c26a9c
- convert save-success to messages. by @OoBook in https://github.com/unusualify/modularous/commit/3a2ffd6a0e558a5400e653f7141d2283b2fd8aed
- :bug: get admin with role scope by @OoBook in https://github.com/unusualify/modularous/commit/17be0370e3e17a5d3e563ab8e533bd43be125cef
- remove indexing problems for uuidMorphs by @OoBook in https://github.com/unusualify/modularous/commit/9ba0afe10ba63316a5ddfd9d85d78fe6802a2828
- add previous components by @OoBook in https://github.com/unusualify/modularous/commit/5158f3256551e544919c9e66e756234584121540
- :bug: migration fix by @gunesbizim in https://github.com/unusualify/modularous/commit/056327826ae5b7aada0cdd026b9c6a3ff2eaef6b
- add getIndexUrls into dashboard and profile by @OoBook in https://github.com/unusualify/modularous/commit/2b44d586ad43b4cee4fb423d127d06367aa5d09e
- add default and client keys into profileMenu by @OoBook in https://github.com/unusualify/modularous/commit/36c10ac3419853638ad439e49e0a9d31fac0067e

### :recycle: Refactors

- :recycle: utilities as system module by @gunesbizim in https://github.com/unusualify/modularous/commit/7cc6332f207eb2fd50cc953eccfc3ceafe621570

### Styling

- lint coding styles by @invalid-email-address in https://github.com/unusualify/modularous/commit/632254be57dc21840d48f43006957f7090c2faa1
- :lipstick: change padding-top to 4 by @OoBook in https://github.com/unusualify/modularous/commit/6a5bdf52b7cfe2ce495601d85ef3b2b7b4f28c1e
- :lipstick: add text-body-1 for subText by @OoBook in https://github.com/unusualify/modularous/commit/0a3e83503b6ad2189ef214c78536b84fbb0510ec
- add md threshold for radio buttons by @OoBook in https://github.com/unusualify/modularous/commit/7029c67581d45d95a12031eaa5e09131834aa489

### :package: Build

- add new v0.17.0 front build by @OoBook in https://github.com/unusualify/modularous/commit/5b7b7069b2839744268c95088628b58b8cce8322

### :beers: Other Stuff

- Merge branch 'refs/heads/release/v0.16.0'
- Update CHANGELOG
- Merge branch 'dev' into feature/enum
- Merge pull request #72 from unusualify/feature/enum

Feature/enum branch passed tests, so we can merge it in experimental mode. We check it out again later.

- add Vat and Total localization keys by @OoBook in https://github.com/unusualify/modularous/commit/d63f912627eef02ac388b9bc82cd3ee0e01fdff3
- change defult input paddings by @OoBook in https://github.com/unusualify/modularous/commit/9d459fdf19a766873a502be64641909bb087c99e
- add save-success key to translation by @OoBook in https://github.com/unusualify/modularous/commit/f330d2908dce79d9c9d70a1383e5bb59fa51abe2
- fix mb-theme to mb by @OoBook in https://github.com/unusualify/modularous/commit/0508c3f71464c73d2e103922df7bf181c6591a43
- :recycle: clear navigation defaults by @OoBook in https://github.com/unusualify/modularous/commit/92119ee22efbf5afa85a7b49d1e059ec4e4fede4
- Merge pull request #73 from unusualify/feature/currency-exchange-service

Feature/currency exchange service has passed tests.

- Merge remote-tracking branch 'origin/feature/general-table-update' into dev

## v0.16.0 - 2024-11-04

### :rocket: Features

- :sparkles: Global scrollable directive with height modifier by @gunesbizim in https://github.com/unusualify/modularous/commit/db119c1728c0a6bc39dc97eef9e2b7262e892b6c
- :sparkles: add hasRequestInProgress feature for tracking any request in progress by @OoBook in https://github.com/unusualify/modularous/commit/343472acf3786eac1264bee1a088293f921dd333
- add __isBoolean helper by @OoBook in https://github.com/unusualify/modularous/commit/65f303d46a500a489683771a03d8a1de8cb0deae
- :sparkles: images of payment services to the seeder by @gunesbizim in https://github.com/unusualify/modularous/commit/39cc53dc017fb9cf0bb0bc169bc9e9b63b970b90
- :sparkles: add curly braces expression into translation's replacement pattern by @OoBook in https://github.com/unusualify/modularous/commit/e4381a2b6b9fe8502f0807541de5d99bcea1f111
- add en-US number format by @OoBook in https://github.com/unusualify/modularous/commit/62f77a7fb722c46e00f10ce0fc05aa6359c2bca7
- :sparkles: translatable title and modified title component by @gunesbizim in https://github.com/unusualify/modularous/commit/768434641a54852bde1ef22790d551a5fc4e7d09
- :lipstick: upgrade theming structure by @OoBook in https://github.com/unusualify/modularous/commit/b670fc992c9214d80bd37937f85d02e120e709bd
- :art: add validation ui styling to ue-tab-group by @OoBook in https://github.com/unusualify/modularous/commit/28a2e259c3efd0e770ccedf56fc97a3c4dc03228
- :art: add new infrastructure helpers by @OoBook in https://github.com/unusualify/modularous/commit/66e003c6a748c32d2f444bf2132b1f4ccad8f189
- :art: add sidebar toggling methods and mutations to config store by @OoBook in https://github.com/unusualify/modularous/commit/01fcf6e1725abd95546661e9edada9a00284ae42
- add ue-text-display component by @OoBook in https://github.com/unusualify/modularous/commit/21cdffd750433bb2ac351f081a0e89f962845fe4
- :art: add new notation util for various dot formation by @OoBook in https://github.com/unusualify/modularous/commit/0187c7e6fb8aba4f0ed5f0eef186f454a49f7a82
- add title and subtitle elements for wrap and group by @OoBook in https://github.com/unusualify/modularous/commit/4ec3a0d5e1fb4f4b64d0bdb50de15e1f3fa63bf2
- add ue-overlay generic class by @OoBook in https://github.com/unusualify/modularous/commit/c7729c2936c0ba00450e61d3e46a45f43ab1159d
- :art: add new ue-propert-list comp. to list bold and desc. elements of arrays by @OoBook in https://github.com/unusualify/modularous/commit/75cd736a3c3da0a809de7a6b008bcf18b27b3338
- :art: add ue-dynamic-component-renderer for parse vue component literals into object by @OoBook in https://github.com/unusualify/modularous/commit/d85fcd6c3ab231fe4b23821824adf81f00e779e6
- :sparkles: add configurableCard for various card combinations by @OoBook in https://github.com/unusualify/modularous/commit/2bc39845fa3935ee78b02fabeab7dca549f4e204

### :wrench: Bug Fixes

- merge remote-tracking branch 'origin/main' into dev by @OoBook in https://github.com/unusualify/modularous/commit/29c387ae064552f3c7dea15abbea2923246881f6
- :bug: CreditCard component image url fix by @gunesbizim in https://github.com/unusualify/modularous/commit/acdd24c36e7d3a16e9516ded8d07bc75553c43a2
- :bug: responsiveness of auth pages by @gunesbizim in https://github.com/unusualify/modularous/commit/65c4762496c259fb150b6235a6281f0156a5e481
- update title localization of form for new fields group by @OoBook in https://github.com/unusualify/modularous/commit/08d2f3a70c0237eb22b5f8c3f5c3bcad85645b07
- :bug: payment service icons by @gunesbizim in https://github.com/unusualify/modularous/commit/0fbf429071ac7ce2d886d56d1e9de8ecb6f77d30
- :bug: default selected payment service && error response of payment by @gunesbizim in https://github.com/unusualify/modularous/commit/24ada13bda8b4b165f7dc5a966d3421d663cdcbc
- :bug: sidebar issue for files by @gunesbizim in https://github.com/unusualify/modularous/commit/0c73fde84028f37139350bb1f3730a1d9ecd6334
- remove v--theme class from v-field of inputs by @OoBook in https://github.com/unusualify/modularous/commit/6301a81ef209d1d8b07fa3a87cac230e7ddd0165
- merge remote-tracking branch 'origin/bugfix/responsiveAuthPages' into dev by @OoBook in https://github.com/unusualify/modularous/commit/44f50bee6267acf676f4484474dfcb0123d6c960
- :bug: add CONFIG mutation into useSidebar by @OoBook in https://github.com/unusualify/modularous/commit/118ad2f4ea51b21d3314623abd07600ea239f290
- :bug: add a check for whether title is object or string by @OoBook in https://github.com/unusualify/modularous/commit/fb09615a2eac83abcbd6bec64e8424829a1b52b4
- remove csrf variable from Filepond by @OoBook in https://github.com/unusualify/modularous/commit/554949e41aebcb40d3b98bc28bfb3540b47b6890
- put bg if isset and put default and right slot by @OoBook in https://github.com/unusualify/modularous/commit/464785dd166c79d6dbd83b8a381100bff4b22d9d
- :bug: add methods as ref by @OoBook in https://github.com/unusualify/modularous/commit/de96770bf690a752ee6fce07ab724b4c9579eb88
- update the self value of related field as the value but not the object key by @OoBook in https://github.com/unusualify/modularous/commit/022ddf8492e6cdd7ce7894f7c1995ff64985cc1d
- :bug: add set sidebar false if it's mdAndDown by @OoBook in https://github.com/unusualify/modularous/commit/56f64cc376fc4300127d88c158d9aceba87e5647
- :bug: get $csrf from root methods by @OoBook in https://github.com/unusualify/modularous/commit/7c3a68e0943a91f32667cb61499c318623b09a1c
- :bug: change activeMenu ux mechanism by @OoBook in https://github.com/unusualify/modularous/commit/1511ef93bf1a30e39893e59a378c627fb8e85cb7
- :bug: move v-html directive from template to span by @OoBook in https://github.com/unusualify/modularous/commit/5d8c434e77ec15229b80450eccb51a4775a7d070
- :art: add different ui options and ux operations into StepperForm by @OoBook in https://github.com/unusualify/modularous/commit/041b8893418413623ffc243ad6eec8fb92561786

### :recycle: Refactors

- :recycle: priceController optimized by @gunesbizim in https://github.com/unusualify/modularous/commit/c516084300b7ccaf5fde410841edb26a802cb98d
- combine array ad object conditions by @OoBook in https://github.com/unusualify/modularous/commit/5416b48c37884387882cd40121f22dfca5ecd026
- :recycle: configure title.vue structure with props by @OoBook in https://github.com/unusualify/modularous/commit/f0e9cc604e9138d3abb9414f8ef1d3016cd7b985
- :recycle: configure title.vue structure with props by @gunesbizim in https://github.com/unusualify/modularous/commit/d7c0815fc44265e9e1465a02bd05034274b1a513
- update price structure as in standard inputs, add additional attrs requiring on ui by @OoBook in https://github.com/unusualify/modularous/commit/37fc0e3abb46fedc07d2421f29a1cb28dc129b73
- :art: add new sidebar structure, and update unnecessary dipslay thresholds by @OoBook in https://github.com/unusualify/modularous/commit/b70a3e5e1fcb7ae287c1fde418d19b517c787c58
- add icon and remove colors from impersonate toolbar by @OoBook in https://github.com/unusualify/modularous/commit/e79550ff0bb889896f35f1646989ecc243c1e3b9

### Styling

- add white-space:normal to RadioGroup by @OoBook in https://github.com/unusualify/modularous/commit/c9382791ce5409896f3881d3d20aa6f6eab42063
- :wastebasket: clean the hooks by @OoBook in https://github.com/unusualify/modularous/commit/d5275b87cd17d5087516f4fa6ecdfa4280c273e1

### :package: Build

- :building_construction: add pluralize package to frontend by @OoBook in https://github.com/unusualify/modularous/commit/74966b30b07cb09a7880cd47fd3f4ef19d154314
- :arrow_up: upgrade vuetify from 3.6.13 to 3.7.3 by @OoBook in https://github.com/unusualify/modularous/commit/8fcabe5d9318850f30ded5b2213dfa5b2b741ea8
- :building_construction: add new v0.16.0 front build by @OoBook in https://github.com/unusualify/modularous/commit/03b463f073dd800b64af1c3fde34a26c2dc5c18d

### :beers: Other Stuff

- :recycle: comment old development variables by @OoBook in https://github.com/unusualify/modularous/commit/a306972ab2e9ece0e7f629a48fdd3c5186f259c6
- Update CHANGELOG
- safety commit before merge
- Merge remote-tracking branch 'origin/dev' into dev
- live fixes
- Merge remote-tracking branch 'origin/main' into dev
- Merge remote-tracking branch 'origin/refactor/priceController' into dev
- Merge remote-tracking branch 'origin/testing/payment-testing-live' into dev
- Merge remote-tracking branch 'origin/feature/scrollable-directive' into dev
- add facade comments by @OoBook in https://github.com/unusualify/modularous/commit/26fd5a89257e249e7ba6a3376aacac34820691ec
- Merge pull request #71 from unusualify/bugfix/internal-payment-services

fix: :bug: CreditCard component image url fix

- add missing fields by @OoBook in https://github.com/unusualify/modularous/commit/d569bb7ae249053b335ccd855441c51270457353
- remove log by @OoBook in https://github.com/unusualify/modularous/commit/ff1a98b1b1be6d48f16606c51a2bd79af18961e1
- add missing icons for navigation by @OoBook in https://github.com/unusualify/modularous/commit/8737da67cd45267e890ec57b004a2b30695bf3f7
- add some icons into mdi.js by @OoBook in https://github.com/unusualify/modularous/commit/4976b38d5b6f0a0032c9a1a4d3a148232940e9dc
- add some snapshots by @OoBook in https://github.com/unusualify/modularous/commit/247a97e83fafc8340f77314a1100b973e98ac91f
- add some test methods to getFormData by @OoBook in https://github.com/unusualify/modularous/commit/6521092963fb5e4cee178b502950a164e5bc59d5
- :alembic: add experimental configurableCardHelper by @OoBook in https://github.com/unusualify/modularous/commit/7093bf8d801ed00c99c841ca31ada569e3a4cb99

## v0.15.1 - 2024-10-07

### :wrench: Bug Fixes

- change filepond facade and service class directory by @OoBook in https://github.com/unusualify/modularous/commit/db2de645dd5cdcec99ad053f5b4838a4a983599f

### :beers: Other Stuff

- Update CHANGELOG
- Merge pull request #69 from unusualify/hotfix/v0.15.1

fix: change filepond facade and service class directory

## v0.15.0 - 2024-10-04

### :beers: Other Stuff

- change command signatures by @OoBook in https://github.com/unusualify/modularous/commit/29a7d9a5ea2c2054ab9ca0355331b7e82b7a8911

## v0.14.1 - 2024-10-04

### :wrench: Bug Fixes

- :ambulance: remove systemPayment migrate calling from InstallCommand by @OoBook in https://github.com/unusualify/modularous/commit/18309f0c8b6ebb3a58581ad521f98bf63edf5e26

### :beers: Other Stuff

- Update CHANGELOG

## v0.14.0 - 2024-10-04

### :rocket: Features

- add SystemPayment migration to install command by @OoBook in https://github.com/unusualify/modularous/commit/f95226dd5a3641fd495f2596cbadd50294267204

### :wrench: Bug Fixes

- add ClassMapGenerator dependency to the Finder class by @OoBook in https://github.com/unusualify/modularous/commit/c3e942d966a025cf0b21a17f966f86167fe5a9ce

### :beers: Other Stuff

- organize seeder for test and setup by @OoBook in https://github.com/unusualify/modularous/commit/efde13964e747a72ba90a541c14a155fd8988188
- Merge pull request #67 from unusualify/dev

Dev

- Update CHANGELOG

## v0.13.0 - 2024-10-04

### :rocket: Features

- :sparkles: media library tag filters && ui update by @gunesbizim in https://github.com/unusualify/modularous/commit/9761ab91f112dc6f5344599e5e046ccec2e84dac
- :sparkles: default system seeder by @gunesbizim in https://github.com/unusualify/modularous/commit/23e69e30e9e614d9171ffbeb6931dc0557a0ca0b

### :wrench: Bug Fixes

- :bug: add slotable input to processedInputs by @OoBook in https://github.com/unusualify/modularous/commit/827e44c0b3c21c75298daa86d1966e3a50501e44
- set currency acc. to priceable setUserCurrency by @OoBook in https://github.com/unusualify/modularous/commit/3eeac3360ddab4f31dd3621d234ccc2f0272be9b
- change currency behaviour and fix price calculating on update by @OoBook in https://github.com/unusualify/modularous/commit/79ef5709565c1d65b359359b901dc50c5b21d4c1
- change orders of Relation and Payment traits by @OoBook in https://github.com/unusualify/modularous/commit/07219430736b947c79b6c3b96b366dfb79ed3257

### :recycle: Refactors

- :recycle: add is_external and is_internal field into payment_services create migration by @OoBook in https://github.com/unusualify/modularous/commit/c8c3c81b574de099b6ee3aa7da458d8782ed318c

### Styling

- set ordered_traits to false by @OoBook in https://github.com/unusualify/modularous/commit/825423c319cd43db38fa23c904469e1a6dc8fca2
- change return value on comment by @OoBook in https://github.com/unusualify/modularous/commit/96413571fd7e4601d810a2394fa1cc44a0c3e40d

### :package: Build

- :building_construction: build the frontend for v0.13.0 by @OoBook in https://github.com/unusualify/modularous/commit/4fab4ce7dc072b22bd8adadde81e872ebe52e97c

### :green_heart: Workflow

- fix checkout before merge by @OoBook in https://github.com/unusualify/modularous/commit/992025145948ef81699de438cf2f3b447d3c870d

### :beers: Other Stuff

- merge remote-tracking branch 'origin/main' into dev by @OoBook in https://github.com/unusualify/modularous/commit/789798c05c2e2ba99573a25a492743804181bb56
- assess currency config fields by @OoBook in https://github.com/unusualify/modularous/commit/63d8e93a6d8c20ec0c0f8b23aadfc21ba17b61fa
- merge remote-tracking branch 'origin/feature/media-tags' into dev by @OoBook in https://github.com/unusualify/modularous/commit/58f465fe3dc773459f2a49041a1c95763b2ae5ad
- change currency order in seeder by @OoBook in https://github.com/unusualify/modularous/commit/a26be282472c612fc5d1806d345a97a374ede9eb
- add SystemPayment Main Seeder by @OoBook in https://github.com/unusualify/modularous/commit/b1f6efcec6072bf8b32371626e1ef4294694858b
- add snapshot and payable configs to the publishes by @OoBook in https://github.com/unusualify/modularous/commit/2edd7f6a1c3eda3c5ff7aea00c04810cb664e93c
- Merge pull request #66 from unusualify/feature/system-seeders

feat: :sparkles: default system seeder

## v0.11.0 - 2024-10-02

### :rocket: Features

- add new CacheList command by @OoBook in https://github.com/unusualify/modularous/commit/537588472a7258669c836be3fc73fa63e12ae0ac
- :art: add currency preset according to locale by @OoBook in https://github.com/unusualify/modularous/commit/623d9f3ad4e659a1df62093fee6fdbfc6356e379
- :sparkles: add modularous:pint command for modules and modularous by @OoBook in https://github.com/unusualify/modularous/commit/a4b2c8e393aac286cf3edfe3a95cddd9e9ccf2be

### :wrench: Bug Fixes

- :bug: missing migration for is_external && is_internal fields on payment_services table by @gunesbizim in https://github.com/unusualify/modularous/commit/c88bcddcc310fe80974f3a6946532be0e208cc9b
- :bug: creditCard component fixes and icon change for pay action by @gunesbizim in https://github.com/unusualify/modularous/commit/0c327f8e2c366403bbe162f25a5f235d6f8d95d2
- :bug: added tags method to coreController && fixed the tags issue on relationTrait && updated paymentServiceSeeder for ideal payment by @gunesbizim in https://github.com/unusualify/modularous/commit/18bb8f66f7753f2b443eb3e64dfcea562dd2b9ab
- add if Snapshot model has priceable by @OoBook in https://github.com/unusualify/modularous/commit/c8f38b6912557281cb6da698fbce5f86a2a1aa24
- :ambulance: reset cache if paths does not match with base path by @OoBook in https://github.com/unusualify/modularous/commit/2863bf407e6f48fb7f284e39d2ce59cc67ff3204
- :bug: remove class v-btn--uppercase from default v-btn by @OoBook in https://github.com/unusualify/modularous/commit/26c96d29939422b6760870b60a0d45f4b82486c7
- add pint.json of the modularous as config into the pint command by @OoBook in https://github.com/unusualify/modularous/commit/ecb291789f957c495508735384c1bd3b09bd6d18
- :bug: get module view path from module->getDirectoryPath by @OoBook in https://github.com/unusualify/modularous/commit/904925a03f7b573209b5b723207481a30202896f
- enum for php >=8.1 by @OoBook in https://github.com/unusualify/modularous/commit/301036d8da97a708b82db54d7efd3cc11ffb1baa

### :recycle: Refactors

- add payment config for payable transactions by @OoBook in https://github.com/unusualify/modularous/commit/c74182e3801b12bd2359d0a4e321c2eb59bc1a47

### :memo: Documentation

- :pushpin: pin php support to 8.1 >=  for release 1 by @OoBook in https://github.com/unusualify/modularous/commit/85604530dff0abb8bc23e3e86413d5442e50ec52

### Styling

- :art: lint generally files according to pint.json added newly by @OoBook in https://github.com/unusualify/modularous/commit/b2c12b510e8969350790c3f768989ee01431971f

### :green_heart: Workflow

- change working-directory for vue tests by @OoBook in https://github.com/unusualify/modularous/commit/c88b8d6065bdfff2ef9008813c2fcfcd8c6cdc74
- change main.yml by @OoBook in https://github.com/unusualify/modularous/commit/ab12b1b827590644372d85b40d15fbbd18023669
- change main.yml by @OoBook in https://github.com/unusualify/modularous/commit/02a1756423a781b7ac69ee83308be75bedc43425
- change main.yml by @OoBook in https://github.com/unusualify/modularous/commit/a25c79b00e7d02d590bebf863cbd18647ed31398
- :green_heart: add laravel and vue conditions for testing by @OoBook in https://github.com/unusualify/modularous/commit/741e78858478772a5009f6107981a5a1ad459f8f
- create debug.yml by @web-flow in https://github.com/unusualify/modularous/commit/45c17d2414a9d496fd31a108ff7f9f37c76fb543
- remove vue and laravel tests from release action by @OoBook in https://github.com/unusualify/modularous/commit/badab6074abee05b5d29effc56e6f23b28acde4e
- Update debug.yml by @web-flow in https://github.com/unusualify/modularous/commit/43ba43843e4491a24948b50f27e7fb513654d6be
- add linter before releasing by @OoBook in https://github.com/unusualify/modularous/commit/503b06560b1a910ffe50a90a5d5edc3d6ec0d26e

### :beers: Other Stuff

- Update CHANGELOG
- fix psr-4 issues by @OoBook in https://github.com/unusualify/modularous/commit/a40820ad37d733f34e2c25c96fce38d2c2d9c303
- Merge pull request #62 from unusualify/dev

We have merged ci changes.

- remove manifest dd by @OoBook in https://github.com/unusualify/modularous/commit/6a69715b542af1ee37aa816bb0f88ff264b0ee59
- Update main.yml
- Merge remote-tracking branch 'origin/main' into dev
- Update main.yml
- Update main.yml
- Update main.yml
- add pint for dev changes by @OoBook in https://github.com/unusualify/modularous/commit/42c2abc524ff2023fa024541d132eebf018b8413
- configure pint.json file by @OoBook in https://github.com/unusualify/modularous/commit/49a31ef3c1c4aefa580a75eb04cab4427a74ad83
- Merge pull request #63 from unusualify/dev

It's tested successfully.

- merge remote-tracking branch 'origin/feature/media-tags' into dev by @OoBook in https://github.com/unusualify/modularous/commit/be75c556f19b6157b5ed52c354b14f52c1399a00
- update php version by @OoBook in https://github.com/unusualify/modularous/commit/2e1671b08288d96de8a60c610d0b6a28944e2b64
- add pint scripts by @OoBook in https://github.com/unusualify/modularous/commit/69087a2afda0fb0564e1f3fd56a957cb18d1fecf
- Merge pull request #64 from unusualify/chore/pint-fix-all

Thanks, the pint command is so good starting point. To style available files is also testing case, it has run successfully. That's done.

- Merge remote-tracking branch 'origin/main' into dev
- Update debug.yml
- Merge remote-tracking branch 'origin/main' into dev
- Update debug.yml
- merge remote-tracking branch 'origin/main' into dev by @OoBook in https://github.com/unusualify/modularous/commit/64af45efe03b236665c9bf6b97389bcf2fbb2067
- merge remote-tracking branch 'origin/main' into dev by @OoBook in https://github.com/unusualify/modularous/commit/8e3423785ce7d9cfe8f170be65e9496f993d86e8

## v0.10.0 - 2024-09-26

### :rocket: Features

- :sparkles: introduce preview on media files uploaded with filepond by @ilkerciblak in https://github.com/unusualify/modularous/commit/1622e35b5ae752840914d6e18efc56a14d59993b
- :sparkles: introduce file type validation functionality to filepond component by @ilkerciblak in https://github.com/unusualify/modularous/commit/7e0c64e2aa1a350f8beea789e1de84edc0e6e8fb
- :art: add merge mechanism for laravel localization by @OoBook in https://github.com/unusualify/modularous/commit/648d39c2f76783fba73a1577c903ef8828b7daa9
- :sparkles: table name option to migration make command by @gunesbizim in https://github.com/unusualify/modularous/commit/75af31ac5b2489d2d5c34a99e52dd079d1524c24
- add new route-custom-model script by @OoBook in https://github.com/unusualify/modularous/commit/9c5e3432443ea3adc1b39abe518eeef87b9ff31c
- :sparkles: payment trait && required module files such as payment services && payment by @gunesbizim in https://github.com/unusualify/modularous/commit/4335a8aa617734de8657f056fc09945dfce38b6a
- :sparkles: default currency for payment services by @gunesbizim in https://github.com/unusualify/modularous/commit/74094cc22c733042fba697367b540286e3e760c5
- :sparkles: feat: :sparkles: payment trait && required module files such as payment services && payment continue by @gunesbizim in https://github.com/unusualify/modularous/commit/782943ec555f216f843c4bb8d371ddaa63dcd2a2
- :art: add change_array_file_array and add_route_to_config helpers by @OoBook in https://github.com/unusualify/modularous/commit/3aedeecaaa36cdbf29a877ba294e9ebb758e7382
- :sparkles: paymentcontroller for systempayment by @gunesbizim in https://github.com/unusualify/modularous/commit/cf88c26cc62dae997d8c1232b4bcb8d3a01488e3
- :art: add new url query handling functions to pushState by @OoBook in https://github.com/unusualify/modularous/commit/15416c9d5802f5fdcf36e5ca4416436a2b41cb44
- :art: add useModule for creating view boilerplate to components like table and tabGroups by @OoBook in https://github.com/unusualify/modularous/commit/9f031c8be41db9247d06fd13adf9a5b2f9b7e9c5
- :art: add ue-tabs component by @OoBook in https://github.com/unusualify/modularous/commit/9a277652fa63d5129f0c973d1a4b2fea702d7609
- :art: add tab-groups component by @OoBook in https://github.com/unusualify/modularous/commit/08354cd0baa003d1cfc16e0517887998133bacc8
- :sparkles: credit card form component by @gunesbizim in https://github.com/unusualify/modularous/commit/470105233756bd874fc5d819d97810fefdf91ea4
- :art: add no-migration flag into make:module and make:route commands to not create add migration if custom-model exists by @OoBook in https://github.com/unusualify/modularous/commit/53e555dfd621eeda4f3f9327a53bbc7b53100672
- :art: add new method to get all module models by @OoBook in https://github.com/unusualify/modularous/commit/30a7719ba835b8ab3860e063476c915b34aae0ff
- :art: add new method to Finder to find all model classes by @OoBook in https://github.com/unusualify/modularous/commit/9fb5c83b7087c06c8b1143ec3ed79576a4d03623
- :art: add cross relationship structure by @OoBook in https://github.com/unusualify/modularous/commit/3bf41e0db782b6e9c0888d0cba95947629325fd1
- :sparkles: introduce draggable table rows by @ilkerciblak in https://github.com/unusualify/modularous/commit/34c4d1e78d829f1aacae723ee2780f905a18a96a
- :sparkles: implemented end to end drag-drop reorder functionality with optimistic ui approach by @ilkerciblak in https://github.com/unusualify/modularous/commit/ca18777d112b650ceab2d25cd81ca489d97bb5dc
- :sparkles: creditcard, creditcardform and payment vue components by @gunesbizim in https://github.com/unusualify/modularous/commit/d35cc9cf2de6a7a7e86fe0903f927ba5eb50ef1a
- :sparkles: icons to payment services by @gunesbizim in https://github.com/unusualify/modularous/commit/046c760cb5935a98f54dd2e89d468d3bd8f713e8
- :art: add hydrateInputType feature for predefined input types by @OoBook in https://github.com/unusualify/modularous/commit/cfa3902944fb6ea93bac926dea6b6da9c97ebe8b
- :art: enhance Tabs component responsing to array items by @OoBook in https://github.com/unusualify/modularous/commit/3c8899964eff6f1f7c3ff600dcb61241a9e274a4
- :art: add noUpperCase and noBold props to Title.vue by @OoBook in https://github.com/unusualify/modularous/commit/b8f465ab417f763b2c76a8f3cdd1d58d847bf2e0
- :art: convert RadioGroup into a component having only radio buttons by @OoBook in https://github.com/unusualify/modularous/commit/469a306643d396de46f166fc523c10c1f4c0b729
- add __dot and __wildcard_change helpers by @OoBook in https://github.com/unusualify/modularous/commit/d969164470cb61aac3f4589b031cd7b183527e54
- :art: upgrade formatSet and formatFilter events for non-array model by @OoBook in https://github.com/unusualify/modularous/commit/c673cd2c2319670bca5b0da5cfb7877832fbbca0
- add checklist-group to ManageForm by @OoBook in https://github.com/unusualify/modularous/commit/65c819b3b99a75e139b1b07e82bd6f42ab26fdc5
- :art: add default values for ext date andtime by @OoBook in https://github.com/unusualify/modularous/commit/56c0d08a5bc47ce06fedd662857dcdc1c8e03186
- add relative condition to getDirectoryPath by @OoBook in https://github.com/unusualify/modularous/commit/3204fd87b497042c7111f18e7f28e3e0765b0f1d
- :art: add getTitleField method on ModelHelpers by @OoBook in https://github.com/unusualify/modularous/commit/00659671cdae49833d2251f4c77fe0cd0e118d1f
- :art: use getTitleField of the model on nested case by @OoBook in https://github.com/unusualify/modularous/commit/77b765430fbd0c177cd8896ec7fe3291b23fbc4a
- add new input_types by @OoBook in https://github.com/unusualify/modularous/commit/a8c6e58c6da0f9001bbeee864248772f521de8e6
- update inputs of form_draft wrt input_types structure by @OoBook in https://github.com/unusualify/modularous/commit/d42793f1b6103fa5dd06e1f3375494e22375e5ca
- :art: add vue input component generator command by @OoBook in https://github.com/unusualify/modularous/commit/f4987f42ff45b4abf3b1eda1a6807d5a1a1d6776
- :art: add input hydrate class generator command by @OoBook in https://github.com/unusualify/modularous/commit/d8ac8e0e295a8e5ffde694eae376776b00f69be9
- :art: add necessary input hydrate classes by @OoBook in https://github.com/unusualify/modularous/commit/a1e2a64829743e5bb6a407d5c8403d50c8160cbe
- :art: add VSheetRounded alias into vuetify by @OoBook in https://github.com/unusualify/modularous/commit/63b66a5d2715e48629e3d3b7b4536960daaee13e
- :art: add snakeToHeadline helper by @OoBook in https://github.com/unusualify/modularous/commit/161fc594733ec61cdcf184fa802df87f6b6f13e1
- :art: upgrade tokenizePath helper by @OoBook in https://github.com/unusualify/modularous/commit/90c69e81039462b5d49b069eb5061eaec1ff3ada
- :art: add prependSchema ext event by @OoBook in https://github.com/unusualify/modularous/commit/fc28754d7ee74840ab11685ebdb8c9c8e0dd7e34
- :sparkles: pay action, customFormModal added && paymentServiceComponent fixed by @gunesbizim in https://github.com/unusualify/modularous/commit/e0bb05d7bb4f987decc594caf9b63dcafa24c7eb
- :sparkles: price controller, migration for is_payable field && payment service seeder added by @gunesbizim in https://github.com/unusualify/modularous/commit/ed02ebe06dfbb5d9d0792559b9b0df5e84ef3bd3
- :sparkles: default payment services added by @gunesbizim in https://github.com/unusualify/modularous/commit/b51ac66e3305f80ae822b30ba3691e25a44eaa88
- :sparkles: paymentServices and required flow by @gunesbizim in https://github.com/unusualify/modularous/commit/c52acd3c0d70fb55f06ed420f021c2a97b996ef0
- :sparkles: a helper function for removing query params by @gunesbizim in https://github.com/unusualify/modularous/commit/e91328c29194c1623e20f9b4f723095231d14982
- :sparkles: payment service and its components revised for the flow by @gunesbizim in https://github.com/unusualify/modularous/commit/c97bd7d60d820ea3a8fee49c3b7b3146b25258b6
- :sparkles: customModal revised for more general use by @gunesbizim in https://github.com/unusualify/modularous/commit/d0917b155f71a501dd486194257249e53aab6aad
- :sparkles: paypal first response moved into package by @gunesbizim in https://github.com/unusualify/modularous/commit/1f48834de5c6eba21088f321d82d360113257e1e
- :adhesive_bandage: add disabled props to Checklist & RadioGroup by @OoBook in https://github.com/unusualify/modularous/commit/576d242675932239a2b5f88b33282291d081ae7a
- :sparkles: add dialog alert first version by @OoBook in https://github.com/unusualify/modularous/commit/97b453d50dfb33dcd6782c18f00d66cf26c092ba
- :sparkles: add dialog alert first version by @OoBook in https://github.com/unusualify/modularous/commit/b69929b61a40371f00ab4d52a1f7599d5d9f214f
- :art: add formatPrependSchema event by @OoBook in https://github.com/unusualify/modularous/commit/f0113cda179bffe30bf6cbc6c67b34a6311cabcb
- :art: add alpha version of completing of stepperForm by @OoBook in https://github.com/unusualify/modularous/commit/3c0933e2b2150bcf34dd8044776a8e763b3b067b
- :art: add moduleFrontRoutes macro to Route by @OoBook in https://github.com/unusualify/modularous/commit/bbe936af4671f5d5467ec083f18f251b29385cbc
- upgrade routing on provider by @OoBook in https://github.com/unusualify/modularous/commit/8389f64925d1324f2abcf8ca8e04bf30e6a10d09
- :art: add replace_curly_braces helper to change model bindings of endpoints by @OoBook in https://github.com/unusualify/modularous/commit/7bc55ee50092367c74bc5919e468cfc33942a3d3
- :art: convert  route front controller extends BaseController on stub file by @OoBook in https://github.com/unusualify/modularous/commit/513fa72bd29d3755fc9ccf9c06b30c276469209e
- :art: add getColums to get column names of db table by @OoBook in https://github.com/unusualify/modularous/commit/202efcde3907ba06a57e875bac169dac92062a11
- :art: add ManagePrevious trait to track previous route and structuring by it by @OoBook in https://github.com/unusualify/modularous/commit/801ab53e5c3d75cd639b0911765b98fb5fa87cf3
- :art: add array handling for 'ext' key by @OoBook in https://github.com/unusualify/modularous/commit/d14f1f1c2fb421959905ac9586d4323af70b3577
- :art: add uuid suffix on headers by @OoBook in https://github.com/unusualify/modularous/commit/d2f65280fc3d5e203e4f7db2bbd9b1853220dfa0
- :art: add ManagePrevious trait to BaseController by @OoBook in https://github.com/unusualify/modularous/commit/21c30ce1663e2145ffeb55f0d5048692c2c67432
- :adhesive_bandage: add parentName to group schema inputs by @OoBook in https://github.com/unusualify/modularous/commit/0ca0d1963e7ef9247f67ec22d9f13452b0fbe3be
- add hasManyRelations into getFormFields method of RelationTrait by @OoBook in https://github.com/unusualify/modularous/commit/8c9bbee47e040317474b375cf9417610c1fd8d4b
- :sparkles: icon made dynamic by @gunesbizim in https://github.com/unusualify/modularous/commit/0f4c652d728b068da7970cf53cea7e9c53a8661a
- :sparkles: __removeQueryParams functionality moved to hook file by @gunesbizim in https://github.com/unusualify/modularous/commit/86eefa4548aa6bb5255e442a90efa53c366ca7fb
- :sparkles: __removeQueryParams functionality moved to hooks by @gunesbizim in https://github.com/unusualify/modularous/commit/cea2b7ba14bac700855afd8c9053e5e61d8319f8
- :sparkles: reverseDot method added to init.js by @gunesbizim in https://github.com/unusualify/modularous/commit/1c32ea06b595c2102f667e6fafebf36e9185f988
- :art: add getCloneSourceFields method to modelMakeCommand for hasCloneTrait by @OoBook in https://github.com/unusualify/modularous/commit/d02e4e79c8add59022af38f8c1c92003ead9aa98
- :art: add array_except helper by @OoBook in https://github.com/unusualify/modularous/commit/1a88c817b9462b8c2b20765815164b67d6067ccc
- :art: add new method string generator methods by @OoBook in https://github.com/unusualify/modularous/commit/54411c234ab879af17784fda42b51fa78b30de89
- :sparkles: svg icon component by @gunesbizim in https://github.com/unusualify/modularous/commit/137d99b72cd9c2335c4ebe092ed57b3f1660c377
- :sparkles: nested dynamic blocks for recursive stuff component by @gunesbizim in https://github.com/unusualify/modularous/commit/dd88a9d606112de9697bdc347a56febe062e164d
- :sparkles: new authentication pages by @gunesbizim in https://github.com/unusualify/modularous/commit/3eed15f13e13d214cfe2f36f3b191b1ee01e4103
- :art: add methods for snapshot trait by @OoBook in https://github.com/unusualify/modularous/commit/de4a5e11c866c29bd6ae7df6431d7434cd3cd91e
- add HasUuid feature by @OoBook in https://github.com/unusualify/modularous/commit/f8c095ea3180d4e3d2a5a9331fcedab0a19f7381
- add MethodTransformers trait by @OoBook in https://github.com/unusualify/modularous/commit/71afc7228139f1ee90b12bf3c55de3c9414deb8c
- :art: add title or name check for array values for cells preview by @OoBook in https://github.com/unusualify/modularous/commit/80a0689bd404da6762c5399bbdff027a956858e4
- :art: add new addible traits to traits config by @OoBook in https://github.com/unusualify/modularous/commit/517b345b927c086c85957188c2d34452e96abc2b

### :wrench: Bug Fixes

- :bug: fix some code syntax related bugs by @ilkerciblak in https://github.com/unusualify/modularous/commit/84be6d5108c690cfaa2eb910c2fd50895d67c984
- :bug: fix state management issue on new file uploads and new form processes by @ilkerciblak in https://github.com/unusualify/modularous/commit/31de48634b8da210b19b01e0e19ddcbd78a16c91
- remove dd for translation by @OoBook in https://github.com/unusualify/modularous/commit/24caae8e2b7641400ec6c85b4816a363d4020264
- :bug: remove unnecessary base_prefix field from config by @OoBook in https://github.com/unusualify/modularous/commit/3fc4a3a357f57c4ae3d878a190a80fb843e26171
- :art: add override model fillable by @OoBook in https://github.com/unusualify/modularous/commit/d57a94386d40114f74a8028769ed27a0fd7366a7
- add lang files of system modules and fix some controller sources by @OoBook in https://github.com/unusualify/modularous/commit/b4474af08f8eb10a139e10ad6801cb4cc5ec7a0c
- :bug: directory path for system modules by @gunesbizim in https://github.com/unusualify/modularous/commit/dd4c86257bdc14f5c3be9b153aa3712b8fa94cae
- :bug: price calculation error where related class doesn't have HasPriceable trait by @gunesbizim in https://github.com/unusualify/modularous/commit/a338dbc8e9a8644ad0210e2078be04eb0f2fa83f
- :bug: where PaymentTrait is triggered eventhough there is no relation by @gunesbizim in https://github.com/unusualify/modularous/commit/3bb621d5c545c760f5acea22dfa2c9fe45d07488
- :ambulance: add default fields as an array for empty fields and schema by @OoBook in https://github.com/unusualify/modularous/commit/3119aa2d0a63cdb188450c2cca6dd9a15f857414
- :bug: fix form.validModel issue on useTable by @OoBook in https://github.com/unusualify/modularous/commit/cceab5a6c9b839008ec88161afcfdbb22abba02c
- :bug: add BelongsToMany condition into addWiths structure by @OoBook in https://github.com/unusualify/modularous/commit/004ebe59a9f27200346326ad54a5c80ef11e0b58
- :bug: fix searching filter for translation columns by @OoBook in https://github.com/unusualify/modularous/commit/10c3008045509d082d51b9158f4b71ea5308f9d2
- :bug: fix filepond convention issues by @OoBook in https://github.com/unusualify/modularous/commit/4d04f10a608796964e74b35f99c684ae89daf93b
- :bug: add api.languages endpoint to auth layout by @OoBook in https://github.com/unusualify/modularous/commit/19cbab153d6f5dc3ba58537dc77cdc0dc842023f
- :bug: fix test-route-morphTo by putting morphTo into schema flag instead of relationships by @OoBook in https://github.com/unusualify/modularous/commit/8ef04d6b829a972f9b386a303d067ba1a9871ede
- :ambulance: fix advancedFilter condition of related button by @OoBook in https://github.com/unusualify/modularous/commit/2f46fd77291b5053660277f03c5b9416bc0f45fb
- :bug: rebuild flattenArray on model structure changed by @OoBook in https://github.com/unusualify/modularous/commit/b56b8e8a928d2e515417d4d4a895d10bb8f6bd72
- :bug: implement default table names for something by @ilkerciblak in https://github.com/unusualify/modularous/commit/0210d68e8fd4699ad749d2dd4046be55077784a0
- :bug: add modelValue to ue-tabs v-model by @OoBook in https://github.com/unusualify/modularous/commit/11c6f8b78a6e976d98887573cb3bd0ca417243cb
- :bug: fix object editing remove-update file issue by @ilkerciblak in https://github.com/unusualify/modularous/commit/1aac3a2de9f2ffabdfd5e5bb0cd8ed389e58ff50
- :bug: share inputEvent coming from wrap and group form-base by @OoBook in https://github.com/unusualify/modularous/commit/2b9cc83a6fa75ea115318747c64b65777d94d2f9
- :art: recall invokeRuleGenerator if input has schema object by @OoBook in https://github.com/unusualify/modularous/commit/5ca19f5f38c658c92eb59ec0770f2c8f22a32c8a
- :bug: fix group type cases in getFormData by @OoBook in https://github.com/unusualify/modularous/commit/caea306358a4343b94bcf43e8930f0f15be8e920
- :bug: remove the key from previewModel if it is removed from the models by @OoBook in https://github.com/unusualify/modularous/commit/6303a3b47f3c6d138db396bed3336808b45274d7
- :art: reconfigure model and inputSchema if model structure changes by @OoBook in https://github.com/unusualify/modularous/commit/a9dbf32847f0c1407409766981f00d56f18d6b79
- :bug: fix wrap and group hydrates by @OoBook in https://github.com/unusualify/modularous/commit/231d532c611c534a72856615ac6dbca846988316
- :bug: add module getDirectoryPath into MigrateCommand by @OoBook in https://github.com/unusualify/modularous/commit/0c172873f130d268704f1b807fa6f767cfacce6c
- add payable package into composer by @OoBook in https://github.com/unusualify/modularous/commit/98473a37704d55f31d30e7fe5465cffc22591115
- :bug: payment trait relation by @gunesbizim in https://github.com/unusualify/modularous/commit/ba04bfa80c66269a1b7468a3904daf4ea9829f1d
- :bug: fix spreading unless morphTo's schema exists by @OoBook in https://github.com/unusualify/modularous/commit/7960ab4fdd47548330036ab50ad2dbb4a2bebea3
- :bug: fix fields suffixed _timestamp and _relation on sorting by @OoBook in https://github.com/unusualify/modularous/commit/522b90c282bf9d82a78df777e500141b8f9a5596
- :bug: fix fields suffixed _timestamp and _relation on sorting by @OoBook in https://github.com/unusualify/modularous/commit/3b9b72585a3a2fc60238a5d2eb25564cca6dd94a
- :bug: fix getting advancedFilters with filtering by @OoBook in https://github.com/unusualify/modularous/commit/6252713df8d520f0a5cadbcb9ac85c7ecee8ffbd
- :bug: fix item's column if $column variable is array by @OoBook in https://github.com/unusualify/modularous/commit/d29e7e1913a9ed98d17bf17b409f694d594b480b
- :bug: fix caret goes start point on each inputting by @OoBook in https://github.com/unusualify/modularous/commit/a3e6e1ab81beb16818b6cce010c0bb997f62741f
- :bug: fix recursive searching if modelValue is null by @OoBook in https://github.com/unusualify/modularous/commit/dd736904cc7434229391686f2bbc07b07f138a4d
- :bug: add preg_quote for action matching by @OoBook in https://github.com/unusualify/modularous/commit/d997dd7f61331ddf2b9776866d99147c231f0a46
- :art: add default handling in RadioGroupHydrate by @OoBook in https://github.com/unusualify/modularous/commit/204df8391f284c442dacbf1c277dd8120dc4983e
- :art: change label-idle translation in FilepondHydrate by @OoBook in https://github.com/unusualify/modularous/commit/ed71b655326d6274a39d06f9289c8280eeb46ad7
- :bug: fix key getter if next keys chain is key in related object by @OoBook in https://github.com/unusualify/modularous/commit/f0a8da45f36ffb35dce217b2b3344c809fe972df
- :bug: add name prefix if it is wrap in a group input by @OoBook in https://github.com/unusualify/modularous/commit/583287853d692dd4c785564bee1808b042947779
- :adhesive_bandage: fix setSchemaError while handling response errors by @OoBook in https://github.com/unusualify/modularous/commit/bcb09f1c9a2712d2e7dabb67d795b9a190af0314
- :adhesive_bandage: fix wildcard_change pattern with '?' by @OoBook in https://github.com/unusualify/modularous/commit/c8c4c3c80ce1685249a77e6636766e044a6cbfb2
- :adhesive_bandage: fix error handling on error response from api by @OoBook in https://github.com/unusualify/modularous/commit/ee23825759839f92d6c3002a4d66b30261445238
- :bug: fix group type on changes by @OoBook in https://github.com/unusualify/modularous/commit/e2216dee051ba0904d8d4a82f9c4f59c60440389
- :adhesive_bandage: fix state handling on changing schema and model structure by @OoBook in https://github.com/unusualify/modularous/commit/617ff03845a3ff1796e42e9d4cf9d218e6a4ca0f
- :adhesive_bandage: fix refresh command with unusual rollback and migrate commands by @OoBook in https://github.com/unusualify/modularous/commit/d5323b06232f7fcf1c155db5de8daf72b036a957
- :adhesive_bandage: fix guest user cases about middleware, trait and navigation by @OoBook in https://github.com/unusualify/modularous/commit/440a5f4d93e128c0510d7f59bebdc76fda2a3103
- :adhesive_bandage: fix guest user cases about middleware, trait and navigation by @OoBook in https://github.com/unusualify/modularous/commit/0959ad8fd73dafdd0a775bc2b549bd1d98b3d201
- :bug: fix filepond on multiple input name and preview route by @OoBook in https://github.com/unusualify/modularous/commit/8204900aec35d11cfa4bf6fec8d4846f6e055026
- :adhesive_bandage: fix default value of selectable inputs if not multiple by @OoBook in https://github.com/unusualify/modularous/commit/bf48e005c4c56d414db4ebaf67ac063598674db8
- :adhesive_bandage: fix itemTitle of items if not exists by @OoBook in https://github.com/unusualify/modularous/commit/0f746097d17e7b101d8f15a079075b34cedf7264
- :adhesive_bandage: add itemValue and itemTitle defaults into RepeaterHydrate by @OoBook in https://github.com/unusualify/modularous/commit/661fb5710b55abfa64382d4624a7d67ea486ad73
- :bug: fix morph id colums as uuidMorphs by @OoBook in https://github.com/unusualify/modularous/commit/cfa973de2cb64f294a7566d6c9478e0ef1861982
- :adhesive_bandage: fix guest user cases about middleware, trait and navigation by @OoBook in https://github.com/unusualify/modularous/commit/813584f603a7bbca0cf8df7e014a457b101f3e4d
- :bug: add morphTo fields to fillable generator by @OoBook in https://github.com/unusualify/modularous/commit/1253a721cb21f2240c178deb09f20dc198a025b3
- :bug: add a check to handle third-part traits as featured-trait by @OoBook in https://github.com/unusualify/modularous/commit/c77f0ff129a0a038355988757577e72f6cc36471
- move the repositoryClass method outside from try-catch by @OoBook in https://github.com/unusualify/modularous/commit/779705e7cb9301f7dbc7d20142a7f6fedfe1b08d
- :bug: where object price is present but there is no price for the object by @gunesbizim in https://github.com/unusualify/modularous/commit/08adab8651f72fd92e217bafa4e25a8f158e32ac
- :bug: method that causes unnecesarry initialize removed by @gunesbizim in https://github.com/unusualify/modularous/commit/8bfa6775bdefea4343f24bbcc056b7645a90ebd1
- :bug: where module is undefined on ManageForm in some cases by @gunesbizim in https://github.com/unusualify/modularous/commit/83b13193ca435658928ea871872c68a3baf82a2a
- change filepond preview route name on mediableFormat by @OoBook in https://github.com/unusualify/modularous/commit/c99924cc0f5abce62fb5a6124c227bd594862c19
- organize composer file with php requirements and test requirements by @OoBook in https://github.com/unusualify/modularous/commit/bc26495b73884178519b185f91d8c4d1dcd8d310

### :recycle: Refactors

- :recycle: add get_file_string helper and it's usages by @OoBook in https://github.com/unusualify/modularous/commit/d85c2058b003c58cf9dfaa2a45ad263edac1acc5
- :recycle: add if condition to array helpers and refactor usages by @OoBook in https://github.com/unusualify/modularous/commit/b0209d2fbda6fd26bbb5279a7ef4567b234db0e1
- :art: add only route config to config array for preventing to remove namespaces and comments by @OoBook in https://github.com/unusualify/modularous/commit/3a5ea67a19f15c4d28d7056d79a2ae4c2f88081e
- :recycle: remove double quotes from parameter of setModule by @OoBook in https://github.com/unusualify/modularous/commit/dd1ff51acd89a3c5a847d3c7adb623f98dd601e8
- add response to callback function on get method by @OoBook in https://github.com/unusualify/modularous/commit/b752e006c86116885408e1f1be2128009a0c0b86
- :art: reorder defining routes for fixing slug structures by @OoBook in https://github.com/unusualify/modularous/commit/3329da4e43a851b431c3f8827465bbbec6a7167e
- :recycle: refactor bottom slot and prettify file by @OoBook in https://github.com/unusualify/modularous/commit/9711655167ffa41e2293125b715f62bc1a5291c8
- :recycle: remove embeddedForm on default by @OoBook in https://github.com/unusualify/modularous/commit/e1f30f590d6890c1405246ac5af227419b7332ae
- :recycle: merge namespaces into one variable by @OoBook in https://github.com/unusualify/modularous/commit/46cdca0ca5aaef5667dedf5c126af552e444461f
- :recycle: refactor index.blade structure by @OoBook in https://github.com/unusualify/modularous/commit/492ba2196f3e2ee5b7d817a46e30552716e3a8ed
- :recycle: refactor getFormData method by @OoBook in https://github.com/unusualify/modularous/commit/9eecd5886e0e149b7de8b70c15e3d8e7b7eec8b3
- :recycle: change radio-group to checklist-group by @OoBook in https://github.com/unusualify/modularous/commit/3e66b15b20a1895552fdb477d3e9fa405b338976
- :recycle: use ue-tabs with windows slot in TabGroup.vue by @OoBook in https://github.com/unusualify/modularous/commit/ddf691076fc831e300e7d9f27a76e5a534441870
- :recycle: remove Object.assign and put todo note draggableItem by @OoBook in https://github.com/unusualify/modularous/commit/d5a4f258aef187eb9c22eac012633257d9b16b71
- :recycle: convert custom-input-... pattern to input-... pattern by @OoBook in https://github.com/unusualify/modularous/commit/58ad62eb85711d04ae38d5407d6f5834408db9b1
- add connector to permissions input of role route by @OoBook in https://github.com/unusualify/modularous/commit/871821ae287256250da2a4ce5c77a6399783952a
- :recycle: modals to come from unified modal object and table ui fixes by @gunesbizim in https://github.com/unusualify/modularous/commit/2b37ad8b69fd03fdafc722d1af29a7f4e5d4fb85
- :recycle: convert hydrate input switch cases into hydrate class by @OoBook in https://github.com/unusualify/modularous/commit/9dca404d42207888e43f91e6ebe69ca2034c2320
- :art: change some translation fields for grouping by @OoBook in https://github.com/unusualify/modularous/commit/7284216e7ce4858660fe4cc4c294dc4b79b0a533
- :recycle: PaymentService hydration changed by @gunesbizim in https://github.com/unusualify/modularous/commit/032ed262286e2be3980c112feaf3255c3a85ccca
- :fire: inside of paymentController cleaned by @gunesbizim in https://github.com/unusualify/modularous/commit/314491173beca8c96c91616223501e4dbd5f9249
- :recycle: update loading-text translation by @OoBook in https://github.com/unusualify/modularous/commit/366a45e084fb218689ea0038245b143b9fc43e51
- :recycle: clone formatter array by @OoBook in https://github.com/unusualify/modularous/commit/ee320a1027a0aca92b56c0ac7767862e2aa662b8
- :recycle: refactor chunkInputs, flattenGroupSchema by @OoBook in https://github.com/unusualify/modularous/commit/8aaaafc06d809e63382258a92bbe58e563688892
- add ternary in case empty by @OoBook in https://github.com/unusualify/modularous/commit/50a214f37023643a4831f9880be95df5b91350e9
- :recycle: payment parameter removed from datatable.js by @gunesbizim in https://github.com/unusualify/modularous/commit/b3b3ffd4fbfd411cbfa8e89adbb48819384b6480
- :recycle: sidebar logo replaced with new svg icon component by @gunesbizim in https://github.com/unusualify/modularous/commit/4f6b2fd881cdc4e06825be39968b68577c78d541
- :recycle: deprecated xlink:href replaced with href by @gunesbizim in https://github.com/unusualify/modularous/commit/1abe1e8f13847178436b327b53047a6e899256d1
- :recycle: payment service pay method call changed by @gunesbizim in https://github.com/unusualify/modularous/commit/0b6c8f90293c25e4bfd7a362dc543436fa64923a
- move src/Database to database dir by @OoBook in https://github.com/unusualify/modularous/commit/4d0f26afa3bbd6e937bb7832e85486c25834d438
- change Priceable namespace as Oobook by @OoBook in https://github.com/unusualify/modularous/commit/024f11f2b423cc7282137fad3c14a0d99d26529b
- :recycle: remove methods of ManageEloquent from ModelHelpers by @OoBook in https://github.com/unusualify/modularous/commit/59baa1003b151a35344028ae97a530c81a28f83b
- :recycle: move methods of MethodTransformers from repository by @OoBook in https://github.com/unusualify/modularous/commit/8654f03672a52d41b3c71dfe308f734841f0cde6
- add $this->app instead of app helper in setModule by @OoBook in https://github.com/unusualify/modularous/commit/bf6127008dc5d1d77b95fb276df99dc2f28bb828
- :recycle: lang files updated by @gunesbizim in https://github.com/unusualify/modularous/commit/ae96e12d5a3af82e349a60eb32ac1cd915540215
- add realpath with relative path for scan paths by @OoBook in https://github.com/unusualify/modularous/commit/fde6e7a52b5c533e62212a39222db33b62b0dcb3

### :memo: Documentation

- :memo: payment trait documentation update by @gunesbizim in https://github.com/unusualify/modularous/commit/7a77a50fb0b6046291ae1ea10ebd9880f70a50f4
- :memo: add brief documentation about filepond usage and component by @ilkerciblak in https://github.com/unusualify/modularous/commit/a786fee15650e17dcd435ef9f9de0095b0a8ed2e
- :memo: payment documentation update by @gunesbizim in https://github.com/unusualify/modularous/commit/899a8d4197adb62369b9edf37c612682e4d5e984
- :memo: payment documentation grammer and typo fixes by @gunesbizim in https://github.com/unusualify/modularous/commit/60c9c9169af309ce7796c8364e4f5942bdc7e2e8
- :memo: payment documentation grammer and typo fixes by @gunesbizim in https://github.com/unusualify/modularous/commit/51fcf5b6e4d73204aa11559227328526161fcd9d
- :memo: add feature related documentation about filepond and file storage by @ilkerciblak in https://github.com/unusualify/modularous/commit/886bdef6899f23557f2ac682946de64c8f4bca44
- add checklist-group input by @OoBook in https://github.com/unusualify/modularous/commit/d121469db52e7e55e77d88e24e0ba8e6ef96e3d5
- add radio-group input by @OoBook in https://github.com/unusualify/modularous/commit/56874a8c8e2bbd578b276de033d8dbe5be7fbf69
- change namespace of priceable on payment doc by @OoBook in https://github.com/unusualify/modularous/commit/f8c14f1e499b12088735873ed44df26c133aef82

### Styling

- :lipstick: refactor filling main body on master, and Table.vue by @OoBook in https://github.com/unusualify/modularous/commit/c53b3a0ae9ba2c037b54627437ba4d819ce9baa6
- fix psr-4 issues on advancedFilters by @OoBook in https://github.com/unusualify/modularous/commit/abfdb3c91cf032ea0d564c3ab31b2b97ccd44e7b
- fix space issue of route-controller.stub by @OoBook in https://github.com/unusualify/modularous/commit/fc6f0b413989147244c083114c8f5efbafd4db0d
- :lipstick: success color update by @gunesbizim in https://github.com/unusualify/modularous/commit/6ed227afe6fa8045335de2cb2d06562878df5c39
- :lipstick: change Logout buttons by @OoBook in https://github.com/unusualify/modularous/commit/659def38d1f9daa746d6c95a6128625a0e51137d
- remove resposne log on SAVE_FORM by @OoBook in https://github.com/unusualify/modularous/commit/76d83ccefcafdaf67b9c5febc3120ecc091ef364
- fix psr-4 standards of PanelController by @OoBook in https://github.com/unusualify/modularous/commit/f24dd00ec9c91c5bcbba322cc424e326571adbdd
- comment unnecessities of RelationTrait by @OoBook in https://github.com/unusualify/modularous/commit/bd27c28cc6d03e9b8ca135a3018e0ec2b91094ef
- cleaning some files by @OoBook in https://github.com/unusualify/modularous/commit/c6ad3cc27370592987098d4cbf269faba8ae4a60
- clean LanguageMiddleware by @OoBook in https://github.com/unusualify/modularous/commit/131a5551d43604e480851fb40308e912354ac3a5

### :white_check_mark: Testing

- add RouteGeneratorTest draft by @OoBook in https://github.com/unusualify/modularous/commit/d5b69da1915abfdee7fa69878b1ca28740f2fc7c
- :white_check_mark: add test drafts of FileActivator and RouteGenerator by @OoBook in https://github.com/unusualify/modularous/commit/9c9645d69dd642f1c72d025550f37cb0f20d7e51

### :green_heart: Workflow

- add vite test action for vue sources by @OoBook in https://github.com/unusualify/modularous/commit/5bf59ec76e4af0b1308847815e6ca01b269d1d3e
- :green_heart: add laravel matrix tests by @OoBook in https://github.com/unusualify/modularous/commit/9baa3cb76f0e845c73ed0d993aaea48099cada35
- change on push event as only for dev branch by @OoBook in https://github.com/unusualify/modularous/commit/104d40dea36c172583561f4a4d47709a67761cb8
- add releasing workflow by @OoBook in https://github.com/unusualify/modularous/commit/2cd4c9e0ef195e9a2c35b669e07b1068109ea32f
- add workflow_dispatch for manual release by @OoBook in https://github.com/unusualify/modularous/commit/4019b383e71cf20b94d7a957923416eac3bda1df
- :green_heart: add update changelog workflow by @OoBook in https://github.com/unusualify/modularous/commit/839d3e1ef4104ce1b20b5746bdd74661fee41b48

### :beers: Other Stuff

- :art: introduce some more property definition on new file component by @ilkerciblak in https://github.com/unusualify/modularous/commit/c52b027cb58363585a3ce5994fca7318853e4a1d
- :wrench: restructure lang files to publish and merge by @OoBook in https://github.com/unusualify/modularous/commit/3c2eb1a22dc83421d91087b193416c47c389cec9
- :art: improve code structure of the filepond component by @ilkerciblak in https://github.com/unusualify/modularous/commit/4d43ad085c749eaceebf5e69808182873202984c
- :art: introduce more properties in manageform process by @ilkerciblak in https://github.com/unusualify/modularous/commit/800f238382246116fcbcc1ebc5e2167c5579f723
- Merge remote-tracking branch 'origin/feature/payment-trait' into dev
- Merge remote-tracking branch 'origin/feature/payment-trait' into dev
- Merge remote-tracking branch 'origin/feature/payment-trait' into dev
- add draft structure for draggable Table by @OoBook in https://github.com/unusualify/modularous/commit/7adaeed6b1376dc8d00c3e3a184948cadef4cbf3
- rename draggable on Repeater by @OoBook in https://github.com/unusualify/modularous/commit/e2a7a168c95068e640d177d7941b49a1cf2e64fa
- add faq and all keys by @OoBook in https://github.com/unusualify/modularous/commit/189e692b04573ad6c7e5455f95f5d812ae0089c6
- Merge remote-tracking branch 'origin/feature/payment-trait' into dev
- Merge remote-tracking branch 'origin/feature/filepond-implementation' into dev
- Merge remote-tracking branch 'origin/docs/filepond-documentation' into dev
- remove NodeTrait because it does not exists on composer by @OoBook in https://github.com/unusualify/modularous/commit/f66a32abe7b15d6427c054c2c87478ccdde6d173
- style(): fix psr-4 issues on traits
- Merge remote-tracking branch 'origin/dev' into feature/draggable-table-row
- :zap: add backend functionality of the draggable feature by @ilkerciblak in https://github.com/unusualify/modularous/commit/50bba0a128173f5de4705d747df582664b396a02
- change language and timezone input wrt input type by @OoBook in https://github.com/unusualify/modularous/commit/e445d6ad869bebed2ff1797dc134cbd7eae65985
- Merge remote-tracking branch 'origin/feature/draggable-table-row' into dev
- Merge remote-tracking branch 'origin/feature/CreditCard' into dev
- Merge remote-tracking branch 'origin/bugfix/filepond-remove-bug' into dev
- add payable package in composer by @OoBook in https://github.com/unusualify/modularous/commit/c6bbf298f2b6d7029eb5a3e547296f3057601ea9
- by @OoBook in https://github.com/unusualify/modularous/commit/6fefa596f2469e0d3c22372979b72c174fb3683c
- Merge remote-tracking branch 'origin/dev' into feature/payment-currency
- Merge remote-tracking branch 'origin/dev' into feature/payment-currency
- :fire: remove alert mixin by @OoBook in https://github.com/unusualify/modularous/commit/058fa5eff00ebfd8ebdd5b458722363f7393405c
- unset lang-publish by @OoBook in https://github.com/unusualify/modularous/commit/3bdb1418418e7664630473226b5ef8101517235e
- add item to formData as parameter by @OoBook in https://github.com/unusualify/modularous/commit/e693a085d8cd2ebd1275bba1c0e3d6736039f9f2
- add hidden prop to developer commands by @OoBook in https://github.com/unusualify/modularous/commit/67f877195643449385ea3264cef325f8b1310d97
- :coffin: remove dead-catty table names by @OoBook in https://github.com/unusualify/modularous/commit/9fd41f248ef395962106aeafd91e1b3e775f4446
- separate packages from oobook by @OoBook in https://github.com/unusualify/modularous/commit/82fa7109b72158ca9dba1b2b5b0ac52330572c10
- Merge remote-tracking branch 'origin/refactor/make-table-modals-object' into dev
- Merge remote-tracking branch 'origin/feature/payment-currency' into dev
- Merge remote-tracking branch 'origin/feature/svg-icon' into dev
- Merge remote-tracking branch 'origin/feature/recursive-stuff' into dev
- Merge remote-tracking branch 'origin/bugfix/payment-trait' into dev
- Merge remote-tracking branch 'origin/feature/authentication-pages' into dev
- Merge remote-tracking branch 'origin/feature/authentication-pages' into dev
- Merge remote-tracking branch 'origin/test/draft-tests' into dev
- Merge remote-tracking branch 'origin/test/draft-tests' into dev
- add CHANGELOG file by @OoBook in https://github.com/unusualify/modularous/commit/b196b19b3e434c865e2c906721ce67da51d0bbb5

## v0.0.0 -

- Initial Tag
