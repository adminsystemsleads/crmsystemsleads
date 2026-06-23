{{-- Campana de notificaciones (fija arriba a la derecha en todas las vistas autenticadas) --}}
<div x-data="notifBell()" x-init="init()" @click.away="open = false" class="notif-bell">
    <button type="button" @click="toggle()" class="notif-bell-btn" title="{{ __('Notificaciones') }}">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span x-show="count > 0 && prefs.enabled" x-cloak class="notif-badge" x-text="count > 99 ? '99+' : count"></span>
    </button>

    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="notif-panel">

        {{-- Cabecera del panel --}}
        <div class="notif-head">
            <span x-text="view === 'settings' ? '{{ __('Configurar notificaciones') }}' : '{{ __('Notificaciones') }}'"></span>
            <div class="notif-head-actions">
                <template x-if="view === 'list'">
                    <span style="display:inline-flex;align-items:center;gap:.35rem;">
                        {{-- Silenciar / activar sonido --}}
                        <button type="button" @click="toggleSound()" class="notif-icon-btn"
                                :title="prefs.sound ? '{{ __('Silenciar') }}' : '{{ __('Activar sonido') }}'">
                            <svg x-show="prefs.sound" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072M17.95 6.05a8 8 0 010 11.9M5 9v6h4l5 4V5L9 9H5z"/>
                            </svg>
                            <svg x-show="!prefs.sound" x-cloak fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 9v6h4l5 4V5L9 9H5zM17 9l4 4m0-4l-4 4"/>
                            </svg>
                        </button>
                        {{-- Tuerca de configuración --}}
                        <button type="button" @click="openSettings()" class="notif-icon-btn" title="{{ __('Configurar notificaciones') }}">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                    </span>
                </template>
                <template x-if="view === 'settings'">
                    <button type="button" @click="view = 'list'" class="notif-link">{{ __('Volver') }}</button>
                </template>
            </div>
        </div>

        {{-- ===== Vista LISTA ===== --}}
        <div x-show="view === 'list'">
            <div x-show="count > 0" class="notif-subhead">
                <button type="button" @click="markAll()" class="notif-link">{{ __('Marcar todas como leídas') }}</button>
            </div>

            <template x-if="items.length === 0">
                <div class="notif-empty">{{ __('No tienes notificaciones') }}</div>
            </template>

            <template x-for="n in items" :key="n.id">
                <a :href="n.data.url" @click="markRead(n)"
                   class="notif-item" :class="{ 'notif-unread': !n.read }">
                    <div class="notif-title" x-text="msg(n)"></div>
                    <div class="notif-sub">
                        <span class="notif-client" x-text="sub(n)"></span>
                        <span x-show="n.time"> · <span x-text="n.time"></span></span>
                    </div>
                </a>
            </template>
        </div>

        {{-- ===== Vista CONFIGURACIÓN ===== --}}
        <div x-show="view === 'settings'" x-cloak class="notif-settings">
            <label class="notif-row">
                <span>{{ __('Activar notificaciones') }}</span>
                <input type="checkbox" x-model="prefs.enabled">
            </label>
            <label class="notif-row">
                <span>{{ __('Sonido') }}</span>
                <input type="checkbox" x-model="prefs.sound">
            </label>

            <div class="notif-sep"></div>

            <label class="notif-row">
                <span>{{ __('Notificar negociaciones asignadas') }}</span>
                <input type="checkbox" x-model="prefs.deal_assigned">
            </label>
            <label class="notif-row">
                <span>{{ __('Notificar actividades por vencer') }}</span>
                <input type="checkbox" x-model="prefs.activity_due">
            </label>

            <div class="notif-sep"></div>

            <div class="notif-row" style="cursor:default;">
                <span style="font-weight:700;">{{ __('Embudos a notificar') }}</span>
                <button type="button" @click="toggleAllPipes()" class="notif-link"
                        x-text="allPipesOn() ? '{{ __('Desmarcar todos') }}' : '{{ __('Marcar todos') }}'"></button>
            </div>
            <template x-if="pipelines.length === 0">
                <div style="padding:.25rem .9rem .5rem;font-size:.72rem;color:#9ca3af;">{{ __('No hay embudos disponibles.') }}</div>
            </template>
            <template x-for="p in pipelines" :key="p.id">
                <label class="notif-row notif-pipe">
                    <span x-text="p.name"></span>
                    <input type="checkbox" :checked="isPipeOn(p.id)" @change="togglePipe(p.id)">
                </label>
            </template>

            <div class="notif-settings-foot">
                <button type="button" @click="savePrefs()" class="notif-save">{{ __('Guardar') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
    function notifBell() {
        return {
            open: false,
            view: 'list',
            count: 0,
            items: [],
            prevCount: null,
            loaded: false,
            prefs: { enabled: true, sound: true, deal_assigned: true, activity_due: true, pipelines_excluded: [] },
            pipelines: [],
            excludedPipes: [],

            init() {
                this.loadPrefs();
                this.load();
                setInterval(() => this.load(), 30000);
            },

            async load() {
                try {
                    const r = await fetch('{{ route('notifications.index') }}', { headers: { 'Accept': 'application/json' } });
                    if (!r.ok) return;
                    const d = await r.json();
                    const newCount = d.unread || 0;
                    if (this.loaded && newCount > this.prevCount && this.prefs.enabled && this.prefs.sound) {
                        this.beep();
                    }
                    this.prevCount = newCount;
                    this.count = newCount;
                    this.items = d.items || [];
                    this.loaded = true;
                } catch (e) {}
            },

            async loadPrefs() {
                try {
                    const r = await fetch('{{ route('notifications.prefs') }}', { headers: { 'Accept': 'application/json' } });
                    if (!r.ok) return;
                    const d = await r.json();
                    this.prefs = Object.assign(this.prefs, d.prefs || {});
                    this.pipelines = d.pipelines || [];
                    this.excludedPipes = (this.prefs.pipelines_excluded || []).slice();
                } catch (e) {}
            },

            toggle() {
                this.open = !this.open;
                if (this.open) { this.view = 'list'; this.load(); }
            },
            openSettings() { this.loadPrefs(); this.view = 'settings'; },

            msg(n) {
                const k = n.data && n.data.kind;
                if (k === 'activity_due') return '{{ __('Actividad por vencer') }}';
                if (k === 'created') return '{{ __('Nueva negociación asignada') }}';
                return '{{ __('Se te asignó una negociación') }}';
            },
            sub(n) {
                if (!n.data) return '';
                if (n.data.kind === 'activity_due') {
                    return (n.data.subject ? n.data.subject + ' — ' : '') + (n.data.client || '');
                }
                return n.data.client || '';
            },

            isPipeOn(id) { return !this.excludedPipes.includes(id); },
            togglePipe(id) {
                const i = this.excludedPipes.indexOf(id);
                if (i >= 0) this.excludedPipes.splice(i, 1);  // estaba excluido -> activar
                else this.excludedPipes.push(id);             // estaba activo -> excluir
            },
            allPipesOn() { return this.excludedPipes.length === 0; },
            toggleAllPipes() {
                // Si están todos activos -> excluir todos; si no -> activar todos.
                this.excludedPipes = this.allPipesOn() ? this.pipelines.map(p => p.id) : [];
            },

            async toggleSound() {
                this.prefs.sound = !this.prefs.sound;
                await this.persist();
            },

            async savePrefs() {
                await this.persist();
                this.view = 'list';
            },

            async persist() {
                const payload = {
                    enabled: !!this.prefs.enabled,
                    sound: !!this.prefs.sound,
                    deal_assigned: !!this.prefs.deal_assigned,
                    activity_due: !!this.prefs.activity_due,
                    pipelines_excluded: this.excludedPipes,
                };
                this.prefs.pipelines_excluded = this.excludedPipes.slice();
                try {
                    await fetch('{{ route('notifications.savePrefs') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                } catch (e) {}
            },

            markRead(n) {
                if (n.read) return;
                n.read = true;
                if (this.count > 0) this.count--;
                try {
                    fetch('{{ url('/notifications') }}/' + n.id + '/read', {
                        method: 'POST', keepalive: true,
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

            beep() {
                try {
                    const Ctx = window.AudioContext || window.webkitAudioContext;
                    if (!Ctx) return;
                    const ctx = new Ctx();
                    const o = ctx.createOscillator();
                    const g = ctx.createGain();
                    o.connect(g); g.connect(ctx.destination);
                    o.type = 'sine'; o.frequency.value = 880;
                    g.gain.setValueAtTime(0.0001, ctx.currentTime);
                    g.gain.exponentialRampToValueAtTime(0.18, ctx.currentTime + 0.02);
                    g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.35);
                    o.start();
                    o.stop(ctx.currentTime + 0.36);
                } catch (e) {}
            },
        }
    }

    // Alinea la campana al centro vertical de la cabecera (app-header o barra de WhatsApp),
    // que tienen distinta altura. Se recalcula al cargar, al hacer scroll y al redimensionar.
    (function () {
        function alignBell() {
            var b = document.querySelector('.notif-bell');
            if (!b) return;
            var h = document.querySelector('.app-header') || document.querySelector('.wa-topbar');
            if (!h) { b.style.top = '1rem'; return; }
            var r = h.getBoundingClientRect();
            var bh = b.offsetHeight || 38;
            var top = r.top + (r.height / 2) - (bh / 2);
            if (top < 4) top = 4;
            b.style.top = top + 'px';
        }
        window.addEventListener('scroll', alignBell, true);
        window.addEventListener('resize', alignBell);
        document.addEventListener('DOMContentLoaded', alignBell);
        // Reintenta tras el render inicial (por si la cabecera mide distinto al cargar).
        setTimeout(alignBell, 200);
    })();
</script>
