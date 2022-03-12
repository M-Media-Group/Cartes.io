"use strict";(self.webpackChunksite=self.webpackChunksite||[]).push([[391],{3798:(e,a,n)=>{n.d(a,{Z:()=>l});var t=n(4015),s=n.n(t),r=n(3645),i=n.n(r)()(s());i.push([e.id,"#formControlRange{direction:rtl}","",{version:3,sources:["webpack://./resources/js/components/MapContainerComponent.vue"],names:[],mappings:"AA6XA,kBACA,aACA",sourcesContent:['<template>\n  <div>\n    <map-component\n      v-if="map"\n      :map_id="map.uuid"\n      :map_token="map_token"\n      style="height: 65vh"\n      :users_can_create_markers="map.users_can_create_markers"\n      :map_categories="categories"\n      :initial_markers="activeMarkers"\n      v-on:marker-create="handleMarkerCreate"\n      v-on:marker-delete="handleMarkerDelete"\n      :user="user"\n      :linkOption="map.options ? map.options.links : null"\n    ></map-component>\n    <div v-else style="height: 65vh" class="row align-items-center bg-dark">\n      <div class="col text-center">\n        <div>Cartes.io</div>\n        <p class="text-muted mb-0">Contacting planet Earth...</p>\n      </div>\n    </div>\n    <div class="container">\n      <div class="row justify-content-center mt-5">\n        <div class="col-md-12" style="max-width: 950px">\n          <map-details-component\n            :map_id="map.uuid"\n            :map_token="map_token"\n            :map="map"\n            v-on:map-update="handleMapUpdate"\n          >\n            <map-markers-feed-component\n              v-if="hasLiveData"\n              :markers="activeMarkers"\n              @showAllMarkers="map_settings.show_all = true"\n            ></map-markers-feed-component>\n            <div class="card bg-dark text-white mb-3">\n              <div\n                class="card-header"\n                data-toggle="collapse"\n                data-target="#displayCollapse"\n                aria-expanded="false"\n                aria-controls="displayCollapse"\n                style="cursor: pointer"\n              >\n                <i class="fa fa-sliders"></i> Map display options\n              </div>\n              <div class="card-body collapse" id="displayCollapse">\n                <div class="form-group row" v-if="!map_settings.show_all">\n                  <label class="col-md-12 col-form-label" for="formControlRange"\n                    >Time slider\n                    <small v-if="map_settings.mapSelectedAge > 0"\n                      >(showing map as of\n                      {{ map_settings.mapSelectedAge }} minutes ago)</small\n                    >\n                    <small v-else>(showing live map)</small>\n                  </label>\n                  <div class="col-md-12">\n                    <input\n                      type="range"\n                      class="form-control-range w-100"\n                      id="formControlRange"\n                      :max="mapAgeInMinutes"\n                      step="5"\n                      min="0"\n                      v-model="map_settings.mapSelectedAge"\n                    />\n                  </div>\n                </div>\n                <div class="form-group row">\n                  <label class="col-md-12 col-form-label"\n                    >Visible markers</label\n                  >\n                  <div class="col-md-12">\n                    <div class="form-check">\n                      <input\n                        type="checkbox"\n                        id="show_all_checkbox"\n                        v-model="map_settings.show_all"\n                      />\n                      <label class="form-check-label" for="show_all_checkbox">\n                        Show all markers\n                      </label>\n                    </div>\n                  </div>\n                </div>\n                <div class="form-group row">\n                  <label class="col-md-12 col-form-label">Related maps</label>\n                  <div class="col-md-12">\n                    <div class="form-check">\n                      <input\n                        type="checkbox"\n                        id="show_related_checkbox"\n                        v-model="map_settings.show_related"\n                      />\n                      <label\n                        class="form-check-label"\n                        for="show_related_checkbox"\n                      >\n                        Show related maps\n                      </label>\n                    </div>\n                  </div>\n                </div>\n              </div>\n            </div>\n            \x3c!--                         <api-data-transformer-component v-on:markers-updated="handleApiMarkers"></api-data-transformer-component>\n --\x3e\n          </map-details-component>\n          <h2 class="mt-5" v-if="nonSpamMarkers && nonSpamMarkers.length > 0">\n            Map stats\n          </h2>\n          <div class="row" v-if="nonSpamMarkers && nonSpamMarkers.length > 0">\n            <div class="col-md-6">\n              <h3>Total markers</h3>\n              <div class="jumbotron jumbotron-fluid bg-dark rounded">\n                <div class="container">\n                  <div class="display-4 text-center">\n                    {{ nonSpamMarkers.length }}\n                  </div>\n                  <p class="lead text-center">All the markers created.</p>\n                </div>\n              </div>\n            </div>\n            <div class="col-md-6">\n              <h3>Active markers</h3>\n              <div class="jumbotron jumbotron-fluid bg-dark rounded">\n                <div class="container">\n                  <div class="display-4 text-center">\n                    {{ nonSpamMarkers.length - expiredMarkers.length }}\n                  </div>\n                  <p class="lead text-center">\n                    Markers that are currently live.\n                  </p>\n                </div>\n              </div>\n            </div>\n          </div>\n          <map-markers-chart-component\n            v-if="nonSpamMarkers && nonSpamMarkers.length > 0"\n            :markers="markers"\n          ></map-markers-chart-component>\n          <template v-if="map_settings.show_related && relatedMaps.length > 0">\n            <h2>Related maps</h2>\n            <map-card-component\n              v-for="(map, i) in relatedMaps"\n              :key="map.uuid"\n              :map="map"\n              :disable_map="i > 2 ? true : false"\n            ></map-card-component>\n          </template>\n        </div>\n      </div>\n    </div>\n  </div>\n</template>\n<script lang="ts">\nimport axios from "axios";\nimport Vue from "vue";\n\nexport default {\n  // props: [\'initial_map\', \'initial_markers\', \'initial_map_token\', \'user\'],\n\n  props: {\n    // Basic type check (`null` and `undefined` values will pass any type validation)\n    initial_map: Object,\n    // Multiple possible types\n    initial_markers: Object,\n    // Required string\n    initial_map_token: {\n      type: String,\n      required: false,\n    },\n    // Object with a default value\n    user: {\n      type: Object,\n      // Object or array defaults must be returned from\n      // a factory function\n      default: function () {\n        return {};\n      },\n    },\n  },\n\n  components: {},\n\n  data() {\n    return {\n      map: this.initial_map,\n      map_token: this.initial_map_token,\n      markers: this.initial_markers,\n      relatedMaps: [],\n      map_settings: {\n        show_all: false,\n        mapSelectedAge: 0,\n        show_related: true,\n      },\n    };\n  },\n\n  created() {\n    if (!this.markers) {\n      this.getAllMarkers();\n    }\n    this.listenForNewMarkers();\n    this.listenForDeletedMarkers();\n    this.getRelatedMaps();\n  },\n\n  mounted() {},\n\n  computed: {\n    mapAgeInMinutes() {\n      if (!this.map) {\n        return false;\n      }\n      return Math.abs(Vue.moment().diff(this.map.created_at, "minutes"));\n    },\n    nonSpamMarkers() {\n      if (!this.markers) {\n        return [];\n      }\n      return this.markers.filter(function (marker) {\n        if (marker.is_spam && !localStorage["post_" + marker.id]) {\n          return false;\n        }\n        return true;\n      });\n    },\n    activeMarkers() {\n      if (!this.markers) {\n        return [];\n      } else if (this.map_settings.show_all) {\n        return this.nonSpamMarkers;\n      }\n\n      let diff_date_time = Vue.moment().subtract(\n        this.map_settings.mapSelectedAge,\n        "minutes"\n      );\n\n      return this.nonSpamMarkers.filter(function (marker) {\n        if (\n          Vue.moment(marker.created_at).isSameOrBefore(\n            diff_date_time,\n            "minute"\n          ) &&\n          (marker.expires_at == null ||\n            Vue.moment(diff_date_time).isBefore(marker.expires_at))\n        ) {\n          return true;\n        }\n        return false;\n      });\n    },\n    expiredMarkers() {\n      if (!this.nonSpamMarkers) {\n        return [];\n      }\n      return this.nonSpamMarkers.filter(function (marker) {\n        if (!marker.expires_at) {\n          return false;\n        }\n        return Vue.moment().isAfter(Vue.moment(marker.expires_at));\n        //return new Date() > new Date(Date.parse(marker.expires_at.replace(/-/g, \'/\')))\n      });\n    },\n    hasLiveData() {\n      if (!this.map) {\n        return false;\n      }\n      if (this.map.users_can_create_markers === "no") {\n        return false;\n      }\n      if (this.markers < 1) {\n        return false;\n      }\n      return true;\n    },\n    categories() {\n      if (!this.markers) {\n        return [];\n      }\n      var map1 = this.markers.map((x) => x.category);\n      return (\n        map1\n          .map((e) => e.id)\n          // store the indexes of the unique objects\n          .map((e, i, final) => final.indexOf(e) === i && i)\n          // eliminate the false indexes & return unique objects\n          .filter((e) => map1[e])\n          .map((e) => map1[e])\n      );\n    },\n  },\n\n  watch: {},\n\n  methods: {\n    groupBy(list, keyGetter) {\n      const map = new Map();\n      list.forEach((item) => {\n        const key = keyGetter(item);\n        const collection = map.get(key);\n        if (!collection) {\n          map.set(key, [item]);\n        } else {\n          collection.push(item);\n        }\n      });\n      return map;\n    },\n\n    getAllMarkers() {\n      axios\n        .get("/api/maps/" + this.map.uuid + "/markers?show_expired=true")\n        .then((response) => (this.markers = response.data));\n    },\n\n    getRelatedMaps() {\n      if (!this.map_settings.show_related) {\n        return;\n      }\n\n      axios\n        .get("/api/maps/" + this.map.uuid + "/related")\n        .then((response) => (this.relatedMaps = response.data));\n    },\n\n    listenForNewMarkers() {\n      window.Echo.channel("maps." + this.map.uuid).listen(\n        "MarkerCreated",\n        (e) => {\n          // this.$notify("A new marker was just added")\n          this.$notify({\n            type: "success",\n            title: \'"\' + e.marker.category.name + \'" marker was just added\',\n            text: e.marker.description,\n          });\n          this.handleMarkerCreate(e.marker);\n        }\n      );\n    },\n\n    listenForDeletedMarkers() {\n      window.Echo.channel("maps." + this.map.uuid).listen(\n        "MarkerDeleted",\n        (e) => {\n          this.handleMarkerDelete(e.marker.id);\n        }\n      );\n    },\n\n    handleMarkerCreate(marker) {\n      this.markers.push(marker);\n      this.getRelatedMaps();\n    },\n\n    handleMarkerDelete(id) {\n      this.$notify("A marker was just deleted");\n      this.markers = this.markers.filter((e) => e.id !== id);\n      this.getRelatedMaps();\n    },\n\n    handleMapUpdate(map) {\n      this.$notify("Information about this map has been updated");\n      this.map = map;\n    },\n\n    handleApiMarkers(markers) {\n      this.markers = markers;\n    },\n    markerInLocalStorageKey(id) {\n      if (localStorage["post_" + id]) {\n        return true;\n      }\n      return false;\n    },\n  },\n};\n<\/script>\n<style>\n#formControlRange {\n  direction: rtl;\n}\n</style>\n'],sourceRoot:""}]);const l=i},6391:(e,a,n)=>{n.r(a),n.d(a,{default:()=>d});var t=n(9669),s=n.n(t),r=n(538);const i={props:{initial_map:Object,initial_markers:Object,initial_map_token:{type:String,required:!1},user:{type:Object,default:function(){return{}}}},components:{},data:function(){return{map:this.initial_map,map_token:this.initial_map_token,markers:this.initial_markers,relatedMaps:[],map_settings:{show_all:!1,mapSelectedAge:0,show_related:!0}}},created:function(){this.markers||this.getAllMarkers(),this.listenForNewMarkers(),this.listenForDeletedMarkers(),this.getRelatedMaps()},mounted:function(){},computed:{mapAgeInMinutes:function(){return!!this.map&&Math.abs(r.default.moment().diff(this.map.created_at,"minutes"))},nonSpamMarkers:function(){return this.markers?this.markers.filter((function(e){return!(e.is_spam&&!localStorage["post_"+e.id])})):[]},activeMarkers:function(){if(!this.markers)return[];if(this.map_settings.show_all)return this.nonSpamMarkers;var e=r.default.moment().subtract(this.map_settings.mapSelectedAge,"minutes");return this.nonSpamMarkers.filter((function(a){return!(!r.default.moment(a.created_at).isSameOrBefore(e,"minute")||null!=a.expires_at&&!r.default.moment(e).isBefore(a.expires_at))}))},expiredMarkers:function(){return this.nonSpamMarkers?this.nonSpamMarkers.filter((function(e){return!!e.expires_at&&r.default.moment().isAfter(r.default.moment(e.expires_at))})):[]},hasLiveData:function(){return!!this.map&&("no"!==this.map.users_can_create_markers&&!(this.markers<1))},categories:function(){if(!this.markers)return[];var e=this.markers.map((function(e){return e.category}));return e.map((function(e){return e.id})).map((function(e,a,n){return n.indexOf(e)===a&&a})).filter((function(a){return e[a]})).map((function(a){return e[a]}))}},watch:{},methods:{groupBy:function(e,a){var n=new Map;return e.forEach((function(e){var t=a(e),s=n.get(t);s?s.push(e):n.set(t,[e])})),n},getAllMarkers:function(){var e=this;s().get("/api/maps/"+this.map.uuid+"/markers?show_expired=true").then((function(a){return e.markers=a.data}))},getRelatedMaps:function(){var e=this;this.map_settings.show_related&&s().get("/api/maps/"+this.map.uuid+"/related").then((function(a){return e.relatedMaps=a.data}))},listenForNewMarkers:function(){var e=this;window.Echo.channel("maps."+this.map.uuid).listen("MarkerCreated",(function(a){e.$notify({type:"success",title:'"'+a.marker.category.name+'" marker was just added',text:a.marker.description}),e.handleMarkerCreate(a.marker)}))},listenForDeletedMarkers:function(){var e=this;window.Echo.channel("maps."+this.map.uuid).listen("MarkerDeleted",(function(a){e.handleMarkerDelete(a.marker.id)}))},handleMarkerCreate:function(e){this.markers.push(e),this.getRelatedMaps()},handleMarkerDelete:function(e){this.$notify("A marker was just deleted"),this.markers=this.markers.filter((function(a){return a.id!==e})),this.getRelatedMaps()},handleMapUpdate:function(e){this.$notify("Information about this map has been updated"),this.map=e},handleApiMarkers:function(e){this.markers=e},markerInLocalStorageKey:function(e){return!!localStorage["post_"+e]}}};var l=n(3379),o=n.n(l),m=n(3798),p={insert:"head",singleton:!1};o()(m.Z,p);m.Z.locals;const d=(0,n(1900).Z)(i,(function(){var e=this,a=e.$createElement,n=e._self._c||a;return n("div",[e.map?n("map-component",{staticStyle:{height:"65vh"},attrs:{map_id:e.map.uuid,map_token:e.map_token,users_can_create_markers:e.map.users_can_create_markers,map_categories:e.categories,initial_markers:e.activeMarkers,user:e.user,linkOption:e.map.options?e.map.options.links:null},on:{"marker-create":e.handleMarkerCreate,"marker-delete":e.handleMarkerDelete}}):n("div",{staticClass:"row align-items-center bg-dark",staticStyle:{height:"65vh"}},[n("div",{staticClass:"col text-center"},[n("div",[e._v("Cartes.io")]),e._v(" "),n("p",{staticClass:"text-muted mb-0"},[e._v("Contacting planet Earth...")])])]),e._v(" "),n("div",{staticClass:"container"},[n("div",{staticClass:"row justify-content-center mt-5"},[n("div",{staticClass:"col-md-12",staticStyle:{"max-width":"950px"}},[n("map-details-component",{attrs:{map_id:e.map.uuid,map_token:e.map_token,map:e.map},on:{"map-update":e.handleMapUpdate}},[e.hasLiveData?n("map-markers-feed-component",{attrs:{markers:e.activeMarkers},on:{showAllMarkers:function(a){e.map_settings.show_all=!0}}}):e._e(),e._v(" "),n("div",{staticClass:"card bg-dark text-white mb-3"},[n("div",{staticClass:"card-header",staticStyle:{cursor:"pointer"},attrs:{"data-toggle":"collapse","data-target":"#displayCollapse","aria-expanded":"false","aria-controls":"displayCollapse"}},[n("i",{staticClass:"fa fa-sliders"}),e._v(" Map display options\n             ")]),e._v(" "),n("div",{staticClass:"card-body collapse",attrs:{id:"displayCollapse"}},[e.map_settings.show_all?e._e():n("div",{staticClass:"form-group row"},[n("label",{staticClass:"col-md-12 col-form-label",attrs:{for:"formControlRange"}},[e._v("Time slider\n                   "),e.map_settings.mapSelectedAge>0?n("small",[e._v("(showing map as of\n                     "+e._s(e.map_settings.mapSelectedAge)+" minutes ago)")]):n("small",[e._v("(showing live map)")])]),e._v(" "),n("div",{staticClass:"col-md-12"},[n("input",{directives:[{name:"model",rawName:"v-model",value:e.map_settings.mapSelectedAge,expression:"map_settings.mapSelectedAge"}],staticClass:"form-control-range w-100",attrs:{type:"range",id:"formControlRange",max:e.mapAgeInMinutes,step:"5",min:"0"},domProps:{value:e.map_settings.mapSelectedAge},on:{__r:function(a){return e.$set(e.map_settings,"mapSelectedAge",a.target.value)}}})])]),e._v(" "),n("div",{staticClass:"form-group row"},[n("label",{staticClass:"col-md-12 col-form-label"},[e._v("Visible markers")]),e._v(" "),n("div",{staticClass:"col-md-12"},[n("div",{staticClass:"form-check"},[n("input",{directives:[{name:"model",rawName:"v-model",value:e.map_settings.show_all,expression:"map_settings.show_all"}],attrs:{type:"checkbox",id:"show_all_checkbox"},domProps:{checked:Array.isArray(e.map_settings.show_all)?e._i(e.map_settings.show_all,null)>-1:e.map_settings.show_all},on:{change:function(a){var n=e.map_settings.show_all,t=a.target,s=!!t.checked;if(Array.isArray(n)){var r=e._i(n,null);t.checked?r<0&&e.$set(e.map_settings,"show_all",n.concat([null])):r>-1&&e.$set(e.map_settings,"show_all",n.slice(0,r).concat(n.slice(r+1)))}else e.$set(e.map_settings,"show_all",s)}}}),e._v(" "),n("label",{staticClass:"form-check-label",attrs:{for:"show_all_checkbox"}},[e._v("\n                       Show all markers\n                     ")])])])]),e._v(" "),n("div",{staticClass:"form-group row"},[n("label",{staticClass:"col-md-12 col-form-label"},[e._v("Related maps")]),e._v(" "),n("div",{staticClass:"col-md-12"},[n("div",{staticClass:"form-check"},[n("input",{directives:[{name:"model",rawName:"v-model",value:e.map_settings.show_related,expression:"map_settings.show_related"}],attrs:{type:"checkbox",id:"show_related_checkbox"},domProps:{checked:Array.isArray(e.map_settings.show_related)?e._i(e.map_settings.show_related,null)>-1:e.map_settings.show_related},on:{change:function(a){var n=e.map_settings.show_related,t=a.target,s=!!t.checked;if(Array.isArray(n)){var r=e._i(n,null);t.checked?r<0&&e.$set(e.map_settings,"show_related",n.concat([null])):r>-1&&e.$set(e.map_settings,"show_related",n.slice(0,r).concat(n.slice(r+1)))}else e.$set(e.map_settings,"show_related",s)}}}),e._v(" "),n("label",{staticClass:"form-check-label",attrs:{for:"show_related_checkbox"}},[e._v("\n                       Show related maps\n                     ")])])])])])])],1),e._v(" "),e.nonSpamMarkers&&e.nonSpamMarkers.length>0?n("h2",{staticClass:"mt-5"},[e._v("\n           Map stats\n         ")]):e._e(),e._v(" "),e.nonSpamMarkers&&e.nonSpamMarkers.length>0?n("div",{staticClass:"row"},[n("div",{staticClass:"col-md-6"},[n("h3",[e._v("Total markers")]),e._v(" "),n("div",{staticClass:"jumbotron jumbotron-fluid bg-dark rounded"},[n("div",{staticClass:"container"},[n("div",{staticClass:"display-4 text-center"},[e._v("\n                   "+e._s(e.nonSpamMarkers.length)+"\n                 ")]),e._v(" "),n("p",{staticClass:"lead text-center"},[e._v("All the markers created.")])])])]),e._v(" "),n("div",{staticClass:"col-md-6"},[n("h3",[e._v("Active markers")]),e._v(" "),n("div",{staticClass:"jumbotron jumbotron-fluid bg-dark rounded"},[n("div",{staticClass:"container"},[n("div",{staticClass:"display-4 text-center"},[e._v("\n                   "+e._s(e.nonSpamMarkers.length-e.expiredMarkers.length)+"\n                 ")]),e._v(" "),n("p",{staticClass:"lead text-center"},[e._v("\n                   Markers that are currently live.\n                 ")])])])])]):e._e(),e._v(" "),e.nonSpamMarkers&&e.nonSpamMarkers.length>0?n("map-markers-chart-component",{attrs:{markers:e.markers}}):e._e(),e._v(" "),e.map_settings.show_related&&e.relatedMaps.length>0?[n("h2",[e._v("Related maps")]),e._v(" "),e._l(e.relatedMaps,(function(e,a){return n("map-card-component",{key:e.uuid,attrs:{map:e,disable_map:a>2}})}))]:e._e()],2)])])],1)}),[],!1,null,null,null).exports}}]);
//# sourceMappingURL=391.js.map