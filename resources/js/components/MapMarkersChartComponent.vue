<template>
    <div>
        <h3>Marker type distribution</h3>
        <chart-pie-component :chart-data="datacollection" :height="225" style="width: 100%;" class="mt-5 mb-5"></chart-pie-component>
        <h3>New markers by date</h3>
        <chart-line-component :chart-data="timedatacollection" :options="{
    scales: {
      xAxes: [{
        type: 'time',
        distribution: 'linear',
                // time: {
                //     unit: 'hour'
                // }
      }]
    }
  }" :height="225" style="width: 100%;" class="mt-5 mb-5"></chart-line-component>
    </div>
</template>
<script>
export default {
    props: ['map_id'],
    data() {
        return {
            datacollection: {},
            timedatacollection: {},
            markers: []
        }
    },
    mounted() {
        this.getMarkers();
    },
    computed: {},

    watch: {

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
        fillData() {
            var data = this.markers
            const grouped = this.groupBy(data, object => object.created_at.slice(0, -6));

            const newArray = Array.from(grouped).map(object => object[1].length);
            const labelsArray = Array.from(grouped).map(object => object[0] + ':00');

            this.timedatacollection = {
                labels: labelsArray,
                datasets: [{
                    label: 'Markers',
                    borderColor: '#1C77C3',
                    data: newArray
                }]
            }


            const groupedByCategory = this.groupBy(data, object => object.category.name);

            const newCategoryArray = Array.from(groupedByCategory).map(object => object[1].length);
            const categoryLabelsArray = Array.from(groupedByCategory).map(object => object[0]);

            this.datacollection = {
                labels: categoryLabelsArray,
                datasets: [{
                    label: 'New markers',
                    borderColor: '#1C77C3',
                    // backgroundColor: '#1C77C3',
                    data: newCategoryArray
                }]
            }
        },
        getMarkers() {
            axios
                .get('/api/maps/' + this.map_id + '/incidents?show_expired=true')
                .then(response => {
                    this.markers = response.data
                    this.fillData();
                })
        },
    }
}

</script>
