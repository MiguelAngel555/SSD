<template>
  <AppShell>
    <!-- ============ HERO SECTION CON EFECTO 3D ============ -->
    <section class="hero-tilt" @mousemove="handleHeroMouseMove" @mouseleave="handleHeroMouseLeave" ref="heroRef">
      <div class="hero-content">
        <div class="hero-tag">
          <Settings :size="14" class="spin-slow" /> Administrador del Sistema
        </div>
        <h1>Panel de<br><span class="accent">Administración</span></h1>
        <p>Gestión centralizada de catálogos académicos y control de usuarios desde un entorno unificado.</p>
      </div>
      <div class="hero-glow"></div>
    </section>

    <!-- ============ CATÁLOGOS ============ -->
    <div class="sec">
      <div class="sec-hdr">
        <div class="sec-num">1</div>
        <div>
          <h2>Catálogos Académicos</h2>
          <p>Selecciona el módulo que deseas administrar hoy.</p>
        </div>
      </div>

      <div class="g2">
        <!-- Tarjeta 1: Carreras -->
        <router-link :to="{ name: 'admin-academico' }" class="card-tilt c-teal">
          <div class="card-inner">
            <div class="card-icon-wrap">
              <GraduationCap :size="28" />
            </div>
            <div class="card-info">
              <h3>Carreras y Especialidades</h3>
              <p>Ver, crear, editar, activar y desactivar carreras y especialidades vigentes.</p>
            </div>
            <div class="card-arrow-wrap">
              <ChevronRight :size="18" />
            </div>
          </div>
          <div class="card-shine"></div>
        </router-link>

        <!-- Tarjeta 2: Usuarios -->
        <router-link :to="{ name: 'admin-usuarios' }" class="card-tilt c-purple">
          <div class="card-inner">
            <div class="card-icon-wrap purple-icon">
              <Users :size="28" />
            </div>
            <div class="card-info">
              <h3>Gestión de Usuarios</h3>
              <p>Crear cuentas, asignar roles institucionales, materias y directores.</p>
            </div>
            <div class="card-arrow-wrap purple-arrow">
              <ChevronRight :size="18" />
            </div>
          </div>
          <div class="card-shine"></div>
        </router-link>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { ref } from 'vue'
import { Settings, GraduationCap, Users, ChevronRight } from 'lucide-vue-next'
import AppShell from '@/components/AppShell.vue'

// Lógica de efecto Parallax / Inclinación 3D para el Hero
const heroRef = ref(null)

function handleHeroMouseMove(e) {
  const card = heroRef.value
  if (!card) return
  const rect = card.getBoundingClientRect()
  const x = e.clientX - rect.left
  const y = e.clientY - rect.top
  const centerX = rect.width / 2
  const centerY = rect.height / 2
  const rotateX = ((y - centerY) / centerY) * -4
  const rotateY = ((x - centerX) / centerX) * 4

  card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-2px)`
}

function handleHeroMouseLeave() {
  const card = heroRef.value
  if (!card) return
  card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0)'
}
</script>

<style scoped>
/* ---- HERO CON EFECTO PROFUNDO ---- */
.hero-tilt {
  position: relative;
  overflow: hidden;
  border-radius: 24px;
  background: linear-gradient(135deg, #131825 0%, #0F172A 50%, #080C14 100%);
  border: 1px solid rgba(34, 211, 168, 0.2);
  padding: 42px 48px;
  margin-bottom: 38px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7), inset 0 1px 0 rgba(255, 255, 255, 0.1);
  transition: transform 0.1s ease-out, box-shadow 0.3s ease;
  will-change: transform;
}

.hero-tilt:hover {
  box-shadow: 0 30px 60px -12px rgba(34, 211, 168, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.2);
  border-color: rgba(34, 211, 168, 0.4);
}

.hero-content {
  position: relative;
  z-index: 2;
}

.hero-tag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(34, 211, 168, 0.1);
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 12.5px;
  font-weight: 600;
  color: var(--brand, #22D3A8);
  margin-bottom: 18px;
  border: 1px solid rgba(34, 211, 168, 0.25);
}

.hero h1 {
  font-family: 'Sora', sans-serif;
  font-weight: 800;
  font-size: 38px;
  line-height: 1.15;
  color: #F8FAFC;
  margin: 0 0 12px 0;
  letter-spacing: -0.02em;
}

.hero h1 .accent {
  background: linear-gradient(90deg, #6EE7C9, #3B82F6, #8B5CF6);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.hero p {
  font-size: 15px;
  color: #94A3B8;
  margin: 0;
  max-width: 500px;
  line-height: 1.6;
}

.hero-glow {
  position: absolute;
  top: -50%;
  right: -20%;
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(139, 92, 246, 0.15) 0%, transparent 70%);
  pointer-events: none;
}

/* ---- SECCIÓN ---- */
.sec {
  margin-bottom: 40px;
}

.sec-hdr {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 22px;
  padding-bottom: 14px;
  border-bottom: 1px solid var(--border-soft, #1A222E);
}

.sec-num {
  width: 28px;
  height: 28px;
  background: rgba(34, 211, 168, 0.15);
  color: var(--brand, #22D3A8);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
  border: 1px solid rgba(34, 211, 168, 0.3);
}

.sec-hdr h2 {
  font-family: 'Sora', sans-serif;
  font-weight: 700;
  font-size: 20px;
  margin: 0;
  color: var(--text, #F4F6F9);
}

.sec-hdr p {
  font-size: 13.5px;
  color: #64748B;
  margin: 2px 0 0 0;
}

/* ---- TARJETAS CON EFECTO HOVER INTERACTIVO Y BRILLO ---- */
.g2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.card-tilt {
  position: relative;
  background: #111827;
  border: 1px solid #1F2937;
  border-radius: 18px;
  overflow: hidden;
  text-decoration: none;
  color: inherit;
  box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.card-inner {
  position: relative;
  z-index: 2;
  padding: 26px;
  display: flex;
  align-items: center;
  gap: 18px;
}

/* Línea lateral de acento distintiva */
.card-tilt.c-teal { border-left: 4px solid #22D3A8; }
.card-tilt.c-purple { border-left: 4px solid #8B5CF6; }

.card-tilt:hover {
  transform: translateY(-6px) scale(1.01);
  box-shadow: 0 20px 40px -15px rgba(34, 211, 168, 0.2);
  border-color: rgba(34, 211, 168, 0.4);
  background: #131F33;
}

.card-tilt.c-purple:hover {
  box-shadow: 0 20px 40px -15px rgba(139, 92, 246, 0.25);
  border-color: rgba(139, 92, 246, 0.4);
}

/* Iconos estilizados */
.card-icon-wrap {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  background: rgba(34, 211, 168, 0.12);
  color: #22D3A8;
  border: 1px solid rgba(34, 211, 168, 0.25);
  transition: transform 0.3s ease;
}

.card-tilt:hover .card-icon-wrap {
  transform: scale(1.1) rotate(-5deg);
}

.purple-icon {
  background: rgba(139, 92, 246, 0.12);
  color: #A78BFA;
  border-color: rgba(139, 92, 246, 0.25);
}

.card-info {
  flex: 1;
}

.card-info h3 {
  font-family: 'Sora', sans-serif;
  font-weight: 700;
  font-size: 16.5px;
  margin: 0 0 6px 0;
  color: #F8FAFC;
  transition: color 0.2s;
}

.card-tilt:hover .card-info h3 {
  color: #22D3A8;
}
.card-tilt.c-purple:hover .card-info h3 {
  color: #C4B5FD;
}

.card-info p {
  font-size: 13px;
  color: #94A3B8;
  line-height: 1.5;
  margin: 0;
}

/* Flechas de navegación animadas */
.card-arrow-wrap {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 1px solid #1F2937;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748B;
  flex-shrink: 0;
  transition: all 0.25s ease;
}

.card-tilt:hover .card-arrow-wrap {
  background: #22D3A8;
  border-color: #22D3A8;
  color: #04241C;
  transform: translateX(4px);
}

.card-tilt.c-purple:hover .card-arrow-wrap {
  background: #8B5CF6;
  border-color: #8B5CF6;
  color: #FFFFFF;
}

@media (max-width: 768px) {
  .g2 { grid-template-columns: 1fr; }
}
</style>