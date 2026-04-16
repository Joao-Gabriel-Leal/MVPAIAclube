<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Plan;
use Illuminate\Support\Str;

class LandingController extends Controller
{
    public function index()
    {
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
            'heroImage' => '/images/clubeaia/hero-banner.svg',
            'aboutText' => 'O ClubeAIA combina esporte, lazer e convivio em um ambiente pensado para dias mais leves, encontros marcantes e uma rotina de clube que se entende rapido.',
            'galleryImages' => [
                [
                    'src' => '/images/clubeaia/gallery-ambiente.svg',
                    'alt' => 'Ambiente principal do ClubeAIA',
                    'title' => 'Ambiente social',
                ],
                [
                    'src' => '/images/clubeaia/gallery-quadras.svg',
                    'alt' => 'Quadras do ClubeAIA',
                    'title' => 'Esporte',
                ],
                [
                    'src' => '/images/clubeaia/gallery-piscina.svg',
                    'alt' => 'Area de piscina do ClubeAIA',
                    'title' => 'Lazer',
                ],
                [
                    'src' => '/images/clubeaia/gallery-lounge.svg',
                    'alt' => 'Lounge do ClubeAIA',
                    'title' => 'Convivio',
                ],
                [
                    'src' => '/images/clubeaia/gallery-fitness.svg',
                    'alt' => 'Espaco fitness do ClubeAIA',
                    'title' => 'Bem-estar',
                ],
                [
                    'src' => '/images/clubeaia/gallery-eventos.svg',
                    'alt' => 'Espaco de eventos do ClubeAIA',
                    'title' => 'Eventos',
                ],
            ],
        ]);
    }
}
