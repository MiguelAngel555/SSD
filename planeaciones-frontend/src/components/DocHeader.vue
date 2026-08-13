<template>
  <div class="doc-header">
    <!-- Logo Izquierda -->
    <div class="doc-header-logo-placeholder">
      <img src="/img/uth.webp" alt="Logo UTH" class="logo-img">
    </div>

    <!-- Texto Central -->
    <div class="doc-header-text">
      <h1>Universidad Tecnológica de Huejotzingo</h1>
      <h2 v-if="programa">{{ programa }}</h2>
      <h3>{{ subtitulo }}</h3>
    </div>

    <!-- Logo Derecha -->
    <div class="doc-header-logo-placeholder">
      <img src="/img/ut.png" alt="Logo UT" class="logo-img">
    </div>
  </div>
</template>

<script setup>
defineProps({
  subtitulo: { type: String, required: true },
  programa: { type: String, default: '' },
})
</script>