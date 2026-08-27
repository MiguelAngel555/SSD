<template>
  <div class="firma-pad" :class="{ 'is-disabled': disabled }">
    <canvas
      ref="canvasEl"
      class="firma-canvas"
      width="600"
      height="180"
      @pointerdown="!disabled && iniciar($event)"
      @pointermove="!disabled && dibujar($event)"
      @pointerup="!disabled && terminar()"
      @pointerleave="!disabled && terminar()"
    ></canvas>
    <div class="firma-pad-actions">
      <span class="sz-xs" style="color:var(--text-300)">
        {{ disabled ? 'Deshabilitado: ya subiste un documento firmado.' : 'Dibuja tu firma con el mouse o el dedo (pantalla táctil).' }}
      </span>
      <button type="button" class="btn btn-ghost btn-sm" :disabled="disabled" @click="limpiar">Borrar firma</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

defineProps({ disabled: { type: Boolean, default: false } })
const emit = defineEmits(['update:modelValue'])

const canvasEl = ref(null)
let ctx = null
let dibujando = false
let tieneTrazo = false

onMounted(() => {
  ctx = canvasEl.value.getContext('2d')
  ctx.lineWidth = 2.2
  ctx.lineCap = 'round'
  ctx.strokeStyle = '#1a1a1a'
})

function posicion(evento) {
  const rect = canvasEl.value.getBoundingClientRect()
  const escalaX = canvasEl.value.width / rect.width
  const escalaY = canvasEl.value.height / rect.height
  return {
    x: (evento.clientX - rect.left) * escalaX,
    y: (evento.clientY - rect.top) * escalaY,
  }
}

function iniciar(evento) {
  dibujando = true
  const { x, y } = posicion(evento)
  ctx.beginPath()
  ctx.moveTo(x, y)
}

function dibujar(evento) {
  if (!dibujando) return
  const { x, y } = posicion(evento)
  ctx.lineTo(x, y)
  ctx.stroke()
  tieneTrazo = true
}

function terminar() {
  if (!dibujando) return
  dibujando = false
  if (tieneTrazo) {
    emit('update:modelValue', canvasEl.value.toDataURL('image/png'))
  }
}

function limpiar() {
  ctx.clearRect(0, 0, canvasEl.value.width, canvasEl.value.height)
  tieneTrazo = false
  emit('update:modelValue', null)
}

defineExpose({ limpiar })
</script>

<style scoped>
.firma-pad {
  border: 1px dashed var(--border-200, #ccc);
  border-radius: 8px;
  padding: 8px;
  background: #fff;
}

.firma-pad.is-disabled {
  opacity: 0.5;
  pointer-events: none;
}

.firma-canvas {
  width: 100%;
  height: 160px;
  touch-action: none;
  cursor: crosshair;
  background:
    linear-gradient(to bottom, transparent 85%, #ccc 85%, #ccc 86%, transparent 86%);
}

.firma-pad-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 6px;
  gap: 8px;
}
</style>
