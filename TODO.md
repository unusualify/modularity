# CRM-BASE TODO's
CRM Base Module

### Translation ✓
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
### Localization GUI
- [x] composer joedixon/laravel-translation adding and configuration
    - [x] adapting json files into crm-base lang/*.json files ⏳
    - [x] adapting translation group/single groundwork
    - [x] chokidar watcher for base_path('lang') files

### Timezone Integration
- [x] User timezone integration
- [ ] This timezone info to use in press release timezone at future and maybe on invoices

### Press Release Module
- [ ] Create Press Release Module
- [ ] Custom Form Page for Submit Press Release including Stepper Form, Package selection, feature details, prices and ...
- [ ] Custom Form Page for Manage Press Release including chat inputs, file adding, summary details of a package for client and admin users

### Support Module
- [ ] Create Model for support model
- [ ] Ticket system to be generated from admin user

### Payment System
- [ ] TEB and Garanti Virtual POS to be added
- [ ] Paypal Integration
- [ ] iDeal Integration (%83 used in netherlands )
- [ ] Credit system for a company
    - [ ] management only for client-manager

### Invoice System
- [ ] relation to payment syste
- [ ] specify which invoice system to use
