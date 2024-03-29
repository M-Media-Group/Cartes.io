<template>
  <div>
    <l-map
      :zoom="2"
      :center="center"
      :maxBoundsViscosity="1.0"
      :worldCopyJump="true"
      style="width: 100%; height: 100%"
      @contextmenu="addMarkerMenu"
      ref="map"
    >
      <l-tile-layer :url="url" :attribution="attribution"></l-tile-layer>
      <l-locatecontrol />
      <v-geosearch :options="geosearchOptions"></v-geosearch>
      <l-control
        class="leaflet-control-ar leaflet-bar leaflet-control"
        v-if="supportsAr"
      >
        <a
          :href="'https://cartesio.netlify.app/?mapId=' + map_id"
          target="_BLANK"
          >AR</a
        >
      </l-control>
      <l-layer-group ref="hello_popup">
        <l-popup>
          <form
            v-if="
              canPost == 'yes' ||
              (canPost == 'only_logged_in' && user && user.id)
            "
            method="POST"
            action="/markers"
            @submit.prevent="addMarker()"
            :disabled="!submit_data.category_name"
          >
            <label class="my-1 mr-2">Marker label:</label>
            <multiselect
              v-model="fullCategory"
              @input="handleSelectInput"
              track-by="name"
              label="name"
              placeholder="Start typing..."
              tag-placeholder="Add this as new label"
              :options="categories"
              :searchable="true"
              :allow-empty="false"
              :taggable="true"
              @tag="addTag"
              style="width: 250px"
              :show-labels="false"
              class="your_custom_class"
              :loading="submit_data.loading"
              :internal-search="false"
              :clear-on-select="false"
              :options-limit="300"
              :limit="3"
              :max-height="600"
              :show-no-results="false"
              @search-change="asyncFind"
              :preserve-search="true"
              required
            >
              <template slot="limit"
                >Keep typing to refine your search</template
              >
              <template slot="noOptions"
                >Search for or add a new label</template
              >
              <template slot="singleLabel" slot-scope="{ option }"
                ><strong>{{ option.name }}</strong></template
              >
              <template slot="option" slot-scope="props"
                ><img
                  v-if="props.option.icon"
                  class="rounded img-thumbnail mr-1"
                  height="25"
                  width="25"
                  :src="props.option.icon"
                  alt=""
                  style="position: initial"
                />{{ props.option.name }}
              </template>
            </multiselect>
            <div class="form-group mt-1">
              <!--                             <label for="description">Description</label>
 -->
              <textarea
                class="form-control mt-1"
                id="description"
                rows="2"
                name="description"
                v-model="submit_data.description"
                placeholder="Description (optional)"
              ></textarea>
              <input
                v-if="linkOption === 'required' || linkOption === 'optional'"
                class="form-control mt-1"
                type="url"
                pattern="https://.*"
                :placeholder="
                  'Link using https://' +
                  (linkOption === 'optional' ? ' (optional)' : '')
                "
                :required="linkOption === 'required'"
                v-model="submit_data.link"
              />
            </div>
            <input
              type="submit"
              value="Add marker"
              class="btn btn-primary btn-sm my-1"
              :disabled="
                submit_data.loading ||
                !submit_data.category_name ||
                (linkOption === 'required' && !submit_data.link)
              "
            />
          </form>
          <div v-else-if="canPost == 'only_logged_in'">
            <a href="/login">Log in</a> to Cartes.io to post on this map.
          </div>
          <div v-else>You can't create markers on this map.</div>
        </l-popup>
      </l-layer-group>
      <l-marker-cluster>
        <l-marker
          v-for="marker in activeMarkers"
          :lat-lng="[marker.location.coordinates[1], marker.location.coordinates[0]]"
          :key="marker.id + 'marker'"
          @click="handleOpenedPopup($event, marker.id)"
        >
          <l-icon
            :icon-url="marker.category.icon"
            :icon-size="[30, 30]"
            :icon-anchor="[15, 25]"
          />
          <l-popup @ready="openPopup">
            <p class="mb-1" style="min-width: 200px">
              <b>{{ marker.category.name }}</b>
            </p>
            <p
              class="mb-1 mt-0 w-100 d-block"
              v-if="marker.description"
              v-html="marker.description"
            ></p>
            <small class="w-100 d-block" v-if="marker.link"
              ><a :href="marker.link" target="blank">{{
                marker.link.split("/")[2]
              }}</a>
            </small>
            <small class="w-100 d-block"
              >Last update:
              <span class="timestamp" :datetime="marker.updated_at">{{
                marker.updated_at
              }}</span
              >.</small
            >
            <small
              v-if="isMarkerExpired(marker.expires_at)"
              class="w-100 d-block"
              >Expired:
              <span class="timestamp" :datetime="marker.expires_at">{{
                marker.expires_at
              }}</span
              >.</small
            >
            <details class="small" v-if="marker.marker">
              <summary>Click to see address</summary>
              <p class="mt-0 mb-1">{{ marker.marker.label }}</p>
            </details>
            <a
              class="btn btn-link btn-sm text-danger"
              v-if="canDeletePost(marker)"
              @click="deleteMarker(marker.id)"
              :disabled="submit_data.loading"
              >Delete</a
            >
            <a
              class="btn btn-link btn-sm text-warning"
              v-if="canMarkAsSpamPost(marker)"
              @click="markAsSpam(marker.id)"
              :disabled="submit_data.loading"
              >Report as spam</a
            >
          </l-popup>
        </l-marker>
      </l-marker-cluster>
    </l-map>
    <div
      class="alert bg-primary map-notification"
      role="alert"
      v-if="new_message"
    >
      <h4 class="alert-heading">A new marker was created</h4>
      <p class="mb-0">{{ new_message }}</p>
    </div>
  </div>
</template>
<script>
// URL MAP HASH FUNCTION
(function (window) {
  var HAS_HASHCHANGE = (function () {
    var doc_mode = window.documentMode;
    return "onhashchange" in window && (doc_mode === undefined || doc_mode > 7);
  })();
  L.Hash = function (map) {
    this.onHashChange = L.Util.bind(this.onHashChange, this);
    if (map) {
      this.init(map);
    }
  };
  L.Hash.parseHash = function (hash) {
    if (hash.indexOf("#") === 0) {
      hash = hash.substr(1);
    }
    var args = hash.split("/");
    if (args.length == 3) {
      var zoom = parseInt(args[0], 10),
        lat = parseFloat(args[1]),
        lon = parseFloat(args[2]);
      if (isNaN(zoom) || isNaN(lat) || isNaN(lon)) {
        return !1;
      } else {
        return { center: new L.LatLng(lat, lon), zoom: zoom };
      }
    } else {
      return !1;
    }
  };
  (L.Hash.formatHash = function (map) {
    var center = map.getCenter(),
      zoom = map.getZoom(),
      precision = Math.max(0, Math.ceil(Math.log(zoom) / Math.LN2));
    return (
      "#" +
      [zoom, center.lat.toFixed(precision), center.lng.toFixed(precision)].join(
        "/"
      )
    );
  }),
    (L.Hash.prototype = {
      map: null,
      lastHash: null,
      parseHash: L.Hash.parseHash,
      formatHash: L.Hash.formatHash,
      init: function (map) {
        this.map = map;
        this.lastHash = null;
        this.onHashChange();
        if (!this.isListening) {
          this.startListening();
        }
      },
      removeFrom: function (map) {
        if (this.changeTimeout) {
          clearTimeout(this.changeTimeout);
        }
        if (this.isListening) {
          this.stopListening();
        }
        this.map = null;
      },
      onMapMove: function () {
        if (this.movingMap || !this.map._loaded) {
          return !1;
        }
        var hash = this.formatHash(this.map);
        if (this.lastHash != hash) {
          location.replace(hash);
          this.lastHash = hash;
        }
      },
      movingMap: !1,
      update: function () {
        var hash = location.hash;
        if (hash === this.lastHash) {
          return;
        }
        var parsed = this.parseHash(hash);
        if (parsed) {
          this.movingMap = !0;
          this.map.setView(parsed.center, parsed.zoom);
          this.movingMap = !1;
        } else {
          this.onMapMove(this.map);
        }
      },
      changeDefer: 100,
      changeTimeout: null,
      onHashChange: function () {
        if (!this.changeTimeout) {
          var that = this;
          this.changeTimeout = setTimeout(function () {
            that.update();
            that.changeTimeout = null;
          }, this.changeDefer);
        }
      },
      isListening: !1,
      hashChangeInterval: null,
      startListening: function () {
        this.map.on("moveend", this.onMapMove, this);
        if (HAS_HASHCHANGE) {
          L.DomEvent.addListener(window, "hashchange", this.onHashChange);
        } else {
          clearInterval(this.hashChangeInterval);
          this.hashChangeInterval = setInterval(this.onHashChange, 50);
        }
        this.isListening = !0;
      },
      stopListening: function () {
        this.map.off("moveend", this.onMapMove, this);
        if (HAS_HASHCHANGE) {
          L.DomEvent.removeListener(window, "hashchange", this.onHashChange);
        } else {
          clearInterval(this.hashChangeInterval);
        }
        this.isListening = !1;
      },
    });
  L.hash = function (map) {
    return new L.Hash(map);
  };
  L.Map.prototype.addHash = function () {
    this._hash = L.hash(this);
  };
  L.Map.prototype.removeHash = function () {
    this._hash.removeFrom();
  };
})(window);
// END MAP HASH FUNCTION

// FULL SCREEN FUNCTION
L.Control.Fullscreen = L.Control.extend({
  options: {
    position: "topleft",
    title: { false: "View Fullscreen", true: "Exit Fullscreen" },
  },
  onAdd: function (map) {
    var container = L.DomUtil.create(
      "div",
      "leaflet-control-fullscreen leaflet-bar leaflet-control"
    );
    this.link = L.DomUtil.create(
      "a",
      "leaflet-control-fullscreen-button leaflet-bar-part",
      container
    );
    this.link.href = "#";
    this._map = map;
    this._map.on("fullscreenchange", this._toggleTitle, this);
    this._toggleTitle();
    L.DomEvent.on(this.link, "click", this._click, this);
    return container;
  },
  _click: function (e) {
    L.DomEvent.stopPropagation(e);
    L.DomEvent.preventDefault(e);
    this._map.toggleFullscreen(this.options);
  },
  _toggleTitle: function () {
    this.link.title = this.options.title[this._map.isFullscreen()];
  },
});
L.Map.include({
  isFullscreen: function () {
    return this._isFullscreen || false;
  },
  toggleFullscreen: function (options) {
    var container = this.getContainer();
    if (this.isFullscreen()) {
      if (options && options.pseudoFullscreen) {
        this._disablePseudoFullscreen(container);
      } else if (document.exitFullscreen) {
        document.exitFullscreen();
      } else if (document.mozCancelFullScreen) {
        document.mozCancelFullScreen();
      } else if (document.webkitCancelFullScreen) {
        document.webkitCancelFullScreen();
      } else if (document.msExitFullscreen) {
        document.msExitFullscreen();
      } else {
        this._disablePseudoFullscreen(container);
      }
    } else {
      if (options && options.pseudoFullscreen) {
        this._enablePseudoFullscreen(container);
      } else if (container.requestFullscreen) {
        container.requestFullscreen();
      } else if (container.mozRequestFullScreen) {
        container.mozRequestFullScreen();
      } else if (container.webkitRequestFullscreen) {
        container.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
      } else if (container.msRequestFullscreen) {
        container.msRequestFullscreen();
      } else {
        this._enablePseudoFullscreen(container);
      }
    }
  },
  _enablePseudoFullscreen: function (container) {
    L.DomUtil.addClass(container, "leaflet-pseudo-fullscreen");
    this._setFullscreen(true);
    this.invalidateSize();
    this.fire("fullscreenchange");
  },
  _disablePseudoFullscreen: function (container) {
    L.DomUtil.removeClass(container, "leaflet-pseudo-fullscreen");
    this._setFullscreen(false);
    this.invalidateSize();
    this.fire("fullscreenchange");
  },
  _setFullscreen: function (fullscreen) {
    this._isFullscreen = fullscreen;
    var container = this.getContainer();
    if (fullscreen) {
      L.DomUtil.addClass(container, "leaflet-fullscreen-on");
    } else {
      L.DomUtil.removeClass(container, "leaflet-fullscreen-on");
    }
  },
  _onFullscreenChange: function (e) {
    var fullscreenElement =
      document.fullscreenElement ||
      document.mozFullScreenElement ||
      document.webkitFullscreenElement ||
      document.msFullscreenElement;
    if (fullscreenElement === this.getContainer() && !this._isFullscreen) {
      this._setFullscreen(true);
      this.fire("fullscreenchange");
    } else if (
      fullscreenElement !== this.getContainer() &&
      this._isFullscreen
    ) {
      this._setFullscreen(false);
      this.fire("fullscreenchange");
    }
  },
});
L.Map.mergeOptions({ fullscreenControl: false });
L.Map.addInitHook(function () {
  if (this.options.fullscreenControl) {
    this.fullscreenControl = new L.Control.Fullscreen(
      this.options.fullscreenControl
    );
    this.addControl(this.fullscreenControl);
  }
  var fullscreenchange;
  if ("onfullscreenchange" in document) {
    fullscreenchange = "fullscreenchange";
  } else if ("onmozfullscreenchange" in document) {
    fullscreenchange = "mozfullscreenchange";
  } else if ("onwebkitfullscreenchange" in document) {
    fullscreenchange = "webkitfullscreenchange";
  } else if ("onmsfullscreenchange" in document) {
    fullscreenchange = "MSFullscreenChange";
  }
  if (fullscreenchange) {
    var onFullscreenChange = L.bind(this._onFullscreenChange, this);
    this.whenReady(function () {
      L.DomEvent.on(document, fullscreenchange, onFullscreenChange);
    });
    this.on("unload", function () {
      L.DomEvent.off(document, fullscreenchange, onFullscreenChange);
    });
  }
});
L.control.fullscreen = function (options) {
  return new L.Control.Fullscreen(options);
};
// END FULL SCREEN FUNCTION

import {
  LMap,
  LTileLayer,
  LLayerGroup,
  LMarker,
  LPopup,
  LIcon,
  LControl,
} from "vue2-leaflet";
import Vue2LeafletLocatecontrol from "vue2-leaflet-locatecontrol/Vue2LeafletLocatecontrol";
import Vue2LeafletMarkerCluster from "vue2-leaflet-markercluster";
import Multiselect from "vue-multiselect";
import { OpenStreetMapProvider } from "leaflet-geosearch";
import VGeosearch from "vue2-leaflet-geosearch";
import axios from "axios";

export default {
  props: [
    "map_id",
    "map_token",
    "users_can_create_markers",
    "map_categories",
    "initial_markers",
    "user",
    "linkOption",
  ],

  components: {
    LMap,
    LTileLayer,
    LMarker,
    LPopup,
    "l-locatecontrol": Vue2LeafletLocatecontrol,
    LIcon,
    "l-marker-cluster": Vue2LeafletMarkerCluster,
    LLayerGroup,
    Multiselect,
    "v-geosearch": VGeosearch,
    LControl,
  },

  data() {
    return {
      center: L.latLng(43.704, 7.3111),
      url: "https://{s}.basemaps.cartocdn.com/rastertiles/voyager_labels_under/{z}/{x}/{y}{r}.png",
      attribution:
        '&copy; <a href="https://cartes.io">Cartes.io</a> &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a> &copy; <a href="https://icons8.com/attributions">Icons8</a>',
      subdomains: "abcd",
      markers: this.initial_markers,
      categories: this.map_categories ? this.map_categories : [],
      new_message: "",
      fullCategory: { id: null, name: "" },
      query: "",
      open_marker: null,
      markerResults: {},
      submit_data: {
        lat: 0,
        lng: 0,
        category: 0,
        description: "",
        category_name: "",
        loading: false,
        map_token: this.map_token,
        link: "",
      },
      geosearchOptions: {
        // Important part Here
        provider: new OpenStreetMapProvider(),
        style: "button",
        autoClose: true,
        showPopup: true,
        showMarker: false,
        keepResult: true,
        marker: {
          // icon: greenIcon,
          draggable: false,
        },
      },
    };
  },

  created: function () {
    this.$root.$on("flyTo", (f) => this.$refs.map.mapObject.flyTo(f, 16));
    if (!this.markers) {
      this.getMarkers();
    }
    // _.debounce is a function provided by lodash to limit how
    // often a particularly expensive operation can be run.
    // In this case, we want to limit how often we access
    // yesno.wtf/api, waiting until the user has completely
    // finished typing before making the ajax request. To learn
    // more about the _.debounce function (and its cousin
    // _.throttle), visit: https://lodash.com/docs#debounce
    this.debouncedGetCategories = _.debounce(this.getCategories, 250);
    this.debouncedRenderTimeago = _.debounce(this.renderTimeago, 50);
  },

  mounted() {
    const map = this.$refs.map.mapObject;

    new L.Hash(map);
    map.addControl(new L.Control.Fullscreen());
    map.on("fullscreenchange", function () {
      map.invalidateSize();
      // if (map.isFullscreen()) {
      //     console.log('entered fullscreen');
      // } else {
      //     console.log('exited fullscreen');
      // }
    });

    //Bounds set slightly higher than actual world max to create a "padding" on the map
    map.setMaxBounds([
      [-90, -Infinity],
      [90, Infinity],
    ]);

    if (
      localStorage.getItem("map_" + this.map_id) &&
      !this.submit_data.map_token
    ) {
      this.submit_data.map_token = localStorage.getItem("map_" + this.map_id);
    } else if (this.submit_data.token) {
      localStorage["map_" + this.map_id] = this.submit_data.map_token;
    }
    //this.getCategories()
  },

  computed: {
    canPost() {
      if (this.submit_data.map_token) {
        return "yes";
      }
      return this.users_can_create_markers;
    },
    activeMarkers() {
      if (this.initial_markers) {
        return this.markers;
      } else if (this.markers) {
        return this.markers.filter(function (marker) {
          if (marker.expires_at == null) {
            return true;
          }
          return Vue.moment(marker.expires_at).isBefore(Vue.moment());
          //return new Date() <= Date(Date.parse(marker.expires_at.replace(/-/g, '/')))
        });
      } else {
        return [];
      }
    },
    supportsAr() {
      return (
        navigator.geolocation &&
        "geolocation" in navigator &&
        navigator.mediaDevices &&
        "mediaDevices" in navigator &&
        // Check if the device has accelerometer and gyroscope
        window.DeviceOrientationEvent &&
        "DeviceOrientationEvent" in window
      );
    },
  },

  watch: {
    initial_markers(newValue) {
      this.markers = newValue;
      //$emit('markers-count-change', newValue);
    },
    map_categories(newValue) {
      this.categories = newValue;
      //$emit('markers-count-change', newValue);
    },
    map_id(newValue) {
      this.getMarkers();
    },
  },

  methods: {
    canDeletePost(marker) {
      if (this.isMarkerExpired(marker.expires_at)) {
        return false;
      }
      if (this.submit_data.map_token) {
        return true;
      }
      return this.inLocalStorageKey(marker.id);
    },
    canMarkAsSpamPost(marker) {
      if (this.inLocalStorageKey(marker.id)) {
        return false;
      }
      if (this.submit_data.map_token) {
        return true;
      }
      return false;
    },
    isMarkerExpired(expiration_date) {
      if (!expiration_date) {
        return false;
      }
      // var now = new Date();
      // var marker_date = new Date(Date.parse(expiration_date.replace(/-/g, '/')));
      // if (now > marker_date) {
      //     return true;
      // }
      return Vue.moment(expiration_date).isBefore(Vue.moment());
    },
    addMarkerMenu(event) {
      this.$refs.hello_popup.mapObject.openPopup(event.latlng);
      if (!this.submit_data.category) {
        document.querySelector(".multiselect").focus();
      }
      this.submit_data.lat = event.latlng.lat;
      this.submit_data.lng = event.latlng.lng;
    },
    async handleOpenedPopup(event, id) {
      this.open_marker = this.markers.findIndex((e) => e.id === id);
      if (
        this.markers[this.open_marker] &&
        this.markers[this.open_marker].marker
      ) {
        // console.log(this.markers[this.open_marker].marker);
      } else {
        Vue.set(this.markers[this.open_marker], "marker", {
          label: "One sec, we're fetching the address...",
        });
        const results = await this.geosearchOptions.provider.search({
          query: event.latlng.lat + " " + event.latlng.lng,
        });
        Vue.set(this.markers[this.open_marker], "marker", results[0]);
      }
      //this.$refs.hello_popup.mapObject.closePopup();
    },
    openPopup(event) {
      this.debouncedRenderTimeago();
    },
    renderTimeago() {
      timeago.render(document.querySelectorAll(".timestamp"));
    },
    addTag(newTag) {
      const tag = {
        category_id: -1,
        name: newTag,
        icon: "/images/marker-01.svg",
      };
      this.categories.push(tag);
      this.fullCategory = tag;
      this.submit_data.category = tag.category_id;
      this.submit_data.category_name = tag.name;
      //this.addMarker()
    },
    handleSelectInput(val) {
      //this.fullCategory = val
      this.submit_data.category = val.id;
      this.submit_data.category_name = val.name;
      // this.addMarker()
    },
    inLocalStorageKey(id) {
      if (localStorage["post_" + id]) {
        return true;
      }
      return false;
    },
    asyncFind(query) {
      if (query.length < 3) {
        return false;
      }
      this.submit_data.loading = true;
      this.query = query;
      this.debouncedGetCategories();
    },
    getMarkers() {
      return axios
        .get("/api/maps/" + this.map_id + "/markers")
        .then((response) => (this.markers = response.data));
    },
    getCategories(query) {
      return axios
        .get("/api/categories?query=" + this.query)
        .then((response) => {
          this.categories = response.data;
          this.submit_data.loading = false;
        });
    },
    deleteMarker(id) {
      this.submit_data.loading = true;
      return axios
        .delete("/api/maps/" + this.map_id + "/markers/" + id, {
          data: {
            token: localStorage["post_" + id],
            map_token: this.submit_data.map_token,
          },
        })
        .then((res) => {
          this.handleDeletedMarker(id);
          this.submit_data.loading = false;
        });
    },
    markAsSpam(id) {
      this.submit_data.loading = true;
      return axios
        .put("/api/maps/" + this.map_id + "/markers/" + id, {
          token: localStorage["post_" + id],
          map_token: this.submit_data.map_token,
          is_spam: true,
        })
        .then((res) => {
          this.handleDeletedMarker(id);
          this.submit_data.loading = false;
          this.$notify({
            type: "success",
            title: "You marked this marker as spam",
            text: "Thank you for keeping our maps clean!",
            duration: 5000,
            speed: 750,
          });
        });
    },
    handleDeletedMarker(id) {
      this.markers = this.markers.filter((e) => e.id !== id);
      localStorage.removeItem("post_" + id);
      this.$emit("marker-delete", id);
    },
    addMarker() {
      this.submit_data.loading = true;
      return axios
        .post("/api/maps/" + this.map_id + "/markers", this.submit_data) // change this to post )
        .then((res) => {
          this.$refs.hello_popup.mapObject.closePopup();
          this.submit_data.loading = false;
          this.submit_data.description = "";
          if (!this.initial_markers) {
            this.markers.push(res.data);
          }
          localStorage["post_" + res.data.id] = res.data.token;
          dataLayer.push({ event: "marker-create" });
          this.$emit("marker-create", res.data);
        })
        .catch((error) => {
          this.submit_data.loading = false;
          console.log(error);
          if (error.response.data.errors) {
            var message = Object.entries(error.response.data.errors)
              .map(
                ([error_name, error_value], i) =>
                  `${error_name}: ${error_value[0]} | `
              )
              .join("\n");
          } else {
            var message = error.response.data.message;
          }
          this.$notify({
            type: "error",
            title: "There was an error creating this marker",
            text: message,
            duration: 8000,
          });
        });
    },
  },
};
</script>
<style>
@import "~leaflet.markercluster/dist/MarkerCluster.css";
@import "~leaflet.markercluster/dist/MarkerCluster.Default.css";
@import "~vue-multiselect/dist/vue-multiselect.min.css";
@import "https://unpkg.com/leaflet-geosearch@2.6.0/assets/css/leaflet.css";
@import "https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css";

.map-notification {
  position: fixed;
  bottom: 1rem;
  z-index: 1002;
  left: 1rem;
  right: 1rem;
}

.your_custom_class .multiselect__option--highlight,
.your_custom_class .multiselect__option::after {
  background: var(--primary);
}
</style>
