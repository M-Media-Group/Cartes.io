<template>
    <div>
        <ul id="marker_feed" class="list-unstyled px-0 pb-3 mb-3 bg-dark card">
            <li class="media p-3 card-header" data-toggle="collapse" data-target="#marker_feed_markers" aria-expanded="false" aria-controls="marker_feed_markers" style="cursor: pointer;">
                <div class="media-body">
                    <h5 class="mt-0 mb-0" v-if="is_connected_live"><i class="fa fa-circle text-danger blink"></i> Live feed</h5>
                    <h5 class="mt-0 mb-0" v-else>Feed</h5>
                </div>
            </li>
            <div id="marker_feed_markers" class="collapse show" style="max-height:57vh; overflow-y: scroll;">
                <template v-if="limitedMarkers.length > 0">
                    <li class="media ml-3 mr-3 p-3 mb-3 bg-secondary text-white card feed-element" v-for="marker in limitedMarkers" :key="'marker_feed_'+marker.id" @click="handleClick(marker)">
                        <div class="media-body">
                            <h5 class="mt-0 mb-1">{{marker.category.name}}</h5>
                            <p class="mt-0 mb-1">{{marker.description}}</p>
                            <span class='timestamp small' :datetime="marker.updated_at">{{ marker.updated_at }}</span>
                        </div>
                    </li>
                </template>
                <template v-else>
                    <div class="text-center text-muted p-3">There's no active markers at this time.</div>
                </template>
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
        // timeago.render(document.querySelectorAll('.timestamp'))
    },
    computed: {
        limitedMarkers() {
            if (!this.markers) {
                return [];
            }
            var sorted_markers = this.markers.sort(function (a, b) {
              return a.created_at < b.created_at;
            });
            //return sorted_markers;
            return sorted_markers.slice(0,50);
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
<style >
li.feed-element {
    cursor: pointer;
    transition: 0.1s;
}

li.feed-element:hover {
   background-color: var(--primary) !important;
}

.blink {
    animation: blinker 1.5s cubic-bezier(.5, 0, 1, 1) infinite;
}

@keyframes blinker {
  from { opacity: 1; transform: scale(0); }
  to { opacity: 0; transform: scale(1); }
}
</style>
