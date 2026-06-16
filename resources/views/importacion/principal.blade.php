 <x-app-layout>
  <div class="p-6">
    <h1 class="text-xl sm:text-2xl font-bold mb-4" :style="!$store.sidebar.open ? 'padding-left:3.75rem;' : ''">Importar Reporte Mensual</h1>
    <p>Solo visible y accesible para administradores.</p>
    {{-- Aquí tu formulario de importación --}}
  </div>
</x-app-layout>
