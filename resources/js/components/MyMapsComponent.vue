<template>
    <div>
        <h2>Your maps</h2>
        <p>These are the maps that you've created on the site.</p>
        <map-card-component v-for='(map, i) in private_maps' :key="map.uuid" :map="map" :disable_map="i > 0 ? true : false"></map-card-component>
        <div v-if="private_maps.length < 1" class="alert bg-dark">You have no maps yet. Create your first map or browse the public ones below.</div>
        <div class="row justify-content-center mb-5" v-if="private_maps_total > 15">
            <div class="small">{{private_maps.length}} / {{private_maps_total}} of your maps are shown here</div>
        </div>
        <hr class="my-4">
        <h2 class="mt-5">Public maps</h2>
        <p>These maps are made by the community and shared with everyone.</p>
        <map-card-component v-for='(map, i) in public_maps' :key="'p_map'+map.uuid" :map="map" :disable_map="i > 2 ? true : false"></map-card-component>
        <div class="row justify-content-center mb-5" v-if="public_maps_total > 15">
            <div class="small">{{public_maps.length}} / {{public_maps_total}} public maps, and a lot more private ones</div>
        </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            public_maps: [],
            private_maps: [],
            public_maps_total: 0,
            private_maps_total: 0,
        }
    },
    created() {
        var ids = []
        Object.keys(localStorage).forEach(function(key) {
            if (key.includes('map_')) {
                ids.push(key.replace('map_', ''))
            }
        });
        axios
            .get('/api/maps?orderBy=incidents_count')
            .then(response => {
                this.public_maps_total = response.data.total
                this.public_maps = response.data.data
            })

        if (ids.length > 0) {
            axios
                .get('/api/maps', {
                    params: {
                        ids: ids,
                        orderBy: 'updated_at'
                    }
                })
                .then(response => {
                    this.private_maps_total = response.data.total
                    this.private_maps = response.data.data
                }
                )
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
