<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Deal;
use App\Models\DealComment;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Support\CustomFieldsHelper;
use Illuminate\Http\Request;

class PublicFormController extends Controller
{
    /** Muestra el formulario público. */
    public function show(Request $request, string $slug)
    {
        $form = Form::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $form->load(['fields.customField']);

        return view('public.form', [
            'form'       => $form,
            'errorsList' => [],
            'old'        => [],
            'success'    => false,
            'embed'      => $request->boolean('embed'),
        ]);
    }

    /** Procesa el envío del formulario (público, sin sesión/CSRF). */
    public function submit(Request $request, string $slug)
    {
        $form = Form::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $form->load(['fields.customField']);
        $team = $form->team;
        $embed = $request->boolean('embed');

        // ---- Validación manual (sin depender de sesión, para que funcione incrustado) ----
        $errors = $this->validateSubmission($request, $form);
        if (!empty($errors)) {
            return response()->view('public.form', [
                'form'       => $form,
                'errorsList' => $errors,
                'old'        => $request->all(),
                'success'    => false,
                'embed'      => $embed,
            ], 422);
        }

        $email = trim((string) $request->input('email'));
        $phone = trim((string) $request->input('phone'));
        $name  = trim((string) $request->input('name'))
              ?: ($email ?: ($phone ?: __('Contacto sin nombre')));

        // Responsable de la negociación: reparto aleatorio y equitativo entre los
        // usuarios marcados en el formulario (null si no hay ninguno marcado).
        $responsibleId = $this->pickResponsible($form, $team);
        // Usuario válido para dueño y autor del comentario del sistema.
        $ownerUserId = $responsibleId ?: $team->user_id;

        // ---- 1) Deduplicación de CONTACTO por teléfono o correo ----
        $contact = $this->findExistingContact($team->id, $email, $phone);

        if (!$contact) {
            $contact = Contact::create([
                'team_id'  => $team->id,
                'owner_id' => $ownerUserId,
                'name'     => $name,
                'first_name' => $name,
                'email'    => $email ?: null,
                'phone'    => $phone ?: null,
                'company'  => $request->input('company') ?: null,
                'status'   => 'nuevo',
                'source'   => 'formulario: ' . $form->name,
            ]);
        } else {
            // Completa datos faltantes sin sobrescribir lo existente.
            $dirty = false;
            if (!$contact->email && $email)   { $contact->email = $email; $dirty = true; }
            if (!$contact->phone && $phone)   { $contact->phone = $phone; $dirty = true; }
            if (!$contact->company && $request->input('company')) { $contact->company = $request->input('company'); $dirty = true; }
            if ($dirty) $contact->save();
        }

        // Guarda valores de campos personalizados de CONTACTO (sync filtra por entidad).
        CustomFieldsHelper::sync($contact, $request->input('custom_fields', []), $team->id, 'contact');

        // ---- 2) Negociación ----
        $deal = $this->handleDeal($form, $team, $contact, $ownerUserId, $responsibleId);

        if ($deal) {
            CustomFieldsHelper::sync($deal, $request->input('custom_fields', []), $team->id, 'deal');
        }

        // ---- 3) Registro del envío ----
        FormSubmission::create([
            'form_id'    => $form->id,
            'team_id'    => $team->id,
            'contact_id' => $contact->id,
            'deal_id'    => $deal?->id,
            'form_name'  => $form->name,
            'payload'    => $request->except(['_token', 'embed']),
            'ip'         => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        // ---- 4) Éxito ----
        if ($form->redirect_url) {
            return redirect()->away($form->redirect_url);
        }

        return view('public.form', [
            'form'           => $form,
            'errorsList'     => [],
            'old'            => [],
            'success'        => true,
            'successMessage' => $form->success_message ?: __('¡Gracias! Hemos recibido tus datos.'),
            'embed'          => $embed,
        ]);
    }

    /** Devuelve el script de incrustación para la web del cliente (iframe autoajustable). */
    public function embed(string $slug)
    {
        $form = Form::where('slug', $slug)->firstOrFail();
        $url  = route('public.form.show', $form->slug) . '?embed=1';

        $js = <<<JS
(function(){
  var s = document.currentScript;
  var iframe = document.createElement('iframe');
  iframe.src = "{$url}";
  iframe.style.width = "100%";
  iframe.style.border = "0";
  iframe.style.minHeight = "520px";
  iframe.setAttribute("scrolling", "no");
  iframe.setAttribute("title", "Formulario");
  (s && s.parentNode ? s.parentNode : document.body).insertBefore(iframe, s);
  window.addEventListener("message", function(e){
    if (e && e.data && e.data.qipuFormHeight && e.source === iframe.contentWindow) {
      iframe.style.height = e.data.qipuFormHeight + "px";
    }
  });
})();
JS;

        return response($js)->header('Content-Type', 'application/javascript; charset=utf-8');
    }

    /* ===================== Helpers ===================== */

    private function validateSubmission(Request $request, Form $form): array
    {
        $errors = [];

        foreach ($form->fields as $field) {
            if ($field->source === 'custom') {
                $key   = "custom_fields.{$field->custom_field_id}";
                $value = $request->input($key);
            } else {
                $key   = $field->core_key;
                $value = $request->input($key);
            }

            $empty = is_array($value) ? count($value) === 0 : trim((string) $value) === '';

            if ($field->is_required && $empty) {
                $errors[$key] = __(':campo es obligatorio.', ['campo' => $field->displayLabel()]);
            }
        }

        // El correo, si viene, debe ser válido.
        $email = trim((string) $request->input('email'));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = __('El correo no es válido.');
        }

        // Siempre se requiere al menos un nombre.
        if (trim((string) $request->input('name')) === '') {
            $errors['name'] = __('El nombre es obligatorio.');
        }

        return $errors;
    }

    private function findExistingContact(int $teamId, string $email, string $phone): ?Contact
    {
        if ($email === '' && $phone === '') return null;

        return Contact::where('team_id', $teamId)
            ->where(function ($q) use ($email, $phone) {
                if ($email !== '') $q->orWhereRaw('LOWER(email) = ?', [mb_strtolower($email)]);
                if ($phone !== '') $q->orWhere('phone', $phone);
            })
            ->first();
    }

    /**
     * Crea o reutiliza la negociación según la configuración de duplicados del formulario.
     * Deja un comentario de sistema en la negociación.
     */
    private function handleDeal(Form $form, $team, Contact $contact, int $ownerUserId, ?int $responsibleId): ?Deal
    {
        // Pipeline destino: el del formulario o, si no hay, el predeterminado del team.
        $pipeline = $form->pipeline
            ?: Pipeline::where('team_id', $team->id)
                ->where('is_active', true)
                ->orderByDesc('is_default')->orderBy('sort_order')
                ->first();

        if (!$pipeline) return null;

        // Etapa destino para negociaciones NUEVAS: la configurada o la primera del embudo.
        $targetStage = ($form->stage_id
            ? PipelineStage::where('pipeline_id', $pipeline->id)->find($form->stage_id)
            : null)
            ?: $pipeline->stages()->orderBy('sort_order')->first();

        if (!$targetStage) return null;

        $deal = null;

        // Modo "usar la activa": buscar una negociación abierta del contacto en ese embudo.
        if ($form->deal_dedup_mode === 'use_active') {
            $deal = Deal::where('team_id', $team->id)
                ->where('contact_id', $contact->id)
                ->where('pipeline_id', $pipeline->id)
                ->where('status', 'open')
                ->latest('id')
                ->first();
        }

        if ($deal) {
            // Reutiliza la negociación activa; opcionalmente la mueve a otra etapa.
            if ($form->move_stage_id) {
                $moveStage = PipelineStage::where('pipeline_id', $pipeline->id)->find($form->move_stage_id);
                if ($moveStage) {
                    $deal->stage_id = $moveStage->id;
                    $deal->status   = $moveStage->is_won ? 'won' : ($moveStage->is_lost ? 'lost' : 'open');
                    $deal->save();
                }
            }
        } else {
            // Crea una negociación nueva.
            $status = $targetStage->is_won ? 'won' : ($targetStage->is_lost ? 'lost' : 'open');

            $deal = Deal::create([
                'team_id'        => $team->id,
                'owner_id'       => $ownerUserId,
                'responsible_id' => $responsibleId,
                'contact_id'     => $contact->id,
                'pipeline_id'    => $pipeline->id,
                'stage_id'       => $targetStage->id,
                'title'          => $this->buildDealTitle($form, $contact),
                'currency'       => 'PEN',
                'status'         => $status,
                'description'    => __('Creado desde el formulario') . ': ' . $form->name,
            ]);
        }

        // Comentario de sistema con el nombre del formulario y la hora.
        $tz   = method_exists($team, 'effectiveTimezone') ? $team->effectiveTimezone() : 'America/Lima';
        $when = now()->timezone($tz)->format('d/m/Y H:i');
        DealComment::create([
            'deal_id' => $deal->id,
            'user_id' => $ownerUserId,
            'body'    => '📝 ' . __('El cliente completó el formulario') . " «{$form->name}» — {$when}",
        ]);

        return $deal;
    }

    /**
     * Elige el responsable para esta negociación entre los usuarios marcados en el
     * formulario. Reparto ALEATORIO pero EQUITATIVO: escoge entre los que menos
     * negociaciones de este formulario tienen ya asignadas, con desempate al azar.
     * Devuelve null si el formulario no tiene responsables marcados.
     */
    private function pickResponsible(Form $form, $team): ?int
    {
        $candidates = array_values(array_filter(array_map('intval', (array) ($form->assigned_user_ids ?? []))));
        if (empty($candidates)) return null;

        // Deja solo usuarios que siguen perteneciendo al equipo.
        $validIds   = $team->allUsers()->pluck('id')->map(fn ($i) => (int) $i)->all();
        $candidates = array_values(array_intersect($candidates, $validIds));

        if (empty($candidates))   return null;
        if (count($candidates) === 1) return (int) $candidates[0];

        // Cuántas negociaciones de ESTE formulario tiene ya asignada cada candidato.
        $counts = FormSubmission::query()
            ->where('form_submissions.form_id', $form->id)
            ->join('deals', 'deals.id', '=', 'form_submissions.deal_id')
            ->whereIn('deals.responsible_id', $candidates)
            ->groupBy('deals.responsible_id')
            ->selectRaw('deals.responsible_id as uid, COUNT(*) as c')
            ->pluck('c', 'uid')
            ->toArray();

        // Escoge entre los menos cargados; desempate aleatorio.
        $min = null;
        $best = [];
        foreach ($candidates as $uid) {
            $c = (int) ($counts[$uid] ?? 0);
            if ($min === null || $c < $min) {
                $min = $c;
                $best = [$uid];
            } elseif ($c === $min) {
                $best[] = $uid;
            }
        }

        return (int) $best[array_rand($best)];
    }

    private function buildDealTitle(Form $form, Contact $contact): string
    {
        $template = $form->deal_title_template ?: '{form} - {name}';
        $title = strtr($template, [
            '{form}'  => $form->name,
            '{name}'  => $contact->name,
            '{email}' => (string) $contact->email,
            '{phone}' => (string) $contact->phone,
        ]);
        $title = trim($title);

        return $title !== '' ? mb_substr($title, 0, 255) : $form->name;
    }
}
