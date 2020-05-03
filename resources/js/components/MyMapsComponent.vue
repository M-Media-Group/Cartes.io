<template>
<div>
<!--       <map-component v-for="maps" :key="map.id" :map_id="map.id"></map-component>
 -->
<h2>Your maps</h2>
<p>These are the maps that you've created on the site.</p>
<div class="card bg-dark text-white mb-3" v-for='map in private_maps' :key="map.id">
  <map-component :map_id="map.uuid" style="height:250px;"></map-component>
  <div class="card-body">
    <h5 class="card-title">{{map.title ? map.title : "Untitled map"}}</h5>
    <p class="card-text">{{map.description}}</p>
    <p class="card-text small">{{map.incidents_count}} reports · Created <span class='timestamp' :datetime="map.created_at">{{ map.created_at }}</span></p>
    <a :href="/maps/+map.uuid" class="btn btn-primary">See map</a>
  </div>
</div>

<div v-if="private_maps.length < 1">You have no maps yet</div>
<hr class="my-4">
<h2 class="mt-5">Public maps</h2>
<p>These maps are made by the community, and public.</p>

<div class="card bg-dark text-white mb-3" v-for='map in public_maps' :key="map.id">
  <map-component :map_id="map.uuid" style="height:250px;"></map-component>
  <div class="card-body">
    <h5 class="card-title">{{map.title ? map.title : "Untitled map"}}</h5>
    <p class="card-text">{{map.description}}</p>
    <p class="card-text small">{{map.incidents_count}} reports</p>
    <a :href="/maps/+map.uuid" class="btn btn-primary">See map</a>
  </div>
</div>

</div>
</template>
<script>
    export default {
        data() {
            return {
              zoom:4,

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
          mounted () {

            if(localStorage['map_'+this.map_id]) {
              this.token = localStorage['map_'+this.map_id]
            }
            var ids = []
            Object.keys(localStorage).forEach(function(key){
              if(key.includes('map_')) {
                ids.push(key.replace('map_', ''))
              }
            });
               console.log(ids);
            axios
              .get('/api/maps')
              .then(response => (
                this.public_maps = response.data
                ))

            if (ids.length > 0){
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
              canEdit() {
                  return this.token.length > 0 ? true : false;
              }
          },

          watch: {
              title: function (newQuestion, oldQuestion) {
                this.title = 'Waiting for you to stop typing...'
                //this.debouncedGetAnswer()
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
            //this.debouncedGetAnswer = _.debounce(this.getAnswer, 500)
          },
          methods: {
            handleSelectInput (val) {
              this.submit_data.fullCategory = val
              this.submit_data.category = val.id
              this.submit_data.category_name = val.name
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
                .post('/api/incidents', this.submit_data) // change this to post )
                .then((res) => {

                    //console.log(res.data);
                    //this.incidents.push(res.data);
                    this.$refs.hello_popup.mapObject.closePopup();
                    this.submit_data.loading = false
                    localStorage['post_'+res.data.id] = res.data.id

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