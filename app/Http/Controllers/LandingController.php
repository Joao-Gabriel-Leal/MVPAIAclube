<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ClubSetting;
use App\Models\Plan;
use App\Support\ClubMediaSlots;

class LandingController extends Controller
{
    public function index()
    {
        $clubSetting = ClubSetting::current();
        $homeMediaLibrary = $clubSetting->homeMediaLibrary();
        $brandName = $clubSetting->resolvedBrandName();
        $activePlans = Plan::query()->active()->orderBy('id')->get();
        $recommendedPlanId = null;

        if ($clubSetting->recommended_plan_id) {
            $recommendedPlanId = $activePlans->contains('id', $clubSetting->recommended_plan_id)
                ? $clubSetting->recommended_plan_id
                : null;
        } else {
            $recommendedPlanId = $activePlans->firstWhere('slug', 'familia')?->id;
        }

        $branches = Branch::query()
            ->active()
            ->orderByRaw("case when type = 'headquarters' then 0 else 1 end")
            ->orderBy('name')
            ->get()
            ->map(function (Branch $branch) {
                return [
                    'model' => $branch,
                    'type' => $branch->type->value,
                    'name' => $branch->name,
                    'address' => (string) ($branch->address ?: 'Endereco em breve'),
                    'city' => $branch->publicCity(),
                    'phone' => $branch->publicPhone(),
                    'phone_link' => $branch->publicPhoneLink(),
                    'whatsapp' => $branch->publicWhatsapp(),
                    'whatsapp_link' => $branch->publicWhatsappLink(),
                    'email' => $branch->email,
                    'hours' => $branch->publicHours(),
                    'summary' => $branch->publicSummary(),
                ];
            });

        $plans = $activePlans
            ->map(function (Plan $plan) {
                $benefits = [
                    'Ate '.$plan->dependent_limit.' dependente(s)',
                    'Ate '.$plan->guest_limit_per_reservation.' convidado(s) por reserva',
                    ($plan->free_reservations_per_month > 0
                        ? $plan->free_reservations_per_month.' reserva(s) gratuita(s) por mes'
                        : 'Acesso essencial ao calendario'),
                    ($plan->extra_reservation_discount_value > 0
                        ? 'Ate '.$plan->extra_reservation_discount_value.'% de desconto extra'
                        : 'Sem desconto adicional'),
                ];

                return [
                    'id' => $plan->id,
                    'model' => $plan,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'price' => $plan->base_price,
                    'benefits' => array_slice($benefits, 0, 4),
                ];
            })
            ->map(fn (array $plan) => [
                ...$plan,
                'recommended' => $plan['id'] === $recommendedPlanId,
            ]);

        return view('welcome', [
            'clubSetting' => $clubSetting,
            'brandName' => $brandName,
            'branches' => $branches,
            'plans' => $plans,
            'heroImageUrl' => $homeMediaLibrary['hero_banner']?->url(),
            'heroTitle' => $clubSetting->resolvedHeroTitle(),
            'heroSubtitle' => $clubSetting->resolvedHeroSubtitle(),
            'aboutText' => $clubSetting->resolvedAboutText(),
            'galleryImages' => collect(ClubMediaSlots::home())
                ->reject(fn (array $definition, string $slot) => $slot === 'hero_banner')
                ->map(function (array $definition, string $slot) use ($homeMediaLibrary) {
                    $asset = $homeMediaLibrary[$slot] ?? null;

                    return [
                        'slot' => $slot,
                        'src' => $asset?->url(),
                        'alt' => null,
                        'title' => $definition['gallery_title'] ?? $definition['title'],
                        'placeholder' => $definition['placeholder_label'],
                    ];
                })
                ->values(),
        ]);
    }
}
