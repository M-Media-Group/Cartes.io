<template>
    <div class="row">
        <div class="col-12 mt-5 mt-md-0">
            <div class="card bg-dark text-white mb-3" key="map-settings">
                <div class="card-header" data-toggle="collapse" data-target="#settingsImportCollapse" aria-expanded="false" aria-controls="settingsImportCollapse" style="cursor: pointer;"><i class="fa fa-cog"></i> Data importer</div>
                <div class="card-body collapse" id="settingsImportCollapse">
                    <div class="form">
                        <div class="form-group mx-sm-3 mb-2">
                            <label for="inputPassword2" class="sr-only">API Endpoint</label>
                            <input v-model="endpoint" type="text" class="form-control" id="inputPassword2" placeholder="API Endpoint">
                        </div>
                        <button type="url" class="btn btn-primary mb-2" @click="getData()">Fetch JSON data</button>
                    </div>
                    <div class="alert alert-success" v-if="is_valid">All good</div>
                    <details class="mt-3 mb-3 ">
                        <summary>
                            <dt class="d-inline">Incoming request</dt>
                        </summary>
                        <div>
                            <pre class="text-white small"><code>{{first_data_point}}</code></pre>
                        </div>
                    </details>
                    <hr />
                    <details class="mt-3 mb-3 ">
                        <summary>
                            <dt class="d-inline ">Data transforming/mapping tools</dt>
                        </summary>
                        <div>
                            <input v-model="transform_parameters.container" type="text" class="form-control" id="inputPassword2" placeholder="Container">
                            <input v-model="transform_parameters.id" type="text" class="form-control" id="inputPassword2" placeholder="Marker ID">
                            <input v-model="transform_parameters.location.lat" type="text" class="form-control" id="inputPassword2" placeholder="Location LAT">
                            <input v-model="transform_parameters.location.lng" type="text" class="form-control" id="inputPassword2" placeholder="Location LNG">
                            <input v-model="transform_parameters.category_name" type="text" class="form-control" id="inputPassword2" placeholder="Category name">
                            <input v-model="transform_parameters.created_at" type="text" class="form-control" id="inputPassword2" placeholder="Created at">
                            <input v-model="transform_parameters.updated_at" type="text" class="form-control" id="inputPassword2" placeholder="Updated at">
                        </div>
                    </details>
                    <hr />
                    <details class="mt-3 mb-3 ">
                        <summary>
                            <dt class="d-inline ">Transformed request</dt>
                        </summary>
                        <div>
                            <pre class="text-white small"><code v-if="transformed_data">{{transformed_data[0]}}</code></pre>
                        </div>
                    </details>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: ['map_id', 'map_token', 'map'],
    components: {},
    data() {
        return {
            endpoint: 'https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/4.5_month.geojson',
            fetched_data: null,
            transform_parameters: {
                container: 'features',
                location: {
                    lng: 'geometry.coordinates.1',
                    lat: 'geometry.coordinates.0'
                },
                category_name: 'properties.title',
                id: 'id',
                created_at: 'properties.time',
                updated_at: 'properties.updated'
            }
        }
    },
    mounted() {
    },
    computed: {
        first_data_point() {
            if (!this.transformed_data_post_container) {
                return null
            }
            return this.transformed_data_post_container[0];
        },
        transformed_data_post_container() {
            if (!this.fetched_data || this.fetched_data.length < 1) {
                return null
            }
            var data_to_return = null;
            if (!this.transform_parameters.container) {
                var data_to_return = this.fetched_data
            } else {
                if (!this.fetched_data || !this.fetched_data[this.transform_parameters.container]) {
                    return null
                }
                var data_to_return = this.fetched_data[this.transform_parameters.container]
            }
            if( !Array.isArray(data_to_return) ) {
                return data_to_return
            }
            return data_to_return.slice(0, 1000)
        },
        transformed_data() {
            if (!this.fetched_data || !this.transform_parameters.created_at || !this.transform_parameters.updated_at) {
                return null
            }
            var data_to_return = this.transformed_data_post_container;

            if (!data_to_return || data_to_return.length < 1 || !Array.isArray(data_to_return)) {
                return null
            }

            var map1 = data_to_return.map((x) => {
                // "id": 1040,
                // console.log(x);
                return {
                    "id": this.resolve(this.transform_parameters.id, x),
                    "location": {
                        "type": "Point",
                        "coordinates": [
                            this.resolve(this.transform_parameters.location.lng, x),
                            this.resolve(this.transform_parameters.location.lat, x),
                        ]
                    },
                    // "category_id": 1013,
                    "created_at": Vue.moment(this.resolve(this.transform_parameters.created_at, x)).format(),
                    "updated_at": Vue.moment(this.resolve(this.transform_parameters.updated_at, x)).format(),
                    "description": null,
                    "expires_at": null,
                    "category": {
                        // "id": 1013,
                        "name": this.resolve(this.transform_parameters.category_name, x),
                        // "slug": "asdasdlkajsda",
                        "icon": "/images/marker-01.svg"
                    }
                }
            })
            return map1
        },
        is_valid() {
            if (!this.transformed_data || !this.transformed_data[0]) {
                return false
            } else if (!this.transformed_data[0].location.coordinates[0]) {
                return false
            } else if (!this.transformed_data[0].location.coordinates[1]) {
                return false
            } else if (!this.transformed_data[0].id) {
                return false
            } else if (!this.transformed_data[0].category.name) {
                return false
            } else if (!this.transformed_data[0].created_at) {
                return false
            } else if (!this.transformed_data[0].updated_at) {
                return false
            }
            this.emitNewMarkers()
            return true
        }
    },

    watch: {

    },
    created: function() {
        // _.debounce is a function provided by lodash to limit how
        // often a particularly expensive operation can be run.
        // In this case, we want to limit how often we access
        // yesno.wtf/api, waiting until the user has completely
        // finished typing before making the ajax request. To learn
        // more about the _.debounce function (and its cousin
        // _.throttle), visit: https://lodash.com/docs#debounce
        this.debouncedGetAnswer = _.debounce(this.submitForm, 750)
    },
    methods: {
        resolve(prop, obj) {
            if (typeof obj !== 'object') throw 'getProp: obj is not an object'
            if (typeof prop !== 'string') throw 'getProp: prop is not a string'

            // Replace [] notation with dot notation
            prop = prop.replace(/\[["'`](.*)["'`]\]/g, ".$1")

            return prop.split('.').reduce(function(prev, curr) {
                return prev ? prev[curr] : undefined
            }, obj || self)
        },
        getData() {
            if (this.endpoint.includes('incidents:7888') || this.endpoint.includes('cartes.io')) {
                this.transform_parameters = {
                    container: '',
                    location: {
                        lng: 'location.coordinates.0',
                        lat: 'location.coordinates.1'
                    },
                    category_name: 'category.name',
                    id: 'id',
                    created_at: 'created_at',
                    updated_at: 'created_at'
                }
            }
            axios
                .get(this.endpoint, {
                    maxContentLength: 20971520, // 20mb
                    timeout: 10 * 1000, // 10 seconds max
                    maxRedirects: 0,
                    transformRequest: [function(data, headers) {
                        delete headers.common['X-Requested-With'];
                        delete headers.common['X-CSRF-TOKEN'];
                        delete headers['X-Socket-Id'];
                        return data;
                    }]
                })
                .then((res) => {
                    this.fetched_data = res.data
                    // console.log(res);
                })
                .catch((e) => {
                    console.log(e);
                    // e.request.res.destroy();
                });
        },
        emitNewMarkers() {
            this.$emit('markers-updated', this.transformed_data);
        },
        submitForm(event) {
            this.submit_data.loading = true;
            axios
                .put('/api/maps/' + this.map_id, this.submit_data) // change this to post )
                .then((res) => {
                    this.submit_data.loading = false
                    this.$emit('map-update', res.data);
                })
                .catch((error) => {
                    this.submit_data.loading = false
                    console.log(error);
                    if (error.response.data.errors) {
                        var message = Object.entries(error.response.data.errors)
                            .map(([error_name, error_value], i) => `${error_name}: ${error_value[0]} | `)
                            .join('\n');
                    } else {
                        var message = error.response.data.message
                    }
                    alert(message);
                });
        }
    }
}

</script>
<style>
</style>
