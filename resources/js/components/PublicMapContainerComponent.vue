<template>
  <div>
    <transition name="slide">
      <div
        key="menu"
        style="position: fixed; left: 1.5rem; bottom: 3rem; z-index: 1000"
        v-if="menu_hidden"
      >
        <div class="btn btn-primary" @click="setMenuVisibility(true)">
          Show all maps
        </div>
        <a :href="/maps/ + map.uuid" class="btn btn-secondary">Open map page</a>
      </div>
      <div
        v-else
        key="menu_button"
        class="p-0"
        style="
          height: 100vh;
          position: fixed;
          left: 0;
          top: 0;
          z-index: 1001;
          min-width: 18rem;
          max-width: 27rem;
          background-color: var(--white);
        "
      >
        <ul
          id="marker_feed"
          class="list-unstyled px-0 pb-3 bg-transparent card"
        >
          <li
            class="media p-3 card-header"
            style="cursor: pointer; display: block"
          >
            <div
              class="media-body"
              style="
                display: flex;
                align-items: center;
                justify-content: space-between;
              "
            >
              <h5 class="mt-0 mb-0">Maps on Cartes.io</h5>
              <a
                href="javascript:void(0)"
                class="btn btn-sm text-white"
                @click="setMenuVisibility(0)"
                >X</a
              >
            </div>
            <div
              class="mt-3"
              style="
                display: flex;
                align-items: center;
                justify-content: space-between;
              "
            >
              <a
                class="btn btn-sm"
                href="#"
                @click="setMapSelector('user')"
                v-bind:class="[
                  map_selector == 'user' ? 'btn-primary' : 'btn-dark',
                ]"
                >Your maps</a
              >
              <a
                class="btn btn-sm"
                href="#"
                @click="setMapSelector('public')"
                v-bind:class="[
                  map_selector == 'public' ? 'btn-primary' : 'btn-dark',
                ]"
                >Public maps</a
              >
            </div>
          </li>
          <div
            id="marker_feed_markers"
            class="collapse show"
            style="max-height: 83vh; overflow-y: scroll"
          >
            <template v-if="activeMaps.length > 0">
              <li
                class="media ml-3 mr-3 p-3 mb-3 card"
                v-for="(single_map, index) in activeMaps"
                :key="'map_id_' + single_map.uuid"
                @click="setMap(index)"
                v-bind:class="[
                  map && single_map.uuid == map.uuid
                    ? 'bg-white text-dark'
                    : 'bg-secondary text-white feed-element',
                ]"
              >
                <div class="media-body">
                  <h5 class="mt-0 mb-1">
                    {{ single_map.title || "Untitled map" }}
                  </h5>
                  <div v-if="map && single_map.uuid == map.uuid">
                    <p>{{ single_map.description }}</p>
                    <a
                      :href="/maps/ + map.uuid"
                      class="btn btn-primary btn-block w-100"
                      >Open map page</a
                    >
                  </div>
                </div>
              </li>
              <li class="media ml-3 mr-3 p-3 mb-3">
                <div class="media-body">
                  <button
                    type="submit"
                    class="btn btn-primary mt-3 mb-5 w-100"
                    form="new_map_form"
                  >
                    Create a map
                  </button>
                </div>
              </li>
            </template>
            <template v-else>
              <div class="text-center text-muted p-3">
                There's no maps to show.
              </div>
              <li class="media ml-3 mr-3 p-3 mb-3">
                <div class="media-body">
                  <button
                    type="submit"
                    class="btn btn-primary mt-3 mb-5 w-100"
                    form="new_map_form"
                  >
                    Create a map
                  </button>
                </div>
              </li>
            </template>
          </div>
        </ul>
      </div>
    </transition>
    <div
      class="col-12 p-0"
      @touchstart="setMenuVisibility(0)"
      @mousedown="setMenuVisibility(0)"
    >
      <map-component
        v-if="map"
        :map_id="map.uuid"
        :map_token="null"
        style="height: 100vh"
        :users_can_create_markers="map.users_can_create_markers"
        :map_categories="categories"
        :initial_markers="null"
        v-on:marker-create="handleMarkerCreate"
        v-on:marker-delete="handleMarkerDelete"
      ></map-component>
      <div v-else style="height: 65vh" class="row align-items-center bg-dark">
        <div class="col text-center">
          <div>Cartes.io</div>
          <p class="text-muted mb-0">Contacting planet Earth...</p>
        </div>
      </div>
    </div>
  </div>
</template>
<script lang="ts">
import axios from "axios";
import Vue from "vue";

export default {
  props: [],

  components: {},

  data() {
    return {
      map_selector: "public",
      map: null,
      maps: [],
      user_maps: [],
      markers: null,
      menu_hidden: false,
      map_settings: {
        show_all: true,
        mapSelectedAge: 0,
      },
    };
  },

  created() {
    this.getAllMaps();
    // if (!this.map && !this.markers) {
    //     //this.getAllMarkers()
    // } else if (this.map && !this.markers) {
    //     this.getAllMarkers()
    //     this.listenForNewMarkers()
    //     this.listenForDeletedMarkers()
    // }
  },

  mounted() {
    this.getUsersMaps();
  },

  computed: {
    mapAgeInMinutes() {
      if (!this.map) {
        return false;
      }
      return Math.abs(Vue.moment().diff(this.map.created_at, "minutes"));
    },
    activeMarkers() {
      if (!this.markers) {
        return [];
      } else if (this.map_settings.show_all) {
        return this.markers;
      }

      let markers = this.markers;
      let diff_date_time = Vue.moment().subtract(
        this.map_settings.mapSelectedAge,
        "minutes"
      );

      return markers.filter(function (marker) {
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
      if (!this.markers) {
        return [];
      }
      return this.markers.filter(function (marker) {
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
    activeMaps() {
      if (this.map_selector == "user") {
        return this.user_maps;
      }
      return this.maps.filter(function (single_map) {
        return single_map.markers_count > 0 && single_map.title !== "";
      });
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

    getAllMaps() {
      axios
        .get("/api/maps")
        .then((response) => ((this.maps = response.data.data), this.setMap(0)));
    },

    getUsersMaps() {
      var ids = [];
      Object.keys(localStorage).forEach(function (key) {
        if (key.includes("map_")) {
          ids.push(key.replace("map_", ""));
        }
      });

      if (ids.length > 0) {
        axios
          .get("/api/maps", {
            params: {
              ids: ids,
              orderBy: "updated_at",
            },
          })
          .then((response) => {
            this.user_maps = response.data.data;
          });
      }
    },

    getAllMarkers() {
      axios
        .get("/api/maps/" + this.map.uuid + "/markers?show_expired=true")
        .then((response) => (this.markers = response.data));
    },

    listenForNewMarkers() {
      window.Echo.channel("maps." + this.map.uuid).listen(
        "MarkerCreated",
        (e) => {
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
    },

    handleMarkerDelete(id) {
      this.markers = this.markers.filter((e) => e.id !== id);
    },

    handleMapUpdate(map) {
      this.map = map;
    },

    handleApiMarkers(markers) {
      this.markers = markers;
    },

    getMap(map_uuid) {
      axios
        .get("/api/maps/" + map_uuid)
        .then((response) => (this.map = response.data));
      this.getAllMarkers();
      this.listenForNewMarkers();
      this.listenForDeletedMarkers();
    },

    setMap(index) {
      this.map = this.activeMaps[index];
    },

    setMapSelector(value) {
      this.map_selector = value;
    },

    setMenuVisibility(value) {
      if (value) {
        return (this.menu_hidden = false);
      }
      return (this.menu_hidden = true);
    },
  },
};
</script>
<style>
#formControlRange {
  direction: rtl;
}
.slide-enter-active,
.slide-leave-active {
  transition: transform 0.2s ease;
}

.slide-enter,
.slide-leave-to {
  transform: translateX(-100%);
  transition: all 150ms ease-in 0s;
}
</style>
