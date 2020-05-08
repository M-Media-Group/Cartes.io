<template>
    <div>
        <input v-if="hasCookie" type="button" class="btn btn-danger mb-5 mt-3" value="Revoke analytics cookie consent" @click='revokeConsent'/>
    </div>
</template>
<script>
export default {
    data() {
        return {
        }
    },
    mounted() {

    },
    computed: {
        hasCookie(){
            return document.cookie.match(/^(.*;)?\s*laravel_cookie_consent\s*=\s*[^;]+(.*)?$/)
        }

    },

    watch: {

    },

    methods: {
        revokeConsent() {
            //alert(document.cookie = "laravel_cookie_consent=;")
            document.cookie = "laravel_cookie_consent=; expires=Thu, 01 Jan 2000 00:00:00 GMT; path=/; domain=.incidents";
            document.cookie = "laravel_cookie_consent=; expires=Thu, 01 Jan 2000 00:00:00 GMT; path=/; domain=.cartes.io";
            window.location.href = "/";
        },

    }
}

</script>
