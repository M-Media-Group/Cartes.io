<template>
    <div>
      <h1 v-if="(!map || !map.title) && canEdit" :contenteditable="canEdit" @input="e.target.innerText = title">Here's your new map!<small v-if="canEdit" class="text-muted"> (Click here to edit the title)</small></h1>
      <h1 v-else-if="title" :contenteditable="canEdit" @input="e.target.innerText = title">{{title}}</h1>
      <h1 v-else>Untitled map {{title}}</h1>

      <p v-if="(!map || !map.description) && canEdit" :contenteditable="canEdit">Click here to edit the map description.</p>
      <p v-else-if="map && map.description" :contenteditable="canEdit">{{map.description}}</p>
      <p v-else>This map has no description.</p>

      <hr class="bg-dark"/>
      <p>Right click (or long-tap on mobile) on the map to create a marker. You can choose one of the existing labels or create your own.</p>
      <p><a href="/login">Login</a> or <a href="/register">register</a> to anonymously report incidents that may be dangerous to activists, journalists, human rights defenders, aid workers, social workers, or NGO staff during times of unrest or protest.</p>
      <p>After 3 hours, your report will automatically dissapear from the map.</p>
    </div>
</template>
<script>
    export default {
      props: ['map_id', 'map_token', 'map'],
        data() {
            return {
              zoom:4,
              title: this.map.title,
              description: this.map.description,
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

            if(this.map_token) {
              localStorage['map_'+this.map_id] = this.map_token
            }

          },
          computed: {
              canEdit() {
                  return this.map_token.length > 0 ? true : false;
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
                    alert('You must be logged in and have permssion to post. Please log in or register.');
                });            }
          }
    }
</script>
<style scoped>
 
</style>