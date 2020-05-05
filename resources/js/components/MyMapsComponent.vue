<template>
    <div>
        <h2>Your maps</h2>
        <p>These are the maps that you've created on the site.</p>
        <map-card-component v-for='map in private_maps' :key="map.uuid" :map="map"></map-card-component>
        <div v-if="private_maps.length < 1" class="alert bg-dark">You have no maps yet. Create your first map or browse the public ones below.</div>
        <hr class="my-4">
        <h2 class="mt-5">Public maps</h2>
        <p>These maps are made by the community, and public.</p>
        <map-card-component v-for='map in public_maps' :key="'p_map'+map.uuid" :map="map"></map-card-component>
    </div>
</template>
<script>
export default {
    data() {
        return {
            public_maps: [],
            private_maps: [],
        }
    },
    mounted() {
        var ids = []
        Object.keys(localStorage).forEach(function(key) {
            if (key.includes('map_')) {
                ids.push(key.replace('map_', ''))
            }
        });
        axios
            .get('/api/maps?orderBy=incidents_count')
            .then(response => (
                this.public_maps = response.data
            ))

        if (ids.length > 0) {
            axios
                .get('/api/maps', {
                    params: {
                        ids: ids
                    }
                })
                .then(response => (
                    this.private_maps = response.data
                ))
        }

    },
    computed: {

    },

    watch: {

    },

    methods: {

    }
}

</script>
