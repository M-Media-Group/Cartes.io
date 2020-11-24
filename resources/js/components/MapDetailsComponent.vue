<template>
    <div class="row">
        <div class="col-md-7">
            <h1 v-if="(!map || !map.title) && canEdit" :contenteditable="canEdit" @input="handleSelectInput($event, 'title')" class="w-100">Here's your new map!<small v-if="canEdit" class="text-muted"> (Click to edit the title)</small></h1>
            <h1 v-else-if="submit_data.title" :contenteditable="canEdit" @input="handleSelectInput($event, 'title')" class="w-100">{{map.title}}</h1>
            <h1 v-else>Untitled map</h1>
            <p v-if="(!map || !map.description) && canEdit" :contenteditable="canEdit" @input="handleSelectInput($event, 'description')" style="white-space: pre-wrap">Right click (or long-tap on mobile) on the map to add markers. Click here to edit the map description.</p>
            <p v-else-if="map && map.description" :contenteditable="canEdit" @input="handleSelectInput($event, 'description')" style="white-space: pre-wrap" v-html="map.description"></p>
            <p v-else>This map has no description.</p>
        </div>
        <div class="col-md-5 mt-5 mt-md-0">
            <navigator-share v-bind:on-error="onShareError" v-bind:url="map_url" v-bind:title="submit_data.title ? submit_data.title : 'Untitled map'" v-bind:text="submit_data.description ? submit_data.description : 'Here\'s my map'" class="mb-3"><a slot="clickable" class="btn btn-primary btn-lg btn-block">Share this map</a></navigator-share>
            <slot></slot>
            <div class="card bg-dark text-white mb-3" v-if="canEdit" key="map-settings">
                <div class="card-header" data-toggle="collapse" data-target="#settingsCollapse" aria-expanded="false" aria-controls="settingsCollapse" style="cursor: pointer;"><i class="fa fa-cog"></i> Map settings</div>
                <div class="card-body collapse" id="settingsCollapse">
                    <div class="form-group row">
                        <label for="password-confirm" class="col-md-12 col-form-label">Who can see this map</label>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="privacy" id="exampleRadios1" value="public" :checked="submit_data.privacy == 'public' ? true : false" @input="handleSelectInput($event, 'privacy')">
                                <label class="form-check-label" for="exampleRadios1">
                                    Everyone <small>(make this map public)</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="privacy" id="exampleRadios2" value="unlisted" @input="handleSelectInput($event, 'privacy')" :checked="submit_data.privacy == 'unlisted' ? true : false">
                                <label class="form-check-label" for="exampleRadios2">
                                    Only people with a link
                                </label>
                            </div>
                            <div class="form-check disabled">
                                <input class="form-check-input" type="radio" name="privacy" id="exampleRadios3" value="private" disabled @input="handleSelectInput($event, 'privacy')">
                                <label class="form-check-label" for="exampleRadios3">
                                    No one
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="users_can_create_incidents" class="col-md-12 col-form-label">Who can create markers</label>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="users_can_create_incidents" id="exampleRadios4" value="yes" :checked="submit_data.users_can_create_incidents == 'yes' ? true : false" @input="handleSelectInput($event, 'users_can_create_incidents')">
                                <label class="form-check-label" for="exampleRadios4">
                                    Anyone
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="users_can_create_incidents" id="exampleRadios5" value="only_logged_in" @input="handleSelectInput($event, 'users_can_create_incidents')" :checked="submit_data.users_can_create_incidents == 'only_logged_in' ? true : false">
                                <label class="form-check-label" for="exampleRadios5">
                                    Only people that are logged in
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="users_can_create_incidents" id="exampleRadios6" value="no" @input="handleSelectInput($event, 'users_can_create_incidents')" :checked="submit_data.users_can_create_incidents == 'no' ? true : false">
                                <label class="form-check-label" for="exampleRadios6">
                                    No one
                                </label>
                            </div>
                            <small v-if="canEdit">You can add markers to your own map regardless of this setting</small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="password-confirm" class="col-md-12 col-form-label">When should new markers dissapear from the map</label>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="options.default_expiration_time" id="cdet1" value="4320" :checked="submit_data.options.default_expiration_time == 4320 ? true : false" @input="handleSelectInput($event, 'options.default_expiration_time')">
                                <label class="form-check-label" for="cdet1">
                                    after 3 days
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="options.default_expiration_time" id="cdet2" value="180" @input="handleSelectInput($event, 'options.default_expiration_time')" :checked="submit_data.options.default_expiration_time == 180 ? true : false">
                                <label class="form-check-label" for="cdet2">
                                    after 3 hours
                                </label>
                            </div>
                            <div class="form-check disabled">
                                <input class="form-check-input" type="radio" name="options.default_expiration_time" id="cdet3" :value="null" @input="handleSelectInput($event, 'options.default_expiration_time')" :checked="submit_data.options.default_expiration_time == null ? true : false">
                                <label class="form-check-label" for="cdet3" >
                                    Never
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="password-confirm" class="col-md-12 col-form-label">Where can markers be placed</label>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="options.limit_to_geographical_body_type" id="cdet1" value="land" :checked="submit_data.options.limit_to_geographical_body_type == 'land' ? true : false" @input="handleSelectInput($event, 'options.limit_to_geographical_body_type')">
                                <label class="form-check-label" for="cdet1">
                                    Only on land
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="options.limit_to_geographical_body_type" id="cdet2" value="water" @input="handleSelectInput($event, 'options.limit_to_geographical_body_type')" :checked="submit_data.options.limit_to_geographical_body_type == 'water' ? true : false">
                                <label class="form-check-label" for="cdet2">
                                    Only on water
                                </label>
                            </div>
                            <div class="form-check disabled">
                                <input class="form-check-input" type="radio" name="options.limit_to_geographical_body_type" id="cdet3" :value="null" @input="handleSelectInput($event, 'options.limit_to_geographical_body_type')" :checked="submit_data.options.limit_to_geographical_body_type == null ? true : false">
                                <label class="form-check-label" for="cdet3" >
                                    Anywhere
                                </label>
                            </div>
                            <small>Setting this to "water" or "land" will also drastically limit how many markers can be created on this map per minute</small>
                        </div>
                    </div>

                    <a class="btn btn-danger btn-sm mt-3" v-if="canEdit" @click="deleteMap" :disabled="submit_data.loading">Delete map</a>
                </div>
            </div>
            <p class="small">Right click (or long-tap on mobile) on the map to create a marker. You can choose one of the existing labels or create your own.</p>
            <p class="small mb-3" v-if="submit_data.options.default_expiration_time">{{submit_data.options.default_expiration_time / 60}} hours after creating a marker it will automatically dissapear from the map.</p>
            <div v-if="map.categories" class="d-flex mt-3" style="flex-wrap: wrap;max-height:30vh;overflow-x:scroll;">
                <a href="#" class="badge badge-secondary mr-1 mb-1" v-for="category in map.categories" :key="category.id">{{category.name}}</a>
            </div>
            <details class="mt-3 mb-3 small">
                <summary>
                    <dt class="d-inline text-muted">Developer info</dt>
                </summary>
                <div>
                    <p>Use standard API requests to get data from this map. No authentication required for public and unlisted maps. <a href="https://github.com/M-Media-Group/Cartes.io/wiki/API" rel="noopener" target="_BLANK">Read the docs</a>.</p>
                    <div class="mb-3">
                        <p class="mb-0">API <code>GET</code> map endpoint</p>
                        <code>/api/maps/{{map.uuid}}</code>
                    </div>
                    <div class="mb-3">
                        <p class="mb-0">API <code>GET</code> markers endpoint</p>
                        <code>/api/maps/{{map.uuid}}/markers</code>
                    </div>
                    <div class="mb-3">
                        <p class="mb-0">Embed this map on your website</p>
                        <code>&lt;iframe src="https://cartes.io/embeds/maps/{{map.uuid}}?type=map" width="100%" height="400" frameborder="0">&lt;/iframe></code>
                    </div>
                    <p>When using the API or embedding the map, you must attribute this website on your front-end.</p>
                </div>
            </details>
        </div>
    </div>
</template>
<script>
import NavigatorShare from 'vue-navigator-share'
import copy from 'copy-to-clipboard';

export default {
    props: ['map_id', 'map_token', 'map'],
    components: {
        NavigatorShare
    },
    data() {
        return {
            submit_data: {
                title: this.map.title,
                description: this.map.description,
                token: this.map_token,
                privacy: this.map.privacy,
                users_can_create_incidents: this.map.users_can_create_incidents,
                loading: false,
                options: {
                  default_expiration_time: this.map.options && this.map.options.default_expiration_time ? this.map.options.default_expiration_time : null,
                  limit_to_geographical_body_type: this.map.options && this.map.options.limit_to_geographical_body_type ? this.map.options.limit_to_geographical_body_type : null,
                },
            }
        }
    },
    mounted() {
        if (localStorage['map_' + this.map_id] && !this.submit_data.token) {
            this.submit_data.token = localStorage['map_' + this.map_id]
        } else if (this.submit_data.token) {
            localStorage['map_' + this.map_id] = this.submit_data.token
        }
    },
    computed: {
        map_url() {
            return window.location.href;
        },
        canEdit() {
            return this.submit_data.token ? true : false;
        }
    },

    watch: {
        title: function(newQuestion, oldQuestion) {
            this.submit_data.title = 'Waiting for you to stop typing...'
            this.debouncedGetAnswer()
        }
    },
    created: function() {
        // _.debounce is a function provided by lodash to limit how
        // often a particularly expensive operation can be run.
        // In this case, we want to limit how often we access
        // yesno.wtf/api, waiting until the user has completely
        // finished typing before making the ajax request. To learn
        // more about the _.debounce function (and its cousin
        // _.throttle), visit: https://lodash.com/docs#debounce
        this.debouncedGetAnswer = _.debounce(this.submitForm, 950)
    },
    methods: {
        onShareError(e) {
            copy(this.map_url, {
                message: 'Press #{key} to copy',
                onCopy: alert('Link copied')
            });
        },
        handleSelectInput(val, type) {

            if (type == 'privacy' || type == 'users_can_create_incidents') {
                this.submit_data[type] = val.target.value
            } else if (type == 'options.default_expiration_time')
            {
                this.submit_data.options.default_expiration_time = val.target.value
            } else if (type == 'options.limit_to_geographical_body_type')
            {
                this.submit_data.options.limit_to_geographical_body_type = val.target.value
            } else {
                this.submit_data[type] = val.target.innerText
            }
            this.debouncedGetAnswer()
        },
        checkForLocalStorageKey(id) {
            if (localStorage['map_' + id]) {
                return true
            }
            return false

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
        },
        deleteMap(id) {
            if(!confirm('You\'ll never see this map again. Are you sure?')) {
              return;
            }
            this.submit_data.loading = true;
            axios
                .delete('/api/maps/' + this.map_id, { data: { token: this.submit_data.token } })
                .then((res) => {
                  localStorage.removeItem('map_' + this.map_id)
                   alert('This map has gone with the wind!')
                   window.location.href = "/";
                    this.submit_data.loading = false;
                })
                .catch((error) => {
                    this.submit_data.loading = false
                    //console.log(error);
                    alert(error.message);
                });;
        },
    }
}

</script>
<style>
    *[contenteditable="true"]{
        display: inline-block;
    }
</style>