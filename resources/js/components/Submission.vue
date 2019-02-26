<template>
    <div class="container" id="sumbission">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card-header text-center">Please, fill out the form</div>
                <div
                        v-if="status.length !== 0"
                        class="alert alert-success"
                        role="alert"
                >
                    {{ status }}
                </div>
                <div
                        v-if="alert.length !== 0"
                        class="alert alert-danger"
                        role="alert"
                >
                    {{ alert }}
                </div>
                <div
                        v-if="errors.length !== 0"
                        class="alert alert-danger"
                        role="alert"
                >
                    <ul>
                        <li v-for="error in errors">
                            {{ error }}
                        </li>
                    </ul>
                </div>
                <div
                        class="card"
                        v-for="(user, index) in users"
                >
                    <div class="card-body cent">
                        <div class="form-group">
                            <div class="row w-100">
                                <div class="w-100 text-right">
                                    <button
                                            type="button"
                                            class="close"
                                            aria-label="Close"
                                            @click="removeRow(index)"
                                    >
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <label>Name</label>
                                </div>
                                <div class="col-md-8">
                                    <input
                                            class="form-control"
                                            type="text"
                                            name="name"
                                            v-model="user.name"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-2">
                                    <label>Phone</label>
                                </div>
                                <div class="col-md-8">
                                    <vue-tel-input v-model="user.phone"
                                                   @onInput="onInput"
                                                   @onValidate="onValidate(index)"
                                                   :preferredCountries="['us', 'gb', 'ru']"
                                                   :disabledFetchingCountry="false"
                                    >
                                    </vue-tel-input>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body text-right">
                        <button
                                class="btn btn-sm btn-success"
                                @click="addRow"
                        >Add row</button>
                        <button
                                class="btn btn-sm btn-primary"
                                @click="submit"
                        ><span
                                v-show="loading"
                                class="spinner-border spinner-border-sm"
                                role="status"
                                aria-hidden="true"
                        ></span><span
                                v-show="loading"
                        > Loading...</span><span
                                v-show="! loading"
                        >Submit</span></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import 'vue-tel-input/dist/vue-tel-input.css';

    export default {

        mounted() {
            this.setEmptyUsers();
        },

        data() {
            return {
                users: [],
                alert: '',
                status: '',
                errors: [],
                phoneValidated: false,
                currentCountryIso: null,
                loading: false,
            }
        },

        computed: {
            emptyUserExists() {
                const emptyUserIndex = this.users.findIndex(user => user.name.length === 0 || user.phone.length === 0);

                return emptyUserIndex !== -1;
            }
        },

        methods: {
            addRow() {
                if (this.loading) {
                    this.showErrorMessage('Please wait while loading.');
                }
                if (this.emptyUserExists) {
                    this.showErrorMessage('Please fill name and email.');
                    return;
                }
                this.clearMessages();
                this.users.push({
                    name: '',
                    phone: '',
                    country: '',
                });
            },
            removeRow(index) {
                this.clearMessages();
                this.users.splice(index, 1);
            },

            submit() {
                if (this.loading) {
                    this.showErrorMessage('Please wait while loading.');
                }
                if (this.emptyUserExists) {
                    this.showErrorMessage('Please fill name and email.');
                    return;
                }
                if (this.users.length === 0) {
                    this.showErrorMessage('The users\' list is empty.');
                    return;
                }
                if (! this.phoneValidated) {
                    this.showErrorMessage('The phone isn\'t validated.');
                    return;
                }
                this.clearMessages();
                this.loading = true;

                axios.post('/api/users', {
                    users: this.users
                })
                    .then(r => {
                        this.setEmptyUsers();
                        if (r.data.message !== undefined) {
                            this.showSuccessMessage(r.data.message);
                        }
                        this.loading = false;
                    })
                    .catch(e => {
                        if (e.response.data.errors !== undefined) {
                            this.showErrors(e.response.data.errors);
                        }
                        else if (e.response.data.message !== undefined) {
                            this.showErrorMessage(e.response.data.message);
                        }
                        else {
                            this.showErrorMessage('Error!');
                        }
                        this.loading = false;
                    });
            },

            setEmptyUsers() {
                this.users = [
                    {
                        name: '',
                        phone: '',
                        country: '',
                    }
                ];
            },

            showErrorMessage(message) {
                this.alert = message;
                this.status = '';
            },

            showSuccessMessage(message) {
                this.alert = '';
                this.status = message;
            },

            showErrors(errObj) {
                let errorsArr = Object.values(errObj);
                if (Array.isArray(errorsArr)) {
                    for (let errorsArrKey in errorsArr) {
                        if (Array.isArray(errorsArr[errorsArrKey])) {
                            for (let errorKey in errorsArr[errorsArrKey]) {
                                this.errors.push(errorsArr[errorsArrKey][errorKey])
                            }
                        }
                        else if (typeof errorsArr === 'string') {
                            this.errors.push(errorsArr[errorsArrKey])
                        }
                    }
                }
                else if (typeof errorsArr === 'string') {
                    this.errors.push(errorsArr)
                }
            },

            clearMessages() {
                this.alert = '';
                this.status = '';
                this.errors = [];
            },

            /**
             * @param {string} number
             * the phone number inputted by user, will be formatted along with country code
             * // Ex: inputted: (AU) 0432 432 432
             * // number = '+61432421546'
             *
             * @param {Boolean} isValid
             * @param {string} country
             */
            onInput({ number, isValid, country }) {
                this.currentCountryIso = country.iso2 !== undefined ? country.iso2 : '';
                if (number !== undefined && isValid !== undefined) {
                    if (! isValid) {
                        this.alert = "Number " + number + " is not valid.";
                        this.phoneValidated = false;
                    }
                    else {
                        this.alert = '';
                        this.phoneValidated = true;
                    }
                }
            },

            onValidate(index) {
                this.users[index].country = this.currentCountryIso;
            }
        },
    }
</script>