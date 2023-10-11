# Modularity TODO's

Modularity System Base Module

<!-- [![Upgrade](https://img.shields.io/badge/description-upgrade-green.svg)](https://GitHub.com/Naereen/StrapDown.js/graphs/commit-activity)
[![Bug](https://img.shields.io/badge/bug-red.svg)](https://GitHub.com/Naereen/StrapDown.js/graphs/commit-activity)
[![GitHub issues](https://badgen.net/github/issues/Naereen/Strapdown.js/)](https://GitHub.com/Naereen/StrapDown.js/issues/) -->

### Translation ✔️️

- [x] Migration
    - [x] route translation table {$route_name}Translation
- [x] Translation Model
  - [x] translation traits for model and repository
      - [x] getFormFields
        translations key on form fields
      - [x] prepareSave
      - [x] afterSave
- [ ] Translation Form
  - [x] add v-custom-input-locale
  - [ ] vuetify form-base 
    - [x] CustomFormBase.vue component to be updated
    - [ ] create localization switch for all locales at top of the form

### Media Library

- [ ] Media Traits and Repositories to be tested
- [ ] Form custom media input to be developed

### Company

- [ ] add feature sorting Company by group labels(A-B-C...) 
    - [ ] This groups is a ranges of price by that companies paid for products
        - .i.e. A groups have purchased more than 50,001 TL of services in the last 1 year
        - .i.e. B groups have purchased between 10,001 and 50,001 TL of services in the last 1 year
        - .i.e. C groups have purchased less than 10,000 of services in the last 1 year
    - [ ] add switch column feature to headers of datatable  or only badge presenter on it's column

### Localization GUI ✔️️

- [x] composer joedixon/laravel-translation adding and configuration
    - [x] adapting json files into unusualify/modularity lang/*.json files ⏳
    - [x] adapting translation group/single groundwork
    - [x] chokidar watcher for base_path('lang') files

### Timezone Integration

- [x] User timezone integration
- [ ] This timezone info to use in press release timezone at future and maybe on invoices

### Press Release Module

- [ ] Create Press Release Module
- [ ] Custom Form Page for Submit Press Release including Stepper Form, Package selection, feature details, prices and ...
- [ ] Custom Form Page for Manage Press Release including chat inputs, file adding, summary details of a package for client and admin users

### B2press Package Module

- [ ] Create Package Feature 
    - [ ] add Translation fields (description)

### Support Module

- [ ] Create Support Module
- [ ] edit page on free.blade.php
- [ ] Ticket system to be generated from admin user
- [ ] how should the file input field be?

### Announcement Module

- [ ] Create Announcement Module
- [ ] edit page on free.blade.php
- [ ] how should the recipient input field be? which options will be in this select input?

### Users Module

- [x] Create Users Module
- [x] Show Only company users, but not system users
- [ ] add company filter at future
- [ ] open a modal with clicking first column of table
    - [x] Company Information
    - [ ] Press Releases
    - [ ] Credits & Invoices

### Payment System

- [ ] TEB and Garanti Virtual POS to be added
- [ ] Paypal Integration
- [ ] iDeal Integration (%83 used in netherlands )
- [ ] Credit system for a company
    - [ ] management only for client-manager

### Invoice System

- [ ] relation to payment syste
- [ ] specify which invoice system to use

### Dashboard

- [ ] Dashboard Division
    - [ ] create a service class for rendering dashboard acc. to role
    - [ ] Admin User UI components
    - [ ] Client User UI components

### Index Page

- [ ] Table Features
    - [x] make border box table and padding&margin adjustments for Package Module
    - [x] adjustment width of embedded form of table for instance Package Module routes
    - [x] add status column formatter to table & datatable components
    - [ ] open the edit form under the table row as collapsible, inspect manage press releases page

## BUGS

- [ ] $store.commit(FORM.SET_EDITED_ITEM, fields) working on standard but not translated fields in Form.vue [![Bug](https://img.shields.io/badge/bug-red.svg)](https://github.com/unusualify/modularity/blob/main/vue/src/js/components/Form.vue#L419)

- [ ] ModalMedia fullscreen attributes [![Bug](https://img.shields.io/badge/bug-red.svg)](https://github.com/unusualify/modularity/blob/main/vue/src/js/components/modals/ModalMedia.vue)
