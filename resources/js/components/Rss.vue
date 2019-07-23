<template>
    <div class="small">
      <p>
        <a target="_BLANK" v-if="title" :href="link">{{ title }} </a>
            <small v-if="date"> {{ date }}
                <span class="text-muted" v-if="source"> {{ source }} </span>
            </small>
        </p>
    </div>
</template>
<script>
    export default {
        props : ['title', 'date', 'source', 'link']
    }
</script>