<template>
    <div>
        <form autocomplete="off" @submit="checkForm" method="post">
            <p v-if="errors.length">
                <b>Please correct the following error(s):</b>
                <ul>
                    <li v-for="error in errors">{{ error }}</li>
                </ul>
            </p>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="" id="email" class="form-control" placeholder="user@example.com" v-model="email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" class="form-control" v-model="password">
            </div>
            <button type="submit" class="btn btn-default">Sign in</button>
        </form>
    </div>
</template>

<script>
    export default {
        mounted() {
            console.log('Component mounted.')
        },
        data(){
            return {
                email: null,
                password: null,
                errors: []
            }
        },
        methods: {
            checkForm: function (e) {
            if (this.email && this.password) {
                console.log('test');
                axios.post('/api/v1/login', {
                    email: this.email,
                    password: this.password
                })
                .then(function (response) {
                    console.log(response);
                    localStorage.setItem('token', response.data.data['token'])
                })
                .catch(function (error) {
                    console.log(error);
                });
            }

            this.errors = [];

            if (!this.email) {
                this.errors.push('Name required.');
            }
            if (!this.password) {
                this.password.push('Age required.');
            }

            e.preventDefault();
            }
        }
    }
</script>
