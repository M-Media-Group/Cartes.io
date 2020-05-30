<template>
    <div>
        <ul id="marker_feed" class="list-unstyled px-0 pb-3 mb-3 bg-dark card" v-if="is_connected_live && markers">
            <li class="media p-3">
                <div class="media-body">
                    <h5 class="mt-0 mb-1"><i class="fa fa-circle text-danger blink"></i> Live feed</h5>
                </div>
            </li>
            <div id="marker_feed_markers" style="max-height:70vh; overflow-y: scroll;">
                <li class="media ml-3 mr-3 p-3 mb-3 bg-secondary card" v-for="marker in limitedMarkers" :key="'marker_feed_'+marker.id" @click="handleClick(marker)">
                    <div class="media-body">
                        <h5 class="mt-0 mb-1">{{marker.category.name}}</h5>
                        Reported <span class='timestamp' :datetime="marker.updated_at">{{ marker.updated_at }}</span>
                    </div>
                </li>
            </div>
        </ul>
    </div>
</template>
<script>
export default {
    props: ['markers'],
    data() {
        return {
            is_connected_live: false
        }
    },
    mounted() {
        this.listenForSocketConnect()
        this.listenForSocketDisconnect()
    },
    computed: {
        limitedMarkers() {
            if (!this.markers) {
                return [];
            }
            var sorted_markers = this.markers.sort(function (a, b) {
              return a.created_at < b.created_at;
            });
            return sorted_markers;
            //return sorted_markers.slice(0,3);
        }
    },

    watch: {
        markers(newValue) {
            this.handleNewMarker()
        }
    },

    methods: {
        handleClick(marker){
            //$(window).animate({ scrollTop: $("#app").offset().top }, "fast");
            $('html').animate({
                scrollTop: $('#app').offset().top
            }, 200);
            //$(window).scrollTop( $("#app").offset().top );

            this.$root.$emit('flyTo', marker.location.coordinates);
        },
        listenForSocketConnect() {
            window.Echo.connector.pusher.connection.bind('connected', () => {
              this.is_connected_live = true
            });
        },
        listenForSocketDisconnect() {
            window.Echo.connector.pusher.connection.bind('disconnected', () => {
              this.is_connected_live = false
            });
        },
        handleNewMarker() {
            $('#marker_feed_markers').animate({
                scrollTop: $('#app').offset().top
            }, 200);
        }
    }
}

</script>
<style>
.blink {
    animation: blinker 1.5s cubic-bezier(.5, 0, 1, 1) infinite;
}

@keyframes blinker {
  from { opacity: 1; transform: scale(0); }
  to { opacity: 0; transform: scale(1); }
}
</style>
