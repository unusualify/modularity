# Changelog

All notable changes to `modularity` will be documented in this file

## v0.45.0 - 2025-09-08

### :rocket: Features

- integrate logo symbol into authentication layout by @OoBook in https://github.com/unusualify/modularity/commit/88e23216809d2c818eee52b050aee66931bb4f7c
- add DateHydrate class and Vue component for date input handling by @OoBook in https://github.com/unusualify/modularity/commit/3251afc3b9e5537e24f104bea0d50ede208cc0ca
- integrate Revolut Checkout components and update dependencies by @OoBook in https://github.com/unusualify/modularity/commit/59bdcc03f1091c0673cf4dc3016c62cd8f694b0a
- enhance input fields with density prop and adjust styles by @OoBook in https://github.com/unusualify/modularity/commit/b77410a59eaf93ce0af3a8eee5358970f8e1f32e
- add service class and built-in form attributes by @OoBook in https://github.com/unusualify/modularity/commit/0241f06d57a892867b400c424ffd5f2a60ea2026
- add attributes for credit card payment service and built-in form by @OoBook in https://github.com/unusualify/modularity/commit/e58c07a09744ba692d137b5a5a85cf763297e20b
- implement checkout method for payment processing by @OoBook in https://github.com/unusualify/modularity/commit/0b43fe7f0fb66cf47b3b90dfb1369860c3245cf7
- refactor currency handling and integrate built-in payment form by @OoBook in https://github.com/unusualify/modularity/commit/8f0d2b6c2f7a1ff4f38c4af8d55933394a51afbd
- add logout confirmation messages and titles by @celikerde in https://github.com/unusualify/modularity/commit/1db313b671e18a69a345145cbdaa28535544172d
- add English language support for payment messages by @celikerde in https://github.com/unusualify/modularity/commit/fb73b95c0c80c9eacf0298a8b0f6d77545e30ca4
- enhance dialog messages and layout for process updates by @celikerde in https://github.com/unusualify/modularity/commit/b0ad89eec7b29f4afa9da70f7c07898669a05308
- enhance action handling with dynamic confirmation modals by @celikerde in https://github.com/unusualify/modularity/commit/8b70896f8dbfb9b12dac7b9f4e2cb8e954edec5c
- add trait for managing changed relationships by @celikerde in https://github.com/unusualify/modularity/commit/baf86c4ae20663cb122c2b29719320677f7c6a51
- dispatch unread chat message event on notification handling by @celikerde in https://github.com/unusualify/modularity/commit/484612dc1238012bf47d36c150ea8990e6ff3c85
- enhance afterSaveRelationships method to track changes by @celikerde in https://github.com/unusualify/modularity/commit/3724fecfee3b9c5d8c5a261bd7fe12072821a72b
- integrate ChangeRelationships trait for enhanced relationship management by @celikerde in https://github.com/unusualify/modularity/commit/9bf4de13facb322c661e6a9f886b71667cc9c05d
- add custom ResetPasswordNotification for enhanced user experience by @celikerde in https://github.com/unusualify/modularity/commit/54a625fafb7392ac48a338221dea5936a0447c7d
- add sendPasswordResetNotification method for password reset functionality by @celikerde in https://github.com/unusualify/modularity/commit/661b5ce2ed508e195e5af7770b8d17abcc83ac6d
- add event dispatching for model creation and updates by @celikerde in https://github.com/unusualify/modularity/commit/507d8984bc8653a81f7ca0e381186335657e8523
- introduce AfterSendable interface for notification handling by @celikerde in https://github.com/unusualify/modularity/commit/bf8be5dbdb9619449699d23c20d155ec12610f0d
- add valid channels and validation methods for notifications by @celikerde in https://github.com/unusualify/modularity/commit/dafd9fc5889939e176f591c32b4428157fe90e71
- add events for authorizable creation and updates, and unread chat messages by @celikerde in https://github.com/unusualify/modularity/commit/3b9d8a791ab7df519ea515b499d3b7b605df875d
- add title justification property to form component by @OoBook in https://github.com/unusualify/modularity/commit/55ca4e482762ead1511915f8b10b548180867e57
- add computed properties for delete dialog title and description by @OoBook in https://github.com/unusualify/modularity/commit/b1cc8a4c7c31fda5a9b410cac6de5c557956e6cf
- add valid_company attribute and enhance company validation logic by @OoBook in https://github.com/unusualify/modularity/commit/3a0f74d0d0033deca184f24eccdf369b48ff4e72
- include additional user attributes in profile data by @OoBook in https://github.com/unusualify/modularity/commit/9b9485c1c9c4d03ab7b952bdf82cae191d76b805
- enhance condition checks for item actions by @OoBook in https://github.com/unusualify/modularity/commit/669b1c1e023d1a7496595fa154bfb406520f4c17
- enhance form action handling with draft support by @OoBook in https://github.com/unusualify/modularity/commit/2712858d995a04b411a8bcc833af4210ac94a4d7
- enhance notification channel handling by @OoBook in https://github.com/unusualify/modularity/commit/313842ebb49fe8cb26fab0ca4e0dea31faeb721f
- add Revolut payment service configuration and image by @OoBook in https://github.com/unusualify/modularity/commit/3b871543e62ac73e4a8d22310a906fb235e46810
- enhance condition evaluation with support for complex logic by @OoBook in https://github.com/unusualify/modularity/commit/089a98c581f14fc9c35e319298fae8e6ea6c1675

### :wrench: Bug Fixes

- simplify body description structure in modal by @OoBook in https://github.com/unusualify/modularity/commit/f0c112515d2313feb9c4a7ddb0a3094200f99b80
- improve body description structure in modal by @OoBook in https://github.com/unusualify/modularity/commit/80174d46920ec4a022510b18185c7d703a826c76
- update success message for task assignment notification by @celikerde in https://github.com/unusualify/modularity/commit/cae2087c16e91ed8334fc20b833ae7228ea5823e
- correct error handling response data structure by @celikerde in https://github.com/unusualify/modularity/commit/5747cb79a5e8e73a0efd04e3851bec0abd6e4d36
- update response modal message styling for better readability by @celikerde in https://github.com/unusualify/modularity/commit/ca022c244b42905e4592d04afd4b14093ca7d5d7
- update button text for password reset form by @celikerde in https://github.com/unusualify/modularity/commit/bea43f000a84ee5e4c3b4af9ec5cc882bbfdd080
- update success message for profile update response by @celikerde in https://github.com/unusualify/modularity/commit/699077df333459a3e2a6a0c524d5665c52152a13
- enhance validation rules for form fields by @celikerde in https://github.com/unusualify/modularity/commit/96ea09a24c55b6f2eb655f0abe7953f1dea5df75
- update companies name field length and related fields by @celikerde in https://github.com/unusualify/modularity/commit/ed3d93c05667953bab4ae3c79ed8ef52ef8cb531
- reorder modal attributes for consistent rendering by @OoBook in https://github.com/unusualify/modularity/commit/d36f4ce2bf60a25fd08ef3736d2605fbfd697a4e
- add padding to input components by @OoBook in https://github.com/unusualify/modularity/commit/750acd3263652f90cbbbfe9c65143fdf73af6a87
- enhance file preview functionality with conditional download by @OoBook in https://github.com/unusualify/modularity/commit/55ab5a9070437fce517ae5bdd03ced079637e16f

### :recycle: Refactors

- update payment routes and enhance payment service hydration by @OoBook in https://github.com/unusualify/modularity/commit/8c5860f24c1ea2050a2095ee1c00d75af2f5b78a
- update logout messages for localization by @celikerde in https://github.com/unusualify/modularity/commit/fa22f3d5098f435670429ca7a0496fd1d9955df0
- enhance state management with caching and attribute access by @celikerde in https://github.com/unusualify/modularity/commit/848b199e13339a91408c9ef37356f878de2556b6
- update modal configuration and comment out unused message field by @celikerde in https://github.com/unusualify/modularity/commit/30500b79b1b9f40dcc1a6a6b3a5fe97d1e40c2fb
- localize payment success and error messages by @celikerde in https://github.com/unusualify/modularity/commit/076d14dee9cb3a84b27bce1deb36aae942ed82b6
- clean notification channel values for improved validation by @celikerde in https://github.com/unusualify/modularity/commit/553a6124a61b8cecb9813c62ab9f815ee0c9adb4
- implement AfterSendable interface and enhance channel validation by @celikerde in https://github.com/unusualify/modularity/commit/42da0a372a6caf461347351ef8bcdd94a2347ad3
- streamline login shortcut schema handling by @OoBook in https://github.com/unusualify/modularity/commit/52e2102d41071b529e4f128c064523953baab1ee
- enhance attribute casting logic and introduce matching functions by @OoBook in https://github.com/unusualify/modularity/commit/8513a0745541deb744b298af1fd338bc564521a9
- streamline attribute casting and matching logic by @OoBook in https://github.com/unusualify/modularity/commit/7593edbf38d7c337e6bbbf8196e84670ca521c8a
- integrate attribute casting utility and simplify value matching by @OoBook in https://github.com/unusualify/modularity/commit/ab7729b628ff77a0fb780ebd23f65996f215d3dc
- remove unused formatter and simplify action formatting by @OoBook in https://github.com/unusualify/modularity/commit/f856de9dd37d2d2aea9201322012027e82c62f40
- remove deprecated casting logic to enhance clarity by @OoBook in https://github.com/unusualify/modularity/commit/dff237d382aa3fbeee856bf49759235efe8f4fd1
- remove deprecated value matching functions for improved clarity by @OoBook in https://github.com/unusualify/modularity/commit/d9f3810d1b2bf40031982643a64787e07b1d6b61
- remove fullscreen button from modal and update delete dialog attributes by @OoBook in https://github.com/unusualify/modularity/commit/f9e3864244e88e5e2b4844fe5a32b8bed5582a3c
- update deletion confirmation messages for improved clarity by @OoBook in https://github.com/unusualify/modularity/commit/2508caa29adb603c995a5b325179cfc3827e38a0
- update afterNotificationSent method to accept notifiable parameter by @OoBook in https://github.com/unusualify/modularity/commit/61bd5b688f2f2107b824aaa9b975f3d588e59926
- introduce TaskCreatedNotification and update notification handling by @OoBook in https://github.com/unusualify/modularity/commit/3206d9829dd9876bd413f0183441194f3a336303
- remove via method from notification classes by @OoBook in https://github.com/unusualify/modularity/commit/084605bb3d46ffc830bdf8954fdd3fc64bcf1de0
- enhance table row actions with dynamic conditions and form attributes by @OoBook in https://github.com/unusualify/modularity/commit/b8991625c5f54dd137e20c757f694903a6bb6ee3
- update modal attributes and improve styling by @OoBook in https://github.com/unusualify/modularity/commit/b2f93004bf45c6288c4a2da1fbc505f9249b7a03
- integrate Vuex store for enhanced condition checks by @OoBook in https://github.com/unusualify/modularity/commit/3efd7de81dac89d1bb6e3ae8bd8523de44e387c9

### :lipstick: Styling

- lint coding styles for v0.44.0 by @invalid-email-address in https://github.com/unusualify/modularity/commit/30957ac09dfe081d981e90e20a8ba9aa6891d2dd
- improve method formatting for clarity by @OoBook in https://github.com/unusualify/modularity/commit/01b193a0aa1a022a25b82442ba266c4e6b461938

### :white_check_mark: Testing

- add comprehensive unit tests for condition evaluation functions by @OoBook in https://github.com/unusualify/modularity/commit/20291944f980b02a49fa3c2dc1a34ee54eb533d8

### :package: Build

- update build artifacts for v0.44.0 by @invalid-email-address in https://github.com/unusualify/modularity/commit/5d8f4d069d27386149fed081f2f8668635d0507e
- update build artifacts for v0.45.0 by @OoBook in https://github.com/unusualify/modularity/commit/27aa9a827266804f27cdbfd67ccaf7db52d22222

### :beers: Other Stuff

- update unusualify/payable dependency version to ^0.12 by @OoBook in https://github.com/unusualify/modularity/commit/30ab7659a5d76076650d9da54c254cf8929824aa
- rename companies table in migration file by @celikerde in https://github.com/unusualify/modularity/commit/00a8c9e4a8996ec01a5c770c596d406f198ac5e4

## v0.44.0 - 2025-08-26

### :rocket: Features

- enhance value assignment logic for various input types by @OoBook in https://github.com/unusualify/modularity/commit/b4a11c985038860f8c151c4e7bd1278a2b056ffa
- enhance message box functionality and textarea behavior by @OoBook in https://github.com/unusualify/modularity/commit/00273292e20e58c406078fb551e7a4bee45c81c5
- add 'density' property to published input type configuration by @OoBook in https://github.com/unusualify/modularity/commit/11b6d050e3e9b80a577d12937f4f05ffe6dd72bf
- enhance modal title customization and layout by @OoBook in https://github.com/unusualify/modularity/commit/99dd9beeb7c765bf2d3cdc2ff7e20aff91196558
- enhance modal functionality and layout by @OoBook in https://github.com/unusualify/modularity/commit/63faece7e701773db027bed71b68842d381ef0e6
- enhance custom form modal attributes and structure by @OoBook in https://github.com/unusualify/modularity/commit/337c23cad94865ce703ebfc6d9dd9ef9391ab23b
- enhance payment form modal attributes and structure by @OoBook in https://github.com/unusualify/modularity/commit/6f07b25e99f43143f1a10c6214a2183ae2985376

### :wrench: Bug Fixes

- add accepted_at field and improve assigner assignment logic by @OoBook in https://github.com/unusualify/modularity/commit/39609d1dbcaf8b422547b5b62b1d2b08f9887bd5
- correct typo in fillable attribute name from 'repatable_id' to 'repeatable_id' by @OoBook in https://github.com/unusualify/modularity/commit/0e27104bbb47caa59a6120ff81d6fde5d16898da

### :recycle: Refactors

- add chat_id to fillable attributes by @OoBook in https://github.com/unusualify/modularity/commit/9011bcfb0ab00030e081d7d6e07a74ee26f94273
- add process_id to fillable attributes by @OoBook in https://github.com/unusualify/modularity/commit/0cd94b8481ddedd2d4aa8b90fe63256f66b68193
- remove unused attributes from fillable array by @OoBook in https://github.com/unusualify/modularity/commit/26920bdfefd716e23331a723f078f9ac51ae080d
- update table name and fillable attributes in Spread model by @OoBook in https://github.com/unusualify/modularity/commit/d8bf8588813788c50ddfbdc0dfbebc7dfd3b970a

### :white_check_mark: Testing

- add comprehensive tests for Assignment model functionality by @OoBook in https://github.com/unusualify/modularity/commit/c026e041c5abdb575fd898c35676c29f2b5dbd1e
- add comprehensive test suites for ChatMessage and Chat models by @OoBook in https://github.com/unusualify/modularity/commit/38c49f9d21bb409127b3a9e158dc00e33aeaf079
- add comprehensive tests for Filepond model functionality by @OoBook in https://github.com/unusualify/modularity/commit/be81654b25ec806873001d451ec561d25fbb2112
- add comprehensive tests for TemporaryFilepond model functionality by @OoBook in https://github.com/unusualify/modularity/commit/cca080b37ef62c57985cd55513ed2cc1c8463b48
- add comprehensive test suites for Process and ProcessHistory models by @OoBook in https://github.com/unusualify/modularity/commit/a57244152352c7da75257538a9fecb31c4ebeb67
- add comprehensive test suites for Stateable and State models by @OoBook in https://github.com/unusualify/modularity/commit/80dab314fe859e9b7ccb9a1e45f3949ec55fa73d
- add comprehensive tests for Authorization model functionality by @OoBook in https://github.com/unusualify/modularity/commit/bc6c115260cc6d856bdf9a20b594b68403bc5080
- add comprehensive tests for CreatorRecord model functionality by @OoBook in https://github.com/unusualify/modularity/commit/3e03cd2c3f9f5222edf87be738fda079dbaffdda
- add comprehensive tests for Repeater model functionality by @OoBook in https://github.com/unusualify/modularity/commit/f6d76ad191a688f2e28c24c9227e7ca01d82bdff
- add comprehensive tests for Singleton model functionality by @OoBook in https://github.com/unusualify/modularity/commit/04532237fdb8f9af8281150315641fa198e606c5
- add comprehensive tests for Spread model functionality by @OoBook in https://github.com/unusualify/modularity/commit/41e77f67a7b2ca66b32f39499ce208716f43ede9

### :package: Build

- update build artifacts for v0.43.0 by @invalid-email-address in https://github.com/unusualify/modularity/commit/bdaaa10c77ec9868d8171e5fa46e8cd1c12f0ead
- update build artifacts for v0.44.0 by @OoBook in https://github.com/unusualify/modularity/commit/ba0786f2a6f304424c30970759c7db7347a339dd

## v0.43.0 - 2025-08-25

### :rocket: Features

- enhance modal handling with URL parameters by @OoBook in https://github.com/unusualify/modularity/commit/a979910fee094d033e65804a4dc03c564965b283

### :wrench: Bug Fixes

- update validation triggers for form fields by @OoBook in https://github.com/unusualify/modularity/commit/464017aa88762d2d30b12f96d47b2855db22ca5d
- enhance global error handling notifications by @OoBook in https://github.com/unusualify/modularity/commit/6743fbe22fa34f9d54a717f746ec3808a7454fc0
- improve error handling for 403 status by @OoBook in https://github.com/unusualify/modularity/commit/f71c41043e283a6aeff135bf50568eefadb276a7
- remove validation triggers for password fields by @OoBook in https://github.com/unusualify/modularity/commit/fca871f6e9db79422187285e7cd2d733c5a20387

### :recycle: Refactors

- unify formatter handling and improve tooltip functionality by @OoBook in https://github.com/unusualify/modularity/commit/e9e4ce1a76b9cbf1d91da0b8b87512a3b7ccc8e3
- replace createModalService with modularity_modal_service by @OoBook in https://github.com/unusualify/modularity/commit/768cbf40f698fc9389d14045b1a3d028e1572f03

### :package: Build

- update build artifacts for v0.42.2 by @invalid-email-address in https://github.com/unusualify/modularity/commit/9e8faa8f7d989e856140c061aad42b990df77db8
- update build artifacts for v0.43.0 by @OoBook in https://github.com/unusualify/modularity/commit/b1c9d2267a1f007daa53048494515b348a56abc7

## v0.42.2 - 2025-08-21

### :rocket: Features

- enhance dynamic Vue component creation and directive handling by @OoBook in https://github.com/unusualify/modularity/commit/5f659327e8991f381d42185a92d72a032f75ae1f

### :recycle: Refactors

- update component structure for improved directive handling by @OoBook in https://github.com/unusualify/modularity/commit/5fede90dab8bfea98d63e3348d3045d7508cfcbd

### :package: Build

- update build artifacts for v0.42.1 by @invalid-email-address in https://github.com/unusualify/modularity/commit/2a897b6f22c40c2f6db5a7f7248524aa18a1ce97
- update build artifacts for v0.42.2 by @OoBook in https://github.com/unusualify/modularity/commit/3c3b7ccd2b810125d3e5702a72d8dfcfe1e17721

## v0.42.1 - 2025-08-20

### :wrench: Bug Fixes

- simplify accepted file types in AssignmentHydrate and update filepond name in ChatHydrate by @OoBook in https://github.com/unusualify/modularity/commit/6149b805ff301715730958889270e7c6cfc67815
- refine layout classes for improved UI consistency by @OoBook in https://github.com/unusualify/modularity/commit/c3cba2ee3d493903c3856a33c6b2fd4b4f2ade1f
- wrap modal content in flex container for improved alignment by @OoBook in https://github.com/unusualify/modularity/commit/2c7753ef74558e357b3362e56451e0fe87abfca8
- update default class for improved spacing in form component by @OoBook in https://github.com/unusualify/modularity/commit/37bf51e2d55abd125dbfa688cd3d3dc1f2e4ed2b

### :lipstick: Styling

- lint coding styles for v0.42.0 by @invalid-email-address in https://github.com/unusualify/modularity/commit/7e9ff30fb36e22518df17c4e3c9f3885cd0c23b3

### :package: Build

- update build artifacts for v0.42.0 by @invalid-email-address in https://github.com/unusualify/modularity/commit/1a7b5d1278b79c734cc4ef687469946491fbf3be
- update build artifacts for v0.42.1 by @OoBook in https://github.com/unusualify/modularity/commit/b67c3cd0872987ace2832cbcdca0a9f461566522

## v0.42.0 - 2025-08-20

### :rocket: Features

- enhance process status handling and UI interactions by @OoBook in https://github.com/unusualify/modularity/commit/8910ccce3a32ca3a42133cc6eedb3187ca8d54ee
- add syncStateData method to manage absent states by @OoBook in https://github.com/unusualify/modularity/commit/52439b5233f873bc2444514ccf65bc706bd0ff8d
- add console command for syncing stateable model states by @OoBook in https://github.com/unusualify/modularity/commit/7c98a72de40607d3d0c3fb08ec5df2adc41aedb1
- enhance mobile responsiveness and styling options by @OoBook in https://github.com/unusualify/modularity/commit/f7588f497f73f454425b01acd6c3c383deadaa0d
- add sidebar bottom navigation group by @OoBook in https://github.com/unusualify/modularity/commit/8ffcbee5834749998ff5be678d9b8d28cd3df6a6
- enhance data handling with lodash get function by @OoBook in https://github.com/unusualify/modularity/commit/e996c34083d7cabb7a9c645344d2a747ab3d1f3a
- update sidebar and profile menu configuration by @OoBook in https://github.com/unusualify/modularity/commit/f27d24e663e0e135839da6652003e7036b98defa
- log successful state synchronization message by @OoBook in https://github.com/unusualify/modularity/commit/e0b02d67833ca143a23d8e261430146a53fdf29c
- enhance rate limiting logic with host validation by @OoBook in https://github.com/unusualify/modularity/commit/13808bbefabc1cda77d4c990ccd43882af7bad70
- enhance table functionality with selectable rows and improved header rendering by @OoBook in https://github.com/unusualify/modularity/commit/841903fedb462bed208acce8cf95cd57f6a2d45d
- update payment status enum and add utility methods by @OoBook in https://github.com/unusualify/modularity/commit/fb2401fbb4f1d2287a95176426e6a3aacd896ba9
- streamline form schema and table header handling by @OoBook in https://github.com/unusualify/modularity/commit/2336f4b615a0078a4a129d6e553d2a3d0a036d77
- enhance payment model with status attributes and global scope by @OoBook in https://github.com/unusualify/modularity/commit/35bca03633a7be73b8b0e7fb220ab5c01c9409de
- add saveForm functionality to form submission handling by @OoBook in https://github.com/unusualify/modularity/commit/b80f812d00a7eb5939200578c132f1b634f2c4ca
- enhance PaymentService with spreadable trait by @OoBook in https://github.com/unusualify/modularity/commit/1e7ad066a94ac54793dedfab28a1a2da5d522102
- implement ShouldQueue interface for asynchronous processing by @OoBook in https://github.com/unusualify/modularity/commit/a6f9ae2256f40294e2ea15e67b9c299706a6286b
- add bank receipts and transfer details functionality by @OoBook in https://github.com/unusualify/modularity/commit/f0929e8c4d13fd71c2a95e6888cbd05f0cfda69a
- enhance payment service input handling and transfer functionality by @OoBook in https://github.com/unusualify/modularity/commit/e0f65d68316f642b4936991ed7af17432e21039c
- add updateOrNewPayment method for payment management by @OoBook in https://github.com/unusualify/modularity/commit/2dd857c6342d2114420d89c5a004575e9e647486
- enhance payment processing with transfer support and validation by @OoBook in https://github.com/unusualify/modularity/commit/b2ca9e47bfb0c16584d089ce83c1c718284d1ec3
- add emoji picker component by @celikerde in https://github.com/unusualify/modularity/commit/1de3858a0cf2d1d638e13eb4c107bc67f9852af4
- enhance chat input functionality and UI by @celikerde in https://github.com/unusualify/modularity/commit/aaeb16fece40ad21ab5dd27a297443d1cfc8b0b7
- enhance email verification process with dynamic parameters by @OoBook in https://github.com/unusualify/modularity/commit/03d7771af8c00dfc24cee0dd97035a502300ca0d
- enhance registration notification with dynamic parameters by @OoBook in https://github.com/unusualify/modularity/commit/c4e1879c0917636ed631793cf819b9babfc896be
- enhance complete registration form and event handling by @OoBook in https://github.com/unusualify/modularity/commit/c16e9e361e7680a257ac13ac7296be8157676d4f
- enhance user registration process with company association by @OoBook in https://github.com/unusualify/modularity/commit/5ca884b3e59452a06abacf3707abb4b67a007b76
- add email verification option for registration by @OoBook in https://github.com/unusualify/modularity/commit/25c47509abe299446d0e673680e771d568305793
- enhance registration form styling and event handling by @OoBook in https://github.com/unusualify/modularity/commit/9b832046cc35c674542c927ef28f9aed9e5f921a
- implement redirect functionality with middleware and service by @OoBook in https://github.com/unusualify/modularity/commit/e6f4dc713f4c5bbd31431f798e000d4c63bef68a
- add stop on defect option for PHPUnit testing by @OoBook in https://github.com/unusualify/modularity/commit/1392671e07a5af4a59f8b10fd70ae8f7ffe03395
- enhance form actions retrieval and merging logic by @OoBook in https://github.com/unusualify/modularity/commit/9fdff977302718a9a4f1ad6f2a8547aacd0a6130
- enhance payment form actions and conditions by @OoBook in https://github.com/unusualify/modularity/commit/cb4924e62d94f0ab9c97d061b7278492c08afdc9
- add payment completion check and modal service handling by @OoBook in https://github.com/unusualify/modularity/commit/50b50c1a1ebfb9b157e8e321c6531059cc9dce99
- enhance pinned message display and layout adjustments by @OoBook in https://github.com/unusualify/modularity/commit/b819327dc278b3d8c94cfeb3902567e68b280c53
- enhance modal handling with URL parameters by @OoBook in https://github.com/unusualify/modularity/commit/4f1b8151f3229d079cc0bda2b45cd2fc6246456c
- enhance input hydration and formatting functions by @OoBook in https://github.com/unusualify/modularity/commit/246332f49783bb5a33854e8be13792471cbc5f19
- add modularity modal and form service functions by @OoBook in https://github.com/unusualify/modularity/commit/c8f3d8405facd17cb7a70251fc832c272d76b5c6
- add modal service API endpoint for session data retrieval by @OoBook in https://github.com/unusualify/modularity/commit/3ce2d48857c221b581251829f42c3e810e5544c8
- implement dynamic payment middleware configuration by @OoBook in https://github.com/unusualify/modularity/commit/6b220904e1ba27fc9dc5f73c40328e32ccf04d15
- update form draft settings and labels by @OoBook in https://github.com/unusualify/modularity/commit/37c94777953465fafac9a9f1ef1732c78e6db100
- enhance state hydration and configuration methods by @OoBook in https://github.com/unusualify/modularity/commit/d44bffc1fb35c5d4c12e816d17d0bfca3b4cd648
- add email verification pre-registration flow by @OoBook in https://github.com/unusualify/modularity/commit/c9461bbf93ed7f9fd459330a6437808e1241ef1c
- implement email verification check for registration by @OoBook in https://github.com/unusualify/modularity/commit/9a7d06f0d5f6545528e760fb39447a50802b929e
- add dynamic column configuration for image display by @OoBook in https://github.com/unusualify/modularity/commit/475826ce63a33d5cd1101cbaa3e08431960de6dc
- add dynamic column configuration for image display settings by @OoBook in https://github.com/unusualify/modularity/commit/3027386ef40179191bc544f404fff99bb59b5324
- add ABN AMRO payment service images by @OoBook in https://github.com/unusualify/modularity/commit/9b188e036bac49837698ea427553e95186140503

### :wrench: Bug Fixes

- update Google sign-in button label for OAuth consistency by @celikerde in https://github.com/unusualify/modularity/commit/bb9dcaf2b62acc654b9338cf1a21da783eca11de
- update Google sign-up button label and route for OAuth by @celikerde in https://github.com/unusualify/modularity/commit/15b73990727aadb82bc149f30d469e3c752fa074
- update Google sign-in button label and route for OAuth, remove Apple sign-in button by @celikerde in https://github.com/unusualify/modularity/commit/eae18c748d6d9888c6c58777a5569f61f7ddbc87
- remove unnecessary condition in bottom slot rendering by @OoBook in https://github.com/unusualify/modularity/commit/30dae37c1889efdbb80c9021b4281158abe34910
- correct request parameter for eager loading includes by @OoBook in https://github.com/unusualify/modularity/commit/895ceb8d5cfc41ee48f9c75e1b51c4f2239e968c
- enhance includes handling for eager loading by @OoBook in https://github.com/unusualify/modularity/commit/6e8c774a992c62b25ddc61a57a18e76c6791635a
- enhance scrollbar styling for improved aesthetics by @OoBook in https://github.com/unusualify/modularity/commit/51546cf09fc6e382be6b6048ccf230a7cddbd665
- update creatable class reference in company relationship query by @OoBook in https://github.com/unusualify/modularity/commit/cbfe83ffa0d99a90be5b664d2a9720edd60dae34
- improve route configuration update logic by @OoBook in https://github.com/unusualify/modularity/commit/4d912d93eee95b556d48cea58ae5377c859f90b8
- improve source normalization logic by @OoBook in https://github.com/unusualify/modularity/commit/88d326bb3e18f7e7303678b7d5faa5ca8d23a5bd
- improve handling of newValue for array and string types by @OoBook in https://github.com/unusualify/modularity/commit/aaebfcb4e3706acedff39c686d1c940b7725c5b0
- override dynamic service class retrieval for payment gateways by @OoBook in https://github.com/unusualify/modularity/commit/835f5709ec4084e0578b91b18635159fddc57241
- update file upload constraints for transfer receipts by @OoBook in https://github.com/unusualify/modularity/commit/14c02e0386f444e9f9e2b3753ce93b8ff9e32004
- use null coalescing assignment for label initialization by @celikerde in https://github.com/unusualify/modularity/commit/298f5457baed0f4073b8fab507825b74ae93c766
- ensure company_id check for role authorization in query scope by @OoBook in https://github.com/unusualify/modularity/commit/dd2af6d26445683ecad0d9310fe86ef08592365c
- update maxRule logic to handle empty values correctly by @OoBook in https://github.com/unusualify/modularity/commit/5c93caa35fd4d77df77ca48fa9f82faaaa241c6f
- update content field of chat_messages table to be nullable by @OoBook in https://github.com/unusualify/modularity/commit/7ace2b47505964d0f3785f654264f30b66ef62d1
- handle null content in message formatting by @OoBook in https://github.com/unusualify/modularity/commit/e9f381680ac8ffa8bcbe61bd80001275ed70589c
- improve form actions merging logic by @OoBook in https://github.com/unusualify/modularity/commit/7eea6ebe55589cc4ae425b7681de66802ac700dd
- update dynamic attribute syntax for message and redirect elements by @OoBook in https://github.com/unusualify/modularity/commit/0e467c510c2aa3b933d09223118535315e71274f
- adjust class attributes for improved layout consistency by @OoBook in https://github.com/unusualify/modularity/commit/d8e715effc4f3d459e023c725f820624945d7783
- remove unnecessary console log for title props by @OoBook in https://github.com/unusualify/modularity/commit/8742a5e3e0ad33097f3c60753bb72392ecf0663d
- update key handling and conditional rendering logic by @OoBook in https://github.com/unusualify/modularity/commit/303bb5590b8baae2d3d0ffee5c4d81d59a9c8054
- update file upload settings for avatar component by @OoBook in https://github.com/unusualify/modularity/commit/46c6e5f761181c87123856b184b1ea8cdea049ab
- update attribute naming for image preview setting by @OoBook in https://github.com/unusualify/modularity/commit/aa3ad4a98711b8e5dbcf582c12d1a2ea3f904bbc
- add density property to componentProps for compact display by @OoBook in https://github.com/unusualify/modularity/commit/730b16678b7da8a6a76e05be6eb09a75448c94a4
- update class for card text alignment in modal component by @OoBook in https://github.com/unusualify/modularity/commit/29f0911458edadaafa5297eb6cda56ca344f6c71
- update token field type in user_oauths table by @OoBook in https://github.com/unusualify/modularity/commit/6c2e7f312e1d040ea5520c64e70e4c55683f0319

### :recycle: Refactors

- update title alignment and add mobile dialog button by @OoBook in https://github.com/unusualify/modularity/commit/79c5a733f08fd1caf06f0a04e41487d25c4303a7
- update OAuth sign-in and sign-up button labels by @celikerde in https://github.com/unusualify/modularity/commit/0ac1ec5fc2828a33064a9de4d23fb13cbb2315dd
- improve alert commit structure and update card variant by @OoBook in https://github.com/unusualify/modularity/commit/458b235d389bf28ebc996991a1a44967e9c7d0d0
- streamline checkbox layout and improve styling by @OoBook in https://github.com/unusualify/modularity/commit/06cdbbe59bcea9b685bed231a763374a0766b017
- improve styling and structure of the comparison table by @OoBook in https://github.com/unusualify/modularity/commit/291c32562e8eb1c7a19799a800d1cbc52a4c6036
- update payment status and user email configuration by @OoBook in https://github.com/unusualify/modularity/commit/8f68d16c80f11a278288c6c8e0441128fe380826
- remove unused slot comments to clean up template by @OoBook in https://github.com/unusualify/modularity/commit/a772a01ed78f612eda4ebfda86a336c99b3c310f
- update invoice file upload limit and restrict role access by @OoBook in https://github.com/unusualify/modularity/commit/1957b36e87326c1ab127baa00b0fcdb6388d8f92
- streamline element handling and improve attribute casting by @OoBook in https://github.com/unusualify/modularity/commit/69a466369bf7b2c31eaff11bc018688ea12e931c
- enhance relationship data handling in getItemColumnData method by @OoBook in https://github.com/unusualify/modularity/commit/cae68f400b9d26785d2ff643746c4c4cd46f70c0
- enhance key parsing logic for input schema by @OoBook in https://github.com/unusualify/modularity/commit/d0881ff72bccbafb7e695dca46f9e54a55a2d87f
- enhance inputs and chunkInputs methods for improved flexibility by @OoBook in https://github.com/unusualify/modularity/commit/39eebfa97455e87daab931e2650eeb504225b8ca
- update input handling in beforeSave method by @OoBook in https://github.com/unusualify/modularity/commit/3b444a970561ec07c509026004f65f9dfbb18e13
- enhance message content formatting by @celikerde in https://github.com/unusualify/modularity/commit/f89bfc8ee8fda5d8359296cb860c4ea836090695
- improve layout and class management for form components by @OoBook in https://github.com/unusualify/modularity/commit/8e48fc195104d4fb2db85b59b830c56bed704fc6
- streamline user registration verification logic by @OoBook in https://github.com/unusualify/modularity/commit/4dd93869d8c55f45b1d5894f7d1d0639be2723b4
- implement pre-registration and complete registration forms by @OoBook in https://github.com/unusualify/modularity/commit/2b3304d3d8d0be283c4e228cf14fa4dfbd62eaa4
- update route names for pre-registration by @OoBook in https://github.com/unusualify/modularity/commit/fb33acbbae6ef0bc5091097e271ff2bcd29b4f5f
- update form attributes and response formatting by @OoBook in https://github.com/unusualify/modularity/commit/19e5b4f51c6a4dcc8e3eec55b9254280497205c1
- adjust padding and layout for improved UI consistency by @OoBook in https://github.com/unusualify/modularity/commit/0a66ffdac2ba009787b5438cacbd5063a834ba49
- enhance form layout and styling for improved user experience by @OoBook in https://github.com/unusualify/modularity/commit/cceeb9cc3a07f108aaefcfd1042614e95bcc932e
- remove unnecessary class attributes for cleaner layout by @OoBook in https://github.com/unusualify/modularity/commit/fa9363da705710fd067fee9d69bf70d605abc930
- update form toggle attributes for improved styling by @OoBook in https://github.com/unusualify/modularity/commit/ace3fa8ae866accacdc969c79274e0b3d3f8fd94
- enhance Published toggle attributes for improved styling by @OoBook in https://github.com/unusualify/modularity/commit/9c04b1961d5879409b8bc7cbe19ec55cf3dd3471
- improve company attribute handling in email registration by @OoBook in https://github.com/unusualify/modularity/commit/6a5192996f19ebb80d3b1174a64fa43f68b9106f
- separate spread_payload assignment for clarity by @OoBook in https://github.com/unusualify/modularity/commit/b656b9295773b877d8945f3ce7cf9cb6dc257d1b
- transition to script setup and enhance layout by @OoBook in https://github.com/unusualify/modularity/commit/2557b057ba7db436c1dd1be68d2d902c89a490e4
- enhance table properties and styling by @OoBook in https://github.com/unusualify/modularity/commit/0a71ac8cdf7bbd648956eebef1cf336c50c66953
- enhance step icon rendering and interactivity by @OoBook in https://github.com/unusualify/modularity/commit/8009994262f8ad1461f6a36bd908d8b472c2e812
- improve stepper window styling and dynamic height handling by @OoBook in https://github.com/unusualify/modularity/commit/908f51d6d0391aae21c7394505b5308aa4f4b30d
- enhance stepper layout and dynamic behavior by @OoBook in https://github.com/unusualify/modularity/commit/0e8879a81e273408d1b4fd38b7068e107e2005a1
- enhance regex pattern for value validation by @OoBook in https://github.com/unusualify/modularity/commit/3ba76088f89e96381ba3d32716eda0e752dd3738
- update label and subtitle rendering for improved HTML support by @OoBook in https://github.com/unusualify/modularity/commit/0ccd6be1cb7c0654980e48e8a11a09e76f1b9a0d
- update maxHeight default value for improved layout consistency by @OoBook in https://github.com/unusualify/modularity/commit/dc60dc3d222bfd07a672955ff1030097cdc751a3
- enhance mobile responsiveness and header properties by @OoBook in https://github.com/unusualify/modularity/commit/db2469b86a22dba601b5a4d4317cb66bab62cbc1
- update button click handler for step navigation by @OoBook in https://github.com/unusualify/modularity/commit/d2408024f950a5aebddcacac1a256c43b61a84f1
- improve layout and styling for label and subtitle by @OoBook in https://github.com/unusualify/modularity/commit/a67bbf39cd12a4ce621e913293e34aba83fc67b7
- simplify message content handling and clean up styles by @OoBook in https://github.com/unusualify/modularity/commit/8f49a20e02405755ccbf45a046f36e1bbfad1125
- update background and text color styles for improved consistency by @OoBook in https://github.com/unusualify/modularity/commit/1d8bef9785f20c6f4ed40c1ad45ae40d117a2960
- streamline method handling and remove unused code by @OoBook in https://github.com/unusualify/modularity/commit/2262e234d76c7fef1ef68e9c39b857bd6076d6d5
- streamline dialog and additional section layout by @OoBook in https://github.com/unusualify/modularity/commit/b88231a7596b219b1acf429fad282fb97520c1dc
- enhance variable pattern handling and replace logic by @OoBook in https://github.com/unusualify/modularity/commit/638087dca97780f9f8a5cbdda9694c4c71cebef4
- enhance attribute pattern matching and replacement logic by @OoBook in https://github.com/unusualify/modularity/commit/5815d678e892507c9dc195eb4e61082dce87dc2c
- enhance payment update logic and modularity integration by @OoBook in https://github.com/unusualify/modularity/commit/b7d57fdbc3081396bf99f145749185c2a808318d
- integrate HasSpreadable trait for enhanced functionality by @OoBook in https://github.com/unusualify/modularity/commit/711195667d4ea913d0ca355eaa413be2744ed10c
- integrate SpreadableTrait for enhanced payment handling by @OoBook in https://github.com/unusualify/modularity/commit/a8e6253f9afb7c50346974e43145afa0276c0d4a
- add spread payload structure for payment services by @OoBook in https://github.com/unusualify/modularity/commit/9040c06904508a2ec2a2c027aae6f8ad41d36aff
- update payment configuration structure for improved clarity by @OoBook in https://github.com/unusualify/modularity/commit/f917f09ed9a27291c6158261121c3ad8fe5a6d65
- enhance payment record creation logic by @OoBook in https://github.com/unusualify/modularity/commit/acaefad3c961714dac0da3ec25a0e2c7d5277031
- enhance model creation logic for form actions by @OoBook in https://github.com/unusualify/modularity/commit/521623d4ee840d7f9dd33a13f9b272c1ef40a572
- update class structure for form actions layout by @OoBook in https://github.com/unusualify/modularity/commit/de62ae5a394ccaee065eacdde6e0182f3fb359d6
- update evaluation pattern and enhance attribute casting logic by @OoBook in https://github.com/unusualify/modularity/commit/acef2ad745ac797e3deb0d0f65ee88fbb13cd2b4
- enhance action handling and filtering logic by @OoBook in https://github.com/unusualify/modularity/commit/716346797680f4884bc8e58e0f37cdf3250f3681
- simplify class attributes for layout consistency by @OoBook in https://github.com/unusualify/modularity/commit/25a9997a6eb2feaa02de80dcd96ba573a9e16f9c
- introduce closure transformation functions by @OoBook in https://github.com/unusualify/modularity/commit/bf65b9c9c70d36e5186143b76dd12983e615e5e0
- replace createModalService with modularity_modal_service by @OoBook in https://github.com/unusualify/modularity/commit/fbc086a432dac89c82d9d8962248cb34e9634fbb
- adjust padding for payment form layout by @OoBook in https://github.com/unusualify/modularity/commit/562c198a0c5e2420b56e3ddd8a2a13d6938e03e5

### :lipstick: Styling

- improve computed property structure for clarity by @OoBook in https://github.com/unusualify/modularity/commit/d49897aad7ae1ea16bc9cf0d022621affeac53ba

### :white_check_mark: Testing

- enhance role-based authorization tests and mock implementations by @OoBook in https://github.com/unusualify/modularity/commit/97955bfe790cd9da5c0e089de19b1541905bb1e1
- enhance email registration tests with event assertions and company creation validation by @OoBook in https://github.com/unusualify/modularity/commit/4626a76aaae2376eb9dd669b28c98cb847028dad

### :package: Build

- update build artifacts for v0.42.0 by @OoBook in https://github.com/unusualify/modularity/commit/8f4ff86d177d7ec7be4499ed80f8d133f889d870

## v0.41.0 - 2025-07-31

### :rocket: Features

- integrate profile menu into layout and sidebar components by @OoBook in https://github.com/unusualify/modularity/commit/2b3e35fb35dde1df0125e2b7d974ed87272e336d
- enhance payment module with new features and attributes by @OoBook in https://github.com/unusualify/modularity/commit/05d72abec59d899e3e1b91a960ccad8db6a2baa2
- introduce MyPayment module with CRUD functionality by @OoBook in https://github.com/unusualify/modularity/commit/6c20d78d7299554c37825168bd2db88b5c10efae
- add support for searching in relationship fields by @OoBook in https://github.com/unusualify/modularity/commit/baf0d1bec62b4c3fda53cfa9519fde3a607e3fa4
- add loading spinner for improved user experience by @OoBook in https://github.com/unusualify/modularity/commit/9127e46372ad721137d619d11f5353797279ef1f
- prevent profile dialog opening for guest users by @OoBook in https://github.com/unusualify/modularity/commit/c171d2618eded23fd06b6f84895901afc54f130b
- add events for Filepond lifecycle management by @OoBook in https://github.com/unusualify/modularity/commit/96e581a069c103f6695b8c3b85a1597aaf2895b9
- enhance notification redirection logic by @OoBook in https://github.com/unusualify/modularity/commit/a2c54b2f9fec5a19a72af6298e3cf5ea28ceb14c
- add filepondable method for polymorphic relationships by @OoBook in https://github.com/unusualify/modularity/commit/2876c356fe6f828ecd5fa981c10c8cdaaf7adfb3

### :wrench: Bug Fixes

- improve creator relationship logic and clean up unreachable code by @OoBook in https://github.com/unusualify/modularity/commit/65735e15dc10f2bdc0a66843a2db995372b1bad5
- reorder HTML elements by length for improved matching by @OoBook in https://github.com/unusualify/modularity/commit/843ddc04d58fe194a4c0f7f2b94f95de06309168
- extend loading spinner duration for better user experience by @OoBook in https://github.com/unusualify/modularity/commit/2483c234cc7a35dbbaba1a5bdaf5427f8fbc34e7

### :recycle: Refactors

- add getFilepondableClass method for improved filepond handling by @OoBook in https://github.com/unusualify/modularity/commit/cdbaf86470aa754d0c1eadf2650f7f10dffa29bb
- enhance creator relationship handling and add user-specific creation scope by @OoBook in https://github.com/unusualify/modularity/commit/8c5037d6d1ece6b5bf5312e0055d04411a0ec62a
- update controller namespace for improved compatibility by @OoBook in https://github.com/unusualify/modularity/commit/b95cbcc79267bf0e674c8def9f0f2809634f78b3
- update slot structure for improved layout flexibility by @OoBook in https://github.com/unusualify/modularity/commit/46af081423788d070a65a35c429b840ec3d9ba09
- streamline layout and include modular slots by @OoBook in https://github.com/unusualify/modularity/commit/458abda47f15c9157742b01f020a49f4ef024600

### :lipstick: Styling

- lint coding styles for v0.41.0 by @OoBook in https://github.com/unusualify/modularity/commit/098a7a3d28775567c80b8f8f87514cb1c16e808f

### :package: Build

- update build artifacts for v0.41.0 by @OoBook in https://github.com/unusualify/modularity/commit/99910da8a627574288e705d5af8edd9e65c6cedd

## v0.40.0 - 2025-07-25

### :rocket: Features

- enhance notification configuration and extend FeatureNotification capabilities by @OoBook in https://github.com/unusualify/modularity/commit/e50c2ece25f0e7acc78051bd638a681480705c9f
- add customizable salutation to email template by @OoBook in https://github.com/unusualify/modularity/commit/c6ede94f889643acda9802e52559f79cd7c77a9c
- enhance notification handling with customizable callbacks by @OoBook in https://github.com/unusualify/modularity/commit/c1b813b976e2c10ec9d1a77fa0d174edc88b734a
- implement dynamic success and error messages for CRUD operations by @OoBook in https://github.com/unusualify/modularity/commit/e0385081bff7bc2d5e3633dee92e8adf146628f6
- introduce HeaderHydrator for dynamic header management by @OoBook in https://github.com/unusualify/modularity/commit/fce4314bb6ffa2fa1b0d90bbd2b2b84bf33cff67
- add 'table-cell' to valid display values by @OoBook in https://github.com/unusualify/modularity/commit/07b5900488829f3a9c6e32a2cfe8b2ad51ab2433
- enhance mobile action visibility and responsiveness by @OoBook in https://github.com/unusualify/modularity/commit/9a9297b361d5a9b76f2520bbadb1f1349071eece
- add support for HTML elements and enhance method handling by @OoBook in https://github.com/unusualify/modularity/commit/c706f5968436b32fba52b6e8c5107b98d37f5e56
- add closure value transformation for dynamic input handling by @OoBook in https://github.com/unusualify/modularity/commit/615f50c6be775c3ab98ef23397260742e686ff0b
- add marked library for markdown parsing by @OoBook in https://github.com/unusualify/modularity/commit/54e20055f7b521803e49764e7e5748279a686675
- enhance table interactivity and responsiveness by @OoBook in https://github.com/unusualify/modularity/commit/4b0e1677dc01979b7e37d75ac454e31e22b65e2f
- enhance checkbox functionality and styling by @OoBook in https://github.com/unusualify/modularity/commit/3b0c3683d5f91cf38d47fed731158a3da5b36572
- implement markdown rendering component with table of contents by @OoBook in https://github.com/unusualify/modularity/commit/6378dad76cd1d34c265b879fb64b4982f000046c

### :wrench: Bug Fixes

- update notification handling to use chat model by @OoBook in https://github.com/unusualify/modularity/commit/3468c7873410834a82264dbb487658f6da31cfb4
- update password rules for enhanced security by @OoBook in https://github.com/unusualify/modularity/commit/1c963ab7ae5d754b6ddb53b69d1b5304b7f2d300
- integrate OauthTrait and enhance user creation logic by @OoBook in https://github.com/unusualify/modularity/commit/6ab4da808499df9c385410a5b8789b18370e6f1c
- add password validation rules for user updates by @OoBook in https://github.com/unusualify/modularity/commit/69c28e9d9660555848b6361c3bf74238004e306d

### :recycle: Refactors

- enhance user fetching logic and toolbar display by @OoBook in https://github.com/unusualify/modularity/commit/5f0a48ad82667c2cff94bf9de458d297778b5a9c
- specify Assignment model type in constructors and enhance event handling by @OoBook in https://github.com/unusualify/modularity/commit/f6de7426c698ae018acb40b3b29efad6be350490
- enhance type safety and improve notification methods by @OoBook in https://github.com/unusualify/modularity/commit/c2720b2f5946f9cc397fe8230de9cf2991f49edb
- clean up unused notification code in StateableListener by @OoBook in https://github.com/unusualify/modularity/commit/fc3f77109d786ee4893710b01f6bde55f1b54a96
- streamline column configuration with HeaderHydrator by @OoBook in https://github.com/unusualify/modularity/commit/4bb8a42b7a03f5ad7493d1a7f69f5341a7cf914a
- enhance route resolution logic for admin and general routes by @OoBook in https://github.com/unusualify/modularity/commit/687e862a782c74a09297dfa9c146886571ef0316
- improve element handling and attribute hydration by @OoBook in https://github.com/unusualify/modularity/commit/38f8eae40e5fcf6df861d3ad97e0d784115eb279
- enhance navigation and configuration handling by @OoBook in https://github.com/unusualify/modularity/commit/41ee2f89a0aa95d01f3d3587d3ec1db808abf4ab
- streamline layout structure and enhance mobile responsiveness by @OoBook in https://github.com/unusualify/modularity/commit/6e28a7ac287ed0a7ebec08fc8e350e1584ffc09b
- update column layout for form fields to improve responsiveness by @OoBook in https://github.com/unusualify/modularity/commit/d28056f5ad709058bd0413e710e83f25d5b89deb
- improve route resolution logic for better error handling by @OoBook in https://github.com/unusualify/modularity/commit/d0b5877f1bd7c1ece4a2c3eb56dbb8c66e8fcc41
- update styling and layout for improved responsiveness by @OoBook in https://github.com/unusualify/modularity/commit/2dc3c9179599fa7dd35a5bb142a9b5f250ed8417
- enhance layout and responsiveness of submit section by @OoBook in https://github.com/unusualify/modularity/commit/f0aa3b3e151b35c1e96f25d8c1c81df533fdbe67
- comment out terms of service validation rule by @OoBook in https://github.com/unusualify/modularity/commit/8a818316f259a4671aa063e004068a0c020f04c2

### :lipstick: Styling

- lint coding styles for v0.40.0 by @OoBook in https://github.com/unusualify/modularity/commit/e7c1a19d76da3bbc448f866e1497c31c04215097

### :package: Build

- update build artifacts for v0.40.0 by @OoBook in https://github.com/unusualify/modularity/commit/868192c6215861b344ace4e4559a9814dc093872

## v0.39.0 - 2025-07-19

### :rocket: Features

- enhance message display with truncation and responsive design by @OoBook in https://github.com/unusualify/modularity/commit/b8d244c698dc50ead53358af6c6926b4659a453d
- enhance chat component with subtitle and file upload support by @OoBook in https://github.com/unusualify/modularity/commit/2a8092b4f0b61ec825d277fe590bfedd0e071ad3
- add methods for app and admin URL handling by @OoBook in https://github.com/unusualify/modularity/commit/740b7424925651e5ede67a21ec9dbc678bf8270c
- enhance error pages with modularity design and functionality by @OoBook in https://github.com/unusualify/modularity/commit/c8f03f854e2386ab93f6ff312d64a739d4e01e65
- add getByIdWithScopes method and enhance getById with scopes support by @OoBook in https://github.com/unusualify/modularity/commit/b67af011222ea075a8919c7977903c797595dea7
- enhance getFormItem method to support authorization scopes by @OoBook in https://github.com/unusualify/modularity/commit/1ddb179f24c9f0c8a5895c7a4f9125f930f4f820
- enhance index method to support eager loading by @OoBook in https://github.com/unusualify/modularity/commit/f0677690aba7e9fe26d563a0fbffcabe89201453
- implement reusable error card component for 403, 404, and 500 pages by @OoBook in https://github.com/unusualify/modularity/commit/141f0dd0440b34f5d7a6256ee60d5dd36a2f148d
- enhance layout and styling options by @OoBook in https://github.com/unusualify/modularity/commit/7e3d0bd0c49b8f60f9513e7beaaf5f3b96a64e1d
- add responsive visibility trait for dynamic class management by @OoBook in https://github.com/unusualify/modularity/commit/8d1b9cf4f1bd7e26e47d34c07a30c0d3513d5e38
- integrate responsive visibility into form and table actions by @OoBook in https://github.com/unusualify/modularity/commit/c61bf2c606e17b873fb2f138b3c8dba6448ee7f9
- enhance filter and action item classes for improved styling by @OoBook in https://github.com/unusualify/modularity/commit/1866a68736d6e69e481bf3e4f89c05ed60d5f949
- add flexBreakpoint prop for responsive layout control by @OoBook in https://github.com/unusualify/modularity/commit/c5822190caa628194042f90329252994101eddb4
- add TranslatableServiceProvider to package providers by @OoBook in https://github.com/unusualify/modularity/commit/fd18886aa331e7af37c940264bf5a5d064d60ea0
- add language parameter handling for request localization by @OoBook in https://github.com/unusualify/modularity/commit/550bca22d6ecc87c353d88a0c0ae1e02ed4d91d9
- add hasScope method for dynamic scope checking by @OoBook in https://github.com/unusualify/modularity/commit/bb70b721b2bfc5cac40b4c1313686c5b9e1bc779
- :sparkles: enhance API route registration and modularity support by @OoBook in https://github.com/unusualify/modularity/commit/f086bc2049c3733cfcd3791130f966d676f91934
- :sparkles: implement new base API controller with modular traits by @OoBook in https://github.com/unusualify/modularity/commit/1f0d3559bbc98d8f18d1e2dbc855e888dfc57004
- add API configuration file for modularity support by @OoBook in https://github.com/unusualify/modularity/commit/ac42ac05fbe3ab8aae006d0b15ad0a7090cafb8e
- refactor UserController to extend ApiController and enhance API capabilities by @OoBook in https://github.com/unusualify/modularity/commit/b757c5920a191b59aa282c28e2c3ef5b84152da4
- add header title functionality to layout by @celikerde in https://github.com/unusualify/modularity/commit/e4deb2210c8ae6a0ca5f80b58d8a5e5ea41effc7
- enhance dashboard view with dynamic page and header titles by @celikerde in https://github.com/unusualify/modularity/commit/a095631a21883cef706e2f945ac8ebf1ecff39c5
- add dynamic page and header titles to profile settings view by @celikerde in https://github.com/unusualify/modularity/commit/d7f520c56146fa2a5fc15aae51fc60b9aed9826a
- add non-run events on create to prevent execution during initial setup by @OoBook in https://github.com/unusualify/modularity/commit/b36bc3aef114577c0b9f81fa65046ac7a9433262
- add support for 'xxl' breakpoint in responsive design by @OoBook in https://github.com/unusualify/modularity/commit/63f5a690abe1d256d665326ee8e34c7dd477f3c5
- include request in user registration event by @OoBook in https://github.com/unusualify/modularity/commit/013fa43c719a53a981d446b5d54d33be388912dc
- add front and API controller paths for module generation by @OoBook in https://github.com/unusualify/modularity/commit/c1e011a4e9825cb7912e001dec928b1411f5a6c4
- add slot for appending custom actions to the table actions component by @OoBook in https://github.com/unusualify/modularity/commit/6ed620997ca58c958c9e70638f6d52595a9cb7a9
- add ModelHelpers trait to Role entity by @OoBook in https://github.com/unusualify/modularity/commit/903190234213b914138eabcc8bb16ed8b8c01cdc

### :wrench: Bug Fixes

- improve responsiveness and text overflow handling by @OoBook in https://github.com/unusualify/modularity/commit/aee7fbef9e77b78fd73c2dc84129b5e50e390abc
- enhance rowActions handling by @celikerde in https://github.com/unusualify/modularity/commit/c40931a51cabb2496a75bdb03ca2270b0c81b6e3
- enhance mandatory item handling and input validation by @OoBook in https://github.com/unusualify/modularity/commit/517fbf6e8416da9887662ffba841a78428abe1d6
- enhance header visibility control for mobile displays by @celikerde in https://github.com/unusualify/modularity/commit/9a67e037fd6d557df95a86dba5bbc0f33b80a301
- update page title generation in success view to use the correct namespace by @OoBook in https://github.com/unusualify/modularity/commit/8fe80a34da6e027e0f014a10ee9dcdc05681f79b
- improve parameter parsing in setEvents method by @OoBook in https://github.com/unusualify/modularity/commit/15c97e860bd88aacc4d1a1f10a2fdebd880cd487
- enhance scopeIsStateables method to handle string input for codes by @OoBook in https://github.com/unusualify/modularity/commit/7da9b4efab55a0669af1b378978750303f0fa110
- enhance pagination and items per page handling for responsive design by @celikerde in https://github.com/unusualify/modularity/commit/8b6e66826ca4c8e0a4c2828e187fce70352afab1
- improve authorization check for index options by @OoBook in https://github.com/unusualify/modularity/commit/47604955d60badad1de13b1766321c7b8ef52d89
- update registration response handling by @OoBook in https://github.com/unusualify/modularity/commit/09f1f24c2ee70ffe5f8c436ad6626fc192664714
- update button class and layout adjustments for improved responsiveness by @OoBook in https://github.com/unusualify/modularity/commit/5515b9a46f3d4077fb8b7f570e26e0ce9ca30e91
- adjust layout classes for improved responsiveness by @OoBook in https://github.com/unusualify/modularity/commit/fc5df0551fa636245d4729a196d073c1045f49f0
- update toolbar title alignment for improved layout by @OoBook in https://github.com/unusualify/modularity/commit/fe4bc2ee6ca57b257950b7af9aba81d489b1b631
- enhance layout and padding options for improved responsiveness by @OoBook in https://github.com/unusualify/modularity/commit/c42c9fc93cd792dce84cb652f54025b8695a4e69

### :recycle: Refactors

- update app and admin URL handling by @OoBook in https://github.com/unusualify/modularity/commit/19d1cab0b05b17c58c121fc36f469256267b7fbc
- add elements property to component state by @OoBook in https://github.com/unusualify/modularity/commit/87bca1da9bab28146c94b364ab606995c47e153c
- update admin app URL handling and improve URL retrieval logic by @OoBook in https://github.com/unusualify/modularity/commit/d5349f819c3f232594cec509b8d7108e69ca8a37
- change Model class to abstract by @OoBook in https://github.com/unusualify/modularity/commit/517780ed5b72054e344d3f8db2807dd2b85a618f
- enhance styling for selected and disabled states by @OoBook in https://github.com/unusualify/modularity/commit/0622a93ba7070610aca34fd971e637afd02c9617
- improve admin app URL handling and logic by @OoBook in https://github.com/unusualify/modularity/commit/6ac635a3f3014d221aa91391374711fae9d401d7
- streamline module route registration and enhance logic by @OoBook in https://github.com/unusualify/modularity/commit/c7f30637e02709864340a9ab591a54ca7135a752
- enhance configuration handling and constructor logic by @OoBook in https://github.com/unusualify/modularity/commit/e2552305c111182791820512571914387b97456e
- enhance language handling and fallback logic by @OoBook in https://github.com/unusualify/modularity/commit/0dca6f4892d5a98de8c887c0e2a9bff6ca8253ea
- simplify schema generation by extracting logic into a separate function by @OoBook in https://github.com/unusualify/modularity/commit/eb83fdaa9bbcd5fd8ec0874a29c830d5a5750d8c
- enhance modularity_format_input and modularity_format_inputs functions by @OoBook in https://github.com/unusualify/modularity/commit/9f378611ce8e3800359d97dc13f3db4262ff522f
- change createFormSchema method visibility from protected to public by @OoBook in https://github.com/unusualify/modularity/commit/f578dad8525ec931310435cafdd69aae5a4ff37a

### :memo: Documentation

- add Allowable trait for role-based access control in arrays and collections by @OoBook in https://github.com/unusualify/modularity/commit/b02428967ed37e3b6ba5fdcc020c2c90e519cc9d
- add responsive visibility guide for modularity trait by @OoBook in https://github.com/unusualify/modularity/commit/9b0c7aa2f17a5ce0c75633c89074676255ca56a5

### :lipstick: Styling

- improve button binding syntax for clarity by @OoBook in https://github.com/unusualify/modularity/commit/7e786e3c81acff7e27f1e7850555a47b1567f707
- lint coding styles for v0.39.0 by @OoBook in https://github.com/unusualify/modularity/commit/b87a2b933d440063e282c84fd144bd9bde248c1b

### :white_check_mark: Testing

- add comprehensive tests for model functionality by @OoBook in https://github.com/unusualify/modularity/commit/46121cfbaa4f3601bca178cd06357cd1b2489b1e
- update admin_app_url configuration to use an empty string instead of null by @OoBook in https://github.com/unusualify/modularity/commit/de512046c9ef336797391786dd4e39cf129186a8

### :package: Build

- update build artifacts for v0.39.0 by @OoBook in https://github.com/unusualify/modularity/commit/b59f859175578fe3f22e821a547f460e2f1d6110

### :green_heart: Workflow

- change version of automated-issue-carrier to v1.0.2 by @web-flow in https://github.com/unusualify/modularity/commit/636f044911e2da7c3bed6ba2c3d0d7cfd43cfcae
- change projects input as wildcard by @web-flow in https://github.com/unusualify/modularity/commit/7757858975116cf1ee670704cd8962ff0c4f3573
- test context print by @web-flow in https://github.com/unusualify/modularity/commit/9bc31da33df1a5d91a07e60f896e7fa876a59c9b

### :beers: Other Stuff

- improve error logging and clean up code by @celikerde in https://github.com/unusualify/modularity/commit/6719f101a6cfcb48132e9f92487f467342743adb

## v0.38.0 - 2025-07-08

### :rocket: Features

- enhance login modal with session expiration message and reload functionality by @OoBook in https://github.com/unusualify/modularity/commit/7e78d21517134913e3689a8f9ef465b39222a115
- enhance formatPrependSchema to support ordering of prepended keys by @OoBook in https://github.com/unusualify/modularity/commit/ce6c9685c3985497d408972b9ec94eb9cc7a3686
- update button actions for notifications by @OoBook in https://github.com/unusualify/modularity/commit/0921f1637f56489b084324a89ca6bac791b31fa3
- implement sourceLoading state management in useInputFetch by @OoBook in https://github.com/unusualify/modularity/commit/e2096eec2532f9fa2c80348a767565f071f3fece
- add loading indicator for schema input source by @OoBook in https://github.com/unusualify/modularity/commit/cd79e7890e839bbd3c1342111d45748d0d0ff87a
- enhance loading state management and update event handling by @OoBook in https://github.com/unusualify/modularity/commit/1f7dadfa3c2c23cd9dd5f8be2852ab5d3c4a9302
- enhance processable details display and validation logic by @OoBook in https://github.com/unusualify/modularity/commit/8a108b0950a58d17c02cc2036cdc3b2f5e87d55f
- add status informational message to process entity by @OoBook in https://github.com/unusualify/modularity/commit/1869cf4a4701ca1707cd851e70c28d3cab162395

### :wrench: Bug Fixes

- correct project name in issue automation by @OoBook in https://github.com/unusualify/modularity/commit/30d7c2bb83bcfe2180f7ef6610060715eee8bf9f
- improve key ordering logic for prepended keys by @OoBook in https://github.com/unusualify/modularity/commit/3c64bad5ceb6262a839801071b76c126a4b6850c
- update route reference to use Module.transNameSingular by @OoBook in https://github.com/unusualify/modularity/commit/a0b20c8c2d1c90cde300aff32d9e3c48f5c90649
- handle undefined item properties in action rendering by @OoBook in https://github.com/unusualify/modularity/commit/7b70a220c7695b93921c7d7bf7e64ca87b4ed557
- update formatter structure for pricing configuration by @OoBook in https://github.com/unusualify/modularity/commit/6c7506f67528f5af630dfd7661b5090ae3cce267
- update condition for form schema value assignment by @OoBook in https://github.com/unusualify/modularity/commit/f2221181cc528f5370711c4f1d01a1045ae93e4e

### :recycle: Refactors

- update action handling to use visibleRowActions by @OoBook in https://github.com/unusualify/modularity/commit/515b278843fbc192fc6b91f6a0b10f4eebfa0fe5
- enhance action rendering and component handling by @OoBook in https://github.com/unusualify/modularity/commit/713220af2995c8e1823b119559f95cd094303ae6
- refactor process model and validation logic by @OoBook in https://github.com/unusualify/modularity/commit/69378864a7ed6fbb57dcdeb45e89f2e14c5c0748
- enhance file validation and rules management by @OoBook in https://github.com/unusualify/modularity/commit/f26013a4637b0b0a2b3daba18a33a845ca4d68c2
- simplify status labels for clarity by @OoBook in https://github.com/unusualify/modularity/commit/aa5b06bae45e0d61c4bdc8e630e062bd189aa7ee
- improve layout and structure of process details display by @OoBook in https://github.com/unusualify/modularity/commit/86da6f30844f39755d3707299c9ff5e22225b21d
- update layout and improve informational message display by @OoBook in https://github.com/unusualify/modularity/commit/8c97f40c8c38e3460eebe14ea044d69230f2a406
- adjust layout for process title and status chip by @OoBook in https://github.com/unusualify/modularity/commit/bd05d70846bf1b24110205fdab60304acbdd16f6

### :lipstick: Styling

- lint coding styles for v0.38.0 by @OoBook in https://github.com/unusualify/modularity/commit/88af8989771ae2e042f6e28f12e9c0099b9cd206

### :white_check_mark: Testing

- mock vue-i18n and useAuthorization for improved test isolation by @OoBook in https://github.com/unusualify/modularity/commit/9bb7507e95efe1d98fdc41f5c45232225f528001

### :package: Build

- update build artifacts for v0.38.0 by @OoBook in https://github.com/unusualify/modularity/commit/4f73919ce26c5785098adee52eb50240b130a3ee

### :green_heart: Workflow

- add workflow to automate issue labeling and project management by @OoBook in https://github.com/unusualify/modularity/commit/f07e4aca774d70ec22e059fcb57a41c7166b1925
- add test flag to project item workflow by @OoBook in https://github.com/unusualify/modularity/commit/6b423a8b1b54de7372cf83bb2372ba7d0c558f77
- disable test flag in project item workflow by @OoBook in https://github.com/unusualify/modularity/commit/6d8bfd0d53185bc405ea7b1f47764c0aa60d346e
- enhance issue closing workflow with repository input and token handling by @OoBook in https://github.com/unusualify/modularity/commit/8158cb19db0930030d74ec7b7feb7a11ce02b3ff

### :beers: Other Stuff

- add .secrets to .gitignore by @OoBook in https://github.com/unusualify/modularity/commit/91898f903954d4b42a10e4a596840f40644a4b8a
- update automated issue carrier version and add step ID by @OoBook in https://github.com/unusualify/modularity/commit/4d3f4e1a29b0cf0a859bea073f3e3d41b817cb59
- update automated issue carrier version to v1 by @OoBook in https://github.com/unusualify/modularity/commit/93d2d2bef7b625fa575ff99429c5ce9289c700c4
- add sourceLoading option to default input configuration by @OoBook in https://github.com/unusualify/modularity/commit/ada91ecdd5728d4a12cc29a672ead35abc01c284
- add 'loadedFile' prop to input emits for enhanced file handling by @OoBook in https://github.com/unusualify/modularity/commit/ae253b38dcc9603bace93d0ab64ca206ee2e4121
- allow rightSlotMinWidth and rightSlotMaxWidth to accept string values by @OoBook in https://github.com/unusualify/modularity/commit/41d1fa8d65310b2b806a86fe2476b5b8ea3a8ef0
- add test:stop-on-error script for improved testing control by @OoBook in https://github.com/unusualify/modularity/commit/06ca63d1e8a8265af673604096a706e8c9543a76

## v0.37.0 - 2025-06-30

### :rocket: Features

- update sidebarMenuItem class assignment for improved flexibility by @OoBook in https://github.com/unusualify/modularity/commit/a733477b65394cb659c3eab7f92c142014bf5b46
- add ModularityNotificationSentListener to handle notification events by @OoBook in https://github.com/unusualify/modularity/commit/5acc360f4e148abc6865e84d9499884b56f75d10
- introduce ModularityFinder facade for enhanced modularity support by @OoBook in https://github.com/unusualify/modularity/commit/fbb9976bcf3a2088c54266966273116bf7edf9de
- add method to retrieve models using a specific trait by @OoBook in https://github.com/unusualify/modularity/commit/b2421bc4728936ae0dc7b3063161a99d9a0adb7f
- implement chat notification system with notified_at field by @OoBook in https://github.com/unusualify/modularity/commit/0ed637c0ff3ab6823afec1f4f22d8b20bf5478c2
- enhance data binding with formItem integration by @OoBook in https://github.com/unusualify/modularity/commit/c5737258dd1c586a6b1cf729fbbcdfd872779f67
- integrate formItem for enhanced data binding by @OoBook in https://github.com/unusualify/modularity/commit/4de49f0e759f209e64f94cceeea4fe68ac7e81b9
- enhance form component with subtitle support and improved data binding by @OoBook in https://github.com/unusualify/modularity/commit/d5538049f1b8b86c51e6261d092ee773a0360dd5
- add page title callback functionality by @OoBook in https://github.com/unusualify/modularity/commit/2e19e3d857685d588b7542b3aca0938cf4a91172

### :wrench: Bug Fixes

- add 'sometimes' rule to roles validation for improved flexibility by @OoBook in https://github.com/unusualify/modularity/commit/3a20d1e47833b97c0803489dea3c62218f20096b
- ensure getModelTitleField returns a default empty string for missing title values by @OoBook in https://github.com/unusualify/modularity/commit/8742f03c866bcb2ad3d69e3d9f57b8200d2c71a9
- update command signature to use option for days parameter by @OoBook in https://github.com/unusualify/modularity/commit/e903535f1125d64b5c7f920de1a1d783aedeeaf6
- adjust header padding for improved layout consistency by @OoBook in https://github.com/unusualify/modularity/commit/278d51cd99c8c6923b3883a1c47d528cc13dafbf
- add color prop and improve button styling by @OoBook in https://github.com/unusualify/modularity/commit/c350372ea8bcdc5d8d53f752764fc037ce9bf719
- move class definition to computed property by @OoBook in https://github.com/unusualify/modularity/commit/3a63c31c79f0e8af668b2c66271fa49c87966975
- update notification color for unread messages by @OoBook in https://github.com/unusualify/modularity/commit/1a6760f36a07bb28addcab72ee17a9f90cdde603

### :recycle: Refactors

- comment out unused orange color definitions for clarity by @OoBook in https://github.com/unusualify/modularity/commit/02a2344b40ad26a356c55ff044a343d68996127d
- comment out rules for logo field to enhance clarity by @OoBook in https://github.com/unusualify/modularity/commit/a2ad6c337ee27176866be83fdfb8cd04649b56df
- enhance accessibility and modal management by @OoBook in https://github.com/unusualify/modularity/commit/586d2c922c0af730b3e44951b17dc90ff3a99f65
- remove unused media library model binding by @OoBook in https://github.com/unusualify/modularity/commit/1d566337529005978c891888d9ede6cbdfd4b781
- adjust layout for improved responsiveness by @OoBook in https://github.com/unusualify/modularity/commit/019add47ba423e2413471e30ddfcb854a54e12c3
- update layout and improve data presentation by @OoBook in https://github.com/unusualify/modularity/commit/6ca469c998529ebe79ec41a8364951ef5b2fa5e7
- update button to span for improved styling and functionality by @OoBook in https://github.com/unusualify/modularity/commit/6e8878ec32dd0cd3526a52c33be3d2762a8860ad
- enhance profile menu configuration for user roles by @OoBook in https://github.com/unusualify/modularity/commit/63b3210ee9ec724a7cf3d040fc335a15006acf0e
- improve form handling with enhanced title and submission logic by @OoBook in https://github.com/unusualify/modularity/commit/a990507b7f68638db50c51fb34e46cfd1103a785
- add title configuration for improved table presentation by @OoBook in https://github.com/unusualify/modularity/commit/640e4a36c5c3a420a132073e4034af369399c039
- enhance relationship data handling in getItemColumnData method by @OoBook in https://github.com/unusualify/modularity/commit/1506e3ca87e45ea41d74714c37f641e4b03371b6
- improve checkbox rendering and add active color props by @OoBook in https://github.com/unusualify/modularity/commit/dd4beb98f215342fee453742f613fe5de088246a
- enhance checkbox rendering and add new props for customization by @OoBook in https://github.com/unusualify/modularity/commit/04842c301267deffbb7de5c58383ce464dbcb2f2
- simplify template structure and adjust title padding by @OoBook in https://github.com/unusualify/modularity/commit/dde856b5e1fe10a76787432b7314266e4ce3bf75
- enhance page title handling across authentication views by @OoBook in https://github.com/unusualify/modularity/commit/362e0b59138dc4d857e82aaf9696201d3b2431cd
- streamline user registration process by @OoBook in https://github.com/unusualify/modularity/commit/ede0df9aca0dcfbfaa2bb1a19a5065fc092f62bc

### :lipstick: Styling

- lint coding styles for v0.37.0 by @OoBook in https://github.com/unusualify/modularity/commit/5499bd8789cb1ac473d1ba9f725184a45e4328ae

### :white_check_mark: Testing

- update role creation and registration success message by @OoBook in https://github.com/unusualify/modularity/commit/bacde4a6e99301ba27b7a146f4d43febe3986de5

### :package: Build

- update build artifacts for v0.37.0 by @OoBook in https://github.com/unusualify/modularity/commit/759a08b2493fc623211975dfeb18c225e8ea9ab5

### :green_heart: Workflow

- change accepted label as planned to create a branch by @web-flow in https://github.com/unusualify/modularity/commit/1e9c3dbf23a7b003e15cde8b9f6f0677d65bbddd

### :beers: Other Stuff

- add new language entries for create and validate actions by @OoBook in https://github.com/unusualify/modularity/commit/a510db8d81c9f6e291d10b6213044816168ad72f

## v0.36.0 - 2025-06-22

### :rocket: Features

- add file size validation options to Filepond component by @OoBook in https://github.com/unusualify/modularity/commit/ace4d9111250a39f07b0ff901096b0f75e1016aa
- add protectDefiner and protectedInputs props for enhanced input protection by @OoBook in https://github.com/unusualify/modularity/commit/10601d90e48c761e163c8990a7f3b56172e28a07
- enhance getByIds method with lazy loading support by @OoBook in https://github.com/unusualify/modularity/commit/c048a782fd1621acdf061fd969849b04f324be22
- add scope methods for state filtering by @OoBook in https://github.com/unusualify/modularity/commit/a960c813bb9d34b3cef24fa3dfdcfc1f33abd419
- enhance form handling with improved data structure and response management by @OoBook in https://github.com/unusualify/modularity/commit/b8e251f0d07856b0602b63c1ee690a5710443dc6
- improve field selection logic and UI feedback by @OoBook in https://github.com/unusualify/modularity/commit/3d4e8ef04b679c6d5dc0a25c394dd939dad7a597
- implement modular logging system with email notifications by @OoBook in https://github.com/unusualify/modularity/commit/c54da5ba7a790f2a4ebb110cc96130a63f04c3fe
- add hydrate_input_type function for enhanced input processing by @OoBook in https://github.com/unusualify/modularity/commit/c6852a56abd4815bba355188d7545f004673953b

### :wrench: Bug Fixes

- improve afterSaveRelationships logic and add logging for numeric data by @OoBook in https://github.com/unusualify/modularity/commit/3213c8677fab0b2e5dbb26a953cf7548e5fd55e8
- update getFormattedIndexItems method to use data_get for improved clarity by @OoBook in https://github.com/unusualify/modularity/commit/16693b790151a1c3e1352e6ae88c37e6320f4e74
- improve loading state handling in StepperPreview component by @OoBook in https://github.com/unusualify/modularity/commit/7c2efbddfbdd5a3bc351063dd070fa06744055af

### :zap: Performance

- simplify getIndexData method by removing unused variables by @OoBook in https://github.com/unusualify/modularity/commit/d24fae361fc38e84192b8450b9512a3d82530fe9

### :recycle: Refactors

- update component naming conventions to PascalCase by @OoBook in https://github.com/unusualify/modularity/commit/6a8f3972d506c13ac34f377e9f106608a96f69cb
- update responseModalOptions type and clean up code by @OoBook in https://github.com/unusualify/modularity/commit/cb9be188baab878ac0e5ece83c54dcc1afc5958c
- simplify input hydration logic by utilizing hydrate_input_type function by @OoBook in https://github.com/unusualify/modularity/commit/fccb2d1b48ba504d805f5a926043575045616ee2

### :lipstick: Styling

- lint coding styles for v0.36.0 by @OoBook in https://github.com/unusualify/modularity/commit/860f84daebd603ad9025eb021ad96fcb015780b6

### :package: Build

- update build artifacts for v0.36.0 by @OoBook in https://github.com/unusualify/modularity/commit/dc4740dbadb8572cd3b6df2236a75d000f4c5c02

### :beers: Other Stuff

- remove logging statement for cleaner code by @OoBook in https://github.com/unusualify/modularity/commit/ad2e9e2086f75a73518629d922745d7a91610acf

## v0.35.0 - 2025-06-16

### :rocket: Features

- enhance model retrieval with connectedRelationship support by @OoBook in https://github.com/unusualify/modularity/commit/28facf156ecb04b57aafd07ac395d0bfc1afc0fc
- enhance getById method with lazy loading support by @OoBook in https://github.com/unusualify/modularity/commit/192e68930b162cd8b85b35c41dc3c127929381c1
- enhance hydration logic to support lazy loading by @OoBook in https://github.com/unusualify/modularity/commit/283bcba15d8c1b208db50eed8fa347bfe17b5003
- add support for lazy loading in filter formatting by @OoBook in https://github.com/unusualify/modularity/commit/6a590571b1bb3cb04544695f9c95a0ce366b4b41
- add comparatorValue prop and improve value retrieval by @OoBook in https://github.com/unusualify/modularity/commit/4614a57a67908935a2937a014ec6eaff50fe498f
- enhance afterSaveRelationships method for improved relationship management by @OoBook in https://github.com/unusualify/modularity/commit/b88b99c30b97952c709b457caf1b975f62dd1c42
- add new Expansion component for collapsible content by @OoBook in https://github.com/unusualify/modularity/commit/7dbcb5e47d2067efe0919cd3fd14c9b298640253
- enhance schema binding with additional props by @OoBook in https://github.com/unusualify/modularity/commit/d5a2b921d54d5e6e1c059b0a1647b158a7582b57
- add sorting functionality for checklist items by @OoBook in https://github.com/unusualify/modularity/commit/6be32e1bb7de13b2d23848ffd7e8ef7cc1b83899
- add FileFactory and enhance File entity with size attributes by @OoBook in https://github.com/unusualify/modularity/commit/12489fbe7c8c660369a274036144aa0748aefc07
- introduce MediaFactory and integrate HasFactory trait by @OoBook in https://github.com/unusualify/modularity/commit/580fe5fd49298b71b60ff8983f6e4874c26a0a52
- enhance orderByCurrencyPrice and orderByBasePrice methods by @OoBook in https://github.com/unusualify/modularity/commit/ab6e11a8240269d427d6c6eb8fc7d531a64229b8
- add validation rules to Price input component by @OoBook in https://github.com/unusualify/modularity/commit/abe71305a098bc9eef183a8a6eafbc30ff10a26b
- add pricing saving key to default attributes by @OoBook in https://github.com/unusualify/modularity/commit/a044df7b5e667e97aecf3c4cdd43d030113bb921
- set default price value in hydrate method by @OoBook in https://github.com/unusualify/modularity/commit/e9ebfe1f55f55ac33f687c4a4e075b93671e2974
- enhance v-text-field attributes for improved validation by @OoBook in https://github.com/unusualify/modularity/commit/53d30b0b2efd35b23ce1c25e09db8d87714cab79
- add errorMessages prop for enhanced error handling by @OoBook in https://github.com/unusualify/modularity/commit/1968d8528bae12f68e24c8939526827f65a95df7
- refactor input handling and enhance error management by @OoBook in https://github.com/unusualify/modularity/commit/b90273e3fa3d865ce0ac30acbb9fa16f348cc4de
- enhance respondWithRedirect method to accept additional attributes by @OoBook in https://github.com/unusualify/modularity/commit/331bae87bed548857be1bbf44ed9fbaaffdb819e
- update getTableAttribute method to accept a default value by @OoBook in https://github.com/unusualify/modularity/commit/14e60f367c66c917af449a123a2f4004d73bad0b
- add redirect option after item creation by @OoBook in https://github.com/unusualify/modularity/commit/2989b666c2007b012c50db0aafa08d725155dbc0
- enhance redirect logic based on response data by @OoBook in https://github.com/unusualify/modularity/commit/e16ce750a79706876bb3679d57aa582a9fe66a2f

### :wrench: Bug Fixes

- add null/undefined check for target input by @OoBook in https://github.com/unusualify/modularity/commit/6faa39c45336c03374affed60989fa3bc80641ff
- update minValueRule to handle undefined and null values by @OoBook in https://github.com/unusualify/modularity/commit/cafacc4baf71140ff69273024cfffde32da63b06

### :recycle: Refactors

- update getById method to use named parameters for improved clarity by @OoBook in https://github.com/unusualify/modularity/commit/4d6db1d00da8e702757bfb5c84364d226b7104c0
- remove commented-out debug code in getFormFieldsRelationships method by @OoBook in https://github.com/unusualify/modularity/commit/197d4942a06a9d0ed33ffc291b769be0e1d444e8
- remove commented-out code related to HasCreator trait by @OoBook in https://github.com/unusualify/modularity/commit/632baa12b16b4ca7297733399df4feeec6603f00
- streamline configuration and cache handling by @OoBook in https://github.com/unusualify/modularity/commit/be6cc18e4611ac62a0a04557eaa2328b828be858
- optimize admin URL configuration handling by @OoBook in https://github.com/unusualify/modularity/commit/a75f74ac602fb392d111b7393004fdceebee9ffd
- replace modularityConfig calls with Modularity methods by @OoBook in https://github.com/unusualify/modularity/commit/aebadf8c328f4d198326672101782e101e591082
- replace Fragment with span for improved HTML semantics by @OoBook in https://github.com/unusualify/modularity/commit/178108f43fd5f756ba15b32d22020c7e917e5899
- streamline modal rendering and improve validation checks by @OoBook in https://github.com/unusualify/modularity/commit/e1b4b84812c50ba033d74b76f35748c029b1e5b9

### :lipstick: Styling

- lint coding styles for v0.35.0 by @OoBook in https://github.com/unusualify/modularity/commit/d823ae6a938af2817d017d4ef3ec4ae3ac978dae

### :white_check_mark: Testing

- add comprehensive tests for File model functionality by @OoBook in https://github.com/unusualify/modularity/commit/eb332a0de23cabc38ac4a51c8441643e69d1a40c
- enhance modularity configuration setup for testing by @OoBook in https://github.com/unusualify/modularity/commit/f80e4b45cf340548114623f8cf0e90baf48fa3c8
- add comprehensive tests for Media model functionality by @OoBook in https://github.com/unusualify/modularity/commit/5c833e8a8eed702b6d19b87e6f3c7aa00bc77fe9
- enhance updateProcess method and mock UeForm validation by @OoBook in https://github.com/unusualify/modularity/commit/fd31b1b56def53a5776185c5c9bd59ea65f15782

### :package: Build

- update build artifacts for v0.35.0 by @OoBook in https://github.com/unusualify/modularity/commit/5009e2ca0176e7d3212db40de3c87ef6af745a4c

## v0.34.0 - 2025-06-11

### :rocket: Features

- enhance sidebar menu item handling with role-based access control by @OoBook in https://github.com/unusualify/modularity/commit/00fb5eb2a7ea5769f5c625a699925d594e72d953
- enhance validation and button behavior for improved user experience by @OoBook in https://github.com/unusualify/modularity/commit/289e479c9e267beb66db1de7b169cb8b49ea0bfd
- add computed property for flattened processable details by @OoBook in https://github.com/unusualify/modularity/commit/d446e1fa807efafa069840720d033e8cafbdbcfb
- enhance email message handling for notifications by @OoBook in https://github.com/unusualify/modularity/commit/5d88430e4f0de22a423fc3c9fafadc4c52e10b38
- add stateableCode attribute for improved state management by @OoBook in https://github.com/unusualify/modularity/commit/ffecc32e1f3e24379ac59b7993d85167ebeabf19
- add noAutoGenerateSchema prop to control schema generation by @OoBook in https://github.com/unusualify/modularity/commit/adaeb6823cc3cfad15cb442278efb8de5a7389a0
- add no-auto-generate-schema attribute to Form component by @OoBook in https://github.com/unusualify/modularity/commit/2caaaca7571630ca6d2bee165481e1237ed89f69
- integrate authorization checks for form submission by @OoBook in https://github.com/unusualify/modularity/commit/9791f7443a661e0374897eebf31e2ad40c050c57
- enhance button behavior based on submittability state by @OoBook in https://github.com/unusualify/modularity/commit/845631e3c99e7801189164f75518b323a76ca462

### :wrench: Bug Fixes

- streamline translation query handling for improved clarity by @OoBook in https://github.com/unusualify/modularity/commit/d9012ea6af886f7927a36d527f9df961579fe88b
- improve file removal logic for better error handling by @OoBook in https://github.com/unusualify/modularity/commit/47c724989df2b04fcc4c94e2154583fc98a928ce
- update tooltip visibility condition for search functionality by @OoBook in https://github.com/unusualify/modularity/commit/a1b20dded7b69e110d0e131a2f5ab0b68f85a42b

### :recycle: Refactors

- update layout and clean up unused code by @OoBook in https://github.com/unusualify/modularity/commit/37269df843ec343dd6ba5ba31f3bc0d061f0e5ff
- comment out unused logic for clarity and streamline caching by @OoBook in https://github.com/unusualify/modularity/commit/a7bac31e2f072db9b2f9c72a907f8b4b9467fcd3

### :lipstick: Styling

- lint coding styles for v0.34.0 by @OoBook in https://github.com/unusualify/modularity/commit/25435834383782daa948eb8d38920c53d5232007

### :package: Build

- update build artifacts for v0.33.0 by @invalid-email-address in https://github.com/unusualify/modularity/commit/5211f01f2a90f775476099f6a562c9d2acb54ef6
- update build artifacts for v0.34.0 by @OoBook in https://github.com/unusualify/modularity/commit/29c3c095240cd9f3bf89dcb5dd37ac28fccb5e05

## v0.33.0 - 2025-06-05

### :rocket: Features

- enhance table functionality with pagination and max height support by @OoBook in https://github.com/unusualify/modularity/commit/9b0da3f9b3919043c87d3dcb9dc565f83a949c11
- add header removal functionality and enhance header filtering by @OoBook in https://github.com/unusualify/modularity/commit/592cf11359a57e570790d0a6db0c1f5a2b208976
- enhance header actions and search functionality by @OoBook in https://github.com/unusualify/modularity/commit/7e7de44b4e2669676709a6c929e8414676695327
- enhance response modal with dynamic properties by @OoBook in https://github.com/unusualify/modularity/commit/8e1f4c0d1a92376889d76753e0e9a62210d8482f
- add success alert on assignment creation by @OoBook in https://github.com/unusualify/modularity/commit/7d2ef950a439d56e1ae0f7613ef620a072da5ef4
- add items per page options for improved pagination flexibility by @OoBook in https://github.com/unusualify/modularity/commit/f6941150cf82286d742b6bcf59b2b1690aa369ae

### :wrench: Bug Fixes

- update formatter to use selected headers for improved functionality by @OoBook in https://github.com/unusualify/modularity/commit/94fd9383837a5cd3da44d6d79aa624e2f20c349e
- ensure future date validation considers time by resetting hours to midnight by @OoBook in https://github.com/unusualify/modularity/commit/da048137a8aba446e03169f8a386abd1202f2c17
- close create form modal after submission to improve user experience by @OoBook in https://github.com/unusualify/modularity/commit/a0aa424b3f8320180e0eefb84ba9ac9431cf4417
- adjust pagination logic to handle zero items per page correctly by @OoBook in https://github.com/unusualify/modularity/commit/f05da66b4c7f1b66f6c67e936a4edcc41fe213b8
- handle null schema in getFormFieldsRelationships to prevent errors by @OoBook in https://github.com/unusualify/modularity/commit/917284e2516da5529ef41b19f1217fdaa555097e
- modify translation query logic for improved flexibility by @OoBook in https://github.com/unusualify/modularity/commit/fb53ca5707a77aa2dce2abd0d3f01a3ee2e47494

### :recycle: Refactors

- increase scrollbar width and enhance thumb styling by @OoBook in https://github.com/unusualify/modularity/commit/6046ed2a56c553a7c1ea0be7a7c156819c81f9ba
- optimize header filtering logic and improve null handling by @OoBook in https://github.com/unusualify/modularity/commit/1e3fe4168d836a899be5e9ae6a6fbc3c81e39ce9
- update default header properties for improved consistency and functionality by @OoBook in https://github.com/unusualify/modularity/commit/a61355025fbe99c45ba732dae4aac81f6b1650fe

### :package: Build

- update build artifacts for v0.33.0 by @OoBook in https://github.com/unusualify/modularity/commit/bd0ed1e5b24a8be0b29f837ee869e79d5c5ecb7c

### :beers: Other Stuff

- change default width for action headers from '100px' to 100 for consistency by @OoBook in https://github.com/unusualify/modularity/commit/be983fa88371b8c8db5aaa715e13f3679830b63d

## v0.32.0 - 2025-06-04

### :rocket: Features

- add 'Surname' field to SystemUser configuration by @OoBook in https://github.com/unusualify/modularity/commit/9d4bd1a74c9ab614f1f013a2aeecf82754e9e867
- change Role model of Role module by @OoBook in https://github.com/unusualify/modularity/commit/2340b2a2ada7f6a388e86b8338a53b1fc772a6b2
- add setFirstDefault feature to InputHydrate class by @OoBook in https://github.com/unusualify/modularity/commit/49e91e66ba1f1d236fc47d8884ea9b55d1f5c039

### :wrench: Bug Fixes

- update item deletion checks for consistency by @OoBook in https://github.com/unusualify/modularity/commit/16b11e1dcca4a46073ca106ab6d91912a1be5c08
- update name validation rule to require a minimum of 2 characters by @OoBook in https://github.com/unusualify/modularity/commit/9fdaf72318e925846e60dc699f07b7445c690124
- comment out 'editable' property in SystemUser configuration by @OoBook in https://github.com/unusualify/modularity/commit/08953614d0d684940be7b5ec91996a3f04c92b10
- add password generation feature and enhance reset password form by @OoBook in https://github.com/unusualify/modularity/commit/5e0eaffb740417d06f50767257525512d4b9a3e3
- change validation rule of UserRequest by @OoBook in https://github.com/unusualify/modularity/commit/fbcf2119115c263630c1091be46d818e1a33b250

### :recycle: Refactors

- restrict users from role updating except superadmin and admin by @OoBook in https://github.com/unusualify/modularity/commit/9c4e802e872d126364032b93d421048a441fe24e

### :lipstick: Styling

- lint coding styles for v0.32.0 by @OoBook in https://github.com/unusualify/modularity/commit/27ff5c8615f85143433ece9dbf903d27bb59f43f

### :package: Build

- update build artifacts for v0.32.0 by @OoBook in https://github.com/unusualify/modularity/commit/d3cc676eb795ca047e3027984ad7a29f5008fc43

## v0.31.0 - 2025-06-02

### :rocket: Features

- enhance pagination component with custom buttons and loading indicator by @OoBook in https://github.com/unusualify/modularity/commit/9af28a989141c3289077928a80042b6593d1f345
- add pagination options for footer component by @OoBook in https://github.com/unusualify/modularity/commit/483ab5781cc6d5152db3a91c1857eb1b74cb7ca2
- add conditional visibility for filter buttons by @OoBook in https://github.com/unusualify/modularity/commit/71c7cf5d479bbbb6204b62a8aaccdd290e395a94
- enhance widget configuration handling by @OoBook in https://github.com/unusualify/modularity/commit/3dcead55c2c2347be7d2fcfac47c112ee242bd3b
- enhance header layout with subtitle support by @OoBook in https://github.com/unusualify/modularity/commit/de00a0655ae7a129f018c27013f0c000dd76399c
- add rounded and elevation props for enhanced styling by @OoBook in https://github.com/unusualify/modularity/commit/878caf940518d3c40c2a8aaa65b8c8462f4e8e1f
- introduce slots for value and label customization by @OoBook in https://github.com/unusualify/modularity/commit/591be788aebb7767a819c3be2dfc3d6eb14bad07
- add new color variables for secondary and green themes by @OoBook in https://github.com/unusualify/modularity/commit/500dba8b45a65dad561ab45bffa416ded99098da
- set timezone on form mount by @OoBook in https://github.com/unusualify/modularity/commit/f26119e865dd989e0f71651d716e29b35c37c161
- add hidden timezone input field by @OoBook in https://github.com/unusualify/modularity/commit/e297f7573d39795ba6dfc5565c3c09126180fc28
- implement auto locale detection based on environment setting by @OoBook in https://github.com/unusualify/modularity/commit/76a94aa433507eca5392cd6eb243e69b65a318d0
- add method to update event parameters by @OoBook in https://github.com/unusualify/modularity/commit/59f4b2cf9e0a28fda8a3bcec7356f7f5caa82189
- store user timezone in session after authentication by @OoBook in https://github.com/unusualify/modularity/commit/642b45b5126cc120d18b1311d27d34b83ed29367
- add  creator record scope by @OoBook in https://github.com/unusualify/modularity/commit/96d840b529286b72ef662b86ec71b3c1a06aa95d
- add timezone field to login forms by @OoBook in https://github.com/unusualify/modularity/commit/ac5e93cd9e867618d9e6eea7efb7e7b427909003
- update column ratios and add date formatting for text inputs by @OoBook in https://github.com/unusualify/modularity/commit/44939718b7dd1a94bca1095dcf38cd32e3902f7a
- enhance last status assignment query with timezone support by @OoBook in https://github.com/unusualify/modularity/commit/d55fbaf3c38a2cc8151f5ee15bfe48d6c54b9475
- enhance date range handling in metrics processing by @OoBook in https://github.com/unusualify/modularity/commit/0d9f9e1f26631d8bbb2f293601689c3d7c9007ab
- add scopes for unanswered chat messages by @OoBook in https://github.com/unusualify/modularity/commit/f4a590b45b68bc2f1100e78ec4e06c460a162fdd
- enhance metrics filtering and date input handling by @OoBook in https://github.com/unusualify/modularity/commit/5a80ae26bacdcf655a7b612e0b2fa076889380fd
- streamline count retrieval and add table filters by @OoBook in https://github.com/unusualify/modularity/commit/a9c68cdb6cdcb89fb2c36a85bf46f9a7165af710
- add methods for table filters and count retrieval by @OoBook in https://github.com/unusualify/modularity/commit/6729c779bd09b1d93e6eecf753606937ede029df
- add method for table filters based on authorization by @OoBook in https://github.com/unusualify/modularity/commit/94c098d0dc7b87bd9e1104da39a6cdc03d94775b
- add table filters for assignment retrieval by @OoBook in https://github.com/unusualify/modularity/commit/443cdc1c281c527e71246b78b98207344816f2ab
- add validation for user roles to prevent assignment of superadmin role by @OoBook in https://github.com/unusualify/modularity/commit/208384b08e950aa009eaeab92fbbbd68e7bfc822
- add scopes for filtering chat messages by creator by @OoBook in https://github.com/unusualify/modularity/commit/0e6be1da5424cc2758c1e7e4b668ed9a62b63fab
- add events for process history creation and update by @OoBook in https://github.com/unusualify/modularity/commit/8eaaf4865d7a872796b713236172a02d1a18fcd2
- include SystemPaymentDatabaseSeeder in default seeding process by @OoBook in https://github.com/unusualify/modularity/commit/bff6b7215071d9290d29ae115edeaadff88906eb
- configure translatable locales for country seeding by @OoBook in https://github.com/unusualify/modularity/commit/7bca40432d545c03be813c03964cbd6d101b3407
- introduce event for user registration process by @OoBook in https://github.com/unusualify/modularity/commit/f6c7ed1f7e0dfcec7317295596be39bcec3b3ad8

### :wrench: Bug Fixes

- ensure custom options are merged correctly by @OoBook in https://github.com/unusualify/modularity/commit/cd35891969b098026dda6e7f3d7c77457c10a97b
- update asset publishing tag for modularity by @OoBook in https://github.com/unusualify/modularity/commit/41b5af2a6e7ae15a93550e93d5283c23074cbdac
- add support for hidden input type by @OoBook in https://github.com/unusualify/modularity/commit/b1cf78ac62e2ddcbcbbbd7ecda4045a20ed9e550
- update available user locales to include only English by @OoBook in https://github.com/unusualify/modularity/commit/158c594e091227e528903274d6d6531289a4ac32
- adjust max-height style for responsive design by @OoBook in https://github.com/unusualify/modularity/commit/b3ab4277a1907f2ded3e43e3081ff450fea63240
- enhance event class selection and module integration by @OoBook in https://github.com/unusualify/modularity/commit/e992fccfe8e8901465cb78b9f903558260617228
- update badge color and styling for improved visibility by @OoBook in https://github.com/unusualify/modularity/commit/7e1c0a2d8cfee10302aaf883497112735084d14a
- adjust responsive width values for display sizes by @OoBook in https://github.com/unusualify/modularity/commit/70c87bf3df44721d7c83f9936478a737972c7e64
- update default page value and query parameter handling by @OoBook in https://github.com/unusualify/modularity/commit/79558fd1c009769e95a8ffa2186dbe37baf000f0
- handle string interval conversion for date calculations by @OoBook in https://github.com/unusualify/modularity/commit/4b2838d8870ba3caa3774d513417743364d7a6ef
- exclude interfaces from model retrieval logic by @OoBook in https://github.com/unusualify/modularity/commit/e6f0b48538cd75f88c9ccd9de2ec7665749ba110
- handle exceptions during model retrieval by @OoBook in https://github.com/unusualify/modularity/commit/d420f953926593ee165951b5d7c62e5dcc3f6352
- enhance default value handling for multiple inputs by @OoBook in https://github.com/unusualify/modularity/commit/4a8a19078119ad5cc8cb6e9657e3cbc91a89aba3
- improve pagination logic and loading state handling by @OoBook in https://github.com/unusualify/modularity/commit/988a7ffc7c83c7505e9aa61982405389a0f4fccb
- add 'spreadable' property to email fields for enhanced flexibility by @OoBook in https://github.com/unusualify/modularity/commit/e2b2c3c0572e6738dbe62a998f7a392de442b8ea
- improve sort handling in orderScope method by @OoBook in https://github.com/unusualify/modularity/commit/0030ce6ccd2cdebf4e1c23bc72981b239fe1b057
- update default page value for improved pagination behavior by @OoBook in https://github.com/unusualify/modularity/commit/3f5bed676cdaac58842f70f6d957302955e7ea3c
- add 'force' property to main filters for enhanced functionality by @OoBook in https://github.com/unusualify/modularity/commit/8c2157e38e6a33f3329be1de8e8505599e205d18
- update description rendering logic for improved clarity by @OoBook in https://github.com/unusualify/modularity/commit/650366a6648d183451d2ed0db4c741e7eda4f619
- add height class to checkbox for improved layout consistency by @OoBook in https://github.com/unusualify/modularity/commit/96ece835bd3421b3aa92d375a22161197809c7af
- extend regex to include 'Span' for component creation by @OoBook in https://github.com/unusualify/modularity/commit/aeb4d73c4aba5951d03b8627e0acb7c3d367bfee
- update dialog visibility conditions for guest users by @OoBook in https://github.com/unusualify/modularity/commit/086595b65d4eae2a83ba7b93ebf33803f0f701ea
- enhance currency conversion and exchange rate handling by @OoBook in https://github.com/unusualify/modularity/commit/e61a72bb583620d51d79cd1a7a5f0bdb740831dd
- remove debug statement from rulesForTranslatedFields method by @OoBook in https://github.com/unusualify/modularity/commit/e1f2f372ed3f9f42af85ff9045f1ba43904aea4f
- update asset publishing tag for modularity by @OoBook in https://github.com/unusualify/modularity/commit/8ec6f96cb7f08662c75bebbcc287bf18fb0c7ec9
- update roles configuration for improved component integration by @OoBook in https://github.com/unusualify/modularity/commit/4930fdf6dce72a559fb5ae750d21ca9bb6d11a42
- clean up code and improve query handling by @OoBook in https://github.com/unusualify/modularity/commit/ca865eac273fc9c94a4084003840a891ad544102
- update column classes for improved text wrapping by @OoBook in https://github.com/unusualify/modularity/commit/b47c3706d276988597de9baa86588b069ee67449
- update pagination logic and variable naming for clarity by @OoBook in https://github.com/unusualify/modularity/commit/fdd78fb2e6a0c53fa3c7b0b4af77e9950b1ea0db
- refine pagination logic and improve readonly state handling by @OoBook in https://github.com/unusualify/modularity/commit/4aed602d45db7523812014dffa3c4bc287b8b90e

### :recycle: Refactors

- add setWidgetAlias method and update widget class by @OoBook in https://github.com/unusualify/modularity/commit/d88013dd79d54f9ad813903a6d076051f07b3962
- update default table attributes for styling enhancements by @OoBook in https://github.com/unusualify/modularity/commit/ee6282f5cb840d9d04416cb1685987cff2c55ff5
- update class attributes for improved styling by @OoBook in https://github.com/unusualify/modularity/commit/5ef1d619241f5afca9e39bfff03b20e215f10814
- refactor filter retrieval and enhance flexibility by @OoBook in https://github.com/unusualify/modularity/commit/8bee6dbfcf0fd279f2f30736e7070c09558cf33a
- enhance input handling and default value management by @OoBook in https://github.com/unusualify/modularity/commit/24730904cf20b82cf2afe67a5fcc9762bf535ff0
- enhance input props and default value handling by @OoBook in https://github.com/unusualify/modularity/commit/c1b240fa975433d04da9672b106a3f98af6d2082
- update input properties for user roles and avatar handling by @OoBook in https://github.com/unusualify/modularity/commit/c601b9e11800cfd2209c519e5039d7265dee5417
- streamline right slot structure for improved readability by @OoBook in https://github.com/unusualify/modularity/commit/d72991d3022d9da31271a6297a61d521ba82c362
- adjust divider margins for improved layout consistency by @OoBook in https://github.com/unusualify/modularity/commit/eaca77b7e78f1d01abc0f2fd0f08ef490821b2d3
- remove unused seeder for default test users by @OoBook in https://github.com/unusualify/modularity/commit/5ea106ad3141a8c3c02e743fcf47c0f84642cff1

### :lipstick: Styling

- lint coding styles for v0.31.0 by @OoBook in https://github.com/unusualify/modularity/commit/039dd2993541c759d4a2365f03f1096c965a7f4a

### :package: Build

- update build artifacts for v0.31.0 by @OoBook in https://github.com/unusualify/modularity/commit/9dfca9fad7a1d61493cf24aab05fb237f18554cb

### :beers: Other Stuff

- remove unnecessary newline in config.php by @OoBook in https://github.com/unusualify/modularity/commit/70ebd8d726d364b0918fe69e6efeba3fd9a5a00a
- add todo lastHistory method by @OoBook in https://github.com/unusualify/modularity/commit/357ef4759bde97b9b44c09e52a1e2e44d4afc1f8
- remove initializeInput by @OoBook in https://github.com/unusualify/modularity/commit/e96a2880d374523e17fe4cd6bf82451114b06f43
- add country permissions for role management by @OoBook in https://github.com/unusualify/modularity/commit/b8170fd61d7cf1a5730a4c7b0000316e634f2915

## v0.30.0 - 2025-05-26

### :rocket: Features

- add clearOnSaved prop to reset form state on successful submission by @OoBook in https://github.com/unusualify/modularity/commit/15163ba394a6f5577e1327ee3e4545ae977beee6
- add clearOnSaved option and update response structure by @OoBook in https://github.com/unusualify/modularity/commit/b68803c0aef29bdfc23778b0ace19082b4ee18bb
- add subtitle prop and enhance layout by @OoBook in https://github.com/unusualify/modularity/commit/fe4cad39d934982f7f6705cfdd50adab1c04626b
- add 'title' column to roles and update DefaultRolesSeeder by @OoBook in https://github.com/unusualify/modularity/commit/c17888069069fb9014406f3e5aa5ac9876b0789a
- :sparkles: add terms and conditions checkbox component by @OoBook in https://github.com/unusualify/modularity/commit/cc565190eeaee920c35a30875ac91a5e4e60f2c3
- add country management functionality by @OoBook in https://github.com/unusualify/modularity/commit/a824c63265e56b61f1d4bfd8415ca44f3e86534a
- restructure sidebar layout and enhance functionality by @OoBook in https://github.com/unusualify/modularity/commit/01b97067c19f52be1f6b2f6d96358d625a4be0de
- enhance company entity with country relationship by @OoBook in https://github.com/unusualify/modularity/commit/f6e23061b0d325d6d76777f9b81ccbcb976c0cd5
- add country-related entries to merges configuration by @OoBook in https://github.com/unusualify/modularity/commit/6fd369465ee9a2a4e644b284e77dc6c5cc7da585
- enhance registration process with company details and validation rules by @OoBook in https://github.com/unusualify/modularity/commit/1bf2779bd250467bb035ece84a536abd2812f36c
- update user configuration with country selection and form adjustments by @OoBook in https://github.com/unusualify/modularity/commit/66528b50cbb0ddb58466ee4214b455f85aa54cea
- add country_id validation rule for company requests by @OoBook in https://github.com/unusualify/modularity/commit/df859af2f857b3e7fd9f5651988a7390081baf7d
- add country_id validation rule for user requests by @OoBook in https://github.com/unusualify/modularity/commit/0b192b33321aaf333dcd94968d646a15112da69f
- add 'Company' permission and update deletion permissions by @OoBook in https://github.com/unusualify/modularity/commit/470ec686c5c2ade8787e340cde17d9e5d4d5a03a
- enhance query parameter handling and response updates by @OoBook in https://github.com/unusualify/modularity/commit/8a0f05ae6182cedcbb3fdde5c36ab9877ed4e6fb
- enhance pagination logic to support specific record retrieval by @OoBook in https://github.com/unusualify/modularity/commit/7a74ab7873996c5d497ab64a826580d5e8061236
- add optional ID parameter for item retrieval by @OoBook in https://github.com/unusualify/modularity/commit/fca47ef4bbd12214b0ae48355215c7694cd1f241
- add configuration for notification channels by @OoBook in https://github.com/unusualify/modularity/commit/80c16640782b934b84bc4d7b46e81f71399e2d88
- :sparkles: implement state change notifications by @OoBook in https://github.com/unusualify/modularity/commit/420519d30eaa5632eb23bbfbf1eb63126a515402
- enhance scope filtering with dynamic configuration by @OoBook in https://github.com/unusualify/modularity/commit/0780cfdcce081fca1252b1d5e0701fbc34fb7e9e
- enhance dynamic filtering capabilities by @OoBook in https://github.com/unusualify/modularity/commit/6b432079cbfd9d43fea4e56ea49c2de6a71a4a92
- update navActive configuration retrieval by @OoBook in https://github.com/unusualify/modularity/commit/873d435a345802ae10f5754a4b850bc725be7e99
- add readonly prop to checklist items for enhanced control by @OoBook in https://github.com/unusualify/modularity/commit/632f100bb292853bb29fce24180fe2e4c7c8d3a4
- add new hook for dynamic attribute casting by @OoBook in https://github.com/unusualify/modularity/commit/26c3250a39831934e1fb6ae4024f00b8f49663da
- add functions to update table elements and their attributes by @OoBook in https://github.com/unusualify/modularity/commit/24511152bd85adc6c77ab6ced899d45051205589
- enhance action handling with pre-processing and dynamic modal support by @OoBook in https://github.com/unusualify/modularity/commit/11dbc2067364228ee175946b72d12301eaea4683
- implement MyNotification feature with CRUD operations and routing by @OoBook in https://github.com/unusualify/modularity/commit/5c79f6d57d33e92806cbe6b29f1f4d3a47dbe73a
- enhance routing for MyNotification with bulk mark read functionality by @OoBook in https://github.com/unusualify/modularity/commit/312128a13a4e78c2cfaffd068269d60bcc97ef62
- add MyNotification permissions for various roles by @OoBook in https://github.com/unusualify/modularity/commit/07c2151e8bd8b69f426c2e2f60bf6ecf7adb46b5
- enhance notification handling with unique token and HTML message support by @OoBook in https://github.com/unusualify/modularity/commit/0ec04f4a34d75241aa2c445810dcf7fd459cdefc
- enhance status formatting with dynamic icon and color support by @OoBook in https://github.com/unusualify/modularity/commit/ffce0c68f804008a7cf6f06996175cd59739f235
- introduce FeatureNotification class for enhanced notification handling by @OoBook in https://github.com/unusualify/modularity/commit/db221afad15dacc0c321feeec1ae45cdda733833
- add event and notification classes for assignment management by @OoBook in https://github.com/unusualify/modularity/commit/19a88755a7979cb8e8fd94aeb46af75d0238b480
- add payment event and notification classes by @OoBook in https://github.com/unusualify/modularity/commit/7639d0c841e650e680fb734a8e33256ed327ecc6
- add subject attribute to Notification entity by @OoBook in https://github.com/unusualify/modularity/commit/a1046c6d5dbbb24fb0330253d3ee5c0dd012e1c1
- register new event listeners for assignment and payment events by @OoBook in https://github.com/unusualify/modularity/commit/025014b363c82d7109cb8ccf39c725c6304a0c9b
- implement polymorphic relation for paymentable model by @OoBook in https://github.com/unusualify/modularity/commit/272554a573d24267b9c9c59a07ad01e836f31e0f
- add badge support to navigation items by @OoBook in https://github.com/unusualify/modularity/commit/e85687f6600ea5e6d87a86eab98000cd80951182
- add subtitle to payment table options for improved user guidance by @OoBook in https://github.com/unusualify/modularity/commit/2854c2ac072e19e42c23ce9b9710710f703d6126
- add item deletion status computation by @OoBook in https://github.com/unusualify/modularity/commit/27c2b08df398b40f966423abd74afe8b6d2f7aa5
- add refreshOnSaved prop and enhance saveForm logic by @OoBook in https://github.com/unusualify/modularity/commit/c6e1a332f1eff9e100bdac6b3e21f3ee8132e051
- add getCustomRowData method by @OoBook in https://github.com/unusualify/modularity/commit/bd599497ec0ec87c33c17b3bfa5df19cd4c3abc0
- add isAuthorized attribute for user authorization check by @OoBook in https://github.com/unusualify/modularity/commit/f79222b5684566b9f8b3119c95f987fb4b10ceac
- enhance notification routing logic by @OoBook in https://github.com/unusualify/modularity/commit/dde02551fc81908836037138c81fb48695af2abe
- add model and token accessors by @OoBook in https://github.com/unusualify/modularity/commit/f41ef62141c0c05023e4768085dfe433d8590950
- add scope for retrieving unread notifications by @OoBook in https://github.com/unusualify/modularity/commit/451b768a9e74ed7fea4b9a3e3a909eab153c2a61
- add new price mutators for payment status by @OoBook in https://github.com/unusualify/modularity/commit/92b0b44f1f3f3303fca935db3046863ecb0b98ae
- enhance payment status initialization and mutator logic by @OoBook in https://github.com/unusualify/modularity/commit/b0d992f498069ce1058c9e8ecc45f003d8a873cc
- update locale and currency configuration by @OoBook in https://github.com/unusualify/modularity/commit/10fc6ac83a9c502e90ddd74fc59e9323d81f25d3
- add refund status and formatted payment status attributes by @OoBook in https://github.com/unusualify/modularity/commit/b862552d85c7acb878320b4a95471ae35be8b112
- update notification channels and add TaskAssignedToAuthorizableNotification by @OoBook in https://github.com/unusualify/modularity/commit/a1ad565e4cb40653ec9cb238f789b43318b19c07
- add scope for checking base price existence by @OoBook in https://github.com/unusualify/modularity/commit/4de977e379156a7989c75cc3763db21b82b931ad

### :wrench: Bug Fixes

- update payable table name and add foreign keys for payment service, price, and currency by @OoBook in https://github.com/unusualify/modularity/commit/d982ee8271687d6edc25208f790f75d940fa2971
- update payment currency ISO code and include additional fields in payment service creation by @OoBook in https://github.com/unusualify/modularity/commit/b6305a5e434d5b4710b02fb33b5c4606333cefff
- update width values for responsive design by @OoBook in https://github.com/unusualify/modularity/commit/6453fb90044637ec6f8c644df167bcdecdcfc65d
- enable validation rules for form fields by @OoBook in https://github.com/unusualify/modularity/commit/8b6f7f28b77387a06a7219f88d57989c9662521a
- add email verification upon password reset by @OoBook in https://github.com/unusualify/modularity/commit/dced0ecd28fdaa34ef285524fc0481d35e5dd050
- add mobile breakpoint prop for improved responsiveness by @OoBook in https://github.com/unusualify/modularity/commit/8866004e51330a08920b9d28368b5d78e8aaa29b
- add preview key handling for input fields by @OoBook in https://github.com/unusualify/modularity/commit/a811b48409f5775435bb06941f72f0cbf671bccb
- bind data to preview component for enhanced functionality by @OoBook in https://github.com/unusualify/modularity/commit/add9840199ef944bce74929ae6bda31a11f4d97f
- enhance rules handling for input validation by @OoBook in https://github.com/unusualify/modularity/commit/13ab2e3a0e8d1969432cdd23306dffa565057a4f
- improve filepond rules handling for attachments by @OoBook in https://github.com/unusualify/modularity/commit/ee830ab27be76aee36ab50074424ae1cb94d33b3
- enhance layout with padding adjustments by @OoBook in https://github.com/unusualify/modularity/commit/295829aa45d3b21e1c61fd43d6b148b020fb341f
- adjust form class padding for improved layout by @OoBook in https://github.com/unusualify/modularity/commit/e4de1faa1ac1d3601be30bf9ea216c37ed261470
- enhance filepond configuration for attachments by @OoBook in https://github.com/unusualify/modularity/commit/1d66ff2ad763d3f6adc66e857afc8864e6fc8a25
- update notification structure and functionality by @OoBook in https://github.com/unusualify/modularity/commit/42fa6f49655e3dad25e6f9ba1e1d2fd9d5f16eaf
- correct URL handling in getRouteActionUrl method by @OoBook in https://github.com/unusualify/modularity/commit/7934c3a7a902f27672627e386d8b5f2d9ff8f2be
- return rules in mergeSchemaRules method by @OoBook in https://github.com/unusualify/modularity/commit/c5d277f27fa048fd23a2bcb3829028dda736abc0
- adjust price value handling in getFormFieldsPricesTrait method by @OoBook in https://github.com/unusualify/modularity/commit/591b1d3921990b6bac3547a197b82b7c47e20365
- correct price calculation logic in price formatting method by @OoBook in https://github.com/unusualify/modularity/commit/112cc62dac8c8c1810d07112c003f56f4b9e8487
- update search key retrieval in manage table trait by @OoBook in https://github.com/unusualify/modularity/commit/c01f8fbbcb38ac54a9e6fbbc1473924fbaa15034
- improve payment price handling in afterSavePaymentTrait method by @OoBook in https://github.com/unusualify/modularity/commit/d2dbde0616fd14914eda30550b544842685aeefe
- update description rendering to support HTML content by @OoBook in https://github.com/unusualify/modularity/commit/e195f56e0be13c49e1726b68fba7b86da5faf13b
- update migration to add payment_service_id and currency_id foreign keys by @OoBook in https://github.com/unusualify/modularity/commit/1de18fc15c78f234d1b3251e6095e011cffb65d5
- update modal title and class attributes for notifications by @OoBook in https://github.com/unusualify/modularity/commit/43654c00010c3b12e47c034fae723c587b3960f6
- update conditions for table row actions to exclude completed payments by @OoBook in https://github.com/unusualify/modularity/commit/cf7bc6eb978290f6c79d8468848436d935f1cd6a
- uncomment notification for task assignment by @OoBook in https://github.com/unusualify/modularity/commit/d24f38deec8bdb24249b23d13a0438178397b929
- update getNotificationUrl method signature by @OoBook in https://github.com/unusualify/modularity/commit/e837b36e68b9e33dc8f84e8c4e20db24e489f9a6
- use updateQuietly for assignment status update by @OoBook in https://github.com/unusualify/modularity/commit/11e6bd16511e8b3399b49ef61e5e09ad2ce29043
- adjust raw amount calculations for price handling by @OoBook in https://github.com/unusualify/modularity/commit/da0a946085ead99601996f30dffa3829ec6cbc3a
- update payment price handling logic by @OoBook in https://github.com/unusualify/modularity/commit/026279634b713515b7e60cbdee79035c5ebd4f89
- ensure minimum repeats are met on initialization by @OoBook in https://github.com/unusualify/modularity/commit/7c877ac4543dc13214b2823094cfdbe4d01f6c18
- comment out itemTitle in payment service configuration by @OoBook in https://github.com/unusualify/modularity/commit/4b5211ea23cb869e3eabdb68e52e40c76f7b67eb
- improve mail message formatting for payment notifications by @OoBook in https://github.com/unusualify/modularity/commit/61a9a7456c00cdbf3209f0ee8951ad445f031a77
- enhance currency handling by @OoBook in https://github.com/unusualify/modularity/commit/84e90cbd554c6664266022f5e005fc531815ca14
- update facade reference from UNavigation to Navigation by @OoBook in https://github.com/unusualify/modularity/commit/074fac8ed6fb90ed06f76d800998b4672fedd6ed
- improve configuration handling for sidebar and profile menus by @OoBook in https://github.com/unusualify/modularity/commit/f4f0147547f08ed67cdfa3f4e80760418a0fb69d
- improve response handling in update method by @OoBook in https://github.com/unusualify/modularity/commit/87b54b89ee1c5ca6c2ee8a64c7c665c4fad9cad1
- update currency display logic and improve formatting by @OoBook in https://github.com/unusualify/modularity/commit/9fd7e05d5011e4c099cf894ac155e8b98b3ef6c5
- run impersonate middleware before 'language' middleware in core middleware group by @OoBook in https://github.com/unusualify/modularity/commit/28da4b9c8e83dadbb208568512e141fc12e0e368
- update persistent prop in Assignment component modal by @OoBook in https://github.com/unusualify/modularity/commit/c0e069bea983e51f231da194e183d8391ea056f7
- handle null authorization record in getAuthorizedModel method by @OoBook in https://github.com/unusualify/modularity/commit/9868e9775008611b6fcd9df7f28a37b79c54a2c0
- enhance event class selection and module event path handling by @OoBook in https://github.com/unusualify/modularity/commit/bedc00dd9e7082c471f37a318649cd2759891a08

### :zap: Performance

- enhance performance returning index resource by @OoBook in https://github.com/unusualify/modularity/commit/e5fed06ffe060e328c4e802b0d5de65c229bb675

### :recycle: Refactors

- simplify form schema creation and clean up unused code by @OoBook in https://github.com/unusualify/modularity/commit/b38f3fa9b27e936aad93da690dd38791e1ca791b
- comment out unused Apple sign-in button code by @OoBook in https://github.com/unusualify/modularity/commit/8684c6a76993e1e5fbe37fc5ed5b822120584bba
- make pageTitle variable optional for improved flexibility by @OoBook in https://github.com/unusualify/modularity/commit/ec09a4d37c48d1d1bafa6d0bc9404d509f3843c0
- update tab management and subtitle styling by @OoBook in https://github.com/unusualify/modularity/commit/186077a80c082acdf96ee2515207cead9f711835
- enhance tab component with additional properties by @OoBook in https://github.com/unusualify/modularity/commit/6a97aa26fd0cc605185a54d24723ff13f383e61c
- update scheduler commands for consistency by @OoBook in https://github.com/unusualify/modularity/commit/d1240926319442179433fead58f6d6076ae035d1
- remove debug statement from register method by @OoBook in https://github.com/unusualify/modularity/commit/05977944870cc3efa0bb19edbd1203d395e5cfe8
- enhance layout and search functionality by @OoBook in https://github.com/unusualify/modularity/commit/18c072173a275354daa8e673d34b17be6694bf86
- improve slot binding in authentication layout by @OoBook in https://github.com/unusualify/modularity/commit/5c1d24c38123825edef3d2f04093986cfe60af9c
- streamline form structure and enhance button options by @OoBook in https://github.com/unusualify/modularity/commit/9cde7c0d07d6be3ff323d12b3b894e65a61cba95
- update terms of service checkbox type and rules by @OoBook in https://github.com/unusualify/modularity/commit/d032ce245392b7d305d5983a3eaa2170f036229c
- comment out unused class properties for clarity by @OoBook in https://github.com/unusualify/modularity/commit/166fe8bc4b6bce7ec49330ee257907fdc95373fe
- rename re_password field and comment out validation rules by @OoBook in https://github.com/unusualify/modularity/commit/baa1db74f22ccdc07ae971d5700d705e5c074a90
- enhance layout responsiveness and styling by @OoBook in https://github.com/unusualify/modularity/commit/8be3cb6236eb712ba16b6b6187a4e2396347391b
- enhance layout responsiveness with mobile breakpoint and order adjustments by @OoBook in https://github.com/unusualify/modularity/commit/2c0112ea610e41da16619644124cf1b9b12ba30b
- uncomment vertical divider for improved layout clarity by @OoBook in https://github.com/unusualify/modularity/commit/800c87b6c135a3f3f67341e6682909cc964a2143
- enhance entity functionality with traits by @OoBook in https://github.com/unusualify/modularity/commit/3a8731e64cadf0a1392952a118813e62578795eb
- comment out module activator for clarity by @OoBook in https://github.com/unusualify/modularity/commit/5569ba438daad264710d5e1bd2651113757ca439
- update country field to country_id for consistency by @OoBook in https://github.com/unusualify/modularity/commit/ecf6fe63b1a62651eea64157bf5164f219d82867
- update country field to country_id and adjust validation rules by @OoBook in https://github.com/unusualify/modularity/commit/60b56915361e2669b6814b8859761c50c83c5599
- comment out hardcoded company field values by @OoBook in https://github.com/unusualify/modularity/commit/955f2df43433b46a39110f96e79d1d523d34a02a
- update country field to country_id in companies and users tables by @OoBook in https://github.com/unusualify/modularity/commit/10e7f2e6c84ba8d6bea9903b38aac10a46634086
- update country field to country_id for consistency by @OoBook in https://github.com/unusualify/modularity/commit/13a3627727faee99e89459e7924e938971df13ee
- move observer to Entities by @OoBook in https://github.com/unusualify/modularity/commit/faef5cd96280d7754f474b782844f663ee54b256
- enhance recursive component with bind-data support by @OoBook in https://github.com/unusualify/modularity/commit/9b2d00fef8200b496a7a1323f3a90a27145c2daf
- enhance input properties and schema handling by @OoBook in https://github.com/unusualify/modularity/commit/35aea951412ac775a27b7217efa9cf5cac6f8d8c
- comment out unused field cleanup logic by @OoBook in https://github.com/unusualify/modularity/commit/24dce74ffe61c0058e055bcc69839f064754b7f5
- streamline state management and enhance relationships by @OoBook in https://github.com/unusualify/modularity/commit/068398a5d26f19ce808c41e19222d735637e79d7
- rename URI methods to URL and update references by @OoBook in https://github.com/unusualify/modularity/commit/5cc49296bea437aec0c7a7d28fed17acf1a9cc69
- optimize query parameter handling and local storage management by @OoBook in https://github.com/unusualify/modularity/commit/e583bf25d001e6c0a4e303f59ec2a635327dd1bc
- enhance route handling and refactor endpoint methods by @OoBook in https://github.com/unusualify/modularity/commit/fecad839b72b2529b7fdce30058a36c79112aa26
- comment out unused price-related logic in updating hook by @OoBook in https://github.com/unusualify/modularity/commit/4764d2cc670047d063f8642febe8b24058e69cf8
- remove obsolete notification form and index views by @OoBook in https://github.com/unusualify/modularity/commit/0dd921e89b48ce799ab67ebe3a23bdb9e992a5a0
- add seeder for default countries by @OoBook in https://github.com/unusualify/modularity/commit/79ecdeaadc23eb0cc01b51cce31340f4bf114f2e
- streamline table row action configuration by @OoBook in https://github.com/unusualify/modularity/commit/34589eb8eac720eb033a67514dd28935008316a5
- clean up user seeder by removing commented-out entries by @OoBook in https://github.com/unusualify/modularity/commit/5b2991f2dec01c31b2f428f932445963c3549472
- clean up code by removing unnecessary whitespace and updating key definitions by @OoBook in https://github.com/unusualify/modularity/commit/cf93cbe1e768748c665ca41a5a6a9e126fcabe3b
- enhance modal component with fullscreen and title features by @OoBook in https://github.com/unusualify/modularity/commit/7c9171d3094864dfeed1aabf1d777dacc4a1d0f9
- update text rendering to support HTML content by @OoBook in https://github.com/unusualify/modularity/commit/d3ecd2259b7e1c6b22585d04e97ab7f74d6ecd33
- remove unnecessary URL manipulation in put method by @OoBook in https://github.com/unusualify/modularity/commit/79496ef634d1f5483af5603847b58201ca13212b
- simplify notification access control and streamline show method by @OoBook in https://github.com/unusualify/modularity/commit/599476ccd72746cadf1e27e2a47bc768417f3842
- rename UNavigation by @OoBook in https://github.com/unusualify/modularity/commit/ef7ebf90c8abf2bfee255f68e7898af4ef661ac3
- enhance sidebar menu item handling by @OoBook in https://github.com/unusualify/modularity/commit/03f927326d9325d34e2dd6d4e5ad5ef6e4b33f57
- streamline navigation configuration handling by @OoBook in https://github.com/unusualify/modularity/commit/c75d3fa231a483e567592c624560d25b4b1819ad
- update button text and layout adjustments by @OoBook in https://github.com/unusualify/modularity/commit/38130f3f21b0ca808949573108a90a8108f49863
- update notification configuration and improve user guidance by @OoBook in https://github.com/unusualify/modularity/commit/050d6ca246ce87276b4698fbd327986695673eeb
- streamline filter method implementation by @OoBook in https://github.com/unusualify/modularity/commit/b661439c6c05d6108f8af68b97ce49eb790ae401
- enhance item action handling and add new props by @OoBook in https://github.com/unusualify/modularity/commit/cf93ecb85b3f6c375ca326764c72491986bf5ac4
- update action handling based on item deletion status by @OoBook in https://github.com/unusualify/modularity/commit/7341cada0ba99cc8ea58b0635c425490bcfb4b0b
- pass isEditing prop to slots for improved state management by @OoBook in https://github.com/unusualify/modularity/commit/9c7e48f723d1a282766b961b1819c4ed05264184
- enhance component props for improved customization by @OoBook in https://github.com/unusualify/modularity/commit/cd382050fd512335b8eabeb13ab96ad5e2205fcc
- extract getExactScope method for improved scope management by @OoBook in https://github.com/unusualify/modularity/commit/069fe9f59a4db5e8626a922873dc3dc9bae6d368
- utilize getExactScope for mainFilters by @OoBook in https://github.com/unusualify/modularity/commit/a08283b3dda3621d556230040428781d38dadc80
- streamline form data handling in getFormData method by @OoBook in https://github.com/unusualify/modularity/commit/1eebae241201c56fe2d1e789e5ebbc53b00a8f75
- comment out draft status filter for clarity by @OoBook in https://github.com/unusualify/modularity/commit/fb0f557f69ddfc36517fab157ba56535f6c03032
- update action colors and enhance navigation action merging by @OoBook in https://github.com/unusualify/modularity/commit/45bb574d2a10c8c67460c9986d348808df1429d2
- simplify price retrieval logic in afterSavePaymentTrait method by @OoBook in https://github.com/unusualify/modularity/commit/55e50e9c724398a382fcbced011418cfa8e79ef9
- update getMailMessage method signatures to include notifiable parameter by @OoBook in https://github.com/unusualify/modularity/commit/e539830977e3272945f5704cf0f4d6a403a37b9f
- update notification methods for improved clarity and structure by @OoBook in https://github.com/unusualify/modularity/commit/ed914d55b08a8ff127094f51fc6b9c4ce3d745f8
- streamline notification methods and enhance structure by @OoBook in https://github.com/unusualify/modularity/commit/43ed283743e507532cab7aa505a71a8129c22cf6
- enable editing on modal and comment out delete action by @OoBook in https://github.com/unusualify/modularity/commit/742127cb9b4df917c8d2651206ac859c1ea29844
- notify all superadmins on payment failure by @OoBook in https://github.com/unusualify/modularity/commit/8a84129cb3f84282714eec04d8398efaf67c58bd
- remove unused imports for cleaner code by @OoBook in https://github.com/unusualify/modularity/commit/1eb2304447e99e89cdcbc0acf7df5a8d4a8ac87a

### :lipstick: Styling

- format class definition for improved readability by @OoBook in https://github.com/unusualify/modularity/commit/265b8b49cb3024604e170dd82e36957663ffed23
- add PHPDoc comments for methods by @OoBook in https://github.com/unusualify/modularity/commit/de049c01660cb8ce806c2328c98c89cbf1a947cd
- center align "Go Back" button in modal options by @OoBook in https://github.com/unusualify/modularity/commit/ef4dcf6ccbc2f4cd7ce4a630cc48e462179fb74b
- lint coding styles for v0.30.0 by @OoBook in https://github.com/unusualify/modularity/commit/02ebf9ea6af267674192e115642cea1e5447d25f

### :white_check_mark: Testing

- remove country field from factories and tests by @OoBook in https://github.com/unusualify/modularity/commit/69dbbf28142f8a7859366e65537c547ec05df28e

### :package: Build

- update build artifacts for v0.30.0 by @OoBook in https://github.com/unusualify/modularity/commit/3f5734d21a1fd2641894a9eedf41614380ab11cc

### :beers: Other Stuff

- add success messages for authentication by @OoBook in https://github.com/unusualify/modularity/commit/569de4e8471f1f3fd3e675ddffe704d34b4b94d4
- add page title for verification success view by @OoBook in https://github.com/unusualify/modularity/commit/df74140a6cd8e0667b15759681a99184c399d65f
- comment out unused Apple sign-in button code by @OoBook in https://github.com/unusualify/modularity/commit/f71cc4bf42ec053ca44868234b09f65cb2e180ae
- add terms and conditions language strings by @OoBook in https://github.com/unusualify/modularity/commit/c60ad18cbc956db7b753bd7497b8d79c735b896d
- add full-stack and Laravel guidelines for development by @OoBook in https://github.com/unusualify/modularity/commit/8023fdc5e797955ce001a357d86cf7a9b502b2a8
- comment out unused endpoint logic by @OoBook in https://github.com/unusualify/modularity/commit/8565983c0e298dc9f4e5987b2033543df8e638d0
- update table row action definitions for payments by @OoBook in https://github.com/unusualify/modularity/commit/06de5212767ff0b7d3e94ada1b6f5a227dcf702d
- add allowedRoles and new related field to payment configuration by @OoBook in https://github.com/unusualify/modularity/commit/d2ef298e208752a328283ead6e062e1e8bc525f1

## v0.29.1 - 2025-05-12

### :wrench: Bug Fixes

- update method calls to retrieve table columns for improved accuracy by @OoBook in https://github.com/unusualify/modularity/commit/98385cff7bebb63e997f2753bb50e081967089f1
- simplify route URL generation by removing unnecessary parameters by @OoBook in https://github.com/unusualify/modularity/commit/84a92b193712b7562a694e19afc4bae45d4dc1df

### :green_heart: Workflow

- update manual-release.yml by @web-flow in https://github.com/unusualify/modularity/commit/e1b03b2374be74d00411101cdc07eeeb3a2d8cc1

## v0.29.0 - 2025-05-12

### :rocket: Features

- add password confirmation label for user input by @OoBook in https://github.com/unusualify/modularity/commit/f8a8408db4a1264ec472006f8801c4b2fcf565bd
- add success message for password saving by @OoBook in https://github.com/unusualify/modularity/commit/6e5add4d8acf25ddd85331a71ecededa1b1576c3
- enhance form component with button positioning by @OoBook in https://github.com/unusualify/modularity/commit/85ccb9456258db6d423e2b94ea58ec383039dfcc
- add options slot for enhanced form flexibility by @OoBook in https://github.com/unusualify/modularity/commit/b9cde09e8e542a73ef59bd70e45f239ed2ccbe09
- add 'not exists' condition for item checks by @OoBook in https://github.com/unusualify/modularity/commit/ee0c84ddb20c90432f789ed72168fdf56574a175
- add password generation notification functionality by @OoBook in https://github.com/unusualify/modularity/commit/ac109b58c0e37fe4abf024c9ce7c7ea504dc4ac0
- implement password reset functionality by @OoBook in https://github.com/unusualify/modularity/commit/3c94a6909cba607a00f8d51c4f6e0caed21b469f
- add email verification functionality by @OoBook in https://github.com/unusualify/modularity/commit/4961b2654f81a67106e96dedaef7eb2f09d78c0c
- implement custom email verification logic by @OoBook in https://github.com/unusualify/modularity/commit/b79f15a70a3765b100f372502e49ba768664573d
- enhance user profile editing with email verification by @OoBook in https://github.com/unusualify/modularity/commit/ec972b67e61d7bf937f5007ef756525db92a8eec
- add email verification and password generation routes by @OoBook in https://github.com/unusualify/modularity/commit/4380f6c0635293092fda0715949eef5749d9afd4
- implement user creation with password reset notification by @OoBook in https://github.com/unusualify/modularity/commit/d319cd53c7547965774ad3ffb5ca6fc4c8a98542
- enhance assignment scopes and add new methods by @OoBook in https://github.com/unusualify/modularity/commit/655089c351cd61520388d1378e7ff60c499d3b57
- add authorization usage check method and integrate Allowable trait by @OoBook in https://github.com/unusualify/modularity/commit/b3108d57320685b20e314238f1e0667acf55f860
- add new query scopes for assignment filtering by @OoBook in https://github.com/unusualify/modularity/commit/9d0477830c6b0346b55fe920ead923eaeee59e4c
- enhance assignment filtering and request handling by @OoBook in https://github.com/unusualify/modularity/commit/ea12e4394d52d655c738a3782a634d39228e15ab
- add new authorization and task-related translations by @OoBook in https://github.com/unusualify/modularity/commit/10ec80590914bd7098857261667fb1444f2ea57e
- enhance task-related translations for better clarity by @OoBook in https://github.com/unusualify/modularity/commit/0b39ca5c5c251c3622060f2d32f0065d4156b419
- enhance role-based assignment query logic by @OoBook in https://github.com/unusualify/modularity/commit/6b494fc4cd6c47f19df35c2d6a92bf2b0235e1cf
- enhance filtering capabilities for assignments by @OoBook in https://github.com/unusualify/modularity/commit/56e8f46bdbd333ddb9cbfbcbf2170327942b732e
- enhance filter interaction with active state indication by @OoBook in https://github.com/unusualify/modularity/commit/cac026d162c121c93efdda90f1226b77ec4ef30a
- add statusIconColor method for assignment status representation by @OoBook in https://github.com/unusualify/modularity/commit/5e77eb44b062cbe2c541d579fa84f9dbd90ef6d5
- add methods for active assigner name and assignment status representation by @OoBook in https://github.com/unusualify/modularity/commit/d9d8dfa704f03637fc771b71e66ce720d810b2fc
- enhance filter logic with role-based permissions by @OoBook in https://github.com/unusualify/modularity/commit/4fa627e09b765a4d1e52171ebb0c4e36b6a52a85
- integrate Allowable trait for role-based component visibility by @OoBook in https://github.com/unusualify/modularity/commit/f7e71d78297b9af1b7842fb5c740d6db03e4feb0
- add appendIcon and appendIconAttributes props for enhanced icon display by @OoBook in https://github.com/unusualify/modularity/commit/c2d929a120c19e2ceda6e562f78f7346e3e4ecd3
- enhance metrics component with new props and functionality by @OoBook in https://github.com/unusualify/modularity/commit/66d74b9cff4642a5a224b4672b8e48c1a0c5b229
- add MetricController for handling metrics requests by @OoBook in https://github.com/unusualify/modularity/commit/f4d9f9d86b3ed082e3cd1ae4182771e1ead613fe
- add date range filtering scopes by @OoBook in https://github.com/unusualify/modularity/commit/2e0ef9a742d10dcfc1ac920f31fee0da995eea38
- enhance event management with new methods by @OoBook in https://github.com/unusualify/modularity/commit/b0362270c87fde75ab3f8c4ee7483d8470c94717
- introduce MetricsWidget for enhanced metrics display by @OoBook in https://github.com/unusualify/modularity/commit/5d24ea58de82e60595a48b4ac407a7abe6b4d04e
- add process status scopes for Eloquent queries by @OoBook in https://github.com/unusualify/modularity/commit/951a1e2cce875868e950e6af7358d09e957a4327
- add process status query scopes by @OoBook in https://github.com/unusualify/modularity/commit/c4dfa408c06c03c596ad4730214b189877062044
- add query scopes for chat messages by @OoBook in https://github.com/unusualify/modularity/commit/04864dfcfa9e633f5ae53441fc6232e16819ed93
- add scope for chat messages awaiting reaction by @OoBook in https://github.com/unusualify/modularity/commit/40e23b757fbb1978becf2dcac68d06be2a687bfa
- integrate ChatMessageScopes for enhanced querying by @OoBook in https://github.com/unusualify/modularity/commit/c4f52495de9ebf6bc970e9bb63bf66b3cc47e69c
- integrate ProcessScopes for enhanced querying by @OoBook in https://github.com/unusualify/modularity/commit/0182d12efc517a144e2370924b9e19650aa1c8e8
- integrate ProcessableScopes for enhanced querying by @OoBook in https://github.com/unusualify/modularity/commit/02c8d7e2126a5d5976182a55aa1ef78f15c56b72
- enhance authorization handling with new user retrieval method by @OoBook in https://github.com/unusualify/modularity/commit/429fc8b0836022cd06dfe95d3fd99e7f8f4fb659
- add HasProcesses trait for managing process relationships by @OoBook in https://github.com/unusualify/modularity/commit/e9b0db0a21b7cc5b19e0a97ca71662ed27590952
- handle callable metric values in metrics processing by @OoBook in https://github.com/unusualify/modularity/commit/48d0a935ffc1e9cd635bed9c18611684b0452adb
- enhance user profile data handling in JavaScript by @OoBook in https://github.com/unusualify/modularity/commit/afb1143ff28330c0da5956279bea5735a0a3e431
- enhance modal component with new props and layout adjustments by @OoBook in https://github.com/unusualify/modularity/commit/c8d88f75290c6483714fb54f3fc5701133c40e3b
- implement global modal service for dialog management by @OoBook in https://github.com/unusualify/modularity/commit/e727b2dc79bae189f0aeac6f7c9475fce78a7f4d
- implement dynamic modal component for flexible dialog rendering by @OoBook in https://github.com/unusualify/modularity/commit/797afe4e76ee79b1a1aa7000a26e612da78456a7
- register DynamicModal and ModalService components by @OoBook in https://github.com/unusualify/modularity/commit/add12816806b244bcfd8365ce342a82d646a7f64
- add DynamicModal component to layout by @OoBook in https://github.com/unusualify/modularity/commit/dbc62d4f6c0c6fea4776a70dc39061f10ed4d442
- add useDynamicModal hook and update index.js exports by @OoBook in https://github.com/unusualify/modularity/commit/33254deaeb73302cab7ebee07824d69f6da0b9a4
- add success and error response handlers for modal service by @OoBook in https://github.com/unusualify/modularity/commit/1296bfbe3643cb72bc82e8af44c16bf1941fd4f6
- add isGuest state and getter for user module by @OoBook in https://github.com/unusualify/modularity/commit/8081697f2c9f360847d01009372e8a1e9736a5a9
- integrate response handlers for improved error management by @OoBook in https://github.com/unusualify/modularity/commit/568d9b9a667d5cfa676c20e65a8a82a1923729c4
- enhance login form with dynamic attributes by @OoBook in https://github.com/unusualify/modularity/commit/3cfe6d353015ae72df7480b07dd296a9a7c17b36
- create event class for user registration by @OoBook in https://github.com/unusualify/modularity/commit/883c3665c551fa0129dbbdf1df86cfcdd95faaa5
- trigger ModularityUserRegistered event on user registration by @OoBook in https://github.com/unusualify/modularity/commit/d54c694915032be6eea7a61e70fc1690c036896e
- add registration form fields and validation rules by @OoBook in https://github.com/unusualify/modularity/commit/2705cf9160d6d291f5102e9cec9f23ecb179e323
- add dynamic attributes for registration form by @OoBook in https://github.com/unusualify/modularity/commit/9be6a3d148f3876904508c6e3bee890d9ed2dbe0
- add FilepondAvatar component and hydrate class for file uploads by @OoBook in https://github.com/unusualify/modularity/commit/e5cbed2824c6daf1946c25c6a6f612623eabccdf
- add avatar field configuration for user profile by @OoBook in https://github.com/unusualify/modularity/commit/5563d11a2aa25991b5dd693b36e83fc7a61f3776
- add disabled prop to phone input component by @OoBook in https://github.com/unusualify/modularity/commit/5945895c7e0af76762d92bd76ae03eb3a97fc64f
- create Company entity extending ModularityCompany by @OoBook in https://github.com/unusualify/modularity/commit/3c02ebe59d684836dc52dd7931273258b10e8e08
- integrate SpreadableTrait into Company entity and repository by @OoBook in https://github.com/unusualify/modularity/commit/ccac192c5e95f4489e19e1341ebe18ac3c882181
- add removeQueryKeys function to manage URL query parameters by @OoBook in https://github.com/unusualify/modularity/commit/8a79345629cfaf0d27373d34d8ab763bd835c466
- implement state management for table parameters by @OoBook in https://github.com/unusualify/modularity/commit/06e447db1d1b8d5a9a7cd2e33888944337a07f23
- integrate useTableState for improved filter management by @OoBook in https://github.com/unusualify/modularity/commit/a5053dba57d7ae91c6794438e5d3f056f7daefbd
- enhance URL generation for table actions with query parameters by @OoBook in https://github.com/unusualify/modularity/commit/11988d6cb979145a3d1b2f425206cb93ab457ab3
- add disabled prop to FilepondAvatar component by @OoBook in https://github.com/unusualify/modularity/commit/7384b85fc0cfe361022926b246b52d80af64b528
- add file upload and checkbox inputs for user configuration by @OoBook in https://github.com/unusualify/modularity/commit/ad074d4c38f3aec787dae27128cef084738c8ded
- enhance user configuration fields and labels by @OoBook in https://github.com/unusualify/modularity/commit/c87e70dd5fa7b496dc71babb98721eb087ece2db
- enhance validation rules and messages for company data by @OoBook in https://github.com/unusualify/modularity/commit/7cb7ad90f866eeb7625b95c6e7ea9b6a03bc36d3
- add protectInitialValue prop to input properties by @OoBook in https://github.com/unusualify/modularity/commit/7fb4101745e2d6e8f305303aca9535b09f0dbe42
- add protectedLastStepModel and protectInitialValue prop by @OoBook in https://github.com/unusualify/modularity/commit/512c0a0b9e62af272b79ef606908eaa56fcdc499
- add readonly functionality based on protectInitialValue by @OoBook in https://github.com/unusualify/modularity/commit/edee176bf604f0138037730ea7f0663dc040fbb9
- update button label to use translation and add readonly prop by @OoBook in https://github.com/unusualify/modularity/commit/a1fdc9af52063aef5e806edc1efbcbd9dafcd8ed
- add gutter support and improve class handling by @OoBook in https://github.com/unusualify/modularity/commit/9ccb8988d0b1da30c34a0bcb0d5fa1819d330e17
- implement Price route to SystemPricing module by @OoBook in https://github.com/unusualify/modularity/commit/43ea25ae37330a4b7b2787f987d7ff5b44f38fd8
- add new payment service and payment labels by @OoBook in https://github.com/unusualify/modularity/commit/80826d2551424201b63fded90cb320c544957d08
- add support for unformatted JSON responses with pagination options by @OoBook in https://github.com/unusualify/modularity/commit/bbdabbb063dfe9fb8fde1d8904fe3d5dd48bf8b7
- add invoice file attribute for enhanced payment details by @OoBook in https://github.com/unusualify/modularity/commit/26892015c85e05ed1cd58423ddde9c47ad373602
- add methods to check trait input availability by @OoBook in https://github.com/unusualify/modularity/commit/eaffabdcd6b8102337a10e6e313125e5853d8a27
- enhance slot functionality for action components by @OoBook in https://github.com/unusualify/modularity/commit/9325eb0400839948fc016c314bbda0d2e3c53052
- integrate HasFileponds trait and enhance morph relations by @OoBook in https://github.com/unusualify/modularity/commit/02b8526d7c329923bdb022d9983459352277444f
- add attachment handling for assignments by @OoBook in https://github.com/unusualify/modularity/commit/21493318077e91c3ed720ee22c86b741ee98a3c9
- enhance file attachment handling in hydrate method by @OoBook in https://github.com/unusualify/modularity/commit/0470e85cc8fed5fa60b078b47fd21f4bf13cea42
- update assignment component and endpoints for improved functionality by @OoBook in https://github.com/unusualify/modularity/commit/aeba14af114093153095fc3bdedff128f0554d84
- add updateInput method for enhanced input handling by @OoBook in https://github.com/unusualify/modularity/commit/783291862925219d36fcf5c4ce1a7c9415c9e72a
- add new hook for fetching input data with pagination and search capabilities by @OoBook in https://github.com/unusualify/modularity/commit/33ae30a5f8f297cafbd3bdd1d08c4056a460a1ac
- add new alert hook for managing alerts in the store by @OoBook in https://github.com/unusualify/modularity/commit/2dc15ea2e182f8c4e46817aee0aa0dd0a3fa77cd
- add 'update:input' event to input emits for enhanced input handling by @OoBook in https://github.com/unusualify/modularity/commit/fe7c71bf005f8e8408947a8569611bba891cbc6c
- add props for title divider and body padding control by @OoBook in https://github.com/unusualify/modularity/commit/d4560b8182a12a296dbf0065be4e4b2f17a801c0
- enhance input component with fetch capabilities and slot support by @OoBook in https://github.com/unusualify/modularity/commit/92aac9725f403d1b34fe57f17b778eb1ee4d2987
- add final form subtitle prop for enhanced form customization by @OoBook in https://github.com/unusualify/modularity/commit/9215b611d55d314c704a33f7386b028e66b2a1f4
- integrate connector functionality for dynamic action handling by @OoBook in https://github.com/unusualify/modularity/commit/7c2d5b7ae58ed5fa1149056cc89247f529f11ba2
- add conditional rendering for cancel and confirm buttons by @OoBook in https://github.com/unusualify/modularity/commit/d8892f46cb7c898d54c1e784d134810c12f4a820
- enhance slot rendering for dynamic content by @OoBook in https://github.com/unusualify/modularity/commit/4ce8c221d528b9da817c216e35c82333cb329c6d
- implement URL parameter handling for modal opening by @OoBook in https://github.com/unusualify/modularity/commit/df20744c867cfa9e84389fca0df5c6bbdc4df222
- add functions to remove URL parameters and update history state by @OoBook in https://github.com/unusualify/modularity/commit/7cac2e36c1be9c4b75827b2857c4ebeb1928b1b3
- add default action handler for item actions by @OoBook in https://github.com/unusualify/modularity/commit/fccfab36777fe15eac2269999a80dc713caab2d1
- add default description for action confirmation prompts by @OoBook in https://github.com/unusualify/modularity/commit/09f2130e865047d8ac01173a42dd5a6837fd4bba
- add subtitle support and improve layout for final form display by @OoBook in https://github.com/unusualify/modularity/commit/eb5eb9e679af99eccbc9051d09f58f348e409d3a
- enhance navigation actions retrieval with custom actions support by @OoBook in https://github.com/unusualify/modularity/commit/cd2628c96080ff09430631020ded885a2338cd43
- enhance table row actions with permission checks and route resolution by @OoBook in https://github.com/unusualify/modularity/commit/50b2a4569c85052d45e4fe2eaf662a51520154f9
- include exchange rate in conversion response by @OoBook in https://github.com/unusualify/modularity/commit/d6f3988dc717d70939192f25cecfaa482582e27f
- filter routes based on front route availability by @OoBook in https://github.com/unusualify/modularity/commit/76c32451969b1dbdf014b87524cf3eb3787f2cb2
- add replicating method to handle price attribute removal by @OoBook in https://github.com/unusualify/modularity/commit/38ed82e88d234671f24981924ac29651d3086dfd
- add payment_service_id column to unfy_currencies table by @OoBook in https://github.com/unusualify/modularity/commit/d3118630f50ffb511e2e2bb8d8bfaf7fc7d3c660
- enhance PaymentCurrency model with new relationships and fillable properties by @OoBook in https://github.com/unusualify/modularity/commit/367b1b01ffbd7a978d107c02bad58ec436f64f86
- enhance PaymentService model with new attributes and relationships by @OoBook in https://github.com/unusualify/modularity/commit/2402a0644cb185f60493ae875d60c9b31b3f900d
- update payment services configuration and add new services by @OoBook in https://github.com/unusualify/modularity/commit/14f4c80bc171ae53cec70ee6897dc2c303642dbd
- prepare payment_currency_payment_service table for future seeding by @OoBook in https://github.com/unusualify/modularity/commit/79f73f79dad5afd7e8f88c00bdc7feec1ad7e0f6
- enhance payment processing and response handling by @OoBook in https://github.com/unusualify/modularity/commit/58c2c185c2a23d3fb19ccc196e4c4cb9fdd48b8a
- add computed property for assignment presence and improve avatar handling by @OoBook in https://github.com/unusualify/modularity/commit/d361f5858620788a02646143edb16ad84426d7cf

### :wrench: Bug Fixes

- change orWhereHas to whereHas for translation filtering by @OoBook in https://github.com/unusualify/modularity/commit/8938bce8810439365e9a5aa89354f32cca147f5c
- update assignment status handling with enums by @OoBook in https://github.com/unusualify/modularity/commit/1ad6883a1a462c9434b54381ef491a32f19d6152
- update validation rules for user locale input by @OoBook in https://github.com/unusualify/modularity/commit/5d5cabbb7eec16bacc629a953bb29718b3cc65b8
- update assignment query methods for role-based checks by @OoBook in https://github.com/unusualify/modularity/commit/20b404b45ad78a68eb104a8f57ff4ac4bb498b5a
- restore and enhance team-pending-assignments filter by @OoBook in https://github.com/unusualify/modularity/commit/4ab60980e6e62cfb435631988df990d854245018
- update condition for table row actions to use total price by @OoBook in https://github.com/unusualify/modularity/commit/050639f85c3e680bab7c9e2a1348ecdef9e15fe7
- update price calculation to use total price instead of price including VAT by @OoBook in https://github.com/unusualify/modularity/commit/85c441c4544cce534958f103dc780a8d49807dcd
- ensure safe merging of 'with' relationships in list method by @OoBook in https://github.com/unusualify/modularity/commit/77a4a7b29d99c2c02dec91d3eb90dbea3d7c0d51
- rename and enhance lastChatMessage method for improved querying by @OoBook in https://github.com/unusualify/modularity/commit/0052e2a17bdc714c231af35743484609ebeb92df
- update column configuration for responsive design by @OoBook in https://github.com/unusualify/modularity/commit/f7d632a2d1f2cc2c225da09da799bbf6b70b6b05
- enhance guest navigation profile menu handling by @OoBook in https://github.com/unusualify/modularity/commit/89d58696e7e6c014b338dd53d0153eec6cdf2562
- update module route registration with domain configuration by @OoBook in https://github.com/unusualify/modularity/commit/5a49443e20042e51c704beadb5d3a7f79dcfbbc8
- improve request handling and data retrieval by @OoBook in https://github.com/unusualify/modularity/commit/8e4dd2334c2b7e8e8f27dcb0f9be39fc1dd3829e
- enhance modal body rendering logic by @OoBook in https://github.com/unusualify/modularity/commit/929935b042fc332286de7581431e3647d683043f
- correct unique validation rule for email field by @OoBook in https://github.com/unusualify/modularity/commit/bfa604f2aaab84e7f2b0a5e06bc4932e0124d343
- update controller imports for consistency by @OoBook in https://github.com/unusualify/modularity/commit/0b20c4afbd96010a28559dac0a3b26577deb7960
- improve locale handling in getFormFieldsFilepondsTrait method by @OoBook in https://github.com/unusualify/modularity/commit/a9f072e6e555268807c82ee6b09270ecd5dfc0b9
- improve spreadable creation and update logic by @OoBook in https://github.com/unusualify/modularity/commit/8e85360a33de6188441cf6112ee550ff91d39a4d
- improve filter handling in getRequestFilters method by @OoBook in https://github.com/unusualify/modularity/commit/f91e87c49029ed51c131f1715f432b8de514a3a0
- improve column handling and uniqueness in list method by @OoBook in https://github.com/unusualify/modularity/commit/d73f7be252aa8057a5978a0c304ffd8cc9d1cf08
- improve file information retrieval and storage path handling by @OoBook in https://github.com/unusualify/modularity/commit/7d36b42f250a2577ee24ed0d319c958f46508d75
- add 'spreadable' attribute to form draft configuration by @OoBook in https://github.com/unusualify/modularity/commit/faf64996dd60ec93a4300ac749606403e057e275
- import inject from Vue for improved modal functionality by @OoBook in https://github.com/unusualify/modularity/commit/e001222dabf91823636b042d7c7d1f58223f72b0
- conditionally render modal body description based on component state by @OoBook in https://github.com/unusualify/modularity/commit/2266603a9811dbaa4739c252ea4d387dc773d892
- enhance route parameter handling for objects and associative arrays by @OoBook in https://github.com/unusualify/modularity/commit/82e465200f231432a214f511a0bb132280706291
- adjust price saving value calculation for accurate representation by @OoBook in https://github.com/unusualify/modularity/commit/afbbdeea6b77dec5d20b69c9c40693e35243a59d
- update reference to main instance in utility methods by @OoBook in https://github.com/unusualify/modularity/commit/7345ff04717be3e026c04b1b6bd63882b3b4fed9
- refine 401 error handling for unauthenticated responses by @OoBook in https://github.com/unusualify/modularity/commit/d143fae672b5b402c04a592343c3dc87b27cdb8c
- update button text for clarity by @OoBook in https://github.com/unusualify/modularity/commit/6965e7bb457ec698b5595dd4352b92818336f98e
- improve previous route matching with enhanced error handling by @OoBook in https://github.com/unusualify/modularity/commit/78a51e064f59d3e7e0416d4054f57825d30aa1e1
- handle division by zero in VAT percentage calculation by @OoBook in https://github.com/unusualify/modularity/commit/04740cdb3edae63a4c50fd528eeed54989f679c8
- correct return statement formatting for modelValue handling by @OoBook in https://github.com/unusualify/modularity/commit/3420797ff34e293502688ca728fcafcb3f34b6df

### :recycle: Refactors

- update validation rules and add reset password form by @OoBook in https://github.com/unusualify/modularity/commit/6b001072a4150ad8d39a5df63cc224ea5dca83bc
- update validation rules for user creation and update by @OoBook in https://github.com/unusualify/modularity/commit/2742d21411c75afd28dc3496922f654e590e8b8c
- enhance layout flexibility with conditional rendering by @OoBook in https://github.com/unusualify/modularity/commit/b5664eaf4b7bddeff6f97229869057eeb6dee8af
- update prop type to support multiple data types by @OoBook in https://github.com/unusualify/modularity/commit/731764a378cbc6507d8fa5103fe636b8b61f98e8
- improve children element handling in addChildren method by @OoBook in https://github.com/unusualify/modularity/commit/0c2265afb8e5f7fdde8f50bd9f0538f56bee4979
- enhance grid section creation with improved attribute handling by @OoBook in https://github.com/unusualify/modularity/commit/95a90fe0673eab91afaf24f53b43476ed48d27fc
- streamline price calculation logic by @OoBook in https://github.com/unusualify/modularity/commit/7170ba84cbaab1a9226440a87f0923293cce7e6a
- enhance profile layout with additional class by @OoBook in https://github.com/unusualify/modularity/commit/16a2be684a0f3570a8e0da9b62f549adff1d3f8d
- simplify assignment status handling in createAssignment method by @OoBook in https://github.com/unusualify/modularity/commit/71f9e7f902cb3daaeb7cc7ad3f97ace5a0369f65
- streamline login handling and response structure by @OoBook in https://github.com/unusualify/modularity/commit/ddd3ca6e5fc7d46cd5dec25917a731697151381a
- move dashboard route in bottom line by @OoBook in https://github.com/unusualify/modularity/commit/a2e9cb18c214d61b754c5515d9ba3780b5061e07
- enhance user input configuration with additional fields by @OoBook in https://github.com/unusualify/modularity/commit/95f24e2f9f54f99efb7a60d063a9255a96eaa197
- enhance user model with company handling and email verification by @OoBook in https://github.com/unusualify/modularity/commit/6af0a1de0f43a1029d63709e340c76d16d4cf337
- streamline reset password form handling by @OoBook in https://github.com/unusualify/modularity/commit/909734cd9ca2c4ee04cd452ee899efe4f9ee0caa
- comment out hasAuthorization scope setting by @OoBook in https://github.com/unusualify/modularity/commit/8f2d97a9893ad35b599a80f5d7a51c04e91c7ded
- update scope key for assignment filtering by @OoBook in https://github.com/unusualify/modularity/commit/d79e2d70345aa064be2f0ea1fb7c5866bd12b872
- update table configuration and attributes by @OoBook in https://github.com/unusualify/modularity/commit/bc8757569f903b68d140cb88445822c2b716ee90
- simplify button class configuration by @OoBook in https://github.com/unusualify/modularity/commit/662f70c031c2ba78df0cba30f145b691e8bf25be
- add forgot password form schema and update controller by @OoBook in https://github.com/unusualify/modularity/commit/5046f3cd13b5d25b5fa1aea04d14c433092a9365
- update form attributes and button configurations by @OoBook in https://github.com/unusualify/modularity/commit/6c35aa41ba70169fe3aab078d52185636060255b
- update button attributes for improved styling by @OoBook in https://github.com/unusualify/modularity/commit/bbef4c0ac9b017b4439831f30849735bb72b6bbc
- update reset password form attributes by @OoBook in https://github.com/unusualify/modularity/commit/24e63594dced3857f677e604b5123f674435b67f
- introduce assignable scopes for enhanced query capabilities by @OoBook in https://github.com/unusualify/modularity/commit/bcb00a70ee7d719e05f21a5190ff0eb92ca1a035
- modularize assignment query scopes by @OoBook in https://github.com/unusualify/modularity/commit/219921be7e0546740bf4d886a4244937065976e4
- streamline user assignment checks and enhance query scopes by @OoBook in https://github.com/unusualify/modularity/commit/7e620bb391280af85d00f6a5984f34013e8f7c08
- streamline event handling logic by @OoBook in https://github.com/unusualify/modularity/commit/fcef063a2b6ff8a674d634ca3db37be86ab528da
- update HasChatable as Chatable by @OoBook in https://github.com/unusualify/modularity/commit/80fbd8d4acd71ca6451f8e3d0dd094475d66a542
- update `scopeHasUnreadChatMessages` to utilize the `unread` method for better readability. by @OoBook in https://github.com/unusualify/modularity/commit/3a0876326c11c582a08f4c2432a3cf68f1bd9e11
- streamline title component props and styles by @OoBook in https://github.com/unusualify/modularity/commit/32c9850ba42a11dd2e21eb98098218ad1ae87f65
- update class binding for form component by @OoBook in https://github.com/unusualify/modularity/commit/8c161873a8187e9e2d1f758fc69f5fe9c3e14f81
- add Filepond hook and props factory by @OoBook in https://github.com/unusualify/modularity/commit/c367092d47e9d9a93a33a13e7f90ec340abff28c
- enhance file upload component with improved class binding and slot handling by @OoBook in https://github.com/unusualify/modularity/commit/6b7f7f8127d0a0070af77999b20df500f1e09a4e
- rename methods for consistency and enhance save logic by @OoBook in https://github.com/unusualify/modularity/commit/684a97de7265479ef2e2388ac70ec43b9c161f01
- update logout modal design and text by @OoBook in https://github.com/unusualify/modularity/commit/1b105ad829bfc8c8eec9d7be7a2399a67c85d22b
- update modal close button logic by @OoBook in https://github.com/unusualify/modularity/commit/5159668833fb377a188a688d7a92b010e6a5d396
- update default values for banner and button text by @OoBook in https://github.com/unusualify/modularity/commit/060ad0b8abc30c21e246ba2a0597b5be27378942
- comment out company validation logic by @OoBook in https://github.com/unusualify/modularity/commit/c21546c4847c43adafad8b9679a6add4a3b5e434
- reorganize fields and update labels for clarity by @OoBook in https://github.com/unusualify/modularity/commit/4cc412a48010bafb71d4a5b0f9a76b9b03b8f930
- update profile editing functionality and improve form structure by @OoBook in https://github.com/unusualify/modularity/commit/69129da7a03eed9165164391ba6f86452ddd3d8a
- enhance form error handling and schema management by @OoBook in https://github.com/unusualify/modularity/commit/22f0d166b76480b86638380e911651ce2aaa0a3a
- update user table references for consistency by @OoBook in https://github.com/unusualify/modularity/commit/6f5384509843b43df3c8776df53edd962579aa60
- replace transition with VExpandTransition and simplify height management by @OoBook in https://github.com/unusualify/modularity/commit/927b6a03bb88acd94ab06af4a8356f0fd94a58e4
- rename metric-card class to ue-metric and adjust padding by @OoBook in https://github.com/unusualify/modularity/commit/0e3798c49e8958b058596a7624e973f6b9ba2252
- enhance relationship handling for nested keys by @OoBook in https://github.com/unusualify/modularity/commit/9acf5a9b43a32866b5b56b92d2f26818aad5daff
- rename raw_price to raw_amount and update related logic by @OoBook in https://github.com/unusualify/modularity/commit/8d08925d9112c1baa379210e953858d35fcffc81
- update table configuration and input fields by @OoBook in https://github.com/unusualify/modularity/commit/921b9b66aafe181b1d71070af2075651f93f40a7
- enhance Payment and PaymentRepository with Fileponds support by @OoBook in https://github.com/unusualify/modularity/commit/bc36711721a2c251bcc715f506f4970cc04d9b69
- enhance URL handling functions by @OoBook in https://github.com/unusualify/modularity/commit/0b1bbcf62808902d083c6bc3024463353fef18e9
- enhance select component with multiple selection and loading state by @OoBook in https://github.com/unusualify/modularity/commit/5014dcf580030721d9af85498b973e670ceb0d89
- remove unused methods and clean up code by @OoBook in https://github.com/unusualify/modularity/commit/3401ef3a5db846fca1984a6b843865172139dc1f
- enhance endpoint resolution in setDefaults method by @OoBook in https://github.com/unusualify/modularity/commit/a85b0ea6ba1ca442b34c313e0329b2f074ebc65a
- enhance list method with pagination support by @OoBook in https://github.com/unusualify/modularity/commit/ffec89fb8e169025dac2a114ee3c4d8a3b7222a4
- add resolve_route function for dynamic URL generation by @OoBook in https://github.com/unusualify/modularity/commit/6486ff312ec52e0ae119d281296c0f157906172c
- enhance input handling and add itemsPerPage property by @OoBook in https://github.com/unusualify/modularity/commit/ffc4bcfd2fcfc511d7117277bab678a681693cf2
- simplify URL generation for table actions by @OoBook in https://github.com/unusualify/modularity/commit/cb2149b757f492fffaa3221d00705b2dbe09035c
- optimize payment price retrieval methods by @OoBook in https://github.com/unusualify/modularity/commit/200fe69ef425ac155735a801dbc9f05109364080
- update roles input configuration for enhanced functionality by @OoBook in https://github.com/unusualify/modularity/commit/7c62553d929176a4ebe4b27410fa6115b448c23b
- simplify payment field retrieval by @OoBook in https://github.com/unusualify/modularity/commit/5a871e43eb0cd31fed3a0ad689b9f60c0c24e960
- remove debug response from register method by @OoBook in https://github.com/unusualify/modularity/commit/7bfcd81e82bfc34562fb082000cb95b373b6811e
- update invoice file input configuration by @OoBook in https://github.com/unusualify/modularity/commit/c685e2e43cd382ed164472ea958f767122d91c04
- optimize price calculation logic by @OoBook in https://github.com/unusualify/modularity/commit/6b6b1c45ef5345c28c8cd0f0bc95ee35a0457324
- enhance file input configuration with new properties by @OoBook in https://github.com/unusualify/modularity/commit/8a95e24656d2273dc80d2b485c0ef0e71876f448
- enhance file information retrieval and component integration by @OoBook in https://github.com/unusualify/modularity/commit/a1b6bcec25199671efa106f14d4026d0062ee7fc
- standardize property naming conventions by @OoBook in https://github.com/unusualify/modularity/commit/6a481adbe7a842e0323c86666f20789bad2e7399
- simplify class structure and remove unused methods by @OoBook in https://github.com/unusualify/modularity/commit/c02ddbf36bcdc846b0beade9387ca4d42408a103
- update property names and logic for improved clarity by @OoBook in https://github.com/unusualify/modularity/commit/e307b516f8b63f1d609eecc52a80a8ef70afeb6d
- add HasFileponds trait for enhanced file handling by @OoBook in https://github.com/unusualify/modularity/commit/4ecff56fb84b7fe1f522e20a5594381cb8e486ce
- update header logic for improved clarity by @OoBook in https://github.com/unusualify/modularity/commit/d1590daa8dbaf29c49bab4d4ca69128241d795b8
- remove unused afterDelete method and update default input handling by @OoBook in https://github.com/unusualify/modularity/commit/3fd2e40a1ff7cd469367d7225f00d3cf2b02262e
- enhance spreadable trait functionality and attribute handling by @OoBook in https://github.com/unusualify/modularity/commit/87772a8b82dac0d13ed8d73edf7a5cbf0b2edf2f
- add column configuration and spreadable saving key by @OoBook in https://github.com/unusualify/modularity/commit/7a3e665f6451e2ef049877dc80a49f32799300bd
- enhance form layout and structure in modal by @OoBook in https://github.com/unusualify/modularity/commit/ec652d55c5da027121c23610feefd83293c98f8d
- update repeater input handling and schema processing by @OoBook in https://github.com/unusualify/modularity/commit/7ef472861d3ec8f654702fc8555ae11b180716c0
- move script to script 'setup' by @OoBook in https://github.com/unusualify/modularity/commit/648d49f054c2a9b4bf1da0a3a344b4fe6648cfb3
- enhance localization and streamline component structure by @OoBook in https://github.com/unusualify/modularity/commit/b5c43ebf2059b1027ad190303b586b912b2ffab5
- streamline sidebar layout and improve user profile display by @OoBook in https://github.com/unusualify/modularity/commit/47a8bef87237f9922fcd40a7cdcb47ee1063ae68
- update payment handling logic and improve code clarity by @OoBook in https://github.com/unusualify/modularity/commit/21dc3fe74da7eab5dd4bb1a87712182547b9892d
- streamline URL query merging and add array to query string conversion by @OoBook in https://github.com/unusualify/modularity/commit/69284acfdf337c2b85531d11eb000fb55bfabf8c
- update column retrieval methods for consistency by @OoBook in https://github.com/unusualify/modularity/commit/fa07477c2c74c3fe50db22457d714b5c6b653c26
- enhance media retrieval with locale support by @OoBook in https://github.com/unusualify/modularity/commit/11ac36572166d530e078a9b2da95bac2f1042329
- optimize data retrieval and enhance currency handling by @OoBook in https://github.com/unusualify/modularity/commit/b90ac2198e763bfca939173c2c9c620a63918230
- update config access method for improved clarity by @OoBook in https://github.com/unusualify/modularity/commit/ae63bdba1d0b6f08896908767a6817e86890557c
- enhance file handling logic for nested structures by @OoBook in https://github.com/unusualify/modularity/commit/7a4fbcd98115c3e9bb350b8cab7528ebbedf860f
- enhance payment service configuration and add action buttons by @OoBook in https://github.com/unusualify/modularity/commit/a20f01abb56c2745361aff7d7d7bada28cf1d67d
- update payment services table structure for improved uniqueness and defaults by @OoBook in https://github.com/unusualify/modularity/commit/b5e15d3a8ec381a17887faa71f34f56c77d90260
- update currency handling and add foreign key constraints by @OoBook in https://github.com/unusualify/modularity/commit/cd8a09418fae442a13ebe3f91bce7dc62398073b
- update payments table structure for improved clarity and defaults by @OoBook in https://github.com/unusualify/modularity/commit/ef9e5a6b0f9f466127ce26ca739d85d82b4d41b0
- enhance Payment model structure and formatting by @OoBook in https://github.com/unusualify/modularity/commit/597bb798721f95f5d2508480445ec22ac7b928dd
- simplify template structure and enhance layout by @OoBook in https://github.com/unusualify/modularity/commit/6d97586851f016a81f78946fd459dd04c1ea443c

### :lipstick: Styling

- remove unnecessary console log from created lifecycle hook by @OoBook in https://github.com/unusualify/modularity/commit/dd7994c52708f955214b997eaa0529e5ab637cc8
- streamline SCSS structure for input assignment component by @OoBook in https://github.com/unusualify/modularity/commit/b6eeb4c059555184dcc9654312cf35a09888e870
- update button class for improved text styling by @OoBook in https://github.com/unusualify/modularity/commit/e9a3744ed24a85ebf729721ccb9e1a0a111c0184
- lint coding styles for v0.29.0 by @OoBook in https://github.com/unusualify/modularity/commit/e5127f8fefcfb5d887736ab3fc9dc2e1026de864

### :white_check_mark: Testing

- remove deprecated test_valid_company method by @OoBook in https://github.com/unusualify/modularity/commit/fe5bc31168a8b50c302885ec43af71ecd06cb8b4
- update company test to include spread payload and personal attribute by @OoBook in https://github.com/unusualify/modularity/commit/11cd0f5604fb67e4f09b3d7753ef56a3eb097f2f
- update endpoint naming and enhance modal test coverage by @OoBook in https://github.com/unusualify/modularity/commit/49843db3f7a9dcdf46e2b1a34d5741b97f76f0c8

### :package: Build

- update build artifacts for v0.29.0 by @OoBook in https://github.com/unusualify/modularity/commit/90d04e52330b9b94719d6b935d941acb6014edfe

### :beers: Other Stuff

- add verification messages for email confirmation by @OoBook in https://github.com/unusualify/modularity/commit/8cfe0fd12da124c4089e99b956242d54823b02f6
- enhance filter organization and clarity by @OoBook in https://github.com/unusualify/modularity/commit/80910f71a641005744e3407ba70440e4b2f74d8f
- add new comments by @OoBook in https://github.com/unusualify/modularity/commit/e80e1b97d646864b8cabcf4990517faa257ca824
- add Turkish translations for authorization and assignment terms by @OoBook in https://github.com/unusualify/modularity/commit/5ec4a82c8c7e8b444c9fe75c99c23a57545269c4
- comment out unused ue-form component for future reference by @OoBook in https://github.com/unusualify/modularity/commit/b394c84127c107d8df4341ca0505fb3d6c0d5bf5
- add Company entity import for user management by @OoBook in https://github.com/unusualify/modularity/commit/2ec40efd615c22fac83b514f666a74b71e2f6cb9
- enhance lastStatusAssignment scope with date filtering by @OoBook in https://github.com/unusualify/modularity/commit/564eb806f29a57eef61acdb41677f0b76903ffb8
- add subtitle prop to Metrics component for enhanced display options by @OoBook in https://github.com/unusualify/modularity/commit/9a65fc2abd43560ae862ac3d54c492201f5b79be
- remove console logs and commented code across components by @OoBook in https://github.com/unusualify/modularity/commit/b554ae2b764dd31a557031baeac0af4d9dfe9122
- remove console log for improved code clarity by @OoBook in https://github.com/unusualify/modularity/commit/19cd4928ada068fb1ee217d1218b6bfd4e0eb673

## v0.27.0 - 2025-02-24

### :rocket: Features

- change base module structure in order to make compatible modules foldering by @OoBook in https://github.com/unusualify/modularity/commit/f0f259698d3646578a82b61d609926b0b64ba9cd
- add configurable cache driver for module caching by @OoBook in https://github.com/unusualify/modularity/commit/d1d62f9e4eaeb375223fbb7cff2b1a4d25dfcf08
- add translation caching and configuration methods by @OoBook in https://github.com/unusualify/modularity/commit/861a483826991d802424cb47ad879b6a5a5f04f4
- enhance authentication redirect handling by @OoBook in https://github.com/unusualify/modularity/commit/08e3fd5df17732f3bb1207b5dea831381a8636c4
- update payments table migration operation by @OoBook in https://github.com/unusualify/modularity/commit/53cca61788b4c24bdb85e00d2ec83b5ac6a28222
- add development environment detection and vendor path methods by @OoBook in https://github.com/unusualify/modularity/commit/682b051bd28136f5a0102911626dd1ca7d4d6a16
- enhance CreateOperationCommand with flexible operation generation by @OoBook in https://github.com/unusualify/modularity/commit/458ca6fbca686f5246a1fe01d3f5b1547809e027
- add ProcessOperationsCommand for flexible Modularity operation processing by @OoBook in https://github.com/unusualify/modularity/commit/a8f4d92fb885c959afb7135c1d6d76a3064b8d8d
- add new helper functions for string formatting and code documentation by @OoBook in https://github.com/unusualify/modularity/commit/78bd09fdf33564f26f2003145c8244f537ff4203
- add Horizon configuration and layout files for job monitoring by @OoBook in https://github.com/unusualify/modularity/commit/9466c10015b9b566d4be46daf4711aaa75863fc2
- add Telescope configuration, migration, and layout files for enhanced monitoring by @OoBook in https://github.com/unusualify/modularity/commit/1c518cb25eee8ee77751044c4f5ca48c07a3c279
- add maintenance mode view for user notifications by @OoBook in https://github.com/unusualify/modularity/commit/abd60841e783dc6633ed93ed5546b3cc2ec28108
- add CleanTemporaryFilepondsScheduler command for managing temporary fileponds by @OoBook in https://github.com/unusualify/modularity/commit/cf1892838288bce9e095f5d442c19b56b22ef6a2
- add Composer root name to environment variables in CacheVersionsCommand by @OoBook in https://github.com/unusualify/modularity/commit/42c4f1c6686753fe9521fa8219401cfb8721a7ac
- enhance CreateConsoleCommand to include formatted signature documentation by @OoBook in https://github.com/unusualify/modularity/commit/cee9caaacdd5962ab945f6acd4140801bf5262ec
- refactor BaseCommand to implement PromptsForMissingInput and add namespace handling methods by @OoBook in https://github.com/unusualify/modularity/commit/07b67de264c195c4b3be0cee2c052bf50700f311
- add CreateHorizonSupervisorCommand and supervisor.stub for Horizon configuration by @OoBook in https://github.com/unusualify/modularity/commit/2f5aab8beb9a6f08707c20e4ab4af0a9564060c8
- add 'no-plain' option to ModuleMakeCommand for route creation control by @OoBook in https://github.com/unusualify/modularity/commit/b61b8f601365578ba2b7186e597d263011a0eb6f
- add EventMakeCommand and event.stub for creating Laravel events by @OoBook in https://github.com/unusualify/modularity/commit/f9d4e6abacbc80bfbd518a5247a2189cbfba2c12
- add ListenerMakeCommand and listener.stub for creating Laravel listeners by @OoBook in https://github.com/unusualify/modularity/commit/3bd3f7dd2deddd68cd9fbb95e6a054e22a139b85
- refactor ModuleServiceProvider to register module providers and streamline middleware handling by @OoBook in https://github.com/unusualify/modularity/commit/08c6e3386970bf6798488fff089b21e00217d5d1
- add broadcasting channels for modular event handling by @OoBook in https://github.com/unusualify/modularity/commit/40a7200ddbe342bfbebba8e55cc62e16c8a341d5
- enhance BaseServiceProvider with scheduled commands and modularity improvements by @OoBook in https://github.com/unusualify/modularity/commit/842ba531263352b57b9b7c35a449aadf60bae4a2
- update LaravelServiceProvider to enhance asset and config publishing by @OoBook in https://github.com/unusualify/modularity/commit/955691fff61fb9ab8f2314c3336dffe622b62bab
- add migration for notifications table by @OoBook in https://github.com/unusualify/modularity/commit/37fc3554be9b603d8005cb8d4e5960e5cc99de87
- add laravel-echo and pusher-js dependencies to enhance real-time event broadcasting by @OoBook in https://github.com/unusualify/modularity/commit/2323de4a719c248a3ce764764b342d2a2b77d474
- implement broadcasting plugin for real-time event handling by @OoBook in https://github.com/unusualify/modularity/commit/757255d2ef1cbcec3f4fbae821ab9f52a1f2f863
- add ModularitySystemPathException for production environment protection by @OoBook in https://github.com/unusualify/modularity/commit/937a12b99d91e62cf8d350a424bfb4ccd86a6592
- add methods to dynamically manage system modules path by @OoBook in https://github.com/unusualify/modularity/commit/5ce56e4b8be24a0423fc58a4d524393cf3fa5609
- enhance modularityTraitOptions() with signature generation support by @OoBook in https://github.com/unusualify/modularity/commit/fd503f16a4c3f234d214edc35b01b963d0d2ed6b
- add isModularityModule method to Module class by @OoBook in https://github.com/unusualify/modularity/commit/7b82534b352de9a80e74ccf25e5bfc66aeee38f0
- add system group configuration for Modularity modules by @OoBook in https://github.com/unusualify/modularity/commit/0f91a812c7a7ffee70a9d3965794819905aa4378
- add self option validation in BaseCommand by @OoBook in https://github.com/unusualify/modularity/commit/ae6fc29464b556f43eb09bef10c271da23d3aaf6
- :sparkles: add Singleton feature for Modularity modules by @OoBook in https://github.com/unusualify/modularity/commit/e2acc0253f4e29e4cf79ed5398f00e01dc8c1afe
- add ManageSingleton trait for singleton controller management by @OoBook in https://github.com/unusualify/modularity/commit/9d9877eff4263e18378372b3e9a9b880bde32ac2
- add ManageEvents trait for controller event handling by @OoBook in https://github.com/unusualify/modularity/commit/646925bbc0a98bff9f707a3a8cd4732bccb37b23
- add singleton detection and absolute URL support in Module methods by @OoBook in https://github.com/unusualify/modularity/commit/c18049705064aa9fb33c84c2cc0ead5653111380
- support singleton model retrieval in Repository update method by @OoBook in https://github.com/unusualify/modularity/commit/0f784ea2426a14d92beabffb8ff831444570b2d1
- enhance sidebar menu routing for singleton and non-singleton modules by @OoBook in https://github.com/unusualify/modularity/commit/622c7c08562bb658399f5b921406f765a882f648
- update schemas configuration for enhanced model publishing by @OoBook in https://github.com/unusualify/modularity/commit/0e7cd9dc53bafd6a918d99231765308c737c00af
- enhance route registration for singleton and non-singleton modules by @OoBook in https://github.com/unusualify/modularity/commit/d448b0bea1e53f9a97b00f6b322fcad6acb9a920
- improve back link generation for singleton modules by @OoBook in https://github.com/unusualify/modularity/commit/7df1a8c8a635a61bd0048fac7a44cc05e4aefd5b
- improve CreateFeatureCommand with self-module flag and table naming by @OoBook in https://github.com/unusualify/modularity/commit/046d3047f2fcec1355507df88a37053bfa3f7d11
- enhance EventMakeCommand with abstract event class selection and self-module support by @OoBook in https://github.com/unusualify/modularity/commit/689a0c6bda29740e3e5b8a29ad50ad01c654eea8
- enhance ListenerMakeCommand with self-module support and event selection by @OoBook in https://github.com/unusualify/modularity/commit/8d54e9a1e98052825cf221448eb8cd2732332184
- add migration publishing method to LaravelServiceProvider by @OoBook in https://github.com/unusualify/modularity/commit/bed5113d2aa5cb213a2a7b451fb1335a667f4963
- create priceable and modularity database migrations by @OoBook in https://github.com/unusualify/modularity/commit/92c54b8a6817ebb697ac6dfb71f81db47704b9a8
- improve MorphedByMany migration generation with dynamic model and table names by @OoBook in https://github.com/unusualify/modularity/commit/35dce9418b956c109c435bdd3cd015a26f27aa47
- add profile dialog state and methods to user store and common methods by @OoBook in https://github.com/unusualify/modularity/commit/5eb1127262c4df3075d204024285864914e5827b
- add profile dialog and avatar functionality to Main and Sidebar layouts by @OoBook in https://github.com/unusualify/modularity/commit/155968dbd48ac5a5ec75abb3ca054bdcff9d688d
- create abstract ModelEvent class for broadcasting model-related events by @OoBook in https://github.com/unusualify/modularity/commit/3c1d6a00e46947254074242fecae488aff6b1c77
- add show modal functionality to useTableModals hook by @OoBook in https://github.com/unusualify/modularity/commit/aaf653261eaf6ff4241a8196b1b15b49238d6234
- enhance useTableItemActions hook with advanced action handling and responsive display logic by @OoBook in https://github.com/unusualify/modularity/commit/3263ad7fb89fad8825b2eb9e39c1b6739e98a11e
- create RecursiveDataViewer component for nested data visualization by @OoBook in https://github.com/unusualify/modularity/commit/04d69bf2569cb33636492037020513b573b11a9e
- add show modal template to Table component by @OoBook in https://github.com/unusualify/modularity/commit/e2d7f261421a55b16eb027670c4c8243af640395
- add dynamic method call utility to UEConfig plugin by @OoBook in https://github.com/unusualify/modularity/commit/81d5af7fb8056efed1ca4e5e9e4cf34d2da51e52
- implement ModularityActivator for module status management by @OoBook in https://github.com/unusualify/modularity/commit/b51f13dca73fd3c2eba19fe43130a4d4b2537b6f
- create ModuleActivator for route status management by @OoBook in https://github.com/unusualify/modularity/commit/f1725a599f05851993c1c94ebf53e4d648fff002
- add new Permission enum cases for activity and show actions by @OoBook in https://github.com/unusualify/modularity/commit/2f8f0e1e7d7ba377806271a3ff5241763349a8ca
- enhance ModelEvent constructor with optional serialized data by @OoBook in https://github.com/unusualify/modularity/commit/f4fd31278d036957e4414cf8633fcd7138b20f24
- add DispatchEvents trait for model event handling by @OoBook in https://github.com/unusualify/modularity/commit/63f7967ce4bbfcef5c1ca7b072723ed0e5204924
- enhance Repository with activity logging and event dispatching by @OoBook in https://github.com/unusualify/modularity/commit/54ce2d2932da1bd70a938713ca544b9672893a00
- create base Listener class for dynamic notification handling by @OoBook in https://github.com/unusualify/modularity/commit/b9ef4e09bb98b811ef6eb9ab8e16dbde38f163d2
- add show and activity actions to table management by @OoBook in https://github.com/unusualify/modularity/commit/e9c9cd7eacd46cef8ddd07e6bfb9d518089d9934
- update PanelController with show and activity permission configurations by @OoBook in https://github.com/unusualify/modularity/commit/bdc0a9ad6c10555277291d8ef2f3f39f3be0b6cc
- add activity logging for translatable models by @OoBook in https://github.com/unusualify/modularity/commit/c04295f354263b69265e438bcbe377b3598966d2
- create SystemNotification module for comprehensive model event notifications by @OoBook in https://github.com/unusualify/modularity/commit/b97b0ed3bc3b0f40c1dc456e167f8045b51f9996
- create BroadcastManager for dynamic event broadcasting configuration by @OoBook in https://github.com/unusualify/modularity/commit/341d72ccba06c943e6d621ba946b00eba576157f
- add Modularity module activator configuration by @OoBook in https://github.com/unusualify/modularity/commit/2090bb23ba8340a003aeb208579ca40721781656
- add Telescope frontend assets for Vue application by @OoBook in https://github.com/unusualify/modularity/commit/aa9f75400e16168a4183345254b3c9cd13bd377c
- add file management methods to FilepondManager by @OoBook in https://github.com/unusualify/modularity/commit/82bcbe8cf6f6152717fe0ebc31a30b883a7e83e2
- publish Telescope frontend assets alongside Modularity assets by @OoBook in https://github.com/unusualify/modularity/commit/5535149a372cb4f6d8aa23c1dd9ec00cf0df34d9
- add FilepondsScheduler for automated temporary file cleanup by @OoBook in https://github.com/unusualify/modularity/commit/ae01f2d43ebf61cca3ccbc69b38d602e57ab7619
- limit maximum file uploads in Filepond configuration by @OoBook in https://github.com/unusualify/modularity/commit/19f63ee3d2425d0d7a4f26ea913fb99dcbfe389f
- add FilepondFlushCommand for manual temporary file cleanup by @OoBook in https://github.com/unusualify/modularity/commit/9c07df69c0a693cb66cc1b5efa77b384ec9c3445
- create show layout blade template for Modularity by @OoBook in https://github.com/unusualify/modularity/commit/f6dab7005397c3d04324aa5a25e0de63b6598931
- add mail configuration and enable conditional email notifications by @OoBook in https://github.com/unusualify/modularity/commit/2ab8eac72c4f3aae50320b2be91bec6bbac83a8d
- add system group configuration to SystemSetting module by @OoBook in https://github.com/unusualify/modularity/commit/e984645551bbf383abb602a46ca3ee74b8e284bc
- create CheckboxCard Vue component for enhanced input selection by @OoBook in https://github.com/unusualify/modularity/commit/efa5d643823e3a910898f5d78471c63673d7fd81
- add description support to RadioGroup component by @OoBook in https://github.com/unusualify/modularity/commit/4def929b08ab8038b00a24a52781855ab2f0808e
- enhance Checklist component with advanced configuration and rendering options by @OoBook in https://github.com/unusualify/modularity/commit/e96d8478507a2660193da8252e073887db1e756c
- enhance RadioGroup component with dynamic description rendering by @OoBook in https://github.com/unusualify/modularity/commit/3a0fce955808072318f2afcbd674ac3f889a0207
- enhance Filepond component with additional rendering and configuration options by @OoBook in https://github.com/unusualify/modularity/commit/45610d35599b5d25a60b4f0ca0ef26fa25065ed8
- enable HTML rendering for subtitle in CustomFormBase component by @OoBook in https://github.com/unusualify/modularity/commit/8d3cefd1c8ccfb5b4883f6766518898079b5f07e
- add conditional locale chip rendering in Locale component by @OoBook in https://github.com/unusualify/modularity/commit/ea2aa89dd42cc805c8d16fd609cffd4c709ba4c2
- enhance list method with improved translation and relationship handling by @OoBook in https://github.com/unusualify/modularity/commit/49875cdfea1ccfe05fa73cf20e4b8bf11f27f161
- add noEager option to skip eager loading in TabGroupHydrate by @OoBook in https://github.com/unusualify/modularity/commit/9c9693d759d25f9e79ada8d9e1e865483f625ac9
- add file type validation support in FilepondHydrate by @OoBook in https://github.com/unusualify/modularity/commit/f14fae76f134c1edbacc6c1b67e2068cdf0b5fa6
- disable activity logging when user is not authenticated by @OoBook in https://github.com/unusualify/modularity/commit/a82125757fd0247948c2c80d133fa37c7afd05b2
- add whereTranslation scope for querying translatable models by locale by @OoBook in https://github.com/unusualify/modularity/commit/338ed4cb189033a6de1ed92fcb9c3e139f68e186
- add polymorphic input type handling in ManageForm trait by @OoBook in https://github.com/unusualify/modularity/commit/5c206130c1e8acdc6ea42c41fbf8a75720ad1ec0
- add translation deletion for soft-deletable models by @OoBook in https://github.com/unusualify/modularity/commit/aa5eb5936d2acb73cc035a441a4a9ae20b3547aa
- add columnClasses prop to ConfigurableCard component by @OoBook in https://github.com/unusualify/modularity/commit/187430bebd215f011e2628a4efe5557b6f7cca23
- add support for 'title' input type in ManageForm trait by @OoBook in https://github.com/unusualify/modularity/commit/19b5ed57fe03ffa47de53fc66003167d2013be96
- add support for 'title' input type in CustomFormBase component by @OoBook in https://github.com/unusualify/modularity/commit/1201f306010127732b68973fcfe1605004b0fb14
- add id attribute to FormOld component for improved targeting by @OoBook in https://github.com/unusualify/modularity/commit/ae5103934c0ae92d0dbb082ed696f3c8a0c40d86
- add FormTabs component for dynamic multi-tab form input by @OoBook in https://github.com/unusualify/modularity/commit/b8cf16dec17283ecb501ba975ca2d24fbd3f4308
- implement TaggerHydrate and Tagger component for dynamic tag input management by @OoBook in https://github.com/unusualify/modularity/commit/adbb5450ea5690afeef5bfbd929b710539abe53a
- add custom suffix support to morph-related helper functions by @OoBook in https://github.com/unusualify/modularity/commit/3f948636fa38fd762ca217c0f933c07af1243223
- add backtrace_formatter helper function by @OoBook in https://github.com/unusualify/modularity/commit/96a6785503cc54e35ab68bd373449755cd8f4e0a
- add Authorization feature with comprehensive model and trait support by @OoBook in https://github.com/unusualify/modularity/commit/f576410cebedfb0a61ddfd47241d1a2dd86d47b6
- add default authorization configuration to ModelHelpers trait by @OoBook in https://github.com/unusualify/modularity/commit/31c66c91fa3786f180af21101c04a4de241d3aaa
- add default creator model to ModelHelpers trait by @OoBook in https://github.com/unusualify/modularity/commit/410232f6f828d82e99d09a7cde7b63933e877a56
- dynamically extend fillable attributes for trait-based models by @OoBook in https://github.com/unusualify/modularity/commit/777b58be53a7d9fe08f3b03f8655a9aa26b9ccfb
- add company-related attributes to User model by @OoBook in https://github.com/unusualify/modularity/commit/e51338cd7fe5aba3fb5ed05d7bc1cdcb2f887c37
- add schema update event emission in Form component by @OoBook in https://github.com/unusualify/modularity/commit/0db38a72b64c05707cc4c5d4a08c4fd265a0e9ff
- enhance AuthorizeHydrate with dynamic model authorization and role-based filtering by @OoBook in https://github.com/unusualify/modularity/commit/52194cc1a3a89945ae353abdd57afaeafa2ee707
- enhance filter method with advanced scope and argument parsing by @OoBook in https://github.com/unusualify/modularity/commit/8619b9a5282e56515aee52788ab7ea8e307e1ac9

### :wrench: Bug Fixes

- remove debug statements from search method by @OoBook in https://github.com/unusualify/modularity/commit/9983b5cd7d25bc111d839759081abd5f2f6ded51
- update PR template check to fetch changed files dynamically by @OoBook in https://github.com/unusualify/modularity/commit/39ef3f0197bf659b1eca70957bf3311feb04ad10
- correct module directory method call in FileActivatorTest by @OoBook in https://github.com/unusualify/modularity/commit/409a090052e2056ae111f87cb3e914f1ded489b5
- improve JSON response for login redirects by @OoBook in https://github.com/unusualify/modularity/commit/9edfd68dd2a1b2e5688a9dbb4afd728b8e8188b0
- update navigation link to point to admin dashboard by @OoBook in https://github.com/unusualify/modularity/commit/eeddc178c933187ca6908c4531356ef249b9f281
- update environment variable prefix in Vite configuration by @OoBook in https://github.com/unusualify/modularity/commit/99f960080bb8446747c0a08a3aca39078515ffc7
- improve Locale input component initialization logic by @OoBook in https://github.com/unusualify/modularity/commit/f087942a27edbafd5ac072d7693e6e88ec5594ad
- enhance Fileponds handling with locale and role support by @OoBook in https://github.com/unusualify/modularity/commit/89379de237c778b860fd8dacded9ddd33bb80661
- improve getFormUrl method with error handling and parameter correction by @OoBook in https://github.com/unusualify/modularity/commit/348aa282da969b337e4bd4cd37c00a043a5f3b43
- adjust FormSummaryItem button margin styling by @OoBook in https://github.com/unusualify/modularity/commit/04c9587dd7e2fac4c775dda4954d2b74ffda9c1a
- improve value formatting in PropertyList component by @OoBook in https://github.com/unusualify/modularity/commit/8a821fa46b312943dda52cee9a71037bb419bd57
- conditionally render StepperPreview form data section by @OoBook in https://github.com/unusualify/modularity/commit/4af8c6688798c9e2f1e4e91220b95c4cb0edff26
- simplify morph to many relations sync logic by @OoBook in https://github.com/unusualify/modularity/commit/7ce791b673f12a3536172bf73d75d26acfd350f8
- update translation languages field handling in TranslationsTrait by @OoBook in https://github.com/unusualify/modularity/commit/cf0902d9998a3d8b572f10744b7990358ba83654
- remove debug logging in StepperForm component by @OoBook in https://github.com/unusualify/modularity/commit/eee5bf246f90552e6f863ff38ec1c066a0ebddbf
- enhance StepperContent component data management and event handling by @OoBook in https://github.com/unusualify/modularity/commit/63b70070e0892ae2113ce9ee3d63165ece5a698d
- add color prop to authentication form titles and reset form by @OoBook in https://github.com/unusualify/modularity/commit/2fbadceabacf5311f815ca66aaf5bb1c0d81813c
- import USER mutation in Main.vue layout component by @OoBook in https://github.com/unusualify/modularity/commit/3c388c4c8da57e109fc51fa96336a31d9c26e89f

### :recycle: Refactors

- simplify cache clearing method by @OoBook in https://github.com/unusualify/modularity/commit/2139cdaeff6328e07436df5d6dab588745234b33
- relocate impersonation routes to web routes by @OoBook in https://github.com/unusualify/modularity/commit/3b533c298251d2a86ba2bfe52a9d50bd17dcab8e
- optimize admin user table migration operation by @OoBook in https://github.com/unusualify/modularity/commit/b10579f36e7e68bea11cc9a17037df927830e08f
- improve vendor path methods and documentation by @OoBook in https://github.com/unusualify/modularity/commit/a3e0595ca654f350690c14ddf01f355c4b963787
- update vendor path method and documentation by @OoBook in https://github.com/unusualify/modularity/commit/f92f92b492d0562b9354d1129af5f323bee96f24
- update AboutCommand with dynamic configuration and version retrieval by @OoBook in https://github.com/unusualify/modularity/commit/60dc59c749a2f20de3cbc6738d1d35292d39e51b
- clean up BaseServiceProvider configuration methods by @OoBook in https://github.com/unusualify/modularity/commit/22cfb1ddfee56387fc9a47b4ae79cc6fd8a6abb1
- update composer helper functions to use Modularity facade by @OoBook in https://github.com/unusualify/modularity/commit/547cb8605c5d8c4de88b4bc54eb545a0288a739d
- improve morph-related helper functions by @OoBook in https://github.com/unusualify/modularity/commit/30c0b7ac7f2b0a095a6acbc691b47e2a64592ed0
- improve translation and pivot table helper functions by @OoBook in https://github.com/unusualify/modularity/commit/97121a6915a7b3d4e4474e5a7035588b13233e1d
- update theme discovery functions to use File and Modularity facades by @OoBook in https://github.com/unusualify/modularity/commit/e8b059d04d42ee8a9df13b9e8d01159baebf3d73
- update morph pivot table stub to use named parameter by @OoBook in https://github.com/unusualify/modularity/commit/6b52a7e9e38a4a292f6bf7110b23f9d94fed451c
- enhance PintCommand with improved configuration and self-linting by @OoBook in https://github.com/unusualify/modularity/commit/ce499c891f0678b3bf6dd522f199165460323d31
- migrate state feature to use dynamic table configuration by @OoBook in https://github.com/unusualify/modularity/commit/8963d595393f64805881a525a1355cac3507a26d
- update BuildCommand to use Modularity facade for vendor directory by @OoBook in https://github.com/unusualify/modularity/commit/5cb244d1a16a59c3be8485b4a6fcb50ef58d3262
- modify modularity payments table operation async behavior by @OoBook in https://github.com/unusualify/modularity/commit/ed3969b8d197ea08e48307ab916ad5b591647c30
- simplify command signature definition in command.stub by @OoBook in https://github.com/unusualify/modularity/commit/12c235319a9bf57f6bcc78d0d69c504813b22d03
- remove unused boot and register methods in provider.stub by @OoBook in https://github.com/unusualify/modularity/commit/ac1f1a201b8fd8ac1d7faa0cc9dcdad775fe4695
- update navigation configuration for superadmin role by @OoBook in https://github.com/unusualify/modularity/commit/9e5e39882854b4c4da5f51b2ce59be70af871b1a
- reorganize controller traits and update import paths by @OoBook in https://github.com/unusualify/modularity/commit/50f20c764f49231e0be16144fa9591893afdcdee
- remove redundant view files from SystemPayment and SystemSetting modules by @OoBook in https://github.com/unusualify/modularity/commit/3ef64ae1f276d8e254e2353767da6dea2c8355cb
- remove commented-out module cache configuration code by @OoBook in https://github.com/unusualify/modularity/commit/d5827e14393e781bf2506c00eeafb9c5707cd21d
- update Form component and useForm hook to improve model handling by @OoBook in https://github.com/unusualify/modularity/commit/cd1334a9a118b9c64702038d1857b797de8c1066
- update trait command option shortcuts by @OoBook in https://github.com/unusualify/modularity/commit/08fd8963b59fc7b1003f780374198f12e2f63947
- remove debug logging in useTableItemActions hook by @OoBook in https://github.com/unusualify/modularity/commit/bb54800ff4d4815c976290b85ae21df56b8a05d6
- update authentication guard name to 'modularity' by @OoBook in https://github.com/unusualify/modularity/commit/a00db1bc95a2199215b7b46803f444448670d780
- remove deprecated admin routes file by @OoBook in https://github.com/unusualify/modularity/commit/675887add6f140129275aa3e53d83ea86f53a32c
- enhance BaseCommand with trait options and module name retrieval by @OoBook in https://github.com/unusualify/modularity/commit/d61e77c1022be8d9a5bf9c9be35696c173ce25c5
- improve input components with enhanced label support by @OoBook in https://github.com/unusualify/modularity/commit/8ae65bb33097d207ed79d57690725a856185217c
- enhance useForm hook with computed formItem property by @OoBook in https://github.com/unusualify/modularity/commit/0b4ba8061a4eaa75d767ebeab3c436890ef64b5b
- improve Form component top inputs with margin and formItem prop by @OoBook in https://github.com/unusualify/modularity/commit/c4863030f6b88552905b09f2e1ad8995d6bd617e
- improve migration, model, and repository commands for singleton support by @OoBook in https://github.com/unusualify/modularity/commit/67b625917987c4b9788d5e70c395e624fae53c69
- update BaseController with singleton and event management traits by @OoBook in https://github.com/unusualify/modularity/commit/f3cac172944fb16e28cd8606024469be32e14d40
- improve form data retrieval with new methods for singleton and dynamic routing by @OoBook in https://github.com/unusualify/modularity/commit/eb2d4771f69101f90952b9395b4d7f733fea42d0
- modernize RouteMakeCommand with Laravel signature and trait support by @OoBook in https://github.com/unusualify/modularity/commit/9d13275f92d56f1eebd7e291dd38875ab7786985
- modernize ModuleMakeCommand with Laravel signature and enhanced options by @OoBook in https://github.com/unusualify/modularity/commit/d225892bc4ab74eb47d09b40aad9aa7aaafdc7d2
- modernize ModelMakeCommand and RepositoryMakeCommand with Laravel signature by @OoBook in https://github.com/unusualify/modularity/commit/7c5853ee3295d6ebf4013ebfa26ba5820b874821
- update migration names by @OoBook in https://github.com/unusualify/modularity/commit/445a0e39263630dda19835c98f9907d587acaffa
- improve createDefaultMorphPivotTableFields helper function by @OoBook in https://github.com/unusualify/modularity/commit/847995e2ed564e9d3b9f9884a1615f0b0163f3ce
- update payment currency payment service migration table creation by @OoBook in https://github.com/unusualify/modularity/commit/b742caeeaba4da0b774063e64cf06c32ccb315f1
- update Modularity guard name method in payment seeders by @OoBook in https://github.com/unusualify/modularity/commit/e6295f2ed508491c838ff9b87c8d7938c8bf73db
- modify migration loading and publishing behavior by @OoBook in https://github.com/unusualify/modularity/commit/c2f229ba08c222c654b64b40569d8c34ac24b57a
- update global properties method binding in UEConfig by @OoBook in https://github.com/unusualify/modularity/commit/73445b472b9028790128d5caf79e0eb2c554f990
- add ModelHelpers trait to SystemPricing entities by @OoBook in https://github.com/unusualify/modularity/commit/92eab0638cbb88e58b03bb12e8377b6cc6cf10b5
- update CurrencyRepository namespace import by @OoBook in https://github.com/unusualify/modularity/commit/e02c7328c91db7fa00ff3db3793e0a1f7437f4a4
- use actionShowingType on item actions by @OoBook in https://github.com/unusualify/modularity/commit/e45dadc2bf80532897388034599f8717b4d19b91
- adjust row action display thresholds for better responsiveness by @OoBook in https://github.com/unusualify/modularity/commit/88617cf3e38ddaae2fa58ced3fa36981aacfb4f4
- disable broadcasting configuration by default by @OoBook in https://github.com/unusualify/modularity/commit/bba3f8c0d3d64d620b913e530b4081dcc87c6a39
- remove $call method from commonMethods utility by @OoBook in https://github.com/unusualify/modularity/commit/0d9b84dfeb478bf0c3725b3cb45f74eef25ecbcf
- update event handling in ManageEvents trait by @OoBook in https://github.com/unusualify/modularity/commit/cb4327df8225dd6e466f915d3b46ee32d2790dca
- change getTitleField to getTitleValue by @OoBook in https://github.com/unusualify/modularity/commit/2b326cb75a2b9b92960d1fb327dc4e8700fd818b
- clean up commented code and simplify action logging in BaseController by @OoBook in https://github.com/unusualify/modularity/commit/0725cb7c4cee59ed63b0279213ab5130cd8bcc7d
- update Modularity and Module classes for improved module management by @OoBook in https://github.com/unusualify/modularity/commit/058274fe7e19a913710421a7347c82445f77447e
- improve IsSingular trait with fillable attribute filtering by @OoBook in https://github.com/unusualify/modularity/commit/bda10e41671823dd65e35154c88afa09512edd3a
- enhance Filepond component with improved file handling and slot configuration by @OoBook in https://github.com/unusualify/modularity/commit/75fb4d3fcf7aa1ec8d50a5e848df4545f06c0092
- simplify useForm hook by removing debug logging by @OoBook in https://github.com/unusualify/modularity/commit/04eb1739b906a3a3ec063f0ec7005b7beaca23b3
- adjust Locale component class binding logic by @OoBook in https://github.com/unusualify/modularity/commit/acbea14785e2b2873d840858b5d79e543fad8a4c
- update scheduler configuration for Fileponds and Telescope by @OoBook in https://github.com/unusualify/modularity/commit/12c4e01d369cc37d55966e8774be84ccc1407b20
- comment out submodule-related properties in BaseController by @OoBook in https://github.com/unusualify/modularity/commit/5abf7e0e74cba87ced37933860191b24e74633e8
- update free layout blade template with @push directive by @OoBook in https://github.com/unusualify/modularity/commit/892b19ee42c631a528774be0282e0f42ccb7f0d0
- update SystemSetting module configuration and model by @OoBook in https://github.com/unusualify/modularity/commit/d4dc39c33c7b78326ab519781564cbb5e7382304
- migrate migration classes to PHP 8.1 anonymous class syntax by @OoBook in https://github.com/unusualify/modularity/commit/732368ee52a2111eb3645104be5446e98b03761a
- update stateables migration with improved morphPivotTableFields generation by @OoBook in https://github.com/unusualify/modularity/commit/c163deab63b0024b5d84f9093face31d7d8c60d9
- enhance morphTo relation handling in repositories and form management by @OoBook in https://github.com/unusualify/modularity/commit/33ef4c70fb992e9b44693ae4bf4cb09b365be645
- modify MigrationMakeCommand option for relational table generation by @OoBook in https://github.com/unusualify/modularity/commit/d1c96369d4e167fc28872adc41e4c209c813ea53
- remove debug logging from Checklist component by @OoBook in https://github.com/unusualify/modularity/commit/410fbc371d3ef7dd4da68efbbf0d3e13bb08ca42
- improve variable naming in commonMethods utility by @OoBook in https://github.com/unusualify/modularity/commit/ea3d80399be6d3d106780e6e6b912846b54860c7
- add returnObject configuration to input hydrate classes by @OoBook in https://github.com/unusualify/modularity/commit/b7d83ea5b58c1b857a737df60a5097ad1ae4d866
- remove commented-out code in getFormData utility by @OoBook in https://github.com/unusualify/modularity/commit/1a59e3005cf7cda2ba9bcbd2cca1310f428b9122
- add v-fit-grid directive to StepperPreview component by @OoBook in https://github.com/unusualify/modularity/commit/60c43968a0df2897a5668fb25619a6d7912bfd58
- improve Checklist component with enhanced group and item rendering by @OoBook in https://github.com/unusualify/modularity/commit/5e96ac56b7d47f714eb3c1d74a1246d89c533290
- improve list method with enhanced column and translation handling by @OoBook in https://github.com/unusualify/modularity/commit/8ba817a512eb98aa3ba4454cd71a603d24769b0d
- enhance HasTranslation trait with advanced translation handling by @OoBook in https://github.com/unusualify/modularity/commit/f05a507f22614310e436e9fbc2523f099abb0fed
- improve polymorphic input handling in ManageForm trait by @OoBook in https://github.com/unusualify/modularity/commit/f0c9cc26f992c03709312c68fa2de1c596f0119a
- improve ModelHelpers trait with static method and default eager loading by @OoBook in https://github.com/unusualify/modularity/commit/f6f8fa3edeb9aa4a4ffa6206ee49af01fc3861af
- improve relationship column data retrieval in BaseController by @OoBook in https://github.com/unusualify/modularity/commit/1b86bf3a72852d36e0cdef49b3c2dc0f075055e1
- improve Repeater component styling and class naming by @OoBook in https://github.com/unusualify/modularity/commit/89cd8580cfa3eb85ff90e37b7d7f7fb69b2d8165
- enhance useInput hook with flexible model value update by @OoBook in https://github.com/unusualify/modularity/commit/7bb456734431289881ebe686f84cd57d97e1fab0
- improve Title component prop handling and class generation by @OoBook in https://github.com/unusualify/modularity/commit/fce588ce59f4e7f12807679f3475f61cfc13b5e3
- improve wildcard matching and value casting by @OoBook in https://github.com/unusualify/modularity/commit/7a54d87d9dc4f68d94eed66fee44d4116bd650b1
- improve component registration and import paths for custom inputs by @OoBook in https://github.com/unusualify/modularity/commit/469fe622caafbd402514c30062fca21a64f9bcd0
- simplify BaseController show method response handling by @OoBook in https://github.com/unusualify/modularity/commit/ebfb6ebf9a2b73bfc87244461bc165c80a66649e
- simplify tags method in CoreController by @OoBook in https://github.com/unusualify/modularity/commit/e8e6a3a65dc97b4026ea1541e809820e82603de6
- improve RadioGroup input default value handling by @OoBook in https://github.com/unusualify/modularity/commit/d16b5f1423eba0b87401022ba17d994590b98f9a
- prevent repository hydration in console environment by @OoBook in https://github.com/unusualify/modularity/commit/58d88de3048959849ef22ef4021f204e9b8d9d98
- improve Form component and useForm hook state management by @OoBook in https://github.com/unusualify/modularity/commit/37c95990dccfbadb5382fb83011310366885058f
- improve form data handling and error logging in getFormData utility by @OoBook in https://github.com/unusualify/modularity/commit/179eea7561802b2278d49fd011678344c474e3ce
- improve StepperPreview and StepperFinalSummary components by @OoBook in https://github.com/unusualify/modularity/commit/1b3e1e43894733758b36a42c025f20146c74dcc8
- improve StepperForm event handling and method signatures by @OoBook in https://github.com/unusualify/modularity/commit/a567156f5e7d6c77f144a83cff69ac5e4937acda
- improve ModularityActivator module status management by @OoBook in https://github.com/unusualify/modularity/commit/7a81aa6c08b33b257df93e387cfc20937f98efa5
- improve soft delete handling in HasTranslation and IsAuthorizedable traits by @OoBook in https://github.com/unusualify/modularity/commit/6a7a73453b05e859861143083b536ba5e23a63a6
- replace IsAuthorizedable with HasCreator trait by @OoBook in https://github.com/unusualify/modularity/commit/aea3418af5b2cfa7806e5c79ecf7304bf27935e7
- simplify HasCreator trait and remove unused methods by @OoBook in https://github.com/unusualify/modularity/commit/8f9ff397cdd2b53526fb5e36e4a0093adf8034b1
- simplify Modal component structure and enhance slot usage by @OoBook in https://github.com/unusualify/modularity/commit/b720e651686900b3fcb73d277f6eaa1ffd96e6ca
- enhance HasAuthorizable trait with more flexible authorization handling by @OoBook in https://github.com/unusualify/modularity/commit/27e2a0e177e746401f5d0b58b5d174e58d11007d
- enhance HasCreator trait with custom creator saving mechanism by @OoBook in https://github.com/unusualify/modularity/commit/22ccf7ec2cad20d498e1b4aa1930f2e1a3e1a07b
- improve HasPayment trait with more robust price and payment handling by @OoBook in https://github.com/unusualify/modularity/commit/a25825c009f6dd0b0bd5dfb2156c0a7e82f7b711
- enhance HasStateable trait with initial state handling by @OoBook in https://github.com/unusualify/modularity/commit/f51905a28a44860290907896ef8e91f765171cdd
- enhance Price model with flexible payment status filtering by @OoBook in https://github.com/unusualify/modularity/commit/26fd9f7d1c35584920038a00ce64af996de24d8f
- optimize Repository list method for translatable models by @OoBook in https://github.com/unusualify/modularity/commit/0347e6b93da1251de26941d7155ce1a7aa982e16
- update StepperForm modal design and interaction by @OoBook in https://github.com/unusualify/modularity/commit/a5d46bf178b5036e3984707ef3173dcd1b24343c
- improve backtrace_formatter with robust error handling by @OoBook in https://github.com/unusualify/modularity/commit/e17e6a6ce6c25eb96e52a047dedc1f5b2ad10a17
- add InnoDB engine configuration for modularity tags table by @OoBook in https://github.com/unusualify/modularity/commit/4fa352abe77ffb6eadf0bcdccab8b2e14b636df1

### :lipstick: Styling

- remove commented code and unused configurations in ModularityProvider by @OoBook in https://github.com/unusualify/modularity/commit/4cdc2576fcf11ef9dade2594517f4092e475cf85
- add comment to getModulePath of RepositoryInterface by @OoBook in https://github.com/unusualify/modularity/commit/4d9a6fce71d360b78c5133b7930d86d5524ea64b
- arrange custom modal actions by @OoBook in https://github.com/unusualify/modularity/commit/621a6eaa49c54faa87444965175ced098c4fdc8d
- clean up commented code and remove unused table props by @OoBook in https://github.com/unusualify/modularity/commit/ab27e286619d557df4a8be6782f65996ea5c88d9
- lint coding styles for v0.27.0 by @OoBook in https://github.com/unusualify/modularity/commit/04629430ecd60f413d68856711c9c19e775576d7

### :white_check_mark: Testing

- configure module scanning for test environment by @OoBook in https://github.com/unusualify/modularity/commit/edfe46447fab0ed3635021972292c939c558dfb1
- add Spatie Permission Service Provider to TestCase by @OoBook in https://github.com/unusualify/modularity/commit/7ba1585b62ed53a9d4dee41a655fb7c6ccb317ed
- add comprehensive helper function tests for format, migration, and sources by @OoBook in https://github.com/unusualify/modularity/commit/6ee0f0b6b9701641b5c47dbd1c2802bb205591aa
- add ResizeObserver polyfill for input-image component test by @OoBook in https://github.com/unusualify/modularity/commit/88af138ca05e222b5ca76d0af317499b4177629d
- add comprehensive ModularityActivator test suite by @OoBook in https://github.com/unusualify/modularity/commit/e6a5893b9f190ffcd9e12edd4c832b96bf65e121

### :package: Build

- update build artifacts for v0.27.0 by @OoBook in https://github.com/unusualify/modularity/commit/dfee69797532ae521679059cc2fbffc600d99db5

### :beers: Other Stuff

- add command aliases for operation creation by @OoBook in https://github.com/unusualify/modularity/commit/9ebdd52ca52a29a0ae0c17f7e36b98a63230107b
- add resize-observer-polyfill for browser compatibility by @OoBook in https://github.com/unusualify/modularity/commit/a1a4b39d50a209cc859f7217ee2e123aafd6e7c1
- update default theme from 'unusual' to 'unusualify' by @OoBook in https://github.com/unusualify/modularity/commit/2223d116b1ce0d49799af598e40dc86cca0f589e
- add fallback values for Reverb broadcasting configuration by @OoBook in https://github.com/unusualify/modularity/commit/995f3c4c5b3680828f03de3796873241ec865772
- add modularity regex replacement command for blade sections by @OoBook in https://github.com/unusualify/modularity/commit/e291c95a7ecb67125485873fcf77ffbb7e96be10
- comment out additional broadcasting channel configurations by @OoBook in https://github.com/unusualify/modularity/commit/6f7c65a8954ba8237a09f95ee3c5842d212d21ca

## v0.26.1 - 2025-02-02

### :wrench: Bug Fixes

- remove debug statements from search method by @OoBook in https://github.com/unusualify/modularity/commit/4828684da7f174c06ef7b543a06d16f47441e675

## v0.26.0 - 2025-02-01

### :rocket: Features

- add authentication guard and provider configuration methods by @OoBook in https://github.com/unusualify/modularity/commit/77ebbc63b78368d4c2cf19f791d5d4f6b97455b2
- add AuthConfigurationException for robust authentication setup by @OoBook in https://github.com/unusualify/modularity/commit/d2eb26d74deb6f3d57e81043db9db9a4f6e7cc60
- add CreateOperationCommand for generating one-time operations by @OoBook in https://github.com/unusualify/modularity/commit/26a06fd16799c5ee749de41995b54487d3b8c39d
- add PublishOperationsCommand and operation stub template by @OoBook in https://github.com/unusualify/modularity/commit/83868b000b55ae57a00458a5561a9490f744f17e
- update Laravel translation package configuration by @OoBook in https://github.com/unusualify/modularity/commit/80c124ac09ba94b888111d68f2272133317af2c2
- enhance service provider with operations publishing and config management by @OoBook in https://github.com/unusualify/modularity/commit/ffbdf133227da645971d7766583893b5281d05ff
- add one-time operations for system configuration updates by @OoBook in https://github.com/unusualify/modularity/commit/6838cd1e948b55cf01693fac9e63629383b63bc8
- enable module scanning and dynamic cache configuration by @OoBook in https://github.com/unusualify/modularity/commit/7aa1e3858b4deba4f024aeb55320720c169e6afd
- enhance modules config with environment-based cache settings by @OoBook in https://github.com/unusualify/modularity/commit/086d18738ca88d75d631c712c46a0055416b3df1
- add one-time operation for updating user guard names by @OoBook in https://github.com/unusualify/modularity/commit/d5560d2e8665adc14d5e74535d43d3ea9dbc0420
- add dynamic condition evaluation for table item actions by @OoBook in https://github.com/unusualify/modularity/commit/dd99615fcf26a34cb82dc32d58df1f9a871ef8ac
- improve input hook with enhanced default value handling by @OoBook in https://github.com/unusualify/modularity/commit/9b920d5f96f410b571cbc2ef4ed293059a6a3189
- enhance form data handling with new utility functions by @OoBook in https://github.com/unusualify/modularity/commit/318b5c172145e9068f47f1a7b9e0a871480f16c4
- improve prepend schema key tracking and deletion by @OoBook in https://github.com/unusualify/modularity/commit/0834ac7d2c85d614a2d6beb9c982a40b2dd6ff52
- improve table form action handling and validation reset by @OoBook in https://github.com/unusualify/modularity/commit/e55ac219334a0e11d4533d86a9299c353c695014
- add FormActions component for dynamic form interactions by @OoBook in https://github.com/unusualify/modularity/commit/41b218e2ff411da95708446ec18c774954618ffe
- add useForm hook for comprehensive form management by @OoBook in https://github.com/unusualify/modularity/commit/03ab5e83f04d265342fd7eee34917938846ff8b2
- add stepper components for multi-step form workflow by @OoBook in https://github.com/unusualify/modularity/commit/d623f403f454db80734b1f73a47b8cd5418dcc49
- add SystemPricing module entities and repositories by @OoBook in https://github.com/unusualify/modularity/commit/11a6d0a8b9756d6fc2e9d919729038aada8713a6
- add PaymentStatus enum for payment state management by @OoBook in https://github.com/unusualify/modularity/commit/a71363a2b9727757f44caeff9e18d3fcc49b300f
- update HasPriceable trait with SystemPricing module integration by @OoBook in https://github.com/unusualify/modularity/commit/4450a3a40bf969ae65e53e0b47b351078a949f3d
- add default value for tab group input hydration by @OoBook in https://github.com/unusualify/modularity/commit/7d7ebbf8b088ab17324d49a78ee332e2179e35a0
- enhance convertTo method with rounding and decimal precision by @OoBook in https://github.com/unusualify/modularity/commit/09e08bf5e5d85edccd8fba12463c8511e6564fca
- add new format events for model clearing and item resetting by @OoBook in https://github.com/unusualify/modularity/commit/31d72dd35cf9920649a6cbc5b53ce92cf875cb88
- add conditional filtering for pending payment states by @OoBook in https://github.com/unusualify/modularity/commit/07bb77e3e228c462686b361283de7fff136c2100
- enhance HasPayment trait with comprehensive payment state management by @OoBook in https://github.com/unusualify/modularity/commit/a45cb061a42c5ba54f2c0e76818ab96971c7933f
- implement intelligent price update strategy for unpaid and paid records by @OoBook in https://github.com/unusualify/modularity/commit/0ca5f39229b5921b33ba8bd50301ef58be868db9
- implement advanced currency conversion and payment processing by @OoBook in https://github.com/unusualify/modularity/commit/a596084c75faf2aad82ce45fd9271a9bc771aeba

### :wrench: Bug Fixes

- add default return URL configuration for payment module by @OoBook in https://github.com/unusualify/modularity/commit/bb3c81736224816cab67c681e87a247abb2500e6
- add configuration for VAT pricing mode by @OoBook in https://github.com/unusualify/modularity/commit/9a85a3ec9a2fc2f1b80e4e671ae301a7f43b2b47
- add error handling for module resolution by @OoBook in https://github.com/unusualify/modularity/commit/a1ab6ae0b9681878e2f85e6a6246cf4e247c654c
- improve default values for switch input hydration by @OoBook in https://github.com/unusualify/modularity/commit/904116fac66120d329db3e91e30e9b0b933ccfa7
- modify operation file naming convention by @OoBook in https://github.com/unusualify/modularity/commit/106cfb68f7892898e35801d99d3f02c05b5d88df
- remove PaymentTrait and add debug statements for search functionality by @OoBook in https://github.com/unusualify/modularity/commit/b72f8c0989429e0bc58bdfcb05f2f629bb13751a
- refactor filterScope method for more robust field handling by @OoBook in https://github.com/unusualify/modularity/commit/94ef1be3aa75b5e29f45d30878e78f66fcda4fa7

### :recycle: Refactors

- update BaseServiceProvider with modularity configuration and auth handling by @OoBook in https://github.com/unusualify/modularity/commit/67708ab5b1b06a6eda7b0a3ef9858c9abe9f72fa
- update authentication and configuration references across controllers by @OoBook in https://github.com/unusualify/modularity/commit/04bfb7d98947c070d28bb45c1e2a8652edc14752
- update translation configuration middleware by @OoBook in https://github.com/unusualify/modularity/commit/ccdba975d7da38630d6564d3ae142b238e568724
- enhance BaseServiceProvider with robust configuration and auth handling by @OoBook in https://github.com/unusualify/modularity/commit/6f18aec58f3a535af2d9dba272c59d707b8055eb
- update BaseServiceProvider with consistent configuration methods by @OoBook in https://github.com/unusualify/modularity/commit/87343f9efff65f3a53f93c9d1b990b08668f11c3
- dynamically configure auth guard in default seeders by @OoBook in https://github.com/unusualify/modularity/commit/2831284dc1c1b8b0e46959cc5bd9f741e48027f1
- update LoginController and ImpersonateMiddleware with dynamic authentication methods by @OoBook in https://github.com/unusualify/modularity/commit/c08a8fda856740d7fcad63c3239e6c9e376de585
- rename theme and update route middleware configuration by @OoBook in https://github.com/unusualify/modularity/commit/a55ba8dcf27db9d10f5758b16401a1367aa19f9c
- update default activity log table name by @OoBook in https://github.com/unusualify/modularity/commit/b289fab8f946e7e1c585c435d89fefec295ea0f5
- update module configuration for Vite and webpack replacement by @OoBook in https://github.com/unusualify/modularity/commit/1fcd3c6c7699e3a1154f9f5790af0692a0250872
- reorganize config publishing with vendor-specific configurations by @OoBook in https://github.com/unusualify/modularity/commit/6bdb3fb1445b34629ee3dc7a4e1a04db6bbac41f
- simplify module scanning and configuration management by @OoBook in https://github.com/unusualify/modularity/commit/eb04d6cd304da5f5584d10519eb4c34c36aaddf2
- enhance useItemActions hook with dynamic action handling by @OoBook in https://github.com/unusualify/modularity/commit/9131189b6d75e08f243a9a438082abef3469ff7e
- simplify Form component with useForm hook and FormActions by @OoBook in https://github.com/unusualify/modularity/commit/d369890770189e589b68ae34128fbcfc34467d5b
- update TabGroup component with minor form component changes by @OoBook in https://github.com/unusualify/modularity/commit/7dac4e395fb9f06206763e12544fd40cb4b78a77
- modularize StepperForm component with extracted subcomponents by @OoBook in https://github.com/unusualify/modularity/commit/e54973e613d413aa15ce5bc56fac57fad18c1e1f

### :lipstick: Styling

- lint coding styles for v0.26.0 by @OoBook in https://github.com/unusualify/modularity/commit/fa0e64a217bfbd5b2404673b2f6d5e556cb6f7df

### :package: Build

- update build artifacts for v0.26.0 by @OoBook in https://github.com/unusualify/modularity/commit/32c74a1edff6338f84f5efa70303e43af3500a0c

## v0.25.0 - 2025-01-22

### :rocket: Features

- :sparkles: add published status toggle to input types configuration by @OoBook in https://github.com/unusualify/modularity/commit/15b7da07bf9317da337dc8da5315619665948092
- :sparkles: enhance form component with switch inputs for better user interaction by @OoBook in https://github.com/unusualify/modularity/commit/97bb857f83167e82de62332ce43a10d7c8576c3d

### :recycle: Refactors

- :recycle: enhance array_merge_recursive_preserve function for improved flexibility by @OoBook in https://github.com/unusualify/modularity/commit/339c1e9eeb58b7515663cf5eb1aeea208c3b704b
- :recycle: optimize translation field handling in TranslationsTrait by @OoBook in https://github.com/unusualify/modularity/commit/9cf7655eb750718d64c48570c01618f7e4eed5cd
- :recycle: streamline table order management in ManageScopes trait by @OoBook in https://github.com/unusualify/modularity/commit/43ed1f7896100150b85a37b3ee2f190d39890280

### :lipstick: Styling

- lint coding styles for v0.25.0 by @OoBook in https://github.com/unusualify/modularity/commit/3839a76ddac516d93e75e038cf4cd4061274eb8b

### :package: Build

- update build artifacts for v0.24.1 by @invalid-email-address in https://github.com/unusualify/modularity/commit/95ef52136c3617bbb83b9400afaf8cbaa923fe78
- update build artifacts for v0.25.0 by @OoBook in https://github.com/unusualify/modularity/commit/0b57543880207984eae812486a6152b1c53a37fd

## v0.24.1 - 2025-01-21

### :wrench: Bug Fixes

- :sparkles: improve mandatory item handling in Checklist component by @OoBook in https://github.com/unusualify/modularity/commit/6b4c3d3f8e798e82098a02524085ed6556efd9d8

### :package: Build

- update build artifacts for v0.24.1 by @OoBook in https://github.com/unusualify/modularity/commit/69a6988298040a6ed5e95c5a634a6becf43424aa

## v0.24.0 - 2025-01-21

### :rocket: Features

- :sparkles: add spreadable feature && spreadable vue component by @gunesbizim in https://github.com/unusualify/modularity/commit/d7183193d0ba8ea0dd971732cb1d25943a7518ac
- :sparkles: add system setting module && general route by @gunesbizim in https://github.com/unusualify/modularity/commit/7bf04f5d7a55d390ddc53de3bebc290231e7fbf3
- :sparkles: add useItemActions hook for managing item actions by @OoBook in https://github.com/unusualify/modularity/commit/46a08b6f59a66d8248f02600f70f8cfcc0457745
- :sparkles: add transition directive for enhanced element animations by @OoBook in https://github.com/unusualify/modularity/commit/6612d595844954675556249d4d42adfbd3f6abf0
- enhance input initialization in useInput hook by @OoBook in https://github.com/unusualify/modularity/commit/aa725d7c6b9c98c9b808c6122e9d51faf0bd678e
- :sparkles: enhance form action permissions in ManageForm trait by @OoBook in https://github.com/unusualify/modularity/commit/b7e5f030431c02bc4cd9b1bbc16b88349785efdf
- :sparkles: add handleScopes method for dynamic query scope handling by @OoBook in https://github.com/unusualify/modularity/commit/b1cb4817a60b25e4c043fc23bf6730edd3829ff5
- :sparkles: implement StateableTrait for enhanced state filtering by @OoBook in https://github.com/unusualify/modularity/commit/127a7d2ed80cc36382cf43a09e4c400e20a05c50
- :sparkles: enhance price handling in HasPriceable trait with new attributes by @OoBook in https://github.com/unusualify/modularity/commit/803538d7f51b6ad52a4254321f5a8e0ecd07cf9d
- :sparkles: add mandatory item handling to Checklist component by @OoBook in https://github.com/unusualify/modularity/commit/90f98973a4415d6fbcd497285c54c5087f876d58

### :wrench: Bug Fixes

- :bug: fix currency seeder && table names && migrations by @gunesbizim in https://github.com/unusualify/modularity/commit/e47b6342ba01d5d38400daf9095c87d4258a7d4c
- :bug: adjust route export formatting in add_route_to_config function by @OoBook in https://github.com/unusualify/modularity/commit/712b70b25b814708c78748a95f01141b46f1c51a
- :bug: enhance media handling in ImagesTrait for improved localization support by @OoBook in https://github.com/unusualify/modularity/commit/4033a1b485407a23f0f63f308410c50fc715c2bc
- :bug: update payment price handling in PaymentTrait to support forced updates by @OoBook in https://github.com/unusualify/modularity/commit/c7211d64eab25a77071350399177f3a1c99d4414
- :bug: correct attribute naming for base price in StepperForm component by @OoBook in https://github.com/unusualify/modularity/commit/a5e60799a5ab594a1b7abe61316662d965be1e88

### :recycle: Refactors

- :recycle: refactor Spreadable to Spread on some files by @gunesbizim in https://github.com/unusualify/modularity/commit/e1be74ffaafdc11beb1088dd1201591a7872da21
- :recycle: comment out unused files_ option in Filepond component by @OoBook in https://github.com/unusualify/modularity/commit/69a9977dbc78a1b21d1337e97a4ae46339a94db9
- :recycle: integrate useItemActions hook and clean up Form.vue component by @OoBook in https://github.com/unusualify/modularity/commit/cfe9549766b5ba5ae410afb6ba8f9b5eeab02604
- :recycle: remove commented-out debug logs in getFormData utility by @OoBook in https://github.com/unusualify/modularity/commit/51307eda6dfb6de4422dd9c30f0f188f263f6e8e
- :recycle: update Spread model and migration to use UUID morphs and rename JSON fields by @OoBook in https://github.com/unusualify/modularity/commit/b0c5a8e03c8f85f964cdfa45e7a3e05495d6daf7
- :recycle: update MigrationMakeCommand to use schema parser variable by @OoBook in https://github.com/unusualify/modularity/commit/49bde06a6614aeddaa49fe2380d6316ccf2f31c6
- :recycle: update getStateableFilterList method call in ManageTable trait by @OoBook in https://github.com/unusualify/modularity/commit/4a3b10071b1c0add7f5ea55a277d037cbc230ea7
- :recycle: improve change tracking and trigger processing in TabGroup component by @OoBook in https://github.com/unusualify/modularity/commit/42880757a406509172bb238c3537107f0afcdc1c

### :lipstick: Styling

- :lipstick: clean up unused code and comments in Repository and MethodTransformers by @OoBook in https://github.com/unusualify/modularity/commit/884311d017725971f00449ea48d03bb9b1cc2ab0
- :lipstick: clean up Model and MethodTransformers classes by removing unused code by @OoBook in https://github.com/unusualify/modularity/commit/ab48d30acfe69f0553eaedf03fea7484756a1b19
- lint coding styles for v0.24.0 by @OoBook in https://github.com/unusualify/modularity/commit/27315d06a07c2d5f619b51770b482b561321642f

### :package: Build

- update build artifacts for v0.24.0 by @OoBook in https://github.com/unusualify/modularity/commit/bf59ce14e6d51080331ea6139c706d4e2770983c

### :beers: Other Stuff

- remove jsconfig.json file from vue.vue-cli directory by @OoBook in https://github.com/unusualify/modularity/commit/b28e62664768fb867aab7f616166bd3a2da992c3

## v0.23.1 - 2025-01-03

### :wrench: Bug Fixes

- comment out default locale initialization in HasStateable trait by @OoBook in https://github.com/unusualify/modularity/commit/27a1fc298d8cc9f77a01d597b4022ee558ef68da

## v0.23.0 - 2025-01-03

### :rocket: Features

- :sparkles: feature auth success pages by @gunesbizim in https://github.com/unusualify/modularity/commit/fe748eaf3994b03c3dcdd24368e2938927f9a404
- :sparkles: add composer helper functions for package management by @OoBook in https://github.com/unusualify/modularity/commit/5a0d79aa04ef5e925ef7c4b7205a58285f0f54c0
- :sparkles: add Verbosity trait for enhanced output control by @OoBook in https://github.com/unusualify/modularity/commit/248bc5e80f8e73029f263f0efcc22b107a539430
- :sparkles: add Pretending trait for dry run functionality by @OoBook in https://github.com/unusualify/modularity/commit/5640daa9baf8c1944e7288be99f39c4ab28dfbe4
- :sparkles: add regex replacement command and support class by @OoBook in https://github.com/unusualify/modularity/commit/c6ce18d065fcd4a8ecff9c2c82a66519c4c87de1
- :sparkles: enhance CreateFeatureCommand with additional trait and component options by @OoBook in https://github.com/unusualify/modularity/commit/22ae08d5a43114913a0051d0970855925bfc946d
- :sparkles: add GenerateCommandDocsCommand for extracting Laravel console documentation by @OoBook in https://github.com/unusualify/modularity/commit/2d4ea08d651987b0f3214472e064ff0644d8d40a
- :sparkles: add user profile management to Vuex store by @OoBook in https://github.com/unusualify/modularity/commit/2a1379b71ffd7701ccbccca31660f022089e3327
- :sparkles: add ambient module to Vuex store and enhance footer script by @OoBook in https://github.com/unusualify/modularity/commit/bd799fb417fa32e9a58607110c7ae80811579d48
- :sparkles: add development mode indicator to Main.vue by @OoBook in https://github.com/unusualify/modularity/commit/722b137895eb9d5690482c92898a79c4623ec1c2
- :sparkles: improve filter method in MethodTransformers trait by @OoBook in https://github.com/unusualify/modularity/commit/72e8e0a2be8dc66f5cb87e90b5dcf11440428cd9
- :sparkles: add stateable filtering to controller traits by @OoBook in https://github.com/unusualify/modularity/commit/75c4eb60d600afb3e39a7d87d6f0dc20377f4118

### :wrench: Bug Fixes

- :bug: fix paymentService modal && cardTypeSeeder images fix by @gunesbizim in https://github.com/unusualify/modularity/commit/84f574347a3dd692dce2aadec7254e306271e168
- :bug: fix reset password controller and mailing by @gunesbizim in https://github.com/unusualify/modularity/commit/d2999688660b424babe8f77e5c226384bbb44819
- add repository context to issue close command in GitHub Actions by @OoBook in https://github.com/unusualify/modularity/commit/305c7b7fc94332e5e013816ae5d254edf4a653dc
- :bug: enhance getTopSchema filtering logic for editing and creation states by @OoBook in https://github.com/unusualify/modularity/commit/474470acbb78b9930525c42754be57221c5d8ec9
- :bug: update useTable and useTableNames to utilize editedIndex from context by @OoBook in https://github.com/unusualify/modularity/commit/8571ab36f09e012cc1a15a1829a0363966fbbf14

### :recycle: Refactors

- :sparkles: standardize vendor path retrieval across the application by @OoBook in https://github.com/unusualify/modularity/commit/5aeb351552db134d3eb8050a57f92090401c3fb6
- :recycle: add CreateInputHydrateCommand for generating input hydrate classes by @OoBook in https://github.com/unusualify/modularity/commit/7faab412ed628fdd3c848a1419c0e2e57f78904c
- :recycle: add ComposerScriptsCommand for managing modularity composer scripts by @OoBook in https://github.com/unusualify/modularity/commit/064a50465a4e55ef7465e2b3ea5d9c8bcfa6a03a
- :recycle: rename Laravel test command for consistency by @OoBook in https://github.com/unusualify/modularity/commit/19da5ad74a55e460f83e1273fd92bcbb5d4bbee2
- :recycle: clean up and organize VitePress configuration and sidebar generation by @OoBook in https://github.com/unusualify/modularity/commit/ed5687458cbae9ef121c9e7ef18162287f227f04
- :recycle: remove deprecated layout files and streamline structure by @OoBook in https://github.com/unusualify/modularity/commit/c3bc2d7ad966627eecc07bd1f800322c5ad35342
- :recycle: replace @section with @push for STORE in multiple Blade views by @OoBook in https://github.com/unusualify/modularity/commit/5652424c2eb66dc66eb62234b8347231b7c670c9
- :recycle: clean up Blade views and streamline JavaScript handling by @OoBook in https://github.com/unusualify/modularity/commit/c4bfda3b06cf004b4f1f614b17d2ab897fd9d27d
- :recycle: simplify Vuex store configuration by removing unused state and mutations by @OoBook in https://github.com/unusualify/modularity/commit/45f1ee2c7624e5b870958cfd6f8b4b649387801d
- :recycle: update Sidebar.vue to utilize Vuex getters for user and app information by @OoBook in https://github.com/unusualify/modularity/commit/4918e8a843bf639a52b516c9af3fe27b9f38e982
- :recycle: move the methods must be on repository class by @OoBook in https://github.com/unusualify/modularity/commit/d4b08423beaeb70ffcfe2c65ef34913f7fe4bfd1
- :recycle: remove unnecessary parameter passing into createNonExistantStates by @OoBook in https://github.com/unusualify/modularity/commit/8b6b1cba27ecc6b96551f9dd79b8d553c1a88da6
- :recycle: enhance state management with new stateable methods by @OoBook in https://github.com/unusualify/modularity/commit/f58faef8715eafc0acb6d2004ed78c29e5dadb9a
- :recycle: simplify authorizedable fields in migration for unusual defaults by @OoBook in https://github.com/unusualify/modularity/commit/5472de12e0ca99266cc8f7877d03be02d415298b
- :recycle: remove StateableTrait as it is no longer needed by @OoBook in https://github.com/unusualify/modularity/commit/74a3c0d718db03165a303da6131543f74539bf4b
- :recycle: update chat attribute handling in HasChatable trait by @OoBook in https://github.com/unusualify/modularity/commit/7e0250736da1a9375439acc6efab90f9ca605718
- :recycle: enhance authorization handling in IsAuthorizedable trait by @OoBook in https://github.com/unusualify/modularity/commit/6f8f869511fdfdb6213773a29a8d21e93935e0e3
- :recycle: update default requirement in ChatHydrate to -1 by @OoBook in https://github.com/unusualify/modularity/commit/d7570fc38348b395791c9c26ddbb6935055f4733
- :recycle: enhance state management in HasStateable trait by @OoBook in https://github.com/unusualify/modularity/commit/28538a2eab48d5a27cf360512adff860c2ecef85
- update Chat component to improve message handling and user profile retrieval by @OoBook in https://github.com/unusualify/modularity/commit/ea12bd4be10128301f9f4d27da18c10340e84cb6
- enhance Filepond component to support file handling by @OoBook in https://github.com/unusualify/modularity/commit/5812e312da61f11884c92851f534826487dc1f85
- improve get_installed_composer function to support dynamic path resolution by @OoBook in https://github.com/unusualify/modularity/commit/84ed28ee2ad1f92039f720bed8cde767c4d02de3

### :memo: Documentation

- :sparkles: add multiple modularity commands for enhanced functionality by @OoBook in https://github.com/unusualify/modularity/commit/95eafc28e0686b93364d599b161e2da87a039502
- :sparkles: add new guide components and index documentation by @OoBook in https://github.com/unusualify/modularity/commit/1a90eb08f74bffd2fabde6da75232f47f0994080
- :sparkles: update index and remove deprecated API examples by @OoBook in https://github.com/unusualify/modularity/commit/a2a4ed048e0dedec9e3de79ea885e2e17fd4b14f

### :lipstick: Styling

- :art: clean up commented-out code in ReplaceRegularExpressionCommand by @OoBook in https://github.com/unusualify/modularity/commit/ce8dd4a9135bbf848d99574195664cceac435c02
- lint coding styles for v0.23.0 by @OoBook in https://github.com/unusualify/modularity/commit/3457d4c7b3173b7b95464a547125d3359d69dbe4
- lint coding styles for v0.23.0 by @OoBook in https://github.com/unusualify/modularity/commit/3ca5a1adb91be08397d76eb7a61bf17f8ddc3c88

### :white_check_mark: Testing

- add ModularityTest class for comprehensive module functionality testing by @OoBook in https://github.com/unusualify/modularity/commit/199b54df3468f2543539f82625d4b5271b33d2ae
- add comprehensive tests for Modularity functionality by @OoBook in https://github.com/unusualify/modularity/commit/f484ef8f94e359ac5496fdb49d31927e21f550bd

### :package: Build

- update build artifacts for v0.23.0 by @OoBook in https://github.com/unusualify/modularity/commit/d068e547cfc0b6afd89c58bde123bfe6348f65d7

### :beers: Other Stuff

- clean up commented-out code in PR template check workflow by @OoBook in https://github.com/unusualify/modularity/commit/85d6dea9323dc718def4e2f6053464a0f3ff1d14
- :sparkles: enhance Vue process configuration with environment variables by @OoBook in https://github.com/unusualify/modularity/commit/ab197cd1c3a2d877632f52cfc8c7b134d238fe83
- update getTopSchema to include editing state in Form.vue by @OoBook in https://github.com/unusualify/modularity/commit/8ed44eb6fbaf57f074166de5fe7174bac6cdf829

## v0.22.5 - 2024-12-26

### :lipstick: Styling

- lint coding styles for v0.22.4 by @invalid-email-address in https://github.com/unusualify/modularity/commit/43ea6698dfb4001b86c3a83eefd9163c4fa9714d
- lint coding styles by @OoBook in https://github.com/unusualify/modularity/commit/bc1851f193e0e4ad4209c1518a64529168f0075e

### :green_heart: Workflow

- Comment out PHP and Vue setup steps in release workflow by @OoBook in https://github.com/unusualify/modularity/commit/de5da26f57c32586480b9359e6565b1293498633

### :beers: Other Stuff

- Add development setup instructions and git hooks for release branches by @OoBook in https://github.com/unusualify/modularity/commit/a2eb1a1ee4874233bce057ddffe94161dde9ddb1

## v0.22.4 - 2024-12-26

### :wrench: Bug Fixes

- streamline chat instance creation logic by @OoBook in https://github.com/unusualify/modularity/commit/9509159b4bf112ae6d06f5b8976fe5784145a831

### :lipstick: Styling

- lint coding styles for v0.22.3 by @invalid-email-address in https://github.com/unusualify/modularity/commit/6dcc0bef1cfb20cdcfbb95ce6df7ab44ee9009b8

## v0.22.3 - 2024-12-25

### :wrench: Bug Fixes

- create chat instance if none exists during model booting by @OoBook in https://github.com/unusualify/modularity/commit/45f76044f2e9165f8476904c41d582b87b1748c7
- update route check for profile access by @OoBook in https://github.com/unusualify/modularity/commit/59bebcb6f36053da7ee07fc6b92ae3bbc06e4535

## v0.22.2 - 2024-12-25

### :package: Build

- update build artifacts for v0.22.1 by @invalid-email-address in https://github.com/unusualify/modularity/commit/4b8ea07c4e1d8345750c6e58e60ee10a654a2989
- update build artifacts for 0.22.2 by @OoBook in https://github.com/unusualify/modularity/commit/b84febecc40bea1554c90ff8ae15a9173544816f
- update build artifacts for v0.22.2 by @invalid-email-address in https://github.com/unusualify/modularity/commit/7fc99cd4bc369bd884ff6a729fce5b1069256f54

### :green_heart: Workflow

- update GitHub Actions workflow for release process by @OoBook in https://github.com/unusualify/modularity/commit/89cc9ae3c438e2c8ae09a3650acd30d564fc2b87

## v0.22.1 - 2024-12-25

### :wrench: Bug Fixes

- update modal action visibility condition by @OoBook in https://github.com/unusualify/modularity/commit/0141eb486efc3d9594dbe1e3e1ad06067b9f665f

### :lipstick: Styling

- lint coding styles for v0.22.0 by @invalid-email-address in https://github.com/unusualify/modularity/commit/87f14a150544be2a61f158d5740d0078b0acd5aa

### :package: Build

- update build artifacts for v0.22.0 by @invalid-email-address in https://github.com/unusualify/modularity/commit/712e5bfee037c291113090912400bd2d32cd9aaf

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
