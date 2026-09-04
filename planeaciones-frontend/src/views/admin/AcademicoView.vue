<template>
  <AppShell>
    <div class="sec">
      <div class="sec-hdr">
        <div class="sec-num"><GraduationCap :size="20" /></div>
        <div>
          <h2>Carreras y Especialidades</h2>
          <p>Consulta, crea, edita y activa o desactiva los catálogos académicos.</p>
        </div>
      </div>

      <!-- Tabs -->
      <div class="tabs">
        <button class="tab" :class="{ active: tab === 'carreras' }" @click="tab = 'carreras'">
          <GraduationCap :size="16" /> Carreras
        </button>
        <button class="tab" :class="{ active: tab === 'especialidades' }" @click="tab = 'especialidades'">
          <BookOpen :size="16" /> Especialidades
        </button>
        <button class="tab" :class="{ active: tab === 'asignaturas' }" @click="tab = 'asignaturas'">
          <FileText :size="16" /> Asignaturas
        </button>
      </div>

      <CarrerasPanel v-if="tab === 'carreras'" />
      <EspecialidadesPanel v-else-if="tab === 'especialidades'" />
      <AsignaturasPanel v-else />
    </div>
  </AppShell>
</template>

<script setup>
import { ref } from 'vue'
import { GraduationCap, BookOpen, FileText } from 'lucide-vue-next'
import AppShell from '@/components/AppShell.vue'
import CarrerasPanel from './CarrerasPanel.vue'
import EspecialidadesPanel from './EspecialidadesPanel.vue'
import AsignaturasPanel from './AsignaturasPanel.vue'

const tab = ref('carreras')
</script>