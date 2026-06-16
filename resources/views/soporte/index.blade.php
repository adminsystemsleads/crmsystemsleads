<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Soporte') }}
    </h2>
  </x-slot>

  <div class="max-w-3xl mx-auto py-8 px-4 space-y-6">

    <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
      <h3 class="text-base font-bold text-gray-900 mb-1">{{ __('¿Necesitas ayuda?') }}</h3>
      <p class="text-sm text-gray-600">
        {{ __('Escríbenos por el chat en vivo (botón flotante en la esquina) o déjanos tus datos en el formulario y nuestro equipo de soporte te contactará.') }}
      </p>
    </div>

    {{-- Formulario de soporte (Bitrix24) --}}
    <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
      @verbatim
      <script data-b24-form="inline/66/9cqfnj" data-skip-moving="true">
        (function(w,d,u){
          var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/180000|0);
          var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
        })(window,document,'https://cdn.bitrix24.es/b25161293/crm/form/loader_66.js');
      </script>
      @endverbatim
    </div>

  </div>

  {{-- Widget flotante de chat en vivo (Bitrix24) --}}
  @verbatim
  <script>
    (function(w,d,u){
      var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/60000|0);
      var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
    })(window,document,'https://cdn.bitrix24.es/b25161293/crm/site_button/loader_12_1vffez.js');
  </script>
  @endverbatim
</x-app-layout>
