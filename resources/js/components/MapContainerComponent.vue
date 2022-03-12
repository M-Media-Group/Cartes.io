<template>
  <div>
    <map-component
      v-if="map"
      :map_id="map.uuid"
      :map_token="map_token"
      style="height: 65vh"
      :users_can_create_markers="map.users_can_create_markers"
      :map_categories="categories"
      :initial_markers="activeMarkers"
      v-on:marker-create="handleMarkerCreate"
      v-on:marker-delete="handleMarkerDelete"
      :user="user"
      :linkOption="map.options.links"
    ></map-component>
    <div v-else style="height: 65vh" class="row align-items-center bg-dark">
      <div class="col text-center">
        <div>Cartes.io</div>
        <p class="text-muted mb-0">Contacting planet Earth...</p>
      </div>
    </div>
    <div class="container">
      <div class="row justify-content-center mt-5">
        <div class="col-md-12" style="max-width: 950px">
          <map-details-component
            :map_id="map.uuid"
            :map_token="map_token"
            :map="map"
            v-on:map-update="handleMapUpdate"
          >
            <map-markers-feed-component
              v-if="hasLiveData"
              :markers="activeMarkers"
              @showAllMarkers="map_settings.show_all = true"
            ></map-markers-feed-component>
            <div class="card bg-dark text-white mb-3">
              <div
                class="card-header"
                data-toggle="collapse"
                data-target="#displayCollapse"
                aria-expanded="false"
                aria-controls="displayCollapse"
                style="cursor: pointer"
              >
                <i class="fa fa-sliders"></i> Map display options
              </div>
              <div class="card-body collapse" id="displayCollapse">
                <div class="form-group row" v-if="!map_settings.show_all">
                  <label class="col-md-12 col-form-label" for="formControlRange"
                    >Time slider
                    <small v-if="map_settings.mapSelectedAge > 0"
                      >(showing map as of
                      {{ map_settings.mapSelectedAge }} minutes ago)</small
                    >
                    <small v-else>(showing live map)</small>
                  </label>
                  <div class="col-md-12">
                    <input
                      type="range"
                      class="form-control-range w-100"
                      id="formControlRange"
                      :max="mapAgeInMinutes"
                      step="5"
                      min="0"
                      v-model="map_settings.mapSelectedAge"
                    />
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-md-12 col-form-label"
                    >Visible markers</label
                  >
                  <div class="col-md-12">
                    <div class="form-check">
                      <input
                        type="checkbox"
                        id="show_all_checkbox"
                        v-model="map_settings.show_all"
                      />
                      <label class="form-check-label" for="show_all_checkbox">
                        Show all markers
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-md-12 col-form-label">Related maps</label>
                  <div class="col-md-12">
                    <div class="form-check">
                      <input
                        type="checkbox"
                        id="show_related_checkbox"
                        v-model="map_settings.show_related"
                      />
                      <label
                        class="form-check-label"
                        for="show_related_checkbox"
                      >
                        Show related maps
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!--                         <api-data-transformer-component v-on:markers-updated="handleApiMarkers"></api-data-transformer-component>
 -->
          </map-details-component>
          <h2 class="mt-5" v-if="nonSpamMarkers && nonSpamMarkers.length > 0">
            Map stats
          </h2>
          <div class="row" v-if="nonSpamMarkers && nonSpamMarkers.length > 0">
            <div class="col-md-6">
              <h3>Total markers</h3>
              <div class="jumbotron jumbotron-fluid bg-dark rounded">
                <div class="container">
                  <div class="display-4 text-center">
                    {{ nonSpamMarkers.length }}
                  </div>
                  <p class="lead text-center">All the markers created.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <h3>Active markers</h3>
              <div class="jumbotron jumbotron-fluid bg-dark rounded">
                <div class="container">
                  <div class="display-4 text-center">
                    {{ nonSpamMarkers.length - expiredMarkers.length }}
                  </div>
                  <p class="lead text-center">
                    Markers that are currently live.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <map-markers-chart-component
            v-if="nonSpamMarkers && nonSpamMarkers.length > 0"
            :markers="markers"
          ></map-markers-chart-component>
          <template v-if="map_settings.show_related && relatedMaps.length > 0">
            <h2>Related maps</h2>
            <map-card-component
              v-for="(map, i) in relatedMaps"
              :key="map.uuid"
              :map="map"
              :disable_map="i > 2 ? true : false"
            ></map-card-component>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>
<script lang="ts">
import axios from "axios";
import Vue from "vue";

export default {
  // props: ['initial_map', 'initial_markers', 'initial_map_token', 'user'],

  props: {
    // Basic type check (`null` and `undefined` values will pass any type validation)
    initial_map: Object,
    // Multiple possible types
    initial_markers: Object,
    // Required string
    initial_map_token: {
      type: String,
      required: false,
    },
    // Object with a default value
    user: {
      type: Object,
      // Object or array defaults must be returned from
      // a factory function
      default: function () {
        return {};
      },
    },
  },

  components: {},

  data() {
    return {
      map: this.initial_map,
      map_token: this.initial_map_token,
      markers: this.initial_markers,
      relatedMaps: [],
      map_settings: {
        show_all: false,
        mapSelectedAge: 0,
        show_related: true,
      },
    };
  },

  created() {
    if (!this.markers) {
      this.getAllMarkers();
    }
    this.listenForNewMarkers();
    this.listenForDeletedMarkers();
    this.getRelatedMaps();
  },

  mounted() {},

  computed: {
    mapAgeInMinutes() {
      if (!this.map) {
        return false;
      }
      return Math.abs(Vue.moment().diff(this.map.created_at, "minutes"));
    },
    nonSpamMarkers() {
      if (!this.markers) {
        return [];
      }
      return this.markers.filter(function (marker) {
        if (marker.is_spam && !localStorage["post_" + marker.id]) {
          return false;
        }
        return true;
      });
    },
    activeMarkers() {
      if (!this.markers) {
        return [];
      } else if (this.map_settings.show_all) {
        return this.nonSpamMarkers;
      }

      let diff_date_time = Vue.moment().subtract(
        this.map_settings.mapSelectedAge,
        "minutes"
      );

      return this.nonSpamMarkers.filter(function (marker) {
        if (
          Vue.moment(marker.created_at).isSameOrBefore(
            diff_date_time,
            "minute"
          ) &&
          (marker.expires_at == null ||
            Vue.moment(diff_date_time).isBefore(marker.expires_at))
        ) {
          return true;
        }
        return false;
      });
    },
    expiredMarkers() {
      if (!this.nonSpamMarkers) {
        return [];
      }
      return this.nonSpamMarkers.filter(function (marker) {
        if (!marker.expires_at) {
          return false;
        }
        return Vue.moment().isAfter(Vue.moment(marker.expires_at));
        //return new Date() > new Date(Date.parse(marker.expires_at.replace(/-/g, '/')))
      });
    },
    hasLiveData() {
      if (!this.map) {
        return false;
      }
      if (this.map.users_can_create_markers === "no") {
        return false;
      }
      if (this.markers < 1) {
        return false;
      }
      return true;
    },
    categories() {
      if (!this.markers) {
        return [];
      }
      var map1 = this.markers.map((x) => x.category);
      return (
        map1
          .map((e) => e.id)
          // store the indexes of the unique objects
          .map((e, i, final) => final.indexOf(e) === i && i)
          // eliminate the false indexes & return unique objects
          .filter((e) => map1[e])
          .map((e) => map1[e])
      );
    },
  },

  watch: {},

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
        .get("/api/maps/" + this.map.uuid + "/markers?show_expired=true")
        .then((response) => (this.markers = response.data));
    },

    getRelatedMaps() {
      if (!this.map_settings.show_related) {
        return;
      }

      axios
        .get("/api/maps/" + this.map.uuid + "/related")
        .then((response) => (this.relatedMaps = response.data));
    },

    listenForNewMarkers() {
      window.Echo.channel("maps." + this.map.uuid).listen(
        "MarkerCreated",
        (e) => {
          // this.$notify("A new marker was just added")
          this.$notify({
            type: "success",
            title: '"' + e.marker.category.name + '" marker was just added',
            text: e.marker.description,
          });
          this.handleMarkerCreate(e.marker);
        }
      );
    },

    listenForDeletedMarkers() {
      window.Echo.channel("maps." + this.map.uuid).listen(
        "MarkerDeleted",
        (e) => {
          this.handleMarkerDelete(e.marker.id);
        }
      );
    },

    handleMarkerCreate(marker) {
      this.markers.push(marker);
      this.getRelatedMaps();
    },

    handleMarkerDelete(id) {
      this.$notify("A marker was just deleted");
      this.markers = this.markers.filter((e) => e.id !== id);
      this.getRelatedMaps();
    },

    handleMapUpdate(map) {
      this.$notify("Information about this map has been updated");
      this.map = map;
    },

    handleApiMarkers(markers) {
      this.markers = markers;
    },
    markerInLocalStorageKey(id) {
      if (localStorage["post_" + id]) {
        return true;
      }
      return false;
    },
  },
};
</script>
<style>
#formControlRange {
  direction: rtl;
}
</style>
