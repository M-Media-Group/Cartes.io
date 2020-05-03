<template>
    <div>
<!--       <map-component v-for="maps" :key="map.id" :map_id="map.id"></map-component>
 -->    </div>
</template>
<script>
    export default {
      props: ['map_id', 'map_token', 'map'],
        data() {
            return {
              zoom:4,
              title: this.map.title,
              description: this.map.description,
              token: this.map_token,
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
                    alert('You must be logged in and have permssion to post. Please log in or register.');
                });            }
          }
    }
</script>
<style scoped>
 
</style>