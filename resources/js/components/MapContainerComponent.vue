<template>
    <div>
        <map-component v-if="map" :map_id="map.uuid" :map_token="map_token" style="height: 65vh;" :users_can_create_incidents="map.users_can_create_incidents" :map_categories="categories" :initial_incidents="activeMarkers" v-on:marker-create="handleMarkerCreate" v-on:marker-delete="handleMarkerDelete"></map-component>
        <div v-else style="height: 65vh;" class="row align-items-center bg-dark">
            <div class="col text-center">
                <div>Cartes.io</div>
                <p class="text-muted mb-0">Contacting planet Earth...</p>
            </div>
        </div>
        <div class="container">
            <div class="row justify-content-center mt-5">
                <div class="col-md-12" style="max-width: 950px;">
                    <map-details-component :map_id="map.uuid" :map_token="map_token" :map="map" v-on:map-update="handleMapUpdate">
                        <map-markers-feed-component v-if="hasLiveData" :markers="activeMarkers"></map-markers-feed-component>
                        <div class="card bg-dark text-white mb-3" >
                            <div class="card-header" data-toggle="collapse" data-target="#displayCollapse" aria-expanded="false" aria-controls="displayCollapse" style="cursor: pointer;"><i class="fa fa-sliders"></i> Map display options</div>
                            <div class="card-body collapse" id="displayCollapse">
                                <div class="form-group row" v-if="!map_settings.show_all">
                                    <label class="col-md-12 col-form-label" for="formControlRange">Time slider 
                                        <small v-if="map_settings.mapSelectedAge > 0">(showing map as of {{map_settings.mapSelectedAge}} minutes ago)</small>
                                        <small v-else>(showing live map)</small>
                                    </label>
                                    <div class="col-md-12">
                                        <input type="range" class="form-control-range w-100" id="formControlRange"  :max="mapAgeInMinutes" step="5" min="0" v-model="map_settings.mapSelectedAge">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-12 col-form-label">Visible markers</label>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input type="checkbox" id="show_all_checkbox" v-model="map_settings.show_all">
                                            <label class="form-check-label" for="show_all_checkbox">
                                                Show all markers
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
<!--                         <api-data-transformer-component v-on:markers-updated="handleApiMarkers"></api-data-transformer-component>
 -->                    </map-details-component>
                    <h2 class="mt-5" v-if="markers && markers.length > 0">Map stats</h2>
                    <div class="row" v-if="markers && markers.length > 0">
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
                    <map-markers-chart-component v-if="markers && markers.length > 0" :markers="markers"></map-markers-chart-component>
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
                show_all: false,
                mapSelectedAge: 0,
            },
        }
    },

    created() {
        if (!this.markers) {
            this.getAllMarkers()
        }
        this.listenForNewMarkers()
        this.listenForDeletedMarkers()
    },

    mounted() {

    },

    computed: {
        mapAgeInMinutes() {
            if(!this.map) {
                return false;
            }
            return Math.abs(Vue.moment().diff(this.map.created_at, 'minutes'))
        },
        activeMarkers() {
            if (!this.markers) {
                return []
            }
            if (this.map_settings.show_all) {
                return this.markers
            }
            const diff_date_time = Vue.moment().subtract(this.map_settings.mapSelectedAge, 'minutes');
            return this.markers.filter(function(marker) {
                if ( Vue.moment(marker.created_at).isBefore(diff_date_time) && (marker.expires_at == null || Vue.moment(diff_date_time).isBefore(marker.expires_at))) {
                    return true
                }
                return false
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
                return Vue.moment().isAfter(Vue.moment(marker.expires_at))
                //return new Date() > new Date(Date.parse(marker.expires_at.replace(/-/g, '/')))
            })
        },
        hasLiveData() {
            if (!this.map) {
                return false
            }
            if (this.map.users_can_create_incidents === 'no' ) {
                return false
            }
            if(this.markers < 1) {
                return false
            }
            return true
        },
        categories() {
            if (!this.markers) {
                return []
            }
            var map1 = this.markers.map(x => x.category);
            return map1.map(e => e.id)
                  // store the indexes of the unique objects
                  .map((e, i, final) => final.indexOf(e) === i && i)
                  // eliminate the false indexes & return unique objects
                 .filter((e) => map1[e]).map(e => map1[e]);
        }
    },

    watch: {

    },

    methods: {
        groupBy(list, keyGetter) {
            const map = new Map();
            list.forEach((item) => {
                const key = keyGetter(item);
                const collection = map.get(key);
                if (!collection) {
                    map.set(key, [item]);
                } else {
                    collection.push(item);
                }
            });
            return map;
        },

        getAllMarkers() {
            axios
                .get('/api/maps/' + this.map.uuid + '/incidents?show_expired=true')
                .then(response => (
                    this.markers = response.data
                ))
        },

        listenForNewMarkers() {
            Echo.channel('maps.' + this.map.uuid).listen('IncidentCreated', (e) => {
                this.handleMarkerCreate(e.incident);
            });
        },

        listenForDeletedMarkers() {
            Echo.channel('maps.' + this.map.uuid).listen('IncidentDeleted', (e) => {
                this.handleMarkerDelete(e.incident.id);
            });
        },

        handleMarkerCreate(marker) {
            this.markers.push(marker);
        },

        handleMarkerDelete(id) {
            this.markers = this.markers.filter((e) => e.id !== id)
        },

        handleMapUpdate(map) {
            this.map = map
        },

        handleApiMarkers(markers) {
            this.markers = markers
        }

    }
}

</script>
<style>
#formControlRange {
  direction: rtl
}
</style>
