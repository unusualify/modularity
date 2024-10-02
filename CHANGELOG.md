# Changelog

All notable changes to `modularity` will be documented in this file

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
