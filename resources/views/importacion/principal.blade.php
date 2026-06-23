 <x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Importar Reporte Mensual') }}</h2>
  </x-slot>
  <div class="p-6">
    <p>{{ __('Solo visible y accesible para administradores.') }}</p>
    {{-- Aquí tu formulario de importación --}}
  </div>
</x-app-layout>
