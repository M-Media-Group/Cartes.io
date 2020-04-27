<template>
    <div>
      <l-map :zoom="zoom" :center="center" style="width: 100%; height: 71vh;" @contextmenu="addMarker" ref="map">
          <l-tile-layer :url="url" :attribution="attribution"></l-tile-layer>
          <l-locatecontrol/>
          <l-layer-group ref="hello_popup">
          <l-popup style="height:100px;">
            <form method="POST" id="reportForm" action="/incidents" @submit.prevent="submitForm()">
              <label class="my-1 mr-2">Report incident:</label>
              <select name="category" class="custom-select my-1 mr-sm-2 custom-select-sm" v-on:change="submit_data.category = $event.target.value">
                <option v-for="category in categories" :value="category.id"><img :src="category.icon" width="20">{{category.name}}</option>
              </select>
              <br/>
              <input type="submit" value="Report" class="btn btn-primary btn-sm my-1" :disabled="submit_data.loading">
            </form>
          </l-popup>
        </l-layer-group>

        <l-marker-cluster>
          <l-marker v-for="incident in incidents" :lat-lng="[incident.x, incident.y]" :key="incident.id+'marker'">
            <l-icon :icon-url="incident.category.icon" :icon-size="[20, 20]" :icon-anchor="[10, 10]"/>
            <l-popup @ready="openPopup"><b>{{incident.category.name}} reported in the area.</b><br/>Last report: <span class='timestamp' :datetime="incident.updated_at">{{ incident.updated_at }}</span>.<br/></l-popup>
          </l-marker>
        </l-marker-cluster>
    </l-map>
    </div>
</template>
<script>
    import { LMap, LTileLayer, LLayerGroup, LMarker, LPopup, LIcon } from 'vue2-leaflet';
import Vue2LeafletLocatecontrol from 'vue2-leaflet-locatecontrol/Vue2LeafletLocatecontrol';
    import Vue2LeafletMarkerCluster from 'vue2-leaflet-markercluster';

    export default {
        components: { LMap, LTileLayer, LMarker, LPopup, 'l-locatecontrol': Vue2LeafletLocatecontrol, LIcon, 'l-marker-cluster': Vue2LeafletMarkerCluster, LLayerGroup },
        data() {
            return {
              zoom:13,
              center: L.latLng(43.7040, 7.3111),
              url:'https://{s}.basemaps.cartocdn.com/rastertiles/voyager_labels_under/{z}/{x}/{y}{r}.png',
              attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
               subdomains: 'abcd',
                maxZoom: 19,
                minZoom: 2,
              incidents: [],
              categories: [],
              submit_data: {
                lat: 0,
                lng: 0,
                category: 1,
                loading: false
              }
            }
          },
          mounted () {
            axios
              .get('/api/incidents')
              .then(response => (this.incidents = response.data))

            axios
              .get('/api/categories')
              .then(response => (
                //console.log(response.data.data)
                this.categories = response.data.data
                ))

          },
          methods: {
            addMarker(event) {
                console.log(event)
                this.$refs.hello_popup.mapObject.openPopup(event.latlng);
                this.submit_data.lat = event.latlng.lat;
                this.submit_data.lng = event.latlng.lng;
                this.$refs.map.mapObject.flyTo(event.latlng, 18)
                console.log(this.submit_data)
            },
            openPopup(event) {
                timeago.render(document.querySelectorAll('.timestamp'))
            },
            submitForm(event) {
              this.submit_data.loading = true
              axios
                .post('/api/incidents', this.submit_data) // change this to post )
                .then((res) => {

                    console.log(res.data);
                    this.incidents.push(res.data);
                    this.$refs.hello_popup.mapObject.closePopup();
                    this.submit_data.loading = false

                })
                .catch((error) => {

                    console.log(error);
                    alert('You must be logged in and have permssion to post. Please log in or register.');
                    this.submit_data.loading = false
                });            }
          }
    }
</script>
<style scoped>
    @import "~leaflet.markercluster/dist/MarkerCluster.css";
    @import "~leaflet.markercluster/dist/MarkerCluster.Default.css";
</style>