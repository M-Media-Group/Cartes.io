<template>
        <div class="card bg-dark text-white mb-5" style="min-height:250px;">
            <map-component :map_id="map.uuid" style="height: 50vh"></map-component>
            <div v-if="map.categories" class="pl-1 pr-1 d-flex" style="top: 49vh;z-index: 1001;overflow: scroll;width: 100%;position: absolute; ">
                <a href="#" class="badge badge-secondary mr-1 mb-1" v-for="category in map.categories" :key="category.id">{{category.name}}</a>
            </div>
            <div class="card-body">
                <h5 class="card-title mt-3">{{map.title ? map.title : "Untitled map"}}</h5>
                <p class="card-text" v-if="!is_minimal">{{ map.description | truncate(250, '...') }}</p>
                <a :href="/maps/+map.uuid" class="btn btn-primary btn-block">Open map</a>
            </div>
            <div class="card-footer" v-if="!is_minimal">
                <p class="card-text small text-secondary">{{map.incidents_count}} reports · Created <span class='timestamp' :datetime="map.created_at">{{ map.created_at }}</span></p>
            </div>
        </div>
</template>
<script>
export default {
    props: ['map', 'is_minimal'],
    data() {
        return {
            zoom: 4,
            public_maps: [],
            private_maps: [],
            submit_data: {
                lat: 0,
                lng: 0,
                category: 0,
                category_name: '',
                loading: false
            }
        }
    },
    mounted() {


    },
    computed: {
        canEdit() {
            return this.token.length > 0 ? true : false;
        }
    },

    watch: {
 
    },

    methods: {

    }
}

</script>
