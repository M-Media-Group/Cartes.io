<template>
    <div>
        <map-component v-if="map && markers" :map_id="map.uuid" :map_token="map_token" style="height: 65vh;" :users_can_create_incidents="map.users_can_create_incidents" :map_categories="map.categories" :initial_incidents="activeMarkers" v-on:marker-create="handleMarkerCreate" v-on:marker-delete="handleMarkerDelete"></map-component>
        <div v-else style="height: 65vh;" class="row align-items-center bg-dark">
            <div class="col text-center">
                <div>Cartes.io</div>
                <p class="text-muted mb-0">Loading map...</p>
            </div>
        </div>
        <div class="container">
            <div class="row justify-content-center mt-5">
                <div class="col-md-12" style="max-width: 950px;">
                    <map-details-component :map_id="map.uuid" :map_token="map_token" :map="map">
                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header">Map display options</div>
                            <div class="card-body">
                                <div class="form-group row">
                                    <label class="col-md-12 col-form-label">What markers are visible</label>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input type="checkbox" id="show_all_checkbox" v-model="map_settings.show_all">
                                            <label class="form-check-label" for="show_all_checkbox">
                                                Include {{expiredMarkers.length}} markers that have expired
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </map-details-component>
                    <h2 class="mt-5">Map stats</h2>
                    <div class="row" v-if="markers">
                        <div class="col-md-6">
                            <h3>Total markers</h3>
                            <div class="jumbotron jumbotron-fluid bg-dark rounded">
                                <div class="container">
                                    <div class="display-4 text-center">{{markers.length}}</div>
                                    <p class="lead text-center">All the markers created.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h3>Active markers</h3>
                            <div class="jumbotron jumbotron-fluid bg-dark rounded">
                                <div class="container">
                                    <div class="display-4 text-center">{{markers.length - expiredMarkers.length}}</div>
                                    <p class="lead text-center">Markers that are currently live.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <map-markers-chart-component :map_id="map.uuid" :markers="activeMarkers"></map-markers-chart-component>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: ['initial_map', 'initial_incidents', 'initial_map_token'],

    components: {},

    data() {
        return {
            map: this.initial_map,
            map_token: this.initial_map_token,
            markers: this.initial_incidents,
            map_settings: {
                show_all: false
            }
        }
    },

    created: function() {

    },

    mounted() {
        if (!this.markers) {
            this.getAllMarkers()
        }
    },

    computed: {
        activeMarkers() {
            if (!this.markers) {
                return []
            }
            if (this.map_settings.show_all) {
                return this.markers
            }
            return this.markers.filter(function(marker) {
                if (marker.expires_at == null) {
                    return true
                }
                return new Date() <= new Date(Date.parse(marker.expires_at.replace(/-/g, '/')))
            })
        },
        expiredMarkers() {
            if (!this.markers) {
                return []
            }
            return this.markers.filter(function(marker) {
                if (!marker.expires_at) {
                    return false
                }
                return new Date() > new Date(Date.parse(marker.expires_at.replace(/-/g, '/')))
            })
        }
    },

    watch: {

    },

    methods: {

        getAllMarkers() {
            axios
                .get('/api/maps/' + this.map.uuid + '/incidents?show_expired=true')
                .then(response => (
                    this.markers = response.data
                ))
        },

        listenForNewMarkers() {
            Echo.channel('maps.' + this.map.id).listen('IncidentCreated', (e) => {
                this.handleMarkerCreate(e.incident);
            });
        },

        handleMarkerCreate(marker) {
            this.markers.push(marker);
        },

        handleMarkerDelete(id) {
            this.markers = this.markers.filter((e) => e.id !== id)
        }

    }
}

</script>
<style>
</style>
