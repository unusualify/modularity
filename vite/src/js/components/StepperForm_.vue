<template>
    <div id="app">
        <v-stepper v-model="e1" v-if="!isStepHidden">
            <v-stepper-header>
                <v-stepper-step :complete="e1 > 1" step="1">Company Information</v-stepper-step>

                <v-divider></v-divider>

                <v-stepper-step :complete="e1 > 2" step="2">Directors</v-stepper-step>

                <v-divider></v-divider>

                <v-stepper-step step="3">Shareholders</v-stepper-step>
            </v-stepper-header>

            <v-stepper-items>
                <v-stepper-content step="1">
                    <form data-vv-scope="form1">
                        <v-row>

                            <v-col>
                                <v-alert icon="account_circle" type="info" text>
                                    CONTACT INFO
                                </v-alert>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="3">
                                <v-text-field v-model="Contact_FirstName" label="First Name"
                                    :error-messages="errors.collect('Contact_FirstName')" v-validate="'required'"
                                    data-vv-name="Contact_FirstName" required data-vv-scope="form1"
                                    data-vv-as='First Name'>
                                </v-text-field>
                            </v-col>

                            <v-col cols="3">
                                <v-text-field v-model="Contact_LastName" label="Last Name"
                                    :error-messages="errors.collect('Contact_LastName')" v-validate="'required'"
                                    data-vv-name="Contact_LastName" required data-vv-scope="form1"
                                    data-vv-as='Last Name'>
                                </v-text-field>

                            </v-col>
                        </v-row>

                        <v-row>
                            <v-col cols="3">
                                <v-text-field v-model="Contact_Email" label="Email"
                                    :error-messages="errors.collect('Contact_Email')" v-validate="'required|email'"
                                    data-vv-name="Contact_Email" required data-vv-scope="form1"
                                    data-vv-as='Contact Email'>
                                </v-text-field>
                            </v-col>
                            <v-col cols="3">
                                <v-text-field v-model="Contact_Phone" label="Contact Number"
                                    :error-messages="errors.collect('Contact_Phone')" v-validate="'required'"
                                    data-vv-name="Contact_Phone" required data-vv-scope="form1"
                                    data-vv-as='Contact Phone'>
                                </v-text-field>
                            </v-col>
                        </v-row>

                        <v-row>

                            <v-col>
                                <v-alert icon="card_travel" type="info" text>
                                    COMPANY INFO
                                </v-alert>
                            </v-col>
                        </v-row>

                        <v-row>
                            <v-col cols="3">
                                <v-text-field v-model="Proposed_Company_Name_I" label="Proposed Company Name"
                                    :error-messages="errors.collect('Proposed_Company_Name_I')"
                                    v-validate="'required'" data-vv-name="Proposed_Company_Name_I" required
                                    data-vv-scope="form1" data-vv-as='Proposed Company Name'>
                                </v-text-field>
                            </v-col>
                            <v-col cols="3">

                                <v-menu v-model="menu2" :close-on-content-click="false" :nudge-right="40"
                                    transition="scale-transition" offset-y min-width="290px">
                                    <template v-slot:activator="{ on }">
                                        <v-text-field v-model="Financial_Year_End" label="Financial Year End"
                                            prepend-icon="event" readonly v-on="on"
                                            :error-messages="errors.collect('Financial_Year_End')"
                                            v-validate="'required'" data-vv-name="Financial_Year_End" required
                                            data-vv-scope="form1" :value="computedDateFormattedMomentjs"
                                            data-vv-as='Financial Year End'></v-text-field>
                                    </template>
                                    <v-date-picker v-model="Financial_Year_End" @input="menu2 = false">
                                    </v-date-picker>
                                </v-menu>

                                    <!-- <v-text-field 
                                        v-model="Financial_Year_End" label="Financial Year End" 
                                        :error-messages="errors.collect('Financial_Year_End')" 
                                        v-validate="'required'" 
                                        data-vv-name="Financial_Year_End" 
                                        required 
                                        data-vv-scope="form1"
                                        prepend-icon="date_range"
                                        :value="Financial_Year_End"
                                        slot="activator"> -->
                                    <!-- <v-menu>
                                    <v-text-field 
                                        v-model="Financial_Year_End" label="Financial Year End" 
                                        :error-messages="errors.collect('Financial_Year_End')" 
                                        v-validate="'required'" 
                                        data-vv-name="Financial_Year_End" 
                                        required 
                                        data-vv-scope="form1"
                                        prepend-icon="date_range"
                                        :value="Financial_Year_End"
                                        slot="activator">
                                        
                                    </v-text-field>
                                    <v-date-picker v-model="Financial_Year_End"></v-date-picker>
                                    </v-menu> -->
                            </v-col>
                        </v-row>

                        <v-row>
                            <v-col cols="3">

                                <v-text-field v-model="Principal_Business_Activity_I"
                                    label="Principal Business Activity I"
                                    :error-messages="errors.collect('Principal_Business_Activity_I')"
                                    v-validate="'required'" data-vv-name="Principal_Business_Activity_I" required
                                    data-vv-scope="form2" data-vv-as='Principal Business Activity I'>
                                </v-text-field>

                            </v-col>
                            <v-col cols="3">
                                <v-text-field v-model="Principal_Business_Activity_II"
                                    label="Principal Business Activity II"
                                    :error-messages="errors.collect('Principal_Business_Activity_II')"
                                    v-validate="'required'" data-vv-name="Principal_Business_Activity_II" required
                                    data-vv-scope="form2" data-vv-as='Principal Business Activity II'>
                                </v-text-field>
                            </v-col>
                        </v-row>

                        <v-row>
                            <v-col cols="3">

                                <v-select :items="currency_options" v-model="Paid_Up_Capital_Currency"
                                    label="Paid Up Capital Currency"
                                    :error-messages="errors.collect('Paid_Up_Capital_Currency')"
                                    v-validate="'required'" data-vv-name="Paid_Up_Capital_Currency" required
                                    data-vv-scope="form1" data-vv-as='Paid Up Capital Currency'>
                                </v-select>

                            </v-col>
                            <v-col cols="3">
                                <v-text-field v-model="Paid_Up_Capital_Amount" label="Paid Up Capital Amount"
                                    :error-messages="errors.collect('Paid_Up_Capital_Amount')"
                                    v-validate="'required'" data-vv-name="Paid_Up_Capital_Amount" required
                                    data-vv-scope="form1" data-vv-as='Paid Up Capital Amount'>
                                </v-text-field>
                            </v-col>
                        </v-row>


                        <v-row>
                            <v-col cols="3">

                                <v-text-field v-model="Total_Number_of_Shares" label="Total Number of Shares"
                                    :error-messages="errors.collect('Total_Number_of_Shares')"
                                    v-validate="'required'" data-vv-name="Total_Number_of_Shares" required
                                    data-vv-scope="form2" data-vv-as='Total Number of Shares'>
                                </v-text-field>

                            </v-col>
                            <v-col cols="3">
                                <v-text-field v-model="Registered_Office_Address" label="Registered Office Address"
                                    :error-messages="errors.collect('Registered_Office_Address')"
                                    v-validate="'required'" data-vv-name="Registered_Office_Address" required
                                    data-vv-scope="form2" data-vv-as='Registered Office Address'>
                                </v-text-field>
                            </v-col>
                        </v-row>

                        <v-btn color="primary"
                            @click.native="stepContinue('form1')"
                            :disabled="errors.any()"
                            >
                            <v-icon left>skip_next</v-icon>
                            Continue
                        </v-btn>
                    </form>

                </v-stepper-content>

                <v-stepper-content step="2">
                    <form data-vv-scope="form2">

                        <div v-for="(director, index) in directors">
                            <v-row>

                                <v-col>
                                    <v-alert icon="person" type="info" text>
                                        Director {{ index + 1 }}
                                    </v-alert>
                                </v-col>
                            </v-row>


                            <v-row>
                                <v-col cols="3">
                                    <v-text-field v-model="directors[index].Name" label="Name"
                                        :error-messages="errors.collect(`directors_${index}_Name`)"
                                        v-validate="'required'" :data-vv-name="`directors_${index}_Name`"
                                        data-vv-as='Name' required data-vv-scope="form2" name="directors[][Name]">
                                    </v-text-field>
                                    </v-text-field>
                                </v-col>
                                <v-col cols="3" v-if="isCorpShow">

                                    <v-text-field v-model="directors[index].ID" label="Registration Number"
                                        :error-messages="errors.collect(`directors_${index}_ID`)"
                                        v-validate="'required'" data-vv-as='Registration Number'
                                        :data-vv-name="`directors_${index}_ID`" required data-vv-scope="form2"
                                        name="directors[][ID]">
                                    </v-text-field>
                                </v-col>

                                <v-col cols="3">
                                    <v-text-field v-model="directors[index].ID" label="ID"
                                        :error-messages="errors.collect(`directors_${index}_ID`)"
                                        v-validate="'required'" data-vv-as='ID'
                                        :data-vv-name="`directors_${index}_ID`" required data-vv-scope="form2"
                                        name="directors[][ID]">
                                    </v-text-field>
                                </v-col>



                                <v-col cols="3">
                                    <v-menu v-model="directors[index].Menu" :close-on-content-click="false"
                                        :nudge-right="40" transition="scale-transition" offset-y min-width="290px">
                                        <template v-slot:activator="{ on }">
                                            <v-text-field v-model="directors[index].DOB" label="DOB"
                                                prepend-icon="event" readonly v-on="on"
                                                :error-messages="errors.collect(`directors_${index}_DOB`)"
                                                v-validate="'required'" :data-vv-name="`directors_${index}_DOB`"
                                                data-vv-as='DOB' required data-vv-scope="form2"></v-text-field>
                                        </template>
                                        <v-date-picker v-model="directors[index].DOB"
                                            @input="directors[index].Menu = false"></v-date-picker>
                                    </v-menu>
                                </v-col>


                                <v-col cols="3">
                                    <v-select :items="nationality_options" name="directors[][Nationality]"
                                        v-model="directors[index].Nationality" label="Nationality"
                                        :error-messages="errors.collect(`directors_${index}_Nationality`)"
                                        v-validate="'required'" :data-vv-name="`directors_${index}_Nationality`"
                                        data-vv-as='Nationality' data-vv-scope="form2">
                                    </v-select>
                                </v-col>

                            </v-row>

                            <v-row>
                                <v-col cols="3">
                                    <v-text-field v-model="directors[index].Email" label="Email"
                                        :error-messages="errors.collect(`directors_${index}_Email`)"
                                        v-validate="'required|email'" :data-vv-name="`directors_${index}_Email`"
                                        data-vv-as='Email' required data-vv-scope="form2" name="directors[][Email]">
                                    </v-text-field>
                                </v-col>
                                <v-col cols="3">
                                    <v-text-field v-model="directors[index].Phone" label="Contact Number"
                                        :error-messages="errors.collect(`directors_${index}_Phone`)"
                                        v-validate="'required'" :data-vv-name="`directors_${index}_Phone`"
                                        data-vv-as='Contact Number' required data-vv-scope="form2"
                                        name="directors[][Phone]">
                                    </v-text-field>
                                </v-col>
                                <v-col cols="6">
                                    <v-text-field v-model="directors[index].Address" label="Address"
                                        :error-messages="errors.collect(`directors_${index}_Address`)"
                                        v-validate="'required'" :data-vv-name="`directors_${index}_Address`"
                                        data-vv-as='Address' required data-vv-scope="form2"
                                        name="directors[][Address]">
                                    </v-text-field>
                                </v-col>
                            </v-row>



                            <v-row v-if="index!=0">
                                <v-col>
                                    <v-btn @click="removeDirector(index)">Remove</v-btn>
                                </v-col>
                            </v-row>
                        </div>

                        <v-btn
                            color="primary"
                            @click="stepContinue('form2')"
                            :disabled="errors.any()"
                            >

                            <!-- <v-btn
                                color="primary"
                                @click="goNextStep()"                                    
                                > -->
                            <v-icon left>skip_next</v-icon> Continue
                        </v-btn>
                        <v-btn @click="addNewDirector">
                            <v-icon left> person_add</v-icon> Add
                        </v-btn>
                        <v-btn text @click="goBack()">
                            <v-icon left>skip_previous</v-icon> Back
                        </v-btn>
                    </form>
                </v-stepper-content>

                <v-stepper-content step="3">
                    <form data-vv-scope="form3">

                        <div v-for="(shareholder, index) in shareholders">
                            <v-row>

                                <v-col>
                                    <v-alert icon="person" type="info" text>
                                        SHAREHOLDER {{ index + 1 }}
                                    </v-alert>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="3">
                                    <v-select 
                                        :items="shareholder_options"
                                        name="shareholders[][Type]"
                                        v-model="shareholders[index].Type" 
                                        label="Type" 
                                        :error-messages="errors.collect(`shareholders_${index}_Type`)" 
                                        v-validate="'required'" 
                                        :data-vv-name="`shareholders_${index}_Type`"
                                        data-vv-as = 'Type'  
                                        data-vv-scope="form3"
                                        @input="onValueChange($event,shareholders[index].ID)" 
                                        required
                                        >
                                    </v-select>

                                </v-col>

                            </v-row>
                            <v-row>
                                <v-col cols="3">
                                    <v-text-field 
                                        v-model="shareholders[index].Name" 
                                        label="Name"
                                        :error-messages="errors.collect(`shareholders_${index}_Name`)"
                                        v-validate="'required'" 
                                        :data-vv-name="`shareholders_${index}_Name`"
                                        data-vv-as='Name' 
                                        required 
                                        data-vv-scope="form3"
                                        name="shareholders[][Name]"
                                        >
                                    </v-text-field>
                                </v-col>
                                <v-col cols="3" v-if="shareholders[index].isCorpShow">

                                    <v-text-field 
                                        v-model="shareholders[index].ID" 
                                        label="Registration Number"
                                        :error-messages="errors.collect(`shareholders_${index}_ID`)"
                                        v-validate="'required'" 
                                        data-vv-as='Registration Number'
                                        :data-vv-name="`shareholders_${index}_ID`" 
                                        required 
                                        data-vv-scope="form3"
                                        name="shareholders[][ID]">
                                    </v-text-field>
                                </v-col>

                                <v-col cols="3" v-else>
                                    <v-text-field 
                                        v-model="shareholders[index].ID" 
                                        label="ID"
                                        :error-messages="errors.collect(`shareholders_${index}_ID`)"
                                        v-validate="'required'" data-vv-as='ID'
                                        :data-vv-name="`shareholders_${index}_ID`" required data-vv-scope="form3"
                                        name="shareholders[][ID]">
                                    </v-text-field>
                                </v-col>


                                <v-col cols="3" v-if="shareholders[index].isCorpShow">
                                    <v-menu v-model="shareholders[index].Menu" :close-on-content-click="false"
                                        :nudge-right="40" transition="scale-transition" offset-y min-width="290px">
                                        <template v-slot:activator="{ on }">
                                            <v-text-field v-model="shareholders[index].DOB"
                                                label="Date of Incorporation" prepend-icon="event" readonly
                                                v-on="on"
                                                :error-messages="errors.collect(`shareholders_${index}_DOB`)"
                                                v-validate="'required'" data-vv-as='Country of Incorporation'
                                                :data-vv-name="`shareholders_${index}_DOB`" required
                                                data-vv-scope="form2"></v-text-field>
                                        </template>
                                        <v-date-picker v-model="shareholders[index].DOB"
                                            @input="shareholders[index].Menu = false"></v-date-picker>
                                    </v-menu>
                                </v-col>
                                <v-col cols="3" v-else>
                                    <v-menu v-model="shareholders[index].Menu" :close-on-content-click="false"
                                        :nudge-right="40" transition="scale-transition" offset-y min-width="290px">
                                        <template v-slot:activator="{ on }">
                                            <v-text-field v-model="shareholders[index].DOB" label="DOB"
                                                prepend-icon="event" readonly v-on="on"
                                                :error-messages="errors.collect(`shareholders_${index}_DOB`)"
                                                v-validate="'required'" :data-vv-name="`shareholders_${index}_DOB`"
                                                data-vv-as='DOB' required data-vv-scope="form2"></v-text-field>
                                        </template>
                                        <v-date-picker v-model="shareholders[index].DOB"
                                            @input="shareholders[index].Menu = false"></v-date-picker>
                                    </v-menu>
                                </v-col>

                                <v-col cols="3" v-if="shareholders[index].isCorpShow">
                                    <v-select :items="country_options" name="shareholders[][Nationality]"
                                        v-model="shareholders[index].Nationality" label="Country of Incorporation"
                                        :error-messages="errors.collect(`shareholders_${index}_Nationality`)"
                                        v-validate="'required'" :data-vv-name="`shareholders_${index}_Nationality`"
                                        data-vv-as='Country of Incorporation' data-vv-scope="form3">
                                    </v-select>

                                </v-col>
                                <v-col cols="3" v-else>
                                    <v-select :items="nationality_options" name="shareholders[][Nationality]"
                                        v-model="shareholders[index].Nationality" label="Nationality"
                                        :error-messages="errors.collect(`shareholders_${index}_Nationality`)"
                                        v-validate="'required'" :data-vv-name="`shareholders_${index}_Nationality`"
                                        data-vv-as='Nationality' data-vv-scope="form3">
                                    </v-select>
                                </v-col>

                            </v-row>

                            <v-row>
                                <v-col cols="3">
                                    <v-text-field v-model="shareholders[index].Email" label="Email"
                                        :error-messages="errors.collect(`shareholders_${index}_Email`)"
                                        v-validate="'required|email'" :data-vv-name="`shareholders_${index}_Email`"
                                        data-vv-as='Email' required data-vv-scope="form3"
                                        name="shareholders[][Email]">
                                    </v-text-field>
                                </v-col>
                                <v-col cols="3">
                                    <v-text-field v-model="shareholders[index].Phone" label="Contact Number"
                                        :error-messages="errors.collect(`shareholders_${index}_Phone`)"
                                        v-validate="'required'" :data-vv-name="`shareholders_${index}_Phone`"
                                        data-vv-as='Contact Number' required data-vv-scope="form3"
                                        name="shareholders[][Phone]">
                                    </v-text-field>
                                </v-col>
                                <v-col cols="3">
                                    <v-text-field v-model="shareholders[index].Address" label="Address"
                                        :error-messages="errors.collect(`shareholders_${index}_Address`)"
                                        v-validate="'required'" :data-vv-name="`shareholders_${index}_Address`"
                                        data-vv-as='Address' required data-vv-scope="form3"
                                        name="shareholders[][Address]">
                                    </v-text-field>
                                </v-col>
                                <v-col cols="3">
                                    <v-text-field v-model="shareholders[index].Shares" label="Shares"
                                        :error-messages="errors.collect(`shareholders_${index}_Shares`)"
                                        v-validate="'required'" :data-vv-name="`shareholders_${index}_Shares`"
                                        data-vv-as='Shares' required data-vv-scope="form3"
                                        name="shareholders[][Shares]">
                                    </v-text-field>
                                </v-col>

                            </v-row>


                            <v-row v-if="shareholders[index].isCorpShow">
                                <v-col cols="3">
                                    <v-text-field v-model="shareholders[index].Corporate_Representative_Name"
                                        label="Corporate Representative Name"
                                        :error-messages="errors.collect(`shareholders_${index}_Corporate_Representative_Name`)"
                                        v-validate="'required'" name="shareholders[][Corporate_Representative_Name]"
                                        :data-vv-name="`shareholders_${index}_Corporate_Representative_Name`"
                                        data-vv-as='Corporate Representative Name' required data-vv-scope="form3">
                                    </v-text-field>
                                </v-col>
                                <v-col cols="3">
                                    <v-text-field v-model="shareholders[index].Corporate_Representative_Address"
                                        label="Corporate Representative Address"
                                        :error-messages="errors.collect(`shareholders_${index}_Corporate_Representative_Address`)"
                                        v-validate="'required'"
                                        name="shareholders[][Corporate_Representative_Address]"
                                        data-vv-as='Corporate Representative Name'
                                        :data-vv-name="`shareholders_${index}_Corporate_Representative_Address`"
                                        required data-vv-scope="form3">
                                    </v-text-field>
                                </v-col>


                            </v-row>

                            <v-row v-if="index!=0">
                                <v-col>
                                    <v-btn @click="removeShareholder(index)">Remove</v-btn>
                                </v-col>
                            </v-row>
                        </div>


                        <v-btn color="primary"
                            click|%3D%26%2334%3BstepContinue(%26%2339%3Bform3%26%2339%3B)%26%2334%3B>

                            >
                        <!-- <v-btn
                            color="primary"
                                @click="goNextStep()"
                                    
                                    > -->

                            Submit <v-icon right>send</v-icon>
                        </v-btn>
                        <v-btn @click="addNewShareholder">
                            <v-icon left>person_add</v-icon> Add
                        </v-btn>
                        <v-btn text @click="goBack()">
                            <v-icon left>skip_previous</v-icon> Back
                        </v-btn>
                    </form>
                </v-stepper-content>
            </v-stepper-items>
        </v-stepper>

        <v-alert icon="cloud_upload" type="info" v-if="!isLoading">
            {{ responsefromServer }}
        </v-alert>
    </div>
</template>

<script>

export default {

    el: '#app',
    vuetify: new Vuetify(),
    $_veeValidate: {
        validator: 'new',
    },
    data() {
        return {
            e1: 0,
            Contact_FirstName: '',
            Contact_LastName: '',
            Contact_Phone: '',
            Contact_Email: '',
            Proposed_Company_Name_I: '',
            Principal_Business_Activity_I: '',
            Principal_Business_Activity_II:'',
            Registered_Office_Address: '',
            Paid_Up_Capital_Amount: '',
            Paid_Up_Capital_Currency: '',
            Total_Number_of_Shares: '',
            Financial_Year_End:'',
            currency_options: ["SGD SINGAPORE, DOLLARS", "AFN AFGHANISTAN, AFGHANIS", "ALL ALBANIAN, LEK", "DZD ALGERIA, DINARS", "AOA ANGOLA, KWANZA", "ARS ARGENTINA, PESOS", "AMD ARMENIA, DRAMS", "AWG ARUBAN, FLORIN", "AUD AUSTRALIA, DOLLARS", "AZN AZERBAIJAN, NEW MANATS", "BSD BAHAMAS, DOLLARS", "BHD BAHRAIN, DINARS", "BDT BANGLADESH, TAKA", "BBD BARBADOS, DOLLARS", "BYR BELARUS, RUBLES", "BZD BELIZE, DOLLARS", "BMD BERMUDA, DOLLARS", "BTN BHUTAN, NGULTRUM", "VEF BOLIVAR FUERTE", "BOV BOLIVIAN, MVDOL", "BOB BOLIVIANO", "XBA BOND MARKETS UNITS EUROPEAN COMPOSITE UNIT (EURCO)", "BAM BOSNIA AND HERZEGOVINA, CONVERTIBLE MARK", "BWP BOTSWANA, PULAS", "BRL BRAZIL, REAL", "BND BRUNEI DARUSSALAM, DOLLARS", "BGN BULGARIAN,LEV", "BIF BURUNDI, FRANCS", "KHR CAMBODIA, RIELS", "CAD CANADA, DOLLARS", "CVE CAPE VERDE, ESCUDOS", "KYD CAYMAN ISLANDS, DOLLARS", "GHS CEDI", "CLP CHILE, PESOS", "CNY CHINA, YUAN RENMINBI", "COP COLOMBIA, PESOS", "XAF COMMUNAUTE FINANCIERE AFRICAINE BEAC, FRANCS", "XOF COMMUNAUTE FINANCIERE AFRICAINE FRANCS BCEAO, FRANCS", "KMF COMOROS, FRANCS", "XPF COMPTOIRS FRANCAIS DU PACIFIQUE FRANCS", "CDF CONGOLESE, FRANCS", "CRC COSTA RICA, COLONES", "HRK CROATIA, KUNA", "CUP CUBA, PESOS", "CYP CYPRUS, POUNDS", "CZK CZECH, KORUNA", "DKK DANISH, KRONE", "DJF DJIBOUTI, FRANCS", "DOP DOMINICAN REPUBLIC, PESOS", "XCD EAST CARIBBEAN DOLLARS", "EGP EGYPT, POUNDS", "SVC EL SALVADOR, COLONES", "ERN ERITREA, NAKFA", "EEK ESTONIA, KROONI", "ETB ETHIOPIA, BIRR", "EUR EURO MEMBER COUNTRIES, EURO", "XBB EUROPEAN MONETARY UNIT", "XBC EUROPEAN UNIT OF ACCOUNT", "XBD EUROPEAN UNIT OF ACCOUNT", "FKP FALKLAND ISLANDS (MALVINAS), POUNDS", "FJD FIJI, DOLLARS", "GMD GAMBIA, DALASI", "GEL GEORGIA, LARI", "GHC GHANA, CEDIS", "GIP GIBRALTAR, POUNDS", "XAU GOLD, OUNCES", "GTQ GUATEMALA, QUETZALES", "GGP GUERNSEY, POUNDS", "GNF GUINEA, FRANCS", "GYD GUYANA, DOLLARS", "HTG HAITI, GOURDES", "HNL HONDURAS, LEMPIRAS", "HKD HONG KONG, DOLLARS", "HUF HUNGARY, FORINT", "ISK ICELANDIC, KRONA", "INR INDIA, RUPEES", "IDR INDONESIA, RUPIAHS", "XDR INTERNATIONAL MONETARY FUND (IMF), SPECIAL DRAWING RIGHTS", "IRR IRAN, RIALS", "IQD IRAQ, DINARS", "IMP ISLE OF MAN, POUNDS", "ILS ISRAEL, NEW SHEKELS", "JMD JAMAICA, DOLLARS", "JPY JAPAN, YEN", "JEP JERSEY, POUNDS", "JOD JORDAN, DINARS", "KZT KAZAKHSTAN, TENGE", "KES KENYA, SHILLINGS", "KRI KIRIBATI DOLLAR", "KPW KOREA (NORTH), WON", "KRW KOREA (SOUTH), WON", "KWD KUWAIT, DINARS", "KGS KYRGYZSTAN, SOMS", "LAK LAOS, KIPS", "LVL LATVIAN, LATS", "LBP LEBANON, POUNDS", "LSL LESOTHO, LOTI", "LRD LIBERIA, DOLLARS", "LYD LIBYA, DINARS", "LTL LITHUANIAN, LITAS", "MOP MACAU, PATACAS", "MKD MACEDONIA, DENARS", "MGA MADAGASCAR, ARIARY", "MWK MALAWI, KWACHAS", "MYR MALAYSIA, RINGGIT", "MVR MALDIVES (MALDIVE ISLANDS), RUFIYAA", "MTL MALTA, LIRI", "TMT MANAT", "MRO MAURITANIA, OUGUIYAS", "MUR MAURITIUS, RUPEES", "MXV MEXICAN UNIDAD DE INVERSION (UDI)", "MXN MEXICO, PESOS", "MDL MOLDOVAN, LEU", "MNT MONGOLIA, TUGRIKS", "MAD MOROCCO, DIRHAMS", "MZN MOZAMBIQUE, METICAIS", "MMK MYANMAR (BURMA), KYATS", "NAD NAMIBIA, DOLLARS", "NPR NEPAL, RUPEES", "ANG NETHERLANDS ANTILLES, GUILDERS", "NZD NEW ZEALAND, DOLLARS", "NIO NICARAGUA, CORDOBAS", "NGN NIGERIA, NAIRAS", "XXX NO CURRENCY", "NOK NORWEGIAN, KRONE", "OMR OMAN, RIALS", "OTH OTHERS", "PKR PAKISTAN, RUPEES", "XPD PALLADIUM, OUNCES", "PAB PANAMA, BALBOA", "PGK PAPUA NEW GUINEA, KINA", "PYG PARAGUAY, GUARANI", "PEN PERU, NUEVOS SOLES", "CUC PESO CONVERTIBLE", "PHP PHILIPPINES, PESOS", "XPT PLATINUM, OUNCES", "PLN POLISH, ZTOTY", "QAR QATAR, RIALS", "RON ROMANIAN, NEW LEU", "RUB RUSSIA, RUBLES", "RWF RWANDA, FRANCS", "SHP SAINT HELENA, POUNDS", "WST SAMOA, TALA", "STD SAO TOME AND PRINCIPE, DOBRAS", "SAR SAUDI ARABIA, RIYALS", "SPL SEBORGA, LUIGINI", "RSD SERBIA, DINARS", "SCR SEYCHELLES, RUPEES", "SLL SIERRA LEONE, LEONES", "XAG SILVER, OUNCES", "SKK SLOVAKIA, KORUNY", "SBD SOLOMON ISLANDS, DOLLARS", "SOS SOMALIA, SHILLINGS", "SLS SOMALILAND SHILLING", "ZAR SOUTH AFRICA, RAND", "SSP SOUTH SUDANESE POUND", "LKR SRI LANKA, RUPEES", "SDD SUDAN, DINARS", "SDG SUDANESE POUND", "SRD SURINAME, DOLLARS", "SZL SWAZI, LILANGENI", "SEK SWEDEN, KRONOR", "CHF SWITZERLAND, FRANCS", "SYP SYRIAN, POUND", "TWD TAIWAN, NEW DOLLARS", "TJS TAJIKISTAN, SOMONI", "TZS TANZANIA, SHILLINGS", "THB THAILAND, BAHT", "TOP TONGA, PA'ANGA", "PRB TRANSNISTRIAN RUBLE", "TTD TRINIDAD AND TOBAGO, DOLLARS", "TND TUNISIA, DINARS", "TRY TURKEY, NEW LIRA", "TMM TURKMENISTAN, MANATS", "TVD TUVALU, TUVALU DOLLARS", "UGX UGANDA, SHILLINGS", "XFU UIC-FRANC", "UAH UKRAINE, HRYVNIA", "COU UNIDAD DE VALOR REAL", "CLF UNIDADES DE FOMENTO", "AED UNITED ARAB EMIRATES, DIRHAMS", "GBP UNITED KINGDOM, POUNDS", "USN UNITED STATES DOLLAR (NEXT DAY)(FUNDS CODE)", "USS UNITED STATES DOLLAR (SAME DAY) (FUNDS CODE)", "USD UNITED STATES OF AMERICA, DOLLARS", "UYI URUGUAY PESO EN UNIDADES INDEXADSA (URUIURUI) (FUNDS CODE)", "UYU URUGUAY, PESOS", "UZS UZBEKISTAN, SUMS", "VUV VANUATU, VATU", "VEB VENEZUELA, BOLIVARES", "VND VIET NAM, DONG", "CHE WIR EURO", "CHW WIR FRANC", "YER YEMEN, RIALS", "ZMK ZAMBIA, KWACHA", "ZWL ZIMBABWE DOLLAR", "ZWD ZIMBABWE, ZIMBABWE DOLLARS"],
            shareholder: {
                        Type: '',
                        Name: '',
                        ID: '',
                        DOB: '',
                        Shares: '',
                        Nationality: '',
                        Email: '',
                        Phone: '',
                        Address: '',
                        Corporate_Representative_Name: '',
                        Corporate_Representative_Address: '',
                        Menu: false,
                        isCorpShow: false,
                        },
            nationality_options: ["AFGHAN", "ALBANIAN", "ALGERIAN", "AMERICAN", "ANDORRAN", "ANGOLAN", "ANTIGUAN", "ARGENTINIAN", "ARMENIAN", "AUSTRALIAN", "AUSTRIAN", "AZERBAIJANI", "BAHAMIAN", "BAHRAINI", "BANGLADESHI", "BARBADOS", "BELARUSSIAN", "BELGIAN", "BELIZEAN", "BENINESE", "BHUTANESE", "BOLIVIAN", "BOSNIAN", "BOTSWANA", "BR DEP TER CITIZEN", "BR NAT. OVERSEAS", "BR OVERSEAS CIT.", "BR PROTECTED PERS.", "BRAZILIAN", "BRITISH", "BRITISH SUBJECT", "BRUNEIAN", "BULGARIAN", "BURKINABE", "BURUNDIAN", "C ' TRAL AFRICAN REP", "CAMBODIAN", "CAMEROONIAN", "CANADIAN", "CAPE VERDEAN", "CHADIAN", "CHILEAN", "CHINESE", "COLOMBIAN", "COMORAN", "CONGOLESE", "COSTA RICAN", "CROATIAN", "CUBAN", "CYPRIOT", "CZECH", "DANISH", "DEMOCRATIC REPUBLIC OF THE CONGO", "DJIBOUTIAN", "DOMINICAN", "DOMINICAN (REPUBLIC)", "EAST TIMORESE", "ECUADORIAN", "EGYPTIAN", "EQUATORIAL GUINEA", "ERITREAN", "ESTONIAN", "ETHIOPIAN", "FIJIAN", "FILIPINO", "FINNISH", "FRENCH", "GABON", "GAMBIAN", "GEORGIAN", "GERMAN", "GHANAIAN", "GREEK", "GRENADIAN", "GUATEMALAN", "GUINEAN", "GUINEAN (BISSAU)", "GUYANESE", "HAITIAN", "HONDURAN", "HONG KONG", "HUNGARIAN", "ICELANDER", "INDIAN", "INDONESIAN", "IRANIAN", "IRAQI", "IRISH", "ISRAELI", "ITALIAN", "IVORY COAST", "JAMAICAN", "JAPANESE", "JORDANIAN", "KAZAKHSTANI", "KENYAN", "KIRIBATI", "KITTIAN & NEVISIAN", "KOREAN, NORTH", "KOREAN, SOUTH", "KUWAITI", "KYRGYZSTAN", "LAOTIAN", "LATVIAN", "LEBANESE", "LESOTHO", "LIBERIAN", "LIBYAN", "LIECHTENSTEINER", "LITHUANIAN", "LUXEMBOURGER", "MACAO", "MACEDONIAN", "MADAGASY", "MALAWIAN", "MALAYSIAN", "MALDIVIAN", "MALIAN", "MALTESE", "MARSHALLESE", "MAURITANEAN", "MAURITIAN", "MEXICAN", "MICRONESIAN", "MOLDAVIAN", "MONACAN", "MONGOLIAN", "MONTENEGRIN", "MOROCCAN", "MOZAMBICAN", "MYANMAR", "NAMIBIAN", "NAURUAN", "NEPALESE", "NETHERLANDS", "NEW ZEALANDER", "NI-VANUATU", "NICARAGUAN", "NIGER", "NIGERIAN", "NORWEGIAN", "OMANI", "PAKISTANI", "PALAUAN", "PALESTINIAN", "PANAMANIAN", "PAPUA NEW GUINEAN", "PARAGUAYAN", "PERUVIAN", "POLISH", "PORTUGUESE", "QATARI", "ROMANIAN", "RUSSIAN", "RWANDAN", "SALVADORAN", "SAMMARINESE", "SAMOAN", "SAO TOMEAN", "SAUDI ARABIAN", "SENEGALESE", "SERBIAN", "SEYCHELLOIS", "SIERRA LEONE", "SINGAPORE CITIZEN", "SINGAPORE P.R.", "SLOVAK", "SLOVENIAN", "SOLOMON ISLANDER", "SOMALI", "SOUTH AFRICAN", "SPANISH", "SRI LANKAN", "ST. LUCIA", "ST. VINCENTIAN", "STATELESS", "SUDANESE", "SURINAMER", "SWAZI", "SWEDISH", "SWISS", "SYRIAN", "TAIWANESE", "TAJIKISTANI", "TANZANIAN", "THAI", "TOGOLESE", "TONGAN", "TRINIDAD & TOBAGO", "TUNISIAN", "TURK", "TURKMEN", "TUVALU", "UGANDAN", "UKRAINIAN", "UNITED ARAB EM.", "UNKNOWN", "URUGUAYAN", "UZBEKISTAN", "VATICAN CITY STATE (HOLY SEE)", "VENEZUELAN", "VIETNAMESE", "YEMENI", "ZAMBIAN", "ZIMBABWEAN"],
            shareholder_options: ['INDIVIDUAL', 'CORPORATE'],
            country_options : ["Afghanistan","Albania","Algeria","Andorra","Angola","Anguilla","Antigua &amp; Barbuda","Argentina","Armenia","Aruba","Australia","Austria","Azerbaijan","Bahamas"
            ,"Bahrain","Bangladesh","Barbados","Belarus","Belgium","Belize","Benin","Bermuda","Bhutan","Bolivia","Bosnia &amp; Herzegovina","Botswana","Brazil","British Virgin Islands"
            ,"Brunei","Bulgaria","Burkina Faso","Burundi","Cambodia","Cameroon","Canada","Cape Verde","Cayman Islands","Chad","Chile","China","Colombia","Congo","Cook Islands","Costa Rica"
            ,"Cote D Ivoire","Croatia","Cruise Ship","Cuba","Cyprus","Czech Republic","Denmark","Djibouti","Dominica","Dominican Republic","Ecuador","Egypt","El Salvador","Equatorial Guinea"
            ,"Estonia","Ethiopia","Falkland Islands","Faroe Islands","Fiji","Finland","France","French Polynesia","French West Indies","Gabon","Gambia","Georgia","Germany","Ghana"
            ,"Gibraltar","Greece","Greenland","Grenada","Guam","Guatemala","Guernsey","Guinea","Guinea Bissau","Guyana","Haiti","Honduras","Hong Kong","Hungary","Iceland","India"
            ,"Indonesia","Iran","Iraq","Ireland","Isle of Man","Israel","Italy","Jamaica","Japan","Jersey","Jordan","Kazakhstan","Kenya","Kuwait","Kyrgyz Republic","Laos","Latvia"
            ,"Lebanon","Lesotho","Liberia","Libya","Liechtenstein","Lithuania","Luxembourg","Macau","Macedonia","Madagascar","Malawi","Malaysia","Maldives","Mali","Malta","Mauritania"
            ,"Mauritius","Mexico","Moldova","Monaco","Mongolia","Montenegro","Montserrat","Morocco","Mozambique","Namibia","Nepal","Netherlands","Netherlands Antilles","New Caledonia"
            ,"New Zealand","Nicaragua","Niger","Nigeria","Norway","Oman","Pakistan","Palestine","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Poland","Portugal"
            ,"Puerto Rico","Qatar","Reunion","Romania","Russia","Rwanda","Saint Pierre &amp; Miquelon","Samoa","San Marino","Satellite","Saudi Arabia","Senegal","Serbia","Seychelles"
            ,"Sierra Leone","Singapore","Slovakia","Slovenia","South Africa","South Korea","Spain","Sri Lanka","St Kitts &amp; Nevis","St Lucia","St Vincent","St. Lucia","Sudan"
            ,"Suriname","Swaziland","Sweden","Switzerland","Syria","Taiwan","Tajikistan","Tanzania","Thailand","Timor L'Este","Togo","Tonga","Trinidad &amp; Tobago","Tunisia"
            ,"Turkey","Turkmenistan","Turks &amp; Caicos","Uganda","Ukraine","United Arab Emirates","United Kingdom","United States","United States Minor Outlying Islands","Uruguay"
            ,"Uzbekistan","Venezuela","Vietnam","Virgin Islands (US)","Yemen","Zambia","Zimbabwe"],
            shareholders: [],

            director: {
                        
                        Name: '',
                        ID: '',
                        DOB: new Date().toISOString().substr(0, 10),
                        Nationality: '',
                        Email: '',
                        Phone: '',
                        Address: '',
                        Menu: false,
                        
                        },
            directors: [],
            newCompanySheetID: '',


            isStepHidden : false,
            isLoading : true,
            isThankYou : true,
            isError : true,
            responsefromServer: '', 
            isCorpShow: false,

            menu2: false,

        };

    },
    methods: {

        stepContinue(scope){
        
            this.$validator.validateAll(scope).then(result => {
                if (result) {
                
                    if (this.e1===3){
                        
                        // Show loading 
                        this.isLoading = false;
                        this.isStepHidden = true;
                        this.responsefromServer = "Loading..";
                        var shareholder_data = JSON.stringify(this.shareholders);
                        var director_data = JSON.stringify(this.directors);
                        var url = 'https://script.google.com/macros/s/AKfycbzupKBq9pwMrwaQXSMg7ZuQ3H_FOj0L5btphEKkS06j1VNcbBtj/exec';

                        // SEND COMPANY DETAILS 
                        
                        axios.get(url, {
                            params: {
                                sheet:'createnew',
                                Proposed_Company_Name_I : this.Proposed_Company_Name_I,
                                Proposed_Company_Name_II: this.Proposed_Company_Name_II,
                                Principal_Business_Activity_I: this.Principal_Business_Activity_I,
                                Principal_Business_Activity_II: this.Principal_Business_Activity_II,
                                Registered_Office_Address: this.Registered_Office_Address,
                                Paid_Up_Capital_Amount: this.Paid_Up_Capital_Amount,
                                Paid_Up_Capital_Currency: this.Paid_Up_Capital_Currency,
                                Total_Number_of_Shares: this.Total_Number_of_Shares,
                                Financial_Year_End: this.Financial_Year_End,
                                shareholders: this.shareholders,
                                directors: this.directors,
                                shareholder_data: shareholder_data,
                                director_data: director_data,
                                Contact_FirstName: this.Contact_FirstName,
                                Contact_LastName: this.Contact_LastName,
                                Contact_Phone: this.Contact_Phone,  
                                Contact_Email: this.Contact_Email,  


                            } 
                        }).then(response => {
                            // JSON responses are automatically parsed.
                            console.log(response);
                            console.log(response.data.result);
                            result = response.data.result;
                            this.isLoading = false;
                            this.isThankYou = true;
                            this.responsefromServer = "Success!";
                        }).catch(e => {
                        
                            this.responsefromServer = "Failed. Please try again later!";
                        })
                        
                    }
                    else {
                        if (this.e1==0){
                            this.e1 = this.e1+2;
                        }else{
                            this.e1++;
                        }
                    }

                }else{
                
                }
            });
        },
        goBack(){
            this.e1--
        },

        goNextStep () {
            console.log(this.e1);
            if (this.e1===3){
                
                // Show loading 
                this.isLoading = false;
                this.isStepHidden = true;
                this.responsefromServer = "Loading..";
                var shareholder_data = JSON.stringify(this.shareholders);
                var director_data = JSON.stringify(this.directors);
                var url = 'https://script.google.com/macros/s/AKfycbzupKBq9pwMrwaQXSMg7ZuQ3H_FOj0L5btphEKkS06j1VNcbBtj/exec';

                // SEND COMPANY DETAILS 
                
                axios.get(url, {
                    params: {
                        sheet:'createnew',
                        Proposed_Company_Name_I : this.Proposed_Company_Name_I,
                        Proposed_Company_Name_II: this.Proposed_Company_Name_II,
                        Principal_Business_Activity_I: this.Principal_Business_Activity_I,
                        Principal_Business_Activity_II: this.Principal_Business_Activity_II,
                        Registered_Office_Address: this.Registered_Office_Address,
                        Paid_Up_Capital_Amount: this.Paid_Up_Capital_Amount,
                        Paid_Up_Capital_Currency: this.Paid_Up_Capital_Currency,
                        Total_Number_of_Shares: this.Total_Number_of_Shares,
                        Financial_Year_End: this.Financial_Year_End,
                        shareholders: this.shareholders,
                        directors: this.directors,
                        shareholder_data: shareholder_data,
                        director_data: director_data,

                    } 

                }).then(response => {
                    // JSON responses are automatically parsed.
                    console.log(response);
                    console.log(response.data.result);
                    result = response.data.result;
                    this.isLoading = false;
                    this.isThankYou = true;
                    this.responsefromServer = "Success!";

                }).catch(e => {
                    
                    this.responsefromServer = "Failed. Please try again later!";
                })
                
            }
        else {

            this.e1++;

        }
        },

        addNewShareholder (){
            this.shareholders.push({
                Type: '',
                Name: '',
                ID: '',
                DOB: new Date().toISOString().substr(0, 10),
                Shares: '',
                Nationality: '',
                Email: '',
                Phone: '',
                Address: '',
                Corporate_Representative_Name: '',
                Corporate_Representative_Address: '',
                Menu: false,
                isCorpShow: false,
            });
        },
        removeShareholder: function (index) {
            if(index!=0){
                this.shareholders.splice(index,1)
            }
        },

        addNewDirector (){
            this.directors.push({
                Name: '',
                ID: '',
                DOB: new Date().toISOString().substr(0, 10),
                Nationality: '',
                Email: '',
                Phone: '',
                Address: '',
                Menu: false,
            });
        },
        removeDirector: function (index) {
            if(index!=0){
                this.directors.splice(index,1)
            }
        },

        onValueChange(val, id){
        
            let rec = this.$data.shareholders.find(({ID})=> ID==id);
            if(rec) rec.isCorpShow = val == 'CORPORATE';
            //this.$data.isCorpShow = val == 'CORPORATE';
        },

        


    },

    mounted: function () 
    {
        this.Contact_FirstName = 'John';
        this.Contact_LastName = 'Doe';
        this.Contact_Phone= '89898989';
        this.Contact_Email= 'johndoe@aol.com';
        this.Proposed_Company_Name_I= 'TEST 11';
        this.Principal_Business_Activity_I= 'Some Activity';
        this.Principal_Business_Activity_II='Some Activity 2';
        this.Registered_Office_Address= 'Somewhere';
        this.Paid_Up_Capital_Amount= '100';
        this.Paid_Up_Capital_Currency= 'SGD SINGAPORE, DOLLARS';
        this.Total_Number_of_Shares= '100';
        this.Financial_Year_End= new Date().toISOString().substr(0, 10);

        this.directors = [
            { 
                "Name": "John Doe", 
                "ID": "S833", 
                "DOB": this.Financial_Year_End, 
                "Nationality": "AMERICAN", 
                "Email": "johndoe@aol.com", 
                "Phone": "8733", 
                "Address": "Address 1 " 
            }, 
            { 
                "Name": "Jane Doe", 
                "ID": "SSW22", 
                "DOB": this.Financial_Year_End, 
                "Nationality": "ANGOLAN", 
                "Email": "jane@aol.com", 
                "Phone": "98", 
                "Address": "Address 222 " 
            }
        ];

        this.shareholders = [{ "Name": "John Doe", "ID": "S833", "DOB": this.Financial_Year_End, "Nationality": "AMERICAN", "Shares":"10",  "Email": "johndoe@aol.com", "Phone": "8733", "Address": "Address 1 ", "Corporate_Representative_Name" : "-", "Corporate_Representative_Address" : "-"}];
        
    },

    computed: {
        computedDateFormattedMomentjs () {
            return moment(this.Financial_Year_End).format("DD-MMM-YYYY");
        }
    },
  

};
</script>