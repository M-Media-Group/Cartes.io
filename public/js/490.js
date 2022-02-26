(self.webpackChunksite=self.webpackChunksite||[]).push([[490],{131:(e,n,t)=>{"use strict";t.d(n,{Z:()=>o});var r=t(4015),a=t.n(r),s=t(3645),i=t.n(s)()(a());i.push([e.id,"li.feed-element{cursor:pointer;transition:.1s}li.feed-element:hover{background-color:var(--primary)!important}.blink{-webkit-animation:blinker 1.5s cubic-bezier(.5,0,1,1) infinite;animation:blinker 1.5s cubic-bezier(.5,0,1,1) infinite}@-webkit-keyframes blinker{0%{opacity:1;transform:scale(0)}to{opacity:0;transform:scale(1)}}@keyframes blinker{0%{opacity:1;transform:scale(0)}to{opacity:0;transform:scale(1)}}","",{version:3,sources:["webpack://./resources/js/components/MapMarkersFeedComponent.vue"],names:[],mappings:"AA2HA,gBACA,cAAA,CACA,cACA,CAEA,sBACA,yCACA,CAEA,OACA,8DAAA,CAAA,sDACA,CAEA,2BACA,GACA,SAAA,CACA,kBACA,CACA,GACA,SAAA,CACA,kBACA,CACA,CATA,mBACA,GACA,SAAA,CACA,kBACA,CACA,GACA,SAAA,CACA,kBACA,CACA",sourcesContent:['<template>\n  <div>\n    <ul id="marker_feed" class="list-unstyled px-0 pb-3 mb-3 bg-dark card">\n      <li\n        class="media p-3 card-header"\n        data-toggle="collapse"\n        data-target="#marker_feed_markers"\n        aria-expanded="false"\n        aria-controls="marker_feed_markers"\n        style="cursor: pointer"\n      >\n        <div class="media-body">\n          <h5 class="mt-0 mb-0" v-if="is_connected_live">\n            <i class="fa fa-circle text-danger blink"></i> Live feed\n          </h5>\n          <h5 class="mt-0 mb-0" v-else>Feed</h5>\n        </div>\n      </li>\n      <div\n        id="marker_feed_markers"\n        class="collapse show"\n        style="max-height: 57vh; overflow-y: scroll"\n      >\n        <template v-if="limitedMarkers.length > 0">\n          <li\n            class="\n              media\n              ml-3\n              mr-3\n              p-3\n              mb-3\n              bg-secondary\n              text-white\n              card\n              feed-element\n            "\n            v-for="marker in limitedMarkers"\n            :key="\'marker_feed_\' + marker.id"\n            @click="handleClick(marker)"\n          >\n            <div class="media-body">\n              <h5 class="mt-0 mb-1">{{ marker.category.name }}</h5>\n              <p class="mt-0 mb-1" v-html="marker.description"></p>\n              <span class="timestamp small" :datetime="marker.updated_at">{{\n                marker.updated_at\n              }}</span>\n            </div>\n          </li>\n        </template>\n        <template v-else>\n          <div class="text-center text-muted p-3">\n            There\'s no active markers at this time.\n          </div>\n        </template>\n      </div>\n    </ul>\n  </div>\n</template>\n<script lang="ts">\nexport default {\n  props: ["markers"],\n  data() {\n    return {\n      is_connected_live: false,\n    };\n  },\n  mounted() {\n    this.listenForSocketConnect();\n    this.listenForSocketDisconnect();\n    // timeago.render(document.querySelectorAll(\'.timestamp\'))\n  },\n  computed: {\n    limitedMarkers() {\n      if (!this.markers) {\n        return [];\n      }\n      var sorted_markers = this.markers.sort(function (a, b) {\n        return a.created_at < b.created_at;\n      });\n      //return sorted_markers;\n      return sorted_markers.slice(0, 50);\n    },\n  },\n\n  watch: {\n    markers(newValue) {\n      this.handleNewMarker();\n    },\n  },\n\n  methods: {\n    handleClick(marker) {\n      $("html").animate(\n        {\n          scrollTop: $("#app").offset().top,\n        },\n        200\n      );\n\n      this.$root.$emit("flyTo", marker.location.coordinates);\n    },\n    listenForSocketConnect() {\n      window.Echo.connector.pusher.connection.bind("connected", () => {\n        this.is_connected_live = true;\n      });\n    },\n    listenForSocketDisconnect() {\n      window.Echo.connector.pusher.connection.bind("disconnected", () => {\n        this.is_connected_live = false;\n      });\n    },\n    handleNewMarker() {\n      $("#marker_feed_markers").animate(\n        {\n          scrollTop: $("#app").offset().top,\n        },\n        200\n      );\n    },\n  },\n};\n<\/script>\n<style >\nli.feed-element {\n  cursor: pointer;\n  transition: 0.1s;\n}\n\nli.feed-element:hover {\n  background-color: var(--primary) !important;\n}\n\n.blink {\n  animation: blinker 1.5s cubic-bezier(0.5, 0, 1, 1) infinite;\n}\n\n@keyframes blinker {\n  from {\n    opacity: 1;\n    transform: scale(0);\n  }\n  to {\n    opacity: 0;\n    transform: scale(1);\n  }\n}\n</style>\n'],sourceRoot:""}]);const o=i},4490:(e,n,t)=>{"use strict";t.r(n),t.d(n,{default:()=>c});const r={props:["markers"],data:function(){return{is_connected_live:!1}},mounted:function(){this.listenForSocketConnect(),this.listenForSocketDisconnect()},computed:{limitedMarkers:function(){return this.markers?this.markers.sort((function(e,n){return e.created_at<n.created_at})).slice(0,50):[]}},watch:{markers:function(e){this.handleNewMarker()}},methods:{handleClick:function(e){$("html").animate({scrollTop:$("#app").offset().top},200),this.$root.$emit("flyTo",e.location.coordinates)},listenForSocketConnect:function(){var e=this;window.Echo.connector.pusher.connection.bind("connected",(function(){e.is_connected_live=!0}))},listenForSocketDisconnect:function(){var e=this;window.Echo.connector.pusher.connection.bind("disconnected",(function(){e.is_connected_live=!1}))},handleNewMarker:function(){$("#marker_feed_markers").animate({scrollTop:$("#app").offset().top},200)}}};var a=t(3379),s=t.n(a),i=t(131),o={insert:"head",singleton:!1};s()(i.Z,o);i.Z.locals;const c=(0,t(1900).Z)(r,(function(){var e=this,n=e.$createElement,t=e._self._c||n;return t("div",[t("ul",{staticClass:"list-unstyled px-0 pb-3 mb-3 bg-dark card",attrs:{id:"marker_feed"}},[t("li",{staticClass:"media p-3 card-header",staticStyle:{cursor:"pointer"},attrs:{"data-toggle":"collapse","data-target":"#marker_feed_markers","aria-expanded":"false","aria-controls":"marker_feed_markers"}},[t("div",{staticClass:"media-body"},[e.is_connected_live?t("h5",{staticClass:"mt-0 mb-0"},[t("i",{staticClass:"fa fa-circle text-danger blink"}),e._v(" Live feed\n        ")]):t("h5",{staticClass:"mt-0 mb-0"},[e._v("Feed")])])]),e._v(" "),t("div",{staticClass:"collapse show",staticStyle:{"max-height":"57vh","overflow-y":"scroll"},attrs:{id:"marker_feed_markers"}},[e.limitedMarkers.length>0?e._l(e.limitedMarkers,(function(n){return t("li",{key:"marker_feed_"+n.id,staticClass:"\n            media\n            ml-3\n            mr-3\n            p-3\n            mb-3\n            bg-secondary\n            text-white\n            card\n            feed-element\n          ",on:{click:function(t){return e.handleClick(n)}}},[t("div",{staticClass:"media-body"},[t("h5",{staticClass:"mt-0 mb-1"},[e._v(e._s(n.category.name))]),e._v(" "),t("p",{staticClass:"mt-0 mb-1",domProps:{innerHTML:e._s(n.description)}}),e._v(" "),t("span",{staticClass:"timestamp small",attrs:{datetime:n.updated_at}},[e._v(e._s(n.updated_at))])])])})):[t("div",{staticClass:"text-center text-muted p-3"},[e._v("\n          There's no active markers at this time.\n        ")])]],2)])])}),[],!1,null,null,null).exports}}]);
//# sourceMappingURL=490.js.map