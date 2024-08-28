<script setup lang="ts">
    import Header from '../components/header.vue';
    import Footer from '../components/footer.vue';
</script>
<template>
    <div>
      <Header />
      <slot />
      <Footer />
    </div>
</template>