<template>
    <div>
      <l-map :zoom="zoom" :center="center" style="width: 100%; height: 71vh;" @contextmenu="addMarker" ref="map">
          <l-tile-layer :url="url" :attribution="attribution"></l-tile-layer>
          <l-locatecontrol/>

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
    import { LMap, LTileLayer, LMarker, LPopup, LIcon } from 'vue2-leaflet';
import Vue2LeafletLocatecontrol from 'vue2-leaflet-locatecontrol/Vue2LeafletLocatecontrol';
    import Vue2LeafletMarkerCluster from 'vue2-leaflet-markercluster';

    export default {
        components: { LMap, LTileLayer, LMarker, LPopup, 'l-locatecontrol': Vue2LeafletLocatecontrol, LIcon, 'l-marker-cluster': Vue2LeafletMarkerCluster },
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
              categories: []
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
                console.log(event.latlng)

    var content = '<form method="POST" id="reportForm" action="/incidents"><label class="my-1 mr-2">Report incident:</label><select name="category" class="custom-select my-1 mr-sm-2 custom-select-sm">';
      for(const category in this.categories) {
          content += '<option value="'+this.categories[category].id+'"><img src="'+this.categories[category].icon+'" width="20">'+this.categories[category].name+'</option>';
     }
        content += '</select><br/><input type="hidden" name="lat" value="'+event.lat+'"><input type="hidden" name="lng" value="'+event.lng+'">';
        content+= '<input type="submit" value="Report" class="btn btn-primary btn-sm my-1"></form>';
                var popup = L.popup().setLatLng(event.latlng).setContent(content).openOn(this.$refs.map.mapObject);
                this.$refs.map.mapObject.flyTo(event.latlng, 18)
            },
            openPopup(event) {
                timeago.render(document.querySelectorAll('.timestamp'))
            }
          }
    }
</script>
<style scoped>
    @import "~leaflet.markercluster/dist/MarkerCluster.css";
    @import "~leaflet.markercluster/dist/MarkerCluster.Default.css";
</style>