"use strict";(self.webpackChunksite=self.webpackChunksite||[]).push([[287],{7287:(t,a,s)=>{s.r(a),s.d(a,{default:()=>i});const e={props:["map","is_minimal","disable_map"],data:function(){return{}},mounted:function(){},computed:{},watch:{},methods:{}};const i=(0,s(1900).Z)(e,(function(){var t=this,a=t.$createElement,s=t._self._c||a;return s("div",{staticClass:"card bg-dark text-white mb-5",staticStyle:{"min-height":"250px"}},[t.disable_map?t._e():s("map-component",{staticStyle:{height:"50vh"},attrs:{map_id:t.map.uuid,map_categories:t.map.categories,users_can_create_markers:t.map.users_can_create_markers}}),t._v(" "),t.map.categories&&!t.disable_map?s("div",{staticClass:"pl-1 pr-1 d-flex",staticStyle:{top:"49vh","z-index":"1001",overflow:"scroll",width:"100%",position:"absolute"}},t._l(t.map.categories,(function(a){return s("a",{key:a.id,staticClass:"badge badge-secondary mr-1 mb-1",attrs:{href:"#"}},[t._v(t._s(a.name))])})),0):t._e(),t._v(" "),s("div",{staticClass:"card-body"},[s("h5",{staticClass:"card-title mt-3"},[t._v("\n      "+t._s(t.map.title?t.map.title:"Untitled map")+"\n    ")]),t._v(" "),t.is_minimal?t._e():s("p",{staticClass:"card-text"},[t._v("\n      "+t._s(t._f("truncate")(t.map.description,250,"..."))+"\n    ")]),t._v(" "),s("a",{staticClass:"btn btn-primary btn-block",attrs:{href:/maps/+t.map.uuid}},[t._v("Open map")])]),t._v(" "),t.is_minimal?t._e():s("div",{staticClass:"card-footer"},[s("p",{staticClass:"card-text small text-secondary"},[t._v("\n      "+t._s(t.map.markers_count)+" live markers · Updated\n      "),s("span",{staticClass:"timestamp",attrs:{datetime:t.map.updated_at}},[t._v(t._s(t.map.updated_at))])])])],1)}),[],!1,null,null,null).exports}}]);
//# sourceMappingURL=287.js.map