<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">User's Information</div>
                    <div class="card-body">
                        <div class="alert alert-success" role="alert">
                            {{ name }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                name: ''
            }
        },

        mounted() {

        }
    }
</script>