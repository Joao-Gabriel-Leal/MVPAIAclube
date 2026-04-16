<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ClubSetting;
use App\Models\Plan;
use App\Support\ClubMediaSlots;
use Illuminate\Support\Str;

class LandingController extends Controller
{
    public function index()
    {
        $clubSetting = ClubSetting::current();
        $homeMediaLibrary = $clubSetting->homeMediaLibrary();

        $branches = Branch::query()
            ->active()
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->map(function (Branch $branch) {
                $address = (string) $branch->address;

                return [
                    'model' => $branch,
                    'name' => $branch->name,
                    'address' => $address !== '' ? Str::beforeLast($address, ' - ') : 'Endereco em breve',
                    'city' => $address !== '' ? Str::afterLast($address, ' - ') : 'Localizacao em breve',
                ];
            });

        $plans = Plan::query()
            ->active()
            ->orderBy('id')
            ->get()
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
                    'model' => $plan,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'price' => $plan->base_price,
                    'recommended' => $plan->slug === 'prata',
                    'benefits' => array_slice($benefits, 0, 4),
                ];
            });

        return view('welcome', [
            'branches' => $branches,
            'plans' => $plans,
            'heroImageUrl' => $homeMediaLibrary['hero_banner']?->url(),
            'aboutText' => 'O ClubeAIA combina esporte, lazer e convivio em um ambiente pensado para dias mais leves, encontros marcantes e uma rotina de clube que se entende rapido.',
            'galleryImages' => collect(ClubMediaSlots::home())
                ->reject(fn (array $definition, string $slot) => $slot === 'hero_banner')
                ->map(function (array $definition, string $slot) use ($homeMediaLibrary) {
                    $asset = $homeMediaLibrary[$slot] ?? null;

                    return [
                        'slot' => $slot,
                        'src' => $asset?->url(),
                        'alt' => 'Imagem institucional do ClubeAIA',
                        'title' => $definition['gallery_title'] ?? $definition['title'],
                        'placeholder' => $definition['placeholder_label'],
                    ];
                })
                ->values(),
        ]);
    }
}
