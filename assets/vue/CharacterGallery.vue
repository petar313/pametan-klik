<script setup>
import { ref } from "vue";

// The home page gallery: karakondžulice that change on hover or tap, and
// mala Maja leading to the kids' and parents' sections.
// Each item: { src, hoverSrc, alt, href? }
defineProps({ items: { type: Array, required: true } });
const active = ref(null);
</script>

<template>
    <div class="gallery">
        <component
            :is="item.href ? 'a' : 'span'"
            v-for="(item, i) in items"
            :key="item.src"
            :href="item.href"
            class="gallery__item"
            :class="{ 'gallery__item--link': item.href }"
            @mouseenter="active = i"
            @mouseleave="active = null"
            @focus="active = i"
            @blur="active = null"
            @touchstart.passive="active = i"
        >
            <img :src="active === i ? item.hoverSrc : item.src" :alt="item.alt" width="240" height="200" />
        </component>
    </div>
</template>

<style scoped>
.gallery {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 0.25rem;
}
.gallery__item img { display: block; width: 100%; height: auto; }
.gallery__item--link { border-radius: 1rem; outline-offset: 4px; }

@media (max-width: 640px) {
    .gallery { grid-template-columns: repeat(2, 1fr); }
    /* On phones only mala Maja stays, as the two big buttons. */
    .gallery__item:not(.gallery__item--link) { display: none; }
}
</style>
