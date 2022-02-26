<template>
  <div v-if="markers && markers.length > 1" class="row">
    <div class="col-md-6">
      <h3>Marker type distribution</h3>
      <chart-pie-component
        :chart-data="datacollection"
        :height="225"
        style="width: 100%"
        class="mt-5 mb-5"
      ></chart-pie-component>
    </div>
    <div class="col-md-6">
      <h3>New markers by date</h3>
      <chart-line-component
        :chart-data="timedatacollection"
        :options="chartLineOptions"
        :height="225"
        style="width: 100%"
        class="mt-5 mb-5"
      ></chart-line-component>
    </div>
  </div>
  <div v-else>
    <p class="small">
      There's not enough markers on this map yet. More stats will be available
      after a few more markers are added.
    </p>
  </div>
</template>
<script lang="ts">
import Vue from "vue";

export default {
  props: ["markers"],
  data() {
    return {
      datacollection: {},
      timedatacollection: {},
      show_all: true,
      chartLineOptions: {
        onClick: this.handleClick,
        scales: {
          xAxes: [
            {
              type: "time",
              distribution: "linear",
              // time: {
              //     unit: 'hour'
              // }
            },
          ],
        },
      },
    };
  },
  mounted() {
    if (this.markers) {
      this.fillChartData();
      this.fillPieData();
    }
  },
  computed: {},

  watch: {
    markers(newValue) {
      this.fillChartData();
      this.fillPieData();
    },
  },

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
    fillChartData() {
      const grouped = this.groupBy(this.markers, (object) => {
        // console.log(object.created_at);
        return Vue.moment(object.created_at).startOf("hour").toString();
      });
      this.timedatacollection = {
        labels: Array.from(grouped).map((object) => object[0]),
        datasets: [
          {
            label: "Markers",
            borderColor: "#1C77C3",
            data: Array.from(grouped).map((object) => object[1].length),
          },
        ],
      };
    },
    fillPieData() {
      const groupedByCategory = this.groupBy(
        this.markers,
        (object) => object.category.name
      );

      this.datacollection = {
        labels: Array.from(groupedByCategory).map((object) => object[0]),
        datasets: [
          {
            label: "New markers",
            borderColor: "#1C77C3",
            // backgroundColor: '#1C77C3',
            data: Array.from(groupedByCategory).map(
              (object) => object[1].length
            ),
          },
        ],
      };
    },
    handleClick(point, event) {
      const item = event[0];
      let index = item["_index"];
      let date = item["_chart"].data.labels[index];
      let value = item["_chart"].data.datasets[0].data[index];
      this.$emit("clicked-date", {
        index: item._index,
        backgroundColor: item._view.backgroundColor,
        value: date,
      });
    },
  },
};
</script>
