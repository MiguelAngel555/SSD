<template>
  <AppShell>
    <div class="sec">
      <div class="sec-hdr">
        <div class="sec-num"><FileEdit :size="20" /></div>
        <div>
          <h2>Mis Secuencias Didácticas</h2>
          <p>Borradores en desarrollo e historial de secuencias validadas.</p>
        </div>
      </div>

      <div class="tabs">
        <button class="tab" :class="{ active: tab === 'borradores' }" @click="tab = 'borradores'">
          <FileEdit :size="16" /> Borradores
        </button>
        <button class="tab" :class="{ active: tab === 'historial' }" @click="tab = 'historial'">
          <History :size="16" /> Historial
        </button>
      </div>

      <BorradoresPanel v-if="tab === 'borradores'" />
      <HistorialPanel v-else />
    </div>
  </AppShell>
</template>

<script setup>
import { ref } from 'vue'
import { FileEdit, History } from 'lucide-vue-next'
import AppShell from '@/components/AppShell.vue'
import BorradoresPanel from './BorradoresPanel.vue'
import HistorialPanel from './HistorialPanel.vue'

const tab = ref('borradores')
</script>