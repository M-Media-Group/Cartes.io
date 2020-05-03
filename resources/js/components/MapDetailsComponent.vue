<template>
  <div class="row">
    <div class="col-md-7">
        <h1 v-if="(!map || !map.title) && canEdit" :contenteditable="canEdit" @input="handleSelectInput($event, 'title')">Here's your new map!<small v-if="canEdit" class="text-muted"> (Click here to edit the title)</small></h1>
        <h1 v-else-if="submit_data.title" :contenteditable="canEdit" @input="handleSelectInput($event, 'title')">{{map.title}}</h1>
        <h1 v-else>Untitled map</h1>

        <p v-if="(!map || !map.description) && canEdit" :contenteditable="canEdit" @input="handleSelectInput($event, 'description')">Click here to edit the map description.</p>
        <p v-else-if="map && map.description" :contenteditable="canEdit" @input="handleSelectInput($event, 'description')">{{map.description}}</p>
        <p v-else>This map has no description.</p>
        <hr class="my-4">

    </div>
    <div class="col-md-5 p-md-0 mt-5 mt-md-0">
        <div class="card bg-dark text-white" v-if="canEdit">
          <div class="card-header">Map settings</div>
          <div class="card-body">
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
              <label for="password-confirm" class="col-md-12 col-form-label">Who can create markers</label>
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
                  Only people that are logged in<br/><small>(You can still add markers even if not logged in)</small>
                </label>
              </div>
              <div class="form-check disabled">
                <input class="form-check-input" type="radio" name="users_can_create_incidents" id="exampleRadios6" value="no" disabled @input="handleSelectInput($event, 'no')">
                <label class="form-check-label" for="exampleRadios6">
                  No one
                </label>
              </div>
              </div>
            </div>
          </div>
        </div>
    </div>
  </div>
  </template>
</template>
<script>
    export default {
      props: ['map_id', 'map_token', 'map'],
        data() {
            return {
              token: this.map_token,
              submit_data: {
                title: this.map.title,
                description: this.map.description,
                token: this.map_token,
                privacy: this.map.privacy,
                users_can_create_incidents: this.map.users_can_create_incidents,
                loading: false
              }
            }
          },
          mounted () {

            if(localStorage['map_'+this.map_id]) {
              this.token = localStorage['map_'+this.map_id]
              this.submit_data.token = localStorage['map_'+this.map_id]
            }

          },
          computed: {
              canEdit() {
                  return this.token.length > 0 ? true : false;
              }
          },

          watch: {
              title: function (newQuestion, oldQuestion) {
                this.submit_data.title = 'Waiting for you to stop typing...'
                this.debouncedGetAnswer()
              }
          },
          created: function () {
            // _.debounce is a function provided by lodash to limit how
            // often a particularly expensive operation can be run.
            // In this case, we want to limit how often we access
            // yesno.wtf/api, waiting until the user has completely
            // finished typing before making the ajax request. To learn
            // more about the _.debounce function (and its cousin
            // _.throttle), visit: https://lodash.com/docs#debounce
            this.debouncedGetAnswer = _.debounce(this.submitForm, 750)
            //this.foo = _.debounce(function(){}, 1000);
          },
          methods: {
            handleSelectInput (val, type) {
              if (type == 'privacy' || type == 'users_can_create_incidents') {
                this.submit_data[type] = val.target.value
              } else {
                this.submit_data[type] = val.target.innerText
              }
              this.debouncedGetAnswer()
            },
            checkForLocalStorageKey (id) {
              if (localStorage['map_'+id]) {
                return true
              }
              return false

            },
            submitForm(event) {
              this.submit_data.loading = true;
              axios
                .put('/api/maps/'+this.map_id, this.submit_data) // change this to post )
                .then((res) => {

                    //console.log(res.data);
                    //this.incidents.push(res.data);
                    this.submit_data.loading = false
                    //alert('saved');
                })
                .catch((error) => {
                    this.submit_data.loading = false
                    console.log(error);
                    alert(error.message);
                    //alert('You must be logged in and have permssion to post. Please log in or register.');
                });            }
          }
    }
</script>
<style scoped>
 
</style>