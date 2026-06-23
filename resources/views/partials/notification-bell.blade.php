{{-- Campana de notificaciones (fija arriba a la derecha en todas las vistas autenticadas) --}}
<div x-data="notifBell()" x-init="init()" @click.away="open = false" class="notif-bell">
    <button type="button" @click="toggle()" class="notif-bell-btn" title="{{ __('Notificaciones') }}">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span x-show="count > 0" x-cloak class="notif-badge" x-text="count > 99 ? '99+' : count"></span>
    </button>

    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="notif-panel">

        <div class="notif-head">
            <span>{{ __('Notificaciones') }}</span>
            <button type="button" x-show="count > 0" @click="markAll()">{{ __('Marcar todas como leídas') }}</button>
        </div>

        <template x-if="items.length === 0">
            <div class="notif-empty">{{ __('No tienes notificaciones') }}</div>
        </template>

        <template x-for="n in items" :key="n.id">
            <a :href="n.data.url" @click="markRead(n)"
               class="notif-item" :class="{ 'notif-unread': !n.read }">
                <div class="notif-title" x-text="msg(n)"></div>
                <div class="notif-sub">
                    <span class="notif-client" x-text="n.data.client"></span>
                    <span x-show="n.time"> · <span x-text="n.time"></span></span>
                </div>
            </a>
        </template>
    </div>
</div>

<script>
    function notifBell() {
        return {
            open: false,
            count: 0,
            items: [],
            init() {
                this.load();
                setInterval(() => this.load(), 30000);
            },
            async load() {
                try {
                    const r = await fetch('{{ route('notifications.index') }}', { headers: { 'Accept': 'application/json' } });
                    if (!r.ok) return;
                    const d = await r.json();
                    this.count = d.unread || 0;
                    this.items = d.items || [];
                } catch (e) {}
            },
            toggle() {
                this.open = !this.open;
                if (this.open) this.load();
            },
            msg(n) {
                return (n.data && n.data.kind === 'created')
                    ? '{{ __('Nueva negociación asignada') }}'
                    : '{{ __('Se te asignó una negociación') }}';
            },
            markRead(n) {
                if (n.read) return;
                n.read = true;
                if (this.count > 0) this.count--;
                try {
                    fetch('{{ url('/notifications') }}/' + n.id + '/read', {
                        method: 'POST',
                        keepalive: true,
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                    });
                } catch (e) {}
            },
            async markAll() {
                this.items.forEach(i => i.read = true);
                this.count = 0;
                try {
                    await fetch('{{ route('notifications.readAll') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                    });
                } catch (e) {}
            },
        }
    }
</script>
