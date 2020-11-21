<template>
    <div style="display: flex;">
        <div class="col-4 p-0" style="height: 100vh;">
            <ul id="marker_feed" class="list-unstyled px-0 pb-3 mb-3 bg-dark card">
                <li class="media p-3 card-header" data-toggle="collapse" data-target="#marker_feed_markers" aria-expanded="false" aria-controls="marker_feed_markers" style="cursor: pointer;">
                    <div class="media-body" style="display:flex;align-items: center;justify-content: space-between;">
                        <h5 class="mt-0 mb-0">Public maps</h5>
                        <button type="submit" class="btn btn-primary" form="new_map_form">
                            Create a map
                        </button>
                    </div>
                </li>
                <div id="marker_feed_markers" class="collapse show" style="max-height:80vh; overflow-y: scroll;">
                    <template v-if="maps.length > 0">
                        <li class="media ml-3 mr-3 p-3 mb-3 card" v-for="single_map, index in maps" :key="'map_id_'+single_map.uuid" @click="setMap(index)" v-bind:class="[map && single_map.uuid == map.uuid ? 'bg-white text-dark' : 'bg-secondary text-white feed-element']" v-if="single_map.incidents_count > 0 && single_map.title">
                            <div class="media-body">
                                <h5 class="mt-0 mb-1">{{single_map.title}}</h5>
                                <div v-if="map && single_map.uuid == map.uuid">
                                    <p>{{ single_map.description }}</p>
                                    <a :href="/maps/+map.uuid" class="btn btn-primary btn-block">Open map page</a>
                                </div>
                            </div>
                        </li>
                    </template>
                    <template v-else>
                        <div class="text-center text-muted p-3">There's no public maps at this time.</div>
                    </template>
                </div>
            </ul>
        </div>

<!--         <div class="col-4 p-0" style="height: 100vh;">
            <div class="bg-dark text-white p-3 border-dark" v-if="maps" v-for="single_map in maps" @click="getMap(single_map.uuid)">
                <div v-if="map.uuid == single_map.uuid">Active<br/></div>
                {{single_map.title}}
            </div>
        </div> -->

        <div class="col-8 p-0">
            <map-component v-if="map" :map_id="map.uuid" :map_token="null" style="height: 100vh;" :users_can_create_incidents="map.users_can_create_incidents" :map_categories="categories" :initial_incidents="null" v-on:marker-create="handleMarkerCreate" v-on:marker-delete="handleMarkerDelete"></map-component>
            <div v-else style="height: 65vh;" class="row align-items-center bg-dark">
                <div class="col text-center">
                    <div>Cartes.io</div>
                    <p class="text-muted mb-0">Contacting planet Earth...</p>
                </div>
            </div>
        </div>

    </div>
</template>
<script>
export default {
    props: [],

    components: {},

    data() {
        return {
            map: null,
            maps: [],
            markers: null,
            map_settings: {
                show_all: true,
                mapSelectedAge: 0,
            },
        }
    },

    created() {
        this.getAllMaps()
        // if (!this.map && !this.markers) {
        //     //this.getAllMarkers()
        // } else if (this.map && !this.markers) {
        //     this.getAllMarkers()
        //     this.listenForNewMarkers()
        //     this.listenForDeletedMarkers()
        // }

    },

    mounted() {

    },

    computed: {
        mapAgeInMinutes() {
            if (!this.map) {
                return false;
            }
            return Math.abs(Vue.moment().diff(this.map.created_at, 'minutes'))
        },
        activeMarkers() {
            if (!this.markers) {
                return []
            } else if (this.map_settings.show_all) {
                return this.markers
            }

            let markers = this.markers
            let diff_date_time = Vue.moment().subtract(this.map_settings.mapSelectedAge, 'minutes');

            return markers.filter(function(marker) {
                if (Vue.moment(marker.created_at).isSameOrBefore(diff_date_time, 'minute') && (marker.expires_at == null || Vue.moment(diff_date_time).isBefore(marker.expires_at))) {
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
            if (this.map.users_can_create_incidents === 'no') {
                return false
            }
            if (this.markers < 1) {
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

        getAllMaps() {
            axios
                .get('/api/maps')
                .then(response => (
                    this.maps = response.data.data,
                    this.setMap(0)
                ))
        },

        getAllMarkers() {
            axios
                .get('/api/maps/' + this.map.uuid + '/markers?show_expired=true')
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
        },

        getMap(map_uuid) {
            axios
                .get('/api/maps/' + map_uuid )
                .then(response => (
                    this.map = response.data
                ))
            this.getAllMarkers()
            this.listenForNewMarkers()
            this.listenForDeletedMarkers()
        },

        setMap(index) {
            this.map = this.maps[index]
        }

    }
}

</script>
<style>
#formControlRange {
    direction: rtl
}

</style>
